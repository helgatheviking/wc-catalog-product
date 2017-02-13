<?php
/**
 * Process the form submission
 *
 * @version     0.1-beta
 * @author      helgatheviking
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Catalog_Proccesor' ) ) :

/**
 * Processing Class
 *
 * Handles the edit posts views and some functionality on the edit post screen for WC post types.
 */
class WC_Catalog_Proccesor {

   /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_loaded', array( $this, 'process_form' ) );
        add_action( 'wp_loaded', array( $this, 'auto_download' ) );
        add_action( 'wp_ajax_wc_create_catalog', array( $this, 'ajax_callback' ) ); 
        add_action( 'wp_ajax_nopriv_wc_create_catalog', array( $this, 'ajax_callback' ) ); 
    }


    /**
     * Process the PDFs and merge together
     * @since  1.0.0
     */ 
    public function process_form() {
            
        if( ! isset( $_REQUEST[ 'wp_catalog_product_nonce_field' ] ) || ! wp_verify_nonce( $_REQUEST[ 'wp_catalog_product_nonce_field' ], 'wp_catalog_product_nonce_action' ) ){
            return false;
        } 

        if ( empty( $_REQUEST[ 'product_id' ] ) ){
            return false;
        } 

        // Ensure 'download' button was not pressed and not add to cart button
        if( isset( $_REQUEST[ 'wc_catalog_create' ] ) ){
            $pdf_ids = ! empty( $_REQUEST[ 'catalog_merge_ids' ] ) && is_array( $_REQUEST[ 'catalog_merge_ids' ] ) ? $_REQUEST[ 'catalog_merge_ids' ] : array();

            // generate the pdf
            $this->create_pdf( $pdf_ids, intval( $_REQUEST['product_id'] ) );

        }

    }

    /**
     * Create the PDF file
     * @since  1.0.0
     * @param array $pdf_ids
     * @param int $product_id
     * @param str $context // Where is this being called from
     */ 
    public function create_pdf( $pdf_ids = array(), $product_id = 0, $context = 'product' ){

        if( empty( $pdf_ids) || empty( $product_id ) ){
            wc_add_notice( __( 'Please select some PDFs with which to create your catalog.', 'wc-catalog-product' ), 'error' );
            return false;
        }

        $product_id = intval( $product_id );

        // Create new PDF Merge object.
        require_once( WC_Catalog_Product()->plugin_path() . '/assets/composer/autoload.php' );

        try {

            $product = wc_get_product( $product_id );
                   
            $pdf = new PDFMerger;

            // add the PDF cover page
            if( $cover_pdf = $product->get_cover_id() ){
                $path = get_attached_file( $cover_pdf ); // Full path
                $pdf->addPDF( $path, 'all');
            }

             // get the allowed PDFs for this page, probably overkill
            $allowed_pdfs = $product->get_pdf_ids();

            // loop through all submitted PDFs
            foreach ( (array)$pdf_ids as $pdf_id ) {

                if( in_array( $pdf_id, $allowed_pdfs ) ){
                    $path = get_attached_file( $pdf_id ); // Full path//
                    $pdf->addPDF( $path, 'all' );
                }
           
            }

            // Create the file name.
            $post_title = sanitize_title_with_dashes( get_the_title( $product_id ) );
            $file_name = $post_title . __( '-catalog-', 'wc-catalog-product' ) . time() . '.pdf';

            // Path.
            $file_path = $this->get_path( $file_name );

            // Create the file URL.
            $file_url = $this->get_url( $file_name );
            
            // Merge the result.
            $pdf->merge( 'file', $file_path ); 

            // Check that we get an actual file and not an error.
            if( file_exists( $file_path ) ){

                // Schedule cron job to delete file
                wp_schedule_single_event( time() + 3600, 'wc_catalog_product_delete_temporary_file', array( $file_path ) );

                // Forced download link
                $link = add_query_arg( array( 'custom_catalog' => $file_name ), wp_get_referer() );

                // Add success message
                if( 'product' == $context ) {
                    $message = sprintf( __( 'If your download did not begin automatically, you can %sclick here to download%s your custom catalog.', 'wc-catalog-product' ),
                     sprintf( '<a href="%s" class="download-pdf" target="_blank" title="%s">', $link, __( 'Download your custom catalog', 'wc-catalog-product' ) ),
                     '</a>' );
                    wc_add_notice( $message, 'success' );
                }

                // send the legit file name back
                return $file_name;

            } else { 
                $message =  __( 'There was an error creating your PDF. Please try again.', 'wc-catalog-product' );
                throw new exception( $message );
            }
        } catch (exception $e) {
            if( 'product' == $context ) {
                wc_add_notice( $e->getMessage(), 'error' );
            }
        }

    }


    /**
     * Get the standardized URL for a specific filename
     * @since  1.0.0
     */ 
    public function get_url( $file_name = '' ) {
        $wp_upload_dir = wp_upload_dir();
        return trailingslashit( $wp_upload_dir['baseurl'] ) . $file_name;
    }

    /**
     * Get the standardized file path for a specific filename
     * @since  1.0.0
     */ 
    public function get_path( $file_name = '' ) {
        $wp_upload_dir = wp_upload_dir();
        return trailingslashit( $wp_upload_dir['basedir'] ) . $file_name;
    }

    /**
     * Force the download
     * @since  1.0.0
     */ 
    public function auto_download() {

        if( isset( $_GET['custom_catalog'] ) ){
            
            $file_path = $this->get_path( $_GET['custom_catalog'] );

            if( file_exists( $file_path ) ){

                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); 

                header('Content-Type: application/force-download');
                header('Content-Disposition: attachment; filename='.basename($file_path));

                header('Content-Description: File Transfer');
                header('Accept-Ranges: bytes');
                header('Content-Length: ' . filesize( $file_path ) );

                @readfile($file_path);
                exit();
            } else {
                wc_add_notice( __( 'This catalog no longer exists. Please try to regenerate it below.', 'wc-catalog-product' ), 'error' );
            }

        }
    
    }


    /**
     * Process the PDF via AJAX
     * @since  1.0.0
     */ 
    public function ajax_callback(){

        check_ajax_referer( 'wp_catalog_product_nonce_action', 'nonce' );

        if( ! isset( $_POST['product_id'] ) ){
            wp_die();
        }

        $product_id = intval( $_POST['product_id'] );
        $pdf_ids = ! empty( $_POST['catalog_merge_ids'] ) && is_array( $_POST['catalog_merge_ids'] ) ? $_POST['catalog_merge_ids'] : array();

        $result = array();

        if( $file_name = $this->create_pdf( $pdf_ids, $product_id ) ){
            $result['status'] = 1;
            $result['file_name'] = $file_name;
        } else {
            $result['status'] = 0;
        }

        $result['notices'] = wc_get_notices();

        echo json_encode($result);

        wp_die();

    }

}

endif;