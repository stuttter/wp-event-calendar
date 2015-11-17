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
	if ( 'event' === $post->post_type ) {
		$title = esc_html__( 'Name this event', 'wp-event-calendar' );
	}

	// Return possibly modified title
	return $title;
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
		'cb'               => '<input type="checkbox" />',
		'title'            => esc_html__( 'Event',      'wp-event-calendar' ),
		'start'            => esc_html__( 'Starts',     'wp-event-calendar' ),
		'end'              => esc_html__( 'Ends',       'wp-event-calendar' ),
		'duration'         => esc_html__( 'Duration',   'wp-event-calendar' ),
		'repeat'           => esc_html__( 'Repeat',     'wp-event-calendar' ),
		'event-categories' => esc_html__( 'Categories', 'wp-event-calendar' ),
		'event-types'      => esc_html__( 'Types',      'wp-event-calendar' ),
	);

	// Filter & return
	return apply_filters( 'wp_event_calendar_manage_posts_columns', $new_columns, $old_columns );
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
function wp_event_calendar_sortable_columns( $columns = array() ) {

	// Override columns
	$columns = array(
		'title'    => 'title',
		'start'    => 'start_date',
		'end'      => 'end_date',
		'repeat'   => 'repeat'
	);

	return $columns;
}

/**
 * Set the relevant query vars for sorting posts by our front-end sortables.
 *
 * @since 0.1.6
 *
 * @param WP_Query $wp_query The current WP_Query object.
 */
function wp_event_calendar_maybe_sort_by_fields( WP_Query $wp_query ) {

	// Bail if not 'event' post type
	if ( empty( $wp_query->query['post_type'] ) || ! in_array( 'event', (array) $wp_query->query['post_type'] ) ) {
		return;
	}

	// Some default order values
	$order = ! empty( $wp_query->query['order'] )
		? strtoupper( $wp_query->query['order'] )
		: 'DESC';

	// Bail if no orderby
	if ( empty( $wp_query->query['orderby'] ) ) {
		return;
	}

	// Set by 'orderby'
	switch ( $wp_query->query['orderby'] ) {

		// End
		case 'repeat' :
			$wp_query->set( 'order',     $order );
			$wp_query->set( 'orderby',   'meta_value' );
			$wp_query->set( 'meta_key',  'wp_event_calendar_repeat' );
			$wp_query->set( 'meta_type', 'NUMERIC' );
			break;

		// End
		case 'end_date' :
			$wp_query->set( 'order',     $order );
			$wp_query->set( 'orderby',   'meta_value' );
			$wp_query->set( 'meta_key',  'wp_event_calendar_end_date_time' );
			$wp_query->set( 'meta_type', 'DATETIME' );
			break;

		// Start (default)
		case 'start_date' :
			$wp_query->set( 'order',     $order );
			$wp_query->set( 'orderby',   'meta_value' );
			$wp_query->set( 'meta_key',  'wp_event_calendar_date_time' );
			$wp_query->set( 'meta_type', 'DATETIME' );
			break;
	}
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
		case 'event-types' :
			echo wp_get_event_taxonomy_column_data( $post, 'event-type' );
			break;

		// Category
		case 'event-categories' :
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
				case '1000' :
					esc_html_e( 'Yearly', 'wp-event-calendar' );
					break;
				case '100' :
					esc_html_e( 'Monthly', 'wp-event-calendar' );
					break;
				case '10' :
					esc_html_e( 'Weekly', 'wp-event-calendar' );
					break;
				case '0' :
				default :
					esc_html_e( 'Never', 'wp-event-calendar' );
					break;
			}
			break;
	}
}

/**
 * Helper function for getting column data for event taxonomies
 *
 * @since 0.1.5
 *
 * @param   int     $post
 * @param   string  $taxonomy
 * @return  string
 */
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
	wp_enqueue_style( 'wp_event_calendar_datepicker', $url . 'assets/css/datepicker.css', false,             $ver, false );

	// Datepicker & event JS
	wp_enqueue_script( 'wp_event_calendar_all_event', $url . 'assets/js/event.js',        array( 'jquery' ), $ver, true  );
}
