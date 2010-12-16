<?php
/**
* HeyPublisher class is the root class from which all plugin functionality is extended
*
*/
require_once('HeyPublisher.class.php');
class HeyPublisherSubmission extends HeyPublisher {

  var $nounce = 'heypub-bulk-submit';
  var $disallowed_states = array('withdrawn','published','rejected');
  public function __construct() {
  	parent::__construct();

  }   

  public function __destruct() {
  	parent::__destruct();

  }

  public function revision_request_link($id) {
    $form = $this->revision_request_form($id);
    $str = <<<EOF
    <div id='revision_request_off'><a href='#' onclick="
      $('revision_request_off').hide();
      $('revision_request_on').show();
      return false;">Request Revision from Author</a>
    </div>
    <div id='revision_request_on' style='display:none;'>
    $form
    </div>
EOF;
    return $str;
  }

  public function revision_request_form($id) {
    $url = $this->get_form_post_url_for_page('heypub_show_menu_submissions');
    $nounce = wp_nonce_field($this->nounce);
    
    $str = <<<EOF
<hr>
<h3>Note to Author:</h3>
<form id='heypub_revision_request_notes' action='$url' method='post'>
<textarea id='heypub_ed_note' name='notes'> </textarea>
<input type='hidden' name='action' value='request_revision' />
<input type='hidden' name="post" value="$id" />
<input type="submit" value="Send Request" name="doaction" id="doaction" />
$nounce
&nbsp;<a href='#' onclick="$('revision_request_on').hide();$('revision_request_off').show();return false;">Cancel</a>
</form>
EOF;
    return $str;
  }


  public function editor_vote_box() {
    return false;
    $base = HEY_BASE_URL;
    $str = <<<EOF
<hr>
<h3>Editor Notes:</h3>
<p>Vote 'Up' or 'Down' on this submission.</p>
<form id='heypub_editor_notes' action='#'>
<textarea id='heypub_ed_note'> </textarea>
<div id='heypub_vote_buttons'>
<input id='heypub_vote_yes' type="image" src='$base/images/thumbs_up.gif' value="up" name="vote" title='Vote YES on this Submission' />
<input id='heypub_vote_no' type="image" src='$base/images/thumbs_down.gif' value="down" name="vote"  title='Vote NO on this Submission'/>
</div>
{${wp_nonce_field($this->nounce)}}
</form>


EOF;
    return $str;
  }


}