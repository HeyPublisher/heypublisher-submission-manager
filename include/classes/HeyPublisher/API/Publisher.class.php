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
    $path = sprintf('publishers/%s',$this->poid);
    if ($this->publisher) {
      // we've already fetched this data - just return from memory
      return $this->publisher;
    }
    $result = $this->get($path);
    $this->logger->debug(sprintf("get_publisher_info():\n\tResults: %s",print_r($result,1)));
    if (key_exists('publishers',$result)) {
      $this->publisher = $result['publishers'][0];
      return $result['publishers'][0];
    }
    $this->error = 'Error finding publisher info on HeyPublisher.com';
    return;
  }

  // get the Genres HeyPublisher supports
  public function get_genres() {
    $path = 'genres';
    $result = $this->get($path);
    $this->logger->debug(sprintf("get_genres():\n\tResults: %s",print_r($result,1)));
    if (key_exists('genres',$result)) {
      return $result['genres'];
    }
    $this->error = 'Error finding genre info on HeyPublisher.com';
    return;
  }
}

?>
