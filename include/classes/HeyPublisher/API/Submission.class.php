<?php
namespace HeyPublisher\API;
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

/**
* Submission class for JSON API calls related to Submissions
*
*/

require_once(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/API.class.php');
class Submission extends \HeyPublisher\API {

  public function __construct() {
  	parent::__construct();
  }

  public function __destruct() {
  	parent::__destruct();
  }

  public function get_editor_history(){
    $path = 'editors/history';
    $this->logger->debug("calling get()");
    $result = $this->get($path);
    $this->logger->debug(sprintf("get_editor_history():\n\tResults: %s",print_r($result,1)));
    return $result;
  }

  public function get_submission_history($id,$order){
    $path = sprintf('submissions/%s/history',$id);
    $this->logger->debug("calling get()");
    $opts = array('order'=>$order);
    $result = $this->get($path,$opts);
    $this->logger->debug(sprintf("get_submission_history():\n\tResults: %s",print_r($result,1)));
    return $result;
  }
}
?>
