<?php

/**
 * Event List Tables
 *
 * @package EventCalendar/Common/ListTable
 *
 * @see WP_Posts_List_Table
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Include the main list table class if it's not included
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( class_exists( 'WP_List_Table' ) ) :
/**
 * Event table
 *
 * This list table is responsible for showing events in a traditional table,
 * even though it extends the `WP_List_Table` class. Tables & lists & tables.
 *
 * @todo WP_Table_Lol
 */
class WP_Event_Calendar_Calendar_Table extends WP_List_Table {

	/**
	 * The month being viewed
	 *
	 * @since 0.1.0
	 *
	 * @var int
	 */
	protected $month = 1;

	/**
	 * The day being viewed
	 *
	 * @since 0.1.0
	 *
	 * @var int
	 */
	protected $day = 1;

	/**
	 * The year being viewed
	 *
	 * @since 0.1.0
	 *
	 * @var int
	 */
	protected $year = 2015;

	/**
	 * The posts query being run
	 *
	 * @since 0.1.0
	 *
	 * @var object
	 */
	protected $query = null;

	/**
	 * The items being displayed
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	public $items = array();

	/**
	 * The items with pointers
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	public $pointers = array();

	/**
	 * The main constructor method
	 */
	public function __construct( $args = array() ) {

		// Ready the pointer content
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_pointers_footer' ) );

		// Set month, day, & year
		$this->month = $this->get_month();
		$this->year  = $this->get_year();
		$this->day   = $this->get_day();

		// Setup arguments
		$args = array(
			'singular' => esc_html__( 'Event',  'wp-event-calendar' ),
			'plural'   => esc_html__( 'Events', 'wp-event-calendar' ),
			'ajax'     => true
		);
		parent::__construct( $args );
	}

	/**
	 * Return the post type of the current screen
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	protected function get_screen_post_type() {

		// Special handling for "post" post type
		if ( ! empty( $this->screen->post_type ) ) {
			$post_type = $this->screen->post_type;
		} else {
			$post_type = 'post';
		}

		return $post_type;
	}

	/**
	 * Return the base URL
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	protected function get_base_url() {

		// Define local variables
		$args      = array();
		$post_type = $this->get_screen_post_type();

		// "post" post type needs special handling
		if ( 'post' !== $post_type ) {
			$args['post_type'] = $post_type;
		}

		// Setup "page" argument
		$args['page'] = $post_type . '-calendar';

		// Add args & return
		return add_query_arg( $args, admin_url( 'edit.php' ) );
	}

	/**
	 * Setup the list-table's columns
	 *
	 * @see WP_List_Table::::single_row_columns()
	 *
	 * @return array An associative array containing column information
	 */
	public function get_columns() {
		return array(
			'sunday'    => $GLOBALS['wp_locale']->get_weekday( 0 ),
			'monday'    => $GLOBALS['wp_locale']->get_weekday( 1 ),
			'tuesday'   => $GLOBALS['wp_locale']->get_weekday( 2 ),
			'wednesday' => $GLOBALS['wp_locale']->get_weekday( 3 ),
			'thursday'  => $GLOBALS['wp_locale']->get_weekday( 4 ),
			'friday'    => $GLOBALS['wp_locale']->get_weekday( 5 ),
			'saturday'  => $GLOBALS['wp_locale']->get_weekday( 6 )
		);
	}

	/**
	 * Allow columns to be sortable
	 *
	 * @return array An associative array containing the sortable columns
	 */
	public function get_sortable_columns() {
		return $this->get_columns();
	}

	/**
	 * Setup the bulk actions
	 *
	 * @return array An associative array containing all the bulk actions
	 */
	public function get_bulk_actions() {
		return array();
	}

	/**
	 * Get the current month
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 *
	 * @return int
	 */
	protected function get_month() {
		return (int) isset( $_REQUEST['month'] )
			? $_REQUEST['month']
			: date_i18n( 'n' );
	}

	/**
	 * Get the current day
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 *
	 * @return int
	 */
	protected function get_day() {
		return (int) isset( $_REQUEST['day'] )
			? $_REQUEST['day']
			: date_i18n( 'j' );
	}

