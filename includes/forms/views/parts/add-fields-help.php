<?php defined('ABSPATH') || exit; ?>

<div class="nl4wp-admin">
    <h2><?php echo esc_html__('Add more fields', 'newsletter-for-wp'); ?></h2>

    <div>

        <p>
            <?php echo esc_html__('To add more fields to your form, you will need to create those fields in Newsletter first.', 'newsletter-for-wp'); ?>
        </p>

        <p><strong><?php echo esc_html__("Here's how:", 'newsletter-for-wp'); ?></strong></p>

        <ol>
            <li>
                <p>
                    <?php echo esc_html__('Log in to your Newsletter account.', 'newsletter-for-wp'); ?>
                </p>
            </li>
            <li>
                <p>
                    <?php echo esc_html__('Add list fields to any of your selected lists.', 'newsletter-for-wp'); ?>
                    
                </p>
                
            </li>
            <li>
                <p>
                    <?php echo esc_html__('Click the following button to have Newsletter for WordPress pick up on your changes.', 'newsletter-for-wp'); ?>
                </p>

                <p>
                    <a class="button button-primary" href="
                    <?php
                    echo esc_attr(
                        add_query_arg(
                            [
                            '_nl4wp_action' => 'empty_lists_cache',
                            '_wpnonce' => wp_create_nonce('_nl4wp_action'),
                            ]
                        )
                    );
                    ?>
                    ">
                        <?php echo esc_html__('Renew Newsletter audiences', 'newsletter-for-wp'); ?>
                    </a>
                </p>
            </li>
        </ol>


    </div>
</div>
