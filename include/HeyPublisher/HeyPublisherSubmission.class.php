<?php
/**
* HeyPublisher class is the root class from which all plugin functionality is extended
*
*/
require_once('HeyPublisher.class.php');
class HeyPublisherSubmission extends HeyPublisher {

  var $nounce = 'heypub-bulk-submit';
  var $submission_state = array(
    'read' => 'read',
    'under_consideration' => 'review',
    'accepted' => 'accept',
    'rejected' => 'reject',
    'publisher_revision_requested' => 'request_revision'
    );

  public function __construct() {
  	parent::__construct();

  }

  public function __destruct() {
  	parent::__destruct();

  }

  /**
  * @since v 1.4.0
  */
  public function editor_note_text_area() {
    $base = HEY_BASE_URL;
    $str = <<<EOF
<div class='editor-note-to-author' id='editor_notes_off'>
  <a href='#' data-notes='on'>+ Include a Note to Writer</a>
</div>
<div class='hepub_editor_note editor-note-to-author' id='editor_notes_on' style='display:none;'>
  <a href='#' data-notes='off'>- remove</a>
  <textarea name='notes'> </textarea>
</div>
EOF;
    return $str;
  }

  public function select_selected($option,$val) {
    global $hp_xml;
    // $val is an array - so stringify it
    $hp_xml->log(sprintf("select_selected(%s,%s)",$option,$val));
    $hp_xml->log(sprintf("hash (%s)",$this->submission_state["$val"]));
    if ($this->submission_state["$val"] && $this->submission_state["$val"] == $option) {
      return 'selected';
    }
    return '';
  }

}
