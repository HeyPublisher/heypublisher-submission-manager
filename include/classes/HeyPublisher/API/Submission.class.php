<?php
namespace HeyPublisher\API;
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

/**
* Submission class for JSON API calls related to Submissions
*
*/

if (!class_exists("\HeyPublisher\Base\API")) {
  require_once(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Base/API.class.php');
}

class Submission  {

  var $api = null;
  var $logger = null;

  public function __construct() {
    global $HEYPUB_API, $HEYPUB_LOGGER;
    $this->api = $HEYPUB_API;
    $this->logger = $HEYPUB_LOGGER;

    $this->logger->debug("API::Submission#__construct()");
  }

  public function __destruct() {
  }

  public function get_submission_history($id,$order){
    $path = sprintf('v2/submissions/%s/history',$id);
    $opts = array('order'=>$order);
    $result = $this->api->get($path,$opts);
    $this->logger->debug(sprintf("get_submission_history():\n\tResults: %s",print_r($result,1)));
    return $result;
  }

  // Get the votes from this editor - which will inform whether to pre-style vote buttons and display block
  // @since 2.7.0
  public function get_submission_votes($id,$ed_id){
    $path = sprintf('v2/submissions/%s/votes',$id);
    $opts = array('editor_id'=>$ed_id);
    $result = $this->api->get($path,$opts);
    $this->logger->debug(sprintf("get_submission_votes():\n\tResults: %s",print_r($result,1)));
    return $result;
  }

  // Get the notes for this submission
  // @since 2.7.0
  public function get_submission_notes($id){
    $path = sprintf('v2/submissions/%s/notes',$id);
    $opts = array('order'=>'desc');
    $result = $this->api->get($path,$opts);
    $this->logger->debug(sprintf("get_submission_notes():\n\tResults: %s",print_r($result,1)));
    return $result;
  }

  // Get the open submissions
  // @since 3.3.0
  public function get_open_submissions(){
    $poid = $this->api->poid;
    $path = sprintf('publishers/%s/submissions',$poid);
    $opts = array('order'=>'asc');
    $result = $this->api->get($path,$opts);
    // $this->logger->debug(sprintf("get_open_submissions():\n\tResults: %s",print_r($result,1)));
    return $result;
  }
  // Get the submission by ID
  // @since 3.3.0
  public function get_submission_by_id($id) {
    $poid = $this->api->poid;
    $path = sprintf('publishers/%s/submissions/%s',$poid,$id);
    $result = $this->api->get($path);
    $this->logger->debug(sprintf("get_submission_by_id():\n\tResults: %s",print_r($result,1)));
    return $result;
  }
  // Upddate the submission state
  // @since 3.3.0
  public function update_submission($id,$action,$message=null) {
    $this->logger->debug("API::Submission#update_submission()");
    $poid = $this->api->poid;
    // all updates capture the Editor ID
    $editor_id = get_current_user_id();
    $data = array('action'=>$action, 'editor_id'=>$editor_id);
    // If notes were present, include them
    if ($message) {
      $data['note'] = $message;
    }
    $submission = array('submission' => $data);
    $path = sprintf('publishers/%s/submissions/%s',$poid,$id);
    $result = $this->api->put($path,$submission);
    if ($result == 'updated') {
      return $result;
    }
    return;
  }

  // Get the authentication token from the base API call
  public function get_authentication_token() {
    $at = $this->api->authentication_token();
    return $at;
  }

}
?>
