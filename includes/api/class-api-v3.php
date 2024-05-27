<?php

/**
 * Class NL4WP_API_v3
 */
class NL4WP_API_v3
{

    /**
     * @var NL4WP_API_v3_Client
     */
    protected $client;


    /**
     * Constructor
     *
     * @param string $api_key
     */
    public function __construct($api_key)
    {
        $this->client = new NL4WP_API_v3_Client($api_key);
    }

    /**
     * Gets the API client to perform raw API calls.
     *
     * @return NL4WP_API_v3_Client
     */
    public function get_client()
    {
        return $this->client;
    }

    /**
     * Pings the Newsletter API to see if we're connected
     *
     * @return boolean
     * @throws NL4WP_API_Exception
     */
    public function is_connected()
    {
        $data = $this->client->get('/', array( 'fields' => 'account_id' ));
        $connected = is_object($data) && isset($data->account_id);
        return $connected;
    }

    /**
     * @param $email_address
     *
     * @return string
     */
    public function get_subscriber_hash($email_address)
    {
        return base64_encode( trim( $email_address ) ); /* NL_CHANGED */
    }

    /**
     * Get recent daily, aggregated activity stats for a list.
     *
     *
     *
     * @param string $list_id
     * @param array $args
     *
     * @return array
     * @throws NL4WP_API_Exception
     */
    public function get_list_activity($list_id, array $args = array())
    {
        $resource = sprintf('/lists/%s/activity', $list_id);
        $data = $this->client->get($resource, $args);

        if (is_object($data) && isset($data->activity)) {
            return $data->activity;
        }

        return array();
    }

    /**
     * Gets the interest categories for a given List
     *
     *
     *
     * @param string $list_id
     * @param array $args
     *
     * @return array
     * @throws NL4WP_API_Exception
     */
    public function get_list_interest_categories($list_id, array $args = array())
    {
        $resource = sprintf('/lists/%s/interest-categories', $list_id);
        $data = $this->client->get($resource, $args);

        if (is_object($data) && isset($data->categories)) {
            return $data->categories;
        }

        return array();
    }

    /**
     *
     *
     * @param string $list_id
     * @param string $interest_category_id
     * @param array $args
     *
     * @return array
     * @throws NL4WP_API_Exception
     */
    public function get_list_interest_category_interests($list_id, $interest_category_id, array $args = array())
    {
        $resource = sprintf('/lists/%s/interest-categories/%s/interests', $list_id, $interest_category_id);
        $data = $this->client->get($resource, $args);

        if (is_object($data) && isset($data->interests)) {
            return $data->interests;
        }

        return array();
    }

    /**
     * Get merge vars for a given list
     *
     *
     *
     * @param string $list_id
     * @param array $args
     *
     * @return array
     * @throws NL4WP_API_Exception
     */
    public function get_list_merge_fields($list_id, array $args = array())
    {
        $resource = sprintf('/lists/%s/merge-fields', $list_id);
        $data = $this->client->get($resource, $args);

        if (is_object($data) && isset($data->merge_fields)) {
            return $data->merge_fields;
        }

        return array();
    }

    /**
     *
     *
     * @param string $list_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_list($list_id, array $args = array())
    {
        $resource = sprintf('/lists/%s', $list_id);
        $data = $this->client->get($resource, $args);
        return $data;
    }

    /**
     *
     *
     * @param array $args
     *
     * @return array
     * @throws NL4WP_API_Exception
     */
    public function get_lists($args = array())
    {
        $resource = '/lists';
        $data = $this->client->get($resource, $args);

        if (is_object($data) && isset($data->lists)) {
            return $data->lists;
        }

        return array();
    }

