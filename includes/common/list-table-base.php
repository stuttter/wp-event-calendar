<?php

/**
 * Calendar List Table Base Class
 *
 * @package Calendar/ListTables/Base
 *
 * @see WP_Posts_List_Table
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Include the main list table class if it's not included
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// No list table class, so something went very wrong
if ( class_exists( 'WP_List_Table' ) ) :
/**
 * Event table
 *
 * This list table is responsible for showing events in a traditional table,
 * even though it extends the `WP_List_Table` class. Tables & lists & tables.
 */
class WP_Event_Calendar_List_Table extends WP_List_Table {

	/**
	 * The year being viewed
	 *
	 * @since 0.1.0
	 *
	 * @var int
	 */
	protected $year = 2015;

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
	 * The exact day being viewed based on year/month/day
	 *
	 * @since 0.1.8
	 *
	 * @var int
	 */
	protected $today = '';

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
	 * The all-day items in the current query
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	public $all_day_items = array();

	/**
	 * The multi-day items in the current query
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	public $multi_day_items = array();

	/**
	 * The items with pointers
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	public $pointers = array();

	/**
	 * The mode of the current view
	 *
	 * @since 0.1.8
	 *
	 * @var string
	 */
	public $mode = 'month';

	/**
	 * The beginning boundary for the current view
	 *
	 * @since 0.1.8
	 *
	 * @var string
	 */
	public $view_start = '';

	/**
	 * The end boundary for the current view
	 *
	 * @since 0.1.8
	 *
	 * @var string
	 */
	public $view_end = '';

	/**
	 * All-day meta of the current event
	 *
	 * @since 0.1.5
	 *
	 * @var bool
	 */
	protected $item_all_day = false;

	/**
	 * Start meta of the current event
	 *
	 * @since 0.1.5
	 *
	 * @var bool
	 */
	protected $item_start = '';

	/**
	 * End meta of the current event
	 *
	 * @since 0.1.5
	 *
	 * @var bool
	 */
	protected $item_end = '';

	/**
	 * Number of days item is for
	 *
	 * @since 0.1.5
	 *
	 * @var bool
	 */
	protected $item_days = '';

	/**
	 * The main constructor method
	 */
	public function __construct( $args = array() ) {

		// Ready the pointer content
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_pointers_footer' ) );

		// Set year, month, & day
		$this->year  = $this->get_year();
		$this->month = $this->get_month();
		$this->day   = $this->get_day();

		// Set "today" based on current request
		$this->today = strtotime( "{$this->year}/{$this->month}/{$this->day}" );

		// Set modes
		$this->modes = $this->get_modes();

		// Setup arguments
		$r = wp_parse_args( $args, array(
			'singular' => esc_html__( 'Event',  'wp-event-calendar' ),
			'plural'   => esc_html__( 'Events', 'wp-event-calendar' )
		) );

		// Pass arguments into parent
		parent::__construct( $r );
	}

