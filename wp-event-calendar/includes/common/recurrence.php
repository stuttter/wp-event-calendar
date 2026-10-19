<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Return the RRULE frequency for a stored repeat value.
 *
 * @since 1.2.0
 *
 * @param int|string $repeat Stored repeat value.
 * @return string
 */
function wp_event_calendar_get_recurrence_frequency( $repeat = 0 ) {
	$frequencies = array(
		10   => 'WEEKLY',
		100  => 'MONTHLY',
		1000 => 'YEARLY'
	);

	$repeat = (int) $repeat;

	return isset( $frequencies[ $repeat ] )
		? $frequencies[ $repeat ]
		: '';
}

/**
 * Return the site timezone name supported by DateTimeZone.
 *
 * @since 1.2.0
 *
 * @return string
 */
function wp_event_calendar_get_timezone_string() {
	if ( function_exists( 'wp_timezone_string' ) ) {
		return wp_timezone_string();
	}

	$timezone = get_option( 'timezone_string' );
	if ( ! empty( $timezone ) ) {
		return $timezone;
	}

	$offset  = (float) get_option( 'gmt_offset', 0 );
	$hours   = (int) $offset;
	$minutes = (int) abs( ( $offset - $hours ) * 60 );

	return sprintf( '%+03d:%02d', $hours, $minutes );
}

/**
 * Calculate enough iterations to reach the requested calendar range.
 *
 * @since 1.2.0
 *
 * @param string $frequency Recurrence frequency.
 * @param int    $start     Event start timestamp.
 * @param int    $before    End of requested range.
 * @return int
 */
function wp_event_calendar_get_recurrence_max( $frequency, $start, $before ) {
	$seconds = max( 0, $before - $start );

	switch ( $frequency ) {
		case 'WEEKLY':
			$iterations = (int) ceil( $seconds / ( 7 * DAY_IN_SECONDS ) );
			break;

		case 'MONTHLY':
			$iterations = (int) ceil( $seconds / ( 28 * DAY_IN_SECONDS ) );
			break;

		case 'YEARLY':
			$iterations = (int) ceil( $seconds / ( 365 * DAY_IN_SECONDS ) );
			break;

		default:
			$iterations = 0;
			break;
	}

	$maximum = max( 1000, min( 100000, $iterations + 10 ) );

	return (int) apply_filters( 'wp_event_calendar_recurrence_max', $maximum, $frequency, $start, $before );
}

/**
 * Expand an event into the occurrences overlapping a half-open date range.
 *
 * The range includes its start and excludes its end. Event duration is
 * preserved for every generated occurrence.
 *
 * @since 1.2.0
 *
 * @param array $args Event and range arguments.
 * @return array
 */
function wp_event_calendar_get_recurrence_occurrences( $args = array() ) {
	$r = wp_parse_args( $args, array(
		'id'       => 0,
		'start'    => '',
		'end'      => '',
		'repeat'   => 0,
		'expire'   => 0,
		'after'    => '',
		'before'   => '',
		'timezone' => wp_event_calendar_get_timezone_string()
	) );

	$start     = strtotime( $r['start'] );
	$end       = strtotime( $r['end'] );
	$after     = strtotime( $r['after'] );
	$before    = strtotime( $r['before'] );
	$frequency = wp_event_calendar_get_recurrence_frequency( $r['repeat'] );

	if ( empty( $start ) || empty( $after ) || empty( $before ) || ( $before <= $after ) ) {
		return array();
	}

	if ( empty( $end ) || ( $end < $start ) ) {
		$end = $start;
	}

	// A non-recurring event only needs the same overlap check as the query.
	if ( empty( $frequency ) ) {
		return ( ( $start < $before ) && ( $end > $after ) )
			? array( array(
				'dtstart' => date( 'Y-m-d H:i:s', $start ),
				'dtend'   => date( 'Y-m-d H:i:s', $end )
			) )
			: array();
	}

	$duration      = max( 0, $end - $start );
	$sequence_args = array(
		'id'       => $r['id'],
		'after'    => date( 'Y-m-d H:i:s', max( $start, $after - $duration ) ),
		'before'   => date( 'Y-m-d H:i:s', $before ),
		'tzid'     => $r['timezone'],
		'wkst'     => 'SU',
		'dtstart'  => date( 'Y-m-d H:i:s', $start ),
		'dtend'    => date( 'Y-m-d H:i:s', $end ),
		'freq'     => $frequency,
		'interval' => 1,
		'max'      => wp_event_calendar_get_recurrence_max( $frequency, $start, $before )
	);

	$weekdays  = array( 'SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA' );
	$week_start = (int) get_option( 'start_of_week', 0 );
	if ( isset( $weekdays[ $week_start ] ) ) {
		$sequence_args['wkst'] = $weekdays[ $week_start ];
	}

	if ( ! empty( $r['expire'] ) ) {
		$expire = is_numeric( $r['expire'] )
			? (int) $r['expire']
			: strtotime( $r['expire'] );

		if ( ! empty( $expire ) ) {
			$sequence_args['until'] = date( 'Y-m-d 23:59:59', $expire );
		}
	}

	$sequence_args = apply_filters( 'wp_event_calendar_recurrence_sequence_args', $sequence_args, $r );
	$sequence      = new \WP_Event_Calendar\Utilities\iCalendar\Recur\Sequence( $sequence_args );
	$occurrences   = array();

	while ( $occurrence = $sequence->next() ) {
		$occurrence_start = strtotime( $occurrence['dtstart'] );
		$occurrence_end   = strtotime( $occurrence['dtend'] );

		// Enforce half-open calendar ranges even for expanded RRULE dates.
		if ( ( $occurrence_start >= $before ) || ( $occurrence_end <= $after ) ) {
			continue;
		}

		$occurrences[] = $occurrence;
	}

	return apply_filters( 'wp_event_calendar_recurrence_occurrences', $occurrences, $r, $sequence_args );
}
