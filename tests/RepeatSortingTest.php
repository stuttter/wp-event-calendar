<?php

use PHPUnit\Framework\TestCase;

class RepeatSortingTest extends TestCase {

	public function test_repeat_sorting_uses_a_left_join_without_filtering_missing_metadata() {
		$query = new WP_Query( array(
			'post_type' => 'event',
			'orderby'   => 'repeat',
			'order'     => 'ASC',
		) );

		wp_event_calendar_maybe_sort_by_fields( $query );

		$clauses = wp_event_calendar_sort_by_repeat_clauses( array(
			'join'    => '',
			'orderby' => '',
		), $query );

		$this->assertSame( 'none', $query->get( 'orderby' ) );
		$this->assertNotFalse( strpos( $clauses['join'], 'LEFT JOIN wp_postmeta' ) );
		$this->assertNotFalse( strpos( $clauses['join'], "wp_event_calendar_repeat.meta_key = 'wp_event_calendar_repeat'" ) );
		$this->assertFalse( strpos( $clauses['join'], 'WHERE' ) );
		$this->assertSame( 'CAST( COALESCE( wp_event_calendar_repeat.meta_value, 0 ) AS SIGNED ) ASC, wp_posts.ID ASC', $clauses['orderby'] );
	}

	public function test_repeat_sorting_defaults_to_descending_order() {
		$query = new WP_Query( array(
			'post_type' => 'event',
			'orderby'   => 'repeat',
		) );

		wp_event_calendar_maybe_sort_by_fields( $query );

		$clauses = wp_event_calendar_sort_by_repeat_clauses( array(
			'join'    => '',
			'orderby' => '',
		), $query );

		$this->assertSame( 'CAST( COALESCE( wp_event_calendar_repeat.meta_value, 0 ) AS SIGNED ) DESC, wp_posts.ID DESC', $clauses['orderby'] );
	}

	public function test_unrelated_queries_are_unchanged() {
		$query   = new WP_Query( array( 'orderby' => 'title' ) );
		$clauses = array(
			'join'    => 'existing join',
			'orderby' => 'existing order',
		);

		$this->assertSame( $clauses, wp_event_calendar_sort_by_repeat_clauses( $clauses, $query ) );
	}
}
