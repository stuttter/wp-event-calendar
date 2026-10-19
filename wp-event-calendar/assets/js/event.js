jQuery( document ).ready( function( $ ) {
    'use strict';

	if ( $( '.wp_event_calendar_datepicker' ).length > 0 ) {
		$( '.wp_event_calendar_datepicker' ).datepicker( {
			dateFormat: 'mm/dd/yy'
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
