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

  public function get_submission_history($id,$order){
    $path = sprintf('v2/submissions/%s/history',$id);
    $opts = array('order'=>$order);
    $result = $this->get($path,$opts);
    $this->logger->debug(sprintf("get_submission_history():\n\tResults: %s",print_r($result,1)));
    return $result;
  }

  // Get the votes from this editor - which will inform whether to pre-style vote buttons and display block
  // @since 2.7.0
  public function get_submission_votes($id,$ed_id){
    $path = sprintf('v2/submissions/%s/votes',$id);
    $opts = array('editor_id'=>$ed_id);
    $result = $this->get($path,$opts);
    $this->logger->debug(sprintf("get_submission_votes():\n\tResults: %s",print_r($result,1)));
    return $result;
  }

  // Get the notes for this submission
  // @since 2.7.0
  public function get_submission_notes($id){
    $path = sprintf('v2/submissions/%s/notes',$id);
    $opts = array('order'=>'desc');
    $result = $this->get($path,$opts);
    $this->logger->debug(sprintf("get_submission_notes():\n\tResults: %s",print_r($result,1)));
    return $result;
  }

}
?>
