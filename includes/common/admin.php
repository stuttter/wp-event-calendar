<?php

/**
 * Event List Tables
 *
 * @package EventCalendar/ListTable
 *
 * @see WP_Posts_List_Table
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add the "Calendar" submenu
 *
 * @since 0.1.0
 */
function wp_event_calendar_add_submenus() {

	// Get post types
	$post_types = wp_event_calendar_allowed_post_types();

	// Loop through and add submenus
	foreach ( $post_types as $post_type ) {

		// 'post' post type needs special handling
		if ( 'post' === $post_type ) {
			$parent = 'edit.php';

		// All other post types
		} else {
			$parent = 'edit.php?post_type=' . $post_type;
		}

		// View
		$hook = add_submenu_page(
			$parent,
			__( 'Calendar', 'bbpress' ),
			__( 'Calendar', 'bbpress' ),
			'edit_posts',
			$post_type . '-calendar',
			'wp_event_calendar_show_admin_calendar'
		);

		// Highlight helper
		add_action( "admin_head-$hook", 'wp_event_calendar_modify_admin_submenu_highlight' );
		add_action( "admin_head-$hook", 'wp_event_calendar_admin_add_screen_options'       );
		add_action( "admin_head-$hook", 'wp_event_calendar_admin_add_help_tabs'            );
	}
}

/**
 * This tells WordPress to highlight the Events > Calendar submenu.
 *
 * @since 0.1.0
 *
 * @global string $plugin_page
 * @global array  $submenu
 */
function wp_event_calendar_modify_admin_submenu_highlight() {
	global $plugin_page, $submenu_file;

	// Highlight both, since they're the same thing.
	if ( in_array( $plugin_page, array( 'event-calendar', 'event_page_calendar' ) ) ) {
		$submenu_file = $plugin_page;
	}
}

/**
 * Admin screen options
 *
 * @since 0.1.0
 */
function wp_event_calendar_admin_add_screen_options() {

	// columns screen option
	add_screen_option( 'layout_type', array(
		'label'   => _x( 'Layout', 'Month, Week, or Day (screen options)', 'wp-event-calendar' ),
		'default' => 'month'
	) );

	// Events per day
	add_screen_option( 'per_page', array(
		'label'   => _x( 'Events per day', 'Events per day (screen options)', 'wp-event-calendar' ),
		'default' => 5
	) );
}

/**
 * Admin help tabs
 *
 * @since 0.1.0
 */
function wp_event_calendar_admin_add_help_tabs() {

	// Calendar
	get_current_screen()->add_help_tab( array(
		'id'		=> 'bulk-actions',
		'title'		=> __( 'Calendar', 'wp-event-calendar' ),
		'content'	=>
			'<p>' . __( 'Holy schmoly! This is a basic calendar that lays out your content chronologically.',    'wp-event-calendar' ) . '</p>' .
			'<p>' . __( 'Right now only the "month" view is available, but "day" and "week" views are in the works', 'wp-event-calendar' ) . '</p>'
	) );

	// Help Sidebar
	get_current_screen()->set_help_sidebar(
		'<p><strong>' . __( 'This calendar shows:', 'wp-event-calendar' ) . '</strong></p>' .
		'<p>' . __( 'Public, private, pending, password-protected, and draft content.', 'wp-event-calendar' ) . '</p>'
	);
}

/**
 * Reorder "Events" submenu items to reprioritize "Calendar"
 *
 * @since 0.1.0
 *
 * @param  array $menu_order
 *
 * @return array
 */
function wp_event_calendar_change_menu_order( $menu_order = array() ) {
	global $submenu;

	// Setup a bunch of empty arrays
	$neighbors = $calendar_menus = $calendar_submenus = array();

	// Get allowed post types
	$allowed_types = wp_event_calendar_allowed_post_types();

	// Loop through allowed types
	foreach ( $allowed_types as $type ) {

		// Find "All" links
		if ( 'post' === $type ) {
			$pt  = 'edit.php';
		} else {
			$pt  = 'edit.php?post_type=' . $type;
		}

		$neighbors[] = $pt;

		// Custom menus
		$calendar_menus[] = $type . '-calendar';

		// The calendar submenu arrays
		$calendar_submenus[ $pt ] = array(
			esc_html__( 'Calendar', 'wp-event-calendar' ),
			'edit_posts',
			$type . '-calendar',
			esc_html__( 'Calendar', 'wp-event-calendar' ),
		);
	}

	// Loop through menu order and do some rearranging
	foreach ( $submenu as $parent => $children ) {

		// Skip if no neighborly match
		if ( in_array( $parent, $neighbors ) ) {
			foreach ( $children as $child => $menu ) {

				// Found a neighbor
				if ( in_array( $menu[2], $neighbors ) ) {
					unset( $submenu[ $parent ][ $child ] );

					$submenu[ $parent ][ $child ] = $menu;
					$submenu[ $parent ][9] = $calendar_submenus[ $parent ];
				} elseif ( in_array( $menu[2], $calendar_menus ) ) {
					unset( $submenu[ $parent ][ $child ] );
				}
			}
		}
	}

	// Always return the menu order
	return $menu_order;
}

