<?php
/**
 * WC Catalog Product Admin Main Class
 *
 * Adds a setting tab and product meta.
 *
 * @package		WC Catalog Product
 * @category	Class
 * @author		Kathy Darling
 * @since		0.1.0
 */
class WC_Catalog_Product_Metabox {

	/**
	 * Bootstraps the class and hooks required actions & filters.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

		if( ! defined( 'DOING_AJAX' ) ) {

			// Product Meta boxes
			add_filter( 'product_type_selector', array( $this, 'product_selector_filter' ) );
			add_action( 'woocommerce_product_data_tabs', array( $this, 'product_data_tab' ) );
			add_action( 'woocommerce_product_data_panels', array( $this, 'product_data_panel' ) );
			add_action( 'woocommerce_admin_process_product_object', array( $this, 'process_data' ) );

			// Admin Scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'meta_box_script' ), 20 );

		}

	}

    /*-----------------------------------------------------------------------------------*/
	/* Write Panel / metabox */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Adds the 'mix and match product' type to the product types dropdown.
	 *
	 * @param  array 	$options
	 * @return array
	 */
	public static function product_selector_filter( $options ) {
		$options[ 'catalog' ] = __( 'Catalog Product', 'wc-catalog-product' );
		return $options;
	}


	/**
	 * Adds the Product write panel tabs.
	 *
	 * @param  array $tabs
	 * @return string
	 */
	public static function product_data_tab( $tabs ) {

		$tabs[ 'catalog_options' ] = array(
			'label'  => __( 'Catalog Product', 'wc-catalog-product' ),
			'target' => 'catalog_product_data',
			'class'  => array( 'show_if_custom-catalog', 'custom-catalog_product_tab', 'custom-catalog_product_options' )
		);

		$tabs[ 'inventory' ][ 'class' ][] = 'show_if_custom-catalog';

		return $tabs;
	}


	/**
	 * Write panel.
	 *
	 * @return html
	 */
	public static function product_data_panel() {
		global $post, $product_object;

		$_product = new WC_Product_Catalog( $post->ID );
		
		$pdf_cover_id = $_product->get_cover_id();
		$pdfs = $_product->get_pdf_ids();

		?>

		<div id="catalog_product_data" class="custom-catalog_panel panel woocommerce_options_panel wc-metaboxes-wrapper">
			<div class="options_group pdf_cover_container custom_catalog_container">

				<label><?php _e( 'PDF Cover Page', 'wc-custom-catalog' ); ?><?php wc_help_tip( __( 'Optionally, add a PDF that will always be used as the cover page for the resulting merged PDF.', 'wc-custom-catalog' ) );?></label>

				<table class="pdf_catalog">

				<?php
					if ( $pdf_cover_id > 0 ) {

						echo '<tr class="pdf pdf-row" data-attachment_id="' . esc_attr( $pdf_cover_id ) . '">
								<td>' . wp_get_attachment_image( $pdf_cover_id, 'thumbnail', true, array( 'alt' => esc_attr( get_the_title( $pdf_cover_id ) ) ) ) . '</td>
								<td>'. get_the_title( $pdf_cover_id ) . '</td>
								<td><a href="#" class="delete-pdf hide-if-no-js">' . __( 'Delete PDF', 'wc-custom-catalog' ) . '</a></td>
							</tr>';

					}

					?>
				</table>

				<input type="hidden" name="pdf_cover_id" value="<?php echo esc_attr( $pdf_cover_id ); ?>" />

				<button class="add_pdf_cover hide-if-no-js button"><?php _e( 'Add PDF Cover', 'wc-custom-catalog' ); ?></button>	
			</div>
			<div class="options_group custom_catalog_container pdf_catalog_container">
				<label><?php _e( 'PDF Catalogs', 'wc-custom-catalog' ); ?><?php wc_help_tip( __( 'Upload PDFs that will be available for merging.', 'wc-custom-catalog' ) );?></label>
				
				<table class="pdf_catalog pdf_catalog_sortable">
					<?php
						
						if ( ! empty( $pdfs ) ) {
							foreach ( $pdfs as $id ) {
								echo '<tr class="pdf pdf-row" data-attachment_id="' . esc_attr( $id ) . '">
									<td>' . wp_get_attachment_image( $id, 'thumbnail', true ) . '</td>
									<td>'. get_the_title( $id ) . '</td>
									<td><a href="#" class="delete-pdf hide-if-no-js">' . __( 'Delete PDF', 'wc-custom-catalog' ) . '</a></td>
								</tr>';
							}
						}
					?>
				</table>

				<input type="hidden" name="pdf_gallery_ids" value="<?php echo esc_attr( implode( ',', $pdfs ) ); ?>" />

				<button class="button add_pdf hide-if-no-js"><?php _e( 'Add PDF to Collection', 'wc-custom-catalog' )?></button>

			</div> <!-- options group -->
		</div>

	<?php
	}

