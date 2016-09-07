<?php

/**
 * Event Post Cron
 *
 * @package Calendar/Events/Cron
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Schedule the cron job used to update events that have passed
 *
 * @since 0.1.9
 */
function wp_event_calendar_cron_hook() {

	// Bail if already scheduled
	if ( wp_next_scheduled( 'wp_event_calendar_cron_hook' ) ) {
		return;
	}

	// Setup cron
	wp_schedule_event( time(), 'twicedaily', 'wp_event_calendar_update_events' );
}

/**
 * Unschedule the cron job used to update events that have passed
 *
 * @since 0.1.9
 */
function wp_event_calendar_cron_unhook() {
   $timestamp = wp_next_scheduled( 'wp_event_calendar_cron_hook' );
   wp_unschedule_event( $timestamp, 'wp_event_calendar_cron_hook' );
}

/**
 * Update post statuses
 *
 * @since 0.1.9
 */
function wp_event_calendar_update_post_statuses() {

	// End of day, today
	$eod = gmdate( 'Y-m-d H:i:s', strtotime( 'midnight tomorrow' ) );

	// Get old events
	$old_events = new WP_Query( array(
		'fields'         => 'ids',
		'post_type'      => wp_event_calendar_allowed_post_types(),
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_query' => array( array(
			'relation' => 'AND',
			array(
				'key'     => 'wp_event_calendar_date_time',
				'value'   => $eod,
				'type'    => 'DATETIME',
				'compare' => '<',
			),
			array(
				'key'     => 'wp_event_calendar_end_date_time',
				'value'   => $eod,
				'type'    => 'DATETIME',
				'compare' => '<',
			)
		) )
	) );

	// Bail if no posts
	if ( empty( $old_events->posts ) ) {
		return;
	}

	// Loop through posts and update status
	foreach ( $old_events->posts as $post_id ) {
		wp_update_post( array(
			'ID'          => $post_id,
			'post_status' => 'passed'
		) );
	}
}
