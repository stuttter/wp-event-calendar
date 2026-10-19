<?php

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

function post_type_supports( $post_type, $feature ) {
	return ( 'event' === $post_type ) && ( 'events' === $feature );
}

class WP_Query {
	public $query = array();

	public function __construct( $query = array() ) {
		$this->query = $query;
	}

	public function get( $key ) {
		return isset( $this->query[ $key ] )
			? $this->query[ $key ]
			: '';
	}

	public function set( $key, $value ) {
		$this->query[ $key ] = $value;
	}
}

class WP_Event_Calendar_Test_WPDB {
	public $posts    = 'wp_posts';
	public $postmeta = 'wp_postmeta';

	public function prepare( $query, $value ) {
		return str_replace( '%s', "'" . addslashes( $value ) . "'", $query );
	}
}

$GLOBALS['wpdb'] = new WP_Event_Calendar_Test_WPDB();

require_once dirname( __DIR__ ) . '/wp-event-calendar/includes/events/admin.php';
