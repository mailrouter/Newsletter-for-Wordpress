<?php

if (!function_exists('service_info')) require "wrapper.php";

# TODO verificare come mettere il default a jsonwp invece di auto se rileviamo che c'è curl, json_encode e wp_remote_post

function service_invoke_jsonwp($method, $service_api_key, $timestamp, $nonce, $hash, $args) {
    global $service_host;
    if (function_exists('json_encode')) {
        $request = json_encode($args);

        $headers = array(
            "Content-Type" => "application/json; charset=UTF-8",
            "User-Agent" => $GLOBALS['service_wrapper_uaprefix'].'jsonwp',
            "X-API-Key" => $service_api_key,
            "X-API-Timestamp" => $timestamp,
            "X-API-Nonce" => $nonce,
            "X-API-Signature" => $hash,
        );

        $referer = service_get_referer();
        # in alcuni casi il get_referer non trova l'host, ma su WP si può fare così
        # TODO: per testarlo dovrei togliere il get_referer qui sopra
        if (empty($referer)) $referer = add_query_arg($GLOBALS['wp']->query_vars, home_url($GLOBALS['wp']->request));
        if (!empty($referer)) $headers['Referer'] = $referer;

        $url = ($GLOBALS['service_wrapper_method'] == 'https' ? 'https://' : 'http://').$service_host.'/'.$GLOBALS['service_wrapper_jsonrpc_url'].'/'.$method;
        $service_last_result_raw = wp_remote_post($url, array(
            'method'      => 'POST',
            'headers'     => $headers,
            'body'        => $request,
            'data_format' => 'body',
            'timeout'     => $GLOBALS['service_wrapper_timeout'],
        ));

        if ( is_wp_error( $service_last_result_raw ) ) {
            //  $service_last_result_raw->get_error_message();
            $service_last_result = array(
                'faultCode' => $service_last_result_raw->get_error_code(),
                'faultString' => $service_last_result_raw->get_error_message(),
            );
        } else {
            $service_last_result = service_invoke_result('wp_remote_post', 'json', $url, $request, $service_last_result_raw['body']);
        }

    } else {
        $service_last_result = array(
            'faultCode' => ENS_ERROR_UNKNOWN,
            'faultString' => 'Cannot find json PHP extension: try using a different connection method'
        );
    }
    return $service_last_result;
}


class NL4WP_API_v3_Client {
/** File da cambiare per il supporto API Newsletter **/
    private const TRACK_ENABLE_TAG = 'TRACK_ENABLE';

    /**
     * @var string
     */
    private $api_keys;

    /**
     * @var array
     */
    private $last_response;

    /**
     * Constructor
     *
     * @param string $api_key
     */
    public function __construct( $api_key ) {
        $this->api_keys = preg_split('/[\r\n\s,]+/', $api_key, -1, PREG_SPLIT_NO_EMPTY);
    }


    /**
     * @param string $resource
     * @param array $args
     *
     * @return mixed
     */
    public function get( $resource, array $args = array() ) {
        return $this->request( 'GET', $resource, $args );
    }

    /**
     * @param string $resource
     * @param array $data
     *
     * @return mixed
     */
    public function post( $resource, array $data ) {
        return $this->request( 'POST', $resource, $data );
    }

    /**
     * @param string $resource
     * @param array $data
     * @return mixed
     */
    public function put( $resource, array $data ) {
        return $this->request( 'PUT', $resource, $data );
    }

    /**
     * @param string $resource
     * @param array $data
     * @return mixed
     */
    public function patch( $resource, array $data ) {
        return $this->request( 'PATCH', $resource, $data );
    }

    /**
     * @param string $resource
     * @return mixed
     */
    public function delete( $resource ) {
        return $this->request( 'DELETE', $resource );
    }



