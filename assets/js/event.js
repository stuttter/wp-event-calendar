jQuery( document ).ready( function( $ ) {
    'use strict';

	if ( $( '.wp_event_calendar_datepicker' ).length > 0 ) {
		$( '.wp_event_calendar_datepicker' ).datepicker( {
			dateFormat: 'mm/dd/yy'
		} );
	}

	$( '.wp_event_calendar_minutes' ).keyup( function( e ) {
		return ( e < 10 ? '0' : '' ) + e;
	} );

	$( '#wp_event_calendar_all_day' ).click( function( e ) {
		var checked = $( this ).prop( 'checked' );

		$( '.event-time input, .event-time select' ).prop( 'disabled', checked );
	} );
} );
