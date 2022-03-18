<?php
/*
Plugin Name: Newsletter for WordPress
Plugin URI: https://github.com/mailrouter/Newsletter-for-Wordpress
Description: Newsletter for WordPress by mailrouter. Aggiunge vari metodi di iscrizione newsletter al tuo sito.
Version: 4.5.7
Author: mailrouter
Text Domain: newsletter-for-wp
Domain Path: /languages
License: GPL v3

Newsletter for WordPress
Copyright (C) 2022, Void Labs snc, info.it
forked from
Mailchimp for WordPress
Copyright (C) 2012-2022, Danny van Kooten, hi.com

integrates
Plugin Update Checker Library 4.4
http://w-shadow.com/
Copyright 2018 Janis Elsts
Released under the MIT license. See license.txt for details.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/* PLUGIN AUTOUPDATE */
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/mailrouter/Newsletter-for-Wordpress/',
	__FILE__,
	'newsletter-for-wp'
);
/* end */
// Prevent direct file access
defined('ABSPATH') or exit;

/** @ignore */
function _nl4wp_load_plugin()
{
    global $nl4wp;

    // Don't run if Newsletter for WP Pro 2.x is activated
    if (defined('NL4WP_VERSION')) {
        return false;
    }

    // bootstrap the core plugin
    define( 'NL4WP_VERSION', '4.5.7');
    /* NL_CHANGED - start
     * imposta la versione pro
     */
    define ('NL4WP_PREMIUM_VERSION', '4.5.7');
    /* NL_CHANGED - end */
    define('NL4WP_PLUGIN_DIR', dirname(__FILE__) . '/');
    define('NL4WP_PLUGIN_URL', plugins_url('/', __FILE__));
    define('NL4WP_PLUGIN_FILE', __FILE__);

    // load autoloader if function not yet exists (for compat with sitewide autoloader)
    if (! function_exists('nl4wp')) {
        require_once NL4WP_PLUGIN_DIR . 'vendor/autoload_52.php';
    }

    /**
     * @global NL4WP_Container $GLOBALS['nl4wp']
     * @name $nl4wp
     */
    $nl4wp = nl4wp();
    $nl4wp['api'] = 'nl4wp_get_api_v3';
    $nl4wp['request'] = array( 'NL4WP_Request', 'create_from_globals' );
    $nl4wp['log'] = 'nl4wp_get_debug_log';

    // forms
    $nl4wp['forms'] = new NL4WP_Form_Manager();
    $nl4wp['forms']->add_hooks();

    // integration core
    $nl4wp['integrations'] = new NL4WP_Integration_Manager();
    $nl4wp['integrations']->add_hooks();

    // Doing cron? Load Usage Tracking class.
    if (isset($_GET['doing_wp_cron']) || (defined('DOING_CRON') && DOING_CRON) || (defined('WP_CLI') && WP_CLI)) {
        NL4WP_Usage_Tracking::instance()->add_hooks();
    }

    // Initialize admin section of plugin
    if (is_admin()) {
        $admin_tools = new NL4WP_Admin_Tools();

        if (defined('DOING_AJAX') && DOING_AJAX) {
            $ajax = new NL4WP_Admin_Ajax($admin_tools);
            $ajax->add_hooks();
        } else {
            $messages = new NL4WP_Admin_Messages();
            $nl4wp['admin.messages'] = $messages;

            $newsletter = new NL4WP_Newsletter();

            $admin = new NL4WP_Admin($admin_tools, $messages, $newsletter);
            $admin->add_hooks();

            $forms_admin = new NL4WP_Forms_Admin($messages, $newsletter);
            $forms_admin->add_hooks();

            $integrations_admin = new NL4WP_Integration_Admin($nl4wp['integrations'], $messages, $newsletter);
            $integrations_admin->add_hooks();
        }
    }

    return true;
}

// bootstrap custom integrations
function _nl4wp_bootstrap_integrations()
{
    require_once NL4WP_PLUGIN_DIR . 'integrations/bootstrap.php';
}

add_action('plugins_loaded', '_nl4wp_load_plugin', 8);
add_action('plugins_loaded', '_nl4wp_bootstrap_integrations', 90);

/**
 * Flushes transient cache & schedules refresh hook.
 *
 * @ignore
 * @since 3.0
 */
function _nl4wp_on_plugin_activation()
{
    $time_string = sprintf("tomorrow 0%d:%d%d", rand(0, 8), rand(0, 5), rand(0, 9));
    wp_schedule_event(strtotime($time_string), 'daily', 'nl4wp_refresh_newsletter_lists');
}

/**
 * Clears scheduled hook for refreshing Newsletter lists.
 *
 * @ignore
 * @since 4.0.3
 */
function _nl4wp_on_plugin_deactivation()
{
    global $wpdb;
    wp_clear_scheduled_hook('nl4wp_refresh_newsletter_lists');

    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'nl4wp_newsletter_list_%'");
}

register_activation_hook(__FILE__, '_nl4wp_on_plugin_activation');
register_deactivation_hook(__FILE__, '_nl4wp_on_plugin_deactivation');
