<?php

/**
 * Event Taxonomies
 *
 * @package Calendar/Events/Taxonomies
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Event type taxonomy
 *
 * @since 0.1.0
 */
function wp_event_calendar_register_type_taxonomy() {

	// Labels
	$labels = array(
		'name'                       => __( 'Types',                           'wp-event-calendar' ),
		'singular_name'              => __( 'Type',                            'wp-event-calendar' ),
		'search_items'               => __( 'Search Types',                    'wp-event-calendar' ),
		'popular_items'              => __( 'Popular Types',                   'wp-event-calendar' ),
		'all_items'                  => __( 'All Types',                       'wp-event-calendar' ),
		'parent_item'                => __( 'Parent Type',                     'wp-event-calendar' ),
		'parent_item_colon'          => __( 'Parent Type:',                    'wp-event-calendar' ),
		'edit_item'                  => __( 'Edit Type',                       'wp-event-calendar' ),
		'view_item'                  => __( 'View Type',                       'wp-event-calendar' ),
		'update_item'                => __( 'Update Type',                     'wp-event-calendar' ),
		'add_new_item'               => __( 'Add New Type',                    'wp-event-calendar' ),
		'new_item_name'              => __( 'New Type Name',                   'wp-event-calendar' ),
		'separate_items_with_commas' => __( 'Separate types with commas',      'wp-event-calendar' ),
		'add_or_remove_items'        => __( 'Add or remove types',             'wp-event-calendar' ),
		'choose_from_most_used'      => __( 'Choose from the most used types', 'wp-event-calendar' ),
		'no_terms'                   => __( 'No types',                        'wp-event-calendar' ),
		'not_found'                  => __( 'No types found',                  'wp-event-calendar' ),
		'items_list_navigation'      => __( 'Types list navigation',           'wp-event-calendar' ),
		'items_list'                 => __( 'Types list',                      'wp-event-calendar' )
	);

	// Rewrite rules
	$rewrite = array(
		'slug'       => 'events/type',
		'with_front' => false
	);

	// Capabilities
	$caps = array(
		'manage_terms' => 'manage_event_types',
		'edit_terms'   => 'edit_event_types',
		'delete_terms' => 'delete_event_types',
		'assign_terms' => 'assign_event_types'
	);

	// Arguments
	$args = array(
		'labels'                => $labels,
		'rewrite'               => $rewrite,
		'capabilities'          => $caps,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => 'event-type',
		'show_tagcloud'         => true,
		'hierarchical'          => false,
		'show_in_nav_menus'     => false,
		'public'                => false,
		'show_ui'               => true
	);

	// Register
	register_taxonomy( 'event-type', 'event', $args );
}

/**
 * Event category taxonomy
 *
 * @since 0.1.2
 */
