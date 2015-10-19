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
function wp_event_calendar_register_post_types() {

	// Register the event type
	register_post_status( 'passed', array(
		'label'                     => esc_html_x( 'Passed', 'events', 'wp-event-calendar' ),
		'label_count'               => _nx_noop( 'Passed <span class="count">(%s)</span>', 'Passed <span class="count">(%s)</span>', 'events', 'wp-event-calendar' ),
		'exclude_from_search'       => true,
		'public'                    => null,
		'internal'                  => null,
		'protected'                 => null,
		'private'                   => null,
		'publicly_queryable'        => false,
		'show_in_admin_status_list' => true,
		'show_in_admin_all_list'    => false,
	) );
}
