<?php
// API Wrapper v1.23(20180116)
//
// Compatible with PHP4+ with HASH Cryptography extension (PHP >5.1.2)
// or the MHASH Cryptography extension.
//
// Uses PHP xmlrpc extension http://php.net/manual/en/book.xmlrpc.php
// Or the xmlrpc library at http://phpxmlrpc.sourceforge.net/
//

//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions
// are met:
//
//    * Redistributions of source code must retain the above copyright
//      notice, this list of conditions and the following disclaimer.
//
//    * Redistributions in binary form must reproduce the above
//      copyright notice, this list of conditions and the following
//      disclaimer in the documentation and/or other materials provided
//      with the distribution.
//
//    * Neither the name of the "XML-RPC for PHP" nor the names of its
//      contributors may be used to endorse or promote products derived
//      from this software without specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
// "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
// LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
// FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
// REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
// INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
// (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
// SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
// HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
// STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
// ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
// OF THE POSSIBILITY OF SUCH DAMAGE. 

if (!class_exists('xmlrpc_client'))
  @include_once('xmlrpc.inc');

// Settings
if (!isset($GLOBALS['service_wrapper_debug']))
  $GLOBALS['service_wrapper_debug'] = 0;
if (!isset($GLOBALS['service_wrapper_timeout']))
  $GLOBALS['service_wrapper_timeout'] = 30;
if (!isset($GLOBALS['service_wrapper_method']))
  $GLOBALS['service_wrapper_method'] = "http11";
if (!isset($GLOBALS['service_wrapper_url']))
  $GLOBALS['service_wrapper_url'] = "services/xmlrpc";
if (!isset($GLOBALS['service_wrapper_time_offset']))
  $GLOBALS['service_wrapper_time_offset'] = 0;

