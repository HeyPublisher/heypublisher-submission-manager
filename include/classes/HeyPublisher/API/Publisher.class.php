<?php
namespace HeyPublisher\API;
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

/**
* Publisher class for JSON API calls related to the Publisher object
*
*/

require_once(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/API.class.php');
class Publisher extends \HeyPublisher\API {
  var $publisher = false;

  public function __construct() {
    parent::__construct();
    $this->logger->debug("API::Publisher#__construct()");
  }

  public function __destruct() {
  	parent::__destruct();
  }

  // Get the Publisher info for display in the Plugin Options
  public function get_publisher_info(){
    $this->logger->debug("API::Publisher#get_publisher_info()");
    $path = sprintf('publishers/%s',$this->poid);
    if ($this->publisher) {
      // we've already fetched this data - just return from memory
      return $this->publisher;
    }
    $result = $this->get($path);
    if ($result && key_exists('publisher',$result)) {
      $this->logger->debug(sprintf("\tResults: %s",print_r($result,1)));
      $this->publisher = $result['publisher'];
      return $result['publisher'];
    }
    return;
  }

  // get the Genres data
  public function get_genres() {
    $this->logger->debug("API::Publisher#get_genres()");
    $result = $this->get_from_cache('genre_types');
    if ($result) { return $result; }
    $path = 'genres';
    $result = $this->get($path);
    if ($result && key_exists('genres',$result)) {
      $this->logger->debug(sprintf("\tResults: %s",print_r($result,1)));
      $this->set_to_cache('genre_types',$result['genres']);
      return $result['genres'];
    }
    return;
  }

  // get the Publisher Types data
  public function get_publisher_types() {
    $this->logger->debug("API::Publisher#get_publisher_types()");
    $result = $this->get_from_cache('publication_types');
    if ($result) { return $result; }
    // otherwise, fetch the data remotely
    $path = 'publishers/types';
    $result = $this->get($path);
    if ($result && key_exists('publisher_types',$result)) {
      $this->logger->debug(sprintf("\tResults: %s",print_r($result,1)));
      $this->set_to_cache('publication_types',$result['publisher_types']);
      return $result['publisher_types'];
    }
    return;
  }

  // Update the publisher record
  public function update_publisher($data) {
    // expected key is 'publisher'
    $publisher = array('publisher' => $data);
    $this->logger->debug("API::Publisher#update_publisher()");
    $path = sprintf('publishers/%s',$this->poid);
    $result = $this->put($path,$publisher);
    if ($result == 'updated') {
      return $result;
    }
    return;
  }

  public function get_editor_history(){
    $path = 'v2/editors/history';
    $result = $this->get($path);
    $this->logger->debug(sprintf("get_editor_history():\n\tResults: %s",print_r($result,1)));
    return $result;
  }
  // Get the statistics for a publishers
  public function get_publisher_stats() {
    $this->logger->debug("API::Publisher#get_publisher_stats()");
    $path = sprintf('publishers/%s/statistics',$this->poid);
    $result = $this->get($path);
    if ($result && key_exists('statistics',$result)) {
      $this->logger->debug(sprintf("\tResults: %s",print_r($result,1)));
      return $result['statistics'];
    }
    return;
  }
  // Deactivate the publisher record
  public function deactivate(){
    $this->logger->debug("API::Publisher#put_publisher_deactivate()");
    $path = sprintf('publishers/%s',$this->poid);
    $result = $this->delete($path);
    if ($result == 'deleted') {
      return $result;
    }
    return;
  }

}

?>
