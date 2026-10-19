<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Delete all NL4WP related options and transients
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name = 'nl4wp' OR option_name LIKE 'nl4wp_%' OR option_name LIKE '_transient_nl4wp_%' OR option_name LIKE '_transient_timeout_nl4wp_%';");

// Delete all NL4WP forms + settings
$wpdb->query("DELETE p, pm FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID WHERE p.post_type = 'nl4wp-form';");
