<?php
/**
 * WC Catalog Product template functions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wc_catalog_get_catalogs_for_order( $order ){
	
	if ( ! is_a( $order, 'WC_Order' ) ) {
		$order = wc_get_order( $order );
	}
	
	$catalogs = array();
	
	if ( is_a( $order, 'WC_Order' ) ) {
		
		foreach( $order->get_items() as $order_item_id => $order_item ){ error_log($order_item_id);
			$order_item_object = new WC_Order_Item_Product( $order_item_id );
			$merge_ids =  $order_item_object->get_meta( '_catalog_merge_ids', true );
			if( ! empty( $merge_ids ) ) {
				$catalogs[] = array( 'product_id' => $order_item_object->get_product_id(), 'merge_ids' => (array) $merge_ids );
			}
		}
	}
		
	return $catalogs;
}


