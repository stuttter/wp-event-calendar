<?php

/**
 * Event Metaboxes
 *
 * @package Calendar/Event/Metaboxes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Walker_Category_Checklist class */
require_once( ABSPATH . 'wp-admin/includes/class-walker-category-checklist.php' );

/**
 * Extends Walker_Category_Checklist and uses radio input instead of checklist
 *
 * @since 0.4.1
 */
class WP_Event_Calendar_Walker_Category_Radio extends Walker_Category_Checklist {

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 0.4.1
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int    $depth    Depth of the term in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_terms_checklist()
	 * @param int    $id       ID of the current term.
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {

		// Note that Walker classes are trusting with their previously
		// validated object properties.
		$taxonomy = $args['taxonomy'];
		$name     = 'tax_input[' . $taxonomy . ']';

		// Maybe show popular categories tab
		$args['popular_cats'] = empty( $args['popular_cats'] )
			? array()
			: $args['popular_cats'];

		// Maybe add popular category class
		$class = in_array( $category->term_id, $args['popular_cats'] )
			? ' class="popular-category"'
			: '';

		// Maybe use already selected categories
		$args['selected_cats'] = empty( $args['selected_cats'] )
			? array()
			: $args['selected_cats'];

		/** This filter is documented in wp-includes/category-template.php */
		$output .= "\n<li id='{$taxonomy}-{$category->term_id}'$class>" .
			'<label class="selectit"><input value="' . $category->name . '" type="radio" name="' . $name . '[]" id="in-' . $taxonomy . '-' . $category->term_id . '"' .
			checked( in_array( $category->term_id, $args['selected_cats'] ), true, false ) .
			disabled( empty( $args['disabled'] ), false, false ) . ' /> ' .
			esc_html( apply_filters( 'the_category', $category->name ) ) . '</label>';
	}
}

/**
 * Event Types Metabox
 * Output radio buttons instead of the default WordPress mechanism
 *
 * @since 0.4.1
 *
 * @param array  $args
 * @param string $taxonomy
 *
 * @return array
 */
function wp_event_calendar_taxonomy_args( $args = array(), $taxonomy = '' ) {
	if ( 'event-type' === $taxonomy ) {

		$r = apply_filters( 'wp_event_calendar_taxonomy_args', array(
			'meta_box_cb' => 'post_categories_meta_box'
		), $args );

		$args = wp_parse_args( $args, $r );
	}

	return $args;
}

/**
 * Use the custom walker for radio buttons
 *
 * @since 0.4.1
 *
 * @param array $args
 *
 * @return array
 */
function wp_event_calendar_checklist_args( $args = array() ) {
	if ( 'event-type' === $args['taxonomy'] ) {

		$r = apply_filters( 'wp_event_calendar_checklist_args', array(
			'walker' => new WP_Event_Calendar_Walker_Category_Radio(),
		), $args );

		$args = wp_parse_args( $args, $r );
	}

	return $args;
}

/**
 * Event Metabox
 *
 * @since  0.1.1
*/
function wp_event_calendar_add_metabox() {
	add_meta_box(
		'wp_event_calendar_duration',
		__( 'Duration', 'wp-event-calendar' ),
		'wp_event_calendar_duration_metabox',
		wp_event_calendar_allowed_post_types(),
		'above_event_editor',
		'default'
	);

	add_meta_box(
		'wp_event_calendar_details',
		__( 'Details', 'wp-event-calendar' ),
		'wp_event_calendar_details_metabox',
		wp_event_calendar_allowed_post_types(),
		'above_event_editor',
		'default'
	);
}

