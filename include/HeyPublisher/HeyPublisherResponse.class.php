<?php
/**
* HeyPublisher class is the root class from which all plugin functionality is extended
*
*/
require_once('HeyPublisher.class.php');
class HeyPublisherResponse extends HeyPublisher {

  public function __construct() {
  	parent::__construct();

  }   

  public function __destruct() {
  	parent::__destruct();

  }

  public function handler() {
    // if (!$hp_xml->is_validated) {
    //   heypub_not_authenticated();
    //   return;
    // }
    // if (isset($_REQUEST[show])) {
    //   heypub_show_submission($_REQUEST[show]);
    //   return;
    // }

    // default is to show the index
    $this->new_index();
    return;
  }
  public function new_index() {
    global $hp_xml;
    $base = HEYPUB_SVC_URL_BASE;
    $uid = $hp_xml->user_oid;
    $pid = $hp_xml->pub_oid;
    $url = sprintf('%s/response_template/index/%s/%s',$base,$uid,$pid);
    print <<<EOF
<iframe src="$url" width='100%' height='800' scrolling='auto'> </iframe>
EOF;

  }

}
