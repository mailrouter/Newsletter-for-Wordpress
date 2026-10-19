<?php

defined('ABSPATH') || exit;


/**
 * Class NL4WP_Integration_Admin
 *
 * @ignore
 * @access private
 */
class NL4WP_Integration_Admin
{
    /**
     * @var NL4WP_Integration_Manager
     */
    protected $integrations;

    /**
     * @var NL4WP_Admin_Messages
     */
    protected $messages;

    /**
     * @param NL4WP_Integration_Manager $integrations
     * @param NL4WP_Admin_Messages $messages
     */
    public function __construct(NL4WP_Integration_Manager $integrations, NL4WP_Admin_Messages $messages)
    {
        $this->integrations = $integrations;
        $this->messages     = $messages;
    }

    /**
     * Add hooks
     */
    public function add_hooks()
    {
        add_action('admin_init', [ $this, 'register_setting' ]);
        add_action('nl4wp_admin_enqueue_assets', [ $this, 'enqueue_assets' ], 10, 2);
        add_filter('nl4wp_admin_menu_items', [ $this, 'add_menu_item' ]);
    }

    /**
     * Register settings
     */
    public function register_setting()
    {
        register_setting('nl4wp_integrations_settings', 'nl4wp_integrations', [ $this, 'save_integration_settings' ]);
    }

    /**
     * Enqueue assets
     *
     * @param string $suffix
     * @param string $page
     *
     * @return void
     */
    public function enqueue_assets($suffix, $page)
    {
        // only load on integrations pages
        if ($page !== 'integrations') {
            return;
        }

        wp_register_script('nl4wp-integrations-admin', nl4wp_plugin_url('assets/js/integrations-admin.js'), [ 'nl4wp-admin' ], NL4WP_VERSION, true);
        wp_enqueue_script('nl4wp-integrations-admin');
    }

    /**
     * @param array $items
     *
     * @return array
     */
    public function add_menu_item($items)
    {
        $items[] = [
            'title'    => esc_html__('Integrations', 'newsletter-for-wp'),
            'text'     => esc_html__('Integrations', 'newsletter-for-wp'),
            'slug'     => 'integrations',
            'callback' => [ $this, 'show_integrations_page' ],
            'position' => 20,
        ];

        return $items;
    }

    /**
     * @param array $new_settings
     * @return array
     */
    public function save_integration_settings(array $new_settings)
    {
        $integrations     = $this->integrations->get_all();
        $current_settings = (array) get_option('nl4wp_integrations', []);
        $settings         = [];

        foreach ($integrations as $slug => $integration) {
            $settings[ $slug ] = $this->parse_integration_settings($slug, $current_settings, $new_settings);
        }

        return $settings;
    }

    /**
     * @since 3.0
     * @param string $slug
     * @param array $current
     * @param array $new
     *
     * @return array
     */
    protected function parse_integration_settings($slug, $current, $new)
    {
        $settings = [];

        // start with current settings
        if (! empty($current[ $slug ])) {
            $settings = $current[ $slug ];
        }

        // if no new settings were given, return current settings.
        if (empty($new[ $slug ])) {
            return $settings;
        }

        // merge new settings with currents (to allow passing partial setting arrays)
        $settings = array_merge($settings, $new[ $slug ]);

        // sanitize settings
        $settings = $this->sanitize_integration_settings($settings);

        return $settings;
    }

    /**
     * @param array $settings
     * @return array
     */
    protected function sanitize_integration_settings($settings)
    {

        // filter null values from lists setting
        if (! empty($settings['lists'])) {
            $settings['lists'] = array_filter($settings['lists']);
        } else {
            $settings['lists'] = [];
        }

        $settings['label'] = strip_tags($settings['label'], '<strong><b><br><a><script><u><em><i><span><img>');
        if (! current_user_can('unfiltered_html')) {
            $settings['label'] = nl4wp_kses($settings['label']);
        }

        return $settings;
    }

    /**
     * Show the Integration Settings page
     *
     * @internal
     */
    public function show_integrations_page()
    {
        if (! empty($_GET['integration'])) {
            $this->show_integration_settings_page(wp_unslash($_GET['integration']));
            return;
        }

        // get all installed & enabled integrations
        $enabled_integrations = $this->integrations->get_enabled_integrations();

        // get all integrations but remove enabled integrations from the resulting array
        $integrations = $this->integrations->get_all();

        require __DIR__ . '/views/integrations.php';
    }

    /**
     * @param string $slug
     *
     * @internal
     */
    public function show_integration_settings_page($slug)
    {
        try {
            $integration = $this->integrations->get($slug);
        } catch (Exception $e) {
            printf('<h3>Integration not found.</h3><p>No integration with slug <strong>%s</strong> was found.</p>', esc_html($slug));
            return;
        }

        $opts      = $integration->options;
        $newsletter = new NL4WP_Newsletter();
        $lists     = $newsletter->get_lists();

        require __DIR__ . '/views/integration-settings.php';
    }
}