/**
 * Output the event duration metabox
 *
 * @since  0.2.3
 *
 * @param WP_Post $post The post
*/
function wp_event_calendar_duration_metabox( $post = null ) {
	$meta = get_post_custom( $post->ID );
	$date = $hour = $minute = $am_pm = '';
	$end_date = $end_hour = $end_minute = $end_am_pm = '';

	/** All Day ***************************************************************/

	$all_day = ! empty( $meta['wp_event_calendar_all_day'][0] )
		? (bool) $meta['wp_event_calendar_all_day'][0]
		: false;
	$hidden = ( true === $all_day )
		? ' style="display: none;"'
		: '';

	/** Location **************************************************************/

	$location = ! empty( $meta['wp_event_calendar_location'][0] )
		? $meta['wp_event_calendar_location'][0]
		: '';

	/** Ends ******************************************************************/

	// Get date_time
	$end_date_time = ! empty( $meta['wp_event_calendar_end_date_time'][0] )
		? strtotime( $meta['wp_event_calendar_end_date_time'][0] )
		: null;

	// Only if end isn't empty
	if ( ! empty( $end_date_time ) ) {

		// Date
		$end_date = date( 'm/d/Y', $end_date_time );

		// Only if not all-day
		if ( empty( $all_day ) ) {

			// Hour
			$end_hour = date( 'h', $end_date_time );
			if ( empty( $end_hour ) ) {
				$end_hour = '';
			}

			// Minute
			$end_minute = date( 'i', $end_date_time );
			if ( empty( $end_hour ) ) {
				$end_minute = '';
			}

			// Day/night
			$end_am_pm = date( 'a', $end_date_time );
		}
	}

	/** Starts ****************************************************************/

	// Get date_time
	if ( ! empty( $_GET['start_day'] ) ) {
		$date_time = (int) $_GET['start_day'];
	} else {
		$date_time = ! empty( $meta['wp_event_calendar_date_time'][0] )
			? strtotime( $meta['wp_event_calendar_date_time'][0] )
			: null;
	}

	// Date
	if ( ! empty( $date_time ) ) {
		$date = date( 'm/d/Y', $date_time );

		// Only if not all-day
		if ( empty( $all_day ) ) {

			// Hour
			$hour = date( 'h', $date_time );
			if ( empty( $end_hour ) || empty( $hour ) ) {
				$hour = '';
			}

			// Minute
			$minute = date( 'i', $date_time );
			if ( empty( $hour ) && empty( $end_minute ) ) {
				$minute = '';
			}

			// Day/night
			$am_pm = date( 'a', $date_time );
		}
	}

	/** Repeat ****************************************************************/

	// Interval
	$interval = ! empty( $meta['wp_event_calendar_repeat'][0] )
		? $meta['wp_event_calendar_repeat'][0]
		: '';

	// Filter the intervals
	$options = apply_filters( 'wp_event_calendar_intervals', array(
		'0'      => __( 'Never',   'wp-event-calendar' ),
		'10'     => __( 'Weekly',  'wp-event-calendar' ),
		'100'    => __( 'Monthly', 'wp-event-calendar' ),
		'1000'   => __( 'Yearly',  'wp-event-calendar' )
	) );

	/** Let's Go! *************************************************************/

	// Start an output buffer
	ob_start(); ?>

	<input type="hidden" name="wp_event_calendar_metabox_nonce" value="<?php echo wp_create_nonce( 'wp_event_calendar' ); ?>" />
	<table class="form-table rowfat">
		<tbody>
			<tr>
				<th>
					<label for="wp_event_calendar_all_day" class="screen-reader-text"><?php esc_html_e( 'All Day', 'wp-event-calendar' ); ?></label>
				</th>

				<td>
					<label>
						<input type="checkbox" name="wp_event_calendar_all_day" id="wp_event_calendar_all_day" value="1" <?php checked( $all_day ); ?> />
						<?php esc_html_e( 'All-day event', 'wp-event-calendar' ); ?>
					</label>
				</td>
			</tr>

			<tr>
				<th>
					<label for="wp_event_calendar_date"><?php esc_html_e( 'Start', 'wp-event-calendar'); ?></label>
				</th>

				<td>
					<input type="text" class="wp_event_calendar_datepicker" name="wp_event_calendar_date" id="wp_event_calendar_date" value="<?php echo esc_attr( $date ); ?>" placeholder="mm/dd/yyyy" />
					<div class="event-time" <?php echo $hidden; ?>>
						<span class="wp_event_calendar_time_separator"><?php esc_html_e( ' at ', 'wp-event-alendar' ); ?></span>
						<?php wp_event_calendar_time_dropdown( array(
							'first'    => '',
							'id'       => 'wp_event_calendar_time_hour',
							'name'     => 'wp_event_calendar_time_hour',
							'items'    => wp_event_calendar_get_hours(),
							'selected' => $hour
						) ); ?>
						<span class="wp_event_calendar_time_separator">:</span>
						<?php wp_event_calendar_time_dropdown( array(
							'first'    => '',
							'id'       => 'wp_event_calendar_time_minute',
							'name'     => 'wp_event_calendar_time_minute',
							'items'    => wp_event_calendar_get_minutes(),
							'selected' => $minute
						) ); ?>
						<select name="wp_event_calendar_time_am_pm">
							<option value="am" <?php selected( $am_pm, 'am' ); ?>><?php esc_html_e( 'AM', 'wp-event-calendar' ); ?></option>
							<option value="pm" <?php selected( $am_pm, 'pm' ); ?>><?php esc_html_e( 'PM', 'wp-event-calendar' ); ?></option>
						</select>
					</div>
				</td>

			</tr>

			<tr>
				<th>
					<label for="wp_event_calendar_end_date"><?php esc_html_e( 'End', 'wp-event-calendar'); ?></label>
				</th>

				<td>
					<input type="text" class="wp_event_calendar_datepicker" name="wp_event_calendar_end_date" id="wp_event_calendar_end_date" value="<?php echo esc_attr( $end_date ); ?>" placeholder="mm/dd/yyyy" />
					<div class="event-time" <?php echo $hidden; ?>>
						<span class="wp_event_calendar_time_separator"><?php esc_html_e( ' at ', 'wp-event-alendar' ); ?></span>
						<?php wp_event_calendar_time_dropdown( array(
							'first'    => '',
							'id'       => 'wp_event_calendar_end_time_hour',
							'name'     => 'wp_event_calendar_end_time_hour',
							'items'    => wp_event_calendar_get_hours(),
							'selected' => $end_hour
						) ); ?>
						<span class="wp_event_calendar_time_separator">:</span>
						<?php wp_event_calendar_time_dropdown( array(
							'first'    => '',
							'id'       => 'wp_event_calendar_end_time_minute',
							'name'     => 'wp_event_calendar_end_time_minute',
							'items'    => wp_event_calendar_get_minutes(),
							'selected' => $end_minute
						) ); ?>
						<select class="wp_event_calendar_end_time_am_pm" name="wp_event_calendar_end_time_am_pm">
							<option value="am" <?php selected( $end_am_pm, 'am' ); ?>><?php esc_html_e( 'AM', 'wp-event-calendar' ); ?></option>
							<option value="pm" <?php selected( $end_am_pm, 'pm' ); ?>><?php esc_html_e( 'PM', 'wp-event-calendar' ); ?></option>
						</select>
					</div>
				</td>
			</tr>

			<?php if ( apply_filters( 'wp_event_calendar_location', true ) ) : ?>

				<tr>
					<th>
						<label for="wp_event_calendar_location"><?php esc_html_e( 'Location', 'wp-event-calendar' ); ?></label>
					</th>

					<td>
						<label>
							<textarea name="wp_event_calendar_location" id="wp_event_calendar_location" placeholder="<?php esc_html_e( '(Optional)', 'wp-event-calendar' ); ?>"><?php echo esc_textarea( $location ); ?></textarea>
						</label>
					</td>
				</tr>

			<?php endif; ?>

			<tr>
				<th>
					<label for="wp_event_calendar_repeat"><?php esc_html_e( 'Repeat', 'wp-event-calendar' ); ?></label>
				</th>

				<td>
					<select name="wp_event_calendar_repeat" class="wp_event_calendar_repeat" id="wp_event_calendar_repeat">

						<?php foreach ( $options as $key => $option ) : ?>

							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $interval ); ?>><?php echo esc_html( $option ); ?></option>

						<?php endforeach; ?>

					</select>

					<?php /*
					<label for="wp_event_calendar_expire"><?php esc_html_e( 'until', 'wp-event-calendar' ); ?></label>

					<input type="text" class="wp_event_calendar_datepicker" name="wp_event_calendar_expire" id="wp_event_calendar_expire" value="<?php echo esc_attr( $expire ); ?>" placeholder="mm/dd/yyyy" />
					*/ ?>
				</td>
			</tr>
		</tbody>
	</table>

	<?php

	// End & flush the output buffer
	ob_end_flush();
}

