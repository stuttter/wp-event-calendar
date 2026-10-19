<?php

use PHPUnit\Framework\TestCase;
use WP_Event_Calendar\Utilities\iCalendar\Recur\Sequence;

class WPEventCalendarRecurrenceTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['wp_event_calendar_test_options'] = array(
			'gmt_offset'   => 0,
			'start_of_week' => 0,
			'timezone_string' => 'UTC'
		);
	}

	public function test_old_weekly_event_reaches_current_calendar_range() {
		$occurrences = wp_event_calendar_get_recurrence_occurrences( array(
			'start'  => '2015-01-05 09:00:00',
			'end'    => '2015-01-05 10:00:00',
			'repeat' => 10,
			'after'  => '2026-10-01 00:00:00',
			'before' => '2026-11-01 00:00:00'
		) );

		$this->assertSame(
			array( '2026-10-05', '2026-10-12', '2026-10-19', '2026-10-26' ),
			array_map( array( $this, 'occurrence_date' ), $occurrences )
		);
	}

	public function test_monthly_recurrence_skips_dates_missing_from_month() {
		$occurrences = wp_event_calendar_get_recurrence_occurrences( array(
			'start'  => '2026-01-31 09:00:00',
			'end'    => '2026-01-31 10:00:00',
			'repeat' => 100,
			'after'  => '2026-01-01 00:00:00',
			'before' => '2026-04-01 00:00:00'
		) );

		$this->assertSame(
			array( '2026-01-31', '2026-03-31' ),
			array_map( array( $this, 'occurrence_date' ), $occurrences )
		);
	}

	public function test_yearly_leap_day_recurrence_skips_invalid_dates() {
		$occurrences = wp_event_calendar_get_recurrence_occurrences( array(
			'start'  => '2024-02-29 09:00:00',
			'end'    => '2024-02-29 10:00:00',
			'repeat' => 1000,
			'after'  => '2024-01-01 00:00:00',
			'before' => '2030-01-01 00:00:00'
		) );

		$this->assertSame(
			array( '2024-02-29', '2028-02-29' ),
			array_map( array( $this, 'occurrence_date' ), $occurrences )
		);
	}

	public function test_weekly_recurrence_preserves_local_time_across_dst() {
		$occurrences = wp_event_calendar_get_recurrence_occurrences( array(
			'start'    => '2026-03-01 09:00:00',
			'end'      => '2026-03-01 10:00:00',
			'repeat'   => 10,
			'after'    => '2026-03-01 00:00:00',
			'before'   => '2026-03-23 00:00:00',
			'timezone' => 'America/Chicago'
		) );

		$this->assertSame(
			array( '09:00:00', '09:00:00', '09:00:00', '09:00:00' ),
			array_map( array( $this, 'occurrence_time' ), $occurrences )
		);
	}

	public function test_calendar_end_boundary_is_exclusive() {
		$occurrences = wp_event_calendar_get_recurrence_occurrences( array(
			'start'  => '2026-03-01 00:00:00',
			'end'    => '2026-03-01 01:00:00',
			'repeat' => 100,
			'after'  => '2026-03-01 00:00:00',
			'before' => '2026-04-01 00:00:00'
		) );

		$this->assertSame( array( '2026-03-01' ), array_map( array( $this, 'occurrence_date' ), $occurrences ) );
	}

	public function test_expiration_date_is_inclusive() {
		$occurrences = wp_event_calendar_get_recurrence_occurrences( array(
			'start'  => '2026-10-05 09:00:00',
			'end'    => '2026-10-05 10:00:00',
			'repeat' => 10,
			'expire' => '2026-10-19',
			'after'  => '2026-10-01 00:00:00',
			'before' => '2026-11-01 00:00:00'
		) );

		$this->assertSame(
			array( '2026-10-05', '2026-10-12', '2026-10-19' ),
			array_map( array( $this, 'occurrence_date' ), $occurrences )
		);
	}

	public function test_final_occurrence_can_overlap_view_after_expiration_date() {
		$occurrences = wp_event_calendar_get_recurrence_occurrences( array(
			'start'  => '2026-10-01 09:00:00',
			'end'    => '2026-10-11 10:00:00',
			'repeat' => 10,
			'expire' => '2026-10-05',
			'after'  => '2026-10-10 00:00:00',
			'before' => '2026-10-12 00:00:00'
		) );

		$this->assertCount( 1, $occurrences );
		$this->assertSame( '2026-10-01 09:00:00', $occurrences[0]['dtstart'] );
		$this->assertSame( '2026-10-11 10:00:00', $occurrences[0]['dtend'] );
	}

	public function test_array_exception_dates_are_supported() {
		$sequence = new Sequence( array(
			'dtstart' => '2026-10-05 09:00:00',
			'dtend'   => '2026-10-05 10:00:00',
			'freq'    => 'WEEKLY',
			'before'  => '2026-10-26 08:59:59',
			'exdate'  => array( '2026-10-12 09:00:00' ),
			'tzid'    => 'UTC'
		) );
		$dates = array();

		while ( $occurrence = $sequence->next() ) {
			$dates[] = substr( $occurrence['dtstart'], 0, 10 );
		}

		$this->assertSame( array( '2026-10-05', '2026-10-19' ), $dates );
	}

	public function test_ics_recurrence_uses_expiration_instead_of_event_end() {
		$expiration = strtotime( '2026-10-19 00:00:00' );
		$event      = new WP_Event_Calendar_Event(
			'2026-10-05 09:00:00',
			'2026-10-05 10:00:00',
			'Weekly event',
			'',
			'',
			10,
			'EventScheduled',
			$expiration
		);
		$ical = (string) $event;

		$this->assertStringContainsString( 'DTEND:20261005T100000', $ical );
		$this->assertStringContainsString( 'RRULE:FREQ=WEEKLY;UNTIL=20261019T235959', $ical );
	}

	public function test_unbounded_ics_recurrence_has_no_until_value() {
		$event = new WP_Event_Calendar_Event(
			'2026-10-05 09:00:00',
			'2026-10-05 10:00:00',
			'Weekly event',
			'',
			'',
			10
		);
		$ical = (string) $event;

		$this->assertStringContainsString( 'RRULE:FREQ=WEEKLY', $ical );
		$this->assertStringNotContainsString( 'UNTIL=', $ical );
	}

	public function test_event_query_includes_recurring_sources() {
		$query = wp_event_calendar_get_meta_query( array(
			'start' => '2026-10-01 00:00:00',
			'end'   => '2026-11-01 00:00:00'
		) );
		$recurring = $query['wp_event_calendar_recurring_clause'];

		$this->assertSame( 'OR', $query['relation'] );
		$this->assertSame( array( 10, 100, 1000 ), $recurring['recurring_frequency_clause']['value'] );
		$this->assertArrayNotHasKey( 'recurring_expiration_clause', $recurring );
	}

	public function occurrence_date( $occurrence ) {
		return substr( $occurrence['dtstart'], 0, 10 );
	}

	public function occurrence_time( $occurrence ) {
		return substr( $occurrence['dtstart'], 11, 8 );
	}
}
