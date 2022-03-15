<?php

nl4wp_register_integration('wpforms', 'NL4WP_WPForms_Integration', true);

function _nl4wp_wpforms_register_field()
{
    if (! class_exists('WPForms_Field')) {
        return;
    }

    new NL4WP_WPForms_Field();
}

add_action('init', '_nl4wp_wpforms_register_field');
