<?php
/**
 * WC_Catalog_Data class
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MnM Data class.
 *
 * MnM Data filters and includes.
 *
 * @class    WC_Catalog_Data
 * @version  1.2.0
 */
class WC_Catalog_Data {

	public static function init() {

		// Product Bundle CPT data store.
		require_once( 'class-wc-product-catalog-data-store-cpt.php' );

		// Register the Catalog Custom Post Type data store.
		add_filter( 'woocommerce_data_stores', array( __CLASS__, 'register_mnm_type_data_store' ), 10 );
	}

	/**
	 * Registers the Product Bundle Custom Post Type data store.
	 *
	 * @param  array  $stores
	 * @return array
	 */
	public static function register_mnm_type_data_store( $stores ) {

		$stores[ 'product-catalog' ] = 'WC_Product_Catalog_Data_Store_CPT';

		return $stores;
	}
}

WC_Catalog_Data::init();