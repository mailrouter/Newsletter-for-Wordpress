<?php

defined('ABSPATH') || exit;

wp_clear_scheduled_hook('nl4wp_refresh_newsletter_lists');

$time_string = sprintf('tomorrow %d:%d%d am', wp_rand(1, 6), wp_rand(0, 5), wp_rand(0, 9));
wp_schedule_event(strtotime($time_string), 'daily', 'nl4wp_refresh_newsletter_lists');
