<?php

defined('ABSPATH') || exit;

add_filter('nl4wp_form_data', 'nl4wp_add_name_data', 60);
add_filter('nl4wp_integration_data', 'nl4wp_add_name_data', 60);

add_filter('mctb_data', '_nl4wp_update_groupings_data', PHP_INT_MAX);
add_filter('nl4wp_form_data', '_nl4wp_update_groupings_data', PHP_INT_MAX);
add_filter('nl4wp_integration_data', '_nl4wp_update_groupings_data', PHP_INT_MAX);
add_filter('newsletter_sync_user_data', '_nl4wp_update_groupings_data', PHP_INT_MAX);
add_filter('nl4wp_use_sslverify', '_nl4wp_use_sslverify', 1);
add_filter('wp_privacy_personal_data_exporters', [NL4WP_Personal_Data_Exporter::class, 'add_newsletter_to_privacy_export']);

nl4wp_apply_deprecated_filters('nl4wp_merge_vars', 'nl4wp_form_data');
nl4wp_apply_deprecated_filters('nl4wp_form_merge_vars', 'nl4wp_form_data');
nl4wp_apply_deprecated_filters('nl4wp_integration_merge_vars', 'nl4wp_integration_data');
