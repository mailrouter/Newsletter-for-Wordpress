<?php

/**
* Class NL4WP_Admin
*
* @ignore
* @access private
*/
class NL4WP_Admin
{

    /**
    * @var string The relative path to the main plugin file from the plugins dir
    */
    protected $plugin_file;

    /**
    * @var NL4WP_Newsletter
    */
    protected $newsletter;

    /**
    * @var NL4WP_Admin_Messages
    */
    protected $messages;

    /**
    * @var NL4WP_Admin_Ads
    */
    protected $ads;

    /**
    * @var NL4WP_Admin_Tools
    */
    protected $tools;

    /**
    * @var NL4WP_Admin_Review_Notice
    */
    protected $review_notice;

    /**
    * Constructor
    *
    * @param NL4WP_Admin_Tools $tools
    * @param NL4WP_Admin_Messages $messages
    * @param NL4WP_Newsletter      $newsletter
    */
    public function __construct(NL4WP_Admin_Tools $tools, NL4WP_Admin_Messages $messages, NL4WP_Newsletter $newsletter)
    {
        $this->tools = $tools;
        $this->newsletter = $newsletter;
        $this->messages = $messages;
        $this->plugin_file = plugin_basename(NL4WP_PLUGIN_FILE);
        $this->ads = new NL4WP_Admin_Ads();
        $this->review_notice = new NL4WP_Admin_Review_Notice($tools);
        $this->load_translations();
    }

    /**
    * Registers all hooks
    */
    public function add_hooks()
    {

        // Actions used globally throughout WP Admin
        add_action('admin_menu', array( $this, 'build_menu' ));
        add_action('admin_init', array( $this, 'initialize' ));

        add_action('current_screen', array( $this, 'customize_admin_texts' ));
        add_action('wp_dashboard_setup', array( $this, 'register_dashboard_widgets' ));
        add_action('nl4wp_admin_empty_lists_cache', array( $this, 'renew_lists_cache' ));
        add_action('nl4wp_admin_empty_debug_log', array( $this, 'empty_debug_log' ));

        add_action('admin_notices', array( $this, 'show_api_key_notice' ));
        add_action('nl4wp_admin_dismiss_api_key_notice', array( $this, 'dismiss_api_key_notice' ));
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_assets' ));

        $this->ads->add_hooks();
        $this->messages->add_hooks();
        $this->review_notice->add_hooks();
    }

    /**
    * Initializes various stuff used in WP Admin
    *
    * - Registers settings
    */
    public function initialize()
    {

        // register settings
        register_setting('nl4wp_settings', 'nl4wp', array( $this, 'save_general_settings' ));

        // Load upgrader
        $this->init_upgrade_routines();

        // listen for custom actions
        $this->listen_for_actions();
    }


    /**
    * Listen for `_nl4wp_action` requests
    */
    public function listen_for_actions()
    {

        // listen for any action (if user is authorised)
        if (! $this->tools->is_user_authorized() || ! isset($_REQUEST['_nl4wp_action'])) {
            return false;
        }

        $action = (string) $_REQUEST['_nl4wp_action'];

        /**
        * Allows you to hook into requests containing `_nl4wp_action` => action name.
        *
        * The dynamic portion of the hook name, `$action`, refers to the action name.
        *
        * By the time this hook is fired, the user is already authorized. After processing all the registered hooks,
        * the request is redirected back to the referring URL.
        *
        * @since 3.0
        */
        do_action('nl4wp_admin_' . $action);

        // redirect back to where we came from
        $redirect_url = ! empty($_POST['_redirect_to']) ? $_POST['_redirect_to'] : remove_query_arg('_nl4wp_action');
        wp_redirect($redirect_url);
        exit;
    }

    /**
    * Register dashboard widgets
    */
    public function register_dashboard_widgets()
    {
        if (! $this->tools->is_user_authorized()) {
            return false;
        }

        /**
        * Setup dashboard widget, users are authorized by now.
        *
        * Use this hook to register your own dashboard widgets for users with the required capability.
        *
        * @since 3.0
        * @ignore
        */
        do_action('nl4wp_dashboard_setup');

        return true;
    }

