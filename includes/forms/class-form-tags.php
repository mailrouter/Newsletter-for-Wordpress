<?php

defined('ABSPATH') || exit;


/**
 * Class NL4WP_Form_Tags
 *
 * @access private
 * @ignore
 */
class NL4WP_Form_Tags extends NL4WP_Dynamic_Content_Tags
{
    /**
     * @var NL4WP_Form
     */
    protected $form;

    /**
     * @var NL4WP_Form_Element
     */
    protected $form_element;

    public function add_hooks(): void
    {
        add_filter('nl4wp_form_response_html', [ $this, 'replace_in_form_response' ], 10, 2);
        add_filter('nl4wp_form_content', [ $this, 'replace_in_form_content' ], 10, 3);
        add_filter('nl4wp_form_redirect_url', [ $this, 'replace_in_form_redirect_url' ], 10, 2);
    }

    /**
     * Register template tags
     */
    public function register(): void
    {
        parent::register();

        $this->tags['response'] = [
            'description' => __('Replaced with the form response (error or success messages).', 'newsletter-for-wp'),
            'callback'    => [ $this, 'get_form_response' ],
            'raw_html' => true,
        ];

        $this->tags['data'] = [
            'description' => sprintf(__('Data from the URL or a submitted form.', 'newsletter-for-wp')),
            'callback'    => [ $this, 'get_data' ],
            'example'     => "data key='UTM_SOURCE' default='Default Source'",
        ];

        $this->tags['subscriber_count'] = [
            'description' => __('Replaced with the number of subscribers on the selected list(s)', 'newsletter-for-wp'),
            'callback'    => [ $this, 'get_subscriber_count' ],
        ];
    }


    public function replace_in_form_content($string, NL4WP_Form $form, NL4WP_Form_Element $element)
    {
        $this->form         = $form;
        $this->form_element = $element;

        $string = $this->replace_in_html($string);
        return $string;
    }

    public function replace_in_form_response($string, NL4WP_Form $form)
    {
        $this->form = $form;

        $string = $this->replace_in_html($string);
        return $string;
    }

    public function replace_in_form_redirect_url($string, NL4WP_Form $form)
    {
        $this->form = $form;
        $string     = $this->replace_in_url($string);
        return $string;
    }

    /**
     * Returns the number of subscribers on the selected lists (for the form context)
     */
    public function get_subscriber_count(): string
    {
        $newsletter = new NL4WP_Newsletter();
        $count     = $newsletter->get_subscriber_count($this->form->get_lists());
        return number_format($count);
    }

    /**
     * Returns the form response
     */
    public function get_form_response(): string
    {
        if ($this->form_element instanceof NL4WP_Form_Element) {
            return $this->form_element->get_response_html();
        }

        return '';
    }

    /**
     * Gets data value from GET or POST variables.
     *
     * @param array $args
     * @return string
     */
    public function get_data(array $args = [])
    {
        if (empty($args['key'])) {
            return '';
        }

        $default = isset($args['default']) ? $args['default'] : '';
        $key     = $args['key'];

        // phpcs:ignore WordPress.Security.NonceVerification
        $data  = array_merge($_GET, $_POST);
        $value = isset($data[ $key ]) ? $data[ $key ] : $default;

        // turn array into readable value
        if (is_array($value)) {
            $value = array_filter($value);
            $value = join(', ', $value);
        }

        return wp_unslash($value);
    }
}
