jQuery( document ).ready( function( $ ) {
    'use strict';

	if ( $( '.wp_event_calendar_datepicker' ).length > 0 ) {
		$( '.wp_event_calendar_datepicker' ).each( function() {
			var picker = $( this );
			var alternate = $( picker.data( 'alt-field' ) );

			picker.datepicker( {
				dateFormat: picker.data( 'date-format' ),
				onSelect: function() {
					var date = picker.datepicker( 'getDate' );

					alternate.val( date ? $.datepicker.formatDate( 'yy-mm-dd', date ) : '' );
				},
				shortYearCutoff: 69
			} );

			picker.on( 'change', function() {
				var date = null;

				try {
					date = $.datepicker.parseDate( picker.data( 'date-format' ), picker.val(), {
						shortYearCutoff: 69
					} );
				} catch ( error ) {
					// Let the server validate manually entered dates.
				}

				alternate.val( date ? $.datepicker.formatDate( 'yy-mm-dd', date ) : '' );
			} );
		} );
	}

	$( '#wp_event_calendar_all_day' ).click( function( e ) {
		var checked = $( this ).prop( 'checked' );

		if ( true === checked ) {
			$( '.event-time' ).hide();
		} else {
			$( '.event-time' ).show();
		}
	} );

	$( '#wp_event_calendar_repeat' ).on( 'change', function() {
		$( '.wp_event_calendar_expiration' ).toggle( '0' !== $( this ).val() );
	} ).trigger( 'change' );

	if ( $( '.ct_rental_datepicker' ).length > 0 ) {
		$( '.ct_rental_datepicker' ).datepicker( {
			dateFormat: 'mm/dd/yy'
		} );
	}
} );
