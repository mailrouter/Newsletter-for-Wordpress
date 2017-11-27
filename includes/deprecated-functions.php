<?php

/**
 * @use nl4wp_add_name_merge_vars()
 * @deprecated 4.0
 * @ignore
 *
 * @param array $merge_vars
 * @return array
 */
function nl4wp_guess_merge_vars( $merge_vars = array() ) {
	_deprecated_function( __FUNCTION__, 'Newsletter for WordPress v4.0' );
	$merge_vars = nl4wp_add_name_data( $merge_vars );
	$merge_vars = _nl4wp_update_groupings_data( $merge_vars );
	return $merge_vars;
}

/**
 * Echoes a sign-up checkbox.
 *
 * @ignore
 * @deprecated 3.0
 *
 * @use nl4wp_get_integration()
 */
function nl4wp_checkbox() {
	_deprecated_function( __FUNCTION__, 'Newsletter for WordPress v3.0' );
	nl4wp_get_integration('wp-comment-form')->output_checkbox();
}

/**
 * Echoes a Newsletter for WordPress form
 *
 * @ignore
 * @deprecated 3.0
 * @use nl4wp_show_form()
 *
 * @param int $id
 * @param array $attributes
 *
 * @return string
 *
 */
function nl4wp_form( $id = 0, $attributes = array() ) {
	_deprecated_function( __FUNCTION__, 'Newsletter for WordPress v3.0', 'nl4wp_show_form' );
	return nl4wp_show_form( $id, $attributes );
}

