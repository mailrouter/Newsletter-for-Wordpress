<?php defined('ABSPATH') or exit;
/** @var NL4WP_Forms_List_Table $table */
?>
<div id="nl4wp-admin" class="wrap nl4wp-settings">

	<p class="breadcrumbs">
		<span class="prefix"><?php echo __('You are here: ', 'newsletter-for-wp'); ?></span>
		<a href="<?php echo admin_url('admin.php?page=newsletter-for-wp'); ?>">Newsletter for WordPress</a> &rsaquo;
		<span class="current-crumb"><strong><?php _e('Forms', 'newsletter-for-wp'); ?></strong></span>
	</p>

	<h1 class="page-title">Newsletter for WordPress: <?php _e('Forms', 'newsletter-for-wp'); ?>
		<a href="<?php echo nl4wp_get_add_form_url(); ?>" class="page-title-action">
			<span class="dashicons dashicons-plus-alt" style=""></span>
			<?php _e('Add new form', 'newsletter-for-wp'); ?>
		</a>
	</h1>

	<h2 style="display: none;"></h2>
	<?php settings_errors(); ?>

	<?php 
		$table->prepare_items();
		$table->views();
	?>

	<form method="get" action="<?php echo admin_url('admin.php'); ?>">
		<input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>" />
		<?php if (! empty($_GET['post_status'])) { ?>
			<input type="hidden" name="post_status" value="<?php echo esc_attr($_GET['post_status']); ?>" />
		<?php } ?>
	</form>

	<form method="post">
		<?php $table->display(); ?>
	</form>
</div>

