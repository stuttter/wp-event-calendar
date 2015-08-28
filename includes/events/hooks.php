<?php

/**
 * Event Actions
 *
 * @package EventCalendar/Actions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Init
add_action( 'init', 'wp_event_calendar_register_post_types' );
add_action( 'init', 'wp_event_calendar_register_taxonomies' );

// Metaboxes
add_action( 'add_meta_boxes', 'wp_event_calendar_add_metabox'  );
add_action( 'save_post',      'wp_event_calendar_metabox_save' );

// Admin Menu
add_action( 'admin_menu', 'wp_event_calendar_add_submenus' );
add_action( 'admin_head', 'wp_event_calendar_admin_assets' );
add_action( 'admin_head', 'wp_event_calendar_admin_js'     );

// Admin Scripts
add_action( 'admin_enqueue_scripts', 'wp_event_calendar_admin_event_assets' );

// Custom title-box text
add_filter( 'enter_title_here',        'wp_event_calendar_enter_title_here',        10, 2 );
add_filter( 'disable_months_dropdown', 'wp_event_calendar_disable_months_dropdown', 10, 2 );
add_action( 'restrict_manage_posts',   'wp_event_calendar_add_dropdown_filters'           );

// List Table Columns
//add_filter( 'manage_edit-event_sortable_columns', 'wp_event_calendar_sortable_columns' );
add_filter( 'manage_event_posts_columns',         'wp_event_calendar_manage_posts_columns' );
add_action( 'manage_event_posts_custom_column',   'wp_event_calendar_manage_custom_column_data' );
//add_action( 'load-edit.php',                      'wp_event_calendar_default_sort' );
//add_filter( 'pre_get_posts',                      'wp_event_calendar_maybe_sort_by_fields' );
//add_filter( 'posts_clauses',                      'wp_event_calendar_maybe_sort_by_taxonomy', 10, 2 );
