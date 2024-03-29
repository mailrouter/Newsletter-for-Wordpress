<?php

defined('ABSPATH') or exit;

/**
 * Class NL4WP_WooCommerce_Integration
 *
 * @ignore
 */
class NL4WP_WooCommerce_Integration extends NL4WP_Integration
{

    /**
     * @var string
     */
    public $name = "WooCommerce Checkout";

    /**
     * @var string
     */
    public $description = "Subscribes people from WooCommerce's checkout form.";

    /**
     * Add hooks
     */
    public function add_hooks()
    {
        if (! $this->options['implicit']) {
            // create hook name based on position setting
            $hook = sprintf('woocommerce_%s', $this->options['position']);
            add_action($hook, array( $this, 'output_checkbox' ), 20);
            add_action('woocommerce_checkout_update_order_meta', array( $this, 'save_woocommerce_checkout_checkbox_value' ));

            // specific hooks for klarna
            add_filter('kco_create_order', array($this, 'add_klarna_field'));
            add_filter('klarna_after_kco_confirmation', array($this, 'subscribe_from_klarna_checkout'), 10, 2);
        }

        add_action('woocommerce_checkout_order_processed', array( $this, 'subscribe_from_woocommerce_checkout' ));
    }

    /**
     * Add default value for "position" setting
     *
     * @return array
     */
    protected function get_default_options()
    {
        $defaults = parent::get_default_options();
        $defaults['position'] = 'billing';
        return $defaults;
    }

    public function add_klarna_field($create) {
        $create['options']['additional_checkbox']['text'] = $this->get_label_text();
        $create['options']['additional_checkbox']['checked'] = (bool) $this->options['precheck'];
        $create['options']['additional_checkbox']['required'] = false;
        return $create;
    }


    /**
    * @param int $order_id
    */
    public function save_woocommerce_checkout_checkbox_value($order_id)
    {
        update_post_meta($order_id, '_nl4wp_optin', $this->checkbox_was_checked());
    }

    /**
     * {@inheritdoc}
     *
     * @param $order_id
     *
     * @return bool|mixed
     */
    public function triggered($order_id = null)
    {
        if ($this->options['implicit']) {
            return true;
        }

        if (! $order_id) {
            return false;
        }

        $do_optin = get_post_meta($order_id, '_nl4wp_optin', true);
        return $do_optin;
    }

    public function subscribe_from_klarna_checkout($order_id, $klarna_order)
    {
        // $klarna_order is the returned object from Klarna
        if (false == $klarna_order['merchant_requested']['additional_checkbox']) {
            return;
        }

        // get back into regular subscribe flow
        update_post_meta($order_id, '_nl4wp_optin', true);
        $this->subscribe_from_woocommerce_checkout($order_id);
        return;
    }

    /**
    * @param int $order_id
    * @return boolean
    */
    public function subscribe_from_woocommerce_checkout($order_id)
    {
        if (! $this->triggered($order_id)) {
            return false;
        }

        $order = wc_get_order($order_id);

        if (method_exists($order, 'get_billing_email')) {
            $data = array(
                'EMAIL' => $order->get_billing_email(),
                'NAME' => "{$order->get_billing_first_name()} {$order->get_billing_last_name()}",
                'FNAME' => $order->get_billing_first_name(),
                'LNAME' => $order->get_billing_last_name(),
            );
        } else {
            // NOTE: for compatibility with WooCommerce < 3.0
            $data = array(
                'EMAIL' => $order->billing_email,
                'NAME' => "{$order->billing_first_name} {$order->billing_last_name}",
                'FNAME' => $order->billing_first_name,
                'LNAME' => $order->billing_last_name,
            );
        }

        // TODO: add billing address fields, maybe by finding Newsletter field of type "address"?

        return $this->subscribe($data, $order_id);
    }

    /**
     * @return bool
     */
    public function is_installed()
    {
        return class_exists('WooCommerce');
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function get_object_link($object_id)
    {
        return sprintf('<a href="%s">%s</a>', get_edit_post_link($object_id), sprintf(__('Order #%d', 'newsletter-for-wp'), $object_id));
    }
}