	/**
	 * Process, verify and save product data
	 *
	 * @param  WC_Product  $product
	 * @return void
	 */
	public static function process_data( $product ) {

		if ( $product->is_type( 'catalog' ) ) {

			$cover_id = isset( $_POST['pdf_cover_id'] ) ? intval( sanitize_text_field( $_POST['pdf_cover_id'] ) ) : 0;
			$pdf_ids = isset( $_POST['pdf_gallery_ids'] ) ? array_filter( explode( ',', sanitize_text_field( $_POST['pdf_gallery_ids'] ) ) ) : array();

			error_log('pdf IDS = ' .json_encode($pdf_ids));

			// Show a notice if the user hasn't selected any items for the container.
			if ( empty( $pdf_ids ) ) {
				WC_Admin_Meta_Boxes::add_error( __( 'Please select at least one PDF for use in this custom catalog product.', 'wc-custom-catalog' ) );
			}

			$props = array(
				'cover_id'    => $cover_id,
				'pdf_ids'     => $pdf_ids,
			);

			$product->set_props( $props );

		}
	}


	/*
	 * Javascript to handle the metabox options
	 *
	 * @param string $hook
	 * @return void
	 * @since 0.1.0
	 */
    public function meta_box_script( $hook ){

		// check if on Edit-Post page (post.php or new-post.php).
		if( ! in_array( $hook, array( 'post-new.php', 'post.php' ) ) ){
			return;
		}

		// now check to see if the $post type is 'product'
		global $post;
		if ( ! isset( $post ) || 'product' != $post->post_type ){
			return;
		}

		// enqueue and localize
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'wc_catalog_product_metabox', WC_Catalog_Product()->plugin_url() . '/assets/js/wc-catalog-product-metabox'. $suffix . '.js', array( 'jquery' ), WC_Catalog_Product::VERSION, true );

		$i18n = array( 'document_icon' => includes_url( 'images/media/document.png' ),
						'delete_string' => __( 'Delete PDF', 'wc-catalog-product' ),
						'cover_uploader_title' => __( 'Add PDF Cover', 'wc-custom-catalog' ),
						'cover_uploader_button_text' => __( 'Set cover', 'wc-custom-catalog' ),
						'pdfs_uploader_title' => __( 'Add PDF to Collection', 'wc-custom-catalog' ),
						'pdfs_uploader_button_text' => __( 'Add to collection', 'wc-custom-catalog' )
		);

		wp_localize_script( 'wc_catalog_product_metabox', 'wc_catalog_product_metabox', $i18n );
		
		wp_enqueue_style( 'wc_catalog_product_metabox', WC_Catalog_Product()->plugin_url() . '/assets/css/wc-catalog-product-metabox.css', array(), WC_Catalog_Product::VERSION );

		add_action( 'wp_print_scripts', array( $this, 'admin_header' ) );

	}

	/**
	 * Add an icon to catalog product data tab
	 */
	public function admin_header() { ?>
		<style>
			#woocommerce-product-data ul.wc-tabs li.catalog_options_tab a:before { content: "\f330"; font-family: "Dashicons"; }
	    </style>
	    <?php
	}

}
