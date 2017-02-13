<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Catalog Product Class.
 *
 * Create custom catalogs from PDFs.
 *
 * @class 		WC_Product_Catalog
 * @category	Class
 * @author 		Kathy Darling
 */
class WC_Product_Catalog extends WC_Product_Simple {

	/**
	 * Supported features such as 'ajax_add_to_cart'.
	 *
	 * @var array
	 */
	protected $supports = array();

	/**
	 * Stores product data.
	 *
	 * @var array
	 */
	protected $extra_data = array(
		'cover_id'    => '',
		'pdf_ids' => array(),
	);

	/** @private array of PDF attachments. */
	private $pdfs;


	/*
	|--------------------------------------------------------------------------
	| Other Actions
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get internal type.
	 * @return string
	 */
	public function get_type() {
		return 'catalog';
	}

	/**
	 * Get the add to url used mainly in loops.
	 *
	 * @return string
	 */
	public function add_to_cart_url() {
		return apply_filters( 'woocommerce_product_add_to_cart_url', get_permalink( $this->id ), $this );
	}

	/**
	 * Get the add to cart button text.
	 *
	 * @return string
	 */
	public function add_to_cart_text() {
		return apply_filters( 'woocommerce_product_add_to_cart_text', __( 'Customize', 'wc-catalog-product' ), $this );
	}

	/**
	 * Get the add to cart button text for the single page.
	 *
	 * @access public
	 * @return string
	 */
	public function AAAsingle_add_to_cart_text() {
		return apply_filters( 'woocommerce_product_single_add_to_cart_text', __( 'Order Print Copy', 'wc-catalog-product' ), $this );
	}


	/**
	 * Gallery PDF array filter.
	 *
	 * @since 2.7.0
	 * @param array $image_ids
	 */
	public function attachment_is_pdf( $id ) {
		return wp_attachment_is( 'pdf', $id );		
	}

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	|
	| Methods for getting data from the product object.
	*/

	/**
	 * Get PDF cover ID.
	 *
	 * @since 2.7.0
	 * @param  string $context
	 * @return bool
	 */
	public function get_cover_id( $context = 'view' ) {
		return $this->get_prop( 'cover_id', $context );
	}

	/**
	 * Get PDF gallery IDs.
	 *
	 * @since 2.7.0
	 * @param  string $context
	 * @return bool
	 */
	public function get_pdf_ids( $context = 'view' ) {
		return $this->get_prop( 'pdf_ids', $context );
	}


	/**
	 * Return a product's PDFs.
	 *
	 * @return array PDF attachments.
	 */
	public function get_pdfs() {
		return apply_filters( 'wc_product_catalog_pdfs', $this->pdfs, $this );
	}

	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	|
	| Functions for setting product data. These should not update anything in the
	| database itself and should only change what is stored in the class
	| object.
	*/


	/**
	 * Set PDF gallery attachment ids.
	 *
	 * @since 2.7.0
	 * @param array $image_ids
	 */
	public function set_pdf_ids( $ids ) {
		$ids = wp_parse_id_list( $ids );

		if ( $this->get_object_read() ) {
			$ids = array_filter( $ids, array( $this, 'attachment_is_pdf' ) );
		}

		$this->set_prop( 'pdf_ids', $ids );
	}

	/**
	 * Set main cover pdf ID.
	 *
	 * @since 2.7.0
	 * @param int $image_id
	 */
	public function set_cover_id( $cover_id = '' ) {
		$cover_id = wp_attachment_is( 'pdf', $cover_id ) ? $cover_id : 0;
		$this->set_prop( 'cover_id', $cover_id );
	}


	/**
	 * Sets an array of PDF attachment objects for the product.
	 *
	 * @since 2.7.0
	 * @param array
	 */
	public function set_pdfs( $pdfs ) {
		$this->pdfs = array_filter( (array) $pdfs );
	}

	/*
	|--------------------------------------------------------------------------
	| Conditionals
	|--------------------------------------------------------------------------
	|
	*/

	/**
	 * Returns whether or not the product has any child product.
	 *
	 * @return bool
	 */
	public function has_pdfs() {
		return sizeof( $this->get_pdfs() ) ? true : false;
	}

}
