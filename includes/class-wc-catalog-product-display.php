<?php
/**
 * Functions related to front-end display
 *
 * @class 	WC_Catalog_Product_Display
 * @version 0.1.0
 * @since   0.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Catalog_Product_Display {

	/**
	 * Pseudo constructor function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Single Product Display
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 20 );

		// Single product template.
		add_action( 'woocommerce_catalog_add_to_cart', array( $this, 'add_to_cart_template' ) );

	}



	/*-----------------------------------------------------------------------------------*/
	/* Single Product Display Functions */
	/*-----------------------------------------------------------------------------------*/


	/**
	 * Register the script
	 *
	 * @return void
	 */
	public function register_scripts() {

		wp_enqueue_style( 'wc-catalog-product-frontend', WC_Catalog_Product()->plugin_url() . '/assets/css/wc-catalog-product-frontend.css', false, WC_Catalog_Product::VERSION );

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_script( 'wc-catalog-product-add-to-cart', WC_Catalog_Product()->plugin_url() . '/assets/js/wc-catalog-product-add-to-cart'. $suffix . '.js', array( 'jquery', 'jquery-blockui' ), WC_Catalog_Product::VERSION, true );

		$path = wp_upload_dir();
        $i18n = array( 
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'url' => $path['baseurl'],
        );

		wp_localize_script( 'wc-catalog-product-add-to-cart', 'wc_catalog_product_params', $i18n );
	}

    /**
     * Enqueue frontend scripts
     * @since  1.0.0
     */ 
    public function frontend_scripts(){
        wp_enqueue_script( 'jquery-blockui' );
        wp_enqueue_script( 'wc-catalog-product-add-to-cart' );
    }

	/**
	 * Add-to-cart template for mix & match products.
	 * @return void
	 */
	public function add_to_cart_template() {

		global $product;

		// Enqueue scripts and styles.
		$this->frontend_scripts();

		// Load the add to cart template.
		wc_get_template(
			'single-product/add-to-cart/catalog.php',
			array(
				'cover_id'  => $product->get_cover_id(),
				'pdf_ids'  => $product->get_pdf_ids(),
			),
			'',
			WC_Catalog_Product()->plugin_path() . '/templates/'
		);

	}

} //end class