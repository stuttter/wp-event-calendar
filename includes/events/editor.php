<?php

/**
 * Events Editor
 *
 * @package Plugins/Events/Editor
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Custom metaboxes above th editor
 *
 * @global string $post_type
 * @global array $post
 */
function wp_events_calendar_editor_above() {
	global $post_type, $post;

	do_meta_boxes( $post_type, 'above_event_editor', $post );

	// Description title
	if ( ! in_array( $post_type, wp_event_calendar_allowed_post_types(), true ) ) {
		return;
	} ?>

	<div class="meta-box-sortables">
		<div class="wp-event-calendar-editor postbox">
			<div class="handlediv" title="<?php esc_html_e( 'Click to toggle', 'wp-event-calendar'); ?>"><br></div>
			<h3 class="hndle ui-sortable-handle"><span><?php esc_html_e( 'Notes', 'wp-event-calendar'); ?></span></h3>
			<div class="inside" style="padding-top: 12px; margin-top: 0;">

	<?php
}

/**
 * Custom metaboxes above th editor
 *
 * @global string $post_type
 */
function wp_events_calendar_editor_below() {
	global $post_type, $post;

	// Description title
	if ( ! in_array( $post_type, wp_event_calendar_allowed_post_types() ) ) {
		return;
	} ?>

			</div>
		</div>
	</div>

	<?php

	// Below editor
	do_meta_boxes( $post_type, 'below_event_editor', $post );
}

/**
 * Remove media buttons for custom post types
 *
 * @param array $settings
 */
function wp_events_calendar_editor_settings( $settings = array() ) {
	$post_type = get_post_type();

	// No buttons on custom post types
	if ( in_array( $post_type, ct_get_inventory_post_types() ) ) {
		$settings['media_buttons'] = false;
		$settings['dfw'] = false;
		$settings['teeny'] = true;
		$settings['tinymce'] = false;
		$settings['quicktags'] = false;
	}

	return $settings;
}

/**
 * Maybe remove expanding editor for our post types
 *
 * @param boolean $expand
 * @param string  $post_type
 *
 * @return boolean
 */
function wp_events_calendar_editor_expand( $expand = true, $post_type = '' ) {

	// No expanding for our post types
	if ( ( true === $expand ) && in_array( $post_type, ct_get_inventory_post_types() ) ) {
		$expand = false;
	}

	return $expand;
}
