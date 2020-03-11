<?php
namespace HeyPublisher;

require_once(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Log.class.php');

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

/**
 * HeyPublisher base class for all JSON API calls
 * TODO: https://stackoverflow.com/questions/13420952/php-curl-delete-request
 * Clean up this file to be more DRY
 */

class API {
  var $debug = false;
  var $api = HEYPUB_API;
  var $error = false;
  var $timeout = 4;
  var $uoid = '';
  var $poid = '';

  public function __construct() {
    $this->logger = new \HeyPublisher\Log();
    $install = get_option(HEYPUB_PLUGIN_OPT_INSTALL);
    $this->uoid = $install['user_oid'];
    $this->poid = $install['publisher_oid'];
    // $this->curl = curl_init();
    register_shutdown_function(array($this,'shutdown'));
  }

  public function __destruct() {

  }

  // Register the shutdown functions
  public function shutdown() {
    // http://stackoverflow.com/questions/33231656/register-static-class-method-as-shutdown-function-in-php
    // http://us.php.net/manual/en/function.register-shutdown-function.php
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
      CURLOPT_HEADER => 0,
      CURLOPT_RETURNTRANSFER  => TRUE,
      CURLOPT_CUSTOMREQUEST   => 'PUT',
      CURLOPT_TIMEOUT => $this->timeout,
      CURLOPT_POSTFIELDS => http_build_query($data)
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
    $result = $this->send($curl);
    curl_close($curl);
    return $result;
  }

  // Execute the curl command
  private function send($curl) {
    $this->logger->debug("send()");
    $return = false;
    $this->logger->debug(sprintf("send():\n\tuoid = %s\n\tpoid = %s",$this->uoid,$this->poid));
    // Authentication header!!
    curl_setopt($curl, CURLOPT_USERPWD, "{$this->uoid}:{$this->poid}");

    $result = curl_exec($curl);
    $http_code = false;
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
        case 204:   // success DELETE
          $return = 'deleted';
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
}
?>
