<?php

/**
 * Event User Dashboard
 *
 * @package Plugins/Calendar/Event/Dashboard
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Filter sections and add "Events" section
 *
 * @since 0.3.0
 *
 * @param array $sections
 */
function wp_event_calendar_add_section( $sections = array() ) {

	// Events
	$sections[] = array(
		'id'           => 'events',
		'slug'         => 'events',
		'url'          => '',
		'label'        => esc_html__( 'Events', 'wp-user-alerts' ),
		'show_in_menu' => true,
		'order'        => 20
	);

	// Return sections
	return $sections;
}
