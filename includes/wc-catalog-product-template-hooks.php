<?php
/**
 * WC Catalog Product template hooks
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wc_catalog_product_before_template', 'wc_catalog_product_before_template' );
add_action( 'wc_catalog_product_after_template', 'wc_catalog_product_after_template' );