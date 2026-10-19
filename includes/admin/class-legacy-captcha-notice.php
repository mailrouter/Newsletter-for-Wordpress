<?php

defined('ABSPATH') || exit;

/**
 * Warns administrators about CAPTCHA settings that are no longer supported.
 *
 * The notice is intentionally actionable: dismissing it removes only the
 * legacy CAPTCHA values and per-form flags, so the warning cannot reappear.
 *
 * @ignore
 */
class NL4WP_Admin_Legacy_Captcha_Notice
{
    /**
     * @var string
     */
    private const OPTION_NAME = 'nl4wp';

    /**
     * @var string
     */
    private const FORM_POST_TYPE = 'nl4wp-form';

    /**
     * @var string[]
     */
    private const CAPTCHA_KEYS = [
        'grecaptcha_site_key',
        'grecaptcha_secret_key',
        'grecaptcha_enabled',
    ];

    /**
     * Add hooks.
     *
     * @return void
     */
    public function add_hooks()
    {
        add_action('admin_notices', [ $this, 'show' ]);
        add_action('nl4wp_admin_dismiss_legacy_captcha_notice', [ $this, 'dismiss' ]);
    }

    /**
     * Show the warning to users who can manage the plugin settings.
     *
     * @return void
     */
    public function show()
    {
        $capability = (string) apply_filters('nl4wp_admin_required_capability', 'manage_options');

        if (! current_user_can($capability) || ! $this->has_legacy_settings()) {
            return;
        }

        $settings_url = admin_url('admin.php?page=newsletter-for-wp-integrations&integration=prosopo-procaptcha');
        $message      = sprintf(
            __('Legacy CAPTCHA settings are no longer used. <a href="%s">Configure Procaptcha</a>, or dismiss this notice to permanently delete the saved CAPTCHA settings.', 'newsletter-for-wp'),
            esc_url($settings_url)
        );

        echo '<div class="notice notice-warning nl4wp-is-dismissible">';
        echo '<p>', wp_kses($message, [ 'a' => [ 'href' => [] ] ]), '</p>';
        echo '<form method="post">';
        wp_nonce_field('_nl4wp_action', '_wpnonce');
        echo '<input type="hidden" name="_nl4wp_action" value="dismiss_legacy_captcha_notice" />';
        echo '<button type="submit" class="notice-dismiss"><span class="screen-reader-text">';
        echo esc_html__('Dismiss this notice and delete the saved CAPTCHA settings.', 'newsletter-for-wp');
        echo '</span></button>';
        echo '</form>';
        echo '</div>';
    }

    /**
     * Remove the legacy global settings and per-form enable flags.
     *
     * This method is called only through NL4WP_Admin::listen_for_actions(),
     * which verifies the nonce and the required capability first.
     *
     * @return void
     */
    public function dismiss()
    {
        $options = get_option(self::OPTION_NAME, []);

        if (is_array($options)) {
            $changed = false;

            foreach (self::CAPTCHA_KEYS as $key) {
                if (array_key_exists($key, $options)) {
                    unset($options[ $key ]);
                    $changed = true;
                }
            }

            if ($changed) {
                update_option(self::OPTION_NAME, $options);
            }
        }

        foreach ($this->get_forms() as $form) {
            $settings = maybe_unserialize(get_post_meta($form->ID, '_nl4wp_settings', true));

            if (! is_array($settings) || ! array_key_exists('grecaptcha_enabled', $settings)) {
                continue;
            }

            unset($settings['grecaptcha_enabled']);
            update_post_meta($form->ID, '_nl4wp_settings', $settings);
        }
    }

    /**
     * Whether any legacy CAPTCHA setting is still stored.
     *
     * @return bool
     */
    private function has_legacy_settings()
    {
        $options = get_option(self::OPTION_NAME, []);

        if (is_array($options)) {
            foreach (self::CAPTCHA_KEYS as $key) {
                if (! empty($options[ $key ])) {
                    return true;
                }
            }
        }

        foreach ($this->get_forms() as $form) {
            $settings = maybe_unserialize(get_post_meta($form->ID, '_nl4wp_settings', true));

            if (is_array($settings) && ! empty($settings['grecaptcha_enabled'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all forms, including drafts, so dismissal is complete.
     *
     * @return WP_Post[]
     */
    private function get_forms()
    {
        return get_posts([
            'post_type'      => self::FORM_POST_TYPE,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'no_found_rows'  => true,
        ]);
    }
}
