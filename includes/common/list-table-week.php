<?php

/**
 * Calendar Week List Table
 *
 * @since 0.1.8
 *
 * @package Calendar/ListTables/Week
 *
 * @see WP_Posts_List_Table
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Calendar Week List Table
 *
 * This list table is responsible for showing events in a traditional table,
 * even though it extends the `WP_List_Table` class. Tables & lists & tables.
 *
 * @since 0.1.8
 */
class WP_Event_Calendar_Week_Table extends WP_Event_Calendar_List_Table {

	/**
	 * Unix time week start
	 *
	 * @since 0.1.8
	 *
	 * @var int
	 */
	private $week_start = 0;

	/**
	 * Unix time week end
	 *
	 * @since 0.1.8
	 *
	 * @var int
	 */
	private $week_end = 0;

	/**
	 * The main constructor method
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		// Set the mode
		$this->mode = 'week';

		// Setup the week ranges
		$this->week_start = strtotime( 'last Sunday midnight',   $this->today );
		$this->week_end   = strtotime( 'this Saturday midnight', $this->today );

		// Setup the week ranges
		$this->view_start = date_i18n( 'Y-m-d H:i:s', $this->week_start );
		$this->view_end   = date_i18n( 'Y-m-d H:i:s', $this->week_end   );
	}

	/**
	 * Setup the list-table's columns
	 *
	 * @see WP_List_Table::single_row_columns()
	 *
	 * @return array An associative array containing column information
	 */
	public function get_columns() {
		return array(
			'hour'      => sprintf( esc_html__( 'Wk. %s', 'wp-event-calendar' ), date_i18n( 'W', $this->today ) ),
			'sunday'    => date_i18n( 'D, M. j', $this->week_start ),
			'monday'    => date_i18n( 'D, M. j', $this->week_start + ( DAY_IN_SECONDS * 1 ) ),
			'tuesday'   => date_i18n( 'D, M. j', $this->week_start + ( DAY_IN_SECONDS * 2 ) ),
			'wednesday' => date_i18n( 'D, M. j', $this->week_start + ( DAY_IN_SECONDS * 3 ) ),
			'thursday'  => date_i18n( 'D, M. j', $this->week_start + ( DAY_IN_SECONDS * 4 ) ),
			'friday'    => date_i18n( 'D, M. j', $this->week_start + ( DAY_IN_SECONDS * 5 ) ),
			'saturday'  => date_i18n( 'D, M. j', $this->week_end )
		);
	}

	/**
	 * Get a list of CSS classes for the list table table tag.
	 *
	 * @since 0.1.8
	 * @access protected
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', 'calendar', 'week', $this->_args['plural'] );
	}

	/**
	 * Add a post to the items array, keyed by day
	 *
	 * @todo Repeat & expire
	 *
	 * @since 0.1.8
	 *
	 * @param  object  $post
	 * @param  int     $max
	 */
	protected function setup_item( $post = false, $max = 10 ) {

		// Calculate start day
		if ( ! empty( $this->_start ) ) {
			$start_day  = date_i18n( 'j', $this->_start );
			$start_hour = date_i18n( 'H', $this->_start );
		} else {
			$start_day  = 0;
			$start_hour = 0;
		}

		// Calculate end day
		if ( ! empty( $this->_end ) ) {
			$end_day  = date_i18n( 'j', $this->_end );
			$end_hour = date_i18n( 'H', $this->_end );
		} else {
			$end_day  = $start_day;
			$end_hour = $start_hour;
		}

		// Skip overnights for now
		if ( $end_day > $start_day ) {
			return;
		}

		// Start the days loop with the start day
		$hour = $start_hour;

		// Loop through days
		while ( $hour <= $end_hour ) {

			// Setup the pointer for each day
			//$this->setup_pointer( $post, $hour );

			// Add post to items for each day in it's duration
			if ( empty( $this->items[ $hour ][ $start_day ] ) || ( $max > count( $this->items[ $hour ][ $start_day ] ) ) ) {
				$this->items[ $hour ][ $start_day ][ $post->ID ] = $post;
			}

			// Bump the hour
			++$hour;
		}
	}

	/**
	 * Return filtered query arguments
	 *
	 * @since 0.1.8
	 *
	 * @return array
	 */
	protected function main_query_args( $args = array() ) {

		// Events
		if ( 'event' === $this->screen->post_type ) {
			$args = array(
				'meta_query' => array(
					array(
						'key'     => 'wp_event_calendar_date_time',
						'value'   => array( $this->view_start, $this->view_end ),
						'type'    => 'DATETIME',
						'compare' => 'BETWEEN',
					),

					// Skip all day events in this loop
					array(
						'key'     => 'wp_event_calendar_all_day',
						'compare' => 'NOT EXISTS'
					)
				)
			);
		}

		return parent::main_query_args( $args );
	}

