<?php

defined('ABSPATH') || exit;

return [
    'subscribed'             => [
        'type' => 'success',
        'text' => esc_html__('Thank you, your sign-up request was successful! Please check your email inbox to confirm.', 'newsletter-for-wp'),
    ],
    'updated'                => [
        'type' => 'success',
        'text' => esc_html__('Thank you, your records have been updated!', 'newsletter-for-wp'),
    ],
    'unsubscribed'           => [
        'type' => 'success',
        'text' => esc_html__('You were successfully unsubscribed.', 'newsletter-for-wp'),
    ],
    'not_subscribed'         => [
        'type' => 'notice',
        'text' => esc_html__('Given email address is not subscribed.', 'newsletter-for-wp'),
    ],
    'error'                  => [
        'type' => 'error',
        'text' => esc_html__('Oops. Something went wrong. Please try again later.', 'newsletter-for-wp'),
    ],
    'invalid_email'          => [
        'type' => 'error',
        'text' => esc_html__('Please provide a valid email address.', 'newsletter-for-wp'),
    ],
    'already_subscribed'     => [
        'type' => 'notice',
        'text' => esc_html__('Given email address is already subscribed, thank you!', 'newsletter-for-wp'),
    ],
    'required_field_missing' => [
        'type' => 'error',
        'text' => esc_html__('Please fill in the required fields.', 'newsletter-for-wp'),
    ],
    'no_lists_selected'      => [
        'type' => 'error',
        'text' => esc_html__('Please select at least one list.', 'newsletter-for-wp'),
    ],
    'spam'      => [
        'type' => 'error',
        'text' => esc_html__('Your submission was marked as spam.', 'newsletter-for-wp'),
    ],
];
