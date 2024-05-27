<?php

class NL4WP_Google_Recaptcha {

    private $form_ids = array();

    public function add_hooks() {
        add_filter('nl4wp_form_settings', array($this, 'add_default_form_settings'));
        add_filter('nl4wp_settings', array($this, 'add_default_settings'));
        add_action('nl4wp_output_form', array($this, 'on_output_form'), 20);
        add_filter('nl4wp_form_errors', array($this, 'verify_token'), 10, 2);
        add_action('nl4wp_admin_form_after_behaviour_settings_rows', array($this, 'show_settings'), 30, 2);
        add_filter('nl4wp_form_sanitized_data', array($this, 'sanitize_settings'), 20, 2);
        add_action('wp_footer', array($this, 'load_script'), 8000);
    }


    public function add_default_settings($settings) {
        $defaults = array(
            'grecaptcha_site_key' => '',
            'grecaptcha_secret_key' => '',
        );
        $settings = array_merge($defaults, $settings);
        return $settings;
    }

    public function add_default_form_settings($settings) {
        $defaults = array(
            'grecaptcha_enabled' => 0,
        );
        $settings = array_merge($defaults, $settings);
        return $settings;
    }

    public function sanitize_settings($data, $raw_data) {
        if (!isset($data['settings']['grecaptcha_enabled']) || !$data['settings']['grecaptcha_enabled']) {
            return $data;
        }

        // only enable grecaptcha if both site & secret key are set
        $global_settings = nl4wp_get_settings();
        $data['settings']['grecaptcha_enabled'] = isset($global_settings['grecaptcha_site_key'])
            && isset($global_settings['grecaptcha_secret_key'])
            && strlen($global_settings['grecaptcha_site_key']) === 40
            && strlen($global_settings['grecaptcha_secret_key']) === 40 ? '1' : '0';
        return $data;
    }

    public function load_script() {
        $global_settings = nl4wp_get_settings();

        // do not load if no forms with Google reCAPTCHA enabled were outputted
        if (empty($this->form_ids) || empty($global_settings['grecaptcha_site_key']) || empty($global_settings['grecaptcha_secret_key'])) {
            return;
        }

        // load Google reCAPTCHA script
        echo sprintf('<script src="https://www.google.com/recaptcha/api.js?render=%s"></script>', esc_attr($global_settings['grecaptcha_site_key']));

        // hook into form submit
        ?><script>
            (function() {
                var formIds = <?php echo json_encode($this->form_ids); ?>;

                function addGoogleReCaptchaTokenToForm(form, event) {
                    event.preventDefault();

                    var submitForm = function() {
                        if(form.element.className.indexOf('nl4wp-ajax') > -1) {
                            nl4wp.forms.trigger('submit', [form, event]);
                        } else {
                            form.element.submit();
                        }
                    };
                    var previousToken = form.element.querySelector('input[name=_nl4wp_grecaptcha_token]');
                    if (previousToken) {
                        previousToken.parentElement.removeChild(previousToken);
                    }

                    window.grecaptcha
                        .execute('<?php echo esc_attr($global_settings['grecaptcha_site_key']); ?>', {action: 'nl4wp_form_submit'})
                        .then(function (token) {
                            var tokenEl = document.createElement('input');
                            tokenEl.type = 'hidden';
                            tokenEl.value = token;
                            tokenEl.name = '_nl4wp_grecaptcha_token';
                            form.element.appendChild(tokenEl);
                            submitForm();
                        })
                }

                for(var i=0; i<formIds.length; i++) {
                    nl4wp.forms.on(formIds[i]+'.submit', addGoogleReCaptchaTokenToForm)
                }
            })();
        </script><?php
    }

    public function on_output_form(NL4WP_Form $form) {
        // Check if form has Google ReCaptcha enabled
        if (!$form->settings['grecaptcha_enabled']) {
            return;
        }

        if (!in_array($form->ID, $this->form_ids)) {
            $this->form_ids[] = $form->ID;
        }
    }