	/**
	 * Get the current year
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 *
	 * @return int
	 */
	protected function get_year() {
		return (int) isset( $_REQUEST['year'] )
			? $_REQUEST['year']
			: date_i18n( 'Y' );
	}

	/**
	 * Get the current page number
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 *
	 * @return int
	 */
	protected function get_post_status() {
		return isset( $_REQUEST['post_status'] )
			? $_REQUEST['post_status']
			: $this->get_available_post_stati();
	}

	/**
	 * Get the current page number
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 *
	 * @return int
	 */
	protected function get_available_post_stati() {
		return array(
			'publish',
			'future',
			'draft',
			'pending',
			'private',
			'hidden'
		);
	}

	/**
	 * Get the current page number
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 *
	 * @return int
	 */
	protected function get_orderby() {
		return isset( $_REQUEST['orderby'] )
			? $_REQUEST['orderby']
			: 'date';
	}

	/**
	 * Get the current page number
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 *
	 * @return int
	 */
	protected function get_order() {
		return isset( $_REQUEST['order'] )
			? ucwords( $_REQUEST['order'] )
			: 'ASC';
	}

	/**
	 * Get the current page number
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 *
	 * @return int
	 */
	protected function get_search() {
		return isset( $_REQUEST['s'] )
			? wp_unslash( $_REQUEST['s'] )
			: '';
	}

	/**
	 * Get the current page number
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 *
	 * @return int
	 */
	protected function get_per_day() {
		$user_option = get_user_option( get_current_screen()->base . 'per_day' );

		if ( empty( $user_option ) ) {
			$user_option = 10;
		}

		return (int) $user_option;
	}

	/**
	 * Get a list of CSS classes for the list table table tag.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', 'calendar', $this->_args['plural'] );
	}

	/**
	 * Determine if the current view is the "All" view.
	 *
	 * @since 0.1.0
	 *
	 * @return bool Whether the current view is the "All" view.
	 */
	protected function is_base_request() {
		if ( empty( $_GET ) ) {
			return true;
		} elseif ( 2 === count( $_GET ) && ! empty( $_GET['post_type'] ) ) {
			return ( $this->screen->post_type === $_GET['post_type'] );
		}
		return false;
	}

	/**
	 * Get the calendar views
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 *
	 * @return int
	 */
	protected function get_views() {

		// Screen
		$base_url = $this->get_base_url();

		// Posts
		$post_type   = $this->get_screen_post_type();
		$num_posts   = wp_count_posts( $post_type, 'readable' );
		$total_posts = array_sum( (array) $num_posts );
		$class       = $allposts = '';

		// Post type statuses
		$avail_post_stati = $this->get_available_post_stati();
		$status_links     = array();
		$post_statuses    = get_post_stati( array( 'show_in_admin_all_list' => false ) );

		// Subtract post statuses that are not included in the admin all list.
		foreach ( $post_statuses as $state ) {
			$total_posts -= $num_posts->$state;
		}

		// "All" link
		if ( empty( $class ) && ( $this->is_base_request() || isset( $_REQUEST['all_posts'] ) ) ) {
			$class = 'current';
		}

		$all_inner_html = sprintf(
			_nx(
				'All <span class="count">(%s)</span>',
				'All <span class="count">(%s)</span>',
				$total_posts,
				'posts'
			),
			number_format_i18n( $total_posts )
		);

		$status_links['all'] = '<a href="' . esc_url( $base_url ) . '" class="' . $class . '">' . $all_inner_html . '</a>';

		// Other links
		$post_statuses = get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' );

		// Loop through statuses and compile array of available ones
		foreach ( $post_statuses as $status ) {

			// Set variable to trick PHP
			$status_name = $status->name;

			// Skip if not available status
			if ( ! in_array( $status_name, $avail_post_stati ) ) {
				continue;
			}

			// Skip if no post count
			if ( empty( $num_posts->$status_name ) ) {
				continue;
			}

			// Set the class value
			if ( isset( $_REQUEST['post_status'] ) && ( $status_name === $_REQUEST['post_status'] ) ) {
				$class = 'current';
			} else {
				$class = '';
			}

			// Calculate the status text
			$status_text = sprintf( translate_nooped_plural( $status->label_count, $num_posts->$status_name ), number_format_i18n( $num_posts->$status_name ) );
			$status_url  = add_query_arg( array( 'post_status' => $status_name ), $base_url );

			// Add link to array
			$status_links[ $status_name ] = '<a href="' . esc_url( $status_url ) . '" class="' . $class . '">' . $status_text . '</a>';
		}

		return $status_links;
	}

