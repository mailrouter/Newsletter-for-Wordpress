<?php

defined('ABSPATH') || exit;


nl4wp_register_integration('ninja-forms', 'NL4WP_Ninja_Forms_Integration', true);

add_filter('ninja_forms_register_fields', function ($fields) {
    if (class_exists(NF_Abstracts_Input::class)) {
        $fields['nl4wp_optin'] = new NL4WP_Ninja_Forms_Field();
    }
    return $fields;
});

add_filter('ninja_forms_register_actions', function ($actions) {
    if (class_exists(NF_Abstracts_Action::class)) {
        $actions['nl4wp_subscribe'] = new NL4WP_Ninja_Forms_Action();
    }
    return $actions;
});
