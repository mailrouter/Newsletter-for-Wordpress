<?php

defined('ABSPATH') || exit;

nl4wp_register_integration('gravity-forms', 'NL4WP_Gravity_Forms_Integration', true);

add_action('plugins_loaded', function () {
    if (class_exists('GF_Fields')) {
        GF_Fields::register(new NL4WP_Gravity_Forms_Field());
    }
});
