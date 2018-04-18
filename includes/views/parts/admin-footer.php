<?php defined( 'ABSPATH' ) or exit;

/**
 * @ignore
 */
function _nl4wp_admin_translation_notice() {

	// show for every language other than the default
	if( stripos( get_locale(), 'en_us' ) === 0 ) {
		return;
	}

	// TODO: Check translation progress from Transifex here. Only show when < 100.

	echo '<p class="help">' . sprintf( __( 'Newsletter for WordPress is in need of translations. Is the plugin not translated in your language or do you spot errors with the current translations? Helping out is easy! Head over to <a href="%s">the translation project and click "help translate"</a>.', 'newsletter-for-wp' ), 'https://www.transifex.com/projects/p/newsletter-for-wordpress/' ) . '</p>';
}

/**
 * @ignore
 */
function _nl4wp_admin_github_notice() {

	if( strpos( $_SERVER['HTTP_HOST'], 'local' ) !== 0 && ! WP_DEBUG ) {
		return;
	}

	echo '<p class="help">Developer? Follow <a href="https://github.com/ibericode/newsletter-for-wordpress">Newsletter for WordPress on GitHub</a> or have a look at our repository of <a href="https://github.com/ibericode/nl4wp-snippets">sample code snippets</a>.</p>';

}

/**
 * @ignore
 */
function _nl4wp_admin_disclaimer_notice() {
	echo '<p class="help">' . __( 'This plugin is not developed by or affiliated with Newsletter in any way.', 'newsletter-for-wp' ) . '</p>';
}

/* NL_COMMENT - start
* Non mostra translation notice, github e disclaimer
*/
/*add_action( 'nl4wp_admin_footer', '_nl4wp_admin_translation_notice' , 20);
*/

/* NL_COMMENT - start
* Non mostra translation notice, github e disclaimer
*/
/*add_action( 'nl4wp_admin_footer', '_nl4wp_admin_github_notice', 50 );
*/

/* NL_COMMENT - start
* Non mostra translation notice, github e disclaimer
*/
/*add_action( 'nl4wp_admin_footer', '_nl4wp_admin_disclaimer_notice', 80 );
*/

?>

<div class="big-margin">

	<?php

	/**
	 * Runs while printing the footer of every Newsletter for WordPress settings page.
	 *
	 * @since 3.0
	 */
	do_action( 'nl4wp_admin_footer' ); ?>

</div>
