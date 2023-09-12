( function( $ ) {
	'use strict';

	var $doc = $( document );

	$doc.ready( function( $ ) {

		// Add items
		$( 'body' ).on( 'click', '.mw-settings-form .mw-rpf-add', function( event ) {
			event.preventDefault();

			var widgetForm = $( this ).closest( '.mw-settings-form' ),
				cloneEl    = widgetForm.find( '.mw-rpf-clone' ),
				cloneHTML  = $( '<li>' + cloneEl.html() + '</li>' );

			widgetForm.find( '.mw-repeater-field' ).append( cloneHTML );

			cloneHTML.find( 'p' ).eq(0).find( 'input' ).eq(0).focus();

		} );

		// Delete items
		$( 'body' ).on( 'click', '.mw-settings-form .mw-rpf-remove', function( event ) {
			event.preventDefault();
			if ( confirm( moreWidgets.repeaterConfirm ) ) {
				$( this ).parent().find( 'input[type="text"]' ).trigger( 'change' );
				$( this ).closest( 'li' ).remove();
			}
		} );

		// Sort items
		function sortServices() {
			$( '#widgets-right .mw-settings-form .mw-repeater-field, .customize-control .mw-settings-form .mw-repeater-field' ).each( function() {
				var id = $( this ).attr( 'id' ),
					$el = $( '#' + id );
				$el.sortable( {
					revert      : false,
					delay       : 100,
					cursor      : 'move',
					placeholder : 'mw-rpf-placeholder',
					opacity     : 0.8,
					start: function( e, ui ) {
						ui.placeholder.height( ui.item.height() );
					},
					update      : function( event, ui ) {
						$el.find( 'input' ).eq(0).trigger( 'change' );
					}
				} );
			} );
		}

		sortServices();

		// Re-run sorting as needed
		$doc.on( 'widget-updated', sortServices );
		$doc.on( 'widget-added', sortServices );

	} );

} ) ( jQuery );