	/**
	 * Get the possible list table modes
	 *
	 * @since 0.1.8
	 *
	 * @return array
	 */
	protected function get_modes() {
		return apply_filters( 'wp_event_calendar_list_table_modes', array(
			'month' => __( 'Month', 'wp-event-calendar' ),
			'week'  => __( 'Week',  'wp-event-calendar' ),
			'day'   => __( 'Day',   'wp-event-calendar' )
		) );
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

		// Persistent `post_status`
		if ( isset( $_GET['post_status'] ) ) {
			$args['post_status'] = sanitize_key( $_GET['post_status'] );
		}

		// Persistent searches
		if ( isset( $_GET['s'] ) ) {
			$args['s'] = urlencode( $_GET['s'] );
		}

		// Setup `page` & `mode` arguments
		$args['page'] = $post_type . '-calendar';
		$args['mode'] = $this->mode;

		// Add args & return
		return add_query_arg( $args, admin_url( 'edit.php' ) );
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
	protected function get_sortable_columns() {
		return array();
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
		return (int) isset( $_REQUEST['cm'] )
			? (int) $_REQUEST['cm']
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
		return (int) isset( $_REQUEST['cd'] )
			? (int) $_REQUEST['cd']
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
		return (int) isset( $_REQUEST['cy'] )
			? (int) $_REQUEST['cy']
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
			? sanitize_key( $_REQUEST['post_status'] )
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
			'hidden',
			'passed'
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
			? sanitize_key( $_REQUEST['orderby'] )
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
	 * Get the meta_query for the current mode
	 *
	 * @since 0.3.2
	 *
	 * @return array
	 */
	protected function get_meta_query() {
		return wp_event_calendar_get_meta_query( array(
			'mode'  => $this->mode,
			'start' => $this->view_start,
			'end'   => $this->view_end
		) );
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
	protected function get_max() {
		$user_option = get_user_option( get_current_screen()->base . 'per_day' );

		if ( empty( $user_option ) ) {
			$user_option = 10;
		}

		return (int) $user_option;
	}

	/**
	 * Get a list of CSS classes for the list table table tag.
	 *
	 * @since 0.1.0
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
		} elseif ( empty( $_GET['post_status'] ) ) {
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

		$status_links['all'] = '<a href="' . esc_url( remove_query_arg( 'post_status', $base_url ) ) . '" class="' . $class . '">' . $all_inner_html . '</a>';

		// Other links
		$post_statuses = get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' );

		// Loop through statuses and compile array of available ones
		foreach ( $post_statuses as $status ) {

			// Set variable to trick PHP
			$status_name = $status->name;

			// "Passed" status is irrelevant in calendar view
			if ( 'passed' === $status_name ) {
				continue;
			}

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
	 * Prepare items for list-table display
	 *
	 * @since 0.1.0
	 *
	 * @uses $this->_column_headers
	 * @uses $this->get_columns()
	 * @uses $this->get_orderby()
	 * @uses $this->get_order()
	 */
	public function prepare_items() {

		// Set the mode
		$this->set_mode();

		// Set column headers
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			array()
		);

		// Query for posts for this month only
		$this->query = new WP_Query( $this->main_query_args() );

		// Max per day
		$max_per = $this->get_max();

		// Rearrange posts into an array keyed by day of the month
		foreach ( $this->query->posts as $post ) {

			// Get start & end
			$this->item_all_day = (bool) get_post_meta( $post->ID, 'wp_event_calendar_all_day',       true );
			$this->item_start   =        get_post_meta( $post->ID, 'wp_event_calendar_date_time',     true );
			$this->item_end     =        get_post_meta( $post->ID, 'wp_event_calendar_end_date_time', true );

			// Format start
			if ( ! empty( $this->item_start ) ) {
				$this->item_start = strtotime( $this->item_start );
			}

			// Format end
			if ( ! empty( $this->item_end ) ) {
				$this->item_end = strtotime( $this->item_end );
			}

			if ( empty( $this->item_start ) ) {
				continue;
			}

			// Convert dates to timestamps that exclude the timestamp
			$start_date_timestamp = strtotime( date( 'F j, Y', $this->item_start ) );
			$end_date_timestamp   = strtotime( date( 'F j, Y', $this->item_end   ) );

			// Get the number of seconds between each timestamp's date
			$diff = abs( $end_date_timestamp - $start_date_timestamp );

			// Calculate full days spanned
			$this->item_days = ceil( $diff / DAY_IN_SECONDS ) + 1;

			// Prepare pointer & item
			$this->setup_item( $post, $max_per );
		}
	}

	/**
	 *
	 * @return type
	 */
	protected function main_query_args( $args = array() ) {

		// Events
		if ( post_type_supports( $this->screen->post_type, 'events' ) ) {
			$defaults = array(
				'post_type'           => $this->screen->post_type,
				'post_status'         => $this->get_post_status(),
				'posts_per_page'      => -1,
				'orderby'             => 'meta_value',
				'order'               => $this->get_order(),
				'hierarchical'        => false,
				'ignore_sticky_posts' => true,
				's'                   => $this->get_search(),
				'meta_query'          => $this->get_meta_query()
			);

		// All others
		} else {
			$defaults = array(
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
			);
		}

		// Parse the arguments
		$r = wp_parse_args( $args, $defaults );

		return apply_filters( 'wp_event_calendar_month_query', $r, $args, $defaults );
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
	 * This method forces WordPress to always show our calendar, and never to
	 * trigger the `no_items()` method.
	 *
	 * @since 0.1.0
	 *
	 * @return boolean
	 */
	public function has_items() {
		return true;
	}

	/**
	 * Add a post to the item array, keyed by day
	 *
	 * @todo Repeat & expire
	 *
	 * @since 0.1.1
	 *
	 * @param  object  $post
	 * @param  int     $max
	 */
	protected function setup_item( $post = false, $max = 10 ) {

		// Calculate start day
		if ( ! empty( $this->item_start ) ) {
			$start_day = date_i18n( 'j', $this->item_start );
		} else {
			$start_day = 0;
		}

		// Calculate end day
		if ( ! empty( $this->item_end ) ) {
			$end_day = date_i18n( 'j', $this->item_end );
		} else {
			$end_day = $start_day;
		}

		// Start the days loop with the start day
		$day  = (int) $start_day;
		$type = 'items';

		// Loop through days
		while ( $day <= $end_day ) {

			// Setup the pointer for each day
			$this->setup_pointer( $post, $day );

			// Add post to item types for each day in it's duration
			if ( empty( $this->{$type}[ $day ] ) || ( $max > count( $this->{$type}[ $day ] ) ) ) {
				$this->{$type}[ $day ][ $post->ID ] = $post;
			}

			// Bump the day
			++$day;
		}
	}

	/**
	 * Get the already queried posts for a given day
	 *
	 * @since 0.1.8
	 *
	 * @param int $iterator
	 *
	 * @return array
	 */
	protected function get_queried_items( $iterator = 1, $type = 'items' ) {
		return isset( $this->{$type}[ $iterator ] )
			? $this->{$type}[ $iterator ]
			: array();
	}

	/**
	 * Get posts for a given cell
	 *
	 * @since 0.1.0
	 *
	 * @param int $iterator
	 *
	 * @return string
	 */
	protected function get_posts_for_cell( $iterator = 1, $type = 'items' ) {

		// Get posts and bail if none
		$posts = $this->get_queried_items( $iterator, $type );
		if ( empty( $posts ) ) {
			return '';
		}

		// Start an output buffer
		ob_start();

		// Loop through today's posts
		foreach ( $posts as $post ) :

			// Setup the pointer ID
			$ponter_id = "{$post->ID}-{$iterator}";

			// Get the post link
			$post_link = get_edit_post_link( $post->ID );

			// Handle empty titles
			$post_title = get_the_title( $post->ID );
			if ( empty( $post_title ) ) {
				$post_title = esc_html__( '(No title)', 'wp-event-calendar' );
			} ?>

			<a id="event-pointer-<?php echo esc_attr( $ponter_id ); ?>" href="<?php echo esc_url( $post_link ); ?>" class="<?php echo $this->get_event_classes( $post->ID ); ?>"><?php echo esc_html( $post_title ); ?></a>

		<?php endforeach;

		return ob_get_clean();
	}

	/** Event Meta ************************************************************/

	/**
	 * Get the date of the event
	 *
	 * @since 0.1.1
	 *
	 * @param  object $post
	 * @param  string $date
	 *
	 * @return string
	 */
	protected function get_event_date( $post = false, $date = '' ) {
		$retval = date_i18n( get_option( 'date_format' ), $date );

		return apply_filters( 'wp_event_calendar_event_date', $retval, $post, $date );
	}

	/**
	 * Get the date of the event
	 *
	 * @since 0.1.1
	 *
	 * @param  object $post
	 * @param  string $date
	 *
	 * @return string
	 */
	protected function get_event_time( $post = false, $date = '' ) {
		$retval = date_i18n( get_option( 'time_format' ), $date );

		return apply_filters( 'wp_event_calendar_event_time', $retval, $post, $date );
	}

	/** Pointers **************************************************************/

	/**
	 * Add a post to the pointers array
	 *
	 * @since 0.1.1
	 *
	 * @param  object  $post
	 * @param  int     $day
	 */
	protected function setup_pointer( $post = false, $day = 1 ) {

		// Rebase the pointer content
		$pointer_content = array();

		// Pointer content
		$pointer_content[] = '<h3 class="' . $this->get_event_classes( $post->ID ) . '">' . $this->get_pointer_title( $post ) . '</h3>';
		$pointer_content[] = '<p>' . implode( '<br>', $this->get_pointer_text( $post ) ) . '</p>';

		// Filter pointer content specifically
		$pointer_content = apply_filters( 'wp_event_calendar_pointer_content', $pointer_content, $post );

		// Filter the entire pointer array
		$pointer = apply_filters( 'wp_event_calendar_pointer', array(
			'content'   => implode( '', $pointer_content ),
			'anchor_id' => "#event-pointer-{$post->ID}-{$day}",
			'edge'      => 'top',
			'align'     => 'left'
		), $post );

		// Add pointer to pointers array
		$this->pointers[] = $pointer;
	}

	/**
	 * Return the pointer title text
	 *
	 * @since 0.1.1
	 *
	 * @param   object $post
	 * @return  string
	 */
	protected function get_pointer_title( $post = false ) {

		// Get post type object
		$post_type_object = get_post_type_object( $post->post_type );

		// Handle empty titles
		$title = ! empty( $post->post_title )
			? $post->post_title
			: esc_html__( '(No title)', 'wp-event-calendar' );

		// Title links to edit
		if ( current_user_can( $post_type_object->cap->edit_post, $post->ID ) ) {
			$retval = '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">'  . esc_js( $title ) . '</a>';

		// No title link
		} else {
			$retval = esc_js( $title );
		}

		// Filter & return the pointer title
		return apply_filters( 'wp_event_calendar_pointer_title', $retval, $post );
	}

	/**
	 * Return the pointer title text
	 *
	 * @since 0.1.1
	 *
	 * @param   object  $post
	 *
	 * @return  string
	 */
	protected function get_pointer_text( $post = false ) {
		$pointer_text = $this->get_pointer_metadata( $post );

		// Append with new-line if metadata exists
		$new_line = ! empty( $pointer_text )
			? '<br>'
			: '';

		// Special case for password protected posts
		if ( ! empty( $post->post_password ) ) {
			$pointer_text[] = $new_line . esc_html__( 'Password required.', 'wp-event-calendar' );

		// Post is not protected
		} else {

			// No content
			if ( empty( $post->post_content ) ) {
				$pointer_text[] = $new_line . esc_html__( 'No description.', 'wp-event-calendar' );

			// Attempt to sanitize content
			} else {

				// Strip new lines & reduce to allowed tags
				$the_content = wptexturize( $post->post_content );
				$the_content = wpautop( $the_content, true );
				$the_content = wp_kses( $the_content, $this->get_allowed_pointer_tags() );
				$the_content = preg_replace( '#\r|\n#', '<br>', $the_content );

				// Texturize
				$pointer_text[] = $new_line . $the_content;
			}
		}

		// Filter & return the pointer title
		return apply_filters( 'wp_event_calendar_pointer_text', $pointer_text, $post );
	}

	/**
	 * Get event metadata for display in a pointer
	 *
	 * @since 0.1.0
	 *
	 * @param  object  $post
	 *
	 * @return array
	 */
	protected function get_pointer_metadata( $post = false ) {
		$pointer_metadata = array();

		// All day event
		if ( ( true === $this->item_all_day ) ) {
			$pointer_metadata[] = '<strong>' . esc_html__( 'All Day', 'wp-event-calendar' ) . '</strong>';

		} else {

			// Date & Time
			if ( ! empty( $this->item_start ) ) {
				$pointer_metadata[] = '<strong>' . esc_html__( 'Start', 'wp-event-calendar' ) . '</strong>';
				$pointer_metadata[] = sprintf( esc_html__( '%s on %s', 'wp-event-calendar' ), $this->get_event_time( $post, $this->item_start ), $this->get_event_date( $post, $this->item_start ) );
			}

			// Date & Time
			if ( ! empty( $this->item_end ) ) {

				// Extra padding
				if ( ! empty( $this->item_start ) ) {
					$pointer_metadata[] = '';
				}

				$pointer_metadata[] = '<strong>' . esc_html__( 'End', 'wp-event-calendar' ) . '</strong>';
				$pointer_metadata[] = sprintf( esc_html__( '%s on %s', 'wp-event-calendar' ), $this->get_event_time( $post, $this->item_end ), $this->get_event_date( $post, $this->item_end ) );
			}
		}

		// Filter & return the pointer title
		return apply_filters( 'wp_event_calendar_pointer_metadata', $pointer_metadata, $post );
	}

	/**
	 * Return array of allowed HTML tags to use in admin pointers
	 *
	 * @since 0.1.1
	 *
	 * @return allay Allowed HTML tags
	 */
	protected function get_allowed_pointer_tags() {
		return apply_filters( 'wp_event_calendar_get_allowed_pointer_tags', array(
			'a'      => array(),
			'strong' => array(),
			'em'     => array(),
			'img'    => array()
		) );
	}

	/**
	 * Output the pointers for each event
	 *
	 * This is a pretty horrible way to accomplish this, but it's currently the
	 * way WordPress's pointer API expects to work, so be it.
	 *
	 * @since 0.1.1
	 */
	public function admin_pointers_footer() {
		?>

<!-- Start Event Pointers -->
<script type="text/javascript" id="wp-event-calendar-pointers">
	/* <![CDATA[ */
	( function( $ ) {
		$( 'table.calendar .events-for-cell a' ).click( function( event ) {
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

	/** Output & Markup *******************************************************/

	/**
	 * Display the table
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 */
	public function display() {

		// Start an output buffer
		ob_start();

		// Top
		$this->display_tablenav( 'top' ); ?>

		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
			<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
			</thead>

			<tbody id="the-list" data-wp-lists='list:<?php echo $this->_args['singular']; ?>'>
				<?php $this->display_mode(); ?>
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

		// End and flush the buffer
		ob_end_flush();
	}

	/**
	 * Message to be displayed when there are no items
	 */
	public function no_items() {
		// Do nothing; calendars always have rows
	}

	/**
	 * Get classes for post in day
	 *
	 * @since 0.1.0
	 *
	 * @param int $post_id
	 */
	protected function get_event_classes( $post_id = 0 ) {

		// Empty classes array
		$classes = array();

		// Is the event all day?
		$classes[] = get_post_meta( $post_id, 'wp_event_calendar_all_day' )
			? 'all-day'
			: '';

		// Is the event all day?
		$classes[] = get_post_meta( $post_id, 'wp_event_calendar_location' )
			? 'has-location'
			: '';

		// Get event terms
		$terms = wp_get_object_terms( $post_id, get_object_taxonomies( 'event' ) );
		foreach ( $terms as $term ) {
			$classes[] = "tax-{$term->taxonomy}";
			$classes[] = "term-{$term->slug}";
		}

		// Remove any empties
		$classes = get_post_class( $classes, $post_id );

		// Join & return
		return join( ' ', $classes );
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

	/**
	 * Display a calendar by month and year
	 *
	 * @since 0.1.0
	 */
	protected function display_mode() {
		// Performed by subclass
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 0.1.0
	 *
	 * @access protected
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
		?>

		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php

				// Output Month, Year tablenav
				echo $this->extra_tablenav( $which );

				// Output year/month pagination
				echo $this->pagination( $which );

				// Output month/week/day switcher
				echo $this->view_switcher( $which ); ?>

			<br class="clear" />
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
		}

		// Start an output buffer
		ob_start(); ?>

		<label for="cm" class="screen-reader-text"><?php esc_html_e( 'Switch to this month', 'wp-event-calendar' ); ?></label>
		<select name="cm" id="cm">

			<?php for ( $month_index = 1; $month_index <= 12; $month_index++ ) : ?>

				<option value="<?php echo esc_attr( $month_index ); ?>" <?php selected( $month_index, $this->month ); ?>><?php echo $GLOBALS['wp_locale']->get_month( $month_index ); ?></option>

			<?php endfor;?>

		</select>

		<label for="cy" class="screen-reader-text"><?php esc_html_e( 'Switch to this year', 'wp-event-calendar' ); ?></label>
		<input type="number" name="cy" id="cy" value="<?php echo (int) $this->year; ?>" size="5">

		<?php

		// Allow additional tablenav output before the "View" button
		do_action( 'wp_event_calendar_before_tablenav_view' );

		// Output the "View" button
		submit_button( esc_html__( 'View', 'wp-event-calendar' ), 'action', '', false, array( 'id' => 'doaction' ) );

		// Filter & return
		return apply_filters( 'wp_event_calendar_get_extra_tablenav', ob_get_clean() );
	}

	/**
	 * Paginate through months & years
	 *
	 * @since 0.1.0
	 *
	 * @param array $args
	 */
	protected function pagination( $args = array() ) {

		// Parse args
		$r = wp_parse_args( $args, array(
			'which'  => 'top',
			'small'  => '1 month',
			'large'  => '1 year',
			'labels' => array(
				'today'      => esc_html__( 'Today',    'wp-event-calendar' ),
				'next_small' => esc_html__( 'Next',     'wp-event-calendar' ),
				'next_large' => esc_html__( 'Next',     'wp-event-calendar' ),
				'prev_small' => esc_html__( 'Previous', 'wp-event-calendar' ),
				'prev_large' => esc_html__( 'Previous', 'wp-event-calendar' )
			)
		) );

		// No botton pagination
		if ( 'top' !== $r['which'] ) {
			return;
		}

		// Base URLs
		$today = $this->get_base_url();

		// Calculate previous & next weeks & months
		$prev_small = strtotime( "-{$r['small']}", $this->today );
		$next_small = strtotime( "+{$r['small']}", $this->today );
		$prev_large = strtotime( "-{$r['large']}", $this->today );
		$next_large = strtotime( "+{$r['large']}", $this->today );

		// Week
		$prev_small_d = date_i18n( 'j', $prev_small );
		$prev_small_m = date_i18n( 'n', $prev_small );
		$prev_small_y = date_i18n( 'Y', $prev_small );
		$next_small_d = date_i18n( 'j', $next_small );
		$next_small_m = date_i18n( 'n', $next_small );
		$next_small_y = date_i18n( 'Y', $next_small );

		// Month
		$prev_large_d = date_i18n( 'j', $prev_large );
		$prev_large_m = date_i18n( 'n', $prev_large );
		$prev_large_y = date_i18n( 'Y', $prev_large );
		$next_large_d = date_i18n( 'j', $next_large );
		$next_large_m = date_i18n( 'n', $next_large );
		$next_large_y = date_i18n( 'Y', $next_large );

		// Setup month args
		$prev_small_args = array( 'cy' => $prev_small_y, 'cm' => $prev_small_m, 'cd' => $prev_small_d );
		$prev_large_args = array( 'cy' => $prev_large_y, 'cm' => $prev_large_m, 'cd' => $prev_large_d );
		$next_small_args = array( 'cy' => $next_small_y, 'cm' => $next_small_m, 'cd' => $next_small_d );
		$next_large_args = array( 'cy' => $next_large_y, 'cm' => $next_large_m, 'cd' => $next_large_d );

		// Setup links
		$prev_small_link = add_query_arg( $prev_small_args, $today );
		$next_small_link = add_query_arg( $next_small_args, $today );
		$prev_large_link = add_query_arg( $prev_large_args, $today );
		$next_large_link = add_query_arg( $next_large_args, $today );

		// Start an output buffer
		ob_start(); ?>

		<div class="tablenav-pages previous">
			<a class="previous-page" href="<?php echo esc_url( $prev_large_link ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $r['labels']['prev_large'] ); ?></span>
				<span aria-hidden="true">&laquo;</span>
			</a>
			<a class="previous-page" href="<?php echo esc_url( $prev_small_link ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $r['labels']['prev_small'] ); ?></span>
				<span aria-hidden="true">&lsaquo;</span>
			</a>

			<a href="<?php echo esc_url( $today ); ?>" class="previous-page">
				<span class="screen-reader-text"><?php echo esc_html( $r['labels']['today'] ); ?></span>
				<span aria-hidden="true">&Colon;</span>
			</a>

			<a class="next-page" href="<?php echo esc_url( $next_small_link ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $r['labels']['next_small'] ); ?></span>
				<span aria-hidden="true">&rsaquo;</span>
			</a>

			<a class="next-page" href="<?php echo esc_url( $next_large_link ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $r['labels']['next_large'] ); ?></span>
				<span aria-hidden="true">&raquo;</span>
			</a>
		</div>

		<?php

		// Filter & return
		return apply_filters( 'wp_event_calendar_get_pagination', ob_get_clean(), $r, $args );
	}

	/**
	 * Display the view switcher
	 *
	 * @since 0.1.8
	 *
	 * @param string $which
	 */
	protected function view_switcher( $which = 'top' ) {

		// Only switch on top
		if ( 'top' !== $which ) {
			return;
		}

		// Start an output buffer
		ob_start(); ?>

		<div class="view-switch">
			<input type="hidden" name="mode" value="<?php echo esc_attr( $this->mode ); ?>" />

			<?php

			// Loop through modes
			foreach ( $this->modes as $mode => $title ) :

				$url = add_query_arg( 'mode', $mode );

				// Setup classes
				$classes = array( 'view-' . $mode );
				if ( $this->mode === $mode ) {
					$classes[] = 'current';
				} ?>


				<a href="<?php echo esc_url( $url ); ?>" class="<?php echo implode( ' ', $classes ); ?>" id="view-switch-<?php echo esc_attr( $mode ); ?>" title="<?php echo esc_attr( $title ); ?>">
					<span class='screen-reader-text'><?php echo esc_html( $title ); ?></span>
				</a>

			<?php endforeach; ?>

		</div>

		<?php

		// Return the output buffer
		return apply_filters( 'wp_event_calendar_get_view_switcher', ob_get_clean(), $which );
	}
}
endif;
