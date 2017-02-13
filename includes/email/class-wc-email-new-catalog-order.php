<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Email_New_Catalog_Order' ) ) :

/**
 * New Order Email.
 *
 * An email sent to the admin when a new order is received/paid for.
 *
 * @class       WC_Email_New_Catalog_Order
 * @version     2.0.0
 * @package     WooCommerce/Classes/Emails
 * @author      WooThemes
 * @extends     WC_Email
 */
class WC_Email_New_Catalog_Order extends WC_Email {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id               = 'new_catalog_order';
		$this->title            = __( 'New catalog order', 'wc-catalog-product' );
		$this->description      = __( 'New catalog order emails are sent to chosen recipient(s) when a new order with a custom catalog is received.', 'wc-catalog-product' );
		$this->heading          = __( 'New catalog order', 'wc-catalog-product' );
		$this->subject          = __( '[{site_title}] New catalog order ({order_number}) - {order_date}', 'wc-catalog-product' );
		$this->template_base  = WC_Catalog_Product()->plugin_path() . '/templates/';

		$this->template_html    = 'emails/admin-new-catalog-order.php';
		$this->template_plain   = 'emails/plain/admin-new-catalog-order.php';

		// Triggers for this email
		add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'trigger' ), 10, 2 );
		add_action( 'woocommerce_order_status_pending_to_completed_notification', array( $this, 'trigger' ), 10, 2 );
		add_action( 'woocommerce_order_status_failed_to_processing_notification', array( $this, 'trigger' ), 10, 2 );
		add_action( 'woocommerce_order_status_failed_to_completed_notification', array( $this, 'trigger' ), 10, 2 );

		// Call parent constructor
		parent::__construct();

		// Other settings
		$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param int $order_id The order ID.
	 * @param WC_Order $order Order object.
	 */
	public function trigger( $order_id, $order = false ) {
		if ( $order_id && ! is_a( $order, 'WC_Order' ) ){
			$order = wc_get_order( $order_id );
		}

		if ( is_a( $order, 'WC_Order' ) ) {
			$this->object                  = $order;
			$this->find['order-date']      = '{order_date}';
			$this->find['order-number']    = '{order_number}';
			$this->replace['order-date']   = date_i18n( wc_date_format(), $this->object->get_date_created() );
			$this->replace['order-number'] = $this->object->get_order_number();
		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * Get content html.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => false,
			'email'			=> $this,
		),
		'',
		$this->template_base );
	}

	/**
	 * Get content plain.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => true,
			'email'			=> $this,
		),
		'',
		$this->template_base );
	}

    /**
     * get_attachments function.
     *
     * @since 0.1
     * @return string
     */
    public function get_attachments() { 

    	$file = array();

		foreach( $this->object->get_items() as $order_item_id => $order_item ){
			$order_item_object = new WC_Order_Item_Product( $order_item_id );
			$merge_ids = $order_item_object->get_meta( '_catalog_merge_ids', true );
			if( $merge_ids && $file_name = WC_Catalog_Product()->processor->create_pdf( $merge_ids, $order_item_object->get_product_id(), 'email' ) ) {
				$file_path = WC_Catalog_Product()->processor->get_path( $file_name );
				$file[] =  $file_path;
			}
		}

        return apply_filters( 'woocommerce_email_attachments', $file, $this->id, $this->object );
    }

	/**
	 * Initialise settings form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'         => __( 'Enable/Disable', 'wc-catalog-product' ),
				'type'          => 'checkbox',
				'label'         => __( 'Enable this email notification', 'wc-catalog-product' ),
				'default'       => 'yes',
			),
			'recipient' => array(
				'title'         => __( 'Recipient(s)', 'wc-catalog-product' ),
				'type'          => 'text',
				'description'   => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'wc-catalog-product' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
				'placeholder'   => '',
				'default'       => '',
				'desc_tip'      => true,
			),
			'subject' => array(
				'title'         => __( 'Subject', 'wc-catalog-product' ),
				'type'          => 'text',
				'description'   => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: %s.', 'wc-catalog-product' ), '<code>' . $this->subject . '</code>' ),
				'placeholder'   => '',
				'default'       => '',
				'desc_tip'      => true,
			),
			'heading' => array(
				'title'         => __( 'Email heading', 'wc-catalog-product' ),
				'type'          => 'text',
				'description'   => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: %s.', 'wc-catalog-product' ), '<code>' . $this->heading . '</code>' ),
				'placeholder'   => '',
				'default'       => '',
				'desc_tip'      => true,
			),
			'email_type' => array(
				'title'         => __( 'Email type', 'wc-catalog-product' ),
				'type'          => 'select',
				'description'   => __( 'Choose which format of email to send.', 'wc-catalog-product' ),
				'default'       => 'html',
				'class'         => 'email_type wc-enhanced-select',
				'options'       => $this->get_email_type_options(),
				'desc_tip'      => true,
			),
		);
	}
}

endif;

return new WC_Email_New_Catalog_Order();