/**
 * Output the admin calendar
 *
 * @since 0.1.0
 */
function wp_event_calendar_show_admin_calendar() {

	// Get the post type for easy caps checking
	$post_type        = wp_event_calendar_get_admin_post_type();
	$post_type_object = get_post_type_object( $post_type );

	// Load the Calendar table
	$wp_list_table = new WP_Event_Calendar_Calendar_Table();

	// Query for calendar content
	$wp_list_table->prepare_items();

	// Set the help tabs
	$wp_list_table->set_help_tabs(); ?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Events', 'wp-event-calendar' ); ?>
			<?php if ( current_user_can( $post_type_object->cap->create_posts ) ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'post_type' => $post_type ), admin_url( 'post-new.php' ) ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'wp-event-calendar' ); ?></a>
			<?php endif; ?>
		</h1>

		<?php $wp_list_table->views(); ?>

		<form id="posts-filter" method="get">

			<?php $wp_list_table->search_box( $post_type_object->labels->search_items, $post_type ); ?>

			<input type="hidden" name="post_type" class="post_type_page" value="<?php echo esc_attr( $post_type ); ?>" />
			<input type="hidden" name="page" class="post_type_page" value="<?php echo esc_attr( $post_type ); ?>-calendar" />

			<?php $wp_list_table->display(); ?>

		</form>

		<div id="ajax-response"></div>
		<br class="clear" />
	</div>

<?php
}

/**
 * Tweak admin styling for a calendar specific layout
 *
 * @since 0.1.0
 */
function wp_event_calendar_admin_styling() {
?>

	<style type="text/css">
		table.calendar thead th,
		table.calendar tfoot th {
			text-align: center;
		}

		table.calendar tbody td span.day-number {
			position: absolute;
			top: 0px;
			left: 0px;
			padding: 2px 10px;
			opacity: 0.5;
			border-right: 1px solid #e1e1e1;
			border-bottom: 1px solid #e1e1e1;
			background-color: #fafafa;
		}

		table.calendar tbody td.today span.day-number {
			background: #f0f0c0;
		}

		table.calendar tbody td {
			padding: 0;
			height: 110px;
			position: relative;
			text-align: left;
			border-bottom: 1px solid #e1e1e1;
			border-right: 1px solid #e1e1e1;
		}

		table.calendar tbody th {
			height: 80px;
			background-color: #fff;
			border-bottom: 1px solid #e1e1e1;
		}

		table.calendar tbody tr th:last-of-type {
			border-right: 1px solid #e1e1e1;
		}

		table.calendar tbody td:last-of-type:not(.position-6) {
			border-right: 1px solid #e1e1e1;
		}

		table.calendar tbody tr td:last-of-type {
			border-right: none;
		}

		table.calendar tbody td.saturday,
		table.calendar tbody td.sunday {
			background-color: #f1f1f1;
		}

		table.calendar div.events-for-day {
			margin: 30px 0 5px;
			min-height: 80px
		}

		table.calendar a {
			display: block;
			-o-text-overflow: ellipsis;
			text-overflow:    ellipsis;
			overflow-x: hidden;
			white-space: nowrap;
			margin: 0;
			padding: 0 5px;
		}

		table.calendar a.all-day {
			background-color: #f4f477;
		}

		table.calendar a:before {
			font: normal 15px/20px 'dashicons';
			vertical-align: top;
			speak: none;
			-webkit-font-smoothing: antialiased;
			-moz-osx-font-smoothing: grayscale;
			width: 20px;
			height: 20px;
			margin-right: 4px;
		}

		table.calendar a.type-event:before {
			content: '\f145';
		}

		table.calendar a.type-post:before {
			content: '\f109';
		}

		table.calendar a.type-page:before {
			content: '\f105';
		}

		table.calendar a.status-private:before {
			content: '\f177';
		}

		table.calendar a.post-password-required:before {
			content: '\f315';
		}

		table.calendar a.status-draft:before {
			content: '\f469';
		}

		table.calendar a.status-draft {
			opacity: 0.5;
		}

		input#year {
			width: 90px;
		}

	</style>

<?php
}