<?php

define( 'ABSPATH', __DIR__ . '/' );
define( 'DAY_IN_SECONDS', 86400 );

date_default_timezone_set( 'UTC' );

class WP_List_Table {
	protected $modes = array();

	public function __construct( $args = array() ) {
	}
}

class WP_Query {
	public $query;

	public function __construct( $query ) {
		$this->query = $query;
	}

	public function set( $key, $value ) {
		$this->query[ $key ] = $value;
	}
}

function __( $text ) {
	return $text;
}

function apply_filters( $hook, $value ) {
	return $value;
}

function add_action() {
}

function get_option( $option, $default = false ) {
	return isset( $GLOBALS['wp_event_calendar_test_options'][ $option ] )
		? $GLOBALS['wp_event_calendar_test_options'][ $option ]
		: $default;
}

function wp_parse_args( $args, $defaults = array() ) {
	return array_merge( $defaults, $args );
}

function esc_html__( $text ) {
	return $text;
}

function date_i18n( $format, $timestamp = false ) {
	return gmdate( $format, $timestamp );
}

function sanitize_key( $key ) {
	return strtolower( $key );
}

function post_type_supports( $post_type, $feature ) {
	return ( 'event' === $post_type ) && ( 'events' === $feature );
}

class WP_Event_Calendar_Test_Locale {
	public function get_weekday( $day ) {
		return array(
			'Sunday',
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday',
			'Friday',
			'Saturday'
		)[ $day ];
	}
}

$GLOBALS['wp_locale'] = new WP_Event_Calendar_Test_Locale();

require_once dirname( __DIR__ ) . '/wp-event-calendar/includes/common/list-table-base.php';
require_once dirname( __DIR__ ) . '/wp-event-calendar/includes/common/list-table-week.php';
require_once dirname( __DIR__ ) . '/wp-event-calendar/includes/events/admin.php';
