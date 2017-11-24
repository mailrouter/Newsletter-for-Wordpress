<?php

defined( 'ABSPATH' ) or exit;

nl4wp_register_integration( 'gravity-forms', 'NL4WP_Gravity_Forms_Integration', true );

if ( class_exists( 'GF_Fields' ) ) {
    GF_Fields::register( new NL4WP_Gravity_Forms_Field() );
}
