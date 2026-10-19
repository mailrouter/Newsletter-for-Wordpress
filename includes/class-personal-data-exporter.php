<?php

defined('ABSPATH') || exit;


/**
 * Class NL4WP_Personal_Data_Exporter
 */
class NL4WP_Personal_Data_Exporter
{
    /**
     * Registers the personal data exporter for comments.
     *
     * @param array[] $exporters An array of personal data exporters.
     * @return array[] An array of personal data exporters.
     */
    public static function add_newsletter_to_privacy_export($exporters)
    {
        $exporters['newsletter-subscriptions'] = [
            'exporter_friendly_name' => __('Newsletter Subscriptions', 'newsletter-for-wp'),
            'callback' => [self::class, 'get_newsletter_subscription_data'],
        ];

        return $exporters;
    }

    /**
     * Retrieves subscription data for a given email address.
     *
     * The search-members endpoint is not available in the Voxmail adapter.
     * Query each configured list through the member endpoint instead.
     *
     * @param string $email_address The email address of the user to search for.
     * @return array
     */
    public static function get_newsletter_subscription_data($email_address)
    {
        $api              = nl4wp_get_api_v3();
        $subscribed_lists = [];
        $data_to_export   = [];

        try {
            $lists = $api->get_lists();

            foreach ($lists as $list) {
                if (empty($list->id)) {
                    continue;
                }

                try {
                    $member = $api->get_list_member($list->id, $email_address);
                } catch (NL4WP_API_Resource_Not_Found_Exception $e) {
                    continue;
                }

                if (isset($member->status) && $member->status === 'subscribed') {
                    $subscribed_lists[] = $list->name;
                }
            }
        } catch (NL4WP_API_Exception $e) {
            nl4wp_get_service('log')->warning(sprintf('Personal data export: %s', (string) $e));
        }

        $subscribed_lists = array_values(array_unique($subscribed_lists));

        if ($subscribed_lists) {
            $data_to_export[] = [
                'group_id' => 'newsletter',
                'group_label' => __('Newsletter Subscriptions', 'newsletter-for-wp'),
                'item_id' => 'newsletter-subscriptions',
                'data' => [
                    [
                        'name'  => __('Newsletter Lists', 'newsletter-for-wp'),
                        'value' => implode(', ', $subscribed_lists),
                    ],
                ],
            ];
        }

        return [
            'data' => $data_to_export,
            'done' => true,
        ];
    }
}
