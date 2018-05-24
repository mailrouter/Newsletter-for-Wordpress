<?php

/**
 * Class NL4WP_Forms_Admin
 *
 * @ignore
 * @access private
 */
class NL4WP_Forms_Admin {

	/**
	 * @var NL4WP_Admin_Messages
	 */
	protected $messages;

	/**
	 * @var NL4WP_Newsletter
	 */
	protected $newsletter;

	/**
	 * @param NL4WP_Admin_Messages $messages
	 * @param NL4WP_Newsletter $newsletter
	 */
	public function __construct( NL4WP_Admin_Messages $messages, NL4WP_Newsletter $newsletter ) {
		$this->messages = $messages;
		$this->newsletter = $newsletter;
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_action( 'register_shortcode_ui', array( $this, 'register_shortcake_ui' ) );
		add_action( 'nl4wp_save_form', array( $this, 'update_form_stylesheets' ) );
		add_action( 'nl4wp_admin_edit_form', array( $this, 'process_save_form' ) );
		add_action( 'nl4wp_admin_add_form', array( $this, 'process_add_form' ) );
		add_filter( 'nl4wp_admin_menu_items', array( $this, 'add_menu_item' ), 5 );
		add_action( 'nl4wp_admin_show_forms_page-edit-form', array( $this, 'show_edit_page' ) );
		add_action( 'nl4wp_admin_show_forms_page-add-form', array( $this, 'show_add_page' ) );
		add_action( 'nl4wp_admin_enqueue_assets', array( $this, 'enqueue_assets' ), 10, 2 );
	}

	/**
	 * @param string $suffix
	 * @param string $page
	 */
	public function enqueue_assets( $suffix, $page = '' ) {

		if( $page !== 'forms' || empty( $_GET['view'] ) || $_GET['view'] !== 'edit-form' ) {
			return;
		}

		wp_register_script( 'nl4wp-forms-admin', NL4WP_PLUGIN_URL . 'assets/js/forms-admin' . $suffix . '.js', array( 'nl4wp-admin' ), NL4WP_VERSION, true );
		wp_enqueue_script( 'nl4wp-forms-admin');
		wp_localize_script( 'nl4wp-forms-admin', 'nl4wp_forms_i18n', array(
			'addToForm'     => __( "Add to form", 'newsletter-for-wp' ),
			'agreeToTerms' => __( "I have read and agree to the terms & conditions", 'newsletter-for-wp' ),
			'agreeToTermsShort' => __( "Agree to terms", 'newsletter-for-wp' ),
			'agreeToTermsLink' => __( 'Link to your terms & conditions page', 'newsletter-for-wp' ),
			'city'          => __( 'City', 'newsletter-for-wp' ),
			'checkboxes'    => __( 'Checkboxes', 'newsletter-for-wp' ),
			'choices'       => __( 'Choices', 'newsletter-for-wp' ),
			'choiceType'    => __( "Choice type", 'newsletter-for-wp' ),
			'chooseField'   => __( "Choose a field to add to the form", 'newsletter-for-wp' ),
			'close'         => __( 'Close', 'newsletter-for-wp' ),
			'country'       => __( 'Country', 'newsletter-for-wp' ),
			'dropdown'      => __( 'Dropdown', 'newsletter-for-wp' ),
            'fieldType'     => __( 'Field type', 'newsletter-for-wp' ),
			'fieldLabel'    => __( "Field label", 'newsletter-for-wp' ),
			'formAction'    => __( 'Form action', 'newsletter-for-wp' ),
			'formActionDescription' => __( 'This field will allow your visitors to choose whether they would like to subscribe or unsubscribe', 'newsletter-for-wp' ),
			'formFields'    => __( 'Form fields', 'newsletter-for-wp' ),
            'forceRequired' => __( 'This field is marked as required in Newsletter.', 'newsletter-for-wp' ),
            'initialValue'  		=> __( "Initial value", 'newsletter-for-wp' ),
            'interestCategories'    => __( 'Interest categories', 'newsletter-for-wp' ),
			'isFieldRequired' => __( "Is this field required?", 'newsletter-for-wp' ),
			'listChoice'    => __( 'List choice', 'newsletter-for-wp' ),
			'listChoiceDescription' => __( 'This field will allow your visitors to choose a list to subscribe to.', 'newsletter-for-wp' ),
            'listFields'    => __( 'List fields', 'newsletter-for-wp' ),
			'min'           => __( 'Min', 'newsletter-for-wp' ),
			'max'           => __( 'Max', 'newsletter-for-wp' ),
			'noAvailableFields' => __( 'No available fields. Did you select a Newsletter list in the form settings?', 'newsletter-for-wp' ),
			'optional' 		=> __( 'Optional', 'newsletter-for-wp' ),
			'placeholder'   => __( 'Placeholder', 'newsletter-for-wp' ),
			'placeholderHelp' => __( "Text to show when field has no value.", 'newsletter-for-wp' ),
			'preselect' 	=> __( 'Preselect', 'newsletter-for-wp' ),
			'remove' 		=> __( 'Remove', 'newsletter-for-wp' ),
			'radioButtons'  => __( 'Radio buttons', 'newsletter-for-wp' ),
			'streetAddress' => __( 'Street Address', 'newsletter-for-wp' ),
			'state'         => __( 'State', 'newsletter-for-wp' ),
			'subscribe'     => __( 'Subscribe', 'newsletter-for-wp' ),
			'submitButton'  => __( 'Submit button', 'newsletter-for-wp' ),
			'wrapInParagraphTags' => __( "Wrap in paragraph tags?", 'newsletter-for-wp' ),
			'value'  		=> __( "Value", 'newsletter-for-wp' ),
			'valueHelp' 	=> __( "Text to prefill this field with.", 'newsletter-for-wp' ),
			'zip'           => __( 'ZIP', 'newsletter-for-wp' ),
		));
	}

