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
	if ( ! empty( $r['post_type'] ) && post_type_supports( $r['post_type'], 'events' ) ) {

		// Calculate a range of days
		$range = array(
			strtotime( 'Midnight yesterday' ),
			strtotime( 'Midnight today' )
		);

		// Add to meta_query argument
		$r['meta_query'][] = array(
			'relation' => 'OR',
			array(
				'key'     => 'wp_event_calendar_date_time',
				'value'   => $range,
				'type'    => 'DATETIME',
				'compare' => 'BETWEEN',
			),
			array(
				'key'     => 'wp_event_calendar_end_date_time',
				'value'   => $range,
				'type'    => 'DATETIME',
				'compare' => 'BETWEEN',
			)
		);
	}

	return $r;
}
