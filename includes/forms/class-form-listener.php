<?php

defined('ABSPATH') || exit;


/**
 * Class NL4WP_Form_Listener
 *
 * @since 3.0
 * @access private
 */
class NL4WP_Form_Listener
{
    /**
     * @var NL4WP_Form The submitted form instance
     */
    public $submitted_form;

    public function add_hooks()
    {
        add_action('init', [$this, 'action_init'], 10, 0);
    }

    public function action_init()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- forms are for logged-out visitors, explicitly not using a nonce here
        $form_data = $_POST;
        if (empty($form_data['_nl4wp_form_id'])) {
            return;
        }

        // get form instance
        try {
            $form_id = (int) $form_data['_nl4wp_form_id'];
            $form    = nl4wp_get_form($form_id);
        } catch (Exception $e) {
            return;
        }

        // sanitize request data
        $request_data = nl4wp_sanitize_deep($form_data);

        // bind request to form & validate
        $form->handle_request($request_data);
        $form->validate();

        // store submitted form
        $this->submitted_form = $form;

        // did form have errors?
        if (! $form->has_errors()) {
            switch ($form->get_action()) {
                case 'subscribe':
                    $this->process_subscribe_form($form);
                    break;

                case 'unsubscribe':
                    $this->process_unsubscribe_form($form);
                    break;
            }
        } else {
            foreach ($form->errors as $error_code) {
                $form->add_notice($form->get_message($error_code), 'error');
            }

            $this->get_log()->info(sprintf('Form %d > Submitted with errors: %s', $form->ID, join(', ', $form->errors)));
        }

