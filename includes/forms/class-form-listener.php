<?php

/**
 * Class NL4WP_Form_Listener
 *
 * @since 3.0
 * @access private
 */
class NL4WP_Form_Listener {

	/**
	 * @var NL4WP_Form The submitted form instance
	 */
	public $submitted_form;

	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * Listen for submitted forms
	 *
	 * @param NL4WP_Request $request
	 * @return bool
	 */
		public function listen( NL4WP_Request $request ) {

			$form_id = $request->post->get( '_nl4wp_form_id' );
			if( empty( $form_id ) ) {
				return false;
			}

			// get form instance
			try {
				$form = nl4wp_get_form( $form_id );
			} catch( Exception $e ) {
				return false;
			}

			// where the magic happens
			$form->handle_request( $request );
			$form->validate();

			// store submitted form
			$this->submitted_form = $form;

			// did form have errors?
			if( ! $form->has_errors() ) {

				// form was valid, do something
				$method = 'process_' . $form->get_action() . '_form';
				call_user_func( array( $this, $method ), $form, $request );
			} else {
				$this->get_log()->info( sprintf( "Form %d > Submitted with errors: %s", $form->ID, join( ', ', $form->errors ) ) );
			}

			$this->respond( $form );

			return true;
		}

	/**
	 * Process a subscribe form.
	 *
	 * @param NL4WP_Form $form
	 * @param NL4WP_Request $request
	 */
	public function process_subscribe_form( NL4WP_Form $form, NL4WP_Request $request ) {
		$result = false;
		$newsletter = new NL4WP_Newsletter();
		$email_type = $form->get_email_type();
		$data = $form->get_data();
		$client_ip = $request->get_client_ip();

		/** @var NL4WP_Newsletter_Subscriber $subscriber */
		$subscriber = null;

		/**
		 * @ignore
		 * @deprecated 4.0
		 */
		$data = apply_filters( 'nl4wp_merge_vars', $data );

		/**
		 * @ignore
		 * @deprecated 4.0
		 */
		$data = (array) apply_filters( 'nl4wp_form_merge_vars', $data, $form );

		// create a map of all lists with list-specific data
		$mapper = new NL4WP_List_Data_Mapper( $data, $form->get_lists() );

		/** @var NL4WP_Newsletter_Subscriber[] $map */
		$map = $mapper->map();

		// loop through lists
		foreach( $map as $list_id => $subscriber ) {

			$subscriber->status = $form->settings['double_optin'] ? 'pending' : 'subscribed';
			$subscriber->email_type = $email_type;
			$subscriber->ip_signup = $client_ip;

			/**
			 * Filters subscriber data before it is sent to Newsletter. Fires for both form & integration requests.
			 *
			 * @param NL4WP_Newsletter_Subscriber $subscriber
			 */
			$subscriber = apply_filters( 'nl4wp_subscriber_data', $subscriber );

			/**
			 * Filters subscriber data before it is sent to Newsletter. Only fires for form requests.
			 *
			 * @param NL4WP_Newsletter_Subscriber $subscriber
			 */
			$subscriber = apply_filters( 'nl4wp_form_subscriber_data', $subscriber );

			// send a subscribe request to Newsletter for each list
			$result = $newsletter->list_subscribe( $list_id, $subscriber->email_address, $subscriber->to_array(), $form->settings['update_existing'], $form->settings['replace_interests'] );
		}

		$log = $this->get_log();

		// do stuff on failure
		if( ! is_object( $result ) || empty( $result->id ) ) {

			$error_code = $newsletter->get_error_code();
			$error_message = $newsletter->get_error_message();

			if( $newsletter->get_error_code() == 214 ) {
				$form->add_error('already_subscribed');
				$log->warning( sprintf( "Form %d > %s is already subscribed to the selected list(s)", $form->ID, $data['EMAIL'] ) );
			} else {
				$form->add_error('error');
				$log->error( sprintf( 'Form %d > Newsletter API error: %s %s', $form->ID, $error_code, $error_message ) );

				/**
				 * Fire action hook so API errors can be hooked into.
				 *
				 * @param NL4WP_Form $form
				 * @param string $error_message
				 */
				do_action( 'nl4wp_form_api_error', $form, $error_message );
			}

			// bail
			return;
		}

		// Success! Did we update or newly subscribe?
		if( $result->status === 'subscribed' && $result->was_already_on_list ) {
			$form->add_message( 'updated' );

			$log->info( sprintf( "Form %d > Successfully updated %s", $form->ID, $data['EMAIL'] ) );

			/**
			 * Fires right after a form was used to update an existing subscriber.
			 *
			 * @since 3.0
			 *
			 * @param NL4WP_Form $form Instance of the submitted form
			 * @param string $email
			 * @param array $data
			 */
			do_action( 'nl4wp_form_updated_subscriber', $form, $subscriber->email_address, $data );
		} else {
			$form->add_message( 'subscribed' );

			$log->info( sprintf( "Form %d > Successfully subscribed %s", $form->ID, $data['EMAIL'] ) );
		}

		/**
		 * Fires right after a form was used to add a new subscriber (or update an existing one).
		 *
		 * @since 3.0
		 *
		 * @param NL4WP_Form $form Instance of the submitted form
		 * @param string $email
		 * @param array $data
		 * @param NL4WP_Newsletter_Subscriber[] $subscriber
		 */
		do_action( 'nl4wp_form_subscribed', $form, $subscriber->email_address, $data, $map );
	}

