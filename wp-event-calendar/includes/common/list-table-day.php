<?php

/**
 * Calendar Day List Table
 *
 * @since 0.1.8
 *
 * @package Calendar/ListTables/Day
 *
 * @see WP_Posts_List_Table
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Calendar Day List Table
 *
 * This list table is responsible for showing events in a traditional table,
 * even though it extends the `WP_List_Table` class. Tables & lists & tables.
 *
 * @since 0.1.8
 */
class WP_Event_Calendar_Day_Table extends WP_Event_Calendar_List_Table {

	/**
	 * The mode of the current view
	 *
	 * @since 0.1.8
	 *
	 * @var string
	 */
	public $mode = 'day';

	/**
	 * Unix time week start
	 *
	 * @since 0.1.8
	 *
	 * @var int
	 */
	private $day_start = 0;

	/**
	 * Unix time week end
	 *
	 * @since 0.1.8
	 *
	 * @var int
	 */
	private $day_end = 0;

	/**
	 * The main constructor method
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		// Setup the week ranges
		$this->day_start = strtotime( 'midnight', $this->today );
		$this->day_end   = strtotime( 'tomorrow', $this->day_start );

		// Setup the day ranges
		$this->view_start = date_i18n( 'Y-m-d H:i:s', $this->day_start );
		$this->view_end   = date_i18n( 'Y-m-d H:i:s', $this->day_end   );
	}

	/**
	 * Setup the list-table's columns
	 *
	 * @see WP_List_Table::single_row_columns()
	 *
	 * @return array An associative array containing column information
	 */
	public function get_columns() {

		// Lowercase day, for column key
		$day = strtolower( date( 'l', $this->day_start ) );

		// Return Week & Day
		return array(
			'hour' => sprintf( esc_html__( 'Wk. %s', 'wp-event-calendar' ), date_i18n( 'W', $this->today ) ),
			$day   => date_i18n( 'l, F j, Y', $this->day_start ),
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
		return array( 'widefat', 'fixed', 'striped', 'calendar', 'day', $this->_args['plural'] );
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
		if ( ! empty( $this->item_start ) ) {
			$start_day  = date_i18n( 'j', $this->item_start );
			$start_hour = date_i18n( 'G', $this->item_start );
		} else {
			$start_day  = 0;
			$start_hour = 0;
		}

		// Calculate end day
		if ( ! empty( $this->item_end ) ) {
			$end_day  = date_i18n( 'j', $this->item_end );
			$end_hour = date_i18n( 'G', $this->item_end );
		} else {
			$end_day  = $start_day;
			$end_hour = $start_hour;
		}

		// All day events (and days that overlap multiple days)
		if ( ( true === $this->item_all_day ) || ( $this->item_days > 1 ) || ( $start_day !== $end_day ) ) {

			// What type of item is this?
			$type = ( true === $this->item_all_day )
				? 'all_day_items'
				: 'multi_day_items';

			// Math the heck out of this
			$interval = 1;
			$offset   = 0;
			$cell     = $offset;
			$end_cell = ( $end_day * $start_day ) + $offset;

		// Regular single-day events
		} else {
			$type     = 'items';
			$interval = 1;

			// Calculate the cell offset
			$offset   = intval( ( $this->item_start - $this->day_start ) / DAY_IN_SECONDS );
			$cell     = ( $start_hour * $interval ) + $offset;
			$end_cell = ( $end_hour   * $interval ) + $offset;
		}

		// Loop through days
		while ( $cell <= $end_cell ) {

			// Setup the pointer for each day
			$this->setup_pointer( $post, $cell );

			// Add post to items for each day in it's duration
			if ( empty( $this->{$type}[ $cell ] ) || ( $max > count( $this->{$type}[ $cell ] ) ) ) {
				$this->{$type}[ $cell ][ $post->ID ] = $post;
			}

			// Bump the hour
			$cell = intval( $cell + $interval );
		}
	}

	/**
	 * Paginate through days & weeks
	 *
	 * @since 0.1.8
	 *
	 * @param array $args
	 */
	protected function pagination( $args = array() ) {

		// Parse args
		$r = wp_parse_args( $args, array(
			'small'  => '1 day',
			'large'  => '1 week',
			'labels' => array(
				'next_small' => esc_html__( 'Tomorrow',      'wp-event-calendar' ),
				'next_large' => esc_html__( 'Next Week',     'wp-event-calendar' ),
				'prev_small' => esc_html__( 'Yerterday',     'wp-event-calendar' ),
				'prev_large' => esc_html__( 'Previous Week', 'wp-event-calendar' )
			)
		) );

		// Return pagination
		return parent::pagination( $r );
	}

	/**
	 * Output a special row for "All Day" events
	 *
	 * @since 0.1.8
	 */
	protected function get_all_day_row() {

		// Start an output buffer
		ob_start(); ?>

		<tr class="all-day">
			<th><?php esc_html_e( 'All day', 'wp-event-calendar' ); ?></th>
			<td><?php echo $this->get_day_row_cell( 0, 'all_day_items' ); ?></td>
		</tr>

		<?php

		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * Start the week with a table row, and a th to show the hour
	 *
	 * @since 0.2.2
	 */
	protected function get_multi_day_row() {

		// Start an output buffer
		ob_start(); ?>

		<tr class="multi-day">
			<th><?php esc_html_e( 'Multi-day', 'wp-event-calendar' ); ?></th>
			<td><?php echo $this->get_day_row_cell( 0, 'multi_day_items' ); ?></td>
		</tr>

		<?php

		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * Start the week with a table row
	 *
	 * @since 0.2.2
	 */
	protected function get_day_row_cell( $iterator = 1, $type = 'all_day_items' ) {

		// Start an output buffer
		ob_start(); ?>

		<div class="events-for-cell">
			<?php echo $this->get_posts_for_cell( $iterator, $type ); ?>
		</div>

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

		// Current hour
		$hour = date_i18n( 'H', $time );

		// No row classes
		$classes = array(
			"hour-{$hour}"
		);

		// Is this this hour?
		if ( date_i18n( 'H' ) === $hour ) {
			$classes[] = 'this-hour';
		}

		// Start an output buffer
		ob_start(); ?>

		<tr class="<?php echo implode( ' ', $classes ); ?>"><th><?php echo date_i18n( 'g:i a', $time ); ?></th>

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
	 * @since 0.1.0
	 */
	protected function get_row_cell( $iterator = 1, $start_day = 1 ) {

		// Start an output buffer
		ob_start(); ?>

		<td class="<?php echo $this->get_day_classes( $iterator, $start_day ); ?>">
			<div class="events-for-cell">
				<?php echo $this->get_posts_for_cell( $iterator ); ?>
			</div>
		</td>

		<?php

		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * Display a calendar by mode & range
	 *
	 * @since 0.1.8
	 */
	protected function display_mode() {

		// Get timestamp
		$timestamp  = mktime( 0, 0, 0, $this->month, $this->day, $this->year );
		$this_month = getdate( $timestamp );
		$start_day  = $this_month['wday'];

		// All day events
		echo $this->get_all_day_row();

		// Multi day events
		echo $this->get_multi_day_row();

		// Loop through days of the month
		for ( $i = 0; $i <= ( 24 - 1 ); $i++ ) {

			// Get timestamp & hour
			$timestamp = mktime( $i, 0, 0, $this->month, $this->day, $this->year );

			// New row
			echo $this->get_row_start( $timestamp );

			// Get this table cell
			echo $this->get_row_cell( $i, $start_day );

			// Close row
			echo $this->get_row_end();
		}
	}
}
