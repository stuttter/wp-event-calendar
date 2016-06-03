<?php

/**
 * Event Post Statuses
 *
 * @package Calendar/Events/PostStatuses
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register the Event post statuses
 *
 * @since 0.1.9
 */
function wp_event_calendar_register_post_statuses() {

	// Register the event type
	register_post_status( 'passed', array(
		'label'                     => esc_html_x( 'Past', 'events', 'wp-event-calendar' ),
		'label_count'               => _nx_noop( 'Past <span class="count">(%s)</span>', 'Past <span class="count">(%s)</span>', 'events', 'wp-event-calendar' ),
		'exclude_from_search'       => get_post_type_object( 'event' )->exclude_from_search,
		'public'                    => get_post_type_object( 'event' )->public,
		'publicly_queryable'        => get_post_type_object( 'event' )->publicly_queryable,
		'show_in_admin_status_list' => true,
		'show_in_admin_all_list'    => true,
	) );
}
