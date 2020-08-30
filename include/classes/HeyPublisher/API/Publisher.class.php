<?php
namespace HeyPublisher\API;
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

//
// Publisher class for JSON API calls related to the Publisher object
//
// This class is built against version v20200520 of HeyPublisher API
// and responds to JSON::API spec

if (!class_exists("\HeyPublisher\Base\API")) {
  require_once(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Base/API.class.php');
}

class Publisher {
  var $publisher = false;
  var $api = null;
  var $logger = null;
  var $config = null;

  public function __construct() {
    global $HEYPUB_API, $HEYPUB_LOGGER, $hp_config;
    $this->api = $HEYPUB_API;
    $this->logger = $HEYPUB_LOGGER;
    $this->logger->debug("API::Publisher#__construct()");
    $this->config = $hp_config;
  }

  public function __destruct() {
  }

  // Get the Publisher info for display in the Plugin Options
  public function get_publisher_info(){
    $this->logger->debug("API::Publisher#get_publisher_info()");
    $path = sprintf('publishers/%s',$this->api->poid);
    if ($this->publisher) {
      // we've already fetched this data - just return from memory
      return $this->publisher;
    }
    $result = $this->api->get($path);
    $this->logger->debug(sprintf("\t#get_publisher_info() results: %s",print_r($result,1)));
    if ($result && key_exists('object',$result) && $result['object'] == 'publisher' ) {
      $this->publisher = $result;
      return $result;
    }
    return;
  }

  // get the Genres data
  public function get_genres() {
    $this->logger->debug("API::Publisher#get_genres()");
    // $result = $this->get_from_cache('genre_types');
    if ($result) { return $result; }
    $path = 'genres';
    $result = $this->api->get($path);
    if ($result && key_exists('object',$result) && $result['object'] == 'list' ) {
      $this->logger->debug(sprintf("\t#get_genres() results: %s",print_r($result,1)));
      // $this->set_to_cache('genre_types',$result['data']);
      return $result['data'];
    }
    return;
  }

  // get the Publisher Types data
  public function get_publisher_types() {
    $this->logger->debug("API::Publisher#get_publisher_types()");
    // $result = $this->get_from_cache('publication_types');
    if ($result) { return $result; }
    // otherwise, fetch the data remotely
    $path = 'mediums';
    $result = $this->api->get($path);
    if ($result && key_exists('object',$result) && $result['object'] == 'list' ) {
      $this->logger->debug(sprintf("\t#get_publisher_types() results: %s",print_r($result,1)));
      // $this->set_to_cache('publication_types',$result['data']);
      return $result['data'];
    }
    return;
  }

  // Update the publisher record
  public function update_publisher($data) {
    // expected key is 'publisher'
    $publisher = array('publisher' => $data);
    $this->logger->debug("API::Publisher#update_publisher()");
    $path = sprintf('publishers/%s',$this->api->poid);
    $result = $this->api->put($path,$publisher);
    if ($result == 'updated') {
      return $result;
    }
    return;
  }

  public function get_editor_history(){
    $path = 'v2/editors/history';
    $result = $this->api->get($path);
    $this->logger->debug(sprintf("get_editor_history():\n\tResults: %s",print_r($result,1)));
    return $result;
  }
  // Get the statistics for a publishers
  public function get_publisher_stats() {
    $this->logger->debug("API::Publisher#get_publisher_stats()");
    $path = sprintf('publishers/%s/statistics',$this->api->poid);
    $result = $this->api->get($path);
    if ($result && key_exists('object',$result) && $result['object'] == 'list' ) {
      $this->logger->debug(sprintf("\t#get_publisher_stats() results: %s",print_r($result['data'],1)));
      return $result['data'];
    }
    return;
  }
  // Deactivate the publisher record
  public function deactivate(){
    $this->logger->debug("API::Publisher#put_publisher_deactivate()");
    $path = sprintf('publishers/%s',$this->api->poid);
    $result = $this->api->delete($path);
    if ($result == 'deleted') {
      return $result;
    }
    return;
  }

  // Get a key from local cache
  protected function get_from_cache($key) {
    $this->logger->debug("\tget_from_cache '{$key}' from cache");
    $hash = $this->config->get_config_option($key);
    $expry = date('U');
    if ($hash && ($hash['cache_date'] + 86400) > $expry) { // 1 day cache only
      unset($hash['cache_date']);
      $this->logger->debug(sprintf("\tcache is FRESH\n\tcache = %s",print_r($hash,1)));
      return $hash;
    }
    // $this->logger->debug(sprintf("\tcache date = %s\n\texpiry = %s\n\tdiff = %s",$hash['cache_date'],$expry,($hash['cache_date']- $expry)));
    $this->logger->debug("\tcache is OLD '{$key}'");
    return;
  }

  // Get a key from local cache
  protected function set_to_cache($key,$hash) {
    $this->logger->debug(sprintf("\tset_to_cache '%s' to cache %s",$key,print_r($hash,1)));
    $hash['cache_date'] = date('U');
    $hash = $this->config->set_config_option($key,$hash);
  }

}

?>