    /**
    * Upgrade routine
    */
    private function init_upgrade_routines()
    {

        // upgrade routine for upgrade routine....
        $previous_version = get_option('nl4wp_lite_version', 0);
        if ($previous_version) {
            delete_option('nl4wp_lite_version');
            update_option('nl4wp_version', $previous_version);
        }

        $previous_version = get_option('nl4wp_version', 0);

        // allow setting migration version from URL, to easily re-run previous migrations.
        if (isset($_GET['nl4wp_run_migration'])) {
            $previous_version = $_GET['nl4wp_run_migration'];
        }

        // Ran upgrade routines before?
        if (empty($previous_version)) {
            update_option('nl4wp_version', NL4WP_VERSION);

            // if we have at least one form, we're going to run upgrade routine for v3 => v4 anyway.
            // TODO: Remove this once we hit 4.2.x
            $posts = get_posts(array( 'post_type' => 'nl4wp-form', 'numberposts' => 1 ));
            if (empty($posts)) {
                return false;
            }

            $previous_version = '3.9';
        }

        // Rollback'ed?
        if (version_compare($previous_version, NL4WP_VERSION, '>')) {
            update_option('nl4wp_version', NL4WP_VERSION);
            return false;
        }

        // This means we're good!
        if (version_compare($previous_version, NL4WP_VERSION) > -1) {
            return false;
        }

        define('NL4WP_DOING_UPGRADE', true);
        $upgrade_routines = new NL4WP_Upgrade_Routines($previous_version, NL4WP_VERSION, dirname(__FILE__) . '/migrations');
        $upgrade_routines->run();
        update_option('nl4wp_version', NL4WP_VERSION);
    }

    /**
    * Renew Newsletter lists cache
    */
    public function renew_lists_cache()
    {
        // try getting new lists to fill cache again
        $lists = $this->newsletter->fetch_lists();

        if (! empty($lists)) {
            $this->messages->flash(__('Success! The cached configuration for your Newsletter lists has been renewed.', 'newsletter-for-wp'));
        }
    }

    /**
    * Load the plugin translations
    */
    private function load_translations()
    {
        // load the plugin text domain
        load_plugin_textdomain('newsletter-for-wp', false, dirname($this->plugin_file) . '/languages');
    }

    /**
    * Customize texts throughout WP Admin
    */
    public function customize_admin_texts()
    {
        $texts = new NL4WP_Admin_Texts($this->plugin_file);
        $texts->add_hooks();
    }

    /**
    * Validates the General settings
    * @param array $settings
    * @return array
    */
    public function save_general_settings(array $settings)
    {
        $current = nl4wp_get_options();

        // merge with current settings to allow passing partial arrays to this method
        $settings = array_merge($current, $settings);

        // toggle usage tracking
        if ($settings['allow_usage_tracking'] !== $current['allow_usage_tracking']) {
            NL4WP_Usage_Tracking::instance()->toggle($settings['allow_usage_tracking']);
        }

        // Make sure not to use obfuscated key
        if (strpos($settings['api_key'], '*') !== false) {
            $settings['api_key'] = $current['api_key'];
        }

        // Sanitize API key
        $settings['api_key'] = sanitize_text_field($settings['api_key']);

        // if API key changed, empty Newsletter cache
        if ($settings['api_key'] !== $current['api_key']) {
            $this->newsletter->empty_cache();
        }


        /**
        * Runs right before general settings are saved.
        *
        * @param array $settings The updated settings array
        * @param array $current The old settings array
        */
        do_action('nl4wp_save_settings', $settings, $current);

        return $settings;
    }

