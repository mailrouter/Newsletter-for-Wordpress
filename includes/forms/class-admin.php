<?php

defined('ABSPATH') || exit;


/**
 * Class NL4WP_Forms_Admin
 *
 * @ignore
 * @access private
 */
class NL4WP_Forms_Admin
{
    /**
     * @var NL4WP_Admin_Messages
     */
    protected $messages;

    /**
     * @param NL4WP_Admin_Messages $messages
     */
    public function __construct(NL4WP_Admin_Messages $messages)
    {
        $this->messages = $messages;
    }

    public function add_hooks(): void
    {
        add_action('nl4wp_save_form', [$this, 'update_form_stylesheets']);
        add_action('nl4wp_admin_edit_form', [$this, 'process_save_form']);
        add_action('nl4wp_admin_add_form', [$this, 'process_add_form']);
        add_filter('nl4wp_admin_menu_items', [$this, 'add_menu_item'], 5);
        add_action('nl4wp_admin_show_forms_page-edit-form', [$this, 'show_edit_page']);
        add_action('nl4wp_admin_show_forms_page-add-form', [$this, 'show_add_page']);
        add_action('nl4wp_admin_enqueue_assets', [$this, 'enqueue_assets'], 10, 2);

        add_action('enqueue_block_editor_assets', [$this, 'enqueue_gutenberg_assets']);
    }


    public function enqueue_gutenberg_assets()
    {
        wp_enqueue_script('nl4wp-form-block', nl4wp_plugin_url('assets/js/forms-block.js'), [
            'wp-blocks',
            'wp-i18n',
            'wp-element',
            'wp-components',
            'wp-block-editor',
        ]);

        $forms = nl4wp_get_forms();
        $data  = [];
        foreach ($forms as $form) {
            $data[] = [
                'name' => $form->name,
                'id'   => $form->ID,
            ];
        }
        wp_add_inline_script(
            'nl4wp-form-block',
            'var nl4wp_forms = ' . wp_json_encode($data) . ';',
            'before'
        );
    }

    /**
     * @param string $suffix
     * @param string $page
     */
    public function enqueue_assets($suffix, $page = '')
    {
        if ($page !== 'forms' || empty($_GET['view']) || $_GET['view'] !== 'edit-form') {
            return;
        }

        wp_enqueue_script('nl4wp-forms-admin', nl4wp_plugin_url('assets/js/forms-admin.js'), ['nl4wp-admin'], NL4WP_VERSION, true);
        wp_localize_script(
            'nl4wp-forms-admin',
            'nl4wp_forms_i18n',
            [
                'addToForm'             => __('Add to form', 'newsletter-for-wp'),
                'agreeToTerms'          => __('I have read and agree to the terms & conditions', 'newsletter-for-wp'),
                'agreeToTermsShort'     => __('Agree to terms', 'newsletter-for-wp'),
                'agreeToTermsLink'      => __('Link to your terms & conditions page', 'newsletter-for-wp'),
                'city'                  => __('City', 'newsletter-for-wp'),
                'checkboxes'            => __('Checkboxes', 'newsletter-for-wp'),
                'choices'               => __('Choices', 'newsletter-for-wp'),
                'choiceType'            => __('Choice type', 'newsletter-for-wp'),
                'chooseField'           => __('Choose a field to add to the form', 'newsletter-for-wp'),
                'close'                 => __('Close', 'newsletter-for-wp'),
                'country'               => __('Country', 'newsletter-for-wp'),
                'dropdown'              => __('Dropdown', 'newsletter-for-wp'),
                'emailAddress'          => __('Email address', 'newsletter-for-wp'),
                'fieldType'             => __('Field type', 'newsletter-for-wp'),
                'fieldLabel'            => __('Field label', 'newsletter-for-wp'),
                'formAction'            => __('Form action', 'newsletter-for-wp'),
                'formActionDescription' => __('This field will allow your visitors to choose whether they would like to subscribe or unsubscribe', 'newsletter-for-wp'),
                'formFields'            => __('Form fields', 'newsletter-for-wp'),
                'forceRequired'         => __('This field is marked as required in Newsletter.', 'newsletter-for-wp'),
                'initialValue'          => __('Initial value', 'newsletter-for-wp'),
                'interestCategories'    => __('Interest categories', 'newsletter-for-wp'),
                'isFieldRequired'       => __('Is this field required?', 'newsletter-for-wp'),
                'listChoice'            => __('Audience choice', 'newsletter-for-wp'),
                'listChoiceDescription' => __('This field will allow your visitors to choose an audience to subscribe to.', 'newsletter-for-wp'),
                'listFields'            => __('Audience fields', 'newsletter-for-wp'),
                'min'                   => __('Min', 'newsletter-for-wp'),
                'max'                   => __('Max', 'newsletter-for-wp'),
                'noAvailableFields'     => __('No available fields. Did you select a Newsletter list in the form settings?', 'newsletter-for-wp'),
                'optional'              => __('Optional', 'newsletter-for-wp'),
                'placeholder'           => __('Placeholder', 'newsletter-for-wp'),
                'placeholderHelp'       => __('Text to show when field has no value.', 'newsletter-for-wp'),
                'preselect'             => __('Preselect', 'newsletter-for-wp'),
                'remove'                => __('Remove', 'newsletter-for-wp'),
                'radioButtons'          => __('Radio buttons', 'newsletter-for-wp'),
                'streetAddress'         => __('Street Address', 'newsletter-for-wp'),
                'state'                 => __('State', 'newsletter-for-wp'),
                'subscribe'             => __('Subscribe', 'newsletter-for-wp'),
                'submitButton'          => __('Submit button', 'newsletter-for-wp'),
                'wrapInParagraphTags'   => __('Wrap in paragraph tags?', 'newsletter-for-wp'),
                'value'                 => __('Value', 'newsletter-for-wp'),
                'valueHelp'             => __('Text to prefill this field with.', 'newsletter-for-wp'),
                'zip'                   => __('ZIP', 'newsletter-for-wp'),
            ]
        );
    }

