<?php
/**
 * Template
 * 
 * @author 		Kathy Darling
 * @package 	WC_Catalog_Product/Templates
 * @version     0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;

if( $product->is_purchasable() ){
	echo wc_get_stock_html( $product );
}

if ( $product->has_pdfs() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form method="post" enctype="multipart/form-data" class="wc-catalog-form">

		<input type="hidden" name="product_id" value="<?php echo $product->get_id();?>"/>

        <?php wp_nonce_field( 'wp_catalog_product_nonce_action', 'wp_catalog_product_nonce_field' ); ?>

		<table cellspacing="0" class="catalog_table group_table">
			<thead>
				<tr>
                    <th class="pdf-checkbox">&nbsp;</th>
                    <th class="pdf-title"><?php _e( 'Catalog Title', 'wc-catalog-product' );?></th>
                </tr>
			</thead>

			<tbody>

				<?php
					global $post;
					foreach ( $product->get_pdfs() as $post ) :  setup_postdata( $post );?>

						<tr>
                            <td><input type="checkbox" name="catalog_merge_ids[]" value="<?php the_ID();?>"  /></td>
                            <td><?php the_title();?></td>
                        </tr>

                     <?php 
                     endforeach; 
                     wp_reset_postdata();
                     ?>
			
			</tbody>

		</table>

        <button type="submit" name="wc_catalog_create" value="<?php echo esc_attr( $product->get_id() ); ?>" class="button wc_catalog_create"><?php _e( 'Download Combined Catalog', 'wc-catalog-product');?></button><div class="spinner"></div>

        <?php 
        if( $product->is_purchasable() ){ ?>

        	<div class="add_to_cart_wrap">

        	<?php

        	do_action( 'woocommerce_before_add_to_cart_button' ); 

        	if ( ! $product->is_sold_individually() ){
	 			woocommerce_quantity_input( array(
	 				'min_value' => apply_filters( 'woocommerce_quantity_input_min', 1, $product ),
	 				'max_value' => apply_filters( 'woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product )
	 			) );
	 		}
		 	
		 	?>
		 		
 			<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />

			<button type="submit" class="single_add_to_cart_button mnm_add_to_cart_button button alt"><?php echo $product->single_add_to_cart_text(); ?></button>
 		
 			</div>
 		<?php
 		
		do_action( 'woocommerce_after_add_to_cart_button' ); 

		} ?>

	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>
