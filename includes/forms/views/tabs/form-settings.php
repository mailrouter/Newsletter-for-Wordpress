<h2><?php echo __('Form Settings', 'newsletter-for-wp'); ?></h2>

<div class="medium-margin"></div>

<h3><?php echo __('Newsletter specific settings', 'newsletter-for-wp'); ?></h3>

<table class="form-table" style="table-layout: fixed;">

	<?php
    /** @ignore */
    do_action('nl4wp_admin_form_after_newsletter_settings_rows', $opts, $form);
    ?>

	<tr valign="top">
		<th scope="row" style="width: 250px;"><?php _e('Lists this form subscribes to', 'newsletter-for-wp'); ?></th>
		<?php // loop through lists
        if (empty($lists)) {
            ?><td colspan="2"><?php printf(__('No lists found, <a href="%s">are you connected to Newsletter</a>?', 'newsletter-for-wp'), admin_url('admin.php?page=newsletter-for-wp')); ?></td><?php
        } else {
            ?>
			<td >

				<ul id="nl4wp-lists" style="margin-bottom: 20px; max-height: 300px; overflow-y: auto;">
					<?php foreach ($lists as $list) {
                ?>
						<li>
							<label>
								<input class="nl4wp-list-input" type="checkbox" name="nl4wp_form[settings][lists][]" value="<?php echo esc_attr($list->id); ?>" <?php  checked(in_array($list->id, $opts['lists']), true); ?>> <?php echo esc_html($list->name); ?>
							</label>
						</li>
					<?php
            } ?>
				</ul>
				<p class="help"><?php _e('Select the list(s) to which people who submit this form should be subscribed.', 'newsletter-for-wp'); ?></p>
			</td>
		<?php
        } ?>

	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('Use double opt-in?', 'newsletter-for-wp'); ?></th>
		<td class="nowrap">
			<label>
				<input type="radio"  name="nl4wp_form[settings][double_optin]" value="1" <?php checked($opts['double_optin'], 1); ?> />&rlm;
				<?php _e('Yes'); ?>
			</label> &nbsp;
			<label>
				<input type="radio" name="nl4wp_form[settings][double_optin]" value="0" <?php checked($opts['double_optin'], 0); ?> onclick="return confirm('<?php esc_attr_e('Are you sure you want to disable double opt-in?', 'newsletter-for-wp'); ?>');" />&rlm;
				<?php _e('No'); ?>
			</label>
			<p class="help"><?php _e('We strongly suggest keeping double opt-in enabled. Disabling double opt-in may result in abuse.', 'newsletter-for-wp'); ?></p>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row"><?php _e('Update existing subscribers?', 'newsletter-for-wp'); ?></th>
		<td class="nowrap">
			<label>
				<input type="radio" name="nl4wp_form[settings][update_existing]" value="1" <?php checked($opts['update_existing'], 1); ?> />&rlm;
				<?php _e('Yes'); ?>
			</label> &nbsp;
			<label>
				<input type="radio" name="nl4wp_form[settings][update_existing]" value="0" <?php checked($opts['update_existing'], 0); ?> />&rlm;
				<?php _e('No'); ?>
			</label>
			<p class="help"><?php _e('Select "yes" if you want to update existing subscribers with the data that is sent.', 'newsletter-for-wp'); ?></p>
		</td>
	</tr>

	<?php $config = array( 'element' => 'nl4wp_form[settings][update_existing]', 'value' => 1 ); ?>
	<tr valign="top" data-showif="<?php echo esc_attr(json_encode($config)); ?>">
		<th scope="row"><?php _e('Replace interest groups?', 'newsletter-for-wp'); ?></th>
		<td class="nowrap">
			<label>
				<input type="radio" name="nl4wp_form[settings][replace_interests]" value="1" <?php checked($opts['replace_interests'], 1); ?> />&rlm;
				<?php _e('Yes'); ?>
			</label> &nbsp;
			<label>
				<input type="radio" name="nl4wp_form[settings][replace_interests]" value="0" <?php checked($opts['replace_interests'], 0); ?> />&rlm;
				<?php _e('No'); ?>
			</label>
			<p class="help">
				<?php _e('Select "no" if you want to add the selected interests to any previously selected interests when updating a subscriber.', 'newsletter-for-wp'); ?>
				
			</p>
		</td>
	</tr>

    <tr valign="top">
        <th scope="row"><label for="nl4wp_form_subscriber_tags"><?php _e('Subscriber tags', 'newsletter-for-wp'); ?></label></th>
        <td>
            <input type="text" class="widefat" name="nl4wp_form[settings][subscriber_tags]" id="nl4wp_form_subscriber_tags" placeholder="<?php echo __('Example: My tag, another tag', 'newsletter-for-wp'); ?>" value="<?php echo esc_attr($opts['subscriber_tags']); ?>" />
            <p class="help">
                <?php _e('The listed tags will be applied to all new subscribers added by this form.', 'newsletter-for-wp'); ?>
                <?php _e('Separate multiple values with a comma.', 'newsletter-for-wp'); ?>
            </p>

        </td>
    </tr>

	<?php
    /** @ignore */
    do_action('nl4wp_admin_form_after_newsletter_settings_rows', $opts, $form);
    ?>

</table>

<div class="medium-margin"></div>

<h3><?php _e('Form behaviour', 'newsletter-for-wp'); ?></h3>

<table class="form-table" style="table-layout: fixed;">

	<?php
    /** @ignore */
    do_action('nl4wp_admin_form_before_behaviour_settings_rows', $opts, $form);
    ?>

	<tr valign="top">
		<th scope="row"><?php _e('Hide form after a successful sign-up?', 'newsletter-for-wp'); ?></th>
		<td class="nowrap">
			<label>
				<input type="radio" name="nl4wp_form[settings][hide_after_success]" value="1" <?php checked($opts['hide_after_success'], 1); ?> />&rlm;
				<?php _e('Yes'); ?>
			</label> &nbsp;
			<label>
				<input type="radio" name="nl4wp_form[settings][hide_after_success]" value="0" <?php checked($opts['hide_after_success'], 0); ?> />&rlm;
				<?php _e('No'); ?>
			</label>
			<p class="help">
				<?php _e('Select "yes" to hide the form fields after a successful sign-up.', 'newsletter-for-wp'); ?>
			</p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="nl4wp_form_redirect"><?php _e('Redirect to URL after successful sign-ups', 'newsletter-for-wp'); ?></label></th>
		<td>
			<input type="text" class="widefat" name="nl4wp_form[settings][redirect]" id="nl4wp_form_redirect" placeholder="<?php printf(__('Example: %s', 'newsletter-for-wp'), esc_attr(site_url('/thank-you/'))); ?>" value="<?php echo esc_attr($opts['redirect']); ?>" />
			<p class="help">
				<?php _e('Leave empty or enter <code>0</code> for no redirect. Otherwise, use complete (absolute) URLs, including <code>http://</code>.', 'newsletter-for-wp'); ?>
			</p>
			<p class="help">
				<?php _e('Your "subscribed" message will not show when redirecting to another page, so make sure to let your visitors know they were successfully subscribed.', 'newsletter-for-wp'); ?>
			</p>		
				
		</td>
	</tr>

	<?php
    /** @ignore */
    do_action('nl4wp_admin_form_after_behaviour_settings_rows', $opts, $form);
    ?>

</table>

<?php submit_button(); ?>
