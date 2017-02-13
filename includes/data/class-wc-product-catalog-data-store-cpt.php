<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC Product Data Store: Stored in CPT.
 *
 * @version  2.7.0
 * @category Class
 * @author   WooThemes
 */
class WC_Product_Catalog_Data_Store_CPT extends WC_Product_Data_Store_CPT implements WC_Object_Data_Store_Interface {

	/**
	 * Data stored in meta keys, but not considered "meta" for the Bundle type.
	 * @var array
	 */
	protected $internal_meta_keys = array(
		'_cover_id',
		'_pdf_ids',
	);


	/*
	|--------------------------------------------------------------------------
	| Additional Methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Read extra data associated with the product, like button text or product URL for external products.
	 *
	 * @param WC_Product
	 * @since 2.7.0
	 */
	protected function read_extra_data( &$product ) {
		foreach ( $product->get_extra_data_keys() as $key ) {
			$function = 'set_' . $key;
			if ( is_callable( array( $product, $function ) ) ) {
				$meta_key = isset( $this->props_to_meta[$key] ) ? $this->props_to_meta[$key] : '_' . $key;
				$product->{$function}( get_post_meta( $product->get_id(), $meta_key, true ) );
			}
		}
	}


	/**
	 * Read product data.
	 *
	 * @since 2.7.0
	 */
	protected function read_product_data( &$product ) {
		parent::read_product_data( $product );
		$pdfs = $this->read_pdfs( $product );
		$product->set_pdfs( $pdfs );
	}

	/**
	 * Loads variation child IDs.
	 *
	 * @param  WC_Product
	 * @param  bool $force_read True to bypass the transient.
	 * @return array
	 */
	protected function read_pdfs( &$product, $force_read = false ) {
		$pdf_transient_name = 'wc_catalog_pdf_' . $product->get_id();
		$pdfs                = get_transient( $pdf_transient_name );

		if ( empty( $pdfs ) || ! is_array( $pdfs ) || $force_read ) {
			$args = array(
                    'post_type' => 'attachment',
                    'post_status' => 'inherit',
                    'posts_per_page' => -1,
                    'post_mime_type' => 'application/pdf',
                    'post__in' => $product->get_pdf_ids(),
                    'orderby'=>'post__in'
                );

			$pdfs = get_posts( apply_filters( 'wc_catalog_children_pdf_args', $args, $product, false ) );

			set_transient( $pdf_transient_name, $pdfs, DAY_IN_SECONDS * 30 );
		}

		return $pdfs;
	}
	
}
