<?php

/**
 * Calendar Admin
 *
 * @package Calendar/Common/Admin
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
		$parent = ( 'post' === $post_type )
			? 'edit.php'
			: "edit.php?post_type={$post_type}";

		// View
		$hook = add_submenu_page(
			$parent,
			__( 'Calendar', 'wp-event-calendar' ),
			__( 'Calendar', 'wp-event-calendar' ),
			'read_calendar',
			"{$post_type}-calendar",
			'wp_event_calendar_show_admin_calendar'
		);

		// Highlight helper
		add_action( "admin_head-{$hook}", 'wp_event_calendar_admin_submenu_highlight'  );
		add_action( "admin_head-{$hook}", 'wp_event_calendar_admin_add_screen_options' );
		add_action( "admin_head-{$hook}", 'wp_event_calendar_admin_pointer_buttons'    );
		add_action( "admin_head-{$hook}", 'wp_event_calendar_admin_add_help_tabs'      );
	}
}

/**
 * Override the pointer dismiss button text, to make it clear that "Dismiss"
 * does not mean the event itself is being dismissed in same way.
 *
 * @since 0.1.8
 */
function wp_event_calendar_admin_pointer_buttons() {
	wp_localize_script( 'wp-pointer', 'wpPointerL10n', array(
		'dismiss' => esc_html__( 'Close', 'wp-event-calendar' ),
	) );
}

/**
 * This tells WordPress to highlight the Events > Calendar submenu.
 *
 * @since 0.1.0
 *
 * @global string $plugin_page
 * @global array  $submenu
 */
function wp_event_calendar_admin_submenu_highlight() {
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
		'default' => 'month',
		'option'  => 'calendar_layout'
	) );

	// Events per day
	add_screen_option( 'per_page', array(
		'label'   => _x( 'Events per day', 'Events per day (screen options)', 'wp-event-calendar' ),
		'default' => 10,
		'option'  => 'edit_calendar_per_day'
	) );
}

/**
 * Admin help tabs
 *
 * @since 0.1.0
 */
