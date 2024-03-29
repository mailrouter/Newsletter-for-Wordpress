<?php

defined('ABSPATH') or exit;

/**
 * Class NL4WP_MemberPress_Integration
 *
 * @ignore
 */
class NL4WP_MemberPress_Integration extends NL4WP_Integration
{

    /**
     * @var string
     */
    public $name = "MemberPress";

    /**
     * @var string
     */
    public $description = "Subscribes people from MemberPress register forms.";


    /**
     * Add hooks
     */
    public function add_hooks()
    {
        if (! $this->options['implicit']) {
            add_action('mepr_checkout_before_submit', array( $this, 'output_checkbox' ));
        }

        add_action('mepr_signup', array( $this, 'subscribe_from_memberpress' ), 5);
    }



    /**
     * Subscribe from MemberPress sign-up forms.
     *
     * @param MeprTransaction $txn
     * @return bool
     */
    public function subscribe_from_memberpress($txn)
    {

        // Is this integration triggered? (checkbox checked or implicit)
        if (! $this->triggered()) {
            return false;
        }

        $user = get_userdata($txn->user_id);

        $data = array(
            'EMAIL' => $user->user_email,
            'FNAME' => $user->first_name,
            'LNAME' => $user->last_name
        );

        // subscribe using email and name
        return $this->subscribe($data, $txn->id);
    }

    /**
     * @return bool
     */
    public function is_installed()
    {
        return defined('MEPR_VERSION');
    }
}
