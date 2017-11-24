<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class NL4WP_Ninja_Forms_Field
 */
class NL4WP_Ninja_Forms_Field extends NF_Fields_Checkbox
{
    protected $_name = 'nl4wp_optin';

    protected $_nicename = 'Newsletter';

    protected $_section = 'misc';

    public function __construct() {
        parent::__construct();

        $this->_nicename = __( 'Newsletter opt-in', 'newsletter-for-wp' );
    }
}
