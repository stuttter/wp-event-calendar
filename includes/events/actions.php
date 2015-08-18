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
//add_action( 'add_meta_boxes', 'wp_event_calendar_add_meta_boxes' );

// Admin Menu
add_action( 'admin_menu',        'wp_event_calendar_add_submenus'      );
add_action( 'admin_head',        'wp_event_calendar_admin_styling'     );
//add_filter( 'menu_order',        'wp_event_calendar_change_menu_order' );
add_filter( 'custom_menu_order', '__return_true'                       );