<?php

/**
 * Event User Alerts
 *
 * @package Plugins/Calendar/Event/Alerts
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Filter the meta query used to get alerts, if querying for Event alerts
 *
 * @since 0.1.0
 *
 * @param array $queries
 * @param array $r
 * @param array $args
 */
function wp_event_calendar_alerts_meta_query( $r = array() ) {

	// Post type supports events
	if ( empty( $r['post_type'] ) || is_array( $r['post_type'] ) || ( 'any' === $r['post_type'] ) || ! post_type_supports( $r['post_type'], 'events' ) ) {
		return $r;
	}

	// Last night at midnight
	$midnight_yesterday = strtotime( 'Midnight yesterday' );

	// Add to meta_query argument
	$r['meta_query'][] = array(
		'relation' => 'OR',

		// Starts after midnight yesterday
		array(
			'key'     => 'wp_event_calendar_date_time',
			'value'   => $midnight_yesterday,
			'type'    => 'DATETIME',
			'compare' => '>',
		),

		// Ends after midnight today
		array(
			'key'     => 'wp_event_calendar_end_date_time',
			'value'   => $midnight_yesterday,
			'type'    => 'DATETIME',
			'compare' => '>',
		)
	);

	return $r;
}
