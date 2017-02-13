;(function($){

	// Simple type options are valid for mnm.
	$( '.show_if_simple:not(.hide_if_catalog)' ).addClass( 'show_if_catalog' );

	// Mix and Match type specific options
	$( 'body' ).on( 'woocommerce-product-type-change', function( event, select_val, select ) {

		if ( select_val === 'catalog' ) {
			$( '.show_if_external' ).hide();
			$( '.show_if_catalog' ).show();
			$( 'input#_manage_stock' ).change();
		}

	} );

	$( 'select#product-type' ).change();

	var file_frame;

	/*
	 * PDF cover : Single PDF
	 */ 
	$('.add_pdf_cover').on('click', function( event ){
		
		var $button = $( this );
		var $input = $button.prev( 'input' );
		var $table    = $button.closest('.pdf_cover_container' ).find( 'table.pdf_catalog' );

	    event.preventDefault();

	    // If the media frame already exists, reopen it.
	    if ( file_frame ) {
			file_frame.open();
			return;
	    }

	    // Create the media frame.
	    file_frame = wp.media.frames.file_frame = wp.media({
			title: wc_catalog_product_metabox.cover_uploader_title,
			button: {
				text: wc_catalog_product_metabox.cover_uploader_button_text,
			},
			library : { type : 'application/pdf'},
			multiple: false  // Set to true to allow multiple files to be selected
	    });

	    //file_frame.uploader.options.uploader['params']['allowed_type'] = 'pdf';
	    //console.log(file_frame.uploader);

		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {

			var selection = file_frame.state().get('selection');

		    selection.map( function( attachment ) {

				attachment = attachment.toJSON();

				if ( attachment.id ) {

					var attachment_image = wc_catalog_product_metabox.document_icon;

					$table.empty().append( '<tr class="pdf-row" data-attachment_id="' + attachment.id + '"><td><img src="' + attachment_image + '" /></td><td>' + attachment.title + '</td><td><a href="#" class="delete-pdf" title="' + $button.data('delete') + '">' + $button.data('text') + '</a></td></tr>' );
					
					$input.val( attachment.id );

				}

		    });

		});

	    // Finally, open the modal
	    file_frame.open();
 	
 	});

	// Remove PDF
	$( '.pdf_cover_container' ).on( 'click', 'a.delete-pdf', function() { 
		$button = $( this );
		$input = $button.closest('table').next('input');
		$button.closest( 'tr' ).remove();
		$input.val( 0 );
		return false;
	});


	/*
	 * PDF gallery file uploads : Multiple PDFs
	 */
	var pdf_gallery_frame;

	$( '.add_pdf' ).on( 'click', function( event ){

		var $button = $( this );
		var $input = $button.prev( 'input' );
		var $table = $button.closest( '.pdf_catalog_container' ).find( '.pdf_catalog' );

		event.preventDefault();

		// If the media frame already exists, reopen it.
		if ( pdf_gallery_frame ) {
			pdf_gallery_frame.open();
			return;
	    }

	    // Create the media frame.
	    pdf_gallery_frame = wp.media.frames.pdf_gallery_frame = wp.media({
			title: wc_catalog_product_metabox.pdfs_uploader_title,
			button: {
				text: wc_catalog_product_metabox.pdfs_uploader_button_text,
			},
			library : { type : 'application/pdf' },
			multiple: true  // Set to true to allow multiple files to be selected
	    });

	    //pdf_gallery_frame.uploader.options.uploader['params']['allowed_type'] = 'pdf';
	    //console.log(pdf_gallery_frame.uploader);

		// When an image is selected, run a callback.
		pdf_gallery_frame.on( 'select', function() {

		    var selection = pdf_gallery_frame.state().get( 'selection' );
			var attachment_ids = $input.val();

			selection.map( function( attachment ) {
				attachment = attachment.toJSON();

				if ( attachment.id ) {

					attachment_ids   = attachment_ids ? attachment_ids + ',' + attachment.id : attachment.id;
					var attachment_image = wc_catalog_product_metabox.document_icon;

					$table.append( '<tr class="pdf-row ui-sortable-handle" data-attachment_id="' + attachment.id + '"><td><img src="' + attachment_image + '" /></td><td>' + attachment.title + '</td><td><a href="#" class="delete-pdf" title="' + wc_catalog_product_metabox.delete_string + '">' + wc_catalog_product_metabox.delete_string + '</a></td></tr>' );
				}
			});

			$input.val( attachment_ids );

		});

	    // Finally, open the modal
	    pdf_gallery_frame.open();

	});


	// Image ordering
	$( '.pdf_catalog_sortable' ).sortable({
		items: '.pdf-row',
		cursor: 'move',
		scrollSensitivity: 40,
		forcePlaceholderSize: true,
		forceHelperSize: false,
		helper: 'clone',
		opacity: 0.65,
		placeholder: 'pdf-metabox-sortable-placeholder',
		start: function( event, ui ) {
			ui.item.css( 'background-color', '#f6f6f6' );
		},
		stop: function( event, ui ) { 
			ui.item.removeAttr( 'style' ); // why isn't this removing the style?
		},
		update: function( event, ui ) {
			var pdf_ids = '';

			$table = ui.item.closest( 'table' );

			$table.find( 'tr' ).css( 'cursor', 'default' ).each( function() {
				var attachment_id = jQuery( this ).attr( 'data-attachment_id' );
				pdf_ids = pdf_ids + attachment_id + ',';
			});

			$table.next( 'input' ).val( pdf_ids );
		}
	});

	// Remove PDF
	$( '.pdf_catalog_container' ).on( 'click', 'a.delete-pdf', function() { 
		
		$button = $( this );
		$input = $button.closest('table').next('input');

		$button.closest( 'tr.pdf' ).remove();

		var attachment_ids = '';

		$( '.pdf_catalog_container' ).find( '.pdf_catalog tr.pdf' ).css( 'cursor', 'default' ).each( function() {
			var attachment_id = jQuery( this ).attr( 'data-attachment_id' );
			attachment_ids = attachment_ids + attachment_id + ',';
		});

		$input.val( attachment_ids );

		return false;
	});

})(jQuery); //end