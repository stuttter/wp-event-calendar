<?php

/**
 * Calendar Functions
 *
 * @package Calendar/Common/Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return an array of the post types that calendars are available on
 *
 * You can filter this to enable a post calendar for just about any kind of
 * post type with an interface.
 *
 * @since 0.1.0
 *
 * @return array
 */
function wp_event_calendar_allowed_post_types() {
	global $_wp_post_type_features;

	// Get which post types support events
	$supports = wp_filter_object_list( $_wp_post_type_features, array( 'events' => true ) );
	$types    = array_keys( $supports );

	// Filter & return
	return apply_filters( 'wp_event_calendar_allowed_post_types', $types, $supports );
}

/**
 * Get the current admin post type
 *
 * @since 0.1.0
 *
 * @return string
 */
function wp_event_calendar_get_admin_post_type() {

	// Use $typenow global if it's not empty
	if ( ! empty( $GLOBALS['typenow'] ) ) {
		return $GLOBALS['typenow'];
	}

	// Use $GET post_type if it's not empty
	if ( ! empty( $_GET['post_type'] ) ) {
		return wp_unslash( $_GET['post_type'] );
	}

	// Use get parameter
	return 'post';
}


/**
 * Get a human readable representation of the time elapsed since a given date.
 *
 * Based on function created by Dunstan Orchard - http://1976design.com
 *
 * This function will return a read representation of the time elapsed
 * since a given date.
 * eg: 2 hours and 50 minutes
 * eg: 4 days
 * eg: 4 weeks and 6 days
 *
 * Note that fractions of minutes are not represented in the return string. So
 * an interval of 3 minutes will be represented by "3 minutes", as will an
 * interval of 3 minutes 59 seconds.
 *
 * @param int|string $older_date The earlier time from which you're calculating
 *                               the time elapsed. Enter either as an integer Unix timestamp,
 *                               or as a date string of the format 'Y-m-d h:i:s'.
 * @param int|bool   $newer_date Optional. Unix timestamp of date to compare older
 *                               date to. Default: false (current time).
 *
 * @return string String representing the time since the older date, eg
 *         "2 hours and 50 minutes".
 */
