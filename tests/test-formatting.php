<?php

define( 'ABSPATH', __DIR__ . '/' );
date_default_timezone_set( 'UTC' );

$wp_event_calendar_test_options = array(
	'date_format' => 'm/d/Y',
	'time_format' => 'g:i a',
);
$wp_event_calendar_test_meta = array();
$wp_event_calendar_saved_meta = array();

function get_option( $option ) {
	global $wp_event_calendar_test_options;

	return $wp_event_calendar_test_options[ $option ];
}

function apply_filters( $hook, $value ) {
	return $value;
}

function get_post_custom( $post_id ) {
	global $wp_event_calendar_test_meta;

	return $wp_event_calendar_test_meta;
}

function date_i18n( $format, $timestamp, $gmt = false ) {
	return gmdate( $format, $timestamp );
}

function wp_create_nonce( $action ) {
	return 'test-nonce';
}

function esc_attr( $value ) {
	return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
}

function esc_html( $value ) {
	return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
}

function esc_textarea( $value ) {
	return esc_html( $value );
}

function esc_html_e( $value, $domain = '' ) {
	echo esc_html( $value );
}

function __( $value, $domain = '' ) {
	return $value;
}

function checked( $checked, $current = true, $echo = true ) {
	$result = ( $checked == $current ) ? ' checked="checked"' : '';

	if ( $echo ) {
		echo $result;
	}

	return $result;
}

function selected( $selected, $current = true, $echo = true ) {
	$result = ( $selected == $current ) ? ' selected="selected"' : '';

	if ( $echo ) {
		echo $result;
	}

	return $result;
}

function wp_parse_args( $args, $defaults = array() ) {
	return array_merge( $defaults, $args );
}

function wp_verify_nonce( $nonce, $action ) {
	return 'test-nonce' === $nonce;
}

function get_post_type( $post_id = 0 ) {
	return 'event';
}

function post_type_supports( $post_type, $feature ) {
	return 'event' === $post_type && 'events' === $feature;
}

function wp_is_post_revision( $post_id ) {
	return false;
}

function get_post_type_object( $post_type ) {
	$object = new stdClass();
	$object->cap = new stdClass();
	$object->cap->edit_post = 'edit_event';

	return $object;
}

function current_user_can( $capability, $post_id ) {
	return true;
}

function wp_kses( $value, $allowed_html ) {
	return strip_tags( $value );
}

function sanitize_text_field( $value ) {
	return trim( $value );
}

function sanitize_key( $value ) {
	return preg_replace( '/[^a-z0-9_-]/', '', strtolower( $value ) );
}

function current_time( $type ) {
	return strtotime( '2026-04-03 00:00:00 UTC' );
}

function update_post_meta( $post_id, $key, $value ) {
	global $wp_event_calendar_saved_meta;

	$wp_event_calendar_saved_meta[ $key ] = $value;
}

function delete_post_meta( $post_id, $key ) {
	global $wp_event_calendar_saved_meta;

	unset( $wp_event_calendar_saved_meta[ $key ] );
}

function wp_event_calendar_assert_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException( sprintf( '%s Expected %s, got %s.', $message, var_export( $expected, true ), var_export( $actual, true ) ) );
	}
}

require dirname( __DIR__ ) . '/wp-event-calendar/includes/common/functions.php';
require dirname( __DIR__ ) . '/wp-event-calendar/includes/events/metaboxes.php';

wp_event_calendar_assert_same( 'mm/dd/yy', wp_event_calendar_get_datepicker_format(), 'U.S. date format conversion failed.' );

$wp_event_calendar_test_options['date_format'] = 'F j, Y';
wp_event_calendar_assert_same( 'F j, Y', wp_event_calendar_get_date_format(), 'Text month format was not preserved.' );
wp_event_calendar_assert_same( 'MM d, yy', wp_event_calendar_get_datepicker_format(), 'WordPress default date format conversion failed.' );

