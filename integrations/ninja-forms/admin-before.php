<?php defined('ABSPATH') || exit; ?>

<p>
    <?php
    echo wp_kses(
        sprintf(
            // translators: %s is the URL to the Ninja Forms admin page.
            __('To integrate with Ninja Forms, add the "Newsletter" action to <a href="%s">one of your Ninja Forms forms</a>.', 'newsletter-for-wp'),
            esc_url(admin_url('admin.php?page=ninja-forms'))
        ),
        [ 'a' => [ 'href' => [] ] ]
    );
    ?>
</p>
