<?php defined('ABSPATH') || exit;
/** @var NL4WP_Integration $integration */
/** @var array $opts */
?>
<div id="nl4wp-admin" class="wrap nl4wp-settings">
    <p class="nl4wp-breadcrumbs">
        <span class="prefix"><?php echo esc_html__('You are here: ', 'newsletter-for-wp'); ?></span>
        <a href="<?php echo esc_url(admin_url('admin.php?page=newsletter-for-wp')); ?>">Newsletter for WordPress</a> &rsaquo;
        <a href="<?php echo esc_url(admin_url('admin.php?page=newsletter-for-wp-integrations')); ?>"><?php echo esc_html__('Integrations', 'newsletter-for-wp'); ?></a> &rsaquo;
        <span class="current-crumb"><strong><?php echo esc_html($integration->name); ?></strong></span>
    </p>
    <div class="nl4wp-row">
        <div class="main-content nl4wp-col">
            <h1 class="nl4wp-page-title">
                <?php
                // translators: %s is the name of the integration (e.g. Contact Form 7, WooCommerce).
                printf(esc_html__('%s integration', 'newsletter-for-wp'), esc_html($integration->name));
                ?>
            </h1>

            <h2 style="display: none;"></h2>
            <?php settings_errors(); ?>

            <div id="notice-additional-fields" class="notice notice-warning" style="display: none;">
                <p><?php echo esc_html__('The selected Newsletter audience requires custom fields, which may prevent this integration from working.', 'newsletter-for-wp'); ?></p>
                <p>
                <?php
                    // translators: 1: URL to the NL4WP documentation on sending additional fields, 2: URL to the Newsletter audience settings page.

                ?>
                </p>
            </div>

            <p>
                <?php echo esc_html($integration->description); ?>
            </p>

            <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
                <?php settings_fields('nl4wp_integrations_settings'); ?>

                <?php
                do_action('nl4wp_admin_before_integration_settings', $integration, $opts);
                do_action('nl4wp_admin_before_' . $integration->slug . '_integration_settings', $integration, $opts);
                ?>

                <table class="form-table">
                    <?php
                    if ($integration->has_ui_element('enabled')) {
                        ?>
                        <tbody>
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Enabled?', 'newsletter-for-wp'); ?></th>
                            <td class="nowrap integration-toggles-wrap">
                                <label><input type="radio" name="nl4wp_integrations[<?php echo esc_attr($integration->slug); ?>][enabled]" value="1" <?php checked($opts['enabled'], 1); ?> /> <?php echo esc_html__('Yes', 'newsletter-for-wp'); ?></label> &nbsp;
                                <label><input type="radio" name="nl4wp_integrations[<?php echo esc_attr($integration->slug); ?>][enabled]" value="0" <?php checked($opts['enabled'], 0); ?> /> <?php echo esc_html__('No', 'newsletter-for-wp'); ?></label>
                                <p class="description">
                                <?php
                                    // translators: %s is the name of the integration (e.g. Contact Form 7, WooCommerce).
                                    printf(esc_html__('Enable the %s integration? This will add a sign-up checkbox to the form.', 'newsletter-for-wp'), esc_html($integration->name));
                                ?>
                                </p>
                            </td>
                        </tr>
                        </tbody>
                        <?php
                    }
                    ?>

                    <?php
                    $config = [
                        'element' => 'nl4wp_integrations[' . $integration->slug . '][enabled]',
                        'value'   => '1',
                        'hide'    => false,
                    ];
                    ?>
                    <tbody class="integration-toggled-settings" data-showif="<?php echo esc_attr(json_encode($config)); ?>">

                    <?php
                    if ($integration->has_ui_element('implicit')) {
                        ?>
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Implicit?', 'newsletter-for-wp'); ?></th>
                            <td class="nowrap">
                                <label><input type="radio" name="nl4wp_integrations[<?php echo esc_attr($integration->slug); ?>][implicit]" value="1" <?php checked($opts['implicit'], 1); ?> /> <?php echo esc_html__('Yes', 'newsletter-for-wp'); ?></label> &nbsp;
                                <label><input type="radio" name="nl4wp_integrations[<?php echo esc_attr($integration->slug); ?>][implicit]" value="0" <?php checked($opts['implicit'], 0); ?> /> <?php echo esc_html__('No', 'newsletter-for-wp'); ?> <?php echo '<em>', esc_html__('(recommended)', 'newsletter-for-wp'), '</em>'; ?>
                            </label>
                                <p class="description">
                                    <?php
                                    echo esc_html__('Select "yes" if you want to subscribe people without asking them explicitly.', 'newsletter-for-wp');
                                    echo '<br />';

                                    printf(
                                        wp_kses(
                                            // translators: %s is a URL to the GDPR compliance documentation.
                                            __('<strong>Warning: </strong> enabling this may affect your GDPR compliance.', 'newsletter-for-wp'),
                                            [
                                            'a' => [ 'href' => [] ],
                                            'strong' => [],
                                            ]
                                        )
                                    );
                                    ?>
                                    </p>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>

                    <?php
                    if ($integration->has_ui_element('lists')) {
                        ?>
                        <?php // hidden input to make sure a value is sent to the server when no checkboxes were selected ?>
                        <input type="hidden" name="nl4wp_integrations[<?php echo esc_attr($integration->slug); ?>][lists][]" value="" />
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Newsletter audiences', 'newsletter-for-wp'); ?></th>
                            <?php
                            if (! empty($lists)) {
                                echo '<td>';
                                echo '<ul style="margin-bottom: 20px; max-height: 300px; overflow-y: auto;">';
                                foreach ($lists as $list) {
                                    $checked = checked(in_array($list->id, $opts['lists'], true), true, false);
                                    $value   = esc_attr($list->id);
                                    echo '<li><label>';
                                    printf('<input type="checkbox" name="nl4wp_integrations[%1$s][lists][]" value="%2$s" class="nl4wp-list-input" %3$s> ', esc_attr($integration->slug), esc_attr($value), esc_attr($checked));
                                    echo esc_html($list->name);
                                    echo '</label></li>';
                                }
                                echo '</ul>';

                                echo '<p class="description">';
                                echo esc_html__('Select the audiences to which people who check the checkbox should be subscribed.', 'newsletter-for-wp');
                                echo '</p>';
                                echo '</td>';
                            } else {
                                echo '<td>';
                                // translators: %s is the URL to the Newsletter for WordPress settings page.
                                printf(wp_kses(__('No audiences found, <a href="%s">are you connected to Newsletter</a>?', 'newsletter-for-wp'), [ 'a' => [ 'href' => [] ] ]), esc_url(admin_url('admin.php?page=newsletter-for-wp')));
                                echo '</td>';
                            }
                            ?>
                        </tr>
                        <?php
                    } // end if UI has lists
                    ?>

                    <?php
                    if ($integration->has_ui_element('label')) {
                        $config = [
                            'element' => 'nl4wp_integrations[' . $integration->slug . '][implicit]',
                            'value'   => 0,
                        ];
                        ?>
                        <tr valign="top" data-showif="<?php echo esc_attr(json_encode($config)); ?>">
                            <th scope="row"><label for="nl4wp_checkbox_label"><?php echo esc_html__('Checkbox label text', 'newsletter-for-wp'); ?></label></th>
                            <td>
                                <input type="text"  class="widefat" id="nl4wp_checkbox_label" name="nl4wp_integrations[<?php echo esc_attr($integration->slug); ?>][label]" value="<?php echo esc_attr($opts['label']); ?>" required />
                                <p class="description">
                                <?php
                                    // translators: %s is a list of allowed HTML tags (e.g. <strong><em><a>).
                                    printf(esc_html__('HTML tags like %s are allowed in the label text.', 'newsletter-for-wp'), '<code>' . esc_html('<strong><em><a>') . '</code>');
                                ?>
                                </p>
                            </td>
                        </tr>
                        <?php
                    } // end if UI label
                    ?>


                    <?php
                    if ($integration->has_ui_element('precheck')) {
                        $config = [
                            'element' => 'nl4wp_integrations[' . $integration->slug . '][implicit]',
                            'value'   => 0,
                        ];
                        ?>
                        <tr valign="top" data-showif="<?php echo esc_attr(json_encode($config)); ?>">
                            <th scope="row"><?php echo esc_html__('Pre-check the checkbox?', 'newsletter-for-wp'); ?></th>
                            <td class="nowrap">
                                <label><input type="radio" name="nl4wp_integrations[<?php echo esc_attr($integration->slug); ?>][precheck]" value="1" <?php checked($opts['precheck'], 1); ?> /> <?php echo esc_html__('Yes', 'newsletter-for-wp'); ?></label> &nbsp;
                                <label><input type="radio" name="nl4wp_integrations[<?php echo esc_attr($integration->slug); ?>][precheck]" value="0" <?php checked($opts['precheck'], 0); ?> /> <?php echo esc_html__('No', 'newsletter-for-wp'); ?> <?php echo '<em>' . esc_html__('(recommended)', 'newsletter-for-wp') . '</em>'; ?></label>
                                <p class="description">
                                    <?php
                                    echo esc_html__('Select "yes" if the checkbox should be pre-checked.', 'newsletter-for-wp');
                                    echo '<br />';
                                    printf(
                                        wp_kses(
                                            // translators: %s is a URL to the GDPR compliance documentation.
                                            __('<strong>Warning: </strong> enabling this may affect your GDPR compliance.', 'newsletter-for-wp'),
                                            [
                                                    'a'      => [ 'href' => [] ],
                                                    'strong' => [],
                                            ]
                                        )
                                    );
                                    ?>
                                </p>
                            </td>
                        <?php
                    } // end if UI precheck
                    ?>

                    <?php
                    if ($integration->has_ui_element('css')) {
                        $config = [
                            'element' => 'nl4wp_integrations[' . $integration->slug . '][implicit]',
                            'value'   => 0,
                        ];
                        ?>
                        <tr valign="top" data-showif="<?php echo esc_attr(json_encode($config)); ?>">
                            <th scope="row"><?php echo esc_html__('Load some default CSS?', 'newsletter-for-wp'); ?></th>
                            <td class="nowrap">
                                <label><input type="radio" name="nl4wp_integrations[<?php echo esc_attr($integration->slug); ?>][css]" value="1" <?php checked($opts['css'], 1); ?> />&rlm; <?php echo esc_html__('Yes', 'newsletter-for-wp'); ?></label> &nbsp;
                                <label><input type="radio" name="nl4wp_integrations[<?php echo esc_attr($integration->slug); ?>][css]" value="0" <?php checked($opts['css'], 0); ?> />&rlm; <?php echo esc_html__('No', 'newsletter-for-wp'); ?></label>
                                <p class="description"><?php echo esc_html__('Select "yes" if the checkbox appears in a weird place.', 'newsletter-for-wp'); ?></p>
                            </td>
                        </tr>
                        <?php
                    } // end if UI css
                    ?>

                    <?php
                    if ($integration->has_ui_element('double_optin')) {
                        ?>
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Double opt-in?', 'newsletter-for-wp'); ?></th>
                            <td class="nowrap">
                                <label>
                                    <input type="radio" name="nl4wp_integrations[<?php echo esc_attr($integration->slug); ?>][double_optin]" value="1" <?php checked($opts['double_optin'], 1); ?> />&rlm;
                                    <?php echo esc_html__('Yes', 'newsletter-for-wp'); ?>
                                </label> &nbsp;
                                <label>
                                    <input type="radio" id="nl4wp_checkbox_double_optin_0" name="nl4wp_integrations[<?php echo esc_attr($integration->slug); ?>][double_optin]" value="0" <?php checked($opts['double_optin'], 0); ?> />&rlm;
                                    <?php echo esc_html__('No', 'newsletter-for-wp'); ?>
                                </label>
                                <p class="description">
                                    <?php echo esc_html__('Select "yes" if you want people to confirm their email address before being subscribed (recommended)', 'newsletter-for-wp'); ?>
                                </p>
                            </td>
                        </tr>
                        <?php
                    } // end if UI double_optin
                    ?>

                    <?php
                    if ($integration->has_ui_element('update_existing')) {
                        ?>
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Update existing subscribers?', 'newsletter-for-wp'); ?></th>
                            <td class="nowrap">
                                <label>
                                    <input type="radio" name="nl4wp_integrations[<?php echo esc_attr($integration->slug); ?>][update_existing]" value="1" <?php checked($opts['update_existing'], 1); ?> />&rlm;
                                    <?php echo esc_html__('Yes', 'newsletter-for-wp'); ?>
                                </label> &nbsp;
                                <label>
                                    <input type="radio" name="nl4wp_integrations[<?php echo esc_attr($integration->slug); ?>][update_existing]" value="0" <?php checked($opts['update_existing'], 0); ?> />&rlm;
                                    <?php echo esc_html__('No', 'newsletter-for-wp'); ?>
                                </label>
                                <p class="description"><?php echo esc_html__('Select "yes" if you want to update existing subscribers with the data that is sent.', 'newsletter-for-wp'); ?></p>
                            </td>
                        </tr>
                        <?php
                    } // end if UI update_existing
                    ?>

                    <?php
                    if ($integration->has_ui_element('replace_interests')) {
                        $config = [
                            'element' => 'nl4wp_integrations[' . $integration->slug . '][update_existing]',
                            'value'   => 1,
                        ];
                        ?>
                        <tr valign="top" data-showif="<?php echo esc_attr(json_encode($config)); ?>">
                            <th scope="row"><?php echo esc_html__('Replace interest groups?', 'newsletter-for-wp'); ?></th>
                            <td class="nowrap">
                                <label>
                                    <input type="radio" name="nl4wp_integrations[<?php echo esc_attr($integration->slug); ?>][replace_interests]" value="1" <?php checked($opts['replace_interests'], 1); ?> />&rlm;
                                    <?php echo esc_html__('Yes', 'newsletter-for-wp'); ?>
                                </label> &nbsp;
                                <label>
                                    <input type="radio" name="nl4wp_integrations[<?php echo esc_attr($integration->slug); ?>][replace_interests]" value="0" <?php checked($opts['replace_interests'], 0); ?> />&rlm;
                                    <?php echo esc_html__('No', 'newsletter-for-wp'); ?>
                                </label>
                                <p class="description">
                                    <?php echo esc_html__('Select "no" if you want to add the selected interests to any previously selected interests when updating a subscriber.', 'newsletter-for-wp'); ?>
                                    >
                                </p>
                            </td>
                        </tr>
                        <?php
                    } // end if UI replace_interests
                    ?>

                    </tbody>
                </table>

                <?php
                do_action('nl4wp_admin_after_integration_settings', $integration, $opts);
                do_action('nl4wp_admin_after_' . $integration->slug . '_integration_settings', $integration, $opts);
                ?>

                <?php
                if (count($integration->get_ui_elements()) > 0) {
                    submit_button();
                }
                ?>

            </form>
        </div>
    </div>
</div>
