<?php defined('ABSPATH') or exit; ?>
<div id="nl4wp-admin" class="wrap nl4wp-settings">

	<div class="row">

		<!-- Main Content -->
		<div class="main-content col col-6">

			<h1 class="page-title">
				<?php _e("Add new form", 'newsletter-for-wp'); ?>
			</h1>

			<h2 style="display: none;"></h2><?php // fake h2 for admin notices?>

			<div style="max-width: 480px;">

				<!-- Wrap entire page in <form> -->
				<form method="post">

					<input type="hidden" name="_nl4wp_action" value="add_form" />
					<?php wp_nonce_field('add_form', '_nl4wp_nonce'); ?>


					<div class="small-margin">
						<h3>
							<label>
								<?php _e('What is the name of this form?', 'newsletter-for-wp'); ?>
							</label>
						</h3>
						<input type="text" name="nl4wp_form[name]" class="widefat" value="" spellcheck="true" autocomplete="off" placeholder="<?php _e('Enter your form title..', 'newsletter-for-wp'); ?>">
					</div>

					<div class="small-margin">

						<h3>
							<label>
								<?php _e('To which Newsletter lists should this form subscribe?', 'newsletter-for-wp'); ?>
							</label>
						</h3>

						<?php if (! empty($lists)) {
    ?>
						<ul id="nl4wp-lists">
							<?php foreach ($lists as $list) {
        ?>
								<li>
									<label>
										<input type="checkbox" name="nl4wp_form[settings][lists][<?php echo esc_attr($list->id); ?>]" value="<?php echo esc_attr($list->id); ?>" <?php checked($number_of_lists, 1); ?> >
										<?php echo esc_html($list->name); ?>
									</label>
								</li>
							<?php
    } ?>
						</ul>
						<?php
} else {
        ?>
						<p class="nl4wp-notice">
							<?php printf(__('No lists found. Did you <a href="%s">connect with Newsletter</a>?', 'newsletter-for-wp'), admin_url('admin.php?page=newsletter-for-wp')); ?>
						</p>
						<?php
    } ?>

					</div>

					<?php submit_button(__('Add new form', 'newsletter-for-wp')); ?>


				</form><!-- Entire page form wrap -->

			</div>


			<?php  ?>

		</div><!-- / Main content -->

		


	</div>

</div>
