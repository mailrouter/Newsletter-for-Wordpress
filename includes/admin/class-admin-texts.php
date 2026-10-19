<?php

defined('ABSPATH') || exit;


/**
 * Class NL4WP_Admin_Texts
 *
 * @ignore
 * @since 3.0
 */
class NL4WP_Admin_Texts
{
    /**
     * @var string
     */
    protected $plugin_file;

    /**
     * @param string $plugin_file
     */
    public function __construct($plugin_file)
    {
        $this->plugin_file = $plugin_file;
    }

    /**
     * Add hooks
     */
    public function add_hooks()
    {
        global $pagenow;

        // Hooks for Plugins overview page
        if ($pagenow === 'plugins.php') {
            add_filter('plugin_action_links_' . $this->plugin_file, [ $this, 'add_plugin_settings_link' ], 10, 2);
            add_filter('plugin_row_meta', [ $this, 'add_plugin_meta_links' ], 10, 2);
        }
    }

    /**
     * Add the settings link to the Plugins overview
     *
     * @param array $links
     * @param       $file
     *
     * @return array
     */
    public function add_plugin_settings_link($links, $file)
    {
        if ($file !== $this->plugin_file) {
            return $links;
        }

        $settings_link = sprintf('<a href="%s">%s</a>', admin_url('admin.php?page=newsletter-for-wp'), esc_html__('Settings', 'newsletter-for-wp'));
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Adds meta links to the plugin in the WP Admin > Plugins screen
     *
     * @param array $links
     * @param string $file
     *
     * @return array
     */
    public function add_plugin_meta_links($links, $file)
    {
        if ($file !== $this->plugin_file) {
            return $links;
        }


        /**
         * Filters meta links shown on the Plugins overview page
         *
         * This takes an array of strings
         *
         * @since 3.0
         * @param array $links
         * @ignore
         */
        $links = apply_filters('nl4wp_admin_plugin_meta_links', $links);

        return $links;
    }
}
