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

class Email {

  var $api = null;
  var $logger = null;

  public function __construct() {
    global $HEYPUB_API, $HEYPUB_LOGGER;
    $this->api = $HEYPUB_API;
    $this->logger = $HEYPUB_LOGGER;
  }

  public function __destruct() {
  }

  public function get_emails() {
    $path = 'v2/email_templates';
    $result = $this->api->get($path);
    $this->logger->debug(sprintf("get_emails():\n\tResults: %s",print_r($result,1)));
    $emails = $this->normalize_results($result,true);
    return $emails;
  }

  public function get_email($id) {
    $path = sprintf('v2/email_templates/%s',$id);
    $result = $this->api->get($path);
    $emails = $this->normalize_results($result);
    return $emails[0];
  }

  public function get_submission_states() {
    $path = 'v2/email_templates/submission_states';
    $result = $this->api->get($path);
    $this->logger->debug(sprintf("get_submission_states():\n\tResults: %s",print_r($result,1)));
    if ($result['submission_states'] && sizeof($result['submission_states']) > 0 ) {
      return $result['submission_states'];
    } else {
      return [];
    }
  }

  // Delete a template
  public function delete_template($id) {
    $message = 'Unknown DELETE error';
    $path = sprintf('v2/email_templates/%s',$id);
    $result = $this->api->delete($path);
    if ($result == 'deleted') {
      $message = 'Email template deleted';
    }
    elseif (!$this->error) {
      $this->error = "Unable to delete template";
    }
    return $message;
  }

  // Update a template
  public function update_template($post) {
    $message = 'Unknown POST error';
    if ($post['hp_email']) {
      $path = 'v2/email_templates';
      $result = $this->api->post($path,$post['hp_email']);
      $emails = $this->normalize_results($result);
      if ($emails[0] && $emails[0]['id'] > 0) {
        $message = 'Email template updated';
      }
      elseif (!$this->error) {
        $this->error = "Unable to validate template";
      }
    }
    else {
      $this->error = 'Unknown POST error';
    }
    return $message;
  }

  private function normalize_results($emails,$include_meta=false) {
    if (is_array($emails['email_templates'])) {
      if ($include_meta) {
        return $emails;
      }
      else {
        return $emails['email_templates'];
      }
    } else {
      return [];
    }
  }
}

?>