$wp_event_calendar_test_options['date_format'] = 'd.m.Y';
wp_event_calendar_assert_same( 'dd.mm.yy', wp_event_calendar_get_datepicker_format(), 'European date format conversion failed.' );
wp_event_calendar_assert_same( gmmktime( 0, 0, 0, 12, 31, 2026 ), wp_event_calendar_parse_date( '31.12.2026' ), 'Configured date parsing failed.' );
wp_event_calendar_assert_same( false, wp_event_calendar_parse_numeric_date( '31-12-2026', 'd.m.Y' ), 'Numeric parsing accepted mismatched separators.' );

$wp_event_calendar_test_options['date_format'] = 'd/m/y';
wp_event_calendar_assert_same( gmmktime( 0, 0, 0, 12, 31, 2026 ), wp_event_calendar_parse_date( '31/12/26' ), 'Two-digit year parsing failed.' );
wp_event_calendar_assert_same( gmmktime( 0, 0, 0, 12, 31, 2069 ), wp_event_calendar_parse_date( '31/12/69' ), 'Two-digit year lower boundary parsing failed.' );
wp_event_calendar_assert_same( gmmktime( 0, 0, 0, 12, 31, 1970 ), wp_event_calendar_parse_date( '31/12/70' ), 'Two-digit year upper boundary parsing failed.' );

$wp_event_calendar_test_options['date_format'] = 'j \\d\\e F Y';
wp_event_calendar_assert_same( "d 'de' MM yy", wp_event_calendar_get_datepicker_format(), 'Escaped date format conversion failed.' );

$wp_event_calendar_test_options['date_format'] = 'm/d/Y';
wp_event_calendar_assert_same( gmmktime( 0, 0, 0, 3, 4, 2026 ), wp_event_calendar_parse_date( '03/04/2026', '2026-04-03' ), 'Configured date did not take precedence over stale machine-readable data.' );

$wp_event_calendar_test_options['date_format'] = 'F j, Y';
wp_event_calendar_assert_same( gmmktime( 0, 0, 0, 4, 3, 2026 ), wp_event_calendar_parse_date( 'April 3, 2026', '2026-04-03' ), 'Machine-readable fallback parsing failed.' );
wp_event_calendar_assert_same( false, wp_event_calendar_parse_date( 'May 4, 2026', '2026-04-03' ), 'Mismatched visible and machine-readable dates were accepted.' );

$wp_event_calendar_test_options['date_format'] = 'jS F Y';
wp_event_calendar_assert_same( 'Y-m-d', wp_event_calendar_get_date_format(), 'Unsupported date format did not use the safe fallback.' );
wp_event_calendar_assert_same( 'yy-mm-dd', wp_event_calendar_get_datepicker_format(), 'Unsupported datepicker format did not use the safe fallback.' );

$wp_event_calendar_test_options['date_format'] = 'F Y';
wp_event_calendar_assert_same( 'Y-m-d', wp_event_calendar_get_date_format(), 'Incomplete date format did not use the safe fallback.' );

$wp_event_calendar_test_options['date_format'] = 'l, F j, Y';
wp_event_calendar_assert_same( 'l, F j, Y', wp_event_calendar_get_date_format(), 'Weekday date format was not preserved.' );
wp_event_calendar_assert_same( 'DD, MM d, yy', wp_event_calendar_get_datepicker_format(), 'Weekday datepicker format conversion failed.' );

$wp_event_calendar_test_options['date_format'] = 'Y年n月j日';
wp_event_calendar_assert_same( 'Y年n月j日', wp_event_calendar_get_date_format(), 'Localized date literals were not preserved.' );
wp_event_calendar_assert_same( 'yy年m月d日', wp_event_calendar_get_datepicker_format(), 'Localized date literal conversion failed.' );

$wp_event_calendar_test_options['date_format'] = 'd.m.Y';
wp_event_calendar_assert_same( false, wp_event_calendar_parse_date( 'tomorrow' ), 'Relative date input was accepted.' );

$wp_event_calendar_test_options['time_format'] = 'g:i a';
wp_event_calendar_assert_same( false, wp_event_calendar_uses_24_hour_time(), '12-hour time format detection failed.' );
wp_event_calendar_assert_same( array( '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12' ), wp_event_calendar_get_hours(), '12-hour choices failed.' );
wp_event_calendar_assert_same( 0, wp_event_calendar_adjust_hour_for_meridiem( 12, 'am' ), '12-hour midnight conversion failed.' );
wp_event_calendar_assert_same( 12, wp_event_calendar_adjust_hour_for_meridiem( 12, 'pm' ), '12-hour noon conversion failed.' );

