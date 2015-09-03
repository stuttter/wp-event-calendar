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
function wp_event_calendar_add_dropdown_filters( $post_type = '' ) {

	// Bail if not the event post type
	if ( 'event' !== $post_type ) {
		return;
	}

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
		'cb'         => '<input type="checkbox" />',
		'title'      => esc_html__( 'Event',      'wp-event-calendar' ),
		'start'      => esc_html__( 'Starts',     'wp-event-calendar' ),
		'end'        => esc_html__( 'Ends',       'wp-event-calendar' ),
		'duration'   => esc_html__( 'Duration',   'wp-event-calendar' ),
		'repeat'     => esc_html__( 'Repeat',     'wp-event-calendar' ),
		'categories' => esc_html__( 'Categories', 'wp-event-calendar' ),
		'types'      => esc_html__( 'Types',      'wp-event-calendar' ),
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

		// Type
		case 'types' :
			echo wp_get_event_taxonomy_column_data( $post, 'event-type' );
			break;

		// Category
		case 'categories' :
			echo wp_get_event_taxonomy_column_data( $post, 'event-category' );
			break;

		// Starts
		case 'start' :
			echo wp_get_event_start_date_time( $post );
			break;

		// Ends
		case 'end' :
			echo wp_get_event_end_date_time( $post );
			break;

		// Duration
		case 'duration' :
			echo wp_get_event_duration( $post );
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

function wp_get_event_taxonomy_column_data( $post = false, $taxonomy = '' ) {

	// Get post & taxonomy
	$post            = get_post( $post );
	$taxonomy_object = get_taxonomy( $taxonomy );
	$terms           = get_the_terms( $post->ID, $taxonomy );

	// Has terms
	if ( is_array( $terms ) ) {
		$out = array();
		foreach ( $terms as $t ) {
			$posts_in_term_qv = array();
			if ( 'post' != $post->post_type ) {
				$posts_in_term_qv['post_type'] = $post->post_type;
			}
			if ( $taxonomy_object->query_var ) {
				$posts_in_term_qv[ $taxonomy_object->query_var ] = $t->slug;
			} else {
				$posts_in_term_qv['taxonomy'] = $taxonomy;
				$posts_in_term_qv['term']     = $t->slug;
			}

			$out[] = sprintf( '<a href="%s">%s</a>',
				esc_url( add_query_arg( $posts_in_term_qv, 'edit.php' ) ),
				esc_html( sanitize_term_field( 'name', $t->name, $t->term_id, $taxonomy, 'display' ) )
			);
		}
		/* translators: used between list items, there is a space after the comma */
		$retval = join( __( ', ' ), $out );

	// No terms
	} else {
		$retval = '<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">' . esc_html( $taxonomy_object->labels->no_terms ) . '</span>';
	}

	return apply_filters( 'wp_event_calendar_taxonomy_column_data', $retval, $post, $taxonomy );
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

	$url = wp_event_calendar_get_plugin_url();
	$ver = wp_event_calendar_get_asset_version();

	// Date picker CSS (for jQuery UI calendar)
	wp_enqueue_style( 'wp_event_calendar_datepicker', $url . '/assets/css/datepicker.css', false,             $ver, false );

	// Datepicker & event JS
	wp_enqueue_script( 'wp_event_calendar_all_event', $url . '/assets/js/event.js',        array( 'jquery' ), $ver, true  );
}
