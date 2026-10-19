<?php

defined('ABSPATH') || exit;


/**
* This class takes care of all form assets related functionality
 *
 * @access private
 * @ignore
*/
class NL4WP_Form_Asset_Manager
{
    /**
     * @var bool Flag to determine whether scripts should be enqueued.
     */
    private $load_scripts = false;

    /**
     * @var bool Flag to determine whether email typo checker script should be enqueued.
     */
    private $load_typo_checker = false;

    /**
     * Add hooks
     */
    public function add_hooks(): void
    {
        add_action('init', [ $this, 'register_scripts' ]);
        add_action('wp_enqueue_scripts', [ $this, 'load_stylesheets' ]);
        add_action('wp_footer', [ $this, 'load_scripts' ]);
        add_action('nl4wp_output_form', [ $this, 'before_output_form' ]);
        add_filter('script_loader_tag', [ $this, 'add_defer_attribute' ], 10, 2);
    }

    /**
     * Register scripts to be enqueued later.
     *
     * @return void
     */
    public function register_scripts()
    {
        wp_register_script('nl4wp-forms-api', nl4wp_plugin_url('assets/js/forms.js'), [], NL4WP_VERSION, true);
        wp_register_script('nl4wp-email-typo-checker', nl4wp_plugin_url('assets/js/email-typo-checker.js'), [], NL4WP_VERSION, ['strategy' => 'defer', 'in_footer' => true]);
    }

    /**
     * @param string $stylesheet
     * @return bool
     */
    public function is_registered_stylesheet($stylesheet)
    {
        $stylesheets = $this->get_registered_stylesheets();
        return in_array($stylesheet, $stylesheets, true);
    }

    /**
     * @return array
     */
    public function get_registered_stylesheets()
    {
        return [
            'basic',
            'themes',
        ];
    }

    /**
     * @param string $stylesheet
     *
     * @return string
     */
    public function get_stylesheet_url($stylesheet)
    {
        return nl4wp_plugin_url('assets/css/form-' . $stylesheet . '.css');
    }

    /**
     * Get array of stylesheet handles which should be enqueued.
     *
     * @return array
     */
    public function get_active_stylesheets()
    {
        $stylesheets = (array) get_option('nl4wp_form_stylesheets', []);

        /**
         * Filters the stylesheets to be loaded
         *
         * Should be an array of stylesheet handles previously registered using `wp_register_style`.
         * Each value is prefixed with `nl4wp-form-` to get the handle.
         *
         * Return an empty array if you want to disable the loading of all stylesheets.
         *
         * @since 3.0
         * @param array $stylesheets Array of valid stylesheet handles
         */
        $stylesheets = (array) apply_filters('nl4wp_form_stylesheets', $stylesheets);
        return $stylesheets;
    }

    /**
     * Load the various stylesheets
     */
    public function load_stylesheets()
    {
        $stylesheets = $this->get_active_stylesheets();

        foreach ($stylesheets as $stylesheet) {
            if (! $this->is_registered_stylesheet($stylesheet)) {
                continue;
            }

            $handle = 'nl4wp-form-' . $stylesheet;
            $url    = $this->get_stylesheet_url($stylesheet);
            wp_enqueue_style($handle, $url, [], NL4WP_VERSION);
            add_editor_style($url);
        }

        /**
         * @ignore
         */
        do_action('nl4wp_load_form_stylesheets', $stylesheets);
    }