    public function verify_token(array $errors, NL4WP_Form $form) {
        // Check if form has Google ReCaptcha enabled
        if (!$form->settings['grecaptcha_enabled']) {
            return $errors;
        }

        // Verify token
        if (empty($_POST['_nl4wp_grecaptcha_token'])) {
            $errors[] = 'spam';
            return $errors;
        }

        $global_settings = nl4wp_get_settings();
        $token = $_POST['_nl4wp_grecaptcha_token'];
        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret' => $global_settings['grecaptcha_secret_key'],
                'response' => $token,
            ),
        ));

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code >= 400) {
            // The request somehow failed... Allow the sign-up to go through to not break sign-up forms when Google reCaptcha is down (unlikely)
            return $errors;
        }

        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);
        $score_treshold = apply_filters('nl4wp_grecaptcha_score_treshold', 0.5);

        if (isset($data['error-codes']) && in_array('invalid-input-secret', $data['error-codes'])) {
            $this->get_log()->warning(sprintf('Form %d > Invalid Google reCAPTCHA secret key', $form->ID));
            return $errors;
        }

        if ($data['success'] === false || !isset($data['score']) || $data['score'] <= $score_treshold || $data['action'] !== 'nl4wp_form_submit') {
            $errors[] = 'spam';
            return $errors;
        }

        return $errors;
    }

    public function show_settings(array $settings, NL4WP_Form $form) {
        $global_settings = nl4wp_get_settings();
        ?>
        <tr valign="top">
            <th scope="row"><?php _e('Enable Google reCaptcha', 'newsletter-for-wp' ); ?></th>
            <td>
                <label><input type="radio" name="nl4wp_form[settings][grecaptcha_enabled]" value="1" <?php checked($settings['grecaptcha_enabled'], 1); ?> /> <?php _e('Yes'); ?> &rlm;</label>
                 &nbsp;
                <label><input type="radio" name="nl4wp_form[settings][grecaptcha_enabled]" value="0" <?php checked($settings['grecaptcha_enabled'], 0); ?> /> <?php _e('No'); ?> &rlm;</label>
                <p class="help">
                    <?php _e( 'Select "yes" to enable Google reCAPTCHA spam protection for this form.', 'newsletter-for-wp'); ?>
                </p>
            </td>
        </tr>
        <?php $config = array( 'element' => 'nl4wp_form[settings][grecaptcha_enabled]', 'value' => 1 ); ?>
        <tr valign="top" data-showif="<?php echo esc_attr(json_encode($config)); ?>">
            <th scope="row"><label for="nl4wp_grecaptcha_site_key"><?php _e('Google reCAPTCHA Site Key', 'newsletter-for-wp'); ?></label></th>
            <td>
                <input type="text" class="widefat" name="nl4wp[grecaptcha_site_key]" id="nl4wp_grecaptcha_site_key" placeholder="<?php echo str_repeat('●', 40); ?>" value="<?php echo esc_attr($global_settings['grecaptcha_site_key']); ?>" />
                <p class="help">
                    <?php printf(__('Enter your Google reCAPTCHA keys here. You can <a href="%s">retrieve your keys in the Google reCAPTCHA admin console</a>.', 'newsletter-for-wp'), 'https://g.co/recaptcha/v3'); ?>
                </p>
            </td>
        </tr>
        <?php $config = array( 'element' => 'nl4wp_form[settings][grecaptcha_enabled]', 'value' => 1 ); ?>
        <tr valign="top" data-showif="<?php echo esc_attr(json_encode($config)); ?>">
            <th scope="row"><label for="nl4wp_grecaptcha_secret_key"><?php _e('Google reCAPTCHA Secret Key', 'newsletter-for-wp'); ?></label></th>
            <td>
                <input type="text" class="widefat" name="nl4wp[grecaptcha_secret_key]" id="nl4wp_grecaptcha_secret_key" placeholder="<?php echo str_repeat('●', 40); ?>" value="<?php echo esc_attr($global_settings['grecaptcha_secret_key']); ?>" />
                <p class="help">
                    <?php _e('', 'newsletter-for-wp'); ?>
                </p>
            </td>
        </tr>
        <?php
    }

    /**
     * @return NL4WP_Debug_Log
     */
    private function get_log() {
        return nl4wp('log');
    }
}