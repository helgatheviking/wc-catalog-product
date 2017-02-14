<?php
/**
 * WC_Catalog_Email class
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
 * @class    WC_Catalog_Email
 * @version  1.2.0
 */
class WC_Catalog_Email {

	public function __construct() {

		// Register the custom email.
		add_filter( 'woocommerce_email_classes', array( $this, 'register_email' ) );

		// Register the email for resending action.
		add_filter( 'woocommerce_resend_order_emails_available', array( $this, 'resend_email' ) );
		
	}

	/**
	 * Registers the custom email with WooCommerce.
	 *
	 * @param  array  $emails
	 * @return array
	 */
	public function register_email( $emails ) {
		$emails['WC_Email_New_Catalog_Order'] = include( 'class-wc-email-new-catalog-order.php' );
		return $emails;
	}

	/**
	 * Registers the custom email with WooCommerce order actions.
	 *
	 * @param  array  $emails
	 * @return array
	 */
	public function resend_email( $emails ) {
		global $post;
		$order_id = $post->ID;

		if ( ! empty( wc_catalog_get_catalogs_for_order( $order_id ) ) ) {
			$emails[] = 'new_catalog_order';
		}
		
		return $emails;
	}
}