    private function optionallyRaiseException($method, $resource, &$result, $servicemethod, array $args ) {
        if ($result == false && service_errorcode() != 0) {
            // gestione speciale per l'utente che non esiste
            if (service_errorcode() == ENS_ERROR_USER_NOT_EXISTS) {
                throw new NL4WP_API_Resource_Not_Found_Exception( "Utente non trovato", "404", $servicemethod, service_errorcode(), $args );
            }

            $this->get_log()->warning( sprintf( "CALL to %s %s (%s) failed: [%s] %s", $method, $resource, $servicemethod, service_errorcode(), service_errormessage() ) );
            // 8
            if (service_errorcode() == 8) throw new NL4WP_API_Connection_Exception( service_errormessage(), service_errorcode(), $servicemethod, null, $args );
            throw new NL4WP_API_Exception( service_errormessage(), service_errorcode(), $servicemethod, null, $args );
        } else {
            // $this->get_log()->debug( sprintf( "CALL to %s %s (%s) succeded with result: '%s' [%s] %s", $method, $resource, $servicemethod, print_r($result, true), service_errorcode(), service_errormessage() ) );
        }
    }

    private function init($listid) {
        if (count($this->api_keys) < $listid) {
            throw new NL4WP_API_Exception( "Unconfigured list: " . $listid, 005 );
        }
        service_init($this->api_keys[$listid - 1]);

        // default to jsonwp calls/protocol
        if ($GLOBALS['service_hint'] == 'auto' && function_exists('json_encode') && function_exists('curl_version')) {
            $GLOBALS['service_hint'] = 'jsonwp';
        }
    }

    /**
     * @param string $method
     * @param string $resource
     * @param array $data
     *
     * @return mixed
     *
     * @throws NL4WP_API_Exception
     */
    private function request( $method, $resource, array $args = array() ) {
        global $wp_version;
        $this->reset();

        // don't bother if no API key was given.
        if(count($this->api_keys) == 0 || empty($this->api_keys[0]))
            throw new NL4WP_API_Exception( "Missing API key", 001 );

        $GLOBALS['service_wrapper_uaprefix'] = 'nl4wp/' . NL4WP_VERSION . ' wp/' . $wp_version . ' php/' . phpversion() . ' ';

        
        if ($resource[0] !== '/') {
            throw new NL4WP_API_Exception( "Unsupported call: " . $resource, 002 );
        }

        $respath = explode('/', substr($resource, 1));

        // resource == "/"
        if (empty($respath[0])) {

            if ($args !== array('fields' => 'account_id') /* 4.5.5 */ && $args !== array() /* 4.1.15 */)
            {
                throw new NL4WP_API_Exception( 'Unexpected arguments for '.$method.' '.$resource, 999, false, false, $args ); // da verificare
            }

            // non viene realmente usato, ma qui di fatto controlliamo tutte le chiavi API
            $res = array();
            for($list_id = 1; $list_id <= count($this->api_keys); $list_id++) {
                $this->get_log()->debug( sprintf( "CALL service_info %s", print_r($args, true) ) );
                $this->init($list_id); // inizializzazione API service
                $result = service_info();
                $this->optionallyRaiseException($method, $resource, $result, 'service_info', $args);
                $res[] = $result['uniqid'];
            }

            return (object) array('account_id' => implode(',', $res));


        } else if ($respath[0] == 'lists') {

            // resource  "/lists"
            if (count($respath) == 1) {

                $ff = explode(',', !empty($args['fields']) ? $args['fields'] : 'id');
                $exposecount = in_array('total_items', $ff);

                $ret = (object) array('lists'=> array());
                for($list_id = 1; $list_id <= count($this->api_keys); $list_id++) {
                    $ret->lists[] = $this->fetch_list_data($list_id, $args, $method, $resource);
                }
                if ($exposecount) $ret->total_items = count($ret->lists);

                return $ret;

            // resource  "/lists/#LISTID#..."
            } else if (is_numeric($respath[1])) {

                $list_id = $respath[1];

                // resource "/lists/#LISTID#"
                if (count($respath) == 2) {
                    return $this->fetch_list_data($list_id, $args, $method, $resource);

                // resource "/lists/#LISTID#/"
                } else {

                    // resource "/lists/#LISTID#/merge-fields"
                    if ($respath[2] == 'merge-fields') {

                        return $this->fetch_merge_fields($list_id, $args, $method, $resource);

                    } else if ($respath[2] == 'interest-categories') {


                        // resource "/lists/#LISTID#/interest-categories"
                        if (count($respath) == 3) {

                            return $this->fetch_interest_categories($list_id, $args, $method, $resource);

                        // resource "/lists/#LISTID#/interest-categories/1/interests"
                        } else if (count($respath) == 5 && $respath[3] == 1) {

                            return $this->fetch_interest_categories_interests($list_id, $args, $method, $resource);

                        }

                    // resource "/lists/#LISTID#/members"
                    } else if ($respath[2] == 'members' && count($respath) == 3) {
                        return $this->handle_lists_members_collection_call($list_id, $args, $method, $resource);

                    // resource "/lists/#LISTID#/members/#UID#"
                    } else if ($respath[2] == 'members' && count($respath) == 4) {

                        // gestione ad hoc della richiesta di check utente
                        $emailtocheck = base64_decode($respath[3]);
                        return $this->handle_lists_members_call($list_id, $emailtocheck, $args, $method, $resource);

                    // resource "/lists/#LISTID#/members/#UID#/tags"
                    } else if ($respath[2] == 'members' && count($respath) == 5 && $respath[4] == 'tags') {
                        $emailtocheck = base64_decode($respath[3]);
                        return $this->handle_lists_member_tags_call($list_id, $emailtocheck, $args, $method, $resource);

                    // resource "/lists/#LISTID#/members/#UID#/actions/delete-permanent"
                    } else if ($respath[2] == 'members' && count($respath) == 6 && $respath[4] == 'actions' && $respath[5] == 'delete-permanent') {
                        $emailtocheck = base64_decode($respath[3]);
                        return $this->handle_lists_members_delete_call($list_id, $emailtocheck, $args, $method, $resource);

                    }
                }
            }
        }


        throw new NL4WP_API_Exception( "Unsupported call: " . $resource, 003 );
    }