    /**
     * @param array $items
     *
     * @return mixed
     */
    public function add_menu_item($items)
    {
        $items['forms'] = [
            'title'         => esc_html__('Forms', 'newsletter-for-wp'),
            'text'          => esc_html__('Forms', 'newsletter-for-wp'),
            'slug'          => 'forms',
            'callback'      => [$this, 'show_forms_page'],
            // 'load_callback' => [$this, 'redirect_to_form_action'],
            'position'      => 10,
        ];

        return $items;
    }

    /**
     * Act on the "add form" form
     */
    public function process_add_form()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce check is done in action dispatcher
        $request = $_POST;
        if (! isset($request['nl4wp_form'])) {
            wp_nonce_ays('add_form');
        }

        $form_data    = $request['nl4wp_form'];
        $form_content = include NL4WP_PLUGIN_DIR . '/config/default-form-content.php';

        // Fix for MultiSite stripping KSES for roles other than administrator
        remove_all_filters('content_save_pre');

        $form_id = wp_insert_post(
            [
                'post_type'    => 'nl4wp-form',
                'post_status'  => 'publish',
                'post_title'   => $form_data['name'],
                'post_content' => $form_content,
            ]
        );

        // if settings were passed, save those too.
        if (isset($form_data['settings'])) {
            update_post_meta($form_id, '_nl4wp_settings', $form_data['settings']);
        }

