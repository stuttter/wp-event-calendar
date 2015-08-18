<?php

/**
 * Event Functions
 *
 * @package EventCalendar/Common/Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return an array of the post types that calendars are available on
 *
 * You can filter this to enable a post calendar for just about any kind of
 * post type with an interface.
 *
 * @since 0.1.0
 *
 * @return array
 */
function wp_event_calendar_allowed_post_types() {
	return apply_filters( 'wp_event_calendar_allowed_post_types', array(
		'event'
	) );
}

/**
 * Get the current admin post type
 *
 * @since 0.1.0
 *
 * @return string
 */
function wp_event_calendar_get_admin_post_type() {

	// Use $typenow global if it's not empty
	if ( ! empty( $GLOBALS['typenow'] ) ) {
		return $GLOBALS['typenow'];
	}

	// Use $GET post_type if it's not empty
	if ( ! empty( $_GET['post_type'] ) ) {
		return wp_unslash( $_GET['post_type'] );
	}

	// Use get parameter
	return 'post';
}
