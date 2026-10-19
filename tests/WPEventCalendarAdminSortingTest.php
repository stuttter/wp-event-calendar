<?php

use PHPUnit\Framework\TestCase;

class WPEventCalendarAdminSortingTest extends TestCase {

	public function test_custom_sorting_does_not_change_the_request_or_query() {
		$_GET = array();
		$_GET['orderby'] = 'venue';

		$query = new WP_Query( array(
			'post_type' => 'event',
			'orderby'   => 'venue',
			'order'     => 'asc',
		) );

		wp_event_calendar_maybe_sort_by_fields( $query );

		$this->assertSame( 'venue', $_GET['orderby'] );
		$this->assertSame( 'venue', $query->query['orderby'] );
		$this->assertSame( 'asc', $query->query['order'] );
	}

	public function test_start_date_sorting_changes_only_the_query() {
		$_GET = array();
		$_GET['orderby'] = 'start_date';

		$query = new WP_Query( array(
			'post_type' => 'event',
			'orderby'   => 'start_date',
			'order'     => 'asc',
		) );

		wp_event_calendar_maybe_sort_by_fields( $query );

		$this->assertSame( 'start_date', $_GET['orderby'] );
		$this->assertSame( 'meta_value', $query->query['orderby'] );
		$this->assertSame( 'ASC', $query->query['order'] );
	}
}
