<?php

defined('ABSPATH') || exit;


/**
 * This class takes care of all form related functionality
 *
 * Do not interact with this class directly, use `nl4wp_form` functions tagged with @access public instead.
 *
 * @class NL4WP_Form_Manager
 * @ignore
 * @access private
*/
class NL4WP_Form_Manager
{
    /**
     * @var NL4WP_Form_Output_Manager
     */
    protected $output_manager;

    /**
     * @var NL4WP_Form_Listener
     */
    protected $listener;

    /**
     * @var NL4WP_Form_Tags
     */
    protected $tags;

    /**
    * @var NL4WP_Form_Previewer
    */
    protected $previewer;

    /**
     * @var NL4WP_Form_Asset_Manager
     */
    protected $assets;

    /**
     * @var NL4WP_Form_AMP
     */
    protected $amp_compatibility;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->output_manager    = new NL4WP_Form_Output_Manager();
        $this->tags              = new NL4WP_Form_Tags();
        $this->listener          = new NL4WP_Form_Listener();
        $this->previewer         = new NL4WP_Form_Previewer();
        $this->assets            = new NL4WP_Form_Asset_Manager();
        $this->amp_compatibility = new NL4WP_Form_AMP();
    }

    /**
     * Hook!
     */
    public function add_hooks()
    {
        add_action('init', [ $this, 'initialize' ]);
        add_action('widgets_init', [ $this, 'register_widget' ]);
        add_action('rest_api_init', [ $this, 'register_endpoint' ]);

        $this->listener->add_hooks();
        $this->output_manager->add_hooks();
        $this->assets->add_hooks();
        $this->tags->add_hooks();
        $this->previewer->add_hooks();
        $this->amp_compatibility->add_hooks();
    }

    /**
     * Initialize
     */
    public function initialize()
    {
        $this->register_post_type();
        $this->register_block_type();
    }

    private function register_block_type()
    {
        // Bail if register_block_type does not exist (available since WP 5.0)
        if (! function_exists('register_block_type')) {
            return;
        }

        register_block_type(
            'newsletter-for-wp/form',
            [
                'render_callback' => [ $this->output_manager, 'shortcode' ],
            ]
        );
    }

    /**
     * Register post type "nl4wp-form"
     */
    private function register_post_type()
    {
        // register post type
        register_post_type(
            'nl4wp-form',
            [
                'labels' => [
                    'name'          => 'Newsletter Sign-up Forms',
                    'singular_name' => 'Sign-up Form',
                ],
                'public' => false,
                'capability_type' => 'nl4wp-form',
                'map_meta_cap' => true,
            ]
        );
    }

    /**
     * Register our Form widget
     */
    public function register_widget()
    {
        register_widget('NL4WP_Form_Widget');
    }

    /**
     * Register an API endpoint for handling a form.
     */
    public function register_endpoint()
    {
        register_rest_route(
            'nl4wp/v1',
            '/form',
            [
                'methods'  => 'POST',
                'permission_callback' => '__return_true',
                'callback' => [ $this, 'handle_endpoint' ],
            ]
        );
    }

    /**
     * Process requests to the form endpoint.
     *
     * A listener checks every request for a form submit, so we just need to fetch the listener and get its status.
     */
    public function handle_endpoint()
    {
        $form = nl4wp_get_submitted_form();
        if (! $form instanceof NL4WP_Form) {
            return new WP_Error(
                'not_found',
                esc_html__('Resource does not exist.', 'newsletter-for-wp'),
                [
                    'status' => 404,
                ]
            );
        }

        if ($form->has_errors()) {
            $message_key = $form->errors[0];
            $message     = $form->get_message($message_key);
            return new WP_Error(
                $message_key,
                $message,
                [
                'status' => 400,
                ]
            );
        }

        return new WP_REST_Response(true, 200);
    }

    /**
     * @param       $form_id
     * @param array $config
     * @param bool  $echo
     *
     * @return string
     */
    public function output_form($form_id, $config = [], $echo = true)
    {
        return $this->output_manager->output_form($form_id, $config, $echo);
    }

    /**
     * Gets the currently submitted form
     *
     * @return NL4WP_Form|null
     */
    public function get_submitted_form()
    {
        if ($this->listener->submitted_form instanceof NL4WP_Form) {
            return $this->listener->submitted_form;
        }

        return null;
    }

    /**
     * Return all tags
     *
     * @return array
     */
    public function get_tags()
    {
        return $this->tags->all();
    }
}