$wp_event_calendar_test_options['time_format'] = 'g:i';
wp_event_calendar_assert_same( false, wp_event_calendar_uses_24_hour_time(), '12-hour format without a meridiem marker was misidentified.' );

$wp_event_calendar_test_options['time_format'] = 'H:i';
wp_event_calendar_assert_same( true, wp_event_calendar_uses_24_hour_time(), '24-hour time format detection failed.' );
wp_event_calendar_assert_same( array( '00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23' ), wp_event_calendar_get_hours(), '24-hour choices failed.' );

$wp_event_calendar_test_options['time_format'] = 'G:i';
wp_event_calendar_assert_same( true, wp_event_calendar_uses_24_hour_time(), 'Unpadded 24-hour time format detection failed.' );

$wp_event_calendar_test_options['date_format'] = 'd.m.Y';
$wp_event_calendar_test_options['time_format'] = 'H:i';
$wp_event_calendar_test_meta = array(
	'wp_event_calendar_date_time'     => array( '2026-04-03 00:00:00' ),
	'wp_event_calendar_end_date_time' => array( '2026-04-03 00:30:00' ),
);
$post = new stdClass();
$post->ID = 14;

date_default_timezone_set( 'America/Chicago' );

ob_start();
wp_event_calendar_duration_metabox( $post );
$metabox = ob_get_clean();

date_default_timezone_set( 'UTC' );

wp_event_calendar_assert_same( true, false !== strpos( $metabox, 'value="03.04.2026"' ), 'Localized metabox date was not rendered.' );
wp_event_calendar_assert_same( true, false !== strpos( $metabox, 'id="wp_event_calendar_date_iso" value="2026-04-03"' ), 'Machine-readable metabox date was not rendered.' );
wp_event_calendar_assert_same( true, false !== strpos( $metabox, '<option value="00"  selected="selected">00</option>' ), 'Midnight was not selected in the 24-hour control.' );
wp_event_calendar_assert_same( false, false !== strpos( $metabox, 'name="wp_event_calendar_time_am_pm"' ), 'Meridiem control was rendered for a 24-hour clock.' );

$_POST = array(
	'wp_event_calendar_metabox_nonce'  => 'test-nonce',
	'wp_event_calendar_date'           => '03.04.2026',
	'wp_event_calendar_date_iso'       => '2026-04-03',
	'wp_event_calendar_time_hour'      => '00',
	'wp_event_calendar_time_minute'    => '00',
	'wp_event_calendar_end_date'       => '03.04.2026',
	'wp_event_calendar_end_date_iso'   => '2026-04-03',
	'wp_event_calendar_end_time_hour'  => '00',
	'wp_event_calendar_end_time_minute' => '30',
);

wp_event_calendar_metabox_save( 14 );

wp_event_calendar_assert_same( '2026-04-03 00:00:00', $wp_event_calendar_saved_meta['wp_event_calendar_date_time'], 'Midnight start time did not round-trip.' );
wp_event_calendar_assert_same( '2026-04-03 00:30:00', $wp_event_calendar_saved_meta['wp_event_calendar_end_date_time'], '24-hour end time did not round-trip.' );
wp_event_calendar_assert_same( false, isset( $wp_event_calendar_saved_meta['wp_event_calendar_all_day'] ), 'Midnight event was incorrectly converted to an all-day event.' );

$wp_event_calendar_saved_meta = array(
	'wp_event_calendar_date_time' => 'unchanged',
);
$_POST['wp_event_calendar_date'] = 'invalid';
$_POST['wp_event_calendar_date_iso'] = '';

wp_event_calendar_metabox_save( 14 );

wp_event_calendar_assert_same( array( 'wp_event_calendar_date_time' => 'unchanged' ), $wp_event_calendar_saved_meta, 'Invalid input changed event metadata.' );

echo "Formatting tests passed.\n";
