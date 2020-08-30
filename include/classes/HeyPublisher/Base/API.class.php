<?php
namespace HeyPublisher\Base;
global $HEYPUB_API;

if (!class_exists("\HeyPublisher\Base\Log")) {
  require_once( dirname(__FILE__) . '/Log.class.php');
}

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

  // HeyPublisher base API class for all JSON API calls
  // TODO: https://stackoverflow.com/questions/13420952/php-curl-delete-request
  // This class is instantiated automatically when loaded and will be accessible
  // via the global $HEYPUB_LOGGER

class API {
  var $api   = null;
  var $error  = false;
  var $timeout = 3;
  var $config = null;
  var $uoid   = null;
  var $poid   = null;

  public function __construct() {
    // TODO: to make this generic, the uoid and poid need to be dynamic
    // If HeyPublisher submission manager is installed, use those, else use plugin defaults
    global $HEYPUB_LOGGER;
    $this->logger = $HEYPUB_LOGGER;
    $this->logger->debug("HeyPublisher::API loaded");

    $this->initialize_api_url();
    $this->initialize_oids();
    register_shutdown_function(array($this,'shutdown'));
  }

  public function __destruct() {

  }

  // Register the shutdown functions
  public function shutdown() {
    $this->logger->debug("API#shutdown");
    // https://stackoverflow.com/questions/33231656/register-static-class-method-as-shutdown-function-in-php
    // https://us.php.net/manual/en/function.register-shutdown-function.php
    // curl_close($this->curl);
  }

  /**
  * Send a POST requst using cURL
  * @param string $url to request
  * @param array $post values to send
  * @param array $options for cURL
  * @return string
  */
  public function post($path, array $post = NULL, array $options = array()) {
    $url = sprintf('%s/%s',$this->api,$path);
    $post = $this->clean_post_vars($post);
    $defaults = array(
      CURLOPT_POST => 1,
      CURLOPT_HEADER => 0,
      CURLOPT_URL => $url,
      CURLOPT_FRESH_CONNECT => 1,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_FORBID_REUSE => 1,
      CURLOPT_TIMEOUT => $this->timeout,
      CURLOPT_POSTFIELDS => http_build_query($post)
    );
    $curl = curl_init();
    curl_setopt_array($curl, ($options + $defaults) );
    $result = $this->send($curl);
    curl_close($curl);
    return $result;
  }

  /**
  * Send a GET requst using cURL
  * @param string $url to request
  * @param array $get values to send
  * @param array $options for cURL
  * @return string
  */
  public function get($path, array $get = NULL, array $options = array()) {
    if ($get === NULL ) { $get = array(); }
    $this->logger->debug("in get()\n\tpath: {$path}");
    $this->logger->debug(sprintf("\tget: %s", print_r($get,1)));
    $url = sprintf('%s/%s',$this->api,$path);
    $this->logger->debug("=> url: {$url}");
    $defaults = array(
      CURLOPT_URL => $url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($get),
      CURLOPT_HEADER => 0,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_TIMEOUT => $this->timeout
    );
    $curl = curl_init();
    curl_setopt_array($curl, ($options + $defaults) );
    $result = $this->send($curl);
    curl_close($curl);
    return $result;
  }

  /**
  * Send a PUT requst using cURL
  * @param string $url to request
  * @param array $put values to send
  * @param array $options for cURL
  * @return string
  */
  public function put($path, array $put = NULL, array $options = array()) {
    $url = sprintf('%s/%s',$this->api,$path);
    $this->logger->debug("in put()\n\tpath: {$path}");
    $this->logger->debug(sprintf("\tput: %s", print_r($put,1)));
    $data = $this->clean_post_vars($put);
    $defaults = array(
      CURLOPT_URL => $url,
      CURLOPT_FRESH_CONNECT => 1,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_FORBID_REUSE => 1,
      CURLOPT_TIMEOUT => $this->timeout,
      CURLOPT_CUSTOMREQUEST   => 'PUT',
      CURLOPT_POSTFIELDS => http_build_query($data),
      CURLOPT_HEADER => 0
    );
    $curl = curl_init();
    curl_setopt_array($curl, ($options + $defaults) );
    $result = $this->send($curl,'updated');
    curl_close($curl);
    return $result;
  }

