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

	if ( $( '.ct_rental_datepicker' ).length > 0 ) {
		$( '.ct_rental_datepicker' ).datepicker( {
			dateFormat: 'mm/dd/yy'
		} );
	}
} );
