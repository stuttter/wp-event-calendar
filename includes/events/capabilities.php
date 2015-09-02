<?php

/**
 * Event Capabilities
 *
 * @package Calendar/Events/Capabilities
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Maps event capabilities
 *
 * @since 0.1.4
 *
 * @param  array   $caps     Capabilities for meta capability
 * @param  string  $cap      Capability name
 * @param  int     $user_id  User id
 * @param  array   $args     Arguments
 *
 * @return array   Actual capabilities for meta capability
 */
function wp_event_calendar_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	// What capability is being checked?
	switch ( $cap ) {

		// Reading
		case 'read_event' :
			$caps = array( 'read' );
			break;

		// Publishing
		case 'publish_events' :

		// Editing
		case 'edit_events' :
		case 'edit_others_events' :
		case 'edit_event' :

		// Deleting
		case 'delete_event' :
		case 'delete_events' :
		case 'delete_others_events'  :
			$caps = array( 'list_users' );
			break;
	}

	return apply_filters( 'wp_event_calendar_meta_caps', $caps, $cap, $user_id, $args );
}

/**
 * Maps event type capabilities
 *
 * @since 0.1.4
 *
 * @param   array     $caps      Capabilities for meta capability
 * @param   string    $cap       Capability name
 * @param   int       $user_id   User id
 * @param   array     $args      Arguments
 *
 * @return  array     Actual capabilities for meta capability
 */
function wp_event_calendar_type_meta_caps( $caps, $cap, $user_id, $args ) {

	// What capability is being checked?
	switch ( $cap ) {
		case 'manage_event_types' :
		case 'edit_event_types'   :
		case 'delete_event_types' :
		case 'assign_event_types' :
			$caps = array( 'list_users' );
			break;
	}

	return apply_filters( 'wp_event_calendar_type_meta_caps', $caps, $cap, $user_id, $args );
}

/**
 * Maps event category capabilities
 *
 * @since 0.1.4
 *
 * @param   array     $caps      Capabilities for meta capability
 * @param   string    $cap       Capability name
 * @param   int       $user_id   User id
 * @param   array     $args      Arguments
 *
 * @return  array     Actual capabilities for meta capability
 */
function wp_event_calendar_category_meta_caps( $caps, $cap, $user_id, $args ) {

	// What capability is being checked?
	switch ( $cap ) {
		case 'manage_event_categories' :
		case 'edit_event_categories'   :
		case 'delete_event_categories' :
		case 'assign_event_categories' :
			$caps = array( 'list_users' );
			break;
	}

	return apply_filters( 'wp_event_calendar_category_meta_caps', $caps, $cap, $user_id, $args );
}

/**
 * Maps event tag capabilities
 *
 * @since 0.1.4
 *
 * @param   array     $caps      Capabilities for meta capability
 * @param   string    $cap       Capability name
 * @param   int       $user_id   User id
 * @param   array     $args      Arguments
 *
 * @return  array     Actual capabilities for meta capability
 */
function wp_event_calendar_tag_meta_caps( $caps, $cap, $user_id, $args ) {

	// What capability is being checked?
	switch ( $cap ) {
		case 'manage_event_tags' :
		case 'edit_event_tags'   :
		case 'delete_event_tags' :
		case 'assign_event_tags' :
			$caps = array( 'list_users' );
			break;
	}

	return apply_filters( 'wp_event_calendar_tag_meta_caps', $caps, $cap, $user_id, $args );
}
