<?php
namespace HeyPublisher\API;
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

/**
* Submission class for JSON API calls related to Submissions
*
*/

require_once(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/API.class.php');
class Email extends \HeyPublisher\API {

  public function __construct() {
  	parent::__construct();
  }

  public function __destruct() {
  	parent::__destruct();
  }

  public function get_emails() {
    $path = 'email_templates';
    $result = $this->get($path);
    $this->logger->debug(sprintf("get_emails():\n\tResults: %s",print_r($result,1)));
    $emails = $this->normalize_results($result);
    return $emails;
  }

  public function get_email($id) {
    $path = sprintf('email_templates/%s',$id);
    $result = $this->get($path);
    $emails = $this->normalize_results($result);
    return $emails[0];
  }

  public function get_submission_states() {
    $path = 'email_templates/submission_states';
    $result = $this->get($path);
    $this->logger->debug(sprintf("get_submission_states():\n\tResults: %s",print_r($result,1)));
    if ($result['submission_states'] && sizeof($result['submission_states']) > 0 ) {
      return $result['submission_states'];
    } else {
      return [];
    }
  }

  private function normalize_results($emails) {
    if ($emails['email_templates'] && sizeof($emails['email_templates']) > 0 ) {
      return $emails['email_templates'];
    } else {
      return [];
    }
  }
}

?>
