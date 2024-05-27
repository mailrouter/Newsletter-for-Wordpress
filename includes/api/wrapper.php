<?php
// API Wrapper v1.31(20240509)
//
// Compatible with PHP4+ with HASH Cryptography extension (PHP >5.1.2)
// or the MHASH Cryptography extension.
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


// Settings
if (!isset($GLOBALS['service_wrapper_debug']))
  $GLOBALS['service_wrapper_debug'] = 0;
if (!isset($GLOBALS['service_wrapper_timeout']))
  $GLOBALS['service_wrapper_timeout'] = 30;
if (!isset($GLOBALS['service_wrapper_method']))
  $GLOBALS['service_wrapper_method'] = "https"; // was http11 in older releases
if (!isset($GLOBALS['service_wrapper_url']))
  $GLOBALS['service_wrapper_url'] = "services/xmlrpc";
if (!isset($GLOBALS['service_wrapper_jsonrpc_url']))
  $GLOBALS['service_wrapper_jsonrpc_url'] = "services/jsonrpc";
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
define('ENS_ERROR_RESULT_TOO_BIG', 105);
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
    return 1031;
  }

  function service_init($hostoruniquekey, $api_key = false, $secret = false, $hint = 'auto') {
    global $service_host, $service_api_key, $service_secret, $service_hint;
    if ($api_key == false && $secret == false) {
      $keyparts = explode('-', $hostoruniquekey);
      $service_host = function_exists('hex2bin') ? hex2bin($keyparts[0]) : pack("H*" , $keyparts[0]);
      $service_api_key = $keyparts[1];
      $service_secret = $keyparts[2];
      $service_hint = count($keyparts) > 3 ? $keyparts[3] : $hint;
    } else {
      $service_host = $hostoruniquekey;
      $service_api_key = $api_key;
      $service_secret = $secret;
      $service_hint = $hint;
    }
  }

  function service_invoke_auto($method, $service_api_key, $timestamp, $nonce, $hash, $args) {
    if (!class_exists('xmlrpc_client') && !function_exists('xmlrpc_encode_request') && !function_exists('json_encode'))
      @include_once('xmlrpc.inc');

    if (class_exists('xmlrpc_client') && function_exists('curl_version')) {
      $service_last_result = service_invoke_xmlinc($method, $service_api_key, $timestamp, $nonce, $hash, $args);

    } else if (function_exists('xmlrpc_encode_request')) {
      $service_last_result = service_invoke_xmlext($method, $service_api_key, $timestamp, $nonce, $hash, $args);

    } else if (function_exists('json_encode')) {
      $service_last_result = service_invoke_json($method, $service_api_key, $timestamp, $nonce, $hash, $args);

    } else {
      $service_last_result = array(
        'faultCode' => ENS_ERROR_UNKNOWN,
        'faultString' => 'No client library found. Please enable allow_url_fopen, curl extension or xmlrpc PHP extension'
      );

    }

    return $service_last_result;
  }

  function service_invoke_xmlinc($method, $service_api_key, $timestamp, $nonce, $hash, $args) {
    global $service_host;

    if (!class_exists('xmlrpc_client'))
      require_once('xmlrpc.inc');

    $args = array_merge(array($service_api_key, $timestamp, $nonce, $hash), $args);
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
    
    $referer = service_get_referer();
    if (!empty($referer)) $c->SetCurlOptions(array(CURLOPT_REFERER => $referer));

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

    return $service_last_result;
  }

  function service_invoke_result($netfunc, $decodefunc, $url, $request, &$service_last_result_raw) {
    if ($GLOBALS['service_wrapper_debug']) {
      print("<PRE>\r\n");
      print(">>> " . $netfunc . "(" . $url .")\r\n");
      print(">>> " . $request . "\r\n");
      print("<<< " . ($service_last_result_raw ? $service_last_result_raw : "ERROR: " . print_r(error_get_last(), true)) . "\r\n");
      print("</PRE>\r\n");
    }

    if ($service_last_result_raw == false) {
      $error = error_get_last();
      $service_last_result = array(
        'faultCode' => 8,
        'faultString' => 'Remote request timed-out or failed: ' . $error['message']
      );
    } else {
      if ($decodefunc == 'json') $service_last_result_dec = json_decode($service_last_result_raw, true);
      elseif ($decodefunc == 'xmlrpc') $service_last_result_dec = xmlrpc_decode($service_last_result_raw, 'utf-8');

      if ($service_last_result_dec === null) {
        $service_last_result = array(
          'faultCode' => 9,
          'faultString' => 'Remote response cannot be decoded by ' . $decodefunc . '_decode (length: '.strlen($service_last_result_raw).')'
        );
      } else {
        if (isset($service_last_result_dec['faultCode']) && $service_last_result_dec['faultCode']) {
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
    return $service_last_result;
  }

  function service_get_referer() {
    if (isset($GLOBALS['service_wrapper_referer'])) return $GLOBALS['service_wrapper_referer'];
    if (!empty($_SERVER['HTTP_HOST'])) return ((empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    return false;
  }

  function service_invoke_xmlext($method, $service_api_key, $timestamp, $nonce, $hash, $args) {
    global $service_host;
    if (ini_get('allow_url_fopen') && function_exists('xmlrpc_encode_request')) {
      $args = array_merge(array($service_api_key, $timestamp, $nonce, $hash), $args);
      $request = xmlrpc_encode_request($method,$args,array(
        'encoding' => 'utf-8',
        'escaping' => 'markup',
        'verbosity' => 'no_white_space',
      ));
      $referer = service_get_referer();
      $refheader = !empty($referer) ? "Referer: " . $referer . "\r\n" : '';
      $context = stream_context_create(array('http' => array(
        'method' => 'POST',
        'header' => "Content-Type: text/xml; charset=UTF-8\r\n".
                    "User-Agent: ".$GLOBALS['service_wrapper_uaprefix']."php-xmlrpc-ext/".phpversion("xmlrpc")."\r\n".
                    $refheader,
        'content' => $request,
        'timeout' => $GLOBALS['service_wrapper_timeout'],
      )));

      $url = ($GLOBALS['service_wrapper_method'] == 'https' ? 'https://' : 'http://').$service_host.'/'.$GLOBALS['service_wrapper_url'];
      $service_last_result_raw = @file_get_contents($url, false, $context);

      $service_last_result = service_invoke_result('file_get_contents', 'xmlrpc', $url, $request, $service_last_result_raw);

    } else if (!function_exists('xmlrpc_encode_request')) {
      $service_last_result = array(
        'faultCode' => ENS_ERROR_UNKNOWN,
        'faultString' => 'Cannot find xmlrpc PHP extension: try using a different connection method'
      );
    } else {
      $service_last_result = array(
        'faultCode' => ENS_ERROR_UNKNOWN,
        'faultString' => 'No usable XMLRPC library found. Please set allow_url_fopen PHP ini directive to 1 (default) or download xmlrpc.inc from http://phpxmlrpc.sourceforge.net/'
      );
    }
    return $service_last_result;
  }

  function service_invoke_jsonfopen($method, $service_api_key, $timestamp, $nonce, $hash, $args) {
    global $service_host;
    if (ini_get('allow_url_fopen') && function_exists('json_encode')) {
      $request = json_encode($args);
      $referer = service_get_referer();
      $refheader = !empty($referer) ? "Referer: " . $referer . "\r\n" : '';
      $context = stream_context_create(array('http' => array(
        'method' => 'POST',
        'header' => "Content-Type: application/json; charset=UTF-8\r\n".
                    "User-Agent: ".$GLOBALS['service_wrapper_uaprefix']."\r\n".
                    $refheader.
                    "X-API-Key: ".$service_api_key."\r\n".
                    "X-API-Timestamp: ".$timestamp."\r\n".
                    "X-API-Nonce: ".$nonce."\r\n".
                    "X-API-Signature: ".$hash."\r\n",
        'content' => $request,
        'timeout' => $GLOBALS['service_wrapper_timeout'],
      )));

      $url = ($GLOBALS['service_wrapper_method'] == 'https' ? 'https://' : 'http://').$service_host.'/'.$GLOBALS['service_wrapper_jsonrpc_url'].'/'.$method;
      $service_last_result_raw = @file_get_contents($url, false, $context);

      $service_last_result = service_invoke_result('file_get_contents', 'json', $url, $request, $service_last_result_raw);

    } else if (!function_exists('json_encode')) {
      $service_last_result = array(
        'faultCode' => ENS_ERROR_UNKNOWN,
        'faultString' => 'Cannot find json PHP extension: try using a different connection method'
      );
    } else {
      $service_last_result = array(
        'faultCode' => ENS_ERROR_UNKNOWN,
        'faultString' => 'No usable client library found. Please set allow_url_fopen PHP ini directive to 1 (default) and enable json module'
      );
    }
    return $service_last_result;
  }

  function service_invoke_jsoncurl($method, $service_api_key, $timestamp, $nonce, $hash, &$args) {
    global $service_host;
    if (function_exists('curl_version') && function_exists('json_encode')) {
      $curlv = curl_version();
      $request = json_encode($args);

      $ch = curl_init();
      $url = ($GLOBALS['service_wrapper_method'] == 'https' ? 'https://' : 'http://').$service_host.'/'.$GLOBALS['service_wrapper_jsonrpc_url'].'/'.$method;
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json; charset=UTF-8",
                    "X-API-Key: ".$service_api_key,
                    "X-API-Timestamp: ".$timestamp,
                    "X-API-Nonce: ".$nonce,
                    "X-API-Signature: ".$hash]);
      curl_setopt($ch, CURLOPT_USERAGENT, $GLOBALS['service_wrapper_uaprefix'].'curl/'.$curlv['version']);
      $referer = service_get_referer();
      if (!empty($referer))
        curl_setopt($ch, CURLOPT_REFERER, $referer);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
      curl_setopt($ch, CURLOPT_TIMEOUT, $GLOBALS['service_wrapper_timeout']);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      $service_last_result_raw = curl_exec($ch);
      curl_close($ch);

      $service_last_result = service_invoke_result('curl_exec', 'json', $url, $request, $service_last_result_raw);
    } else if (!function_exists('json_encode')) {
      $service_last_result = array(
        'faultCode' => ENS_ERROR_UNKNOWN,
        'faultString' => 'Cannot find json PHP extension: try using a different connection method'
      );
    } else {
      $service_last_result = array(
        'faultCode' => ENS_ERROR_UNKNOWN,
        'faultString' => 'No usable client library found. Please enable curl and json extensions.'
      );
    }
    return $service_last_result;
  }

  function service_invoke_json($method, $service_api_key, $timestamp, $nonce, $hash, &$args) {
    if (function_exists('json_encode')) {
      if (ini_get('allow_url_fopen')) {
        $service_last_result = service_invoke_jsonfopen($method, $service_api_key, $timestamp, $nonce, $hash, $args);
      } else if (function_exists('curl_version')) {
        $service_last_result = service_invoke_jsoncurl($method, $service_api_key, $timestamp, $nonce, $hash, $args);
      } else {
        $service_last_result = array(
          'faultCode' => ENS_ERROR_UNKNOWN,
          'faultString' => 'No usable client library found. Please set allow_url_fopen PHP ini directive to 1 (default) or enable the curl extension'
        );
      }
    } else {
      $service_last_result = array(
        'faultCode' => ENS_ERROR_UNKNOWN,
        'faultString' => 'No usable client library found. Please enable the json extension or the xmlrpc extension'
      );
    }
    return $service_last_result;
  }

  function service_invoke($method) {
    global $service_host, $service_api_key, $service_secret, $service_last_result, $service_last_method, $service_last_args, $service_hint;
    $service_last_result = 0;
    $timestamp = time() + $GLOBALS['service_wrapper_time_offset'];
    $nonce = md5((string)mt_rand());
    $hash = function_exists('hash_hmac') ?
      hash_hmac("sha256", $service_api_key.';'.$timestamp.';'.$nonce.';'.$method, $service_secret) :
      bin2hex(mhash(MHASH_SHA256, $service_api_key.';'.$timestamp.';'.$nonce.';'.$method, $service_secret));

    $args = func_get_args();
    array_shift($args);

    $service_last_method = $method;
    $service_last_args = $args;

    $service_invoke_method = 'service_invoke_' . $service_hint;


    if (!preg_match('/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/', $service_host)) {
      $service_last_result = array(
        'faultCode' => ENS_ERROR_UNKNOWN,
        'faultString' => 'Invalid API host configuration (please do not use http scheme or path, just the hostname)'
      );
    } else if (!preg_match('/^[a-f0-9]{32}$/', $service_api_key)) {
      $service_last_result = array(
        'faultCode' => ENS_ERROR_UNKNOWN,
        'faultString' => 'Invalid API key configuration (must be 32 lowercase hex digits)'
      );
    } else if (!preg_match('/^[a-f0-9]{32}$/', $service_secret)) {
      $service_last_result = array(
        'faultCode' => ENS_ERROR_UNKNOWN,
        'faultString' => 'Invalid API secret configuration (must be 32 lowercase hex digits)'
      );
    } else if (function_exists($service_invoke_method)) {
      $service_last_result = $service_invoke_method($method, $service_api_key, $timestamp, $nonce, $hash, $args);
    } else {
      $service_last_result = array(
        'faultCode' => ENS_ERROR_UNKNOWN,
        'faultString' => 'Invalid API call hint: ' . $service_hint
      );
    }

    return !$service_last_result['faultCode'] ? $service_last_result['value'] : false;
  }

  function service_errorcode() {
    global $service_last_result;
    return $service_last_result === 0 || !isset($service_last_result) ? 0 : $service_last_result['faultCode'];
  }

  function service_errormessage() {
    global $service_last_result;
    return $service_last_result === 0 || !isset($service_last_result) ? '' : $service_last_result['faultString'];
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

if (!function_exists('_service_realip')) {
  function _service_realip($ip) {
    if (empty($ip) && isset($_SERVER) && isset($_SERVER['REMOTE_ADDR'])) $ip = $_SERVER['REMOTE_ADDR'];
    $fip = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    if (empty($fip) && isset($_SERVER) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $fip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    $fip = filter_var($fip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    if (empty($fip)) $fip = $ip;
    return $fip;
  }
}

if (!isset($GLOBALS['service_wrapper_uaprefix']))
  $GLOBALS['service_wrapper_uaprefix'] = "service-wrapper/".service_version()." "."php/".phpversion()." ";


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
  $ip = _service_realip($ip);
  return service_invoke('service.user.subscribe', $data, $ip);
}

function service_user_unsubscribe($uid_mail, $ip = false) {
  $ip = _service_realip($ip);
  return service_invoke('service.user.unsubscribe', $uid_mail, $ip);
}

function service_user_disable_mail($uid_mail, $type = 'admin', $ip = false) {
  $ip = _service_realip($ip);
  return service_invoke('service.user.disable_mail', $uid_mail, $type, $ip);
}

function service_user_enable_mail($uid_mail, $ip = false) {
  $ip = _service_realip($ip);
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

function service_user_bulk_update($data, $create_if_not_exists = false) {
  return service_invoke('service.user.bulk_update', $data, $create_if_not_exists);
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