function wp_event_calendar_register_category_taxonomy() {

	// Labels
	$labels = array(
		'name'                       => __( 'Categories',                           'wp-event-calendar' ),
		'singular_name'              => __( 'Category',                             'wp-event-calendar' ),
		'search_items'               => __( 'Search Categories',                    'wp-event-calendar' ),
		'popular_items'              => __( 'Popular Categories',                   'wp-event-calendar' ),
		'all_items'                  => __( 'All Categories',                       'wp-event-calendar' ),
		'parent_item'                => __( 'Parent Category',                      'wp-event-calendar' ),
		'parent_item_colon'          => __( 'Parent Category:',                     'wp-event-calendar' ),
		'edit_item'                  => __( 'Edit Category',                        'wp-event-calendar' ),
		'view_item'                  => __( 'View Category',                        'wp-event-calendar' ),
		'update_item'                => __( 'Update Category',                      'wp-event-calendar' ),
		'add_new_item'               => __( 'Add New Category',                     'wp-event-calendar' ),
		'new_item_name'              => __( 'New Category Name',                    'wp-event-calendar' ),
		'separate_items_with_commas' => __( 'Separate categories with commas',      'wp-event-calendar' ),
		'add_or_remove_items'        => __( 'Add or remove categories',             'wp-event-calendar' ),
		'choose_from_most_used'      => __( 'Choose from the most used categories', 'wp-event-calendar' ),
		'no_terms'                   => __( 'No categories',                        'wp-event-calendar' ),
		'not_found'                  => __( 'No categories found',                  'wp-event-calendar' ),
		'items_list_navigation'      => __( 'Categories list navigation',           'wp-event-calendar' ),
		'items_list'                 => __( 'Categories list',                      'wp-event-calendar' )
	);

	// Rewrite rules
	$rewrite = array(
		'slug'       => 'events/category',
		'with_front' => false
	);

	// Capabilities
	$caps = array(
		'manage_terms' => 'manage_event_categories',
		'edit_terms'   => 'edit_event_categories',
		'delete_terms' => 'delete_event_categories',
		'assign_terms' => 'assign_event_categories'
	);

	// Arguments
	$args = array(
		'labels'                => $labels,
		'rewrite'               => $rewrite,
		'capabilities'          => $caps,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => 'event-category',
		'show_tagcloud'         => false,
		'hierarchical'          => true,
		'show_in_nav_menus'     => false,
		'public'                => false,
		'show_ui'               => true
	);

	// Register
	register_taxonomy( 'event-category', 'event', $args );
}

/**
 * Event tag taxonomy
 *
 * @since 0.1.2
 */
function wp_event_calendar_register_tag_taxonomy() {

	// Labels
	$labels = array(
		'name'                       => __( 'Tags',                           'wp-event-calendar' ),
		'singular_name'              => __( 'Tag',                            'wp-event-calendar' ),
		'search_items'               => __( 'Search Tags',                    'wp-event-calendar' ),
		'popular_items'              => __( 'Popular Tags',                   'wp-event-calendar' ),
		'all_items'                  => __( 'All Tags',                       'wp-event-calendar' ),
		'parent_item'                => __( 'Parent Tag',                     'wp-event-calendar' ),
		'parent_item_colon'          => __( 'Parent Tag:',                    'wp-event-calendar' ),
		'edit_item'                  => __( 'Edit Tag',                       'wp-event-calendar' ),
		'view_item'                  => __( 'View Tag',                       'wp-event-calendar' ),
		'update_item'                => __( 'Update Tag',                     'wp-event-calendar' ),
		'add_new_item'               => __( 'Add New Tag',                    'wp-event-calendar' ),
		'new_item_name'              => __( 'New Tag Name',                   'wp-event-calendar' ),
		'separate_items_with_commas' => __( 'Separate tags with commas',      'wp-event-calendar' ),
		'add_or_remove_items'        => __( 'Add or remove tags',             'wp-event-calendar' ),
		'choose_from_most_used'      => __( 'Choose from the most used tags', 'wp-event-calendar' ),
		'no_terms'                   => __( 'No tags',                        'wp-event-calendar' ),
		'not_found'                  => __( 'No tags found',                  'wp-event-calendar' ),
		'items_list_navigation'      => __( 'Tags list navigation',           'wp-event-calendar' ),
		'items_list'                 => __( 'Tags list',                      'wp-event-calendar' )
	);

	// Rewrite rules
	$rewrite = array(
		'slug'       => 'events/tag',
		'with_front' => false
	);

	// Capabilities
	$caps = array(
		'manage_terms' => 'manage_event_tags',
		'edit_terms'   => 'edit_event_tags',
		'delete_terms' => 'delete_event_tags',
		'assign_terms' => 'assign_event_tags'
	);

	// Arguments
	$args = array(
		'labels'                => $labels,
		'rewrite'               => $rewrite,
		'capabilities'          => $caps,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => 'event-tag',
		'show_tagcloud'         => false,
		'hierarchical'          => false,
		'show_in_nav_menus'     => false,
		'public'                => false,
		'show_ui'               => true
	);

	// Register
	register_taxonomy( 'event-tag', 'event', $args );
}
