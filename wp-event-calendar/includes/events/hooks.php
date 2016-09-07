<?php

/**
 * Event Actions
 *
 * @package EventCalendar/Actions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Post types
add_action( 'init', 'wp_event_calendar_register_post_types'    );
add_action( 'init', 'wp_event_calendar_register_post_statuses' );

// Taxonomies
add_action( 'init', 'wp_event_calendar_register_type_taxonomy'     );
add_action( 'init', 'wp_event_calendar_register_category_taxonomy' );
add_action( 'init', 'wp_event_calendar_register_tag_taxonomy'      );

add_action( 'edit_form_after_title',  'wp_event_calendar_editor_above' );
add_action( 'edit_form_after_editor', 'wp_event_calendar_editor_below' );

// Caps
add_filter( 'map_meta_cap', 'wp_event_calendar_meta_caps',          10, 4 );
add_filter( 'map_meta_cap', 'wp_event_calendar_type_meta_caps',     10, 4 );
add_filter( 'map_meta_cap', 'wp_event_calendar_category_meta_caps', 10, 4 );
add_filter( 'map_meta_cap', 'wp_event_calendar_tag_meta_caps',      10, 4 );

// Metaboxes
add_action( 'add_meta_boxes',          'wp_event_calendar_add_metabox'           );
add_action( 'save_post',               'wp_event_calendar_metabox_save'          );
add_filter( 'register_taxonomy_args',  'wp_event_calendar_taxonomy_args',  10, 2 );
add_filter( 'wp_terms_checklist_args', 'wp_event_calendar_checklist_args', 10, 1 );

// Admin Menu
add_action( 'admin_menu', 'wp_event_calendar_add_submenus' );
add_action( 'admin_head', 'wp_event_calendar_admin_assets' );

// Admin Scripts
add_action( 'admin_enqueue_scripts', 'wp_event_calendar_admin_event_assets' );

// Custom title-box text
add_filter( 'enter_title_here',        'wp_event_calendar_enter_title_here',        10, 2 );
add_filter( 'disable_months_dropdown', 'wp_event_calendar_disable_months_dropdown', 10, 2 );
add_action( 'restrict_manage_posts',   'wp_event_calendar_add_dropdown_filters'           );

// List Table Columns
add_filter( 'manage_event_posts_columns',         'wp_event_calendar_manage_posts_columns' );
add_action( 'manage_event_posts_custom_column',   'wp_event_calendar_manage_custom_column_data' );
add_filter( 'manage_edit-event_sortable_columns', 'wp_event_calendar_sortable_columns' );
add_filter( 'pre_get_posts',                      'wp_event_calendar_maybe_sort_by_fields' );
add_filter( 'pre_get_posts',                      'wp_event_calendar_maybe_filter_by_fields' );

// Cron
add_action( 'wp_event_calendar_update_events', 'wp_event_calendar_update_post_statuses' );

// Admin Help
add_action( 'admin_head-edit.php',     'wp_event_calendar_admin_add_help_tabs' );
add_action( 'admin_head-post-new.php', 'wp_event_calendar_admin_add_help_tabs' );

// User Alerts
add_filter( 'wp_user_alerts_get_alerts', 'wp_event_calendar_alerts_meta_query' );

// User Dashboard
add_filter( 'wp_user_dashboard_get_sections', 'wp_event_calendar_add_section' );

// Remove password and private from quick/bulk edit admin screen
add_action( 'admin_print_footer_scripts', 'wp_event_calendar_hide_quick_bulk_edit' );