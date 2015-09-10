<?php

/**
 * Plugin Name: WP Event Calendar
 * Plugin URI:  https://wordpress.org/plugins/wp-event-calendar/
 * Description: Flexible events, with a calendar view.
 * Author:      John James Jacoby
 * Version:     0.1.8
 * Author URI:  https://profiles.wordpress.org/johnjamesjacoby/
 * License:     GPL v2 or later
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
	require $plugin_path . 'includes/common/functions.php';

	// Admin-only common files
	if ( is_admin() ) {
		require $plugin_path . 'includes/common/admin.php';
		require $plugin_path . 'includes/common/list-table-base.php';
		require $plugin_path . 'includes/common/list-table-month.php';
		require $plugin_path . 'includes/common/list-table-week.php';
		require $plugin_path . 'includes/common/list-table-day.php';
	}

	// Event files
	require $plugin_path . 'includes/events/capabilities.php';
	require $plugin_path . 'includes/events/post-types.php';
	require $plugin_path . 'includes/events/taxonomies.php';
	require $plugin_path . 'includes/events/metaboxes.php';
	require $plugin_path . 'includes/events/admin.php';
	require $plugin_path . 'includes/events/hooks.php';
}
add_action( 'plugins_loaded', 'wp_event_calendar' );

/**
 * Return the plugin's URL
 *
 * @since 0.1.2
 *
 * @return string
 */
function wp_event_calendar_get_plugin_url() {
	return plugin_dir_url( __FILE__ );
}

/**
 * Return the asset version
 *
 * @since 0.1.2
 *
 * @return int
 */
function wp_event_calendar_get_asset_version() {
	return 201509100001;
}
