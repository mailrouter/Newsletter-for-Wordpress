<?php

defined('ABSPATH') || exit;

/**
 * Class NL4WP_Ninja_Forms_Integration
 *
 * @ignore
 */
class NL4WP_Gravity_Forms_Integration extends NL4WP_Integration
{
    /**
     * @var string
     */
    public $name = 'Gravity Forms';

    /**
     * @var string
     */
    public $description = 'Subscribe visitors from your Gravity Forms forms.';


    /**
     * Add hooks
     */
    public function add_hooks()
    {
        add_action('gform_field_standard_settings', [ $this, 'settings_fields' ], 10, 2);
        add_action('gform_editor_js', [ $this, 'editor_js' ]);
        add_action('gform_after_submission', [ $this, 'after_submission' ], 10, 2);
    }

    public function after_submission($submission, $form)
    {
        $subscribe         = false;
        $email_address     = '';
        $newsletter_list_id = '';
        $double_optin      = $this->options['double_optin'];

        // find email field & checkbox value
        foreach ($form['fields'] as $field) {
            if ($field->type === 'email' && empty($email_address) && ! empty($submission[ $field->id ])) {
                $email_address = $submission[ $field->id ];
            }

            if ($field->type === 'newsletter' && ! empty($submission[ $field->id ])) {
                $subscribe         = true;
                $newsletter_list_id = $field->newsletter_list;

                if (isset($field->newsletter_double_optin)) {
                    $double_optin = $field->newsletter_double_optin;
                }
            }
        }

        if (! $subscribe || empty($email_address)) {
            return;
        }

        // override integration settings with field options
        $orig_options                  = $this->options;
        $this->options['lists']        = [ $newsletter_list_id ];
        $this->options['double_optin'] = $double_optin;

        // perform the sign-up
        $this->subscribe([ 'EMAIL' => $email_address ], $submission['form_id']);

        // revert back to original options in case request lives on
        $this->options = $orig_options;
    }

    public function editor_js()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).on('gform_load_field_settings', function(evt, field) {
                jQuery('#field_newsletter_list').val(field.newsletter_list || '');
                jQuery('#field_newsletter_double_optin').val(field.newsletter_double_optin || "1");
                jQuery('#field_newsletter_precheck').val(field.newsletter_precheck || "0");
            });

            // Sync label changes to checkbox label text in real-time.
            if (window.gform) {
                gform.addAction('gform_post_set_field_property', function(name, field) {
                    if (name === 'label' && field.type === 'newsletter') {
                        jQuery('#field_' + field.id + ' .gfield_checkbox label').text(field.label);
                    }
                });
            }
        </script>
        <?php
    }

    public function settings_fields($pos, $form_id)
    {
        if ($pos !== 0) {
            return;
        }

        $newsletter = new NL4WP_Newsletter();
        $lists     = $newsletter->get_lists();
        ?>
        <li class="newsletter_list_setting field_setting">
            <label for="field_newsletter_list" class="section_label">
                <?php esc_html_e('Newsletter list', 'newsletter-for-wp'); ?>
            </label>
            <select id="field_newsletter_list" onchange="SetFieldProperty('newsletter_list', this.value)">
                <option value="" disabled><?php esc_html_e('Select a Newsletter list', 'newsletter-for-wp'); ?></option>
                <?php
                foreach ($lists as $list) {
                    printf('<option value="%s">%s</option>', esc_attr($list->id), esc_html($list->name));
                }
                ?>
            </select>
            <p class="help">
                <?php echo esc_html__('Select the list(s) to which people who check the checkbox should be subscribed.', 'newsletter-for-wp'); ?>
            </p>
        </li>
        <li class="newsletter_double_optin field_setting">
            <label for="field_newsletter_double_optin" class="section_label">
                <?php esc_html_e('Double opt-in?', 'newsletter-for-wp'); ?>
            </label>
            <select id="field_newsletter_double_optin" onchange="SetFieldProperty('newsletter_double_optin', this.value)">
                <option value="1"><?php echo esc_html__('Yes', 'newsletter-for-wp'); ?></option>
                <option value="0"><?php echo esc_html__('No', 'newsletter-for-wp'); ?></option>
            </select>
            <p class="help">
                <?php esc_html_e('Select "yes" if you want people to confirm their email address before being subscribed (recommended)', 'newsletter-for-wp'); ?>
            </p>
        </li>
        <li class="newsletter_precheck field_setting">
            <label for="field_newsletter_precheck" class="section_label">
                <?php esc_html_e('Pre-check the checkbox?', 'newsletter-for-wp'); ?>
            </label>
            <select id="field_newsletter_precheck" onchange="SetFieldProperty('newsletter_precheck', this.value)">
                <option value="1"><?php echo esc_html__('Yes', 'newsletter-for-wp'); ?></option>
                <option value="0"><?php echo esc_html__('No', 'newsletter-for-wp'); ?></option>
            </select>
            <p class="help">
                <?php
                esc_html_e('Select "yes" if the checkbox should be pre-checked.', 'newsletter-for-wp');
                echo '<br />';
                // translators: %s is the URL to the GDPR compliance documentation.
                echo wp_kses(
                    sprintf(
                        /* translators: %s is the URL to the GDPR compliance documentation. */
                        __('<strong>Warning: </strong> enabling this may affect your GDPR compliance.', 'newsletter-for-wp'),

                    ),
                    [
                        'strong' => [],
                        'a'      => [ 'href' => [] ],
                    ]
                );
                ?>
            </p>
        </li>
        <?php
    }

    /**
     * @return bool
     */
    public function is_installed()
    {
        return class_exists('GF_Field') && class_exists('GF_Fields');
    }

    /**
     * @since 3.0
     * @return array
     */
    public function get_ui_elements()
    {
        return [];
    }

    /**
     * @param int $form_id
     * @return string
     */
    public function get_object_link($form_id)
    {
        return '<a href="' . admin_url(sprintf('admin.php?page=gf_edit_forms&id=%d', $form_id)) . '">Gravity Forms</a>';
    }
}