    /**
     *
     *
     * @param string $list_id
     * @param string $email_address
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_list_member($list_id, $email_address, array $args = array())
    {
        $subscriber_hash = $this->get_subscriber_hash($email_address);
        $resource = sprintf('/lists/%s/members/%s', $list_id, $subscriber_hash);
        $data = $this->client->get($resource, $args);
        return $data;
    }

    /**
     * Batch subscribe / unsubscribe list members.
     *
     *
     *
     * @param string $list_id
     * @param array $args
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function add_list_members($list_id, array $args)
    {
        $resource = sprintf('/lists/%s', $list_id);
        return $this->client->post($resource, $args);
    }

    /**
     * Add or update (!) a member to a Newsletter list.
     *
     *
     *
     * @param string $list_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function add_list_member($list_id, array $args)
    {
        $subscriber_hash = $this->get_subscriber_hash($args['email_address']);
        $resource = sprintf('/lists/%s/members/%s', $list_id, $subscriber_hash);

        // make sure we're sending an object as the Newsletter schema requires this
        if (isset($args['merge_fields'])) {
            $args['merge_fields'] = (object) $args['merge_fields'];
        }

        if (isset($args['interests'])) {
            $args['interests'] = (object) $args['interests'];
        }

        // "put" updates the member if it's already on the list... take notice
        $data = $this->client->put($resource, $args);
        return $data;
    }

    /**
     *
     *
     * @param $list_id
     * @param $email_address
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function update_list_member($list_id, $email_address, array $args)
    {
        $subscriber_hash = $this->get_subscriber_hash($email_address);
        $resource = sprintf('/lists/%s/members/%s', $list_id, $subscriber_hash);

        // make sure we're sending an object as the Newsletter schema requires this
        if (isset($args['merge_fields'])) {
            $args['merge_fields'] = (object) $args['merge_fields'];
        }

        if (isset($args['interests'])) {
            $args['interests'] = (object) $args['interests'];
        }

        $data = $this->client->patch($resource, $args);
        return $data;
    }

    /**
     *
     *
     * @param string $list_id
     * @param string $email_address
     *
     * @return bool
     * @throws NL4WP_API_Exception
     */
    public function delete_list_member($list_id, $email_address)
    {
        $subscriber_hash = $this->get_subscriber_hash($email_address);
        $resource = sprintf('/lists/%s/members/%s', $list_id, $subscriber_hash);
        $data = $this->client->delete($resource);
        return !!$data;
    }

    /**
     * Get the tags on a list member.
     *
     *
     * @param string $list_id
     * @param string $email_address
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_list_member_tags($list_id, $email_address)
    {
        $subscriber_hash = $this->get_subscriber_hash($email_address);
        $resource = sprintf('/lists/%s/members/%s/tags', $list_id, $subscriber_hash);
        return $this->client->get($resource);
    }

    /**
     * Add or remove tags from a list member. If a tag that does not exist is passed in and set as ‘active’, a new tag will be created.
     *
     *
     * @param string $list_id
     * @param string $email_address
     * @param array $data
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function update_list_member_tags($list_id, $email_address, array $data)
    {
        $subscriber_hash = $this->get_subscriber_hash($email_address);
        $resource = sprintf('/lists/%s/members/%s/tags', $list_id, $subscriber_hash);
        return $this->client->post($resource, $data);
    }

    /**
     * Get information about all available segments for a specific list.
     *
     *
     * @param string $list_id
     * @param array $args
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_list_segments($list_id, array $args = array())
    {
        $resource = sprintf('/lists/%s/segments', $list_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_stores(array $args = array())
    {
        $resource = '/ecommerce/stores';
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store($store_id, array $args = array())
    {
        $resource =  sprintf('/ecommerce/stores/%s', $store_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function add_ecommerce_store(array $args)
    {
        $resource = '/ecommerce/stores';
        return $this->client->post($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function update_ecommerce_store($store_id, array $args)
    {
        $resource =  sprintf('/ecommerce/stores/%s', $store_id);
        return $this->client->patch($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     *
     * @return boolean
     * @throws NL4WP_API_Exception
     */
    public function delete_ecommerce_store($store_id)
    {
        $resource = sprintf('/ecommerce/stores/%s', $store_id);
        return !!$this->client->delete($resource);
    }

