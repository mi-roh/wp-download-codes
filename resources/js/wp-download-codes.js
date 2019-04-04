/**
 * WP Download Codes Plugin
 *
 * FILE
 * resources/wp-download-codes.js
 *
 * DESCRIPTION
 * Contains JS functions for plugin
 *
 */

/* globals: jQuery */

( function( $ ) {

	var $lightbox, $overlay, $close, $content, $textarea;

	$( function() {
		// Add lightbox DOM elements
		$lightbox = $( '<div id="dc_lightbox">' )
			.appendTo( 'body' );
		$overlay = $( '<div id="dc_overlay">' )
			.appendTo( 'body' )
			.on( 'click', closeLightbox );
		$close = $( '<div class="close"></div>' )
			.appendTo( $lightbox )
			.on( 'click', closeLightbox );
		$content = $( '<div id="dc_lightbox-content" class="content">' )
			.appendTo( $lightbox );
		$textarea = $( '<textarea id="dc_lightbox-textarea"></textarea>' )
			.appendTo( $lightbox );

		// Admin Page: Manage Codes

		var $tableRoot = $( 'table.dc_codes' );

		// Open lightbox to list download codes
		$( 'a.action-list', $tableRoot ).on( 'click', function() {
			return openLightbox( $( this ).attr( "rel" ), true );
		} );

		// Open lightbox to list downloads
		$( 'a.action-report', $tableRoot ).on( 'click', function() {
			return openLightbox( $( this ).attr( "rel" ) );
		} );

		// Add confirm step before deleting release
		$( 'a.action-delete', $tableRoot ).on( 'click', function() {
			return confirm( "Are you absolutely sure? This cannot be undone!" );
		} );

		// Add confirm step before finalizing codes
		$( 'a.action-finalize', $tableRoot ).on( 'click', function() {
			return confirm( "Are you absolutely sure? Codes cannot be deleted after they're finalized. (Only the whole release can be deleted including all codes.)" );
		} );

		// Add confirm step if more than 500 download codes shall be created
		$( "form#form-manage-codes" ).on( 'submit', function() {
			// Get number of download codes to be created
			var numberOfCodes = $( '#new-quantity', this ).val();

			// Check if number of codes exceeds 500
			if ( $.isNumeric( numberOfCodes ) && numberOfCodes >= 500 ) {
				return confirm( "Are you sure that you want to create that many codes?" );
			}

			return true;
		} );

		// Admin Page: Manage Releases

		// Add handlers for hosting types
		$( "#hosting_type" ).on( 'change', function() {
			changeHostingType( $( this ).val() );
		} ).trigger( 'change' );
	} );

	/***********************
	 // open lightbox
	 */
	function openLightbox( lightboxId, selectable ) {

		var $reference = $( '#' + lightboxId );

		if ( $reference.length <= 0 ) {
			return true;
		}

		$overlay.show();
		$lightbox.show();
		if ( !! selectable ) {

			// Show Textarea / Hide Content
			$textarea
				.show()
				.html( $reference.text() );
			$content.hide();
		} else {

			// Show Content / Hide Textarea
			$content
				.show()
				.append( $reference.clone().show() );
			$textarea.hide();

			// Enable select/deselect all in download reports
			$( 'input.cb-select-all', $content ).on( 'click', function() {
				var relatedId = $( this ).attr( "rel" );
				$( 'input.cb-related-' + relatedId, $content ).prop( 'checked', $( this ).prop( 'checked' ) );
			} );

		}

		return false;
	}

	/***********************
	 // close lightbox
	 */
	function closeLightbox() {
		$overlay.hide();
		$lightbox.hide();
		return false;
	}

	function changeHostingType( hostingType ) {

		$( "#manage-releases-hosting-type-wp-direct" ).toggle( hostingType === 'WP_DIRECT' );
		$( "#manage-releases-hosting-type-redirect-url-open" ).toggle( hostingType === 'REDIRECT_URL_OPEN' );

	}

} )( jQuery.noConflict() );