	/**
	 * @param $items
	 *
	 * @return mixed
	 */
	public function add_menu_item( $items ) {

		$items['forms'] = array(
			'title' => __( 'Forms', 'newsletter-for-wp' ),
			'text' => __( 'Form', 'newsletter-for-wp' ),
			'slug' => 'forms',
			'callback' => array( $this, 'show_forms_page' ),
			'load_callback' => array( $this, 'redirect_to_form_action' ),
			'position' => 10
		);

		return $items;
	}

	/**
	 * Act on the "add form" form
	 */
	public function process_add_form() {

		check_admin_referer( 'add_form', '_nl4wp_nonce' );

		$form_data = $_POST['nl4wp_form'];
		$form_content = include NL4WP_PLUGIN_DIR . 'config/default-form-content.php';

		// Fix for MultiSite stripping KSES for roles other than administrator
		remove_all_filters( 'content_save_pre' );

		$form_id = wp_insert_post(
			array(
				'post_type' => 'nl4wp-form',
				'post_status' => 'publish',
				'post_title' => $form_data['name'],
				'post_content' => $form_content,
			)
		);

        // if settings were passed, save those too.
        if( isset( $form_data['settings'] ) ) {
            update_post_meta( $form_id, '_nl4wp_settings', $form_data['settings'] );
        }

        // set default form ID
        $this->set_default_form_id( $form_id );

		$this->messages->flash( __( "<strong>Success!</strong> Form successfully saved.", 'newsletter-for-wp' ) );
		wp_redirect( nl4wp_get_edit_form_url( $form_id ) );
		exit;
	}

