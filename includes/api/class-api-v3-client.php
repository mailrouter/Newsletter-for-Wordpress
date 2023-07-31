<?php

if (!function_exists('service_info')) require "wrapper.php";

class NL4WP_API_v3_Client {
/** File da cambiare per il supporto API Newsletter **/
    /**
     * @var string
     */
    private $api_key;

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
        $this->api_key = $api_key;
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

    /**
     * @param string $method
     * @param string $resource
     * @param array $data
     *
     * @return mixed
     *
     * @throws NL4WP_API_Exception
     * NEWSLETTER unica funzione da cambiare.
     */
    private function request( $method, $resource, array $args = array() ) {
        global $wp_version;
        $this->reset();

        // don't bother if no API key was given.
        if(empty($this->api_key))
            throw new NL4WP_API_Exception( "Missing API key", 001 );

        $GLOBALS['service_wrapper_uaprefix'] = 'nl4wp/' . NL4WP_VERSION . ' wp/' . $wp_version . ' (+' . home_url() . ') ';

        service_init($this->api_key); // inizializzazione API service
        
        // gestione ad hoc della richiesta di check utente
        if (preg_match ('/(\/lists\/1\/members)\/([^\/]*)/', $resource, $matches)) {
            $resource=$matches[1];
            $emailtocheck=base64_decode($matches[2]);
        }

        // gestione delle varie richieste
        switch ($resource) {
            case '/':
                if ($args !== array('fields' => 'account_id') /* 4.5.5 */ && $args !== array() /* 4.1.15 */)
                {
                    throw new NL4WP_API_Exception( 'Unexpected arguments for '.$resource, 999, false, $args ); // da verificare
                }

                $this->get_log()->debug( sprintf( "CALL service_info %s", print_r($args, true) ) );
                $result=service_info();

                if ($result)
                    return (object) array('account_id' => $this->api_key);
                else 
                {
                    $this->get_log()->warning( sprintf( "CALL to service_info failed: %s", service_errormessage() ) );
                    throw new NL4WP_API_Exception( service_errormessage(), service_errorcode(), $result, $args ); // da verificare
                }
                
            break;
            case '/lists':
            case '/lists/1':

                foreach ($args as $k => $v) if ($k !== 'count' && $k !== 'fields' && $k !== 'offset') {
                    throw new NL4WP_API_Exception( 'Unexpected arguments for '.$resource.': (supported count and fields)', 999, false, $args ); // da verificare
                }

                // $groups = service_audience_list();
                // $groups = array_filter($groups, function($v) {
                //             return $v["handler"] == "servicesubscription"; //visibility register??
                //             });

                $ret= (object) array();


                $ff = explode(',', !empty($args['fields']) ? $args['fields'] : 'id');
                $exposecount = false;
                if (empty($args['offset'])) foreach($ff as $f) {
                    switch($f) {
                        case 'total_items': $exposecount = true; break;
                        case 'id': case 'lists.id': $ret->id = 1; break;
                        case 'name': case 'lists.name': $ret->name = 'Lista Contatti'; break;
                        case 'stats': case 'lists.stats': case 'lists.stats.member_count': case 'lists.stats.merge_field_count':
                            if (!property_exists($ret, 'stats')) $ret->stats = (object) array();
                            if ($f == 'stats' || $f == 'lists.stats' || $f == 'lists.stats.member_count') $ret->stats->member_count = service_user_count();
                            if ($f == 'stats' || $f == 'lists.stats' || $f == 'lists.stats.merge_field_count') $ret->stats->merge_field_count = count(service_user_profile_fields_list());
                            break;
                        case 'web_id': case 'lists.web_id': $ret->web_id = '1'; break; // non applicabile
                        // fino a nl4wp 4.5.5 venivano chiesti, ma non utilizzati, quindi li popolo casualmente.
                        case 'campaign_defaults.from_name': 
                        case 'campaign_defaults.from_email': 
                            if (!property_exists($ret, 'campaign_default')) $ret->campaign_default = (object) array();
                            if ($f == 'campaign_defaults.from_name') $ret->campaign_default->from_name = 'Mittente';
                            if ($f == 'campaign_defaults.from_email') $ret->campaign_default->from_email = 'mittente@example.com';
                            break;
                        case 'lists.marketing_permissions': /* Whether or not the list has marketing permissions (eg. GDPR) enabled. */
                            $ret->marketing_permissions = true;
                            break;
                        default: throw new NL4WP_API_Exception( 'Unsupported field for '.$resource.': '.$f, 999, false, $args );
                    }
                };

                if ($resource == '/lists') {
                    $ret = (object) array('lists'=> array($ret));
                    if ($exposecount) $ret->total_items = 1;
                }

                return $ret;
                
            break;
            case '/lists/1/merge-fields':
                $result=service_user_profile_fields_list();
                 
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
            break;
            case '/lists/1/interest-categories':
                $groups= service_audience_list();
                $groups= array_filter($groups, function($v) {
                            return $v["visibility"] == "register"; //visibility register??
                        });
                $ret = (object) array();
                if (count($groups) > 0)
                    $ret->categories = array((object) array('id' => 1, 'title' => "Gruppi", 'type' => 'checkboxes'));

                return $ret;                
            break;
            case '/lists/1/interest-categories/1/interests':
                $groups= service_audience_list();
                $groups= array_filter($groups, function($v) {
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
            break;
            case '/lists/1/members':
                $ip = !empty($args['ip_signup']) ? $args['ip_signup'] : false;
                switch ($method) {
                    case 'GET':
                        $this->get_log()->debug( sprintf( "GET %s %s", $emailtocheck, print_r($args, true) ) );
                        $result = service_user_load($emailtocheck);
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
                        }
                        else
                            throw new NL4WP_API_Resource_Not_Found_Exception( "Utente non trovato", "404", $result, $args );  // da mettere a posto
                    break;
                    case 'PUT':
                        $this->get_log()->debug( sprintf( "PUT %s %s", $emailtocheck, print_r($args, true) ) );

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
                                if (!$result) throw new NL4WP_API_Exception( service_errormessage(), service_errorcode(), $result, $args );
                            } else throw new NL4WP_API_Exception( 'Unexpected status after '.$method.': '.service_errormessage(), service_errorcode(), false, $args );
                        }
                        $ret = array('id' => $result) + $args;
                        return (object) $ret;
                    break;
                    case 'PATCH':
                        $this->get_log()->debug( sprintf( "PATCH %s %s", $emailtocheck, print_r($args, true) ) );
                        if ($args['status'] == 'unsubscribed') { //al momento viene usato solo per la disicrizione
                            $this->get_log()->debug( sprintf( "CALL service_user_unsubscribe %s %s", $emailtocheck, $ip ) );
                            $result = service_user_unsubscribe($emailtocheck, $ip);
                            if (!$result) throw new NL4WP_API_Exception( service_errormessage(), service_errorcode(), $result, $args );

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
                    default:
                        throw new NL4WP_API_Exception( "Unsupported method " . $method . " for " . $resource, 004 );
                }
            break;
            default: 
                throw new NL4WP_API_Exception( "Unsupported call: " . $resource, 002 );
        }
       
        throw new NL4WP_API_Exception( "Unsupported call: " . $resource, 003 );
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