function wp_event_calendar_human_diff_time( $older_date, $newer_date = false ) {

	// Format
	if ( ! is_numeric( $older_date ) ) {
		$older_date = strtotime( $older_date );
	}

	if ( ! is_numeric( $newer_date ) ) {
		$newer_date = strtotime( $newer_date );
	}

	// Catch issues with flipped old vs. new dates
	$flipped = false;

	// array of time period chunks
	$chunks = array(
		YEAR_IN_SECONDS,
		30 * DAY_IN_SECONDS,
		WEEK_IN_SECONDS,
		DAY_IN_SECONDS,
		HOUR_IN_SECONDS,
		MINUTE_IN_SECONDS,
		1
	);

	if ( ! empty( $older_date ) && ! is_numeric( $older_date ) ) {
		$time_chunks = explode( ':', str_replace( ' ', ':', $older_date ) );
		$date_chunks = explode( '-', str_replace( ' ', '-', $older_date ) );
		$older_date  = gmmktime( (int) $time_chunks[1], (int) $time_chunks[2], (int) $time_chunks[3], (int) $date_chunks[1], (int) $date_chunks[2], (int) $date_chunks[0] );
	}

	/**
	 * $newer_date will equal false if we want to know the time elapsed between
	 * a date and the current time. $newer_date will have a value if we want to
	 * work out time elapsed between two known dates.
	 */
	$newer_date = empty( $newer_date )
		? current_time( 'timestamp' )
		: $newer_date;

	// Difference in seconds
	$since = $newer_date - $older_date;

	// Flipped
	if ( $since < 0 ) {
		$flipped = true;
		$since   = $older_date - $newer_date;
	}

	// Step one: the first chunk
	for ( $i = 0, $j = count( $chunks ); $i < $j; ++$i ) {
		$seconds = $chunks[$i];

		// Finding the biggest chunk (if the chunk fits, break)
		$count = floor( $since / $seconds );
		if ( 0 != $count ) {
			break;
		}
	}

	// Set output var
	switch ( $seconds ) {
		case YEAR_IN_SECONDS :
			$output = sprintf( _n( '%s year',   '%s years',   $count, 'wp-event-calendar' ), $count );
			break;
		case 30 * DAY_IN_SECONDS :
			$output = sprintf( _n( '%s month',  '%s months',  $count, 'wp-event-calendar' ), $count );
			break;
		case WEEK_IN_SECONDS :
			$output = sprintf( _n( '%s week',   '%s weeks',   $count, 'wp-event-calendar' ), $count );
			break;
		case DAY_IN_SECONDS :
			$output = sprintf( _n( '%s day',    '%s days',    $count, 'wp-event-calendar' ), $count );
			break;
		case HOUR_IN_SECONDS :
			$output = sprintf( _n( '%s hour',   '%s hours',   $count, 'wp-event-calendar' ), $count );
			break;
		case MINUTE_IN_SECONDS :
			$output = sprintf( _n( '%s minute', '%s minutes', $count, 'wp-event-calendar' ), $count );
			break;
		default:
			$output = sprintf( _n( '%s second', '%s seconds', $count, 'wp-event-calendar' ), $count );
	}

	// Step two: the second chunk
	// A quirk in the implementation means that this
	// condition fails in the case of minutes and seconds.
	// We've left the quirk in place, since fractions of a
	// minute are not a useful piece of information for our
	// purposes
	if ( $i + 2 < $j ) {
		$seconds2 = $chunks[$i + 1];
		$count2   = floor( ( $since - ( $seconds * $count ) ) / $seconds2 );

		// Add to output var
		if ( 0 != $count2 ) {
			$output .= _x( ',', 'Separator in time since', 'wp-event-calendar' ) . ' ';

			switch ( $seconds2 ) {
				case 30 * DAY_IN_SECONDS :
					$output .= sprintf( _n( '%s month',  '%s months',  $count2, 'wp-event-calendar' ), $count2 );
					break;
				case WEEK_IN_SECONDS :
					$output .= sprintf( _n( '%s week',   '%s weeks',   $count2, 'wp-event-calendar' ), $count2 );
					break;
				case DAY_IN_SECONDS :
					$output .= sprintf( _n( '%s day',    '%s days',    $count2, 'wp-event-calendar' ), $count2 );
					break;
				case HOUR_IN_SECONDS :
					$output .= sprintf( _n( '%s hour',   '%s hours',   $count2, 'wp-event-calendar' ), $count2 );
					break;
				case MINUTE_IN_SECONDS :
					$output .= sprintf( _n( '%s minute', '%s minutes', $count2, 'wp-event-calendar' ), $count2 );
					break;
				default:
					$output .= sprintf( _n( '%s second', '%s seconds', $count2, 'wp-event-calendar' ), $count2 );
			}
		}
	}

	if ( true === $flipped ) {
		$output = '-' . $output;
	}

	/**
	 * Filters the human readable representation of the time elapsed since a
	 * given date.
	 *
	 * @since 0.1.2
	 *
	 * @param string $output     Final string
	 * @param string $older_date Earlier time from which we're calculating time elapsed
	 * @param string $newer_date Unix timestamp of date to compare older time to
	 */
	return apply_filters( 'wp_event_calendar_human_diff_time', $output, $older_date, $newer_date );
}

/**
 * Return the start date & time of an event
 *
 * @since 0.1.5
 *
 * @param  mixed $post
 *
 * @return string
 */
function wp_get_event_start_date_time( $post = false ) {

	// Get the post object & start date
	$post    = get_post( $post );
	$date    = get_post_meta( $post->ID, 'wp_event_calendar_date_time', true );
	$all_day = get_post_meta( $post->ID, 'wp_event_calendar_all_day',   true );

	// Start an output buffer
	ob_start();

	if ( ! empty( $date ) ) {
		$date = strtotime( $date );
		$df   = get_option( 'date_format' );
		$tf   = get_option( 'time_format' );

		echo date_i18n( $df, $date );

		// Time
		if ( empty( $all_day ) ) {
			echo '<br>'. date_i18n( $tf, $date );
		}

	// No start date
	} else {
		echo '&mdash;';
	}

	// Get the output buffer
	$retval = ob_get_clean();

	// Filter & return
	return apply_filters( 'wp_get_event_start_date_time', $retval, $post, $date );
}

