<?php
/**
 * WC Catalog Product order functions and filters.
 *
 * @class 	WC_Catalog_Product_Order
 * @version 0.1.0
 * @since   0.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Catalog_Product_Order {

	/**
	 * Setup order class
	 */
	public function __construct() {

		// Save file URL to order item.
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_order_item_meta' ), 10, 3 );

	}


	/**
	 * Add bundle info meta to order items.
	 *
	* @param  WC_Order_Item_Product   $item
	* @param  str   $cart_item_key
	* @param  array    $values   cart item data
	* @return void
	 */
	public function add_order_item_meta( $item, $cart_item_key, $values ) {

		// add data to the product
		if ( isset( $values[ 'catalog_merge_ids' ] ) ) {
			$item->add_meta_data( '_catalog_merge_ids', $values[ 'catalog_merge_ids' ], true );
		}
	}

}
