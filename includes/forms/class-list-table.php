<?php

defined('ABSPATH') or exit;

if (! class_exists('WP_List_Table', false)) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Class NL4WP_Forms_List_Table
 */
class NL4WP_Forms_List_Table extends WP_List_Table
{

    /**
     * @var bool
     */
    public $is_trash = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            array(
                'singular' => 'form',
                'plural'   => 'forms',
                'ajax'     => false
            )
        );
    }

    public function prepare_items() {
        $columns  = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $hidden   = array();
        $this->is_trash = isset($_REQUEST['post_status']) && $_REQUEST['post_status'] === 'trash';

        $this->process_bulk_action();

        $this->_column_headers = array( $columns, $hidden, $sortable );
        $this->items = $this->get_items();
        $this->set_pagination_args(
            array(
                'per_page' => 50,
                'total_items' => count($this->items)
            )
        );
    }

    /**
     * Get an associative array ( id => link ) with the list
     * of views available on this table.
     *
     * @since 3.1.0
     * @access protected
     *
     * @return array
     */
    public function get_views()
    {
        $counts = wp_count_posts('nl4wp-form');
        $current = isset($_GET['post_status']) ? $_GET['post_status'] : '';

        $count_others = $counts->publish + $counts->draft + $counts->future + $counts->pending;

        return array(
            '' => sprintf('<a href="%s" class="%s">%s</a> (%d)', remove_query_arg(array('post_status')), $current === '' ? 'current' : '', __('All'), $count_others),
            'trash' => sprintf('<a href="%s" class="%s">%s</a> (%d)', add_query_arg(array('post_status' => 'trash' )), $current == 'trash' ? 'current' : '', __('Trash'), $counts->trash),
        );
    }

    /**
     * @return array
     */
    public function get_bulk_actions()
    {
        $actions = array();
        if ($this->is_trash) {
            $actions['untrash'] = __('Restore');
            $actions['delete'] = __('Delete Permanently');
        } else {
            $actions['duplicate'] = __('Duplicate');
            $actions['trash'] = __('Move to Trash');
        }
        return $actions;
    }

    public function get_default_primary_column_name()
    {
        return 'form_name';
    }

    /**
     * @return array
     */
    public function get_table_classes()
    {
        return array( 'widefat', 'fixed', 'striped', 'nl4wp-table' );
    }

    /**
     * @return array
     */
    public function get_columns()
    {
        return array(
            'cb'       => '<input type="checkbox" />',
            'form_name'    => __('Form', 'newsletter-for-wp'),
            'ID'            => __('ID', 'newsletter-for-wp'),
            'shortcode'     => __('Shortcode', 'newsletter-for-wp'),
        );
    }

    /**
     * @return array
     */
    public function get_sortable_columns()
    {
        return array();
    }

    /**
     * @return array
     */
    public function get_items()
    {
        $args = array(
            'post_status' =>  array( 'publish', 'draft', 'pending', 'future' )
        );

        if (! empty($_GET['post_status' ])) {
            $args['post_status'] = sanitize_text_field($_GET['post_status']);
        }

        $items = nl4wp_get_forms($args);

        return $items;
    }

    /**
     * Textshown when there are no items to show
     */
    public function no_items()
    {
        if (! $this->is_trash) {
            echo sprintf(__('No forms found. <a href="%s">Create your first form</a>.', 'newsletter-for-wp'), nl4wp_get_add_form_url());
        } else {
            _e( 'No items found.' );
        }
    }

    /**
     * @param $item
     *
     * @return string
     */
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="forms[]" value="%s" />', esc_attr($item->ID));
    }

    /**
     * @param NL4WP_Form $form
     *
     * @return mixed
     */
    public function column_ID(NL4WP_Form $form)
    {
        return $form->ID;
    }

    /**
     * @param NL4WP_Form $form
     * @return string
     */
    public function column_form_name($form)
    {
        if ($this->is_trash) {
            return sprintf('<strong>%s</strong>', esc_html($form->name));
        }

        $edit_link = nl4wp_get_edit_form_url($form->ID);
        $title      = '<strong><a class="row-title" href="' . $edit_link . '">' . esc_html($form->name) . '</a></strong>';

        $actions    = array(
            'edit'   => '<a href="' . $edit_link . '">' . __('Fields', 'newsletter-for-wp') . '</a>',
            'messages' => '<a href="'. add_query_arg(array( 'tab' => 'messages' ), $edit_link) .'">'. __('Messages', 'newsletter-for-wp') .'</a>',
            'settings' => '<a href="'. add_query_arg(array( 'tab' => 'settings' ), $edit_link) .'">'. __('Settings', 'newsletter-for-wp') . '</a>',
            'appearance' => '<a href="'. add_query_arg(array( 'tab' => 'appearance' ), $edit_link) .'">'. __('Appearance', 'newsletter-for-wp') .'</a>'
        );

        return $title . $this->row_actions($actions);
    }

    /**
     * @param NL4WP_Form $form
     *
     * @return string
     */
    public function column_shortcode(NL4WP_Form $form)
    {
        return sprintf('<input type="text" onfocus="this.select();" readonly="readonly" value="%s">', esc_attr('[nl4wp_form id="' . $form->ID . '"]'));
    }

    /**
     *
     */
    public function process_bulk_action()
    {
        $action = $this->current_action();
        if (empty($action)) {
            return false;
        }

        $forms = (array) $_REQUEST['forms'];

        switch ($action) {

            case 'trash': 
                return array_map('wp_trash_post', $forms);

            case 'untrash':
                return array_map('wp_untrash_post', $forms);

            case 'delete':
                return array_map('wp_delete_post', $forms);

            case 'duplicate':
                foreach($forms as $form_id) {
                    $this->duplicate_form($form_id);
                }
                return true;

        }

        return false;
    }

    protected function duplicate_form($form_id)
    {
        $post = get_post($form_id);
        $post_meta = get_post_meta($form_id);

        $new_post_id = wp_insert_post(
            array(
                'post_title' => $post->post_title,
                'post_content' => $post->post_content,
                'post_type' => 'nl4wp-form',
                'post_status' => 'publish'
            )
        );
        foreach ($post_meta as $meta_key => $meta_value) {
            $meta_value = maybe_unserialize($meta_value[0]);
            update_post_meta($new_post_id, $meta_key, $meta_value);
        }
    }

}