	/**
	 * Handle bulk action requests
	 */
	public function process_bulk_action() {
		// No bulk actions
	}

	/**
	 * Always have items
	 *
	 * @since 0.1.0
	 *
	 * @return boolean
	 */
	public function has_items() {
		return true;
	}

	/**
	 * Prepare the list-table items for display
	 *
	 * @since 0.1.0
	 *
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_orderby()
	 * @uses $this->get_order()
	 */
	public function prepare_items() {

		// Set column headers
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			array()
		);

		// Handle bulk actions
		$this->process_bulk_action();

		// Query for rentals
		$this->query = new WP_Query( array(
			'post_type'           => $this->screen->post_type,
			'post_status'         => $this->get_post_status(),
			'monthnum'            => $this->month,
			'year'                => $this->year,
			'day'                 => null,
			'posts_per_page'      => -1,
			'orderby'             => $this->get_orderby(),
			'order'               => $this->get_order(),
			'hierarchical'        => false,
			'ignore_sticky_posts' => true,
			's'                   => $this->get_search()
		) );

		// Max per day
		$max_per_day = $this->get_per_day();

		// Rearrange posts into an array keyed by day of the month
		foreach ( $this->query->posts as $post ) {

			// Pointers
			$pointer_title     = '<h3 class="' . $this->get_day_post_classes( $post->ID ) . '">' . esc_html( $post->post_title   ) . '</h3>';
			$pointer_edit_link = current_user_can( 'edit_post', $post->ID ) ? ' <a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">'  . esc_html__( 'Edit', 'wp-event-calendar' ) . '</a>' : '';
			$pointer_excerpt   = '<p>'  . esc_js( wp_kses_data( $post->post_content ) ) . $pointer_edit_link . '</p>';
			$this->pointers[] = array(
				'content'   => $pointer_title . $pointer_excerpt,
				'anchor_id' => '#event-pointer-' . $post->ID,
				'edge'      => 'top',
				'align'     => 'left'
			);

			// Reorder posts by day
			$post_day = mysql2date( 'j', $post->post_date );
			if ( empty( $this->items[ $post_day ] ) || ( $max_per_day > count( $this->items[ $post_day ] ) ) ) {
				$this->items[ $post_day ][ $post->ID ] = $post;
			}
		}
	}

	/**
	 * Output the pointers for each event
	 *
	 * @since 0.1.1
	 */
	public function admin_pointers_footer() {
	?>

<!-- Start Event Pointers -->
<script type="text/javascript">
	/* <![CDATA[ */
	( function($) {
		$( '.calendar a' ).click( function( event ) {
			event.preventDefault();
		} );

	<?php foreach ( $this->pointers as $item ) : ?>

		$( '<?php echo $item[ 'anchor_id' ]; ?>' ).pointer( {
			content: '<?php echo $item[ 'content' ]; ?>',
			position: {
				edge:  '<?php echo $item[ 'edge' ]; ?>',
				align: '<?php echo $item[ 'align' ]; ?>'
			}
		} );

		$( '<?php echo $item[ 'anchor_id' ]; ?>' ).click( function() {
			$( this ).pointer( 'open' );
		} );

	<?php endforeach; ?>		
	} )( jQuery );
	/* ]]> */
</script>
<!-- End Event Pointers -->

		<?php
	}


	/**
	 * Message to be displayed when there are no items
	 */
	public function no_items() {
		// Do nothing; calendars always have rows
	}

	/**
	 * Paginate through months & years
	 *
	 * @since 0.1.0
	 *
	 * @param string $which
	 */
	protected function pagination( $which = '' ) {

		// No botton pagination
		if ( 'top' !== $which ) {
			return;
		}

		// Base URLs
		$today    = $this->get_base_url();
		$page_url = add_query_arg( array(
			'month' => $this->month,
			'year'  => $this->year,
			'day'   => $this->day,
		), $today );

		// Adjust previous & next
		$prev_year  = $this->year - 1;
		$next_year  = $this->year + 1;
		$prev_month = $this->month - 1;
		$next_month = $this->month + 1;

		// Setup month args
		$prev_month_args = array( 'month' => $prev_month );
		$next_month_args = array( 'month' => $next_month );

		// Previous month is last year
		if ( $prev_month === 0 ) {
			$prev_month_args['month'] = 12;
			$prev_month_args['year']  = $prev_year;
		}

		// Next month is a new year
		if ( $next_month === 13 ) {
			$next_month_args['month'] = 1;
			$next_month_args['year']  = $next_year;
		} ?>

		<div class="tablenav-pages previous">
			<a class="previous-page" href="<?php echo esc_url( add_query_arg( array( 'year' => $prev_year ), $page_url ) ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Previous year', 'wp-event-calendar' ); ?></span>
				<span aria-hidden="true">&laquo;</span>
			</a>
			<a class="previous-page" href="<?php echo esc_url( add_query_arg( $prev_month_args, $page_url ) ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Previous month', 'wp-event-calendar' ); ?></span>
				<span aria-hidden="true">&lsaquo;</span>
			</a>

			<a href="<?php echo esc_url( $today ); ?>" class="previous-page">
				<span class="screen-reader-text"><?php esc_html_e( 'Today', 'wp-event-calendar' ); ?></span>
				<span aria-hidden="true">&Colon;</span>
			</a>

			<a class="next-page" href="<?php echo esc_url( add_query_arg( $next_month_args, $page_url ) ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Next month', 'wp-event-calendar' ); ?></span>
				<span aria-hidden="true">&rsaquo;</span>
			</a>

			<a class="next-page" href="<?php echo esc_url( add_query_arg( array( 'year' => $next_year ), $page_url ) ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Next year', 'wp-event-calendar' ); ?></span>
				<span aria-hidden="true">&raquo;</span>
			</a>
		</div>

		<?php
	}

	/**
	 * Output month & year inputs, for viewing relevant posts
	 *
	 * @since 0.1.0
	 *
	 * @param  string  $which
	 */
	protected function extra_tablenav( $which = '' ) {

		// No bottom extras
		if ( 'top' !== $which ) {
			return;
		} ?>

		<label for="month" class="screen-reader-text"><?php esc_html_e( 'Switch to this month', 'wp-event-calendar' ); ?></label>
		<select name="month" id="month">

			<?php for ( $month_index = 1; $month_index <= 12; $month_index++ ) : ?>

				<option value="<?php echo esc_attr( $month_index ); ?>" <?php selected( $month_index, $this->month ); ?>><?php echo $GLOBALS['wp_locale']->get_month( $month_index ); ?></option>

			<?php endfor;?>

		</select>

		<label for="year" class="screen-reader-text"><?php esc_html_e( 'Switch to this year', 'wp-event-calendar' ); ?></label>
		<input type="number" name="year" id="year" value="<?php echo (int) $this->year; ?>" size="5">

		<?php submit_button( esc_html__( 'View', 'wp-event-calendar' ), 'action', '', false, array( 'id' => "doaction" ) );
	}

	/**
	 * Display the table
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function display() {

		// Top
		$this->display_tablenav( 'top' ); ?>

		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
			<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
			</thead>

			<tbody id="the-list" data-wp-lists='list:<?php echo $this->_args['singular']; ?>'>
				<?php $this->display_calendar( $this->month, $this->year ); ?>
			</tbody>

			<tfoot>
				<tr>
					<?php $this->print_column_headers( false ); ?>
				</tr>
			</tfoot>
		</table>

		<?php

		// Bottom
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Start the week with a table row
	 *
	 * @since 0.1.0
	 */
	protected function get_week_start() {
		?>

			<tr>

		<?php
	}

	/**
	 * End the week with a closed table row
	 *
	 * @since 0.1.0
	 */
	protected function get_week_end() {
		?>

			</tr>

		<?php
	}

	/**
	 * Start the week with a table row
	 *
	 * @since 0.1.0
	 */
	protected function get_week_day_pad(  $iterator = 1, $start_day = 1 ) {
		?>

			<th class="padding <?php echo $this->get_day_classes( $iterator, $start_day ); ?>"></th>

		<?php
	}

	/**
	 * Start the week with a table row
	 *
	 * @since 0.1.0
	 */
	protected function get_week_day( $iterator = 1, $start_day = 1 ) {

		// Calculate the day of the month
		$day_of_month = (int) ( $iterator - (int) $start_day + 1 ) ?>

		<td class="<?php echo $this->get_day_classes( $iterator, $start_day ); ?>">
			<span class="day-number">
				<?php echo (int) $day_of_month; ?>
			</span>

			<div class="events-for-day">
				<?php echo $this->get_day_posts( $day_of_month ); ?>
			</div>
		</td>

		<?php
	}

	/**
	 * Get the already queried posts for a given day
	 *
	 * @since 0.1.0
	 *
	 * @param int $day
	 *
	 * @return array
	 */
	protected function get_day_queried_posts( $day = 1 ) {
		return isset( $this->items[ $day ] )
			? $this->items[ $day ]
			: array();
	}

	/**
	 * Get posts for the day
	 *
	 * @since 0.1.0
	 *
	 * @param int $day
	 *
	 * @return string
	 */
	protected function get_day_posts( $day = 1 ) {

		// Get posts and bail if none
		$posts = $this->get_day_queried_posts( $day );
		if ( empty( $posts ) ) {
			return '';
		}

		// Start an output buffer
		ob_start();

		// Loop through today's posts
		foreach ( $posts as $post ) : ?>

			<a id="event-pointer-<?php echo esc_attr( $post->ID ); ?>" href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>" class="<?php echo $this->get_day_post_classes( $post->ID ); ?>"><?php echo esc_html( get_the_title( $post->ID ) ); ?></a>

		<?php endforeach;

		return ob_get_clean();
	}

	/**
	 * Get classes for post in day
	 *
	 * @since 0.1.0
	 *
	 * @param int $post_id
	 */
	protected function get_day_post_classes( $post_id = 0 ) {
		return join( ' ', get_post_class( '', $post_id ) );
	}

	/**
	 * Is the current calendar view today
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	protected function is_today( $month, $day, $year ) {
		$_month = (bool) ( $month == date_i18n( 'n' ) );
		$_day   = (bool) ( $day   == date_i18n( 'j' ) );
		$_year  = (bool) ( $year  == date_i18n( 'Y' ) );

		return (bool) ( true === $_month && true === $_day && true === $_year );
	}

	/**
	 * Get classes for table cell
	 *
	 * @since 0.1.0
	 *
	 * @param   type  $iterator
	 * @param   type  $start_day
	 *
	 * @return  type
	 */
	protected function get_day_classes( $iterator = 1, $start_day = 1 ) {
		$dow      = ( $iterator % 7 );
		$day_key  = sanitize_key( $GLOBALS['wp_locale']->get_weekday( $dow ) );

		$offset   = ( $iterator - $start_day ) + 1;

		// Position & day info
		$position     = "position-{$dow}";
		$day_number   = "day-{$offset}";
		$month_number = "month-{$this->month}";
		$year_number  = "year-{$this->year}";

		$is_today = $this->is_today( $this->month, $offset, $this->year )
			? 'today'
			: '';

		// Assemble classes
		$classes = array(
			$day_key,
			$is_today,
			$position,
			$day_number,
			$month_number,
			$year_number
		);

		return implode( ' ', $classes );
	}

	protected function display_calendar( $month = 1, $year = 2015 ) {

		// Get timestamp
		$timestamp  = mktime( 0, 0, 0, $month, 1, $year );
		$max_day    = date_i18n( 't', $timestamp );
		$this_month = getdate( $timestamp );
		$start_day  = $this_month['wday'];

		// Loop through days of the month
		for ( $i = 0; $i < ( $max_day + $start_day ); $i++ ) {

			// New row
			if ( ( $i % 7 ) === 0  ) {
				$this->get_week_start();
			}

			// Pad day
			if ( $i < $start_day ) {
				$this->get_week_day_pad( $i, $start_day );

			// Month day
			} else {
				$this->get_week_day( $i, $start_day );
			}

			if ( ( $i % 7 ) === 6 ) {
				$this->get_week_end();
			}
		}
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
	?>

		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<?php
				$this->extra_tablenav( $which );
				$this->pagination( $which );
			?>
			<br class="clear" />
		</div>

	<?php
	}
}
endif;