function wp_event_calendar_admin_add_help_tabs() {

	// Bail if not an Event type screen
	if ( 'event' !== get_current_screen()->post_type ) {
		return;
	}

	// Bail if viewing a taxonomy
	if ( get_current_screen()->taxonomy ) {
		return;
	}

	// Calendar
	get_current_screen()->add_help_tab( array(
		'id'		=> 'calendar',
		'title'		=> esc_html__( 'Calendar', 'wp-event-calendar' ),
		'content'	=>
			'<p>'  . esc_html__( 'This is a calendar that lays out your content chronologically.',   'wp-event-calendar' ) . '</p><ul>' .
			'<li>' . esc_html__( 'You can view events in month and week modes.',                     'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Clicking an event shows a snapshot of the event for that period.', 'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Events may have icons and styling to differentiate them.',         'wp-event-calendar' ) . '</li></ul>' .

			'<p><strong>'  . esc_html__( 'Events', 'wp-event-calendar' ) . '</strong></p><ul>' .
			'<li>' . esc_html__( 'Most events are single-day, for a few hours.',       'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Location data can be attached.',                     'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Some events span multiple days, or have intervals.', 'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Organize events with types, categories, & tags.',    'wp-event-calendar' ) . '</li></ul>'	) );

	// Month View
	get_current_screen()->add_help_tab( array(
		'id'		=> 'month',
		'title'		=> esc_html__( '&mdash; Month', 'wp-event-calendar' ),
		'content'	=>
			'<p>'  . esc_html__( 'This is a traditional monthly calendar view.',             'wp-event-calendar' ) . '</p><ul>' .
			'<li>' . esc_html__( 'Events are listed chronologically in each day.',           'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Events may happen over several days.',                     'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Events may repeat in daily, weekly, or yearly intervals.', 'wp-event-calendar' ) . '</li></ul>' .

			'<p><strong>'  . esc_html__( 'Navigation', 'wp-event-calendar' ) . '</strong></p><ul>' .
			'<li>' . esc_html__( 'View specific months & years via the dropdown on the left.', 'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Paginate through years with double-arrow buttons.',          'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Paginate through months with single-arrow buttons.',         'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Return to today with the double-colon button.',              'wp-event-calendar' ) . '</li></ul>'
	) );

	// Month View
	get_current_screen()->add_help_tab( array(
		'id'		=> 'week',
		'title'		=> __( '&mdash; Week', 'wp-event-calendar' ),
		'content'	=>
			'<p>'  . esc_html__( 'This is a traditional weekly calendar view.',    'wp-event-calendar' ) . '</p><ul>' .
			'<li>' . esc_html__( 'Events are listed chronologically in each day.', 'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Events spanning more than 1 day are omitted.',   'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'All-day events appear in the top row.',          'wp-event-calendar' ) . '</li></ul>' .

			'<p><strong>'  . esc_html__( 'Navigation', 'wp-event-calendar' ) . '</strong></p><ul>' .
			'<li>' . esc_html__( 'View specific months & years via the dropdown on the left.', 'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Paginate through months with double-arrow buttons.',         'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Paginate through weeks with single-arrow buttons.',          'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Return to today with the double-colon button.',              'wp-event-calendar' ) . '</li></ul>'
	) );

	// Month View
	get_current_screen()->add_help_tab( array(
		'id'		=> 'day',
		'title'		=> __( '&mdash; Day', 'wp-event-calendar' ),
		'content'	=>
			'<p>'  . esc_html__( 'This is a traditional daily calendar view.',     'wp-event-calendar' ) . '</p><ul>' .
			'<li>' . esc_html__( 'Events are listed chronologically for the day.', 'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Events spanning more than 1 day are shown.',     'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'All-day events appear in the top row.',          'wp-event-calendar' ) . '</li></ul>' .

			'<p><strong>'  . esc_html__( 'Navigation', 'wp-event-calendar' ) . '</strong></p><ul>' .
			'<li>' . esc_html__( 'View specific months & years via the dropdown on the left.', 'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Paginate through weeks with double-arrow buttons.',          'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Paginate through days with single-arrow buttons.',           'wp-event-calendar' ) . '</li>' .
			'<li>' . esc_html__( 'Return to today with the double-colon button.',              'wp-event-calendar' ) . '</li></ul>'
	) );

	// Help Sidebar
	get_current_screen()->set_help_sidebar(
		'<p><i class="dashicons dashicons-calendar"></i> ' . esc_html__( 'Regular Event', 'wp-event-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-location"></i> ' . esc_html__( 'Has Location',  'wp-event-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-clock"></i> '    . esc_html__( 'All Day',       'wp-event-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-backup"></i> '   . esc_html__( 'Recurring',     'wp-event-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-trash"></i> '    . esc_html__( 'Trashed',       'wp-event-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-hidden"></i> '   . esc_html__( 'Private',       'wp-event-calendar' ) . '</p>' .
		'<p><i class="dashicons dashicons-lock"></i> '     . esc_html__( 'Protected',     'wp-event-calendar' ) . '</p>'
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
			'read_calendar',
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

			ksort( $submenu[ $parent ] );
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
	$user_id          = get_current_user_id();

	// Check for new mode
	if ( ! empty( $_REQUEST['mode'] ) ) {
		if ( in_array( $_REQUEST['mode'], array( 'month', 'week', 'day' ) ) ) {
			$mode = $_REQUEST['mode'];
		}
		update_user_option( $user_id, 'event_calendar_mode', $mode );

	// Use existing mode
	} else {
		$mode = get_user_option( 'event_calendar_mode', $user_id );
	}

	// Load the list table based on the mode
	switch ( $mode ) {
		case 'day' :
			$wp_list_table = new WP_Event_Calendar_Day_Table();
			break;
		case 'week' :
			$wp_list_table = new WP_Event_Calendar_Week_Table();
			break;
		case 'month' :
		default :
			$wp_list_table = new WP_Event_Calendar_Month_Table();
			break;
	}

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
function wp_event_calendar_admin_assets() {

	// Pointer styling
	wp_enqueue_script( 'wp-pointer' );
	wp_enqueue_style( 'wp-pointer' );

	// Date picker CSS (for jQuery UI calendar)
	wp_enqueue_style( 'wp_event_calendar_admin_calendar', wp_event_calendar_get_plugin_url() . 'assets/css/calendar.css', false, wp_event_calendar_get_asset_version(), false );
}