    /**
     *
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_customers($store_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/customers', $store_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $customer_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_customer($store_id, $customer_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/customers/%s', $store_id, $customer_id);
        return $this->client->get($resource, $args);
    }

    /**
     * Add OR update a store customer
     *
     *
     *
     * @param $store_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function add_ecommerce_store_customer($store_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/customers/%s', $store_id, $args['id']);
        return $this->client->put($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $customer_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function update_ecommerce_store_customer($store_id, $customer_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/customers/%s', $store_id, $customer_id);
        return $this->client->patch($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $customer_id
     *
     * @return bool
     * @throws NL4WP_API_Exception
     */
    public function delete_ecommerce_store_customer($store_id, $customer_id)
    {
        $resource = sprintf('/ecommerce/stores/%s/customers/%s', $store_id, $customer_id);
        return !!$this->client->delete($resource);
    }

    /**
     *
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_products($store_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/products', $store_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $product_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_product($store_id, $product_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/products/%s', $store_id, $product_id);
        return $this->client->get($resource, $args);
    }

    /**
     * Add a product to a store
     *
     *
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function add_ecommerce_store_product($store_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/products', $store_id);
        return $this->client->post($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $product_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function update_ecommerce_store_product($store_id, $product_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/products/%s', $store_id, $product_id);
        return $this->client->patch($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $product_id
     *
     * @return boolean
     * @throws NL4WP_API_Exception
     */
    public function delete_ecommerce_store_product($store_id, $product_id)
    {
        $resource = sprintf('/ecommerce/stores/%s/products/%s', $store_id, $product_id);
        return !!$this->client->delete($resource);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $product_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_product_variants($store_id, $product_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/products/%s/variants', $store_id, $product_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $product_id
     * @param string $variant_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_product_variant($store_id, $product_id, $variant_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/products/%s/variants/%s', $store_id, $product_id, $variant_id);
        return $this->client->get($resource, $args);
    }

    /**
     * Add OR update a product variant.
     *
     *
     *
     * @param string $store_id
     * @param string $product_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function add_ecommerce_store_product_variant($store_id, $product_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/products/%s/variants/%s', $store_id, $product_id, $args['id']);
        return $this->client->put($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $product_id
     * @param string $variant_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function update_ecommerce_store_product_variant($store_id, $product_id, $variant_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/products/%s/variants/%s', $store_id, $product_id, $variant_id);
        return $this->client->patch($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $product_id
     * @param string $variant_id
     *
     * @return boolean
     * @throws NL4WP_API_Exception
     */
    public function delete_ecommerce_store_product_variant($store_id, $product_id, $variant_id)
    {
        $resource = sprintf('/ecommerce/stores/%s/products/%s/variants/%s', $store_id, $product_id, $variant_id);
        return !!$this->client->delete($resource);
    }

    /**
     *
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_orders($store_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/orders', $store_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $order_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_order($store_id, $order_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/orders/%s', $store_id, $order_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function add_ecommerce_store_order($store_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/orders', $store_id);
        return $this->client->post($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $order_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function update_ecommerce_store_order($store_id, $order_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/orders/%s', $store_id, $order_id);
        return $this->client->patch($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $order_id
     *
     * @return bool
     * @throws NL4WP_API_Exception
     */
    public function delete_ecommerce_store_order($store_id, $order_id)
    {
        return !! $this->client->delete(sprintf('/ecommerce/stores/%s/orders/%s', $store_id, $order_id));
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $order_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function add_ecommerce_store_order_line($store_id, $order_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/orders/%s/lines', $store_id, $order_id);
        return $this->client->post($resource, $args);
    }


    /**
     *
     *
     * @param string $store_id
     * @param string $order_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_order_lines($store_id, $order_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/orders/%s/lines', $store_id, $order_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $order_id
     * @param string $line_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_order_line($store_id, $order_id, $line_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/orders/%s/lines/%s', $store_id, $order_id, $line_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $order_id
     * @param string $line_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function update_ecommerce_store_order_line($store_id, $order_id, $line_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/orders/%s/lines/%s', $store_id, $order_id, $line_id);
        return $this->client->patch($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $order_id
     * @param string $line_id
     *
     * @return bool
     * @throws NL4WP_API_Exception
     */
    public function delete_ecommerce_store_order_line($store_id, $order_id, $line_id)
    {
        $resource = sprintf('/ecommerce/stores/%s/orders/%s/lines/%s', $store_id, $order_id, $line_id);
        return !! $this->client->delete($resource);
    }

    /**
     *
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_carts($store_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/carts', $store_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $cart_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_cart($store_id, $cart_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/carts/%s', $store_id, $cart_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function add_ecommerce_store_cart($store_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/carts', $store_id);
        return $this->client->post($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $cart_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function update_ecommerce_store_cart($store_id, $cart_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/carts/%s', $store_id, $cart_id);
        return $this->client->patch($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $cart_id
     *
     * @return bool
     */
    public function delete_ecommerce_store_cart($store_id, $cart_id)
    {
        return !! $this->client->delete(sprintf('/ecommerce/stores/%s/carts/%s', $store_id, $cart_id));
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $cart_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_cart_lines($store_id, $cart_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/carts/%/lines', $store_id, $cart_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $cart_id
     * @param string $line_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_cart_line($store_id, $cart_id, $line_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/carts/%s/lines/%s', $store_id, $cart_id, $line_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $cart_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function add_ecommerce_store_cart_line($store_id, $cart_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/carts/%s/lines', $store_id, $cart_id);
        return $this->client->post($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $cart_id
     * @param string $line_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function update_ecommerce_store_cart_line($store_id, $cart_id, $line_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/carts/%s/lines/%s', $store_id, $cart_id, $line_id);
        return $this->client->patch($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $cart_id
     * @param string $line_id
     *
     * @return bool
     * @throws NL4WP_API_Exception
     */
    public function delete_ecommerce_store_cart_line($store_id, $cart_id, $line_id)
    {
        $resource = sprintf('/ecommerce/stores/%s/carts/%s/lines/%s', $store_id, $cart_id, $line_id);
        return !! $this->client->delete($resource);
    }

    /**
     *
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function add_ecommerce_store_promo_rule($store_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/promo-rules', $store_id);
        return $this->client->post($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_promo_rules($store_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/promo-rules', $store_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $promo_rule_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_promo_rule($store_id, $promo_rule_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/promo-rules/%s', $store_id, $promo_rule_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $promo_rule_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function update_ecommerce_store_promo_rule($store_id, $promo_rule_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/promo-rules/%s', $store_id, $promo_rule_id);
        return $this->client->patch($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $promo_rule_id
     *
     * @return boolean
     * @throws NL4WP_API_Exception
     */
    public function delete_ecommerce_store_promo_rule($store_id, $promo_rule_id)
    {
        $resource = sprintf('/ecommerce/stores/%s/promo-rules/%s', $store_id, $promo_rule_id);
        return !! $this->client->delete($resource);
    }

    /**
     *
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function add_ecommerce_store_promo_rule_promo_code($store_id, $promo_rule_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/promo-rules/%s/promo-codes', $store_id, $promo_rule_id);
        return $this->client->post($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_promo_rule_promo_codes($store_id, $promo_rule_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/promo-rules/%s/promo-codes', $store_id, $promo_rule_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $promo_rule_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_ecommerce_store_promo_rule_promo_code($store_id, $promo_rule_id, $promo_code_id, array $args = array())
    {
        $resource = sprintf('/ecommerce/stores/%s/promo-rules/%s/promo-codes/%s', $store_id, $promo_rule_id, $promo_code_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $promo_rule_id
     * @param array $args
     *
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function update_ecommerce_store_promo_rule_promo_code($store_id, $promo_rule_id, $promo_code_id, array $args)
    {
        $resource = sprintf('/ecommerce/stores/%s/promo-rules/%s/promo-codes/%s', $store_id, $promo_rule_id, $promo_code_id);
        return $this->client->patch($resource, $args);
    }

    /**
     *
     *
     * @param string $store_id
     * @param string $promo_rule_id
     *
     * @return boolean
     * @throws NL4WP_API_Exception
     */
    public function delete_ecommerce_store_promo_rule_promo_code($store_id, $promo_rule_id, $promo_code_id)
    {
        $resource = sprintf('/ecommerce/stores/%s/promo-rules/%s/promo-codes/%s', $store_id, $promo_rule_id, $promo_code_id);
        return !! $this->client->delete($resource);
    }


    /**
     * Get a list of an account's available templates
     *
     *
     * @param array $args
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_templates(array $args = array())
    {
        $resource = '/templates';
        return $this->client->get($resource, $args);
    }

    /**
     * Get information about a specific template.
     *
     *
     * @param string $template_id
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_template($template_id, array $args = array())
    {
        $resource = sprintf('/templates/%s', $template_id);
        return $this->client->get($resource, $args);
    }

    /**
     *
     * @param string $template_id
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_template_default_content($template_id, array $args = array())
    {
        $resource = sprintf('/templates/%s/default-content', $template_id);
        return $this->client->get($resource, $args);
    }

    /**
     * Create a new campaign
     *
     *
     * @param array $args
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function add_campaign(array $args)
    {
        $resource = '/campaigns';
        return $this->client->post($resource, $args);
    }

    /**
     * Get all campaigns in an account
     *
     *
     * @param array $args
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_campaigns(array $args = array())
    {
        $resource = '/campaigns';
        return $this->client->get($resource, $args);
    }

    /**
     * Get information about a specific campaign.
     *
     *
     * @param string $campaign_id
     * @param array $args
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_campaign($campaign_id, array $args = array())
    {
        $resource = sprintf('/campaigns/%s', $campaign_id);
        return $this->client->get($resource, $args);
    }

    /**
     * Update some or all of the settings for a specific campaign.
     *
     *
     * @param string $campaign_id
     * @param array $args
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function update_campaign($campaign_id, array $args)
    {
        $resource = sprintf('/campaigns/%s', $campaign_id);
        return $this->client->patch($resource, $args);
    }

    /**
     * Remove a campaign from the Newsletter account
     *
     *
     * @param string $campaign_id
     * @return bool
     * @throws NL4WP_API_Exception
     */
    public function delete_campaign($campaign_id)
    {
        $resource = sprintf('/campaigns/%s', $campaign_id);
        return !! $this->client->delete($resource);
    }

    /**
     * Perform an action on a Newsletter campaign
     *
     *
     *
     * @param string $campaign_id
     * @param string $action
     * @param array $args
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function campaign_action($campaign_id, $action, array $args = array())
    {
        $resource = sprintf('/campaigns/%s/actions/%s', $campaign_id, $action);
        return $this->client->post($resource, $args);
    }

    /**
     * Get the HTML and plain-text content for a campaign
     *
     *
     * @param string $campaign_id
     * @param array $args
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function get_campaign_content($campaign_id, array $args = array())
    {
        $resource = sprintf('/campaigns/%s/content', $campaign_id);
        return $this->client->get($resource, $args);
    }

    /**
     * Set the content for a campaign
     *
     *
     * @param string $campaign_id
     * @param array $args
     * @return object
     * @throws NL4WP_API_Exception
     */
    public function update_campaign_content($campaign_id, array $args)
    {
        $resource = sprintf('/campaigns/%s/content', $campaign_id);
        return $this->client->put($resource, $args);
    }

    /**
     * @return string
     */
    public function get_last_response_body()
    {
        return $this->client->get_last_response_body();
    }

    /**
     * @return array
     */
    public function get_last_response_headers()
    {
        return $this->client->get_last_response_headers();
    }
}