if (!function_exists('service_version')) {

// Constants
define('ENS_ERROR_WRONG_PARAMETER_COUNT', -32602);
define('ENS_ERROR_UNKNOWN', -99);
define('ENS_ERROR_INVALID_API_KEY', 11);
define('ENS_ERROR_TOKEN_EXPIRED', 12);
define('ENS_ERROR_TOKEN_PREVIOUSLY_USED', 13);
define('ENS_ERROR_UNSUPPORTED', 101);
define('ENS_ERROR_PERMISSION_DENIED', 102);
define('ENS_ERROR_INVALID_ARGUMENT', 103);
define('ENS_ERROR_NEWSLETTER_NOT_EXISTS', 201);
define('ENS_ERROR_NEWSLETTER_NOT_VALID', 202);
define('ENS_ERROR_NEWSLETTER_NOT_SENT', 203);
define('ENS_ERROR_NEWSLETTER_CANT_SEND', 204);
define('ENS_ERROR_NEWSLETTER_TOO_OLD', 205);
define('ENS_ERROR_USER_NOT_EXISTS', 301);
define('ENS_ERROR_USER_NOT_VALID', 302);
define('ENS_ERROR_USER_ID_NOT_FOUND', 303);
define('ENS_ERROR_AUDIENCE_NOT_EXISTS', 401);
define('ENS_ERROR_AUDIENCE_NOT_VALID', 402);
define('ENS_ERROR_INVALID_FROM', 502);


  function service_version() {
    return 1023;
  }

  function service_init($hostoruniquekey, $api_key = false, $secret = false) {
    global $service_host, $service_api_key, $service_secret;
    if ($api_key == false && $secret == false) {
      $keyparts = explode('-', $hostoruniquekey);
      $service_host = function_exists('hex2bin') ? hex2bin($keyparts[0]) : pack("H*" , $keyparts[0]);
      $service_api_key = $keyparts[1];
      $service_secret = $keyparts[2];
    } else {
      $service_host = $hostoruniquekey;
      $service_api_key = $api_key;
      $service_secret = $secret;
    }
  }

  function service_invoke($method) {
    global $service_host, $service_api_key, $service_secret, $service_last_result, $service_last_method, $service_last_args;
    $service_last_result = 0;
    $timestamp = time() + $GLOBALS['service_wrapper_time_offset'];
    $nonce = md5(mt_rand());
    $hash = function_exists('hash_hmac') ? 
      hash_hmac("sha256", $service_api_key.';'.$timestamp.';'.$nonce.';'.$method, $service_secret) :
      bin2hex(mhash(MHASH_SHA256, $service_api_key.';'.$timestamp.';'.$nonce.';'.$method, $service_secret));

    $args = func_get_args();
    array_shift($args);
    
    $service_last_method = $method; 
    $service_last_args = $args;

    $args = array_merge(array('API'.$service_api_key, $timestamp, $nonce, $hash), $args);

    if (class_exists('xmlrpc_client')) {    

      // 'xmlrpc.inc' from phpxmlrpc sourceforge library version 4.0 does not support the runtime GLOBAL encoding switch.
      // in order to keep this class syntax compatible with php <= 5.2.x we use Reflection here.
      $refclass = null;
      if (class_exists("PhpXmlRpc\PhpXmlRpc")) {
        $refclass = new ReflectionClass('PhpXmlRpc\PhpXmlRpc');
        $xmlrpc_internalencoding_save = $refclass->getStaticPropertyValue('xmlrpc_internalencoding');
        $refclass->setStaticPropertyValue('xmlrpc_internalencoding', 'UTF-8');
        $xmlrpcName_save = $refclass->getStaticPropertyValue('xmlrpcName');
        $refclass->setStaticPropertyValue('xmlrpcName', $GLOBALS['service_wrapper_uaprefix'].$refclass->getStaticPropertyValue('xmlrpcName'));
      } else {
        $xmlrpc_internalencoding_save = isset($GLOBALS['xmlrpc_internalencoding']) ? $GLOBALS['xmlrpc_internalencoding'] : null;
        $GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';
        $xmlrpcName_save = $GLOBALS['xmlrpcName'];
        $GLOBALS['xmlrpcName'] = $GLOBALS['service_wrapper_uaprefix'].$GLOBALS['xmlrpcName'];
      }
    
      $c = new xmlrpc_client($GLOBALS['service_wrapper_url'], $service_host, '', $GLOBALS['service_wrapper_method']);
      // $c->setSSLVerifyPeer(0);
      $c->setDebug($GLOBALS['service_wrapper_debug']);
      $c->return_type = "phpvals";
    
      foreach ($args as $k => $a)
        $args[$k] = php_xmlrpc_encode($a);
    
      $service_last_result_dec = $c->send(new xmlrpcmsg($method ,$args), $GLOBALS['service_wrapper_timeout']);
    
      if ($refclass !== null) {
        $refclass->setStaticPropertyValue('xmlrpc_internalencoding', $xmlrpc_internalencoding_save);
        $refclass->setStaticPropertyValue('xmlrpcName', $xmlrpcName_save);
      } else {
        if (is_null($xmlrpc_internalencoding_save))
          unset($GLOBALS['xmlrpc_internalencoding']);
        else
          $GLOBALS['xmlrpc_internalencoding'] = $xmlrpc_internalencoding_save;
        $GLOBALS['xmlrpcName'] = $xmlrpcName_save;
      }

      $service_last_result = array(
        'faultCode' => $service_last_result_dec->faultCode(),
        'faultString' => $service_last_result_dec->faultString()
      );

      if (!$service_last_result_dec->faultCode()) {
        $service_last_result['value'] = $service_last_result_dec->value();
      }

    } else if (function_exists('xmlrpc_encode_request')) {

      $request = xmlrpc_encode_request($method,$args,array(
        'encoding' => 'utf-8',
        'escaping' => 'markup',
        'verbosity' => 'no_white_space',
      ));
      $context = stream_context_create(array('http' => array(
        'method' => 'POST',
        'header' => "Content-Type: text/xml; charset=UTF-8\r\n".
                    "User-Agent: ".$GLOBALS['service_wrapper_uaprefix']."php-xmlrpc-ext/".phpversion("xmlrpc")."\r\n",
        'content' => $request,
        'timeout' => $GLOBALS['service_wrapper_timeout'],
      )));

      $service_last_result_raw = file_get_contents('http://'.$service_host.'/'.$GLOBALS['service_wrapper_url'], false, $context);

      if ($service_last_result_raw == false) {
        $service_last_result = array(
          'faultCode' => 8,
          'faultString' => 'Remote request timed-out or failed'
        );
      } else {
        $service_last_result_dec = xmlrpc_decode($service_last_result_raw, 'utf-8');

        if ($service_last_result_dec === null) {
          $service_last_result = array(
            'faultCode' => 9,
            'faultString' => 'Remote request cannot be parsed by xmlrpc_decode (length: '.strlen($service_last_result_raw).')'
          );
        } else {
          if (isset($service_last_result_dec['faultCode'])) {
            $service_last_result = $service_last_result_dec;
          } else {
            $service_last_result = array(
              'faultCode' => 0,
              'faultString' => '',
              'value' => $service_last_result_dec
            );
          }
        }
      }

    } else {

      $service_last_result = array(
        'faultCode' => ENS_ERROR_UNKNOWN,
        'faultString' => 'No XMLRPC library found. Please enable xmlrpc PHP extension or download xmlrpc.inc from http://phpxmlrpc.sourceforge.net/'
      );

    }
   
    return !$service_last_result['faultCode'] ? $service_last_result['value'] : false;
  }

  function service_errorcode() {
    global $service_last_result;
    return $service_last_result === 0 ? 0 : $service_last_result['faultCode'];
  }

  function service_errormessage() {
    global $service_last_result;
    return $service_last_result === 0 ? '' : $service_last_result['faultString'];
  }

  function service_last_method() {
    global $service_last_method;
    return $service_last_method;
  }

  function service_last_method_args() {
    global $service_last_args;
    return $service_last_args;
  }

}

