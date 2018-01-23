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

  public function get_emails(){
    $path = 'email_templates';
    $result = $this->get($path);
    $this->logger->debug(sprintf("get_emails():\n\tResults: %s",print_r($result,1)));
    return $result;
  }

}

?>
