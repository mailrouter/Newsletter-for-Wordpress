<?php

nl4wp_register_integration('ninja-forms', 'NL4WP_Ninja_Forms_Integration', true);

if (class_exists('Ninja_Forms') && method_exists('Ninja_Forms', 'instance')) {
    $ninja_forms = Ninja_Forms::instance();

    if (isset($ninja_forms->fields)) {
        $ninja_forms->fields['nl4wp_optin'] = new NL4WP_Ninja_Forms_Field();
    }

    if (isset($ninja_forms->actions)) {
        $ninja_forms->actions['nl4wp_subscribe'] = new NL4WP_Ninja_Forms_Action();
    }
}
