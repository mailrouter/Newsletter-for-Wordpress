<?php
/*
Plugin Name: Newsletter for WordPress
Plugin URI: https://github.com/mailrouter/Newsletter-for-Wordpress/
Description: Newsletter for WordPress by mailrouter. Aggiunge vari metodi di iscrizione newsletter al tuo sito.
Version: 4.14.1
Author: mailrouter
Text Domain: newsletter-for-wp
Domain Path: /languages
Requires PHP: 7.4
License: GPL v3

Newsletter for WordPress
Copyright (C) 2024, Void Labs snc, info@voidlabs.it
forked from
Mailchimp for WordPress
Copyright (C) 2012-2024, Danny van Kooten, hi@dannyvankooten.com

integrates
Plugin Update Checker Library 5.7
http://w-shadow.com/
Copyright 2026 Janis Elsts
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

phpcs:disable:PSR1.Files.SideEffects.FoundWithSymbols
 */

/* PLUGIN AUTOUPDATE */
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
	'https://github.com/mailrouter/Newsletter-for-Wordpress/',
	__FILE__,
	'newsletter-for-wp'
);
/* end */
// Prevent direct file access
defined('ABSPATH') || exit;

// bootstrap main plugin
add_action('plugins_loaded', function () {
    global $nl4wp;

    // don't run if Newsletter for WP Pro 2.x is activated
    // don't run if PHP version is lower than 7.4.0
    if (defined('NL4WP_VERSION') || PHP_VERSION_ID < 70400) {
        return;
    }

    // bootstrap the core plugin
    define( 'NL4WP_VERSION', '4.14.1');
    define('NL4WP_PLUGIN_DIR', __DIR__);
    define('NL4WP_PLUGIN_FILE', __FILE__);

    require __DIR__ . '/autoload.php';
    require __DIR__ . '/includes/default-actions.php';
    require __DIR__ . '/includes/default-filters.php';

    /**
     * @var NL4WP_Container $nl4wp
     */
    $nl4wp        = nl4wp_get_container();
    $nl4wp['api'] = 'nl4wp_get_api_v3';
    $nl4wp['log'] = 'nl4wp_get_debug_log';

    // forms
    $form_manager = new NL4WP_Form_Manager();
    $form_manager->add_hooks();
    $nl4wp['forms'] = $form_manager;

    // campaign archive

    // integration core
    $integration_manager = new NL4WP_Integration_Manager();
    $integration_manager->add_hooks();
    $nl4wp['integrations'] = $integration_manager;

    $opts = nl4wp_get_options();

    // Initialize admin section of plugin
    if (is_admin()) {
        require __DIR__ . '/includes/admin/class-legacy-captcha-notice.php';
        (new NL4WP_Admin_Legacy_Captcha_Notice())->add_hooks();
        $admin_tools = new NL4WP_Admin_Tools();

        if (wp_doing_ajax()) {
            $ajax = new NL4WP_Admin_Ajax($admin_tools);
            $ajax->add_hooks();
        } else {
            $messages                = new NL4WP_Admin_Messages();
            $nl4wp['admin.messages'] = $messages;

            (new NL4WP_Admin($admin_tools, $messages))->add_hooks();
            (new NL4WP_Forms_Admin($messages))->add_hooks();
            (new NL4WP_Integration_Admin($integration_manager, $messages))->add_hooks();
        }
    } else {
    }

    // bootstrap integrations
    require __DIR__ . '/integrations/bootstrap.php';
}, 8);

// schedule the action hook to refresh the stored Newsletter lists on a daily basis
register_activation_hook(__FILE__, function () {
    $timezone = wp_timezone();
    $datetime = new DateTimeImmutable('tomorrow', $timezone);
    $datetime = $datetime->setTime(wp_rand(0, 6), wp_rand(0, 59));
    wp_schedule_event($datetime->getTimestamp(), 'daily', 'nl4wp_refresh_newsletter_lists');
});

// remove scheduled hook when plugin is deactivated
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('nl4wp_refresh_newsletter_lists');
});