if (!isset($GLOBALS['service_wrapper_uaprefix']))
  $GLOBALS['service_wrapper_uaprefix'] = "service-wrapper/".service_version()." ";
function service_info() {
  return service_invoke('service.info');
}

function service_newsletter_load($nid, $fields = array ()) {
  return service_invoke('service.newsletter.load', $nid, $fields);
}

function service_newsletter_create($data) {
  return service_invoke('service.newsletter.create', $data);
}

function service_newsletter_update($nid, $data) {
  return service_invoke('service.newsletter.update', $nid, $data);
}

function service_newsletter_delete($nid) {
  return service_invoke('service.newsletter.delete', $nid);
}

function service_newsletter_duplicate($nid) {
  return service_invoke('service.newsletter.duplicate', $nid);
}

function service_newsletter_check($nid) {
  return service_invoke('service.newsletter.check', $nid);
}

function service_newsletter_send($nid, $timestamp = 0) {
  return service_invoke('service.newsletter.send', $nid, $timestamp);
}

function service_newsletter_csend($data, $timestamp = 0) {
  return service_invoke('service.newsletter.csend', $data, $timestamp);
}

function service_newsletter_send_test($nid, $to = false) {
  return service_invoke('service.newsletter.send_test', $nid, $to);
}

function service_newsletter_results($nid, $section = '') {
  return service_invoke('service.newsletter.results', $nid, $section);
}

function service_newsletter_results_users($nid, $filters = array (), $order = '', $pageLength = 0, $pageNo = 0) {
  return service_invoke('service.newsletter.results_users', $nid, $filters, $order, $pageLength, $pageNo);
}

function service_newsletter_list($filters = array (), $order = '', $pageLength = 0, $pageNo = 0) {
  return service_invoke('service.newsletter.list', $filters, $order, $pageLength, $pageNo);
}

function service_user_load($uid_mail, $fields = array ()) {
  return service_invoke('service.user.load', $uid_mail, $fields);
}

function service_user_login($uid_mail, $pass, $fields = array ()) {
  return service_invoke('service.user.login', $uid_mail, $pass, $fields);
}

function service_user_subscribe($data, $ip = false) {
  return service_invoke('service.user.subscribe', $data, $ip);
}

function service_user_unsubscribe($uid_mail, $ip = false) {
  return service_invoke('service.user.unsubscribe', $uid_mail, $ip);
}

function service_user_disable_mail($uid_mail, $type = 'admin', $ip = '') {
  return service_invoke('service.user.disable_mail', $uid_mail, $type, $ip);
}

function service_user_enable_mail($uid_mail, $ip = '') {
  return service_invoke('service.user.enable_mail', $uid_mail, $ip);
}

function service_user_create($data) {
  return service_invoke('service.user.create', $data);
}

function service_user_update($uid_mail, $data, $create_if_not_exists = false) {
  return service_invoke('service.user.update', $uid_mail, $data, $create_if_not_exists);
}

function service_user_erase($uid_mail) {
  return service_invoke('service.user.erase', $uid_mail);
}

function service_user_list($filters = array (), $order = '', $pageLength = 0, $pageNo = 0) {
  return service_invoke('service.user.list', $filters, $order, $pageLength, $pageNo);
}

function service_user_count($filters = array ()) {
  return service_invoke('service.user.count', $filters);
}

function service_user_profile_fields_list() {
  return service_invoke('service.user.profile_fields.list');
}

function service_audience_reset($aidlist) {
  return service_invoke('service.audience.reset', $aidlist);
}

function service_audience_list($filters = array ()) {
  return service_invoke('service.audience.list', $filters);
}

function service_audience_create($data) {
  return service_invoke('service.audience.create', $data);
}

function service_audience_delete($aid) {
  return service_invoke('service.audience.delete', $aid);
}

