<?php

defined('ABSPATH') || exit;


nl4wp_register_integration('prosopo-procaptcha', 'NL4WP_Procaptcha_Integration');

NL4WP_Procaptcha::get_instance()->set_hooks();
