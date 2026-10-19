<?php
defined('ABSPATH') || exit;
?>
<div id="nl4wp-admin" class="wrap nl4wp-settings">

    <p class="nl4wp-breadcrumbs">
        <span class="prefix"><?php echo esc_html__('You are here: ', 'newsletter-for-wp'); ?></span>
        <span class="current-crumb"><strong>Newsletter for WordPress</strong></span>
    </p>

    <div class="nl4wp-row">
        <div class="main-content nl4wp-col">

            <h1 class="nl4wp-page-title">
                Newsletter for WordPress: <?php echo esc_html__('API Settings', 'newsletter-for-wp'); ?>
            </h1>

            <h2 style="display: none;"></h2>
            <?php
            settings_errors();
            $this->messages->show();
            ?>

            <form action="<?php echo esc_attr(admin_url('options.php')); ?>" method="post">
                <?php settings_fields('nl4wp_settings'); ?>

                <table class="form-table">

                    <tr valign="top">
                        <th scope="row">
                            <?php echo esc_html__('Status', 'newsletter-for-wp'); ?>
                        </th>
                        <td>
                            <?php
                            if ($connected) {
                                ?>
                                <span class="nl4wp-status positive"><?php echo esc_html__('CONNECTED', 'newsletter-for-wp'); ?></span>
                                <?php
                            } else {
                                ?>
                                <span class="nl4wp-status neutral"><?php echo esc_html__('NOT CONNECTED', 'newsletter-for-wp'); ?></span>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>


                    <tr valign="top">
                        <th scope="row"><label for="newsletter_api_key"><?php echo esc_html__('API Key', 'newsletter-for-wp'); ?></label></th>
                        <td>
                            <textarea class="widefat" placeholder="<?php echo esc_attr__('Your Newsletter API key', 'newsletter-for-wp'); ?>" id="newsletter_api_key" name="nl4wp[api_key]" <?php echo defined('NL4WP_API_KEY') ? 'readonly="readonly"' : ''; ?> ><?php echo esc_textarea( $obfuscated_api_key ); ?></textarea>
                            <p class="description">
                                <?php echo esc_html__('The API key for connecting with your Newsletter account.', 'newsletter-for-wp'); ?>
                                
                            </p>

                            <?php
                            if (defined('NL4WP_API_KEY')) {
                                echo '<p class="description">', wp_kses(__('You defined your Newsletter API key using the <code>NL4WP_API_KEY</code> constant.', 'newsletter-for-wp'), [ 'code' => [] ]), '</p>';
                            }
                            ?>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <?php
            do_action('nl4wp_admin_after_general_settings');

            if (! empty($opts['api_key'])) {
                echo '<hr />';
                include __DIR__ . '/parts/lists-overview.php';
            }

            

            ?>
        </div>
    </div>
</div>

