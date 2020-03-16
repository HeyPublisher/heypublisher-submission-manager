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

  // get the Genres HeyPublisher supports
  public function get_genres() {
    $this->logger->debug("API::Publisher#get_genres()");
    $path = 'genres';
    $result = $this->get($path);
    if ($result && key_exists('genres',$result)) {
      $this->logger->debug(sprintf("\tResults: %s",print_r($result,1)));
      return $result['genres'];
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

    $this->error = 'Nothing to see here yet...';
    return;
  }

}

?>
