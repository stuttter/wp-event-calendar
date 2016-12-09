<?php

/**
 * Event Post Types
 *
 * @package Calendar/Events/PostTypes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register the Event post types
 *
 * If you want to manipulate these arguments, use the `register_post_type_args`
 * filter that's built into WordPress since version 4.4.
 *
 * @since 0.1.0
 */
function wp_event_calendar_register_post_types() {

	// Labels
	$labels = array(
		'name'                  => _x( 'Events', 'post type general name', 'wp-event-calendar' ),
		'singular_name'         => _x( 'Event', 'post type singular name', 'wp-event-calendar' ),
		'add_new'               => _x( 'Add New', 'event', 'wp-event-calendar' ),
		'add_new_item'          => __( 'Add New Event', 'wp-event-calendar' ),
		'edit_item'             => __( 'Edit Event', 'wp-event-calendar' ),
		'new_item'              => __( 'New Event', 'wp-event-calendar' ),
		'view_item'             => __( 'View Event', 'wp-event-calendar' ),
		'search_items'          => __( 'Search Events', 'wp-event-calendar' ),
		'not_found'             => __( 'No events found.', 'wp-event-calendar' ),
		'not_found_in_trash'    => __( 'No events found in trash.', 'wp-event-calendar' ),
		'parent_item_colon'     => __( 'Parent Event:', 'wp-event-calendar' ),
		'all_items'             => __( 'All Events', 'wp-event-calendar' ),
		'featured_image'        => __( 'Featured Image', 'wp-event-calendar' ),
		'set_featured_image'    => __( 'Set featured image', 'wp-event-calendar' ),
		'remove_featured_image' => __( 'Remove featured image', 'wp-event-calendar' ),
		'use_featured_image'    => __( 'Use as featured image', 'wp-event-calendar' ),
	);

	// Supports
	$supports = array(
		'title',
		'thumbnail',
		'revisions',
		'events'
	);

	// Capability types
	$cap_types = array(
		'event',
		'events'
	);

	// Capabilities
	$caps = array(

		// Meta caps
		'edit_post'              => 'edit_event',
		'read_post'              => 'read_event',
		'delete_post'            => 'delete_event',

		// Primitive/meta caps
		'read'                   => 'read',
		'create_posts'           => 'create_events',

		// Primitive caps (used outside of map_meta_cap)
		'edit_posts'             => 'edit_events',
		'edit_others_posts'      => 'edit_others_events',
		'publish_posts'          => 'publish_events',
		'read_private_posts'     => 'read_private_events',

		// Primitive caps (used inside of map_meta_cap)
		'delete_posts'           => 'delete_events',
		'delete_private_posts'   => 'delete_private_events',
		'delete_published_posts' => 'delete_published_events',
		'delete_others_posts'    => 'delete_others_events',
		'edit_private_posts'     => 'edit_private_events',
		'edit_published_posts'   => 'edit_published_events'
	);

	// Rewrite
	$rewrite = array(
		'slug'       => 'event',
		'with_front' => false
	);

	// Post type arguments
	$args = array(
		'labels'               => $labels,
		'supports'             => $supports,
		'description'          => '',
		'public'               => true,
		'hierarchical'         => true,
		'exclude_from_search'  => true,
		'publicly_queryable'   => false,
		'show_ui'              => true,
		'show_in_menu'         => true,
		'show_in_nav_menus'    => false,
		'archive_in_nav_menus' => false,
		'show_in_admin_bar'    => true,
		'menu_position'        => 44,
		'menu_icon'            => 'dashicons-calendar-alt',
		'capabilities'         => $caps,
		'capability_type'      => $cap_types,
		'register_meta_box_cb' => null,
		'taxonomies'           => array(),
		'has_archive'          => false,
		'rewrite'              => $rewrite,
		'query_var'            => true,
		'can_export'           => true,
		'delete_with_user'     => false,
	);

	// Register the event type
	register_post_type( 'event', $args );
}
