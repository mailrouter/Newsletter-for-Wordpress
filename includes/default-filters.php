<?php

defined( 'ABSPATH' ) or exit;

add_filter( 'nl4wp_form_data', 'nl4wp_add_name_data', 60 );
add_filter( 'nl4wp_integration_data', 'nl4wp_add_name_data', 60 );

add_filter( 'mctb_data', '_nl4wp_update_groupings_data', PHP_INT_MAX - 1 );
add_filter( 'nl4wp_form_data', '_nl4wp_update_groupings_data', PHP_INT_MAX - 1 );
add_filter( 'nl4wp_integration_data', '_nl4wp_update_groupings_data', PHP_INT_MAX - 1 );
add_filter( 'newsletter_sync_user_data', '_nl4wp_update_groupings_data', PHP_INT_MAX - 1 );
add_filter( 'nl4wp_use_sslverify', '_nl4wp_use_sslverify', 1 );
