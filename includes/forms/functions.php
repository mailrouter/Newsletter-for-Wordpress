<?php

defined('ABSPATH') || exit;


/**
 * Returns a Form instance
 *
 * @access public
 *
 * @param int|WP_Post $form_id.
 *
 * @return NL4WP_Form
 */
function nl4wp_get_form($form_id = 0)
{
    return NL4WP_Form::get_instance($form_id);
}

/**
 * Get an array of Form instances
 *
 * @access public
 *
 * @param array $args Array of parameters
 *
 * @return NL4WP_Form[]
 */
function nl4wp_get_forms(array $args = [])
{
    // parse function arguments
    $default_args = [
        'post_status'         => 'publish',
        'posts_per_page'      => -1,
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ];
    $args         = array_merge($default_args, $args);

    // set post_type here so it can't be overwritten using function arguments
    $args['post_type'] = 'nl4wp-form';

    $q     = new WP_Query();
    $posts = $q->query($args);
    $forms = [];
    foreach ($posts as $post) {
        try {
            $form = nl4wp_get_form($post);
        } catch (Exception $e) {
            continue;
        }

        $forms[] = $form;
    }
    return $forms;
}

/**
 * Echoes the given form
 *
 * @access public
 *
 * @param int $form_id
 * @param array $config
 * @param bool $echo
 *
 * @return string
 */
function nl4wp_show_form($form_id = 0, $config = [], $echo = true)
{
    /** @var NL4WP_Form_Manager $forms */
    $forms = nl4wp_get_service('forms');
    return $forms->output_form($form_id, $config, $echo);
}


/**
 * Gets an instance of the submitted form, if any.
 *
 * @access public
 *
 * @return NL4WP_Form|null
 */
function nl4wp_get_submitted_form()
{
    return nl4wp_get_service('forms')->get_submitted_form();
}
