<?php

defined('ABSPATH') || exit;

/** @param NL4WP_Integration_Fixture $integration */
function _nl4wp_integrations_table_row($integration)
{
    ?>
    <tr
    <?php
    if (! $integration->is_installed()) {
        echo ' style="opacity: 0.6;"';
    }
    ?>
        >

        <td>
            <?php
            if ($integration->is_installed()) {
                $href  = add_query_arg([ 'integration' => $integration->slug ]);
                $title = esc_attr__('Configure this integration', 'newsletter-for-wp');
                printf('<strong><a href="%s" title="%s">%s</a></strong>', esc_url($href), esc_attr($title), esc_html($integration->name));
            } else {
                echo esc_html($integration->name);
            }
            ?>
        </td>
        <td class="desc">
            <?php
            echo esc_html($integration->description);
            ?>
        </td>
        <td>
            <?php
            if ($integration->enabled && $integration->is_installed()) {
                echo '<span class="nl4wp-status positive">', esc_html__('Active', 'newsletter-for-wp'), '</span>';
            } elseif ($integration->is_installed()) {
                echo '<span class="nl4wp-status neutral">', esc_html__('Inactive', 'newsletter-for-wp'), '</span>';
            } else {
                echo '<span>', esc_html__('Not installed', 'newsletter-for-wp'), '</span>';
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
            <th><?php echo esc_html__('Name', 'newsletter-for-wp'); ?></th>
            <th><?php echo esc_html__('Description', 'newsletter-for-wp'); ?></th>
            <th><?php echo esc_html__('Status', 'newsletter-for-wp'); ?></th>
        </tr>
        </thead>
        <tbody>

        <?php
        // active & enabled integrations first
        foreach ($integrations as $integration) {
            if ($integration->is_installed() && $integration->enabled) {
                _nl4wp_integrations_table_row($integration);
            }
        }

        // active & disabled integrations next
        foreach ($integrations as $integration) {
            if ($integration->is_installed() && ! $integration->enabled) {
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
    </table>
    <?php
}
?>
<div id="nl4wp-admin" class="wrap nl4wp-settings">
    <p class="nl4wp-breadcrumbs">
        <span class="prefix"><?php echo esc_html__('You are here: ', 'newsletter-for-wp'); ?></span>
        <a href="<?php echo esc_url(admin_url('admin.php?page=newsletter-for-wp')); ?>">Newsletter for WordPress</a> &rsaquo;
        <span class="current-crumb"><strong><?php echo esc_html__('Integrations', 'newsletter-for-wp'); ?></strong></span>
    </p>

    <div class="nl4wp-row">
        <div class="nl4wp-col nl4wp-col-4">
            <h1 class="nl4wp-page-title">Newsletter for WordPress: <?php echo esc_html__('Integrations', 'newsletter-for-wp'); ?></h1>

            <h2 style="display: none;"></h2>
            <?php settings_errors(); ?>

            <p>
                <?php echo esc_html__('The table below shows all available integrations.', 'newsletter-for-wp'); ?>
                <?php echo esc_html__('Click on the name of an integration to edit all settings specific to that integration.', 'newsletter-for-wp'); ?>
            </p>

            <form action="<?php echo esc_url(admin_url('options.php')); ?>" method="post">
                <?php settings_fields('nl4wp_integrations_settings'); ?>
                <h3><?php echo esc_html__('Integrations', 'newsletter-for-wp'); ?></h3>
                <?php _nl4wp_integrations_table($integrations); ?>
                <p><?php echo esc_html__('Greyed out integrations will become available after installing & activating the corresponding plugin.', 'newsletter-for-wp'); ?></p>
            </form>
        </div>
    </div>
</div>
