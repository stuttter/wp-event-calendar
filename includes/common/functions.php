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
	return apply_filters( 'wp_event_calendar_allowed_post_types', array(
		'event'
	) );
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

	if ( !empty( $older_date ) && !is_numeric( $older_date ) ) {
		$time_chunks = explode( ':', str_replace( ' ', ':', $older_date ) );
		$date_chunks = explode( '-', str_replace( ' ', '-', $older_date ) );
		$older_date  = gmmktime( (int) $time_chunks[1], (int) $time_chunks[2], (int) $time_chunks[3], (int) $date_chunks[1], (int) $date_chunks[2], (int) $date_chunks[0] );
	}

	/**
	 * $newer_date will equal false if we want to know the time elapsed between
	 * a date and the current time. $newer_date will have a value if we want to
	 * work out time elapsed between two known dates.
	 */
	$newer_date = ( !$newer_date ) ? bp_core_current_time( true, 'timestamp' ) : $newer_date;

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
			$output = sprintf( _n( '%s year',   '%s years',   $count, 'wp-event-calander' ), $count );
			break;
		case 30 * DAY_IN_SECONDS :
			$output = sprintf( _n( '%s month',  '%s months',  $count, 'wp-event-calander' ), $count );
			break;
		case WEEK_IN_SECONDS :
			$output = sprintf( _n( '%s week',   '%s weeks',   $count, 'wp-event-calander' ), $count );
			break;
		case DAY_IN_SECONDS :
			$output = sprintf( _n( '%s day',    '%s days',    $count, 'wp-event-calander' ), $count );
			break;
		case HOUR_IN_SECONDS :
			$output = sprintf( _n( '%s hour',   '%s hours',   $count, 'wp-event-calander' ), $count );
			break;
		case MINUTE_IN_SECONDS :
			$output = sprintf( _n( '%s minute', '%s minutes', $count, 'wp-event-calander' ), $count );
			break;
		default:
			$output = sprintf( _n( '%s second', '%s seconds', $count, 'wp-event-calander' ), $count );
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
			$output .= _x( ',', 'Separator in time since', 'wp-event-calander' ) . ' ';

			switch ( $seconds2 ) {
				case 30 * DAY_IN_SECONDS :
					$output .= sprintf( _n( '%s month',  '%s months',  $count2, 'wp-event-calander' ), $count2 );
					break;
				case WEEK_IN_SECONDS :
					$output .= sprintf( _n( '%s week',   '%s weeks',   $count2, 'wp-event-calander' ), $count2 );
					break;
				case DAY_IN_SECONDS :
					$output .= sprintf( _n( '%s day',    '%s days',    $count2, 'wp-event-calander' ), $count2 );
					break;
				case HOUR_IN_SECONDS :
					$output .= sprintf( _n( '%s hour',   '%s hours',   $count2, 'wp-event-calander' ), $count2 );
					break;
				case MINUTE_IN_SECONDS :
					$output .= sprintf( _n( '%s minute', '%s minutes', $count2, 'wp-event-calander' ), $count2 );
					break;
				default:
					$output .= sprintf( _n( '%s second', '%s seconds', $count2, 'wp-event-calander' ), $count2 );
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