    // fetch list data
    private function fetch_list_data($list_id, &$args, $method, $resource) {
        foreach ($args as $k => $v) if ($k !== 'count' && $k !== 'fields' && $k !== 'offset') {
            throw new NL4WP_API_Exception( 'Unexpected arguments for '.$method.' '.$resource.': (supported count and fields)', 999, false, false, $args ); // da verificare
        }

        // $groups = service_audience_list();
        // $groups = array_filter($groups, function($v) {
        //             return $v["handler"] == "servicesubscription"; //visibility register??
        //             });

        $this->get_log()->debug( sprintf( "CALL service_info for fetch_list_data %s", print_r($args, true) ) );
        $this->init($list_id); // inizializzazione API service
        $result = service_info();
        $this->optionallyRaiseException($method, $resource, $result, 'service_info', $args);

        $ret= (object) array();

        $ff = explode(',', !empty($args['fields']) ? $args['fields'] : 'id');
        $exposecount = false;
        if (empty($args['offset'])) foreach($ff as $f) {
            switch($f) {
                case 'total_items': $exposecount = true; break;
                case 'id': case 'lists.id': $ret->id = $list_id; break;
                case 'name': case 'lists.name': $ret->name = 'Lista ' . $result['host']; break;
                case 'stats': case 'lists.stats': case 'lists.stats.member_count': case 'lists.stats.merge_field_count':
                    if (!property_exists($ret, 'stats')) $ret->stats = (object) array();
                    if ($f == 'stats' || $f == 'lists.stats' || $f == 'lists.stats.member_count') {
                        $ret->stats->member_count = $result['users_count'];
                    }
                    if ($f == 'stats' || $f == 'lists.stats' || $f == 'lists.stats.merge_field_count') {
                        $this->get_log()->debug( sprintf( "CALL service_user_profile_fields_list for fetch_list_data %s", print_r($args, true) ) );
                        $fc = service_user_profile_fields_list();
                        $this->optionallyRaiseException($method, $resource, $fc, 'service_user_profile_fields_list', $args);
                        $ret->stats->merge_field_count = is_array($fc) ? count($fc) : 0;
                    }
                    break;
                case 'web_id': case 'lists.web_id': $ret->web_id = '1'; break; // non applicabile
                // fino a nl4wp 4.5.5 venivano chiesti, ma non utilizzati, quindi li popolo casualmente.
                case 'campaign_defaults.from_name': 
                case 'campaign_defaults.from_email': 
                    if (!property_exists($ret, 'campaign_defaults')) $ret->campaign_defaults = (object) array();
                    if ($f == 'campaign_defaults.from_name') $ret->campaign_defaults->from_name = 'Mittente';
                    if ($f == 'campaign_defaults.from_email') $ret->campaign_defaults->from_email = 'mittente@example.com';
                    break;
                case 'lists.marketing_permissions': /* Whether or not the list has marketing permissions (eg. GDPR) enabled. */
                    // Voxmail has no Newsletter-style, per-contact marketing permissions.
                    // Expose the list as a valid audience without GDPR permissions.
                    $ret->marketing_permissions = false;
                    break;
                default: throw new NL4WP_API_Exception( 'Unsupported field for '.$resource.': '.$f, 999, false, false, $args );
            }
        };

        return $ret;
    }

