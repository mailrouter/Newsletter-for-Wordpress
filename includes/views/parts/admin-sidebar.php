<?php

defined( 'ABSPATH' ) or exit;

/**
 * @ignore
 */
function _nl4wp_admin_sidebar_support_notice() {
	?>
	<div class="nl4wp-box">
		<h4 class="nl4wp-title"><?php echo esc_html__( 'Looking for help?', 'newsletter-for-wp' ); ?></h4>
		<p><?php echo __( 'We have some resources available to help you in the right direction.', 'newsletter-for-wp' ); ?></p>
		<ul class="ul-square">
			<li><a href="https://kb.mc4wp.com/#utm_source=wp-plugin&utm_medium=newsletter-for-wp&utm_campaign=sidebar"><?php echo esc_html__( 'Knowledge Base', 'newsletter-for-wp' ); ?></a></li>
			<li><a href="https://wordpress.org/plugins/newsletter-for-wp/faq/"><?php echo esc_html__( 'Frequently Asked Questions', 'newsletter-for-wp' ); ?></a></li>
		</ul>
		<p><?php echo sprintf( __( 'If your answer can not be found in the resources listed above, please use the <a href="%s">support forums on WordPress.org</a>.' ), 'https://wordpress.org/support/plugin/newsletter-for-wp' ); ?></p>
		<p><?php echo sprintf( __( 'Found a bug? Please <a href="%s">open an issue on GitHub</a>.' ), 'https://github.com/ibericode/newsletter-for-wordpress/issues' ); ?></p>
	</div>
	<?php
}

/**
 * @ignore
 */
function _nl4wp_admin_sidebar_other_plugins() {

    echo '<div class="nl4wp-box">';
    echo '<h4 class="nl4wp-title">' . __( 'Other plugins by ibericode', 'newsletter-for-wp' ) . '</h4>';

    echo '<ul style="margin-bottom: 0;">';

    // Boxzilla
    echo '<li>';
    echo sprintf( '<strong><a href="%s">Boxzilla Pop-ups</a></strong><br />', 'https://boxzillaplugin.com/#utm_source=wp-plugin&utm_medium=newsletter-for-wp&utm_campaign=sidebar' );
    echo  __( 'Pop-ups or boxes that slide-in with a newsletter sign-up form. A sure-fire way to grow your email lists.', 'newsletter-for-wp');
    echo '</li>';

    // HTML Forms
    echo '<li>';
    echo sprintf( '<strong><a href="%s">HTML Forms</a></strong><br />', 'https://www.htmlforms.io/#utm_source=wp-plugin&utm_medium=newsletter-for-wp&utm_campaign=sidebar' );
    echo  __( 'Super flexible forms using native HTML. Just like with Newsletter for WordPress forms but for other purposes, like a contact form.', 'newsletter-for-wp' );
    echo '</li>';

    echo '</ul>';
    echo '</div>';
}

/* NL_COMMENT - start
* Non mostra help e boxilla
*/
/*add_action( 'nl4wp_admin_sidebar', '_nl4wp_admin_sidebar_other_plugins', 40 );
*/

/* NL_COMMENT - start
* Non mostra help e boxilla
*/
/*add_action( 'nl4wp_admin_sidebar', '_nl4wp_admin_sidebar_support_notice', 50 );
*/


/**
 * Runs when the sidebar is outputted on Newsletter for WordPress settings pages.
 *
 * Please note that not all pages have a sidebar.
 *
 * @since 3.0
 */
do_action( 'nl4wp_admin_sidebar' );