        $this->messages->flash(__('Form saved.', 'newsletter-for-wp'));
        $edit_form_url = nl4wp_get_edit_form_url($form_id);
        wp_redirect($edit_form_url);
        exit;
    }

    /**
     * Saves a form to the database
     *
     * @param int $form_id
     * @param array $data The form data. Expects slashed content.
     * @return int
     */
    private function save_form($form_id, array $data)
    {
        $keys = [
            'settings' => [],
            'messages' => [],
            'name'     => '',
            'content'  => '',
        ];
        $data = array_merge($keys, $data);
        $data = $this->sanitize_form_data($data);

        $post_data = [
            'ID' => $form_id,
            'post_type'    => 'nl4wp-form',
            'post_status'  => ! empty($data['status']) ? $data['status'] : 'publish',
            'post_title'   => $data['name'],
            'post_content' => $data['content'],
        ];

        // Fix for MultiSite stripping KSES for roles other than administrator
        remove_all_filters('content_save_pre');
        wp_insert_post($post_data);

        // merge new settings  with current settings to allow passing partial data
        $current_settings = get_post_meta($form_id, '_nl4wp_settings', true);
        if (is_array($current_settings)) {
            $data['settings'] = array_merge($current_settings, $data['settings']);
        }
        update_post_meta($form_id, '_nl4wp_settings', $data['settings']);

        // save form messages in individual meta keys
        foreach ($data['messages'] as $key => $message) {
            update_post_meta($form_id, 'text_' . $key, $message);
        }

        /**
         * Runs right after a form is updated.
         *
         * @since 3.0
         *
         * @param int $form_id
         */
        do_action('nl4wp_save_form', $form_id);

        return $form_id;
    }

    /**
     * @return array
     */
    public function sanitize_form_data(array $data)
    {
        $raw_data = $data;

        // strip <form> tags from content
        $data['content'] = preg_replace('/<\/?form\b[^>]{0,1024}>/i', '', $data['content']);

        // replace lowercased name="name" to prevent 404
        $data['content'] = str_ireplace(' name=\"name\"', ' name=\"NAME\"', $data['content']);

        // sanitize text fields
        $data['settings']['redirect'] = sanitize_text_field($data['settings']['redirect']);

        // strip tags from messages
        foreach ($data['messages'] as $key => $message) {
            $data['messages'][$key] = strip_tags($message, '<strong><b><br><a><script><u><em><i><span><img>');
        }

        // make sure lists is an array
        if (! isset($data['settings']['lists'])) {
            $data['settings']['lists'] = [];
        }

        $data['settings']['lists'] = array_filter((array) $data['settings']['lists']);

        // if current user can not post unfiltered HTML, run HTML through whitelist using wp_kses
        if (! current_user_can('unfiltered_html')) {
            $data['content'] = nl4wp_kses($data['content']);
            foreach ($data['messages'] as $key => $message) {
                $data['messages'][$key] = nl4wp_kses($data['messages'][$key]);
            }
        }

        /**
         * Filters the form data just before it is saved.
         *
         * @param array $data Sanitized array of form data.
         * @param array $raw_data Raw array of form data.
         *
         * @since 3.0.8
         * @ignore
         */
        $data = (array) apply_filters('nl4wp_form_sanitized_data', $data, $raw_data);

        return $data;
    }

    /**
     * Saves a form
     */
    public function process_save_form()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce is handled in action dispatcher
        if (! isset($_POST['nl4wp_form_id']) || ! isset($_POST['nl4wp_form'])) {
            wp_nonce_ays('save_form');
        }

        // update form, settings and messages
        $form_id   = (int) $_POST['nl4wp_form_id'];

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- wp_insert_post expects slashed data
        $form_data = $_POST['nl4wp_form'];

        $this->save_form($form_id, $form_data);
        $this->messages->flash(__('Form saved.', 'newsletter-for-wp'));
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }

    /**
     * Goes through each form and aggregates array of stylesheet slugs to load.
     *
     * @hooked `nl4wp_save_form`
     */
    public function update_form_stylesheets()
    {
        $stylesheets = [];

        $forms = nl4wp_get_forms();
        foreach ($forms as $form) {
            $stylesheet = $form->get_stylesheet();

            if (! empty($stylesheet) && ! in_array($stylesheet, $stylesheets, true)) {
                $stylesheets[] = $stylesheet;
            }
        }

        update_option('nl4wp_form_stylesheets', $stylesheets);
    }

    /**
     * Redirect to correct form action
     *
     * @ignore
     */
    public function redirect_to_form_action()
    {
        if (! empty($_GET['view'])) {
            return;
        }

        try {
            // try default form first
            $default_form = nl4wp_get_form();
            $redirect_url = nl4wp_get_edit_form_url($default_form->ID);
        } catch (Exception $e) {
            // no default form, query first available form and go there
            $forms = nl4wp_get_forms(
                [
                    'posts_per_page' => 1,
                    'orderby' => 'ID',
                    'order' => 'ASC',
                ]
            );

            if (count($forms) > 0) {
                // take first form and use it to go to the "edit form" screen
                $form         = array_shift($forms);
                $redirect_url = nl4wp_get_edit_form_url($form->ID);
            } else {
                // we don't have a form yet, go to "add new" screen
                $redirect_url = nl4wp_get_add_form_url();
            }
        }

        if (headers_sent()) {
            printf('<meta http-equiv="refresh" content="0;url=%s" />', esc_url($redirect_url));
        } else {
            wp_redirect($redirect_url);
        }

        exit;
    }

    /**
     * Show the Forms Settings page
     *
     * @internal
     */
    public function show_forms_page()
    {
        $view = ! empty($_GET['view']) ? wp_unslash($_GET['view']) : '';

        /**
         * @ignore
         */
        do_action('nl4wp_admin_show_forms_page', $view);
        if (empty($view)) {
            $table = new NL4WP_Forms_List_Table();
            include __DIR__ . '/views/overview.php';
        }


        /**
         * @ignore
         */
        do_action('nl4wp_admin_show_forms_page-' . $view);
    }

    /**
     * Show the "Edit Form" page
     *
     * @internal
     */
    public function show_edit_page()
    {
        $form_id   = ! empty($_GET['form_id']) ? (int) $_GET['form_id'] : 0;
        $newsletter = new NL4WP_Newsletter();
        $lists     = $newsletter->get_lists();

        try {
            $form = nl4wp_get_form($form_id);
        } catch (Exception $e) {
            echo '<h2>', esc_html__('Form not found.', 'newsletter-for-wp'), '</h2>';
            echo '<p>', esc_html($e->getMessage()), '</p>';
            echo '<p><a href="javascript:history.go(-1);"> &lsaquo; ', esc_html__('Go back', 'newsletter-for-wp'), '</a></p>';
            return;
        }

        $opts       = $form->settings;
        $active_tab = isset($_GET['tab']) ? wp_unslash($_GET['tab']) : 'fields';
        $form_preview_url = add_query_arg(['nl4wp_preview_form' => $form_id], site_url('/', 'admin'));

        require __DIR__ . '/views/edit-form.php';
    }

    /**
     * Shows the "Add Form" page
     *
     * @internal
     */
    public function show_add_page()
    {
        $newsletter       = new NL4WP_Newsletter();
        $lists           = $newsletter->get_lists();
        $number_of_lists = count($lists);
        require __DIR__ . '/views/add-form.php';
    }

    /**
     * Get URL for a tab on the current page.
     *
     * @since 3.0
     * @internal
     * @param string $tab
     * @return string
     */
    public function tab_url($tab)
    {
        return add_query_arg(['tab' => $tab], remove_query_arg('tab'));
    }
}
