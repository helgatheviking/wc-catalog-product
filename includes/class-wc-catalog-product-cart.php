<?php
/**
 * WC Catalog Product cart functions and filters.
 *
 * @class 	WC_Catalog_Product_Cart
 * @version 0.1.0
 * @since   0.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Catalog_Product_Cart {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Functions for cart actions - ensure they have a priority before addons (10)
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data'), 5, 3 );
        add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session'), 5, 2 );

        // Validation.
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_cart_item' ), 10, 6 );

		// Put back cart item data to allow re-ordering of container.
		add_filter( 'woocommerce_order_again_cart_item_data', array( $this, 'order_again' ), 10, 3 );

	}

	/*-----------------------------------------------------------------------------------*/
	/* Cart Filters */
	/*-----------------------------------------------------------------------------------*/


	/*
	 * Add cart session data
	 *
	 * @param  array  $cart_item_data
	 * @param  int 	  $product_id
	 * @param  int 	  $variation
	 * @return void
	 * @since 0.1.0
	 */
    public function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
        if( isset( $_REQUEST[ 'catalog_merge_ids' ] ) ) {
            $cart_item_data['catalog_merge_ids'] = ( array ) $_REQUEST[ 'catalog_merge_ids' ];
        }
        return $cart_item_data;
    }


	/*
	 * Preserve cart session data
	 *	 
	 * @param  array 	$cart_item
	 * @param  array 	$values
	 * @return void
	 * @since 0.1.0
	 */
    public function get_cart_item_from_session( $cart_item, $values ) {         
        if ( isset( $values[ 'catalog_merge_ids' ] ) ) {
            $cart_item[ 'catalog_merge_ids' ] = (array) $values[ 'catalog_merge_ids' ];
        }
        return $cart_item;
    }


	/*
	 * Validate before adding to cart
	 * @since 0.1.0
	 */
	public function validate_add_cart_item( $passed, $product_id, $product_quantity, $variation_id = '', $variations = array(), $cart_item_data = array() ) {

		// The container product.
		$product = wc_get_product( $product_id );

		// Ignore add to cart if "Download" button is pressed
		if ( isset( $_REQUEST[ 'wc_catalog_create' ] ) ) { 
			return false;
		}

		// Make sure we have some PDFs.
		if ( $product->is_type( 'catalog' ) && empty ( $_REQUEST[ 'catalog_merge_ids' ] ) ) {
			$passed = false;
			wc_add_notice( __( 'Please select some PDFs with which to create your catalog.', 'wc-catalog-product' ), 'error' );
		}

		return $passed;
	}


	/**
	 * Reinitialize cart item data for re-ordering purchased orders.
	 * @param  mixed 		$cart_item_data
	 * @param  mixed 		$order_item
	 * @param  WC_Order 	$order
	 * @return mixed
	 */
	public function order_again( $cart_item_data, $order_item, $order ) {

		// Add data to product.
		if ( isset( $order_item[ 'catalog_config' ] ) ) {
			$cart_item_data[ 'catalog_config' ]   = maybe_unserialize( $order_item[ 'catalog_config' ] );
		}

		return $cart_item_data;
	}

} //end class
