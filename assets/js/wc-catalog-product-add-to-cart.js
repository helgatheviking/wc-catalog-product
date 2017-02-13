;(function($){

	$(".wc_catalog_create").on("click keyup", function (e) {
	    if (e.type == "click" || e.keyCode == 13) {
	        e.preventDefault();

			var $form = $(this).closest('.wc-catalog-form');
			var $table = $form.find('.catalog_table');
			
			// get data to send
			var nonce = $form.find('input[name="wp_catalog_product_nonce_field"]').val();
			var product_id = $form.find('input[name="product_id"]').val();
			var pdf_ids = $form.find('input[type="checkbox"][name="catalog_merge_ids\\[\\]"]:checked').map(function() { return this.value; }).get();

			var $button = $form.find('button[type="submit"]');
			var $spinner = $button.next('.spinner');

			$.ajax({
				url: wc_catalog_product_params.ajaxurl,
				type: 'POST',
				data: {
					'action': 'wc_create_catalog', 
					'product_id' : product_id, 
					'catalog_merge_ids' : pdf_ids, 
					'nonce' : nonce
				},
				beforeSend: function( xhr ) {
					$spinner.css('display', 'inline-block');
					$button.prop('disabled',true);
					$form.addClass( 'processing' ).block();
				},
				success: function( response ) {
				
					// remove any old notices
					$('.woocommerce-error').remove();
						
					var notice = [];

					var response = jQuery.parseJSON( response );

					if ( response && response.status == 1 ) {

						// replace the link URL in success message
	                    url = '?custom_catalog=' + response.file_name;

						// create a hidden iframe that should download the file
						$('<iframe />', {
						    id:  url,
						    src: url,
						}).appendTo('body');

					} 

					// display all the notices
					if( 'undefined' != response.notices ){
						$(response.notices).each( function( i, notice ){
							// create a div and add appropriate class
							$message = $('<div />', {
							    class:  'pdfmerger-notice',
							}).insertAfter($table).addClass( notice.type ).html( notice.message ).fadeIn();
						});
					}

						
				},
			})
			.done(function() {
			    $spinner.css('display', 'none');
			    $form.removeClass( 'processing' ).unblock();
			    $button.prop('disabled',false);
			});
	    }
	});

})(jQuery); //end