  /**
  * Send a GET requst using cURL
  * @param string $url to request
  * @return string
  */
  public function delete($path) {
    $this->logger->debug("in delete()\n\tpath: {$path}");
    $url = sprintf('%s/%s',$this->api,$path);
    $this->logger->debug("=> url: {$url}");
    $defaults = array(
      CURLOPT_URL => $url,
      CURLOPT_HEADER => 0,
      CURLOPT_RETURNTRANSFER  => TRUE,
      CURLOPT_CUSTOMREQUEST   => 'DELETE',
      CURLOPT_TIMEOUT => $this->timeout
    );
    $curl = curl_init();
    curl_setopt_array($curl, $defaults );
    $result = $this->send($curl,'deleted');
    curl_close($curl);
    return $result;
  }

  // Execute the curl command
  // 2nd parameter `$desired` indicates the value expected to be returned.
  // This is used in PUT an DELETE calls when a 204 is the status, so we can differentiate between the two returns.
  private function send($curl, $desired = false) {
    $this->logger->debug("send()");
    $return = false;
    $this->logger->debug(sprintf("send():\n\tuoid = %s\n\tpoid = %s",$this->uoid,$this->poid));
    // Authentication header!!
    curl_setopt($curl, CURLOPT_USERPWD, "{$this->uoid}:{$this->poid}");

    $result = curl_exec($curl);
    $url = curl_getinfo($curl,CURLINFO_EFFECTIVE_URL);
    $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $this->logger->debug(sprintf("send():\n\tURL = %s\n\tStatus = %s",$url,$status));
    // Check for errors
    if ( curl_errno($curl) ) {
      $this->error = sprintf('HeyPublisher API Error : %s', curl_error($curl));
    }
    else {
      switch($status){
        case 200:   // success GET
          $return = json_decode($result, true);
          break;
        case 201:   // success POST
          $return = json_decode($result, true);
          break;
        case 204:   // success for PUT & DELETE
          $return = $desired;
          break;
        default:
          if ($result) {
            $data = json_decode($result, true);
            $message = $data['message'];
            $this->logger->debug(sprintf("\tdata = %s",print_r($data,1)));
            $this->error = sprintf('HeyPublisher API Error : %s (%s)', $message, $status);
          } else {
            $this->error = sprintf('HeyPublisher API Return Status : %s', $status);
          }
          $this->logger->debug(sprintf("\treturning error %s",$this->error));
          break;
      }
    }
    $this->logger->debug("\treturning from send: {$status}");
    return $return;
  }

  public function authentication_token() {
    $pass = "{$this->uoid}:{$this->poid}";
    $token = base64_encode($pass);
    return $token;
  }

  private function clean_post_vars($array) {
    $tmp = array();
    foreach ($array as $key=>$val) {
      if (is_scalar($val)) {
        $tmp[$key] = htmlentities(stripslashes($val));
      } else {
        $tmp[$key] = $val;
      }
    }
    return $tmp;
  }
  // Set the API URL depending on whether we're in dev or prod
  private function initialize_api_url() {
    $domain = 'https://www.heypublisher.com';
    $debug = (getenv('HEYPUB_DEBUG') === 'true');
    if ($debug) {
      $domain = 'http://127.0.0.1:3000';
    }
    $this->api = sprintf("%s/api",$domain);
    $this->logger->debug(sprintf("\tAPI URL: %s",$this->api));
  }

  // Load the uoid and poid into memory
  private function initialize_oids() {
    global $hp_config;
    $this->logger->debug("API#initialize_oids()");
    if (is_object($hp_config) && strtolower(get_class($hp_config)) == strtolower("HeyPublisher\Config")) {
      $this->uoid = $hp_config->uoid;
      $this->poid = $hp_config->poid;
      $this->logger->debug("\tloading from \$hp_confg global");
    }
    else {
      $this->logger->debug("\tloading defaults");
      // NOTE: These should never need to change
      $this->uoid = "99f7c470-665d-0138-47c1-38f9d3071b82";
      $this->poid = "9c0484c0-665d-0138-47c1-38f9d3071b82";
    }
    $this->logger->debug(sprintf("\tUOID: %s\n\tPOID: %s",$this->uoid,$this->poid));
  }
}

// This class sets a global accessor
if (!isset($HEYPUB_API)) {
  $HEYPUB_API  = new \HeyPublisher\Base\API();
}

?>
