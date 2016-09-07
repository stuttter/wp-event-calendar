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
	 * The mode of the current view
	 *
	 * @since 0.1.8
	 *
	 * @var string
	 */
	public $mode = 'week';

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

		// Reset the week
		$where_is_thumbkin = ( 0 === date( 'w' ) )
			? 'this Sunday midnight'
			: 'last Sunday midnight';

		// Setup the week ranges
		$this->week_start = strtotime( $where_is_thumbkin,       $this->today );
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
			$offset   = - ceil( ( $this->week_start - $this->item_start ) / DAY_IN_SECONDS );
			$cell     = $offset;
			$end_cell = ( $end_day * $start_day ) + $offset;

		// Regular single-day events
		} else {
			$type     = 'items';
			$interval = 7;

			// Calculate the cell offset
			$offset   = intval( ( $this->item_start - $this->week_start ) / DAY_IN_SECONDS );

			// Start the days loop with the start day
			$cell     = ( $start_hour * $interval ) + $offset;

			// Maybe don't include the end hour
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
	 * Paginate through months & years
	 *
	 * @since 0.1.8
	 *
	 * @param array $args
	 */
	protected function pagination( $args = array() ) {

		// Parse args
		$r = wp_parse_args( $args, array(
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
		return parent::pagination( $r );
	}

	/**
	 * Start the week with a table row, and a th to show the time
	 *
	 * @since 0.1.8
	 */
	protected function get_all_day_row() {

		// Items that last an entire day, maybe for multiple
		$type = 'all_day_items';

		// Start an output buffer
		ob_start(); ?>

		<tr class="all-day">
			<th><?php esc_html_e( 'All day', 'wp-event-calendar' ); ?></th>
			<td><?php echo $this->get_day_row_cell( 0, $type ); ?></td>
			<td><?php echo $this->get_day_row_cell( 1, $type ); ?></td>
			<td><?php echo $this->get_day_row_cell( 2, $type ); ?></td>
			<td><?php echo $this->get_day_row_cell( 3, $type ); ?></td>
			<td><?php echo $this->get_day_row_cell( 4, $type ); ?></td>
			<td><?php echo $this->get_day_row_cell( 5, $type ); ?></td>
			<td><?php echo $this->get_day_row_cell( 6, $type ); ?></td>
		</tr>

		<?php

		// Return the output buffer
		return ob_get_clean();
	}

	/**
	 * Start the week with a table row, and a th to show the time
	 *
	 * @since 0.2.2
	 */
	protected function get_multi_day_row() {

		// Items that span multiple days
		$type = 'multi_day_items';

		// Start an output buffer
		ob_start(); ?>

		<tr class="multi-day">
			<th><?php esc_html_e( 'Multi-day', 'wp-event-calendar' ); ?></th>
			<td><?php echo $this->get_day_row_cell( 0, $type ); ?></td>
			<td><?php echo $this->get_day_row_cell( 1, $type ); ?></td>
			<td><?php echo $this->get_day_row_cell( 2, $type ); ?></td>
			<td><?php echo $this->get_day_row_cell( 3, $type ); ?></td>
			<td><?php echo $this->get_day_row_cell( 4, $type ); ?></td>
			<td><?php echo $this->get_day_row_cell( 5, $type ); ?></td>
			<td><?php echo $this->get_day_row_cell( 6, $type ); ?></td>
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

		// Loop through hours in days of week
		for ( $i = 0; $i <= ( 7 * 24 ) - 1; $i++ ) {

			// Get timestamp & hour
			$timestamp = mktime( ( $i / 7 ), 0, 0, $this->month, $this->day, $this->year );

			// New row
			if ( ( $i % 7 ) === 0 ) {
				echo $this->get_row_start( $timestamp );
			}

			// Get this table cell
			echo $this->get_row_cell( $i, $start_day );

			// Close row
			if ( ( $i % 7 ) === 6 ) {
				echo $this->get_row_end();
			}
		}
	}
}
