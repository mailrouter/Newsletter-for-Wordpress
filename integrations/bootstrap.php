<?php

/**
 * Try to include a file before each integration's settings page
 *
 * @param NL4WP_Integration $integration
 * @param array $opts
 * @ignore
 */
function nl4wp_admin_before_integration_settings(NL4WP_Integration $integration, $opts)
{
    $file = dirname(__FILE__) . sprintf('/%s/admin-before.php', $integration->slug);

    if (file_exists($file)) {
        include $file;
    }
}

/**
 * Try to include a file before each integration's settings page
 *
 * @param NL4WP_Integration $integration
 * @param array $opts
 * @ignore
 */
function nl4wp_admin_after_integration_settings(NL4WP_Integration $integration, $opts)
{
    $file = dirname(__FILE__) . sprintf('/%s/admin-after.php', $integration->slug);

    if (file_exists($file)) {
        include $file;
    }
}

add_action('nl4wp_admin_before_integration_settings', 'nl4wp_admin_before_integration_settings', 30, 2);
add_action('nl4wp_admin_after_integration_settings', 'nl4wp_admin_after_integration_settings', 30, 2);

// Register core integrations
nl4wp_register_integration('ninja-forms-2', 'NL4WP_Ninja_Forms_v2_Integration', true);
nl4wp_register_integration('wp-comment-form', 'NL4WP_Comment_Form_Integration');
nl4wp_register_integration('wp-registration-form', 'NL4WP_Registration_Form_Integration');
nl4wp_register_integration('buddypress', 'NL4WP_BuddyPress_Integration');
nl4wp_register_integration('woocommerce', 'NL4WP_WooCommerce_Integration');
nl4wp_register_integration('easy-digital-downloads', 'NL4WP_Easy_Digital_Downloads_Integration');
nl4wp_register_integration('contact-form-7', 'NL4WP_Contact_Form_7_Integration', true);
nl4wp_register_integration('events-manager', 'NL4WP_Events_Manager_Integration');
nl4wp_register_integration('memberpress', 'NL4WP_MemberPress_Integration');
nl4wp_register_integration('affiliatewp', 'NL4WP_AffiliateWP_Integration');
nl4wp_register_integration('give', 'NL4WP_Give_Integration');


nl4wp_register_integration('custom', 'NL4WP_Custom_Integration', true);
$dir = dirname(__FILE__);
require $dir . '/ninja-forms/bootstrap.php';
require $dir . '/wpforms/bootstrap.php';
require $dir . '/gravity-forms/bootstrap.php';


