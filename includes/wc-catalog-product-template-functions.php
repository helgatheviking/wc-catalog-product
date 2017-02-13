<?php
/**
 * WC Catalog Product template functions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wc_catalog_product_before_template(){
	echo '<p>' . __( 'Before Template', 'wc-catalog-product' ) . '</p>';
}

function wc_catalog_product_after_template(){
	echo '<p>' . __( 'After Template', 'wc-catalog-product' ) . '</p>';
}