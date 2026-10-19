<?php

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

class WP_Query {
	public $query;

	public function __construct( $query ) {
		$this->query = $query;
	}

	public function set( $key, $value ) {
		$this->query[ $key ] = $value;
	}
}

function post_type_supports( $post_type, $feature ) {
	return ( 'event' === $post_type ) && ( 'events' === $feature );
}

function assert_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		fwrite( STDERR, $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n" );
		exit( 1 );
	}
}

require dirname( __DIR__ ) . '/wp-event-calendar/includes/events/admin.php';

$_GET['orderby'] = 'venue';

$query = new WP_Query( array(
	'post_type' => 'event',
	'orderby'   => 'venue',
	'order'     => 'asc',
) );

wp_event_calendar_maybe_sort_by_fields( $query );

assert_same( 'venue', $_GET['orderby'], 'Custom sorting should not change the request.' );
assert_same( 'venue', $query->query['orderby'], 'Custom sorting should not change the query.' );
assert_same( 'asc', $query->query['order'], 'Custom sorting should preserve the requested order.' );

$_GET['orderby'] = 'start_date';

$query = new WP_Query( array(
	'post_type' => 'event',
	'orderby'   => 'start_date',
	'order'     => 'asc',
) );

wp_event_calendar_maybe_sort_by_fields( $query );

assert_same( 'start_date', $_GET['orderby'], 'Event sorting should not change the request.' );
assert_same( 'meta_value', $query->query['orderby'], 'Start-date sorting should use event metadata.' );
assert_same( 'ASC', $query->query['order'], 'Start-date sorting should normalize the order.' );

echo "Admin sorting checks passed.\n";
