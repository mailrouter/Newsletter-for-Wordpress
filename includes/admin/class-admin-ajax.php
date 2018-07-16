<?php

class NL4WP_Admin_Ajax {

    /**
     * @var NL4WP_Admin_Tools
     */
    protected $tools;

    /**
     * NL4WP_Admin_Ajax constructor.
     *
     * @param NL4WP_Admin_Tools $tools
     */
    public function __construct( NL4WP_Admin_Tools $tools )
    {
        $this->tools = $tools;
    }

    /**
     * Hook AJAX actions
     */
    public function add_hooks() {
        add_action( 'wp_ajax_nl4wp_renew_newsletter_lists', array( $this, 'refresh_newsletter_lists' ) );
    }

    /**
     * Empty lists cache & fetch lists again.
     */
	public function refresh_newsletter_lists() {
        if( ! $this->tools->is_user_authorized() ) {
            wp_send_json(false);
        }

        $newsletter = new NL4WP_Newsletter();
        $success = $newsletter->fetch_lists();
        wp_send_json( $success );
    }


}