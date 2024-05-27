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
            throw new NL4WP_API_Exception( "Unconfigured list: " . $resource, 005 );
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
                throw new NL4WP_API_Exception( 'Unexpected arguments for '.$mthod.' '.$resource, 999, false, false, $args ); // da verificare
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

                    // resource "/lists/#LISTID#/members/#UID#"
                    } else if ($respath[2] == 'members' && count($respath) == 4) {

                        // gestione ad hoc della richiesta di check utente
                        $emailtocheck = base64_decode($respath[3]);
                        return $this->handle_lists_members_call($list_id, $emailtocheck, $args, $method, $resource);

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
                    $ret->marketing_permissions = true;
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
         
        if ($result) {
            $merge_vars = array();
            $id = 1;
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
                        $mv->options = (object) array('choices' => $merge_var['options'] ? explode("\n", $merge_var['options']) : '');

                    $merge_vars[] = $mv;
                }
            }
            return (object) array('merge_fields' => $merge_vars);
        } else return (object) array();
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

    private function handle_lists_members_call($list_id, $emailtocheck, &$args, $method, $resource) {
        $this->get_log()->debug( sprintf( "%s %s %s", $method, $emailtocheck, print_r($args, true) ) );
        $this->init($list_id); // inizializzazione API service

        $ip = !empty($args['ip_signup']) ? $args['ip_signup'] : false;
        switch ($method) {

            case 'GET':
                $result = service_user_load($emailtocheck);
                $this->optionallyRaiseException($method, $resource, $result, 'service_user_load', $args);

                //check esistenza
                if (is_array($result)) {
                    // gestione gruppi
                    $interests=explode(',', $result['audiences']);
                    $inter = (object) array();
                    foreach ($interests as $value) if (!empty($value)) $inter->{$value} = 1;
                    $ret = (object) array(
                        'id' => $result['uid'], 
                        'email_address' => $result['mail'], 
                        'unique_email_id' => $result['uid'], 
                        'status' => $result['mail_disable'] == 0 ? 'subscribed' : '',
                        'interests' => $inter
                    );
                    return $ret;
                } else throw new NL4WP_API_Exception( 'Unexpected status in '.$method.' '.$resource.': '.service_errormessage(), service_errorcode(), false, false, $args );
            break;

            case 'PUT':
                $data_to = array('mail' => $args['email_address']);
                foreach($args['merge_fields'] as $key => $value) switch ($key) {
                    case 'FNAME': $data_to['profile_name'] = $value; break;
                    case 'LNAME': $data_to['profile_surname'] = $value; break;
                    default:
                        if (is_array($value)) $value = 1;
                        $data_to[strtolower($key)] = $value;
                    break;
                }
                foreach ($args['interests'] as $key => $value) if ($value)
                    $data_to['audiences'] = $key . (!empty($data_to['audiences']) ? ','.$data_to['audiences'] : '');

                if (!empty($args['tags'])) foreach ($args['tags'] as $value) if ($value)
                    $data_to['audiences'] = $value.(!empty($data_to['audiences']) ? ','.$data_to['audiences'] : '');
                               
                $data_to['privacy'] = 1; // forzatura Privacy
                $method = 'user.subscribe';
                if ($args['status'] == 'pending') { //GESTIONE DOUBLE OPTIN!!
                    $this->get_log()->debug( sprintf( "CALL service_user_subscribe %s %s", print_r($data_to, true), $ip ) );
                    $result = service_user_subscribe($data_to, $ip);
                } else {
                    $this->get_log()->debug( sprintf( "CALL service_user_create %s", print_r($data_to, true) ) );
                    $result = service_user_create($data_to);
                    $method = 'user.create';
                }
                
                if (!$result) {
                    if ($args['status'] == 'subscribed') { //se arriva qui, vuol dire che l'utente è da aggiornare
                        // gestione degli interessi da togliere
                        foreach ($args['interests'] as $key => $value) if (!$value)
                            $data_to['-audiences']=$key.','.$data_to['-audiences'];

                        $this->get_log()->debug( sprintf( "CALL service_user_update %s %s", $data_to["mail"], print_r($data_to, true) ) );
                        $result = service_user_update($data_to["mail"],$data_to);
                        $this->optionallyRaiseException($method, $resource, $result, 'service_user_update', $args);

                    } else throw new NL4WP_API_Exception( 'Unexpected status in '.$method.' '.$resource.': '.service_errormessage(), service_errorcode(), false, false, $args );
                }
                $ret = array('id' => $result) + $args;
                return (object) $ret;
            break;

            case 'PATCH':
                if ($args['status'] == 'unsubscribed') { //al momento viene usato solo per la disicrizione
                    $this->get_log()->debug( sprintf( "CALL service_user_unsubscribe %s %s", $emailtocheck, $ip ) );
                    $result = service_user_unsubscribe($emailtocheck, $ip);
                    $this->optionallyRaiseException($method, $resource, $result, 'service_user_unsubscribe', $args);

                    $ret = array('id' => $result) + $args;
                    return (object) $ret;
                }
            break;

            /*
                case 'DELETE'  per la cancellazione ( vedi delete_list_member )
                NL4WP_API_Resource_Not_Found_Exception  se non esiste
                NL4WP_API_Exception   se non riesce
                si aspetta un void, quindi non importa che ritorno nulla.
            */
        }

        throw new NL4WP_API_Exception( "Unsupported method " . $method . " for " . $resource, 004 );
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
