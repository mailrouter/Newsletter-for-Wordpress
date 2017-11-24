<?php 

defined( 'ABSPATH' ) or exit;

if( function_exists( 'nl4wp_refresh_newsletter_lists' ) ) {
	nl4wp_refresh_newsletter_lists();
}

delete_transient( 'nl4wp_newsletter_lists_v3' );
delete_option( 'nl4wp_newsletter_lists_v3_fallback' );

wp_schedule_event( strtotime('tomorrow 3 am'), 'daily', 'nl4wp_refresh_newsletter_lists' );