    /**
     * Get data object for client-side use for after a form is submitted over HTTP POST (not AJAX).
     *
     * @return null|array
     */
    public function get_submitted_form_data()
    {
        $submitted_form = nl4wp_get_submitted_form();
        if (! $submitted_form instanceof NL4WP_Form) {
            return null;
        }

        $data = [
            'id'         => $submitted_form->ID,
            'event'      => $submitted_form->last_event,
            'data'       => $submitted_form->get_data(),
            'element_id' => $submitted_form->config['element_id'],
            'auto_scroll' => true,
        ];

        if ($submitted_form->has_errors()) {
            $data['errors'] = $submitted_form->errors;
        }

        /**
         * Filters the `auto_scroll` setting for when a form is submitted.
         * Set to false to disable scrolling to form.
         *
         * @param boolean $auto_scroll
         * @since 3.0
         */
        $data['auto_scroll'] = apply_filters('nl4wp_form_auto_scroll', $data['auto_scroll']);

        return $data;
    }

    /**
     * Load JavaScript files
     */
    public function before_output_form($form)
    {
        $load_scripts = apply_filters('nl4wp_load_form_scripts', true);
        if (! $load_scripts) {
            return;
        }

        $this->print_dummy_javascript();
        $this->load_scripts = true;

        // check if this form has typo checker enabled
        if (! empty($form->settings['email_typo_check'])) {
            $this->load_typo_checker = true;
        }
    }

    /**
     * Prints dummy JavaScript which allows people to call `nl4wp.forms.on()` before the JS is loaded.
     */
    public function print_dummy_javascript()
    {
        echo '<script>';
        include __DIR__ . '/views/js/dummy-api.js';
        echo '</script>';
    }

    /**
    * Outputs the inline JavaScript that is used to enhance forms
    */
    public function load_scripts()
    {
        $load_scripts = apply_filters('nl4wp_load_form_scripts', $this->load_scripts);
        if (! $load_scripts) {
            return;
        }

        // load general client-side form API
        wp_enqueue_script('nl4wp-forms-api');

        // load email typo checker script only if at least one form has it enabled
        if ($this->load_typo_checker) {
            wp_enqueue_script('nl4wp-email-typo-checker');
            wp_localize_script('nl4wp-email-typo-checker', 'nl4wp_email_typo_checker', [
                // translators: %s is the suggested correct email address.
                'suggestion_text' => __('Did you mean %s?', 'newsletter-for-wp'),
                'domains'         => apply_filters('nl4wp_email_typo_checker_domains', [
                    'gmail.com',
                    'yahoo.com',
                    'hotmail.com',
                    'outlook.com',
                    'icloud.com',
                    'aol.com',
                    'live.com',
                    'msn.com',
                    'me.com',
                    'mac.com',
                    'googlemail.com',
                    'ymail.com',
                    'protonmail.com',
                    'mail.com',
                    'gmx.com',
                    'zoho.com',
                ]),
            ]);
        }

        // maybe load JS file for when a form was submitted over HTTP POST
        $submitted_form_data = $this->get_submitted_form_data();
        if ($submitted_form_data !== null) {
            wp_enqueue_script('nl4wp-forms-submitted', nl4wp_plugin_url('assets/js/forms-submitted.js'), [ 'nl4wp-forms-api' ], NL4WP_VERSION, true);
            wp_add_inline_script(
                'nl4wp-forms-submitted',
                'var nl4wp_submitted_form = ' . wp_json_encode($submitted_form_data) . ';',
                'before'
            );
        }

        // print inline scripts
        echo '<script>';
        echo '(function() {';
        include __DIR__ . '/views/js/url-fields.js';
        echo '})();';
        echo '</script>';

        /** @ignore */
        do_action('nl4wp_load_form_scripts');
    }

    /**
     * Adds `defer` attribute to all form-related `<script>` elements so they do not block page rendering.
     *
     * @param string $tag
     * @param string $handle
     * @return string
     */
    public function add_defer_attribute($tag, $handle)
    {
        // only act on scripts registered with any of these handles
        if ($handle !== 'nl4wp-forms-api' && $handle !== 'nl4wp-forms-submitted') {
            return $tag;
        }

        // don't add twice
        if (strpos($tag, ' defer') !== false) {
            return $tag;
        }

        return str_replace(' src=', ' defer src=', $tag);
    }
}
