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
class NL4WP_Form_Manager {

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
	 * Constructor
	 */
	public function __construct() {
		$this->output_manager = new NL4WP_Form_Output_Manager();
		$this->tags = new NL4WP_Form_Tags();
	}

	/**
	 * Hook!
	 */
	public function add_hooks() {
		add_action( 'init', array( $this, 'initialize' ) );

		// forms
		add_action( 'template_redirect', array( $this, 'init_asset_manager' ), 1 );
		add_action( 'template_redirect', array( 'NL4WP_Form_Previewer', 'init' ) );

		// widget
		add_action( 'widgets_init', array( $this, 'register_widget' ) );

		$this->output_manager->add_hooks();
		$this->tags->add_hooks();
	}

	/**
	 * Initialize
	 */
	public function initialize() {
		$this->register_post_type();
		$this->init_form_listener();
	}


	/**
	 * Register post type "nl4wp-form"
	 */
	public function register_post_type() {

		// register post type
		register_post_type( 'nl4wp-form', array(
				'labels' => array(
					'name' => 'Newsletter Sign-up Forms',
					'singular_name' => 'Sign-up Form',
				),
				'public' => false
			)
		);
	}

	/**
	 * Initialise the form listener
	 *
	 * @hooked `init`
	 */
	public function init_form_listener() {
		$request = $this->get_request();
		$this->listener = new NL4WP_Form_Listener();
		$this->listener->listen( $request );
	}

	/**
	 * Initialise asset manager
	 *
	 * @hooked `template_redirect`
	 */
	public function init_asset_manager() {
		$assets = new NL4WP_Form_Asset_Manager();
		$assets->hook();
	}

	/**
	 * Register our Form widget
	 */
	public function register_widget() {
		register_widget( 'NL4WP_Form_Widget' );
	}

	/**
	 * @param       $form_id
	 * @param array $config
	 * @param bool  $echo
	 *
	 * @return string
	 */
	public function output_form(  $form_id, $config = array(), $echo = true ) {
		return $this->output_manager->output_form( $form_id, $config, $echo );
	}

	/**
	 * Gets the currently submitted form
	 *
	 * @return NL4WP_Form|null
	 */
	public function get_submitted_form() {
		if( $this->listener->submitted_form instanceof NL4WP_Form ) {
			return $this->listener->submitted_form;
		}

		return null;
	}

	/**
	 * Return all tags
	 *
	 * @return array
	 */
	public function get_tags() {
		return $this->tags->get();
	}

	/**
	 * @return NL4WP_Request
	 */
	private function get_request() {
		return nl4wp('request');
	}
}