    /**
    * Load scripts and stylesheet on Newsletter for WP Admin pages
    *
    * @return bool
    */
    public function enqueue_assets()
    {
        global $wp_scripts;


        if (! $this->tools->on_plugin_page()) {
            return false;
        }

        $opts = nl4wp_get_options();
        $page = $this->tools->get_plugin_page();
        $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

        // css
        wp_register_style('nl4wp-admin', NL4WP_PLUGIN_URL . 'assets/css/admin-styles' . $suffix . '.css', array(), NL4WP_VERSION);
        wp_enqueue_style('nl4wp-admin');

        // js
        wp_register_script('es5-shim', NL4WP_PLUGIN_URL . 'assets/js/third-party/es5-shim.min.js', array(), NL4WP_VERSION);
        $wp_scripts->add_data('es5-shim', 'conditional', 'lt IE 9');

        // TODO: eventually get rid of jQuery here
        wp_register_script('nl4wp-admin', NL4WP_PLUGIN_URL . 'assets/js/admin' . $suffix . '.js', array( 'jquery', 'es5-shim' ), NL4WP_VERSION, true);
        wp_enqueue_script(array( 'jquery', 'es5-shim', 'nl4wp-admin' ));

        wp_localize_script(
            'nl4wp-admin',
            'nl4wp_vars',
            array(
                'newsletter' => array(
                    'api_connected' => ! empty($opts['api_key']),
                    'lists' => $this->newsletter->get_cached_lists()
                ),
                'countries' => NL4WP_Tools::get_countries(),
                'i18n' => array(
                    'invalid_api_key' => __('The given value does not look like a valid Newsletter API key.', 'newsletter-for-wp'),
                    'pro_only' => __('This is a premium feature. Please upgrade to Newsletter for WordPress Premium to be able to use it.', 'newsletter-for-wp'),
                    'renew_newsletter_lists' => __('Renew Newsletter lists', 'newsletter-for-wp'),
                    'fetching_newsletter_lists' => __('Fetching Newsletter lists', 'newsletter-for-wp'),
                    'fetching_newsletter_lists_done' => __('Done! Newsletter lists renewed.', 'newsletter-for-wp'),
                    'fetching_newsletter_lists_can_take_a_while' => __('This can take a while if you have many Newsletter lists.', 'newsletter-for-wp'),
                    'fetching_newsletter_lists_error' => __('Failed to renew your lists. An error occured.', 'newsletter-for-wp'),
                )
            )
        );

        /**
        * Hook to enqueue your own custom assets on the Newsletter for WordPress setting pages.
        *
        * @since 3.0
        *
        * @param string $suffix
        * @param string $page
        */
        do_action('nl4wp_admin_enqueue_assets', $suffix, $page);

        return true;
    }



    /**
    * Register the setting pages and their menu items
    */
    public function build_menu()
    {
        $required_cap = $this->tools->get_required_capability();

        $menu_items = array(
            array(
                'title' => __('Newsletter API Settings', 'newsletter-for-wp'),
                'text' => __('Newsletter', 'newsletter-for-wp'),
                'slug' => '',
                'callback' => array( $this, 'show_generals_setting_page' ),
                'position' => 0
            ),
            array(
                'title' => __('Other Settings', 'newsletter-for-wp'),
                'text' => __('Other', 'newsletter-for-wp'),
                'slug' => 'other',
                'callback' => array( $this, 'show_other_setting_page' ),
                'position' => 90
            ),
            
        );

        /**
        * Filters the menu items to appear under the main menu item.
        *
        * To add your own item, add an associative array in the following format.
        *
        * $menu_items[] = array(
        *     'title' => 'Page title',
        *     'text'  => 'Menu text',
        *     'slug' => 'Page slug',
        *     'callback' => 'my_page_function',
        *     'position' => 50
        * );
        *
        * @param array $menu_items
        * @since 3.0
        */
        $menu_items = (array) apply_filters('nl4wp_admin_menu_items', $menu_items);

        // add top menu item
        add_menu_page('Newsletter for WP', 'Newsletter for WP', $required_cap, 'newsletter-for-wp', array( $this, 'show_generals_setting_page' ), NL4WP_PLUGIN_URL . 'assets/img/icon.png', '99.68491');

        // sort submenu items by 'position'
        usort($menu_items, array( $this, 'sort_menu_items_by_position' ));

        // add sub-menu items
        foreach ($menu_items as $item) {
            $this->add_menu_item($item);
        }
    }

    /**
    * @param array $item
    */
    public function add_menu_item(array $item)
    {

        // generate menu slug
        $slug = 'newsletter-for-wp';
        if (! empty($item['slug'])) {
            $slug .= '-' . $item['slug'];
        }

        // provide some defaults
        $parent_slug = ! empty($item['parent_slug']) ? $item['parent_slug'] : 'newsletter-for-wp';
        $capability = ! empty($item['capability']) ? $item['capability'] : $this->tools->get_required_capability();

        // register page
        $hook = add_submenu_page($parent_slug, $item['title'] . ' - Newsletter for WordPress', $item['text'], $capability, $slug, $item['callback']);

        // register callback for loading this page, if given
        if (array_key_exists('load_callback', $item)) {
            add_action('load-' . $hook, $item['load_callback']);
        }
    }