    private function fetch_merge_fields($list_id, &$args, $method, $resource) {
        $this->get_log()->debug( sprintf( "CALL service_user_profile_fields_list for fetch_merge_fields %s", print_r($args, true) ) );
        $this->init($list_id); // inizializzazione API service
        $result = service_user_profile_fields_list();
        $this->optionallyRaiseException($method, $resource, $result, 'service_user_profile_fields_list', $args);

        $merge_vars = array();
        $id = 1;
        if (is_array($result)) {
            foreach ($result as $merge_var) {
                if ($merge_var['visibility'] == 'hidden' || $merge_var['visibility'] =='public' || $merge_var['visibility'] == 'register' || $merge_var['visibility'] == 'register_required') {

                    if ($merge_var['type']=='checkbox')
                        $merge_var['options']=$merge_var['title'];
                        
                    $mv = (object) array(
                        'id' => $id++,
                        'required' => $merge_var['visibility'] == 'register_required' ? 1 : 0,
                        'name' => $merge_var['title']
                    );

                    switch($merge_var['name']) {
                        case 'profile_name': $mv->tag = 'FNAME'; break;
                        case 'profile_surname': $mv->tag = 'LNAME'; break;
                        default: $mv->tag = $merge_var['name']; break;
                    }

                    $mv->helptext = $merge_var['description'];

                    switch($merge_var['type']) {
                        case 'selection': $mv->type = 'dropdown'; break;
                        case 'textfield': $mv->type = 'text'; break;
                        case 'checkbox': $mv->type = 'checkboxes'; break;
                        default: $mv->type = $merge_var['type'];
                    }

                    $mv->public = ($merge_var['visibility'] !== 'hidden') ? 1 : 0;

                    if (isset($merge_var['options']))
                        $mv->options = (object) array('choices' => $merge_var['options'] ? explode("\n", $merge_var['options']) : array());

                    $merge_vars[] = $mv;
                }
            }
        }

        // Voxmail's enable-tracking switch is not exposed as a profile field.
        // Publish it as a synthetic single-choice field so NL4WP can expose it
        // in forms and integrations. The values must be non-empty strings:
        // NL4WP intentionally drops empty values while mapping submissions.
        $merge_vars[] = (object) array(
            'id'       => $id++,
            'required' => 0,
            'name'     => __('Enable tracking', 'newsletter-for-wp'),
            'tag'      => self::TRACK_ENABLE_TAG,
            'helptext' => __('Select whether email tracking should be enabled for this subscriber.', 'newsletter-for-wp'),
            'type'     => 'radio',
            'options'  => (object) array(
                'choices' => array(
                    'yes' => __('Yes', 'newsletter-for-wp'),
                    'no'  => __('No', 'newsletter-for-wp'),
                ),
            ),
            'public'   => 1,
        );

        return (object) array('merge_fields' => $merge_vars);
    }

    private function fetch_interest_categories($list_id, &$args, $method, $resource) {
        $sub = $this->fetch_interest_categories_interests($list_id, $args, $method, $resource);

        $ret = (object) array();
        if (count($sub->interests)) $ret->categories = array((object) array(
            'id' => 1, 
            'title' => "Gruppi", 
            'type' => 'checkboxes'
        ));

        return $ret;
    }

