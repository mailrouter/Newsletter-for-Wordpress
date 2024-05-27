<?php defined('ABSPATH') or exit;
/** @var NL4WP_Integration_Fixture[] $enabled_integrations */
/** @var NL4WP_Integration_Fixture[] $available_integrations */
/** @var NL4WP_Integration $integration */
function _nl4wp_integrations_table_row($integration) {
    ?>
    <tr style="<?php if (! $integration->is_installed()) {
        echo 'opacity: 0.4;';
    } ?>">

        <!-- Integration Name -->
        <td>

            <?php
            if ($integration->is_installed()) {
                printf('<strong><a href="%s" title="%s">%s</a></strong>', esc_attr(add_query_arg(array( 'integration' => $integration->slug ))), __('Configure this integration', 'newsletter-for-wp'), $integration->name);
            } else {
                echo $integration->name;
            } ?>


        </td>
        <td class="desc">
            <?php
            _e($integration->description, 'newsletter-for-wp'); ?>
        </td>
        <td>
            <?php
            if ($integration->enabled && $integration->is_installed()) {
                echo '<span class="green">' . __('Active', 'newsletter-for-wp') . '</span>';
            } elseif($integration->is_installed()) {
                echo '<span class="neutral">' . __('Inactive', 'newsletter-for-wp') . '</span>';
            } else {
                echo '<span class="red">' . __('Not installed', 'newsletter-for-wp') . '</span>';
            }
            ?>
        </td>
    </tr>
    <?php
}

/**
 * Render a table with integrations
 *
 * @param $integrations
 * @ignore
 */
function _nl4wp_integrations_table($integrations)
{
    ?>
	<table class="nl4wp-table widefat striped">

		<thead>
		<tr>
			<th><?php _e('Name', 'newsletter-for-wp'); ?></th>
			<th><?php _e('Description', 'newsletter-for-wp'); ?></th>
            <th><?php _e('Status', 'newsletter-for-wp'); ?></th>
		</tr>
		</thead>

		<tbody>

		<?php
        // active & enabled integrations first
        foreach ($integrations as $integration) {
            if ( $integration->is_installed() && $integration->enabled) {
                _nl4wp_integrations_table_row($integration);
            }
        }

        // active & disabled integrations next
        foreach ($integrations as $integration) {
            if ( $integration->is_installed() && ! $integration->enabled) {
                _nl4wp_integrations_table_row($integration);
            }
        }

        // rest
        foreach ($integrations as $integration) {
            if (! $integration->is_installed()) {
                _nl4wp_integrations_table_row($integration);
            }
        }
        ?>

		</tbody>
	</table><?php
}
?>
<div id="nl4wp-admin" class="wrap nl4wp-settings">

	<p class="breadcrumbs">
		<span class="prefix"><?php echo __('You are here: ', 'newsletter-for-wp'); ?></span>
		<a href="<?php echo admin_url('admin.php?page=newsletter-for-wp'); ?>">Newsletter for WordPress</a> &rsaquo;
		<span class="current-crumb"><strong><?php _e('Integrations', 'newsletter-for-wp'); ?></strong></span>
	</p>

	<div class="main-content row">

		<!-- Main Content -->
		<div class="col col-6">

			<h1 class="page-title">Newsletter for WordPress: <?php _e('Integrations', 'newsletter-for-wp'); ?></h1>

			<h2 style="display: none;"></h2>
			<?php settings_errors(); ?>

			<p>
				<?php _e('The table below shows all available integrations.', 'newsletter-for-wp'); ?>
				<?php _e('Click on the name of an integration to edit all settings specific to that integration.', 'newsletter-for-wp'); ?>
			</p>

			<form action="<?php echo admin_url('options.php'); ?>" method="post">

				<?php settings_fields('nl4wp_integrations_settings'); ?>

				<h3><?php _e('Integrations', 'newsletter-for-wp'); ?></h3>
				<?php _nl4wp_integrations_table($integrations); ?>

                <p><?php echo __("Greyed out integrations will become available after installing & activating the corresponding plugin.", 'newsletter-for-wp'); ?></p>


            </form>

		</div>

		

	</div>

</div>
