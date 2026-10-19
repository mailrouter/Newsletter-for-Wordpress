<?php

defined('ABSPATH') || exit;

global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'nl4wp_newsletter_list_%'");
