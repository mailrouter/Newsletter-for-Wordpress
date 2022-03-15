<?php

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
     * Constructor
     */
    public function __construct()
    {
        $this->output_manager = new NL4WP_Form_Output_Manager();
        $this->tags = new NL4WP_Form_Tags();
        $this->listener = new NL4WP_Form_Listener();
        $this->previewer = new NL4WP_Form_Previewer();
    }

    /**
     * Hook!
     */
    public function add_hooks()
    {
        add_action('init', array( $this, 'initialize' ));
        add_action('wp', array( $this, 'init_asset_manager' ), 90);
        add_action('widgets_init', array( $this, 'register_widget' ));

        $this->listener->add_hooks();
        $this->output_manager->add_hooks();
        $this->tags->add_hooks();
        $this->previewer->add_hooks();
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

        register_block_type( 'newsletter-for-wp/form', array(
            'render_callback' => array($this->output_manager, 'shortcode'),
        ));
    }

    /**
     * Register post type "nl4wp-form"
     */
    private function register_post_type()
    {
        // register post type
        register_post_type(
            'nl4wp-form',
            array(
                'labels' => array(
                    'name' => 'Newsletter Sign-up Forms',
                    'singular_name' => 'Sign-up Form',
                ),
                'public' => false
            )
        );
    }

    /**
     * Initialise asset manager
     *
     * @hooked `template_redirect`
     */
    public function init_asset_manager()
    {
        $assets = new NL4WP_Form_Asset_Manager();
        $assets->hook();
    }

    /**
     * Register our Form widget
     */
    public function register_widget()
    {
        register_widget('NL4WP_Form_Widget');
    }

    /**
     * @param       $form_id
     * @param array $config
     * @param bool  $echo
     *
     * @return string
     */
    public function output_form($form_id, $config = array(), $echo = true)
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
        return $this->tags->get();
    }
}
