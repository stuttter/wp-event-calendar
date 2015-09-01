<?php

/**
 * Event Metaboxes
 *
 * @package Calendar/Event/Metaboxes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Event Metabox
 *
 * @since  0.1.1
*/
function wp_event_calendar_add_metabox() {
	add_meta_box( 'wp_event_calendar_details', __( 'Details', 'wp-event-calendar' ), 'wp_event_calendar_details_metabox', 'event', 'normal', 'default' );
}

/**
 * Output the event details metabox
 *
 * @since  0.1.0
*/
function wp_event_calendar_details_metabox() {
	global $post;

	$meta = get_post_custom( $post->ID );

	/** Starts ****************************************************************/

	// Get date_time
	$date_time = ! empty( $meta['wp_event_calendar_date_time'][0] )
		? strtotime( $meta['wp_event_calendar_date_time'][0] )
		: 0;

	// Date
	$date = date( 'm/d/Y', $date_time );
	if ( '01/01/1970' === $date ) {
		$date = '';
	}

	// Hour
	$hour = date( 'H', $date_time );
	if ( '00' === $hour ) {
		$hour = '';
	}

	// Adjustment
	if ( $hour > 12 ) {
		$hour = $hour - 12;
	}

	// Minute
	$minute = date( 'i', $date_time );
	if ( '00' === $minute && empty( $hour ) ) {
		$minute = '';
	}

	// Day/night
	$am_pm = date( 'a', $date_time );

	/** Ends ******************************************************************/

	// Get date_time
	$end_date_time = ! empty( $meta['wp_event_calendar_end_date_time'][0] )
		? strtotime( $meta['wp_event_calendar_end_date_time'][0] )
		: 0;

	// Date
	$end_date = date( 'm/d/Y', $end_date_time );
	if ( '01/01/1970' === $end_date ) {
		$end_date = '';
	}

	// Hour
	$end_hour = date( 'H', $end_date_time );
	if ( '00' === $end_hour ) {
		$end_hour = '';
	}

	// Adjustment
	if ( $end_hour > 12 ) {
		$end_hour = $end_hour - 12;
	}

	// Minute
	$end_minute = date( 'i', $end_date_time );
	if ( '00' === $end_minute && empty( $end_hour ) ) {
		$end_minute = '';
	}

	// Day/night
	$end_am_pm = date( 'a', $end_date_time );

	/** Repeat ****************************************************************/

	// Interval
	$interval = ! empty( $meta['wp_event_calendar_repeat'][0] )
		? $meta['wp_event_calendar_repeat'][0]
		: '';

	// Filter the intervals
	$options = apply_filters( 'wp_event_calendar_intervals', array(
		'never'   => __( 'Never',   'wp-event-calendar' ),
		'weekly'  => __( 'Weekly',  'wp-event-calendar' ),
		'monthly' => __( 'Monthly', 'wp-event-calendar' ),
		'yearly'  => __( 'Yearly',  'wp-event-calendar' )
	) );

	// When to stop repeating?
	$expire = ! empty( $meta['wp_event_calendar_expire'][0] )
		? date( 'm/d/Y', $meta['wp_event_calendar_expire'][0] )
		: '';

	/** Let's Go! *************************************************************/

	// Start an output buffer
	ob_start(); ?>

	<input type="hidden" name="wp_event_calendar_metabox_nonce" value="<?php echo wp_create_nonce( 'wp_event_calendar' ); ?>" />
	<table class="form-table rowfat">
		<tr>
			<td>
				<label for="wp_event_calendar_date"><?php esc_html_e( 'Start Day', 'wp-event-calendar'); ?></label>
			</td>

			<td>
				<input type="text" class="wp_event_calendar_datepicker" name="wp_event_calendar_date" id="wp_event_calendar_date" value="<?php echo esc_attr( $date ); ?>" placeholder="mm/dd/yyyy" /><br>
			</td>

			<td>
				<label for="wp_event_calendar_time_hour"><?php esc_html_e( 'Start Time', 'wp-event-calendar'); ?></label>
			</td>

			<td>
				<input type="text" class="small-text" name="wp_event_calendar_time_hour" id="wp_event_calendar_time_hour" value="<?php echo esc_attr( $hour ); ?>" placeholder="10" />
				<span class="wp_event_calendar_time_separator">&nbsp;:&nbsp;</span>
				<input type="number" min="00" max="59" step="1" class="small-text wp_event_calendar_minutes" name="wp_event_calendar_time_minute" value="<?php echo esc_attr( $minute ); ?>" placeholder="00" />
				<select name="wp_event_calendar_time_am_pm">
					<option value="am" <?php selected( $am_pm, 'am' ); ?>><?php esc_html_e( 'AM', 'wp-event-calendar' ); ?></option>
					<option value="pm" <?php selected( $am_pm, 'pm' ); ?>><?php esc_html_e( 'PM', 'wp-event-calendar' ); ?></option>
				</select>
			</td>
		</tr>

		<tr>
			<td>
				<label for="wp_event_calendar_end_date"><?php esc_html_e( 'End Day', 'wp-event-calendar' ); ?></label>
			</td>

			<td>
				<input type="text" class="wp_event_calendar_datepicker" name="wp_event_calendar_end_date" id="wp_event_calendar_end_date" value="<?php echo esc_attr( $end_date ); ?>" placeholder="mm/dd/yyyy" />
			</td>

			<td>
				<label for="wp_event_calendar_end_time_hour"><?php esc_html_e( 'End Time', 'wp-event-calendar'); ?></label>
			</td>

			<td>
				<input type="text" class="small-text" name="wp_event_calendar_end_time_hour" id="wp_event_calendar_end_time_hour" value="<?php echo esc_attr( $end_hour ); ?>" placeholder="11" />
				<span class="wp_event_calendar_time_separator">&nbsp;:&nbsp;</span>
				<input type="number" min="00" max="59" step="1" class="small-text wp_event_calendar_minutes" name="wp_event_calendar_end_time_minute" value="<?php echo esc_attr( $end_minute ); ?>" placeholder="00" />
				<select class="wp_event_calendar_end_time_am_pm" name="wp_event_calendar_end_time_am_pm">
					<option value="am" <?php selected( $end_am_pm, 'am' ); ?>><?php esc_html_e( 'AM', 'wp-event-calendar' ); ?></option>
					<option value="pm" <?php selected( $end_am_pm, 'pm' ); ?>><?php esc_html_e( 'PM', 'wp-event-calendar' ); ?></option>
				</select>
			</td>
		</tr>

		<tr>
			<td>
				<label for="wp_event_calendar_repeat"><?php esc_html_e( 'Repeat', 'wp-event-calendar' ); ?></label>
			</td>

			<td>
				<select name="wp_event_calendar_repeat" class="wp_event_calendar_repeat" id="wp_event_calendar_repeat">

					<?php foreach ( $options as $key => $option ) : ?>

						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $interval ); ?>><?php echo esc_html( $option ); ?></option>

					<?php endforeach; ?>

				</select>
			</td>

			<td>
				<label for="wp_event_calendar_expire"><?php esc_html_e( 'Until', 'wp-event-calendar'); ?></label>
			</td>

			<td>
				<input type="text" class="wp_event_calendar_datepicker" name="wp_event_calendar_expire" id="wp_event_calendar_expire" value="<?php echo esc_attr( $expire ); ?>" placeholder="mm/dd/yyyy" />
			</td>
		</tr>
	</table>

	<?php

	// End & flush the output buffer
	ob_end_flush();
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

	// Only save event metadata to event post type
	if ( 'event' !== get_post_type( $post_id ) ) {
		return $post_id;
	}

	// Bail if revision
	if ( wp_is_post_revision( $post_id ) ) {
		return $post_id;
	}

	// Bail if user cannot edit this event
	if ( ! current_user_can( 'edit_event', $post_id ) ) {
		return $post_id;
	}

	/** Starts ****************************************************************/

	// Calendar date is set
	if ( ! empty( $_POST['wp_event_calendar_date'] ) ) {

		$date = ! empty( $_POST['wp_event_calendar_date'] )
			? sanitize_text_field( $_POST['wp_event_calendar_date'] )
			: '';
		$date = strtotime( $date );

	// Default to today if time is passed without a date
	} elseif ( ! empty( $_POST['wp_event_calendar_time_hour'] ) || ! empty( $_POST['wp_event_calendar_time_minute'] ) ) {
		$date = current_time( 'timestamp' );
	}

	// Times
	if ( ! empty( $_POST['wp_event_calendar_time_hour'] ) || ! empty( $_POST['wp_event_calendar_time_minute'] ) ) {

		// Day
		$dom   = date( 'd', $date );

		// Month
		$month = date( 'm', $date );

		// Year
		$year  = date( 'Y', $date );

		// Hour
		$hour = ! empty( $_POST['wp_event_calendar_time_hour'] )
			? sanitize_text_field( $_POST['wp_event_calendar_time_hour'] )
			: '00';

		// Minutes
		$minutes = ! empty( $_POST['wp_event_calendar_time_minute'] )
			? sanitize_text_field( $_POST['wp_event_calendar_time_minute'] )
			: '00';

		// Day/night
		$am_pm = ! empty( $_POST['wp_event_calendar_time_am_pm'] )
			? sanitize_text_field( $_POST['wp_event_calendar_time_am_pm'] )
			: '';

		// Adjust
		if ( 'pm' === $am_pm && ( $hour < 12 ) ) {
			$hour += 12;
		} elseif ( 'am' === $am_pm && ( $hour >= 12 ) ) {
			$hour -= 12;
		}

		// Calculate date & time
		$final_date_time = gmdate( 'Y-m-d H:i:s', mktime( intval( $hour ), intval( $minutes ), 0, $month, $dom, $year ) );

		// Ketchup & mayonaise, mixed together
		update_post_meta( $post_id, 'wp_event_calendar_date_time', $final_date_time );

	// No time (all day event)
	} else {
		update_post_meta( $post_id, 'wp_event_calendar_date_time', gmdate( 'Y-m-d H:i:s', $date ) );
	}

	/** Ends ******************************************************************/

	// Calendar date is set
	if ( ! empty( $_POST['wp_event_calendar_end_date'] ) ) {

		$end_date = ! empty( $_POST['wp_event_calendar_end_date'] )
			? sanitize_text_field( $_POST['wp_event_calendar_end_date'] )
			: '';
		$end_date = strtotime( $end_date );

	// Default to today if time is passed without a date
	} elseif ( ! empty( $_POST['wp_event_calendar_end_time_hour'] ) || ! empty( $_POST['wp_event_calendar_end_time_hour'] ) ) {
		$end_date = $date;
	}

	// Times
	if ( ! empty( $_POST['wp_event_calendar_end_time_hour'] ) || ! empty( $_POST['wp_event_calendar_end_time_minute'] ) ) {

		// Day
		$end_dom   = date( 'd', $end_date );

		// Month
		$end_month = date( 'm', $end_date );

		// Year
		$end_year  = date( 'Y', $end_date );

		// Hour
		$end_hour = ! empty( $_POST['wp_event_calendar_end_time_hour'] )
			? sanitize_text_field( $_POST['wp_event_calendar_end_time_hour'] )
			: '00';

		// Minutes
		$end_minutes = ! empty( $_POST['wp_event_calendar_end_time_minute'] )
			? sanitize_text_field( $_POST['wp_event_calendar_end_time_minute'] )
			: '00';

		// Day/night
		$end_am_pm = ! empty( $_POST['wp_event_calendar_end_time_am_pm']  )
			? sanitize_text_field( $_POST['wp_event_calendar_end_time_am_pm']  )
			: '';

		// Adjust
		if ( ( 'pm' === $end_am_pm ) && ( $end_hour < 12 ) ) {
			$end_hour += 12;
		} elseif ( ( 'am' === $end_am_pm ) && ( $end_hour >= 12 ) ) {
			$end_hour -= 12;
		}

		// Calculate date & time
		$final_end_date_time = gmdate( 'Y-m-d H:i:s', mktime( intval( $end_hour ), intval( $end_minutes ), 0, $end_month, $end_dom, $end_year ) );

		// Ranch & blue-cheese, mixed together
		update_post_meta( $post_id, 'wp_event_calendar_end_date_time', $final_end_date_time );

	// No time (all day event)
	} else {

		// Use date only
		if ( $end_date !== $date ) {
			update_post_meta( $post_id, 'wp_event_calendar_end_date_time', gmdate( 'Y-m-d H:i:s', $end_date ) );
		} else {
			delete_post_meta( $post_id, 'wp_event_calendar_end_date_time' );
		}
	}

	// Maybe delete existing end-day data
	if ( $final_date_time === $final_end_date_time ) {
		delete_post_meta( $post_id, 'wp_event_calendar_end_date_time' );
	}

	/** Repeat ****************************************************************/

	// Repeat
	$repeat = ! empty( $_POST['wp_event_calendar_repeat'] )
		? sanitize_key( $_POST['wp_event_calendar_repeat'] )
		: '';

	// Save if repeating
	if ( ! empty( $repeat ) ) {
		update_post_meta( $post_id, 'wp_event_calendar_repeat', $repeat );
	} else {
		delete_post_meta( $post_id, 'wp_event_calendar_repeat' );
	}

	// Expire
	$expire = ! empty( $_POST['wp_event_calendar_expire'] )
		? sanitize_text_field( $_POST['wp_event_calendar_expire'] )
		: '';

	// Save only if repeating
	if ( ! empty( $expire ) && ( 'never' !== $repeat ) ) {
		update_post_meta( $post_id, 'wp_event_calendar_expire', strtotime( $expire ) );
	} else {
		delete_post_meta( $post_id, 'wp_event_calendar_expire' );
	}

	// All done. Holy schmoly.
}