    private function fetch_interest_categories_interests($list_id, &$args, $method, $resource) {
        $this->get_log()->debug( sprintf( "CALL service_audience_list for fetch_interest_categories_interests %s", print_r($args, true) ) );
        $this->init($list_id); // inizializzazione API service
        $groups = service_audience_list();
        $this->optionallyRaiseException($method, $resource, $groups, 'service_audience_list', $args);

        if (is_array($groups)) {
            $groups = array_filter($groups, function($v) {
                return $v["visibility"] == "register"; //visibility register??
            });
            
            $interests = array();
            foreach ($groups as $group) {
                $interests[] = (object) array(
                    "id" => $group["aid"],
                    "name" => $group["caption"]
                );  
            }
            return (object) array('interests'=> $interests); 
        }

        return (object) array('interests' => []);
    }

    /**
     * Handle the collection endpoint used by Newsletter 4.9.x.
     *
     * Newsletter uses GET /members to discover the marketing permission
     * definitions and POST /members to create a new subscriber. Voxmail has
     * no equivalent marketing-permissions model, so the GET response is an
     * empty member collection and the POST path creates the user directly.
     */
    private function handle_lists_members_collection_call($list_id, &$args, $method, $resource) {
        $this->init($list_id);

        if ($method === 'GET') {
            $ret = (object) array('members' => array());
            $fields = isset($args['fields']) ? $args['fields'] : '';
            $fields = is_array($fields) ? implode(',', $fields) : (string) $fields;
            if (strpos($fields, 'total_items') !== false) {
                $ret->total_items = 0;
            }
            return $ret;
        }

        if ($method !== 'POST') {
            throw new NL4WP_API_Exception( "Unsupported method " . $method . " for " . $resource, 004 );
        }

        $email = isset($args['email_address']) ? $args['email_address'] : '';
        if ($email === '') {
            throw new NL4WP_API_Exception( "Missing email address for " . $resource, 999, false, false, $args );
        }

        $data_to = $this->build_user_data($args, $email, true);
        $status = isset($args['status']) ? $args['status'] : 'pending';
        if ($status === 'pending') {
            $servicemethod = 'service_user_subscribe';
            $result = service_user_subscribe($data_to, !empty($args['ip_signup']) ? $args['ip_signup'] : false);
        } elseif ($status === 'subscribed') {
            $servicemethod = 'service_user_create';
            $result = service_user_create($data_to);
        } else {
            throw new NL4WP_API_Exception( "Unsupported subscriber status " . $status, 999, false, false, $args );
        }

        $this->assert_service_result($method, $resource, $result, $servicemethod, $args);
        return (object) (array('id' => $result) + $args);
    }

    private function build_user_data($args, $emailtocheck = '', $include_tags = true, $include_removed_interests = false) {
        $email = !empty($args['email_address']) ? $args['email_address'] : $emailtocheck;
        $data_to = array(
            'mail' => $email,
            'privacy' => 1, // Voxmail has one general privacy flag, not Newsletter marketing permissions.
        );

        $merge_fields = !empty($args['merge_fields']) ? (array) $args['merge_fields'] : array();
        foreach ($merge_fields as $key => $value) {
            if (strtoupper((string) $key) === self::TRACK_ENABLE_TAG) {
                $tracking_enabled = $this->normalize_tracking_enable($value);
                if ($tracking_enabled !== null) {
                    $data_to['track_disable'] = !$tracking_enabled;
                }
                continue;
            }

            switch ($key) {
                case 'FNAME':
                    $data_to['profile_name'] = $value;
                    break;
                case 'LNAME':
                    $data_to['profile_surname'] = $value;
                    break;
                default:
                    if (is_array($value)) {
                        $value = 1;
                    }
                    $data_to[strtolower($key)] = $value;
                    break;
            }
        }

        $audiences = array();
        $interests = !empty($args['interests']) ? (array) $args['interests'] : array();
        foreach ($interests as $key => $value) {
            if ($value) {
                $audiences[] = (string) $key;
            } elseif ($include_removed_interests) {
                if (!isset($data_to['-audiences'])) {
                    $data_to['-audiences'] = array();
                }
                $data_to['-audiences'][] = (string) $key;
            }
        }

        // There is no separate Voxmail tag model in this adapter. Preserve the
        // existing project behaviour by representing active tags as audiences.
        if ($include_tags && !empty($args['tags'])) {
            foreach ((array) $args['tags'] as $value) {
                if (is_array($value)) {
                    $value = isset($value['name']) ? $value['name'] : '';
                }
                if ($value) {
                    $audiences[] = (string) $value;
                }
            }
        }

        if (!empty($audiences)) {
            $data_to['audiences'] = implode(',', array_unique($audiences));
        }
        if (!empty($data_to['-audiences'])) {
            $data_to['-audiences'] = implode(',', array_unique($data_to['-audiences']));
        }

        // marketing_permissions is intentionally ignored: Voxmail has no
        // equivalent per-contact permission model.
        return $data_to;
    }