/**
 * Return the end date & time of an event
 *
 * @since 0.1.5
 *
 * @param  mixed $post
 *
 * @return string
 */
function wp_get_event_end_date_time( $post = false ) {

	// Get the post object & start date
	$post    = get_post( $post );
	$date    = get_post_meta( $post->ID, 'wp_event_calendar_end_date_time', true );
	$all_day = get_post_meta( $post->ID, 'wp_event_calendar_all_day',       true );

	// Start an output buffer
	ob_start();

	if ( ! empty( $date ) ) {
		$date = strtotime( $date );
		$df   = get_option( 'date_format' );
		$tf   = get_option( 'time_format' );

		echo date_i18n( $df, $date );

		// Time
		if ( empty( $all_day ) ) {
			echo '<br>'. date_i18n( $tf, $date );
		}

	} else {
		echo '&mdash;';
	}

	// Get the output buffer
	$retval = ob_get_clean();

	// Filter & return
	return apply_filters( 'wp_get_event_end_date_time', $retval, $post, $date );
}

/**
 * Return the duration of an event
 *
 * @since 0.1.5
 *
 * @param  mixed $post
 *
 * @return string
 */
function wp_get_event_duration( $post = false ) {

	// Get the post object & start date
	$post       = get_post( $post );
	$all_day    =     (bool) get_post_meta( $post->ID, 'wp_event_calendar_all_day',       true );
	$start_date = strtotime( get_post_meta( $post->ID, 'wp_event_calendar_date_time',     true ) );
	$end_date   = strtotime( get_post_meta( $post->ID, 'wp_event_calendar_end_date_time', true ) );

	// Start an output buffer
	ob_start();

	// All day event
	if ( true === $all_day ) {

		// 1 day
		if ( date( 'd', $start_date ) === date( 'd', $end_date ) ) {
			esc_html_e( 'All Day', 'wp-event-calendar' );

		// More than 1 day
		} else {
			echo wp_event_calendar_human_diff_time(
				strtotime( 'midnight', $start_date ),
				strtotime( 'midnight', $end_date )
			);
		}

	// Specific times
	} else {
		echo wp_event_calendar_human_diff_time( $start_date, $end_date );
	}

	// Get the output buffer
	$retval = ob_get_clean();

	// Filter & return
	return apply_filters( 'wp_get_event_end_date_time', $retval, $post, $all_day, $start_date, $end_date );
}

/**
 * Return array of hours
 *
 * @since 0.2.4
 *
 * @return array
 */
function wp_event_calendar_get_hours() {
	return apply_filters( 'wp_event_calendar_get_hours', array(
		'01',
		'02',
		'03',
		'04',
		'05',
		'06',
		'07',
		'08',
		'09',
		'10',
		'11',
		'12'
	) );
}

/**
 * Return array of minutes
 *
 * @since 0.2.4
 *
 * @return array
 */
function wp_event_calendar_get_minutes() {
	return apply_filters( 'wp_event_calendar_get_minutes', array(
		'00',
		'05',
		'10',
		'15',
		'20',
		'25',
		'30',
		'35',
		'40',
		'45',
		'50',
		'55'
	) );
}

/**
 * Output a select dropdown for hours & minutes
 *
 * @since 0.2.4
 *
 * @param array $args
 */
function wp_event_calendar_time_dropdown( $args = array() ) {

	// Parse the arguments
	$r = wp_parse_args( $args, array(
		'first'       => esc_html( 'Select One', 'wp-event-calendar' ),
		'placeholder' => '&nbsp;',
		'id'          => '',
		'name'        => '',
		'class'       => '',
		'items'       => array(),
		'selected'    => '',
		'multi'       => false,
		'echo'        => true,
		'width'       => 55
	) );

	// Is multi?
	$multi = ( true === $r['multi'] )
		? 'multi'
		: '';

	// Start an output buffer
	ob_start();

	// Start the select wrapper
	?><select data-placeholder="<?php echo esc_html( $r['placeholder'] ); ?>" name="<?php echo esc_attr( $r['name'] ); ?>" id="<?php echo esc_attr( $r['id'] ); ?>" class="<?php echo esc_attr( $r['class'] ); ?>" <?php echo $multi; ?> style="width: <?php echo esc_attr( $r['width'] ); ?>px;" ><?php

		// First item?
		if ( false !== $r['first'] ) : ?><option value=""><?php echo esc_html( $r['first'] ); ?></option><?php endif;

		// Loop through items
		foreach ( $r['items'] as $item ) :

			?><option value="<?php echo esc_attr( $item ); ?>" <?php selected( $r['selected'], $item ); ?>><?php echo esc_html( $item ); ?></option>

		<?php

		endforeach;

	?></select><?php

	// Output or return
	( true === $r['echo'] )
		? ob_end_flush()
		: ob_end_clean();
}

