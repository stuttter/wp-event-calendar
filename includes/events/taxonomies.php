<?php

/**
 * Event Taxonomies
 *
 * @package EventCalendar/Taxonomies
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Event taxonomies
 *
 * @since 0.1.0
 */
function wp_event_calendar_register_taxonomies() {

	// Type
	register_extended_taxonomy(
		'event-type',
		'event',
		array(
			'show_in_nav_menus' => false,
			'rewrite'           => false,
			'hierarchical'      => false
		),
		array(
			'singular' => __( 'Type',  'wp-event-calendar' ),
			'plural'   => __( 'Types', 'wp-event-calendar' ),
			'slug'     => 'events/type'
		)
	);

	// Category
	register_extended_taxonomy(
		'event-category',
		'event',
		array(
			'show_in_nav_menus' => false,
			'rewrite'           => false,
			'hierarchical'      => true
		),
		array(
			'singular' => __( 'Category',   'wp-event-calendar' ),
			'plural'   => __( 'Categories', 'wp-event-calendar' ),
			'slug'     => 'events/category'
		)
	);

	// Tag
	register_extended_taxonomy(
		'event-tag',
		'event',
		array(
			'show_in_nav_menus' => false,
			'rewrite'           => false,
			'hierarchical'      => false
		),
		array(
			'singular' => __( 'Tag',  'wp-event-calendar' ),
			'plural'   => __( 'Tags', 'wp-event-calendar' ),
			'slug'     => 'events/tag'
		)
	);
}
