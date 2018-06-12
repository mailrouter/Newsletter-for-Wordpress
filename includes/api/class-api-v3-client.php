<?php
require "wrapper.php";
class NL4WP_API_v3_Client {
/** File da cambiare per il supporto API Newsletter **/
    /**
     * @var string
     */
    private $api_key;

    /**
     * @var string Non necessario
     */
    private $api_url = 'https://api.mailchimp.com/3.0/';

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

        $dash_position = strpos( $api_key, '-' );
        if( $dash_position !== false ) {
            $this->api_url = str_replace( '//api.', '//' . substr( $api_key, $dash_position + 1 ) . ".api.", $this->api_url );
        }
        
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
    private function request( $method, $resource, array $data = array() ) {
        $this->reset();

        // don't bother if no API key was given.
        if( empty( $this->api_key ) ) {
            throw new NL4WP_API_Exception( "Missing API key", 001 );
        }

        $GLOBALS['service_wrapper_uaprefix'] = 'newsletter-for-wp/'.NL4WP_VERSION.' ';
        service_init($this->api_key); // inizializzazione API service
        /* commentato
        $url = $this->api_url . ltrim( $resource, '/' );
        $args = array(
            'method' => $method,
            'headers' => $this->get_headers(),
            'timeout' => 10,
            'sslverify' => apply_filters( 'nl4wp_use_sslverify', true ),
        );*/
        //$this->get_log()->info( sprintf( "Richiesta: %s", $resource  ) );
        //$this->get_log()->info( sprintf( "Parametri richiesta: %s",  print_r($data,true)  ) );
        
        // attach arguments (in body or URL)
       /* commentato
        if( $method === 'GET' ) {
            $url = add_query_arg( $data, $url );
        } else {
            $args['body'] = json_encode( $data );
        }
        */
        // perform request
        //$response = wp_remote_request( $url, $args );
        //$this->last_response = $response;

        // parse response
        //$data = $this->parse_response( $response );
        
        // gestione ad hoc della richiesta di check utente
        if (preg_match ( '/(\/lists\/1\/members)\/(.*)/', $resource,$matches))
        {
            $resource=$matches[1];
            $emailtocheck=base64_decode($matches[2]);
        }
        // gestione delle varie richieste
        switch ($resource) {
            case '/':
                service_init( $this->api_key);
                $result=service_info();        
    
                if ($result) 
                {
                    $data=(object) array('account_id'=>$this->api_key);
                }
                else throw new NL4WP_API_Exception( service_errormessage(), service_errorcode(), $response, $data ); // da verificare
                
            break;
            case '/lists':
                $data =(object) array('lists'=>  array((object) array('id'=>1)));
            break;
            case '/lists/1':

                $groups= service_audience_list();
                $groups= array_filter($groups, function($v) {
                            return $v["handler"] == "servicesubscription"; //visibility register??
                            });

                $fields=service_user_profile_fields_list();
                $data=(object) array(
                    'id'=>1,
                    'name'=>'Lista Contatti',
                    'stats'=>(object) array(
                        'member_count'=>service_user_count(),
                        'merge_field_count' => count($fields),
                    )
                );
                
            break;
            case '/lists/1/merge-fields':
                $result=service_user_profile_fields_list();
                 
                if( $result ) {
                    
                    $merge_vars = array();
                    
                    foreach ($result as $merge_var) {
                        if ($merge_var['visibility']=='public'||$merge_var['visibility']=='register'||$merge_var['visibility']=='register_required'){
                            $merge_var['type']=$merge_var['type']=='selection'?'dropdown':$merge_var['type'];
                            $merge_var['type']=$merge_var['type']=='textfield'?'text':$merge_var['type'];
                            if ($merge_var['type']=='checkbox')
                            {
                                $merge_var['options']=$merge_var['title'];
                                $merge_var['type']="checkboxes";
                            }
                                
                            if ($merge_var['name']=='profile_name') {
                                $merge_var['name']='FNAME';
                            }
                            if ($merge_var['name']=='profile_surname') {
                                $merge_var['name']='LNAME';
                            }
                            $merge_vars[] = (object) array(
                                            'id' => $id++,
                                            'required' => $merge_var['visibility']=='register_required'?1:0,
                                            'name' => $merge_var['title'],      
                                            'tag' => $merge_var['name'],    
                                            'helptext' => $merge_var['description'],          
                                            'type' => $merge_var['type'],      
                                            'public' => ($merge_var['visibility']=='public'||$merge_var['visibility']=='register'||$merge_var['visibility']=='register_required')?1:0,
                                            'options' => (object) array('choices' => $merge_var['options']?explode("\n", $merge_var['options']):'')
                                        );
                        }
                    }
                    $data =(object) array('merge_fields'=>$merge_vars);
                }
            break;
            case '/lists/1/interest-categories':
                $groups= service_audience_list();
                $groups= array_filter($groups, function($v) {
                            return $v["visibility"] == "register"; //visibility register??
                        });
                if (count($groups)>0)
                    $data= (object) array('categories'=>array((object) array('id'=>1,'title'=>"Gruppi", 'type'=>'checkboxes')));
                
            break;
            case '/lists/1/interest-categories/1/interests':
                $groups= service_audience_list();
                $groups= array_filter($groups, function($v) {
                            return $v["visibility"] == "register"; //visibility register??
                        });
                
                $data = array();
                $interests = array();
                foreach ($groups as $group) {
                    $interests[] = (object) array(
                                            "id" => $group["aid"],
                                            "name" => $group["caption"]
                                        );  
                }
                $data= (object) array('interests'=> $interests); 
            break;
            case '/lists/1/members':
                switch ($method) {
                    case 'GET':
                        $result=service_user_load($emailtocheck);
                        //check esistenza
                        if (is_array($result))
                        { 
                            $status=$result['mail_disable']==0?'subscribed':'';
                            // gestione gruppi
                            $interests=explode(',',$result['audiences']);
                            foreach ($interests as $value) $inter[$value]=1;
                            $data= (object) array('id' => $result['uid'], 'email_address' => $result['mail'], 'unique_email_id' => $result['uid'], 'status' => $status, 'interests' => (object) $inter);
                        }
                        else
                            throw new NL4WP_API_Resource_Not_Found_Exception( "Utente non trovato", "404", $response, $data );  // da mettere a posto
                    break;
                    case 'PUT':
                        $data_to = array('mail' => $data['email_address']);
                       
                        foreach($data['merge_fields'] as $key=>$value)
                        {
                            switch ($key)
                            {
                                case 'FNAME':
                                    $data_to['profile_name']=$value;
                                break;
                                case 'LNAME':
                                    $data_to['profile_surname']=$value;
                                break;
                                default:
                                    if (is_array($value)) $value=1;
                                    $data_to[strtolower($key)]=$value;
                                break;
                            }
                                
                        }
                        foreach ($data['interests'] as $key => $value) 
                        {
                            if ($value)
                                $data_to['audiences']=$key.','.$data_to['audiences'];
                        }
                                       
                        $data_to['privacy']=1; // forzatura Privacy
                        // $this->get_log()->info( sprintf( "UTENTE VOX: %s",  print_r($data_to,true)  ) );
                        if ($data['status']=='pending')  //GESTIONE DOUBLE OPTIN!!
                            $result=service_user_subscribe($data_to);
                        else
                            $result=service_user_create($data_to);
                        
                        if (!$result) 
                        {
                            if ($data['status']=='subscribed') //se arriva qui, vuol dire che l'utente è da aggiornare
                            {
                                // gestione degli interessi da togliere
                                foreach ($data['interests'] as $key => $value) 
                                {    
                                    if (!$value)
                                        $data_to['-audiences']=$key.','.$data_to['-audiences'];
                                }

                                $result=service_user_update($data_to["mail"],$data_to);
                                if (!$result) throw new NL4WP_API_Exception( service_errormessage(), service_errorcode(), $response, $data ); // verificare
                            }
                            else throw new NL4WP_API_Exception( service_errormessage(), service_errorcode(), $response, $data ); // verificare
                        }
                        $data['id']=$result;
                    break;
                    case 'PATCH':
                        if ($data['status']=='unsubscribed') //al momento viene usato solo per la disicrizione
                        {
                            $result = service_user_unsubscribe($emailtocheck);
                            $data['id']=$result;
                            if (!$result) throw new NL4WP_API_Exception( service_errormessage(), service_errorcode(), $response, $data );
                        }
                    break;
                }
            break;
        }
       
        return (object) $data;
    }

