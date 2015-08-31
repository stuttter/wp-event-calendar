<?php

/**
 * Event Admin
 *
 * @package Calendar/Events/Admin
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Set the placeholder text for the title field for this post type.
 *
 * @since 0.1.2
 *
 * @param   string   $title The placeholder text
 * @param   WP_Post  $post  The current post
 *
 * @return  string          The updated placeholder text
 */
function wp_event_calendar_enter_title_here( $title, WP_Post $post ) {

	// Override if 'event' post type
	if ( 'event' == $post->post_type ) {
		$title = esc_html__( 'Name this event', 'wp-event-calendar' );
	}

	// Return possibly modified title
	return $title;
}

/**
 * Sortable event columns
 *
 * @since 0.1.2
 *
 * @param   array  $columns
 *
 * @return  array
 */
function wp_event_calendar_sortable_columns() {
	return array(
		'start'
	);
}

/**
 * Disable months dropdown
 *
 * @since 0.1.2
 */
function wp_event_calendar_disable_months_dropdown( $disabled = false, $post_type = 'post' ) {

	// Disable dropdown for events
	if ( 'event' === $post_type ) {
		$disabled = true;
	}

	// Return maybe modified value
	return $disabled;
}

/**
 * Output dropdowns & filters
 *
 * @since 0.1.2
 */
function wp_event_calendar_add_dropdown_filters() {

	// Bail if event type taxonomy was unregistered
	if ( ! is_object_in_taxonomy( 'event', 'event-type' ) ) {
		return;
	}

	// Output lable & dropdown
	echo '<label class="screen-reader-text" for="cat">' . __( 'Filter by type', 'wp-event-calendar' ) . '</label>';
	wp_dropdown_categories( array(
		'show_option_none' => __( 'All types', 'wp-event-calendar' ),
		'hide_empty'       => false,
		'hierarchical'     => false,
		'taxonomy'         => 'event-type',
		'show_count'       => 0,
		'orderby'          => 'name',
		'selected'         => $GLOBALS['cat']
	) );
}

/**
 * Filter events posts list-table columns
 *
 * @since 0.1.2
 *
 * @param   array  $old_columns
 * @return  array
 */
function wp_event_calendar_manage_posts_columns( $old_columns = array() ) {

	// New columns
	$new_columns = array(
		'cb'       => '<input type="checkbox" />',
		'username' => esc_html__( 'Creator',  'wp-event-calendar' ),
		'start'    => esc_html__( 'Starts',   'wp-event-calendar' ),
		'end'      => esc_html__( 'Ends',     'wp-event-calendar' ),
		'duration' => esc_html__( 'Duration', 'wp-event-calendar' ),
		'repeat'   => esc_html__( 'Repeat',   'wp-event-calendar' ),
		'type'     => esc_html__( 'Types',    'wp-event-calendar' ),
	);

	// Filter & return
	return apply_filters( 'wp_event_calendar_manage_posts_columns', $new_columns, $old_columns );
}

/**
 * Output content for each event column
 *
 * @since 0.1.2
 *
 * @param  string  $column
 * @param  int     $post_id
 */
function wp_event_calendar_manage_custom_column_data( $column = '', $post_id = 0 ) {

	// Get post & metadata
	$post = get_post( $post_id );

	// Custom column IDs
	switch ( $column ) {

		// Creator
		case 'username' :
			echo get_avatar( $post->post_author, 32 );
			echo '<strong><a href="">' . get_userdata( $post->post_author )->display_name . '</a></strong>';
			break;

		// Type
		case 'type' :
			$terms = get_the_term_list( $post->ID, 'event-type', '', ', ', '' );
			if ( empty( $terms ) ) {
				$terms = '&mdash;';
			}
			echo $terms;
			break;

		// Starts
		case 'start' :

			// Date
			$date = get_post_meta( $post->ID, 'wp_event_calendar_date_time', true );
			if ( ! empty( $date ) ) {
				$date = strtotime( $date );

				echo date_i18n( get_option( 'date_format' ), $date );

				// Time
				$time = date_i18n( 'H:i:s', $date );
				if ( '00:00:00' !== $time  ) {
					echo '<br>'. date_i18n( get_option( 'time_format' ), $date );
				}

			// No start date
			} else {
				echo '&mdash;';
			}

			break;

		// Ends
		case 'end' :

			// Date
			$start_date = get_post_meta( $post->ID, 'wp_event_calendar_date_time',     true );
			$end_date   = get_post_meta( $post->ID, 'wp_event_calendar_end_date_time', true );
			if ( empty( $end_date ) || ( $start_date === $end_date ) ) {
				echo '&mdash;';
			} else {
				$end_date = strtotime( $end_date );

				echo date_i18n( get_option( 'date_format' ), $end_date );

				// Time
				$end_time = date_i18n( 'H:i:s', $end_date );
				if ( '00:00:00' !== $end_time  ) {
					echo '<br>'. date_i18n( get_option( 'time_format' ), $end_date );
				}
			}

			break;

		// Duration
		case 'duration' :
			$start_date = get_post_meta( $post->ID, 'wp_event_calendar_date_time',     true );
			$end_date   = get_post_meta( $post->ID, 'wp_event_calendar_end_date_time', true );
			if ( empty( $start_date ) || empty( $end_date ) || ( $start_date === $end_date ) ) {
				echo '&mdash;';
			} else {
				echo wp_event_calendar_human_diff_time( $start_date, $end_date );
			}
			break;

		// Repeat
		case 'repeat' :
			$repeat = get_post_meta( $post->ID, 'wp_event_calendar_repeat', true );
			switch( $repeat ) {
				case 'weekly' :
					esc_html_e( 'Weekly', 'wp-event-calendar' );
					break;
				case 'monthly' :
					esc_html_e( 'Monthly', 'wp-event-calendar' );
					break;
				case 'yearly' :
					esc_html_e( 'Yearly', 'wp-event-calendar' );
					break;
				case 'never' :
				default :
					esc_html_e( 'Never', 'wp-event-calendar' );
					break;
			}
			break;
	}
}

/**
 * Enqueue scripts
 *
 * @since 0.1.0
 */
function wp_event_calendar_admin_event_assets() {

	// Bail if not an event post type
	if ( 'event' !== get_post_type() ) {
		return;
	}

	// Enqueue the date picker
	wp_enqueue_script( 'jquery-ui-datepicker' );

	// Date picker CSS (for jQuery UI calendar)
	wp_enqueue_style( 'wp_event_calendar_datepicker', wp_event_calendar_get_plugin_url() . '/assets/css/datepicker.css', false, wp_event_calendar_get_asset_version(), false );
}

/**
 * Output admin area JS
 *
 * @since 0.1.2
 */
function wp_event_calendar_admin_js() {
	?>

	<script type="text/javascript">
		jQuery( document ).ready( function( $ ) {
			if ( $( '.wp_event_calendar_datepicker' ).length > 0 ) {
				var dateFormat = 'mm/dd/yy';
				$('.wp_event_calendar_datepicker').datepicker( {
					dateFormat: dateFormat
				} );
			}
			$( '.wp_event_calendar_minutes' ).keyup( function( e ) {
				return ( e < 10 ? '0' : '' ) + e;
			} );
		} );
	</script>

	<?php
}
