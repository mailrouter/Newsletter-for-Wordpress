<?php defined('ABSPATH') || exit;

/** @var NL4WP_Form $form */
?>

<h2><?php echo esc_html__('Form Messages', 'newsletter-for-wp'); ?></h2>

<table class="form-table nl4wp-form-messages">

    <?php do_action('nl4wp_admin_form_before_messages_settings_rows', $opts, $form); ?>

    <tr valign="top">
        <th scope="row"><label for="nl4wp_form_subscribed"><?php echo esc_html__('Successfully subscribed', 'newsletter-for-wp'); ?></label></th>
        <td>
            <input type="text" class="widefat" id="nl4wp_form_subscribed" name="nl4wp_form[messages][subscribed]" value="<?php echo esc_attr($form->messages['subscribed']); ?>" />
            <p class="description"><?php echo esc_html__('The text that shows when an email address is successfully subscribed to the selected Newsletter audiences.', 'newsletter-for-wp'); ?></p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="nl4wp_form_invalid_email"><?php echo esc_html__('Invalid email address', 'newsletter-for-wp'); ?></label></th>
        <td>
            <input type="text" class="widefat" id="nl4wp_form_invalid_email" name="nl4wp_form[messages][invalid_email]" value="<?php echo esc_attr($form->messages['invalid_email']); ?>" required />
            <p class="description"><?php echo esc_html__('The text that shows when an invalid email address is given.', 'newsletter-for-wp'); ?></p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="nl4wp_form_required_field_missing"><?php echo esc_html__('Required field missing', 'newsletter-for-wp'); ?></label></th>
        <td>
            <input type="text" class="widefat" id="nl4wp_form_required_field_missing" name="nl4wp_form[messages][required_field_missing]" value="<?php echo esc_attr($form->messages['required_field_missing']); ?>" required />
            <p class="description"><?php echo esc_html__('The text that shows when a required field for the selected Newsletter audiences is missing.', 'newsletter-for-wp'); ?></p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="nl4wp_form_already_subscribed"><?php echo esc_html__('Already subscribed', 'newsletter-for-wp'); ?></label></th>
        <td>
            <input type="text" class="widefat" id="nl4wp_form_already_subscribed" name="nl4wp_form[messages][already_subscribed]" value="<?php echo esc_attr($form->messages['already_subscribed']); ?>" required />
            <p class="description"><?php echo esc_html__('The text that shows when the given email is already subscribed to the selected Newsletter audiences.', 'newsletter-for-wp'); ?></p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="nl4wp_form_error"><?php echo esc_html__('General error', 'newsletter-for-wp'); ?></label></th>
        <td>
            <input type="text" class="widefat" id="nl4wp_form_error" name="nl4wp_form[messages][error]" value="<?php echo esc_attr($form->messages['error']); ?>" required />
            <p class="description"><?php echo esc_html__('The text that shows when a general error occured.', 'newsletter-for-wp'); ?></p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="nl4wp_form_unsubscribed"><?php echo esc_html__('Unsubscribed', 'newsletter-for-wp'); ?></label></th>
        <td>
            <input type="text" class="widefat" id="nl4wp_form_unsubscribed" name="nl4wp_form[messages][unsubscribed]" value="<?php echo esc_attr($form->messages['unsubscribed']); ?>" required />
            <p class="description"><?php echo esc_html__('When using the unsubscribe method, this is the text that shows when the given email address is successfully unsubscribed from the selected Newsletter audiences).', 'newsletter-for-wp'); ?></p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="nl4wp_form_not_subscribed"><?php echo esc_html__('Not subscribed', 'newsletter-for-wp'); ?></label></th>
        <td>
            <input type="text" class="widefat" id="nl4wp_form_not_subscribed" name="nl4wp_form[messages][not_subscribed]" value="<?php echo esc_attr($form->messages['not_subscribed']); ?>" required />
            <p class="description"><?php echo esc_html__('When using the unsubscribe method, this is the text that shows when the given email address is not on the selected Newsletter audiences.', 'newsletter-for-wp'); ?></p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="nl4wp_form_no_lists_selected"><?php echo esc_html__('No Newsletter audiences selected', 'newsletter-for-wp'); ?></label></th>
        <td>
            <input type="text" class="widefat" id="nl4wp_form_no_lists_selected" name="nl4wp_form[messages][no_lists_selected]" value="<?php echo esc_attr($form->messages['no_lists_selected']); ?>" required />
            <p class="description"><?php echo esc_html__('When offering an audience choice, this is the text that shows when no Newsletter audiences were selected.', 'newsletter-for-wp'); ?></p>
        </td>
    </tr>

    <?php
    $config = [
        'element' => 'nl4wp_form[settings][update_existing]',
        'value'   => 1,
    ];
    ?>
    <tr valign="top" data-showif="<?php echo esc_attr(json_encode($config)); ?>">
        <th scope="row"><label for="nl4wp_form_updated"><?php echo esc_html__('Updated', 'newsletter-for-wp'); ?></label></th>
        <td>
            <input type="text" class="widefat" id="nl4wp_form_updated" name="nl4wp_form[messages][updated]" value="<?php echo esc_attr($form->messages['updated']); ?>" />
            <p class="description"><?php echo esc_html__('The text that shows when an existing subscriber is updated.', 'newsletter-for-wp'); ?></p>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><label for="nl4wp_form_message_spam"><?php echo esc_html__('Spam', 'newsletter-for-wp'); ?></label></th>
        <td>
            <input type="text" class="widefat" id="nl4wp_form_message_spam" name="nl4wp_form[messages][spam]" value="<?php echo esc_attr($form->messages['spam']); ?>" />
            <p class="description"><?php echo esc_html__('The text that shows when a submission is marked as spam.', 'newsletter-for-wp'); ?></p>
        </td>
    </tr>

    <?php do_action('nl4wp_admin_form_after_messages_settings_rows', [], $form); ?>

    <tr valign="top">
        <th></th>
        <td>
            <p class="description">
            <?php
                // translators: %s is a list of allowed HTML tags (e.g. <strong><em><a>).
                printf(esc_html__('HTML tags like %s are allowed in the message fields.', 'newsletter-for-wp'), '<code>' . esc_html('<strong><em><a>') . '</code>');
            ?>
            </p>
        </td>
    </tr>

</table>

<?php submit_button(); ?>
