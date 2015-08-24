<?php

/**
 * Plugin Name: WP Event Calendar
 * Plugin URI:  https://wordpress.org/plugins/wp-event-calendar/
 * Description: Flexible events, with a calendar view.
 * Author:      John James Jacoby
 * Version:     0.1.1
 * Author URI:  https://profiles.wordpress.org/johnjamesjacoby/
 * License:     GPL2
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Include the Event Calendar files
 *
 * @since 0.1.0
 */
function wp_event_calendar() {

	// Get the plugin path
	$plugin_path = plugin_dir_path( __FILE__ );

	// Common files
	require $plugin_path . 'includes/common/list-tables.php';
	require $plugin_path . 'includes/common/functions.php';
	require $plugin_path . 'includes/common/admin.php';

	// Event files
	require $plugin_path . 'includes/events/post-types.php';
	require $plugin_path . 'includes/events/taxonomies.php';
	require $plugin_path . 'includes/events/actions.php';
}
add_action( 'plugins_loaded', 'wp_event_calendar' );
