<?php

/**
 * Gets an array of all registered integrations
 *
 * @since 3.0
 * @access public
 *
 * @return NL4WP_Integration[]
 */
function nl4wp_get_integrations() {
	return nl4wp('integrations')->get_all();
}

/**
 * Get an instance of a registered integration class
 *
 * @since 3.0
 * @access public
 *
 * @param string $slug
 *
 * @return NL4WP_Integration
 */
function nl4wp_get_integration( $slug ) {
	return nl4wp('integrations')->get( $slug );
}

/**
 * Register a new integration with Newsletter for WordPress
 *
 * @since 3.0
 * @access public
 *
 * @param string $slug
 * @param string $class
 *
 * @param bool $always_enabled
 */
function nl4wp_register_integration( $slug, $class, $always_enabled = false ) {
	return nl4wp('integrations')->register_integration( $slug, $class, $always_enabled );
}

/**
 * Deregister a previously registered integration with Newsletter for WordPress
 *
 * @since 3.0
 * @access public
 * @param string $slug
 */
function nl4wp_deregister_integration( $slug ) {
	nl4wp('integrations')->deregister_integration( $slug );
}