	/**
	 * Paginate through months & years
	 *
	 * @since 0.1.8
	 *
	 * @param array $args
	 */
	protected function pagination( $args = array() ) {

		// Parse args
		$args = wp_parse_args( $args, array(
			'small'  => '1 week',
			'large'  => '1 month',
			'labels' => array(
				'next_small' => esc_html__( 'Next Week',      'wp-event-calendar' ),
				'next_large' => esc_html__( 'Next Month',     'wp-event-calendar' ),
				'prev_small' => esc_html__( 'Previous Week',  'wp-event-calendar' ),
				'prev_large' => esc_html__( 'Previous Month', 'wp-event-calendar' )
			)
		) );

		// Return pagination
		return parent::pagination( $args );
	}

	/**
	 * Start the week with a table row, and a th to show the time
	 *
	 * @since 0.1.8
	 */
	protected function get_all_day_row() {

		// Start an output buffer
		ob_start(); ?>

		<tr class="all-day">
			<th><?php esc_html_e( 'All day', 'wp-event-calendar' ); ?></th>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>

		<?php

		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * Start the week with a table row, and a th to show the time
	 *
	 * @since 0.1.8
	 */
	protected function get_row_start( $time = 0 ) {

		// No row classes
		$class = '';

		// Is this this hour?
		if ( date_i18n( 'H' ) === date_i18n( 'H', $time ) ) {
			$class = 'class="this-hour"';
		}

		// Start an output buffer
		ob_start(); ?>

		<tr <?php echo $class; ?>><th><?php echo date_i18n( 'g:i a', $time ); ?></th>

		<?php

		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * End the week with a closed table row
	 *
	 * @since 0.1.8
	 */
	protected function get_row_end() {

		// Start an output buffer
		ob_start(); ?>

			</tr>

		<?php

		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * Start the week with a table row
	 *
	 * @since 0.1.8
	 */
	protected function get_row_contents( $time = 0 ) {

		// Get week start day
		$week_start = date_i18n( 'j', $this->week_start );
		$hour       = date_i18n( 'H', $time );

		// Start an output buffer
		ob_start();

		// Calculate the day of the month
		for ( $dow = 0; $dow <= 6; $dow++ ) :
			$day = ( $dow + $week_start ); ?>

			<td class="<?php echo $this->get_event_classes( $dow, $day ); ?>">
				<div class="events-for-day">
					<?php echo $this->get_posts_for_cell( $hour, $day ); ?>
				</div>
			</td>

		<?php endfor;

		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * Get the already queried posts for a given day
	 *
	 * @since 0.1.8
	 *
	 * @param int $day
	 *
	 * @return array
	 */
	protected function get_day_queried_posts( $hour = 0, $day = 1 ) {
		return isset( $this->items[ $hour ][ $day ] )
			? $this->items[ $hour ][ $day ]
			: array();
	}

	/**
	 * Get posts for the day
	 *
	 * @since 0.1.8
	 *
	 * @param int $day
	 *
	 * @return string
	 */
	protected function get_posts_for_cell( $hour = 0, $day = 1 ) {

		// Get posts and bail if none
		$posts = $this->get_day_queried_posts( $hour, $day );
		if ( empty( $posts ) ) {
			return '';
		}

		// Start an output buffer
		ob_start();

		// Loop through today's posts
		foreach ( $posts as $post ) :

			// Setup the pointer ID
			$ponter_id = "{$post->ID}-{$day}";

			// Get the post link
			$post_link = get_edit_post_link( $post->ID );

			// Handle empty titles
			$post_title = get_the_title( $post->ID );
			if ( empty( $post_title ) ) {
				$post_title = esc_html__( '(No title)', 'wp-event-calendar' );
			} ?>

			<a id="event-pointer-<?php echo esc_attr( $ponter_id ); ?>" href="<?php echo esc_url( $post_link ); ?>" class="<?php echo $this->get_day_post_classes( $post->ID ); ?>"><?php echo esc_html( $post_title ); ?></a>

		<?php endforeach;

		return ob_get_clean();
	}

	/**
	 * Display a calendar by month and year
	 *
	 * @since 0.1.8
	 *
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 */
	protected function display_mode( $year = 2015, $month = 1, $day = 1 ) {

		// All day events
		echo $this->get_all_day_row( $year, $month, $day );

		// Loop through days of the month
		for ( $i = 0; $i <= 23; $i++ ) {

			// Get timestamp & hour
			$timestamp = mktime( $i, 0, 0, $month, $day, $year );

			// New row
			echo $this->get_row_start( $timestamp );

			// Get table cells for all days this week in this hour
			echo $this->get_row_contents( $timestamp );

			// Close row
			echo $this->get_row_end();
		}
	}
}