	/**
	 * @param NL4WP_Form $form
	 * @param NL4WP_Request $request
	 */
	public function process_unsubscribe_form( NL4WP_Form $form, NL4WP_Request $request = null ) {

		$newsletter = new NL4WP_Newsletter();
		$log = $this->get_log();
		$result = null;
		$data = $form->get_data();

		// unsubscribe from each list
		foreach( $form->get_lists() as $list_id ) {
			$result = $newsletter->list_unsubscribe( $list_id, $data['EMAIL'] );
		}

		if( ! $result ) {
			$form->add_error( 'error' );
			$log->error( sprintf( 'Form %d > Newsletter API error: %s', $form->ID, $newsletter->get_error_message() ) );

			// bail
			return;
		}

		// Success! Unsubscribed.
		$form->add_message('unsubscribed');
		$log->info( sprintf( "Form %d > Successfully unsubscribed %s", $form->ID, $data['EMAIL'] ) );


		/**
		 * Fires right after a form was used to unsubscribe.
		 *
		 * @since 3.0
		 *
		 * @param NL4WP_Form $form Instance of the submitted form.
		 */
		do_action( 'nl4wp_form_unsubscribed', $form );
	}

	/**
	 * @param NL4WP_Form $form
	 */
	public function respond( NL4WP_Form $form ) {

		$success = ! $form->has_errors();

		if( $success ) {

			/**
			 * Fires right after a form is submitted without any errors (success).
			 *
			 * @since 3.0
			 *
			 * @param NL4WP_Form $form Instance of the submitted form
			 */
			do_action( 'nl4wp_form_success', $form );

		} else {

			/**
			 * Fires right after a form is submitted with errors.
			 *
			 * @since 3.0
			 *
			 * @param NL4WP_Form $form The submitted form instance.
			 */
			do_action( 'nl4wp_form_error', $form );

			// fire a dedicated event for each error
			foreach( $form->errors as $error ) {

				/**
				 * Fires right after a form was submitted with errors.
				 *
				 * The dynamic portion of the hook, `$error`, refers to the error that occurred.
				 *
				 * Default errors give us the following possible hooks:
				 *
				 * - nl4wp_form_error_error                     General errors
				 * - nl4wp_form_error_spam
				 * - nl4wp_form_error_invalid_email             Invalid email address
				 * - nl4wp_form_error_already_subscribed        Email is already on selected list(s)
				 * - nl4wp_form_error_required_field_missing    One or more required fields are missing
				 * - nl4wp_form_error_no_lists_selected         No Newsletter lists were selected
				 *
				 * @since 3.0
				 *
				 * @param   NL4WP_Form     $form        The form instance of the submitted form.
				 */
				do_action( 'nl4wp_form_error_' . $error, $form );
			}

		}

		/**
		 * Fires right before responding to the form request.
		 *
		 * @since 3.0
		 *
		 * @param NL4WP_Form $form Instance of the submitted form.
		 */
		do_action( 'nl4wp_form_respond', $form );

		// do stuff on success (non-AJAX only)
		if( $success && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

			// do we want to redirect?
			$redirect_url = $form->get_redirect_url();
			if ( ! empty( $redirect_url ) ) {
				wp_redirect( $redirect_url );
				exit;
			}
		}
	}

	/**
	 * @return NL4WP_API_v3
	 */
	protected function get_api() {
		return nl4wp('api');
	}

	/**
	 * @return NL4WP_Debug_Log
	 */
	protected function get_log() {
		return nl4wp('log');
	}

}
