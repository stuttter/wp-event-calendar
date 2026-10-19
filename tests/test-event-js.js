'use strict';

var alternateValue = null;
var changeHandler = null;
var datepickerOptions = null;
var displayValue = '03.04.2026';
var document = {};

var alternate = {
	val: function( value ) {
		alternateValue = value;
	}
};

var picker = {
	data: function( key ) {
		return 'alt-field' === key ? '#event-date-iso' : 'dd.mm.yy';
	},
	datepicker: function( options ) {
		datepickerOptions = options;
	},
	on: function( event, callback ) {
		if ( 'change' === event ) {
			changeHandler = callback;
		}
	},
	val: function() {
		return displayValue;
	}
};

function jQuery( selector ) {
	if ( document === selector ) {
		return {
			ready: function( callback ) {
				callback( jQuery );
			}
		};
	}

	if ( '.wp_event_calendar_datepicker' === selector ) {
		return {
			length: 1,
			each: function( callback ) {
				callback.call( picker );
			}
		};
	}

	if ( picker === selector ) {
		return picker;
	}

	if ( '#event-date-iso' === selector ) {
		return alternate;
	}

	return {
		length: 0,
		click: function() {}
	};
}

jQuery.datepicker = {
	formatDate: function() {
		return '2026-04-03';
	},
	parseDate: function( format, value ) {
		if ( 'dd.mm.yy' !== format || '03.04.2026' !== value ) {
			throw new Error( 'Invalid date' );
		}

		return new Date( 2026, 3, 3 );
	}
};

global.document = document;
global.jQuery = jQuery;

require( '../wp-event-calendar/assets/js/event.js' );

if ( 'yy-mm-dd' !== datepickerOptions.altFormat || '#event-date-iso' !== datepickerOptions.altField || 'dd.mm.yy' !== datepickerOptions.dateFormat ) {
	throw new Error( 'Datepicker options were not localized.' );
}

changeHandler();

if ( '2026-04-03' !== alternateValue ) {
	throw new Error( 'Valid manual input did not synchronize the machine-readable date.' );
}

displayValue = 'invalid';
changeHandler();

if ( '' !== alternateValue ) {
	throw new Error( 'Invalid manual input left stale machine-readable data.' );
}

console.log( 'Event JavaScript tests passed.' );
