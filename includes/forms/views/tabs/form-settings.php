<?php defined('ABSPATH') || exit; ?>

<h2><?php echo esc_html__('Form Settings', 'newsletter-for-wp'); ?></h2>

<div class="nl4wp-margin-m"></div>

<h3><?php echo esc_html__('Newsletter specific settings', 'newsletter-for-wp'); ?></h3>

<table class="form-table" style="table-layout: fixed;">

    <?php do_action('nl4wp_admin_form_before_newsletter_settings_rows', $opts, $form); ?>

    <tr valign="top">
        <th scope="row" style="width: 250px;"><?php echo esc_html__('Audiences this form subscribes to', 'newsletter-for-wp'); ?></th>
        <?php
        // loop through lists
        if (empty($lists)) {
            ?>
            <td colspan="2">
            <?php
                // translators: %s is the URL to the Newsletter for WordPress settings page.
                printf(wp_kses(__('No audiences found, <a href="%s">are you connected to Newsletter</a>?', 'newsletter-for-wp'), [ 'a' => [ 'href' => [] ] ]), esc_url(admin_url('admin.php?page=newsletter-for-wp')));
            ?>
            </td>
            <?php
        } else {
            ?>
            <td >

                <ul id="nl4wp-lists" style="margin-bottom: 20px; max-height: 300px; overflow-y: auto;">
                    <?php
                    foreach ($lists as $list) {
                        ?>
                        <li>
                            <label>
                                <input class="nl4wp-list-input" type="checkbox" name="nl4wp_form[settings][lists][]" value="<?php echo esc_attr($list->id); ?>" <?php checked(in_array($list->id, $opts['lists']), true); ?>> <?php echo esc_html($list->name); ?>
                            </label>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <p class="description"><?php echo esc_html__('Select the Newsletter audience to which people who submit this form should be subscribed.', 'newsletter-for-wp'); ?></p>
            </td>
            <?php
        }
        ?>

    </tr>
    <tr valign="top">
        <th scope="row"><?php echo esc_html__('Use double opt-in?', 'newsletter-for-wp'); ?></th>
        <td class="nowrap">
            <label>
                <input type="radio"  name="nl4wp_form[settings][double_optin]" value="1" <?php checked($opts['double_optin'], 1); ?> />&rlm;
                <?php echo esc_html__('Yes', 'newsletter-for-wp'); ?>
            </label> &nbsp;
            <label>
                <input type="radio" name="nl4wp_form[settings][double_optin]" value="0" <?php checked($opts['double_optin'], 0); ?> onclick="return confirm('<?php echo esc_attr__('Are you sure you want to disable double opt-in?', 'newsletter-for-wp'); ?>');" />&rlm;
                <?php echo esc_html__('No', 'newsletter-for-wp'); ?>
            </label>
            <p class="description"><?php echo esc_html__('We strongly suggest keeping double opt-in enabled. Disabling double opt-in may affect your GDPR compliance.', 'newsletter-for-wp'); ?></p>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php echo esc_html__('Update existing subscribers?', 'newsletter-for-wp'); ?></th>
        <td class="nowrap">
            <label>
                <input type="radio" name="nl4wp_form[settings][update_existing]" value="1" <?php checked($opts['update_existing'], 1); ?> />&rlm;
                <?php echo esc_html__('Yes', 'newsletter-for-wp'); ?>
            </label> &nbsp;
            <label>
                <input type="radio" name="nl4wp_form[settings][update_existing]" value="0" <?php checked($opts['update_existing'], 0); ?> />&rlm;
                <?php echo esc_html__('No', 'newsletter-for-wp'); ?>
            </label>
            <p class="description"><?php echo esc_html__('Select "yes" if you want to update existing subscribers with the data that is sent.', 'newsletter-for-wp'); ?></p>
        </td>
    </tr>

    <?php
    $config = [
        'element' => 'nl4wp_form[settings][update_existing]',
        'value'   => 1,
    ];
    ?>
    <tr valign="top" data-showif="<?php echo esc_attr(json_encode($config)); ?>">
        <th scope="row"><?php echo esc_html__('Replace interest groups?', 'newsletter-for-wp'); ?></th>
        <td class="nowrap">
            <label>
                <input type="radio" name="nl4wp_form[settings][replace_interests]" value="1" <?php checked($opts['replace_interests'], 1); ?> />&rlm;
                <?php echo esc_html__('Yes', 'newsletter-for-wp'); ?>
            </label> &nbsp;
            <label>
                <input type="radio" name="nl4wp_form[settings][replace_interests]" value="0" <?php checked($opts['replace_interests'], 0); ?> />&rlm;
                <?php echo esc_html__('No', 'newsletter-for-wp'); ?>
            </label>
            <p class="description">
                <?php echo esc_html__('Select "no" if you want to add the selected interests to any previously selected interests when updating a subscriber.', 'newsletter-for-wp'); ?>
                
            </p>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><label for="nl4wp_form_add_tags"><?php echo esc_html__('Add tags', 'newsletter-for-wp'); ?></label></th>
        <td>
            <input type="text" class="widefat" name="nl4wp_form[settings][subscriber_tags]" id="nl4wp_form_add_tags" placeholder="<?php echo esc_attr__('Example: My tag, another tag', 'newsletter-for-wp'); ?>" value="<?php echo esc_attr($opts['subscriber_tags']); ?>" />
            <p class="description">
                <?php echo esc_html__('The listed tags will be applied to all subscribers added or updated by this form.', 'newsletter-for-wp'); ?>
                <?php echo esc_html__('Separate multiple values with a comma.', 'newsletter-for-wp'); ?>
            </p>

        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><label for="nl4wp_form_remove_tags"><?php echo esc_html__('Remove tags', 'newsletter-for-wp'); ?></label></th>
        <td>
            <input type="text" class="widefat" name="nl4wp_form[settings][remove_subscriber_tags]" id="nl4wp_form_remove_tags" placeholder="<?php echo esc_attr__('Example: My tag, another tag', 'newsletter-for-wp'); ?>" value="<?php echo esc_attr($opts['remove_subscriber_tags']); ?>" />
            <p class="description">
                <?php echo esc_html__('The listed tags will be removed from all subscribers updated by this form.', 'newsletter-for-wp'); ?>
                <?php echo esc_html__('Separate multiple values with a comma.', 'newsletter-for-wp'); ?>
            </p>

        </td>
    </tr>

    <?php do_action('nl4wp_admin_form_after_newsletter_settings_rows', $opts, $form); ?>
</table>

<div class="nl4wp-margin-m"></div>

<h3><?php echo esc_html__('Form behaviour', 'newsletter-for-wp'); ?></h3>

<table class="form-table" style="table-layout: fixed;">

    <?php do_action('nl4wp_admin_form_before_behaviour_settings_rows', $opts, $form); ?>

    <tr valign="top">
        <th scope="row"><?php echo esc_html__('Hide form after a successful sign-up?', 'newsletter-for-wp'); ?></th>
        <td class="nowrap">
            <label>
                <input type="radio" name="nl4wp_form[settings][hide_after_success]" value="1" <?php checked($opts['hide_after_success'], 1); ?> />&rlm;
                <?php echo esc_html__('Yes', 'newsletter-for-wp'); ?>
            </label> &nbsp;
            <label>
                <input type="radio" name="nl4wp_form[settings][hide_after_success]" value="0" <?php checked($opts['hide_after_success'], 0); ?> />&rlm;
                <?php echo esc_html__('No', 'newsletter-for-wp'); ?>
            </label>
            <p class="description">
                <?php echo esc_html__('Select "yes" to hide the form fields after a successful sign-up.', 'newsletter-for-wp'); ?>
            </p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><?php echo esc_html__('Enable email domain typo checker?', 'newsletter-for-wp'); ?></th>
        <td class="nowrap">
            <label>
                <input type="radio" name="nl4wp_form[settings][email_typo_check]" value="1" <?php checked($opts['email_typo_check'], 1); ?> />&rlm;
                <?php echo esc_html__('Yes', 'newsletter-for-wp'); ?>
            </label> &nbsp;
            <label>
                <input type="radio" name="nl4wp_form[settings][email_typo_check]" value="0" <?php checked($opts['email_typo_check'], 0); ?> />&rlm;
                <?php echo esc_html__('No', 'newsletter-for-wp'); ?>
            </label>
            <p class="description">
                <?php echo esc_html__('When enabled, the form will suggest corrections for common email domain typos (e.g., "gmial.com" → "gmail.com").', 'newsletter-for-wp'); ?>
            </p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="nl4wp_form_redirect"><?php echo esc_html__('Redirect to URL after successful sign-ups', 'newsletter-for-wp'); ?></label></th>
        <td>
            <?php // translators: %s is an example URL, e.g. https://example.com/thank-you/. ?>
            <input type="text" class="widefat" name="nl4wp_form[settings][redirect]" id="nl4wp_form_redirect" placeholder="<?php printf(esc_attr__('Example: %s', 'newsletter-for-wp'), esc_attr(site_url('/thank-you/'))); ?>" value="<?php echo esc_attr($opts['redirect']); ?>" />
            <p class="description">
                <?php echo wp_kses(__('Leave empty or enter <code>0</code> for no redirect. Otherwise, use complete (absolute) URLs, including <code>http://</code>.', 'newsletter-for-wp'), [ 'code' => [] ]); ?>
            </p>
            <p class="description">
                <?php echo esc_html__('Your "subscribed" message will not show when redirecting to another page, so make sure to let your visitors know they were successfully subscribed.', 'newsletter-for-wp'); ?>
            </p>

        </td>
    </tr>

    <?php do_action('nl4wp_admin_form_after_behaviour_settings_rows', $opts, $form); ?>

</table>

<?php submit_button(); ?>
