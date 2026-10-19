jQuery( document ).ready( function( $ ) {
    'use strict';

	if ( $( '.wp_event_calendar_datepicker' ).length > 0 ) {
		$( '.wp_event_calendar_datepicker' ).each( function() {
			var picker = $( this );

			picker.datepicker( {
				altField: picker.data( 'alt-field' ),
				altFormat: 'yy-mm-dd',
				dateFormat: picker.data( 'date-format' )
			} );

			picker.on( 'change', function() {
				var date = null;

				try {
					date = $.datepicker.parseDate( picker.data( 'date-format' ), picker.val() );
				} catch ( error ) {
					// Let the server validate manually entered dates.
				}

				$( picker.data( 'alt-field' ) ).val( date ? $.datepicker.formatDate( 'yy-mm-dd', date ) : '' );
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

	if ( $( '.ct_rental_datepicker' ).length > 0 ) {
		$( '.ct_rental_datepicker' ).datepicker( {
			dateFormat: 'mm/dd/yy'
		} );
	}
} );
