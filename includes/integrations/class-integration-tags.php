<?php

defined('ABSPATH') || exit;


/**
 * Class NL4WP_Integration_Tags
 *
 * @ignore
 * @access private
 */
class NL4WP_Integration_Tags extends NL4WP_Dynamic_Content_Tags
{
    /**
     * @var NL4WP_Integration
     */
    protected $integration;

    /**
     * Add hooks
     */
    public function add_hooks(): void
    {
        add_filter('nl4wp_integration_checkbox_label', [ $this, 'replace_in_checkbox_label' ], 10, 2);
    }

    /**
     * Register template tags for integrations
     */
    public function register(): void
    {
        parent::register();

        $this->tags['subscriber_count'] = [
            'description' => __('Replaced with the number of subscribers on the selected list(s)', 'newsletter-for-wp'),
            'callback'    => [ $this, 'get_subscriber_count' ],
        ];
    }

    /**
     * @hooked `nl4wp_integration_checkbox_label`
     * @param string $string
     * @param NL4WP_Integration $integration
     * @return string
     */
    public function replace_in_checkbox_label($string, NL4WP_Integration $integration)
    {
        $this->integration = $integration;
        return $this->replace_in_html($string);
    }

    /**
     * Returns the number of subscribers on the selected lists (for the form context)
     */
    public function get_subscriber_count(): string
    {
        $newsletter = new NL4WP_Newsletter();
        $list_ids  = $this->integration->get_lists();
        $count     = $newsletter->get_subscriber_count($list_ids);
        return number_format($count);
    }
}