/**
 * Query for events
 *
 * @since 0.4.0
 *
 * @param array $args See WP_Query
 *
 * @return array Array of post objects
 */
function wp_get_events( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'post_type'           => wp_event_calendar_allowed_post_types(),
		'post_status'         => array( 'publish', 'future' ),
		'posts_per_page'      => -1,
		'orderby'             => 'meta_value',
		'order'               => 'ASC',
		'hierarchical'        => false,
		'ignore_sticky_posts' => true,
		'suppress_filters'    => true,
		'no_found_rows'       => true,
		'meta_query'          => wp_event_calendar_get_meta_query()
	) );

	// Query for events
	$query = new WP_Query( $r );

	// Return posts
	return $query->posts;
}

/**
 * Get meta_query argument for a WP_Query
 *
 * Okay; let's talk this out here in regular English, because otherwise it
 * barely makes any sense at all and you just get lost trying to remember:
 *
 * - Ranges are: day, week, month, custom
 * - Look for:
 *     - Events starting & ending within the current range
 *     - Events starting before and ending anytime after current range
 *     - Events starting before and ending within the current range
 *     - Events starting within and ending anytime after the current range
 *
 * @since 0.4.0
 *
 * @param array $args {
 *     @type string $mode  month|week|day
 *     @type string $start Start time in mysql format
 *     @type string $end   End time in mysql format
 * }
 *
 * @return array 'meta_query' used for WP_Query
 */
function wp_event_calendar_get_meta_query( $args = array() ) {

	// Parse arguments
	$r = wp_parse_args( $args, array(
		'mode'  => 'month',
		'start' => '',
		'end'   => ''
	) );

	// They're all the same now!
	$retval = array(
		'wp_event_calendar_clause' => array(
			'relation' => 'AND',
			'within_range_clause' => array(
				'relation' => 'AND',
				'start_between_clause' => array(
					'key'     => 'wp_event_calendar_date_time',
					'value'   => $r['end'],
					'type'    => 'DATETIME',
					'compare' => '<'
				),
				'end_between_clause' => array(
					'key'     => 'wp_event_calendar_end_date_time',
					'value'   => $r['start'],
					'type'    => 'DATETIME',
					'compare' => '>'
				)
			)
		)
	);

	return apply_filters( 'wp_event_calendar_get_meta_query', $retval, $r, $args );
}

/**
 * Get an array of WP_Event_Calendar_Event objects
 *
 * @since 0.4.0
 *
 * @param array $args
 */
function wp_event_calendar_get_events( $args = array() ) {

	// Get posts & define default
	$posts  = wp_get_events( $args );
	$events = array();

	// Loop through events and create the object
	foreach ( $posts as $post ) {
		$events[] = wp_event_calendar_post_to_event( $post );
	}

	return apply_filters( 'wp_event_calendar_get_events', $events, $posts, $args );
}

/**
 * Convert a WP_Post object to a WP_Event_Calendar_Event object
 *
 * @since 0.4.0
 *
 * @param WP_Post $post
 *
 * @return \WP_Event_Calendar_Event
 */
function wp_event_calendar_post_to_event( WP_Post $post ) {

	// Core
	$title    = $post->post_title;
	$content  = $post->post_content;

	// Meta
	$start    = get_post_meta( $post->ID, 'wp_event_calendar_date_time',     true );
	$end      = get_post_meta( $post->ID, 'wp_event_calendar_end_date_time', true );
	$location = get_post_meta( $post->ID, 'wp_event_calendar_location',      true );
	$repeat   = get_post_meta( $post->ID, 'wp_event_calendar_repeat',        true );

	// Return object
	return new WP_Event_Calendar_Event( $start, $end, $title, $content, $location, $repeat );
}