        $this->respond($form);
    }

    /**
     * Process a subscribe form.
     *
     * @param NL4WP_Form $form
     */
    public function process_subscribe_form(NL4WP_Form $form): void
    {
        $result     = false;
        $newsletter  = new NL4WP_Newsletter();
        $email_type = $form->get_email_type();
        $data       = $form->get_data();
        $ip_address = nl4wp_get_request_ip_address();

        /** @var null|NL4WP_Newsletter_Subscriber $subscriber */
        $subscriber = null;

        // create a map of all lists with list-specific data
        $mapper = new NL4WP_List_Data_Mapper($data, $form->get_lists());

        /** @var NL4WP_Newsletter_Subscriber[] $map */
        $map = $mapper->map();

        // loop through lists
        foreach ($map as $list_id => $subscriber) {
            $subscriber->status     = $form->settings['double_optin'] ? 'pending' : 'subscribed';
            $subscriber->email_type = $email_type;
            $subscriber->ip_signup  = $ip_address;
            $subscriber->tags       = $form->get_subscriber_tags();

            /**
             * Filters subscriber data before it is sent to Newsletter. Fires for both form & integration requests.
             *
             * @param NL4WP_Newsletter_Subscriber $subscriber
             * @param string $list_id ID of the Newsletter list this subscriber will be added/updated in
             */
            $subscriber = apply_filters('nl4wp_subscriber_data', $subscriber, $list_id);
            if (! $subscriber instanceof NL4WP_Newsletter_Subscriber) {
                continue;
            }

            /**
             * Filters subscriber data before it is sent to Newsletter. Only fires for form requests.
             *
             * @param NL4WP_Newsletter_Subscriber $subscriber
             * @param string $list_id ID of the Newsletter list this subscriber will be added/updated in
             */
            $subscriber = apply_filters('nl4wp_form_subscriber_data', $subscriber, $list_id);
            if (! $subscriber instanceof NL4WP_Newsletter_Subscriber) {
                continue;
            }

            // send a subscribe request to Newsletter for each list
            $result = $newsletter->list_subscribe($list_id, $subscriber->email_address, $subscriber->to_array(), $form->settings['update_existing'], $form->settings['replace_interests']);
        }

        $log = $this->get_log();

        // do stuff on failure
        if (! is_object($result) || empty($result->id)) {
            $error_code    = $newsletter->get_error_code();
            $error_message = $newsletter->get_error_message();

            if ((int) $newsletter->get_error_code() === 214) {
                $form->add_error('already_subscribed');
                $form->add_notice($form->messages['already_subscribed'], 'notice');
                $log->warning(sprintf('Form %d > %s is already subscribed to the selected list(s)', $form->ID, $data['EMAIL']));
            } else {
                $form->add_error($error_code);
                $form->add_notice($form->messages['error'], 'error');
                $log->error(sprintf('Form %d > Newsletter API error: %s %s', $form->ID, $error_code, $error_message));

                /**
                 * Fire action hook so API errors can be hooked into.
                 *
                 * @param NL4WP_Form $form
                 * @param string $error_message
                 */
                do_action('nl4wp_form_api_error', $form, $error_message);
            }

            // bail
            return;
        }

        // Success! Did we update or newly subscribe?
        if ($result->status === 'subscribed' && $result->was_already_on_list) {
            $form->last_event = 'updated_subscriber';
            $form->add_notice($form->messages['updated'], 'success');
            $log->info(sprintf('Form %d > Successfully updated %s', $form->ID, $data['EMAIL']));

            /**
             * Fires right after a form was used to update an existing subscriber.
             *
             * @since 3.0
             *
             * @param NL4WP_Form $form Instance of the submitted form
             * @param string $email
             * @param array $data
             */
            do_action('nl4wp_form_updated_subscriber', $form, $subscriber->email_address, $data);
        } else {
            $form->last_event = 'subscribed';
            $form->add_notice($form->messages['subscribed'], 'success');
            $log->info(sprintf('Form %d > Successfully subscribed %s', $form->ID, $data['EMAIL']));

            /**
             * Fires right after a form was used to add a new subscriber.
             *
             * @since 4.8.13
             *
             * @param NL4WP_Form $form Instance of the submitted form
             * @param string $email
             * @param array $data
             */
            do_action('nl4wp_form_added_subscriber', $form, $subscriber->email_address, $data);
        }

        /**
         * Fires right after a form was used to add a new subscriber (or update an existing one).
         *
         * @since 3.0
         *
         * @param NL4WP_Form $form Instance of the submitted form
         * @param string $email
         * @param array $data
         * @param NL4WP_Newsletter_Subscriber[] $subscriber
         */
        do_action('nl4wp_form_subscribed', $form, $subscriber->email_address, $data, $map);
    }

    /**
     * @param NL4WP_Form $form
     */
    public function process_unsubscribe_form(NL4WP_Form $form): void
    {
        $form->add_error('unauthorized');
        $this->get_log()->warning(
            sprintf('Form %d > Unsubscribe forms used but this feature is no longer supported because of security implications', $form->ID)
        );
        _deprecated_function('NL4WP_Form_Listener::process_unsubscribe_form', '4.11.2', 'Unsubscribe forms are no longer supported');

        // This feature was removed in version 4.11.2 and unsubscribe forms are now no longer functional
        // We log the message here to give site admins a chance to remove the forms from their site
        // and we don't want to silently turn them into subscribe forms
        // TODO (Mar 2027): Remove this method entirely in a future major release, along with the "unsubscribe" action and related code in the NL4WP_Form class.
    }

    /**
     * @param NL4WP_Form $form
     */
    public function respond(NL4WP_Form $form)
    {
        $success = ! $form->has_errors();

        if ($success) {

            /**
             * Fires right after a form is submitted without any errors (success).
             *
             * @since 3.0
             *
             * @param NL4WP_Form $form Instance of the submitted form
             */
            do_action('nl4wp_form_success', $form);
        } else {

            /**
             * Fires right after a form is submitted with errors.
             *
             * @since 3.0
             *
             * @param NL4WP_Form $form The submitted form instance.
             */
            do_action('nl4wp_form_error', $form);

            // fire a dedicated event for each error
            foreach ($form->errors as $error) {

                /**
                 * Fires right after a form was submitted with errors.
                 *
                 * The dynamic portion of the hook, `$error`, refers to the error that occurred.
                 *
                 * Default errors give us the following possible hooks:
                 *
                 * - nl4wp_form_error_error                     General errors
                 * - nl4wp_form_error_spam
                 * - nl4wp_form_error_invalid_email             Invalid email address
                 * - nl4wp_form_error_already_subscribed        Email is already on selected list(s)
                 * - nl4wp_form_error_required_field_missing    One or more required fields are missing
                 * - nl4wp_form_error_no_lists_selected         No Newsletter lists were selected
                 *
                 * @since 3.0
                 *
                 * @param   NL4WP_Form     $form        The form instance of the submitted form.
                 */
                do_action('nl4wp_form_error_' . $error, $form);
            }
        }

        /**
         * Fires right before responding to the form request.
         *
         * @since 3.0
         *
         * @param NL4WP_Form $form Instance of the submitted form.
         */
        do_action('nl4wp_form_respond', $form);

        // do stuff on success (if form was submitted over plain HTTP, not for AJAX or REST requests)
        if ($success && ! $this->request_wants_json()) {
            $redirect_url = $form->get_redirect_url();
            if (! empty($redirect_url)) {
                wp_redirect($redirect_url);
                exit;
            }
        }
    }

    private function request_wants_json()
    {
        if (isset($_SERVER['HTTP_ACCEPT']) && false !== strpos(wp_unslash($_SERVER['HTTP_ACCEPT']), 'application/json')) {
            return true;
        }

        return false;
    }

    /**
     * @return NL4WP_API_V3
     */
    protected function get_api()
    {
        return nl4wp_get_service('api');
    }

    /**
     * @return NL4WP_Debug_Log
     */
    protected function get_log()
    {
        return nl4wp_get_service('log');
    }
}
