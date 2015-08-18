<?php

/**
 * Event Post Types
 *
 * @package EventCalendar
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Event Post Types
 *
 * @since 0.1.0
 */
function wp_event_calendar_register_post_types() {

	// Event
	register_extended_post_type(
		'event',
		array(
			'list_table'           => 'foo',
			'hierarchical'         => true,
			'has_archive'          => false,
			'publicly_queryable'   => false,
			'menu_position'        => 44,
			'menu_icon'            => 'dashicons-calendar',
			'delete_with_user'     => false,
			'show_in_nav_menus'    => false,
			'show_in_admin_bar'    => false,
			'archive_in_nav_menus' => false,
			'enter_title_here'     => __( 'Name this event', 'wp-event-calendar' ),
			'featured_image'       => __( 'Event Thumbnail', 'wp-event-calendar' ),
			'supports'             => array( 'title', 'editor', 'thumbnail' ),
			'admin_cols' => array(
				'title' => array(
					'title' => __( 'Event', 'wp-event-calendar' )
				),
				'author' => array(
					'title' => __( 'Creator', 'wp-event-calendar' )
				),
				'types' => array(
					'taxonomy' => 'event-type'
				),
				'categories' => array(
					'taxonomy' => 'event-category'
				),
				'tags' => array(
					'taxonomy' => 'event-tag'
				),
				'published' => array(
					'title'       => __( 'Created', 'wp-event-calendar' ),
					'post_field'  => 'post_date'
				),
				'modified' => array(
					'title'       => __( 'Last Modified', 'wp-event-calendar' ),
					'post_field'  => 'post_modified',
					'default'     => true
				),
			),
			'admin_filters' => array(
			)
		),
		array(
			'singular' => __( 'Event',  'wp-event-calendar' ),
			'plural'   => __( 'Events', 'wp-event-calendar' ),
			'slug'     => 'events'
		)
	);
}
