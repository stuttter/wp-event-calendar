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

	// Date
	$date = ! empty( $meta['wp_event_calendar_date'][0] )
		? date( 'm/d/Y', $meta['wp_event_calendar_date'][0] )
		: '';

	// Hour
	$hour = ! empty( $meta['wp_event_calendar_time_hour'][0] )
		? $meta['wp_event_calendar_time_hour'][0]
		: '';

	// Adjustment
	if ( $hour > 12 ) {
		$hour = $hour - 12;
	}

	// Minute
	$minute = ! empty( $meta['wp_event_calendar_time_minute'][0] )
		? $meta['wp_event_calendar_time_minute'][0]
		: '';

	// Day/night
	$am_pm = ! empty( $meta['wp_event_calendar_time_am_pm'][0]  )
		? $meta['wp_event_calendar_time_am_pm'][0]
		: null;

	/** Ends ******************************************************************/

	// Date
	$end_date = ! empty( $meta['wp_event_calendar_end_date'][0] )
		? date( 'm/d/Y', $meta['wp_event_calendar_end_date'][0] )
		: '';

	// Hour
	$end_hour = ! empty( $meta['wp_event_calendar_end_time_hour'][0] )
		? $meta['wp_event_calendar_end_time_hour'][0]
		: '';

	// Adjustment
	if ( $end_hour > 12 ) {
		$end_hour = $end_hour - 12;
	}

	// Minute
	$end_minute = ! empty( $meta['wp_event_calendar_end_time_minute'][0] )
		? $meta['wp_event_calendar_end_time_minute'][0]
		: '';

	// Day/Night
	$end_am_pm = ! empty( $meta['wp_event_calendar_end_time_am_pm'][0] )
		? $meta['wp_event_calendar_end_time_am_pm'][0]
		: null;

	/** Repeat ****************************************************************/

	// Interval
	$interval = ! empty( $meta['wp_event_calendar_repeat'][0] )
		? $meta['wp_event_calendar_repeat'][0]
		: '';

	// Filter the intervals
	$options = apply_filters( 'wp_event_calendar_intervals', array(
		'none'    => __( 'Never',   'wp-event-calendar' ),
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
	<table class="form-table">
		<tr>
			<td>
				<label for="wp_event_calendar_date"><?php esc_html_e( 'Start', 'wp-event-calendar'); ?></label>
			</td>

			<td>
				<input type="text" class="wp_event_calendar_datepicker" name="wp_event_calendar_date" id="wp_event_calendar_date" value="<?php echo esc_attr( $date ); ?>" placeholder="mm/dd/yyyy" /><br>
			</td>

			<td>
				<input type="text" class="small-text" name="wp_event_calendar_time_hour" value="<?php echo esc_attr( $hour ); ?>" placeholder="10" />
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
				<label for="wp_event_calendar_end_date"><?php esc_html_e( 'End', 'wp-event-calendar' ); ?></label>
			</td>

			<td>
				<input type="text" class="wp_event_calendar_datepicker" name="wp_event_calendar_end_date" id="wp_event_calendar_end_date" value="<?php echo esc_attr( $end_date ); ?>" placeholder="mm/dd/yyyy" />
			</td>

			<td>
				<input type="text" class="small-text" name="wp_event_calendar_end_time_hour" value="<?php echo esc_attr( $end_hour ); ?>" placeholder="11" />
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
				<select name="wp_event_calendar_repeat" class="wp_event_calendar_repeat">

					<?php foreach ( $options as $key => $option ) : ?>

						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $interval ); ?>><?php echo esc_html( $option ); ?></option>

					<?php endforeach; ?>

				</select>
			</td>

			<td>
				<?php esc_html_e( 'Expire', 'wp-event-calendar' ); ?>
				<input type="text" class="wp_event_calendar_datepicker" name="wp_event_calendar_expire" value="<?php echo esc_attr( $expire ); ?>" placeholder="mm/dd/yyyy" />
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

		// Date
		$date = ! empty( $_POST['wp_event_calendar_date'] )
			? sanitize_text_field( $_POST['wp_event_calendar_date'] )
			: '';
		$date = strtotime( $date );

		// Day
		$day   = date( 'D', $date );
		$dow   = date( 'w', $date );
		$dom   = date( 'd', $date );
		$doy   = date( 'z', $date );

		// Month
		$month = date( 'm', $date );

		// Year
		$year  = date( 'Y', $date );

		// Many metas
		update_post_meta( $post_id, 'wp_event_calendar_date',         $date  );
		update_post_meta( $post_id, 'wp_event_calendar_day',          $day   );
		update_post_meta( $post_id, 'wp_event_calendar_day_of_week',  $dow   );
		update_post_meta( $post_id, 'wp_event_calendar_day_of_month', $dom   );
		update_post_meta( $post_id, 'wp_event_calendar_day_of_year',  $doy   );
		update_post_meta( $post_id, 'wp_event_calendar_month',        $month );
		update_post_meta( $post_id, 'wp_event_calendar_year',         $year  );

		// Times
		if ( ! empty( $_POST['wp_event_calendar_time_hour'] ) || ! empty( $_POST['wp_event_calendar_time_minute'] ) ) {

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

			// More metas
			update_post_meta( $post_id, 'wp_event_calendar_time_hour',   $hour    );
			update_post_meta( $post_id, 'wp_event_calendar_time_minute', $minutes );
			update_post_meta( $post_id, 'wp_event_calendar_time_am_pm',  $am_pm   );

			// Calculate date & time
			$final_date_time = mktime( intval( $hour ), intval( $minutes ), 0, $month, $dom, $year );

			// Ketchup & mayonaise, mixed together
			update_post_meta( $post_id, 'wp_event_calendar_date_time', $final_date_time );

		// No time (all day event)
		} else {

			// Delete time metas
			delete_post_meta( $post_id, 'wp_event_calendar_time_hour'   );
			delete_post_meta( $post_id, 'wp_event_calendar_time_minute' );
			delete_post_meta( $post_id, 'wp_event_calendar_time_am_pm'  );

			// Use date only
			update_post_meta( $post_id, 'wp_event_calendar_date_time', $date );
		}
	}

	/** Ends ******************************************************************/

	// End date, or use start date
	$wp_event_calendar_end_date = ! empty( $_POST['wp_event_calendar_end_date'] )
		? sanitize_text_field( $_POST['wp_event_calendar_end_date'] )
		: '';

	// retrieve and store event start date / time
	if ( ! empty( $wp_event_calendar_end_date ) ) {

		$end_date = strtotime( $wp_event_calendar_end_date );

		// Day
		$end_day   = date( 'D', $end_date );
		$end_dow   = date( 'w', $end_date );
		$end_dom   = date( 'd', $end_date );
		$end_doy   = date( 'z', $end_date );

		// Month
		$end_month = date( 'm', $end_date );

		// Year
		$end_year  = date( 'Y', $end_date );

		// Times
		if ( ! empty( $_POST['wp_event_calendar_end_time_hour'] ) || ! empty( $_POST['wp_event_calendar_end_time_minute'] ) ) {

			// More metas
			update_post_meta( $post_id, 'wp_event_calendar_end_date',         $end_date  );
			update_post_meta( $post_id, 'wp_event_calendar_end_day',          $end_day   );
			update_post_meta( $post_id, 'wp_event_calendar_end_day_of_week',  $end_dow   );
			update_post_meta( $post_id, 'wp_event_calendar_end_day_of_month', $end_dom   );
			update_post_meta( $post_id, 'wp_event_calendar_end_day_of_year',  $end_doy   );
			update_post_meta( $post_id, 'wp_event_calendar_end_month',        $end_month );
			update_post_meta( $post_id, 'wp_event_calendar_end_year',         $end_year  );

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

			// More metas
			update_post_meta( $post_id, 'wp_event_calendar_end_time_hour',   $end_hour    );
			update_post_meta( $post_id, 'wp_event_calendar_end_time_minute', $end_minutes );
			update_post_meta( $post_id, 'wp_event_calendar_end_time_am_pm',  $end_am_pm   );

			// Calculate date & time
			$final_end_date_time = mktime( intval( $end_hour ), intval( $end_minutes ), 0, $end_month, $end_dom, $end_year );

			// Ranch & blue-cheese, mixed together
			update_post_meta( $post_id, 'wp_event_calendar_end_date_time', $final_end_date_time );

		// No time (all day event)
		} else {

			// Use date only
			if ( $end_date !== $date ) {

				// Delete time metas
				delete_post_meta( $post_id, 'wp_event_calendar_end_time_hour'   );
				delete_post_meta( $post_id, 'wp_event_calendar_end_time_minute' );
				delete_post_meta( $post_id, 'wp_event_calendar_end_time_am_pm'  );

				update_post_meta( $post_id, 'wp_event_calendar_end_date_time', $end_date );
			} else {
				$delete_end_data = true;
			}
		}

	// Use start date for end date
	} else {
		$delete_end_data = true;
	}

	// Maybe delete existing end-day data
	if ( true === $delete_end_data ) {
		delete_post_meta( $post_id, 'wp_event_calendar_end_date'          );
		delete_post_meta( $post_id, 'wp_event_calendar_end_day'           );
		delete_post_meta( $post_id, 'wp_event_calendar_end_day_of_week'   );
		delete_post_meta( $post_id, 'wp_event_calendar_end_day_of_month'  );
		delete_post_meta( $post_id, 'wp_event_calendar_end_day_of_year'   );
		delete_post_meta( $post_id, 'wp_event_calendar_end_month'         );
		delete_post_meta( $post_id, 'wp_event_calendar_end_year'          );
		delete_post_meta( $post_id, 'wp_event_calendar_end_time_hour'     );
		delete_post_meta( $post_id, 'wp_event_calendar_end_time_minute'   );
		delete_post_meta( $post_id, 'wp_event_calendar_end_time_am_pm'    );
		delete_post_meta( $post_id, 'wp_event_calendar_end_date_time'     );		
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
	if ( ! empty( $expire ) && ( 'none' !== $repeat ) ) {
		update_post_meta( $post_id, 'wp_event_calendar_expire', strtotime( $expire ) );
	} else {
		delete_post_meta( $post_id, 'wp_event_calendar_expire' );
	}

	// All done. Holy schmoly.
}
