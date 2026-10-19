<?php defined('ABSPATH') || exit; ?>
<div id="nl4wp-admin" class="wrap nl4wp-settings">
    <div class="nl4wp-row">
        <div class="main-content nl4wp-col">
            <h1 class="nl4wp-page-title">
                <?php echo esc_html__('Add new form', 'newsletter-for-wp'); ?>
            </h1>
            <h2 style="display: none;"></h2><?php // fake h2 for admin notices ?>
            <div style="max-width: 480px;">
                <form method="post">
                    <input type="hidden" name="_nl4wp_action" value="add_form" />
                    <?php wp_nonce_field('_nl4wp_action', '_wpnonce'); ?>
                    <div class="nl4wp-margin-s">
                        <h3>
                            <label>
                                <?php echo esc_html__('What is the name of this form?', 'newsletter-for-wp'); ?>
                            </label>
                        </h3>
                        <input type="text" name="nl4wp_form[name]" class="widefat" value="" spellcheck="true" autocomplete="off" placeholder="<?php echo esc_attr__('Enter your form title..', 'newsletter-for-wp'); ?>">
                    </div>
                    <div class="nl4wp-margin-s">
                        <h3>
                            <label>
                                <?php echo esc_html__('To which Newsletter audience should this form subscribe?', 'newsletter-for-wp'); ?>
                            </label>
                        </h3>

                        <?php
                        if (! empty($lists)) {
                            ?>
                        <ul id="nl4wp-lists">
                            <?php
                            foreach ($lists as $list) {
                                ?>
                                <li>
                                    <label>
                                        <input type="checkbox" name="nl4wp_form[settings][lists][<?php echo esc_attr($list->id); ?>]" value="<?php echo esc_attr($list->id); ?>" <?php checked($number_of_lists, 1); ?> >
                                        <?php echo esc_html($list->name); ?>
                                    </label>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                            <?php
                        } else {
                            ?>
                        <p class="nl4wp-notice">
                            <?php
                            // translators: %s is the URL to the Newsletter for WordPress settings page.
                            printf(wp_kses(__('No Newsletter audiences found. Did you <a href="%s">connect with Newsletter</a>?', 'newsletter-for-wp'), [ 'a' => [ 'href' => [] ] ]), esc_url(admin_url('admin.php?page=newsletter-for-wp')));
                            ?>
                        </p>
                            <?php
                        }
                        ?>
                    </div>
                    <?php submit_button(esc_html__('Add new form', 'newsletter-for-wp')); ?>
                </form>
            </div>
            <?php  ?>
        </div>
    </div>
</div>
