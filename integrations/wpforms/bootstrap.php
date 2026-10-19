<?php

defined('ABSPATH') || exit;


nl4wp_register_integration('wpforms', 'NL4WP_WPForms_Integration', true);

add_action('plugins_loaded', function () {
    if (class_exists('WPForms_Field')) {
        new NL4WP_WPForms_Field();
    }
});
