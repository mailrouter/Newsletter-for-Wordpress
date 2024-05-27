<?php defined('ABSPATH') or exit; ?>

<div class="nl4wp-admin">
	<h2><?php _e('Add more fields', 'newsletter-for-wp'); ?></h2>

	<div class="help-text">

		<p>
			<?php echo __('To add more fields to your form, you will need to create those fields in Newsletter first.', 'newsletter-for-wp'); ?>
		</p>

		<p><strong><?php echo __("Here's how:", 'newsletter-for-wp'); ?></strong></p>

		<ol>
			<li>
				<p>
					<?php echo __('Log in to your Newsletter account.', 'newsletter-for-wp'); ?>
				</p>
			</li>
			<li>
				<p>
					<?php echo __('Add list fields to any of your selected lists.', 'newsletter-for-wp'); ?>
					
				</p>
				
			</li>
			<li>
				<p>
					<?php echo __('Click the following button to have Newsletter for WordPress pick up on your changes.', 'newsletter-for-wp'); ?>
				</p>

				<p>
					<a class="button button-primary" href="<?php echo esc_attr(add_query_arg(array( '_nl4wp_action' => 'empty_lists_cache' ))); ?>">
						<?php _e('Renew Newsletter lists', 'newsletter-for-wp'); ?>
					</a>
				</p>
			</li>
		</ol>


	</div>
</div>