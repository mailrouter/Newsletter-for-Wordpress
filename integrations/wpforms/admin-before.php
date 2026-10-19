<?php defined('ABSPATH') || exit; ?>

<p>
    <?php
    echo wp_kses(
        sprintf(
            // translators: %s is the URL to the WPForms overview page.
            __('Use this integration by adding the "Newsletter" field to <a href="%s">your WPForms forms</a>.', 'newsletter-for-wp'),
            esc_url(admin_url('admin.php?page=wpforms-overview'))
        ),
        [ 'a' => [ 'href' => [] ] ]
    );
    ?>
</p>