    /**
     * Normalize the synthetic TRACK_ENABLE merge field.
     *
     * Keep the result nullable: an omitted or unknown value must not change
     * an existing Voxmail subscriber, and an omitted value lets Voxmail apply
     * its default (tracking enabled).
     *
     * @param mixed $value
     * @return bool|null
     */
    private function normalize_tracking_enable($value) {
        if (is_array($value)) {
            $value = reset($value);
        }

        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim((string) $value));
        if (in_array($value, array('yes', 'si', 'sì', 'true', 'enabled', 'enable', 'on', '1'), true)) {
            return true;
        }
        if (in_array($value, array('no', 'false', 'disabled', 'disable', 'off', '0'), true)) {
            return false;
        }

        return null;
    }

    private function assert_service_result($method, $resource, &$result, $servicemethod, array $args) {
        $this->optionallyRaiseException($method, $resource, $result, $servicemethod, $args);
        if ($result === false) {
            throw new NL4WP_API_Exception( 'Unexpected status in '.$method.' '.$resource.': '.service_errormessage(), service_errorcode(), false, false, $args );
        }
    }

    private function handle_lists_members_call($list_id, $emailtocheck, &$args, $method, $resource) {
        $this->get_log()->debug( sprintf( "%s %s %s", $method, $emailtocheck, print_r($args, true) ) );
        $this->init($list_id); // inizializzazione API service

        $ip = !empty($args['ip_signup']) ? $args['ip_signup'] : false;
        $status = isset($args['status']) ? $args['status'] : 'subscribed';

        switch ($method) {
            case 'GET':
                $result = service_user_load($emailtocheck);
                $this->optionallyRaiseException($method, $resource, $result, 'service_user_load', $args);

                if (is_array($result)) {
                    $interests = !empty($result['audiences']) ? explode(',', $result['audiences']) : array();
                    $inter = (object) array();
                    foreach ($interests as $value) {
                        if (!empty($value)) {
                            $inter->{$value} = 1;
                        }
                    }
                    return (object) array(
                        'id' => $result['uid'],
                        'email_address' => $result['mail'],
                        'unique_email_id' => $result['uid'],
                        'status' => isset($result['mail_disable']) && $result['mail_disable'] == 0 ? 'subscribed' : '',
                        'interests' => $inter,
                    );
                }
                throw new NL4WP_API_Exception( 'Unexpected status in '.$method.' '.$resource.': '.service_errormessage(), service_errorcode(), false, false, $args );

            case 'PUT':
                // Kept for callers using the pre-4.9.13 upsert path.
                $data_to = $this->build_user_data($args, $emailtocheck, true, true);
                if ($status === 'pending') {
                    $servicemethod = 'service_user_subscribe';
                    $result = service_user_subscribe($data_to, $ip);
                } elseif ($status === 'subscribed') {
                    $servicemethod = 'service_user_create';
                    $result = service_user_create($data_to);
                } else {
                    throw new NL4WP_API_Exception( "Unsupported subscriber status " . $status, 999, false, false, $args );
                }

                $this->optionallyRaiseException($method, $resource, $result, $servicemethod, $args);
                if ($result === false && $status === 'subscribed') {
                    $result = service_user_update($data_to['mail'], $data_to);
                    $this->assert_service_result($method, $resource, $result, 'service_user_update', $args);
                } else {
                    $this->assert_service_result($method, $resource, $result, $servicemethod, $args);
                }
                return (object) (array('id' => $result) + $args);

            case 'PATCH':
                if ($status === 'unsubscribed') {
                    $result = service_user_unsubscribe($emailtocheck, $ip);
                    $this->assert_service_result($method, $resource, $result, 'service_user_unsubscribe', $args);
                    return (object) (array('id' => $result) + $args);
                }

                $data_to = $this->build_user_data($args, $emailtocheck, false, true);
                if ($status === 'pending') {
                    $result = service_user_subscribe($data_to, $ip);
                    $servicemethod = 'service_user_subscribe';
                } else {
                    $result = service_user_update($emailtocheck, $data_to);
                    $servicemethod = 'service_user_update';
                }
                $this->assert_service_result($method, $resource, $result, $servicemethod, $args);
                return (object) (array('id' => $result) + $args);

            case 'DELETE':
                return $this->handle_lists_members_delete_call($list_id, $emailtocheck, $args, $method, $resource);
        }

        throw new NL4WP_API_Exception( "Unsupported method " . $method . " for " . $resource, 004 );
    }

    private function handle_lists_members_delete_call($list_id, $emailtocheck, &$args, $method, $resource) {
        $this->init($list_id);

        try {
            $result = service_user_unsubscribe($emailtocheck, !empty($args['ip_signup']) ? $args['ip_signup'] : false);
            $this->assert_service_result($method, $resource, $result, 'service_user_unsubscribe', $args);
        } catch (NL4WP_API_Resource_Not_Found_Exception $e) {
            // The requested contact is already gone; DELETE is idempotent.
            return (object) array('id' => 0);
        }

        $result = service_user_erase($emailtocheck);
        $this->assert_service_result($method, $resource, $result, 'service_user_erase', $args);
        return (object) array('id' => $result);
    }

    private function handle_lists_member_tags_call($list_id, $emailtocheck, &$args, $method, $resource) {
        $this->init($list_id);

        if ($method === 'GET') {
            $result = service_user_load($emailtocheck);
            $this->optionallyRaiseException($method, $resource, $result, 'service_user_load', $args);
            $tags = array();
            if (is_array($result) && !empty($result['audiences'])) {
                foreach (explode(',', $result['audiences']) as $tag) {
                    if ($tag !== '') {
                        $tags[] = (object) array('id' => $tag, 'name' => $tag);
                    }
                }
            }
            return (object) array('tags' => $tags, 'total_items' => count($tags));
        }

        if ($method !== 'POST') {
            throw new NL4WP_API_Exception( "Unsupported method " . $method . " for " . $resource, 004 );
        }

        $add = array();
        $remove = array();
        foreach (!empty($args['tags']) ? (array) $args['tags'] : array() as $tag) {
            if (is_array($tag)) {
                $name = isset($tag['name']) ? $tag['name'] : '';
                $status = isset($tag['status']) ? $tag['status'] : 'active';
            } else {
                $name = $tag;
                $status = 'active';
            }
            if ($name === '') {
                continue;
            }
            if ($status === 'inactive') {
                $remove[] = $name;
            } else {
                $add[] = $name;
            }
        }

        $data_to = array();
        if (!empty($add)) {
            $data_to['audiences'] = implode(',', array_unique($add));
        }
        if (!empty($remove)) {
            $data_to['-audiences'] = implode(',', array_unique($remove));
        }
        if (empty($data_to)) {
            return (object) array('tags' => array());
        }

        $result = service_user_update($emailtocheck, $data_to);
        $this->assert_service_result($method, $resource, $result, 'service_user_update', $args);
        return (object) array('tags' => !empty($args['tags']) ? $args['tags'] : array());
    }

    /**
     * Empties all data from previous response
     */
    private function reset() {
        $this->last_response = null;
    }

    /**
     * @return string
     */
    public function get_last_response_body() {
        return wp_remote_retrieve_body( $this->last_response );
    }

    /**
     * @return array
     */
    public function get_last_response_headers() {
        return wp_remote_retrieve_headers( $this->last_response );
    }

    /**
     * @return array|WP_Error
     */
    public function get_last_response() {
        return $this->last_response;
    }

    /**
     * @return NL4WP_Debug_Log
     */
    protected function get_log() {
        return nl4wp('log');
    }
}
