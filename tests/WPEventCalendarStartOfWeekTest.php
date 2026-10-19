<?php

use PHPUnit\Framework\TestCase;

class WPEventCalendarStartOfWeekTest extends TestCase {

	public function test_columns_begin_with_configured_start_of_week() {
		$expected_days = array(
			'sunday',
			'monday',
			'tuesday',
			'wednesday',
			'thursday',
			'friday',
			'saturday'
		);

		foreach ( $expected_days as $start_of_week => $first_day ) {
			$table   = new WP_Event_Calendar_Test_List_Table( $start_of_week );
			$columns = array_keys( $table->get_columns() );
			$expected = array_merge(
				array_slice( $expected_days, $start_of_week ),
				array_slice( $expected_days, 0, $start_of_week )
			);

			$this->assertSame( $first_day, $columns[0] );
			$this->assertSame( $expected, $columns );
		}
	}

	public function test_weekday_offsets_follow_configured_start_of_week() {
		$table = new WP_Event_Calendar_Test_List_Table( 1 );

		$this->assertSame( 0, $table->weekday_offset( 1 ) );
		$this->assertSame( 5, $table->weekday_offset( 6 ) );
		$this->assertSame( 6, $table->weekday_offset( 0 ) );
	}

	public function test_cell_classes_follow_configured_start_of_week() {
		$table = new WP_Event_Calendar_Test_List_Table( 1 );

		$this->assertStringContainsString( 'monday', $table->day_classes( 0, 0 ) );
		$this->assertStringContainsString( 'sunday', $table->day_classes( 6, 0 ) );
	}

	public function test_week_start_contains_requested_date() {
		$timestamp = gmmktime( 12, 0, 0, 10, 14, 2026 );
		$expected  = array(
			'2026-10-11',
			'2026-10-12',
			'2026-10-13',
			'2026-10-14',
			'2026-10-08',
			'2026-10-09',
			'2026-10-10'
		);

		foreach ( $expected as $start_of_week => $date ) {
			$table = new WP_Event_Calendar_Test_List_Table( $start_of_week );

			$this->assertSame( $date, gmdate( 'Y-m-d', $table->week_start( $timestamp ) ) );
		}
	}

	public function test_week_view_uses_configured_boundaries_and_columns() {
		$GLOBALS['wp_event_calendar_test_options']['start_of_week'] = 1;
		$_REQUEST = array(
			'cy' => 2026,
			'cm' => 10,
			'cd' => 14
		);

		$table   = new WP_Event_Calendar_Test_Week_Table();
		$columns = $table->get_columns();

		$this->assertSame( '2026-10-12', gmdate( 'Y-m-d', $table->boundary( 'week_start' ) ) );
		$this->assertSame( '2026-10-18', gmdate( 'Y-m-d', $table->boundary( 'week_end' ) ) );
		$this->assertSame( '2026-10-19 00:00:00', $table->view_end );
		$this->assertSame(
			array( 'hour', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ),
			array_keys( $columns )
		);
		$this->assertSame( 'Mon, Oct. 12', $columns['monday'] );
		$this->assertSame( 'Sun, Oct. 18', $columns['sunday'] );
	}
}

class WP_Event_Calendar_Test_List_Table extends WP_Event_Calendar_List_Table {

	public function __construct( $start_of_week ) {
		$this->start_of_week = $start_of_week;
		$this->month         = 10;
		$this->year          = 2026;
		$this->today         = gmmktime( 0, 0, 0, 10, 14, 2026 );
	}

	public function day_classes( $iterator, $start_day ) {
		return $this->get_day_classes( $iterator, $start_day );
	}

	public function weekday_offset( $weekday ) {
		return $this->get_weekday_offset( $weekday );
	}

	public function week_start( $timestamp ) {
		return $this->get_week_start( $timestamp );
	}
}

class WP_Event_Calendar_Test_Week_Table extends WP_Event_Calendar_Week_Table {

	public function boundary( $name ) {
		$property = new ReflectionProperty( WP_Event_Calendar_Week_Table::class, $name );

		if ( PHP_VERSION_ID < 80100 ) {
			$property->setAccessible( true );
		}

		return $property->getValue( $this );
	}
}