    /**
    * Show the API Settings page
    */
    public function show_generals_setting_page()
    {
        $opts = nl4wp_get_options();
        $api_key = nl4wp_get_api_key();

        $connected = ! empty($api_key);
        if ($connected) {
            try {
                $connected = $this->get_api()->is_connected();
            } catch (NL4WP_API_Connection_Exception $e) {
                $message = sprintf("<strong>%s</strong> %s %s ", __("Error connecting to Newsletter:", 'newsletter-for-wp'), $e->getCode(), $e->getMessage());

                if (is_object($e->data) && ! empty($e->data->ref_no)) {
                    $message .= '<br />' . sprintf(__('Looks like your server is blocked by Newsletter\'s firewall. Please contact Newsletter support and include the following reference number: %s', 'newsletter-for-wp'), $e->data->ref_no);
                }

                $message .= '<br /><br />' . sprintf('<a href="%s">' . __('Here\'s some info on solving common connectivity issues.', 'newsletter-for-wp') . '</a>', 'https://kb.mc4wp.com/solving-connectivity-issues/#utm_source=wp-plugin&utm_medium=newsletter-for-wp&utm_campaign=settings-notice');

                $this->messages->flash($message, 'error');
                $connected = false;
            } catch (NL4WP_API_Exception $e) {
                $this->messages->flash(sprintf("<strong>%s</strong><br /> %s", __("Newsletter returned the following error:", 'newsletter-for-wp'), $e), 'error');
                $connected = false;
            }
        }

        $lists = $this->newsletter->get_cached_lists();
        $obfuscated_api_key = nl4wp_obfuscate_string($api_key);
        require NL4WP_PLUGIN_DIR . 'includes/views/general-settings.php';
    }

    /**
    * Show the Other Settings page
    */
    public function show_other_setting_page()
    {
        $opts = nl4wp_get_options();
        $log = $this->get_log();
        $log_reader = new NL4WP_Debug_Log_Reader($log->file);
        require NL4WP_PLUGIN_DIR . 'includes/views/other-settings.php';
    }

    /**
    * @param $a
    * @param $b
    *
    * @return int
    */
    public function sort_menu_items_by_position($a, $b)
    {
        $pos_a = isset($a['position']) ? $a['position'] : 80;
        $pos_b = isset($b['position']) ? $b['position'] : 90;
        return $pos_a < $pos_b ? -1 : 1;
    }

    /**
    * Empties the log file
    */
    public function empty_debug_log()
    {
        $log = $this->get_log();
        file_put_contents($log->file, '');

        $this->messages->flash(__('Log successfully emptied.', 'newsletter-for-wp'));
    }

    /**
    * Shows a notice when API key is not set.
    */
    public function show_api_key_notice()
    {

        // don't show if on settings page already
        if ($this->tools->on_plugin_page('')) {
            return;
        }

        // only show to user with proper permissions
        if (! $this->tools->is_user_authorized()) {
            return;
        }

        // don't show if dismissed
        if (get_transient('nl4wp_api_key_notice_dismissed')) {
            return;
        }

        // don't show if api key is set already
        $api_key = nl4wp_get_api_key();
        if (! empty($api_key)) {
            return;
        }

        echo '<div class="notice notice-warning nl4wp-is-dismissible">';
        echo '<p>' . sprintf(__('To get started with Newsletter for WordPress, please <a href="%s">enter your Newsletter API key on the settings page of the plugin</a>.', 'newsletter-for-wp'), admin_url('admin.php?page=newsletter-for-wp')) . '</p>';
        echo '<form method="post"><input type="hidden" name="_nl4wp_action" value="dismiss_api_key_notice" /><button type="submit" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></form>';
        echo '</div>';
    }

    /**
    * Dismisses the API key notice for 1 week
    */
    public function dismiss_api_key_notice()
    {
        set_transient('nl4wp_api_key_notice_dismissed', 1, 3600 * 24 * 7);
    }

    /**
    * @return NL4WP_Debug_Log
    */
    protected function get_log()
    {
        return nl4wp('log');
    }

    /**
    * @return NL4WP_API_v3
    */
    protected function get_api()
    {
        return nl4wp('api');
    }
}