/**
 * Output the event duration metabox
 *
 * @since  1.1.0
 *
 * @param WP_Post $post The post
*/
function wp_event_calendar_details_metabox( $post = null ) {
	wp_editor( $post->post_content, 'post_content' );
}

/**
 * Offset hour based on meridem
 *
 * @since 0.2.2
 *
 * @param  int     $hour
 * @param  string  $meridiem
 *
 * @return int
 */
function wp_event_calendar_adjust_hour_for_meridiem( $hour = 0, $meridiem = 'am' ) {

	// Store new hour
	$new_hour = $hour;

	// Bump by 12 hours
	if ( 'pm' === $meridiem && ( $new_hour < 12 ) ) {
		$new_hour += 12;

	// Decrease by 12 hours
	} elseif ( 'am' === $meridiem && ( $new_hour >= 12 ) ) {
		$new_hour -= 12;
	}

	// Filter & return
	return (int) apply_filters( 'wp_event_calendar_adjust_hour_for_meridiem', $new_hour, $hour, $meridiem );
}

/**
 * Metabox save
 *
 * @since  0.1.1
 *
 * @return int|void
 */
function wp_event_calendar_metabox_save( $post_id = 0 ) {

	// Bail if no nonce or nonce check fails
	if ( empty( $_POST['wp_event_calendar_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['wp_event_calendar_metabox_nonce'], 'wp_event_calendar' ) ) {
		return $post_id;
	}

	// Bail on autosave, ajax, or bulk
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return $post_id;
	}

	// Get the post type
	$post_type = get_post_type( $post_id );

	// Only save event metadata to supported post types
	if ( ! post_type_supports( $post_type, 'events' ) ) {
		return $post_id;
	}

	// Bail if revision
	if ( wp_is_post_revision( $post_id ) ) {
		return $post_id;
	}

	// Get post type object
	$post_type_object = get_post_type_object( $post_type );

	// Bail if user cannot edit this event
	if ( ! current_user_can( $post_type_object->cap->edit_post, $post_id ) ) {
		return $post_id;
	}

	/** Location **************************************************************/

	// Get event location
	$location = ! empty( $_POST['wp_event_calendar_location'] )
		? wp_kses( $_POST['wp_event_calendar_location'], array() )
		: '';

	/** Starts ****************************************************************/

	// Get calendar date
	$date = ! empty( $_POST['wp_event_calendar_date'] )
		? strtotime( sanitize_text_field( $_POST['wp_event_calendar_date'] ) )
		: current_time( 'timestamp' );

	// Hour
	$hour = ! empty( $_POST['wp_event_calendar_time_hour'] )
		? sanitize_text_field( $_POST['wp_event_calendar_time_hour'] )
		: 0;

	// Minutes
	$minutes = ! empty( $_POST['wp_event_calendar_time_minute'] )
		? sanitize_text_field( $_POST['wp_event_calendar_time_minute'] )
		: 0;

	// Day/night
	$am_pm = ! empty( $_POST['wp_event_calendar_time_am_pm'] )
		? sanitize_text_field( $_POST['wp_event_calendar_time_am_pm'] )
		: 'am';

	/** Ends ******************************************************************/

	// Calendar date is set
	$end_date = ! empty( $_POST['wp_event_calendar_end_date'] )
		? strtotime( sanitize_text_field( $_POST['wp_event_calendar_end_date'] ) )
		: null;

	// Hour
	$end_hour = ! empty( $_POST['wp_event_calendar_end_time_hour'] )
		? sanitize_text_field( $_POST['wp_event_calendar_end_time_hour'] )
		: 0;

	// Minutes
	$end_minutes = ! empty( $_POST['wp_event_calendar_end_time_minute'] )
		? sanitize_text_field( $_POST['wp_event_calendar_end_time_minute'] )
		: 0;

	// Day/night
	$end_am_pm = ! empty( $_POST['wp_event_calendar_end_time_am_pm']  )
		? sanitize_text_field( $_POST['wp_event_calendar_end_time_am_pm'] )
		: 'am';

	/** Repeat ****************************************************************/

	// Repeat
	$repeat = ! empty( $_POST['wp_event_calendar_repeat'] )
		? sanitize_key( $_POST['wp_event_calendar_repeat'] )
		: '';

	// Expire
	$expire = ! empty( $_POST['wp_event_calendar_expire'] )
		? sanitize_text_field( $_POST['wp_event_calendar_expire'] )
		: '';

	/** All Day ***************************************************************/

	// Get all-day status
	$all_day = ! empty( $_POST['wp_event_calendar_all_day'] )
		? (bool) $_POST['wp_event_calendar_all_day']
		: false;

	// Set all day if no end date
	if ( ( false === $all_day ) && ( empty( $minutes ) && empty( $hour ) && empty( $end_minutes ) && empty( $end_hour ) ) ) {
		if ( empty( $end_date ) || ( $date === $end_date ) ) {

			// Make all-day event
			$all_day = true;

			// Make single-day event
			if ( empty( $end_date ) ) {
				$end_date = $date;
			}
		}
	}

	/** Combine ***************************************************************/

	// Maybe tweak hours
	$hour     = wp_event_calendar_adjust_hour_for_meridiem( $hour,     $am_pm     );
	$end_hour = wp_event_calendar_adjust_hour_for_meridiem( $end_hour, $end_am_pm );

	// Make timestamps from pieces
	$date     = mktime( intval( $hour     ), intval( $minutes     ), 0, date( 'm', $date     ), date( 'd', $date     ), date( 'Y', $date     ) );
	$end_date = mktime( intval( $end_hour ), intval( $end_minutes ), 0, date( 'm', $end_date ), date( 'd', $end_date ), date( 'Y', $end_date ) );

	// End dates can't be before start dates
	if ( $end_date <= $date ) {
		$end_date = strtotime( '+30 minutes', $date );
	}

	/** Save ******************************************************************/

	// Save the start date & time
	! empty( $date )
		? update_post_meta( $post_id, 'wp_event_calendar_date_time', gmdate( 'Y-m-d H:i:s', $date ) )
		: delete_post_meta( $post_id, 'wp_event_calendar_date_time' );

	// Save the end date & time
	! empty( $end_date )
		? update_post_meta( $post_id, 'wp_event_calendar_end_date_time', gmdate( 'Y-m-d H:i:s', $end_date ) )
		: delete_post_meta( $post_id, 'wp_event_calendar_end_date_time' );

	// Save location
	! empty( $location )
		? update_post_meta( $post_id, 'wp_event_calendar_location', $location )
		: delete_post_meta( $post_id, 'wp_event_calendar_location' );

	// Save all-day
	! empty( $all_day )
		? update_post_meta( $post_id, 'wp_event_calendar_all_day', 1 )
		: delete_post_meta( $post_id, 'wp_event_calendar_all_day' );

	// Save if repeating
	! empty( $repeat )
		? update_post_meta( $post_id, 'wp_event_calendar_repeat', $repeat )
		: delete_post_meta( $post_id, 'wp_event_calendar_repeat' );

	// Save only if repeating
	! empty( $expire ) && ( 'never' !== $repeat )
		? update_post_meta( $post_id, 'wp_event_calendar_expire', strtotime( $expire ) )
		: delete_post_meta( $post_id, 'wp_event_calendar_expire' );
}
