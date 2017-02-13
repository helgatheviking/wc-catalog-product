<?php
/**
 * Plugin Name: WC Catalog Product
 * Plugin URI:  https://github.com/helgatheviking/wc-catalog-product
 * Description: Build your own catalog from a collection of PDFs
 * Version:     0.1.0
 * Author:      Kathy Darling
 * Author URI:  http://www.kathyisawesome.com
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wc_catalog_product
 * Domain Path: /languages
 * Requires at least: 3.8.0
 * Tested up to: 4.4.0
 * WC requires at least: 2.4.0
 * WC tested up to: 2.5.0   
 */

/**
 * Copyright: © 2017 Kathy Darling.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */


/**
 * The Main WC_Catalog_Product class.
 **/
if ( ! class_exists( 'WC_Catalog_Product' ) ) :

class WC_Catalog_Product {

	const VERSION = '0.1.0';
	const PREFIX  = 'WC_Catalog_Product';
	const REQUIRED_WC = '2.7.0';

	/**
	 * @var WC_Catalog_Product - the single instance of the class
	 * @since 0.1.0
	 */
	protected static $_instance = null;            

	/**
	 * Main WC_Catalog_Product Instance
	 *
	 * Ensures only one instance of WC_Catalog_Product is loaded or can be loaded.
	 *
	 * @static
	 * @see WC_Catalog_Product()
	 * @return WC_Catalog_Product - Main instance
	 * @since 0.1.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}



	/**
	 * Constructor.
	 */
	public function __construct(){

		register_activation_hook( __FILE__, array( __CLASS__, 'install' ) );

		$this->includes();

		// Load translation files
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		// Include required files
		add_action( 'after_setup_theme', array( $this, 'template_includes' ) );

		// delete the temp file via cron job
        add_action( 'wc_catalog_product_delete_temporary_file', array( $this, 'delete_temporary_file' ), 10, 1 );

        // Delete PDF transient.
		add_action( 'woocommerce_delete_product_transients', array( $this, 'delete_pdf_query' ) );
	}



	/*-----------------------------------------------------------------------------------*/
	/*  Load Files                                                                       */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @return void
	 */
	public function includes(){

		// Check we're running the required version of WC.
		if ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, self::REQUIRED_WC, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice' ) );
			return false;
		}

		// Require all the files.
		require_once( 'includes/class-wc-catalog-processor.php' );
		require_once( 'includes/admin/class-wc-catalog-product-metabox.php' );
		require_once( 'includes/data/class-wc-catalog-product-data.php' );
		require_once( 'includes/email/class-wc-catalog-email.php' );
		require_once( 'includes/class-wc-catalog-product-display.php' );
		require_once( 'includes/class-wc-catalog-product-cart.php' );
		require_once( 'includes/class-wc-product-catalog.php' );
		require_once( 'includes/class-wc-catalog-product-order.php' );
		
		// Include admin class to handle all back-end functions.
		if( is_admin() ){
			update_option( 'wc_catalog_product_version', self::VERSION );
			$this->admin = new WC_Catalog_Product_Metabox();
		}

		// Launch sub classes.
		$this->display = new WC_Catalog_Product_Display();
		$this->cart = new WC_Catalog_Product_Cart();
		$this->order = new WC_Catalog_Product_Order();
		$this->email = new WC_Catalog_Email();
		$this->processor = new WC_Catalog_Proccesor();

		do_action( 'wc_catalog_product_loaded' );
	}

	/**
	 * Include frontend functions and hooks
	 *
	 * @return void
	 * @since  0.1.0
	 */
	public static function template_includes(){
		require_once( 'includes/wc-catalog-product-template-functions.php' );
		require_once( 'includes/wc-catalog-product-template-hooks.php' );
	}


	/**
	 * Displays a warning message if version check fails.
	 * @return string
	 * @since  0.1.0
	 */
	public function admin_notice() {
		echo '<div class="error"><p>' . sprintf( __( 'WC Catalog Product requires at least WooCommerce %s in order to function. Please upgrade WooCommerce.', 'woocommerce-mix-and-match-products', 'wc-catalog-product' ), self::REQUIRED_WC ) . '</p></div>';
	}


	/*-----------------------------------------------------------------------------------*/
	/* Localization */
	/*-----------------------------------------------------------------------------------*/


	/**
	 * Make the plugin translation ready
	 *
	 * @return void
	 * @since  0.1.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wc-catalog-product' , false , dirname( plugin_basename( __FILE__ ) ) .  '/languages/' );
	}


	/*-----------------------------------------------------------------------------------*/
	/*  Helper Functions                                                                 */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}


	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}


	/**
     * Delete the stored PDF.
     * @return string
     */
     public function delete_temporary_file( $file_path ){
        unlink( $file_path );
    }


    /**
     * Delete the PDF transient when WC deletes it's product transients. 
     * @return string
     */
     public function delete_pdf_query( $post_id ){
		delete_transient( 'wc_catalog_pdf_' . $post_id );
	}

	/**
	 * Create files/directories.
	 */
	private static function install() {
		// Install files and folders for uploading files and prevent hotlinking
		$upload_dir      = wp_upload_dir();
		$download_method = get_option( 'woocommerce_file_download_method', 'force' );

		$files = array(
			array(
				'base' 		=> $upload_dir['basedir'] . '/wc_catalogs',
				'file' 		=> 'index.html',
				'content' 	=> '',
			),
			array(
				'base' 		=> $upload_dir['basedir'] . '/wc_catalogs',
				'file' 		=> '.htaccess',
				'content' 	=> 'deny from all',
			)

		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}
	}

} //end class: do not remove or there will be no more guacamole for you

endif; // end class_exists check


/**
 * Returns the main instance of WC_Catalog_Product to prevent the need to use globals.
 *
 * @since  0.1.0
 * @return WC_Catalog_Product
 */
function WC_Catalog_Product() {
	return WC_Catalog_Product::instance();
}

// Launch the whole plugin
add_action( 'woocommerce_loaded', 'WC_Catalog_Product' );