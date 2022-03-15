<?php defined('ABSPATH') or exit;

/** @var NL4WP_Form $form */
?>

<h2><?php _e('Form Messages', 'newsletter-for-wp'); ?></h2>

<table class="form-table nl4wp-form-messages">

	<?php
    /** @ignore */
    do_action('nl4wp_admin_form_before_messages_settings_rows', $opts, $form);
    ?>

	<tr valign="top">
		<th scope="row"><label for="nl4wp_form_subscribed"><?php _e('Successfully subscribed', 'newsletter-for-wp'); ?></label></th>
		<td>
			<input type="text" class="widefat" id="nl4wp_form_subscribed" name="nl4wp_form[messages][subscribed]" value="<?php echo esc_attr($form->messages['subscribed']); ?>" required />
			<p class="help"><?php _e('The text that shows when an email address is successfully subscribed to the selected list(s).', 'newsletter-for-wp'); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="nl4wp_form_invalid_email"><?php _e('Invalid email address', 'newsletter-for-wp'); ?></label></th>
		<td>
			<input type="text" class="widefat" id="nl4wp_form_invalid_email" name="nl4wp_form[messages][invalid_email]" value="<?php echo esc_attr($form->messages['invalid_email']); ?>" required />
			<p class="help"><?php _e('The text that shows when an invalid email address is given.', 'newsletter-for-wp'); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="nl4wp_form_required_field_missing"><?php _e('Required field missing', 'newsletter-for-wp'); ?></label></th>
		<td>
			<input type="text" class="widefat" id="nl4wp_form_required_field_missing" name="nl4wp_form[messages][required_field_missing]" value="<?php echo esc_attr($form->messages['required_field_missing']); ?>" required />
			<p class="help"><?php _e('The text that shows when a required field for the selected list(s) is missing.', 'newsletter-for-wp'); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="nl4wp_form_already_subscribed"><?php _e('Already subscribed', 'newsletter-for-wp'); ?></label></th>
		<td>
			<input type="text" class="widefat" id="nl4wp_form_already_subscribed" name="nl4wp_form[messages][already_subscribed]" value="<?php echo esc_attr($form->messages['already_subscribed']); ?>" required />
			<p class="help"><?php _e('The text that shows when the given email is already subscribed to the selected list(s).', 'newsletter-for-wp'); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="nl4wp_form_error"><?php _e('General error', 'newsletter-for-wp'); ?></label></th>
		<td>
			<input type="text" class="widefat" id="nl4wp_form_error" name="nl4wp_form[messages][error]" value="<?php echo esc_attr($form->messages['error']); ?>" required />
			<p class="help"><?php _e('The text that shows when a general error occured.', 'newsletter-for-wp'); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="nl4wp_form_unsubscribed"><?php _e('Unsubscribed', 'newsletter-for-wp'); ?></label></th>
		<td>
			<input type="text" class="widefat" id="nl4wp_form_unsubscribed" name="nl4wp_form[messages][unsubscribed]" value="<?php echo esc_attr($form->messages['unsubscribed']); ?>" required />
			<p class="help"><?php _e('When using the unsubscribe method, this is the text that shows when the given email address is successfully unsubscribed from the selected list(s).', 'newsletter-for-wp'); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="nl4wp_form_not_subscribed"><?php _e('Not subscribed', 'newsletter-for-wp'); ?></label></th>
		<td>
			<input type="text" class="widefat" id="nl4wp_form_not_subscribed" name="nl4wp_form[messages][not_subscribed]" value="<?php echo esc_attr($form->messages['not_subscribed']); ?>" required />
			<p class="help"><?php _e('When using the unsubscribe method, this is the text that shows when the given email address is not on the selected list(s).', 'newsletter-for-wp'); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="nl4wp_form_no_lists_selected"><?php _e('No list selected', 'newsletter-for-wp'); ?></label></th>
		<td>
			<input type="text" class="widefat" id="nl4wp_form_no_lists_selected" name="nl4wp_form[messages][no_lists_selected]" value="<?php echo esc_attr($form->messages[ 'no_lists_selected']); ?>" required />
			<p class="help"><?php _e('When offering a list choice, this is the text that shows when no lists were selected.', 'newsletter-for-wp'); ?></p>
		</td>
	</tr>

	<?php $config = array( 'element' => 'nl4wp_form[settings][update_existing]', 'value' => 1 ); ?>
	<tr valign="top" data-showif="<?php echo esc_attr(json_encode($config)); ?>">
		<th scope="row"><label for="nl4wp_form_updated"><?php _e('Updated', 'newsletter-for-wp'); ?></label></th>
		<td>
			<input type="text" class="widefat" id="nl4wp_form_updated" name="nl4wp_form[messages][updated]" value="<?php echo esc_attr($form->messages['updated']); ?>" />
			<p class="help"><?php _e('The text that shows when an existing subscriber is updated.', 'newsletter-for-wp'); ?></p>
		</td>
	</tr>

	<?php
    /** @ignore */
    do_action('nl4wp_admin_form_after_messages_settings_rows', array(), $form);
    ?>

	<tr valign="top">
		<th></th>
		<td>
			<p class="help"><?php printf(__('HTML tags like %s are allowed in the message fields.', 'newsletter-for-wp'), '<code>' . esc_html('<strong><em><a>') . '</code>'); ?></p>
		</td>
	</tr>

</table>

<?php submit_button(); ?>
