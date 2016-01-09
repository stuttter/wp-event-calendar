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

		if ( true === checked ) {
			$( '.event-time' ).hide();
		} else {
			$( '.event-time' ).show();
		}
	} );

	function wp_event_calendar_leading_zero() {
		var input  = $( this ),
			number = input.val(),
			pad;

		number = number.replace( '/[^\d]+/g', '' );
		pad    = ( ( number < 10 && number > 0 ) ? '0' : '' ) + number;

		if ( pad.length > 2 ) {
			pad = pad.substr( 0, 2 );
		}

		input.val( pad );
	}

	if ( $( '.ct_rental_datepicker' ).length > 0 ) {
		$( '.ct_rental_datepicker' ).datepicker( {
			dateFormat: 'mm/dd/yy'
		} );
	}

	$( '#wp_event_calendar_details input[type="number"]' )
		.on( 'input', wp_event_calendar_leading_zero );

} );