    /**
     * @return array
     */
    private function get_headers() {
        global $wp_version;

        $headers = array();
        $headers['Authorization'] = 'Basic ' . base64_encode( 'nl4wp:' . $this->api_key );
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';
        $headers['User-Agent'] = 'nl4wp/' . NL4WP_VERSION . '; WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' );

        // Copy Accept-Language from browser headers
        if( ! empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
            $headers['Accept-Language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }

        return $headers;
    }

    /**
     * @param array|WP_Error $response
     *
     * @return mixed
     *
     * @throws NL4WP_API_Exception
     */
    private function parse_response( $response ) {

        if( $response instanceof WP_Error ) {
            throw new NL4WP_API_Connection_Exception( $response->get_error_message(), (int) $response->get_error_code() );
        }

        // decode response body
        $code = (int) wp_remote_retrieve_response_code( $response );
        $message = wp_remote_retrieve_response_message( $response );
        $body = wp_remote_retrieve_body( $response );

        // set body to "true" in case Newsletter returned No Content
        if( $code < 300 && empty( $body ) ) {
            $body = "true";
        }

        $data = json_decode( $body );
        if( $code >= 400 ) {
            if( $code === 404 ) {
                throw new NL4WP_API_Resource_Not_Found_Exception( $message, $code, $response, $data );
            }

            throw new NL4WP_API_Exception( $message, $code, $response, $data );
        }

        if( ! is_null( $data ) ) {
            return $data;
        }

        // unable to decode response
        $message = service_errormessage();
        $code = (int) service_errorcode();
        throw new NL4WP_API_Exception( $message, $code, $response );
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
