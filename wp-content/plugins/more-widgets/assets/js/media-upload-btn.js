( function( $ ) {

	"use strict";

	var $document = $( document );

	$document.ready( function() {

		function mw_media_select() {
			$( document ).on( 'click', '.more-widgets-upload-button', function( e ) {
				e.preventDefault();
				var button = $( this );
				var image = wp.media().open().on( 'select', function(e) {
                    var selected = image.state().get( 'selection' ).first();
                    button.prev().val( selected.toJSON().url ).trigger( 'change' );
                } );
			} );
		}

		mw_media_select();

		if ( wp.customize !== undefined ) {
			$document.on( 'widget-updated', mw_media_select );
			$document.on( 'widget-added', mw_media_select );
		}

	} );

} ) ( jQuery );