	/**
	 * Saves a form to the database
	 *
	 * @param array $data
	 * @return int
	 */
	public function save_form( $data ) {
		$keys = array(
			'settings' => array(),
			'messages' => array(),
			'name' => '',
			'content' => ''
		);

		$data = array_merge( $keys, $data );
		$data = $this->sanitize_form_data( $data );

		$post_data = array(
			'post_type'     => 'nl4wp-form',
			'post_status'   => ! empty( $data['status'] ) ? $data['status'] : 'publish',
			'post_title'    => $data['name'],
			'post_content'  => $data['content']
		);

		// if an `ID` is given, make sure post is of type `nl4wp-form`
		if( ! empty( $data['ID'] ) ) {
			$post = get_post( $data['ID'] );

			if( $post instanceof WP_Post && $post->post_type === 'nl4wp-form' ) {
				$post_data['ID'] = $data['ID'];

				// merge new settings  with current settings to allow passing partial data
				$current_settings = get_post_meta( $post->ID, '_nl4wp_settings', true );
				if( is_array( $current_settings ) ) {
					$data['settings'] = array_merge( $current_settings, $data['settings'] );
				}
			}
		}

		// Fix for MultiSite stripping KSES for roles other than administrator
		remove_all_filters( 'content_save_pre' );

		$form_id = wp_insert_post( $post_data );
		update_post_meta( $form_id, '_nl4wp_settings', $data['settings'] );

		// save form messages in individual meta keys
		foreach( $data['messages'] as $key => $message ) {
			update_post_meta( $form_id, 'text_' . $key, $message );
		}

		/**
		 * Runs right after a form is updated.
		 *
		 * @since 3.0
		 *
		 * @param int $form_id
		 */
		do_action( 'nl4wp_save_form', $form_id );

		return $form_id;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function sanitize_form_data( $data ) {

		$raw_data = $data;

		// strip <form> tags from content
		$data['content'] =  preg_replace( '/<\/?form(.|\s)*?>/i', '', $data['content'] );

		// replace lowercased name="name" to prevent 404
		$data['content'] = str_ireplace( ' name=\"name\"', ' name=\"NAME\"', $data['content'] );

		// sanitize text fields
		$data['settings']['redirect'] = sanitize_text_field( $data['settings']['redirect'] );

		// strip tags from messages
		foreach( $data['messages'] as $key => $message ) {
			$data['messages'][$key] = strip_tags( $message, '<strong><b><br><a><script><u><em><i><span><img>' );
		}

		// make sure lists is an array
		if( ! isset( $data['settings']['lists'] ) ) {
			$data['settings']['lists'] = array();
		}

		$data['settings']['lists'] = array_filter( (array) $data['settings']['lists'] );

		/**
		 * Filters the form data just before it is saved.
		 *
		 * @param array $data Sanitized array of form data.
		 * @param array $raw_data Raw array of form data.
		 *
		 * @since 3.0.8
         * @ignore
		 */
		$data = (array) apply_filters( 'nl4wp_form_sanitized_data', $data, $raw_data );

		return $data;
	}

	/**
	 * Saves a form
	 */
	public function process_save_form( ) {

		check_admin_referer( 'edit_form', '_nl4wp_nonce' );
		$form_id = (int) $_POST['nl4wp_form_id'];

		$form_data = $_POST['nl4wp_form'];
		$form_data['ID'] = $form_id;

		$this->save_form( $form_data );
		$this->set_default_form_id( $form_id );

		$this->messages->flash( __( "<strong>Success!</strong> Form successfully saved.", 'newsletter-for-wp' ) );
	}

    /**
     * @param int $form_id
     */
	private function set_default_form_id( $form_id ) {
        $default_form_id = (int) get_option( 'nl4wp_default_form_id', 0 );

        if( empty( $default_form_id ) ) {
            update_option( 'nl4wp_default_form_id', $form_id );
        }
    }

	/**
	 * Goes through each form and aggregates array of stylesheet slugs to load.
	 *
	 * @hooked `nl4wp_save_form`
	 */
	public function update_form_stylesheets() {
		$stylesheets = array();

		$forms = nl4wp_get_forms();
		foreach( $forms as $form ) {

			$stylesheet = $form->get_stylesheet();

			if( ! empty( $stylesheet ) && ! in_array( $stylesheet, $stylesheets ) ) {
				$stylesheets[] = $stylesheet;
			}
		}

		update_option( 'nl4wp_form_stylesheets', $stylesheets );
	}

	/**
	 * Redirect to correct form action
	 *
	 * @ignore
	 */
	public function redirect_to_form_action() {

		if( ! empty( $_GET['view'] ) ) {
			return;
		}

		try{
			// try default form first
			$default_form = nl4wp_get_form();
			$redirect_url = nl4wp_get_edit_form_url( $default_form->ID );
		} catch(Exception $e) {
			// no default form, query first available form and go there
			$forms = nl4wp_get_forms( array( 'numberposts' => 1 ) );

			if( $forms ) {
				// if we have a post, go to the "edit form" screen
				$form = array_pop( $forms );
				$redirect_url = nl4wp_get_edit_form_url( $form->ID );
			} else {
				// we don't have a form yet, go to "add new" screen
				$redirect_url = nl4wp_get_add_form_url();
			}
		}
		
		wp_redirect( $redirect_url );
		exit;
	}

	/**
	 * Show the Forms Settings page
	 *
	 * @internal
	 */
	public function show_forms_page() {

		$view = ! empty( $_GET['view'] ) ? $_GET['view'] : '';

		/**
		 * @ignore
		 */
		do_action( 'nl4wp_admin_show_forms_page', $view );

		/**
		 * @ignore
		 */
		do_action( 'nl4wp_admin_show_forms_page-' . $view );
	}

	/**
	 * Show the "Edit Form" page
	 *
	 * @internal
	 */
	public function show_edit_page() {
		$form_id = ( ! empty( $_GET['form_id'] ) ) ? (int) $_GET['form_id'] : 0;
		$lists = $this->newsletter->get_lists();

		try{
			$form = nl4wp_get_form( $form_id );
		} catch( Exception $e ) {
			echo '<h2>' . __( "Form not found.", 'newsletter-for-wp' ) . '</h2>';
			echo '<p>' . $e->getMessage() . '</p>';
			echo '<p><a href="javascript:history.go(-1);"> &lsaquo; '. __( 'Go back' ) .'</a></p>';
			return;
		}

		$opts = $form->settings;
		$active_tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'fields';


		$form_preview_url = add_query_arg( array( 
            'nl4wp_preview_form' => $form_id,
        ), site_url( '/', 'admin' ) );

		require dirname( __FILE__ ) . '/views/edit-form.php';
	}

	/**
	 * Shows the "Add Form" page
	 *
	 * @internal
	 */
	public function show_add_page() {
		$lists = $this->newsletter->get_lists();
		$number_of_lists = count( $lists );
		require dirname( __FILE__ ) . '/views/add-form.php';
	}

	/**
	 * Get URL for a tab on the current page.
	 *
	 * @since 3.0
	 * @internal
	 * @param $tab
	 * @return string
	 */
	public function tab_url( $tab ) {
		return add_query_arg( array( 'tab' => $tab ), remove_query_arg( 'tab' ) );
	}

	/**
	 * Registers UI for when shortcake is activated
	 */
	public function register_shortcake_ui() {

		$assets = new NL4WP_Form_Asset_Manager();
		$assets->load_stylesheets();

		$forms = nl4wp_get_forms();
		$options = array();
		foreach( $forms as $form ) {
			$options[ $form->ID ] = $form->name;
		}

		/**
		 * Register UI for your shortcode
		 *
		 * @param string $shortcode_tag
		 * @param array $ui_args
		 */
		shortcode_ui_register_for_shortcode( 'nl4wp_form', array(
				'label' => esc_html__( 'Newsletter Sign-Up Form', 'newsletter-for-wp' ),
				'listItemImage' => 'dashicons-feedback',
				'attrs' => array(
					array(
						'label'    => esc_html__( 'Select the form to show' ,'newsletter-for-wp' ),
						'attr'     => 'id',
						'type'     => 'select',
						'options'  => $options
					)
				),
			)
		);
	}
}
