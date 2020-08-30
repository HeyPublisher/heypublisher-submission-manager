<?php
namespace HeyPublisher\Page;

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

/**
 * HeyPublisher class for handling options updates and editing page
 *
 */
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/HeyPublisher/HeyPublisherSubmission.class.php');
// Load the class files and associated scoped functionality
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Page.class.php');
require_once(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/API/Submission.class.php');

class Submissions extends \HeyPublisher\Page {

  var $disallowed_states = array('withdrawn','published','rejected');
  var $sub_class = null;
  var $subapi = null;
  var $has_voted = false;
  var $editors = array();
  var $page = '_submissions';
  var $wp_categories = array();

  public function __construct() {
  	parent::__construct();
    $this->sub_class = new \HeyPublisherSubmission;
    $this->subapi = new \HeyPublisher\API\Submission;
    $this->slug .= $this->page;

    // All categories for this install - move this up so it';'s a one-time call:
    $this->wp_categories =  get_categories(array('orderby' => 'name','order' => 'ASC'));
  }

  public function __destruct() {
  	parent::__destruct();
  }

  public function action_handler() {
    // This is not possible as this sub-menu is not accessible if not validated
    // TODO: clean up this logic
    if (!$this->config->is_validated) {
      // parent::page('Submissions', 'Submissions', 'heypub_list_submissions' );
      heypub_not_authenticated();
      return;
    }
    if (isset($_REQUEST['show'])) {
      parent::page('Submission', '', array($this,'display_submission'),$_REQUEST['show']);
      return;
    }

    // process the request actions
    if (isset($_REQUEST['action'])) {
      if ($_REQUEST['action'] == 'reject') {
        $this->message = $this->reject_submission($_REQUEST);
      }
      elseif ($_REQUEST['action'] == 'withdraw') {
        $this->message = $this->withdraw_submission($_REQUEST);
      }
      elseif ($_REQUEST['action'] == 'review') {
        $this->message = $this->consider_submission($_REQUEST);
      }
      elseif ($_REQUEST['action'] == 'accept') {
        $this->message = $this->accept_submission($_REQUEST);
        // We exit if we don't have a message, that means we had to prompt for user.
        if (!$this->message) { return; }
      }
      elseif ($_REQUEST['action'] == 'create_user') {
        $this->message = $this->create_user($_REQUEST);
        if (!$this->message) { return; }
      }
      elseif ($_REQUEST['action'] == 'request_revision') {
        $this->message = $this->request_revision($_REQUEST);
      }
      else {
        $this->xml->error = "Oops - looks like you didn't select an action from the dropdown.";
        if ($_REQUEST['notes'] != '') {
          $this->xml->error .= '<br/>Your note to the author was not sent.';
        }
        $this->xml->print_webservice_errors(false);
      }
    }
    $this->print_message_if_exists();
    // Default is to show list of submissions
    parent::page('Submissions', 'Current Open Submissions', array($this,'list_submissions'));
  }

  /**
  * List the submissions needing review
  */
  protected function list_submissions() {
    global $hp_base;
    $html = '';
    // This is a SimpleXML object being returned, with key the sub-id
    $subs = $this->xml->get_recent_submissions();
    $cats = $this->xml->get_my_categories_as_hash();
    $opts = $this->config->get_config_options();
    $this->logger->debug("Submission#list_submissions");
    $this->logger->debug(sprintf("\t\$cats = %s",print_r($cats,1)));
    $this->logger->debug(sprintf("\t\$opts = %s",print_r($opts,1)));
    $publication = $opts['name'];
    $html .= <<<EOF
      <script type='text/javascript'>
        jQuery(function() {
          HeyPublisher.submissionListInit();
        });
      </script>
      <p>Below are the most recent <b><i>{$publication}</i></b> submissions sent in by your writers.</p>
      <p>Click on the title to read the submission.</p>
      <p>Click on the plus button to see the author's bio.  If they did not provide one, this will will not be avaialable.</p>
        <table class="widefat post fixed ll-plugin" cellspacing="0" id='heypub_submissions'>
        <thead>
        	<tr>
          	<th style='width:4%;'>
          	  &nbsp;
          	</th>
          	<th style='width:29%;'>Title</th>
          	<th style='width:18%;'>Genre</th>
          	<th style='width:18%;'>Author</th>
          	<th style='width:12%;'>Submitted</th>
          	<th style='width:7%;white-space:nowrap;'>Words</th>
          	<th style='width:15%;'>Status</th>
        	</tr>
        </thead>
        <tfoot />
        <tbody>
        {$this->format_submission_list($subs)}
        </tbody>
      </table>

EOF;
    // '
    return $html;
  }
  private function format_submission_list($subs) {
    global $hp_base;
    $accepted = $this->get_accepted_post_ids();
    $html = '';
    if (!empty($subs)) {
      foreach($subs as $x => $hash) {
        $count++;
        $class = null;
        if(($count%2) != 0) { $class = 'alternate'; }
        // overide to highlight submissions where author has provided a rewrite
        if ($hash->status == 'writer_revision_provided') {
          $class .= ' revised';
        } elseif  ($hash->status == 'publisher_revision_requested') {
          $class .= ' requested';
        }

        $url = sprintf('%s/wp-admin/admin.php?page=%s&show=%s',get_bloginfo('wpurl'),$this->slug,"$x");
        $repub_url = $url;
        $disabled_url = '';
        $category = $this->get_display_category($hash->category->id,$hash->category->name);
        $toggle = '';
        if ($hash->author->bio != '') {
          $toggle = <<<EOF
          <a data-sid='{$x}' href="#" title="View details">
            <span class="heypub-icons dashicons dashicons-plus-alt"></span>
          </a>
EOF;
        }

        $html .= <<<EOF
          <tr class='{$class}' valign="top">
            <th scope="row">{$toggle}</th>
            <td class="heypub_list_title">
              <a href="{$url}" title="Review {$hash->title}">{$hp_base->truncate($hash->title,30)}</a>
            </td>
            <td>
              {$category}
            </td>
EOF;
        if ($hash->author->bio != '') {
          $authorName = sprintf("%s", $hash->author->full_name);
          $html .= <<< EOF
            <td class="heypub_list_title">
              {$authorName}
            </td>
EOF;
        } else {
          $html .= sprintf("<td>%s</td>", $hash->author->full_name);
        }
        $contact = $hp_base->blank();
        if (FALSE != $hash->author->email) {
          $contact = sprintf('<a title="Email the Author"  href="mailto:%s?subject=Your%%20submission%%20to%%20%s">%s</a>',$hash->author->email,get_bloginfo('name'),$hp_base->truncate($hash->author->email));
        }
        $word_count = $this->normalize_word_count($hash);
        $status = $this->xml->normalize_submission_status($hash->status);
        $link = $status; // default
        if ($accepted["$x"] || "$hash->status" == 'accepted') {
          if (!$accepted["$x"]) { // the post was accepted but not imported
            $disabled_url = ' onclick="alert(\'An error may have occurred when importing this submission.\n\nTry Re-Accepting it.\'); return false;"';
          }
          $uri = sprintf('%s/wp-admin/post.php?action=edit&post=%s',get_bloginfo('wpurl'),$accepted["$x"]);
          $link = sprintf("<a %s href='%s' title='This submission has already been imported.\nClick to view.'>%s <span class='heypub-icons dashicons dashicons-media-document'></span></a>", $disabled_url, $uri, $status);
        }

        $html .= <<<EOF
          <td nowrap>
            {$hash->submission_date}
          </td>
          <td class='numeric'>
            {$word_count}
          </td>
          <td nowrap>
            {$link}
          </td>
        </tr>
EOF;

        if ($hash->author->bio != '') {
          $html .= <<<EOF
            <tr id='post_bio_{$x}' style='display:none;'>
              <td colspan='8'>
                <div class='heypub_author_bio_preview'>
                  <b>Author Bio:</b>
                  {$hash->author->bio}
                </div>
              </td>
            </tr>
EOF;
        }
      }
    }
    else {
      $html .= <<<EOF
        <tr><td colspan=6 class='heypub_no_subs'>No Submissions At This Time</td></tr>
EOF;
    }
    return $html;
  }

  /**
  * Display the individual submission.  Requires a HeyPublisher submission ID
  *
  * If the submission body is blank, then we don't allow editors to change state
  *
  */
  protected function display_submission($id) {
    global $hp_base, $hp_opt;
    $html = '';
    // Reading a submission marks it as 'read' in HeyPublisher
    if ($this->xml->submission_action($id,'read')) {
      $sub = $this->xml->get_submission_by_id($id);
      if ($sub) {
        $this->log(sprintf("ID  %s\ndisplay_submission \n%s",$sub->id,print_r($sub,1)));
        $this->log(sprintf("author: %s",print_r($sub->author,1)));
        // Build out the side-nav
        // This state should not be possible ??
        if (FALSE == $sub->author->email) {
          $email = "<i><small>No Email Provided</small></i>";
        }
        else {
          $email = sprintf('<a href="mailto:%s?subject=Your%%20submission%%20to%%20%s" target="_blank">%s</a>',$sub->author->email,get_bloginfo('name'),$sub->author->email);
        }
        $hblock = $this->submission_history_block($id);
        $editor_id = get_current_user_id();
        $token = $this->subapi->get_authentication_token();
        $domain = sprintf('%s/api/v2',HEYPUB_DOMAIN);
        $votes = $this->get_votes($id,$editor_id);
        $vote_buttons = $this->vote_buttons_block($votes);
        $vote_summary = $this->get_vote_summary_block($votes);
        $notes_block = $this->get_notes($id,$editor_id);

        $html .= <<<EOF
          <script type='text/javascript'>
            jQuery(function() {
              HeyPublisher.submissionDetailInit('{$domain}','{$editor_id}','{$token}','{$id}','{$this->xml->debug}');
            });
          </script>
          <h2 class='heypub-sub-title'>
            "{$sub->title}" :
            {$sub->category} by {$sub->author->first_name} {$sub->author->last_name}
            <small>({$email})</small>
          </h2>
          <!-- Notes and Votes setter -->
          <div class='heypub-voting'>
            {$vote_buttons}
            <div>
              <textarea id='heypub_editor_note' placeholder="Share your thoughts on this submission..."></textarea>
              <button id='heypub-note-submit' class="heypub-button button-primary">Save Note</button>
            </div>
          </div>
          <!-- Notes and Votes Display - only shown after editor votes -->
          <div id='heypub-notes' style=''>
            <h3>Notes and Votes
              <a data-toggle='heypub-notes-detail' href="#" title="View all Notes" style="float:right;border:0;">
                <span class="heypub-icons dashicons dashicons-plus-alt"></span>
              </a>
            </h3>
            <div id='heypub-notes-detail' style='display:none;'>
              {$vote_summary}
              {$notes_block}
            </div>
          </div>

          <!-- Summary -->
          <div>
            <h3>Summary
              <a data-toggle='heypub_summary' href="#" title="View summary" style="float:right;border:0;">
                <span class="heypub-icons dashicons dashicons-plus-alt"></span>
              </a>
            </h3>
            <div id='heypub_summary' style='display:none;'>
              {$this->summary_block($sub)}
            </div>
          </div>
          <div>
            <h3>Submission</h3>
            {$this->submission_block($sub)}
          </div>
          <div>
            <h3>History
              <a data-toggle='heypub_history' href="#" title="View history" style="float:right;border:0;">
                <span class="heypub-icons dashicons dashicons-plus-alt"></span>
              </a>
            </h3>
            <div id='heypub_history' style='display:none;'>
              <p>The following editors have taken the following actions on this submission:</p>
              {$hblock}
            </div>
          </div>

EOF;
        // this needs to go after all uses of $sub as it mucks with the var
        $this->additional_side_nav = $this->submission_side_nav($sub);
      } else {
        $html = <<<EOF
          <h2 class='error'>Please try again.</h2>
EOF;
      }  // end if $sub
    } // end submission_action
    return $html;
  } // end function

  // HTML for the inported on submission status block
  private function imported_date_side_nav($post_id) {
    $html = null;
    if ($post_id) {
      $date = $this->get_post_mod_date($post_id);
      $html = <<<EOF
        <dt class='days_pending_warn'>Imported on</dt>
        <dd>{$date}</dd>
EOF;
    }
    return $html;
  }

  private function submission_side_nav($sub) {
    global $hp_base;
    $id = $sub->id;
    $post_id = $this->get_post_id_by_submission_id($id);
    $days_pending = ($sub->days_pending >= 60) ? 'late': (($sub->days_pending >= 30) ? 'warn' : 'ok');

    /*
    // Future functionality - downloads of original docs are coming....
    if ($this->config->get_config_option('display_download_link')) {
    <a class='heypub_smart_button' href='<?php echo $sub->document->url; ?>' title="Download '<?php echo $sub->title; ?>'">Download Original Document</a>
    */

    $html = <<<EOF
      <h3>Submission Status</h3>
      <dl class='heypub-sub-status'>
        {$this->imported_date_side_nav($post_id)}
        <dt>Submitted on</dt>
          <dd>{$sub->submission_date}</dd>
        <dt>{$this->xml->normalize_submission_status($sub->status)}</dt>
          <dd>{$sub->status_date}</dd>
        <dt class='days_pending_{$days_pending}'>Days pending</dt>
          <dd>{$sub->days_pending}</dd>
      </dl>
EOF;
    if (!in_array($sub->status,$this->disallowed_states)) {
      $actions = $this->submission_actions('heypub-bulk-submit',true,$sub->status,$post_id);
      $form_post_url = $hp_base->get_form_post_url_for_page($this->slug);
      $html .= <<<EOF
        <form id="posts-filter" action="{$form_post_url}" method="post">
          {$this->sub_class->editor_note_text_area($id)}
          <input type='hidden' name="post[]" value="{$id}" />
          {$actions}
        </form>
EOF;
    }
/*
    // Introduce info about other publishrs later
    if ($sub->manageable_count > 1) {
    ?>
      <h4>Currently with <?php echo ($sub->manageable_count - 1); ?> other <?php echo (($sub->manageable_count - 1) == 1) ? 'publisher' : 'publishers'; ?>:</h4>
    <?php
      echo $hp_base->other_publisher_link($sub->manageable->publisher, $sub);
    ?>
      <p>You can disallow simultaneous submissions in <a href='<?php
       printf('%s/wp-admin/admin.php?page=%s#simu_subs',get_bloginfo('wpurl'),$hp_opt->slug); ?>'>Plugin Options</a></>
    <?php
        }
    ?>
    <?php
        if ($sub->published_count > 0) {
    ?>
        <h4>This work has been previously published by <?php echo ($sub->published_count); ?> other <?php echo (($sub->published_count) == 1) ? 'publisher' : 'publishers'; ?>:</h4>
    <?php
        echo $hp_base->other_publisher_link($sub->published->publisher,$sub);
        }
    ?>

    <?php  }  // end of conditional testing whether submission is in allowed state or not
     ?>

          </td>
          </tr>
        </table>


    <?php
*/
    return $html;

  }
  // Get the notes for this submission
  //  @since 2.7.0
  private function get_notes($id,$editor_id) {
    $notes = $this->subapi->get_submission_notes($id);
    $html =<<<EOF
    <table id='heypub-notes-list' class="widefat post fixed ll-plugin heypub-notes" cellspacing="0">
      <thead>
        <tr>
          <th>Editor</th>
          <th>Date</th>
          <th>Note</th>
        </tr>
      </thead>
      <tbody>
EOF;
    if ($notes['meta']['total'] == 0) {
      $html .= "<tr><td colspan='3' class='heypub-notes not-found'>No notes found for this submission.</td></tr>";
    } else {
      foreach($notes['notes'] as $note) {
        $class = '';
        $editor = $this->get_editor_object($note['editor_id']);
        if ($note['editor_id'] == $editor_id) {
          $class = "class='mine'";
          $editor = 'You';
        }
        $date = $this->get_formatted_date($note['date']);
        $html .= <<<EOF
          <tr {$class}>
            <td>{$editor}</td>
            <td>{$date}</td>
            <td>{$note['note']}</td>
          </tr>
EOF;
      }
    }
    $html .= "</tbody></table>";
    return $html;
  }

  // Fetch the votes and register the vote as a class var for later reference.
  // @since  2.7.0
  private function get_votes($id,$editor_id){
    $this->xml->log("get_votes({$id},{$editor_id})");
    $votes = $this->subapi->get_submission_votes($id,$editor_id);
    $this->xml->log(sprintf("votes: %s",print_r($votes,1)));
    if ($votes['meta']['returned'] == 1) {
      $this->has_voted = $votes['votes'][0]['vote'];
    }
    return $votes;
  }

  // Get the sum total of votes in a display format
  // @since  2.7.0
  private function get_vote_summary_block($votes) {
    if (function_exists('ngettext')) {
      $up = ngettext('vote','votes',$votes['meta']['up']);
      $down = ngettext('vote','votes',$votes['meta']['down']);
    } else {
        $up = '';
        $down = '';
    }
    $display_votes = 'display:none;';
    if ($this->has_voted) { $display_votes = ''; }
    $html .= <<<EOF
      <div id='heypub_vote_sumary' class='heypub-voting heypub-vote_summary' style='{$display_votes}'>
        <a href="#" title="No :(" class='vote-no always-on'>
          <span class="heypub-icons dashicons dashicons-thumbs-down vote-no always-on"></span>
        </a> <span id='votes-down-total'>{$votes['meta']['down']} {$down}</span>
        <a href="#" title="Yes!" class='vote-yes always-on'>
          <span class="heypub-icons dashicons dashicons-thumbs-up vote-yes always-on"></span>
        </a> <span id='votes-up-total'> {$votes['meta']['up']} {$up}</span>
      </div>
EOF;
    return $html;
  }

  // Formats the voting button block
  // @since  2.7.0
  private function vote_buttons_block($votes) {
    $vote_yes = '';
    $vote_no = '';
    if ($this->has_voted) {
      // need to figure out which vote this editor made
      if ($this->has_voted == 'up') {
        $vote_yes = 'on';
      } else {
        $vote_no = 'on';
      }
    }
    $html .= <<<EOF
      <a data-vote='down' href="#" title="No :(" class='vote-no {$vote_no}'>
        <span class="heypub-icons dashicons dashicons-thumbs-down vote-no {$vote_no}"></span>
      </a>
      <a data-vote='up' href="#" title="Yes!" class='vote-yes {$vote_yes}'>
        <span class="heypub-icons dashicons dashicons-thumbs-up vote-yes {$vote_yes}"></span>
      </a>
EOF;
    return $html;
  }

  private function author_bio($sub) {
    if (FALSE != $sub->author->bio) {
      $bio = $sub->author->bio;
    } else {
      $bio = '<i>None provided</i>';
    }
    $html = sprintf('<dt>Author Bio:</dt><dd>%s &nbsp;</dd>',$bio);
    return $html;
  }
  private function submission_summary($sub) {
    $html = null;
    if ($sub->description != '') {
      $html = sprintf('<dt>Summary:</dt><dd>%s</dd>',$sub->description);
    }
    return $html;
  }
  private function word_count($sub) {
    $wc = $this->normalize_word_count($sub);
    // getting weird errors about the type of val for word_count, so explicitly cast here
    $html = sprintf('<dt>Word Count:</dt><dd>%s words</dd>',$wc);
    return $html;
  }
  // Take in a submission id and return a formatted submission block as string
  private function submission_history_block($sid) {
    $block = $this->submission_history_content($sid,'desc');
    return $this->toggle_block($block);
  }

  private function submission_history_content($sid,$order) {
    $this->xml->log("submission_history_content() SID: {$sid}");
    $history = $this->subapi->get_submission_history($sid,$order);
    $this->xml->log(sprintf(" => history:\n%s",print_r($history,1)));
    $rows = '';
    foreach ($history['history'] as $item) {
      $rows .= "\t<li>" . $this->format_submission_history($item) . "</li>\n";
    }
    $block = <<<EOF
      <ul class='post-revisions hide-if-no-js submission-history'>
        {$rows}
      </ul>
EOF;
    return $block;
  }

  // Get an editor object from memory or db
  // @since 2.7.0
  private function get_editor_object($id) {
    if ($id) {
      if ($this->editors["$id"]) {
        $author = $this->editors["$id"];
      } else {
        $author = get_the_author_meta( 'display_name', $id );
        $this->editors["$id"] = $author;
      }
    }
    return $author;
  }

  private function get_formatted_date($d) {
    $date = date_i18n('F j, Y @ H:i:s',strtotime($d));
    return $date;
  }

  // Follows the same format as wp_post_revision_title_expanded()
  // https://developer.wordpress.org/reference/functions/wp_post_revision_title_expanded/
  private function format_submission_history($item){
    $author = $this->get_editor_object( $item['editor_id'] );
    if (!$author) {
      if ($item['name'] == 'submitted') {
        $author = 'Author';
      } else {
        $author = 'Editor';
      }
    }

    $gravatar = get_avatar( $item['editor_id'], 24 );
    $date = $this->get_formatted_date($item['date']);
    $data = sprintf(
         /* translators: post revision title: 1: author avatar, 2: author name, 3: time ago, 4: date */
         __( '%1$s %2$s: %3$s %4$s ago (%5$s)' ),
         $gravatar,
         $author,
         strtolower($item['status']),
         human_time_diff( strtotime( $item['date'] ), current_time( 'timestamp' ) ),
         $date
     );
     return $data;
  }
  private function summary_block($sub) {
    $block = <<<EOF
      {$this->submission_summary($sub)}
      {$this->word_count($sub)}
      {$this->author_bio($sub)}
EOF;
    return $this->toggle_block($block);
  }

  private function toggle_block($block){
    $html = <<<EOF
      <blockquote class='heypub_summary'>
        <dl>{$block}</dl>
      </blockquote>
EOF;
    return $html;
  }

  private function submission_block($sub) {
    // Is this a valid state??
    if (in_array($sub->status,$this->disallowed_states)) {
      $body = sprintf('<h4 class="error">%s</h4>',$sub->body);
    } else {
      $body = $sub->body;
    }
    $html = <<<EOF
      <div id='heypub_submission_body'>
        {$body}
      </div>
EOF;
    return $html;
  }

  /**
  * Display the menu of acceptable state transitions in the menu.
  *
  * @param string $nounce The Nounce for the form
  * @param bool $detail If TRUE will display the 'request author revision' and 'accepted' values.  FALSE by default.
  */
  function submission_actions($nounce,$detail=false,$sel='',$published=false) {
    global $hp_base;
    $html = <<<EOF
    <div class="actions">
      <select name="action">
        <option value="-1">-- Select Action --</option>
EOF;
    if ($detail) {
      if ($published) {
        $accept_text = 'Reimport into WordPress';
        $is_sel = 'selected';
      } else {
        $accept_text = 'Accept for Publication';
        $is_sel = $this->sub_class->select_selected('accept',$sel); // this will default to empty string if either not true
      }
      $html .= <<<EOF
        <option {$is_sel} value="accept">{$accept_text}</option>
        <option {$this->sub_class->select_selected('request_revision',$sel)} value="request_revision">
          Request Author Revision
        </option>
EOF;
    }
    if (!$published) {
      $html .= <<<EOF
        <option {$this->sub_class->select_selected('review',$sel)} value="review">Save for Later Review</option>
EOF;
    }
    $html .= <<<EOF
      <option {$this->sub_class->select_selected('reject',$sel)} value="reject">Reject Submission</option>
      <option {$this->sub_class->select_selected('withdrawn',$sel)} value="withdraw">Withdrawn by Writer</option>
    </select>
    <br/>
    <input type="submit" class="heypub-button button-primary" value="Update Submission" name="doaction" id="doaction" />
EOF;
    if ($detail) {
      $html .= sprintf('<span id="return_to_summary">%s</span>',$hp_base->submission_summary_link('cancel'));
    }
    $html .= wp_nonce_field($nounce);
    $html .= '</div>';
    return $html;
  }
// ---------------------------------------------------------------------------
// End of html
// ---------------------------------------------------------------------------

// Accept Processing Handler - these posts may or may not be in the db already
private function accept_process_submission($req,$uid,$msg=FALSE) {
  global $hp_base;
  check_admin_referer('heypub-bulk-submit');
  $id = $req['post'][0];
  $notes = $req['notes'];
  $this->xml->log(sprintf("accept_process_submission req = \n%s\n USER_ID: %s\nMSG: %s",print_r($req,1),$uid,$msg));

  if ($this->xml->submission_action($id,'accepted',$notes)) {
    $this->xml->log("WE are in the UPDATE/CREATE");
    $sub = $this->xml->get_submission_by_id($id);
    $post_id = $this->create_or_update_post($uid,'pending',$sub);
    $this->xml->log(sprintf("POST ID = %s",$post_id));
  }

  $message = sprintf("%s successfully accepted.<br/><br/>%s been moved to your Posts and put in a 'Pending' status. .",$this->pluralize_submission_message(1),
  ($cnt > 1) ? "These works have" : "This work has" );

  if ($msg) {
    $message .= $msg;
  }
  return $message;
}

// Pre-Accept Handler - Prompt the Admin to create a new user or use an existing user account.
function accept_submission($req) {
  global $hp_base;
  check_admin_referer('heypub-bulk-submit');
  $id = $req['post'][0];
  $sub = $this->xml->get_submission_by_id($id);
  $notes = $req['notes'];
	// do we have this author in the db?
  $user_id = $hp_base->get_author_id($sub->author);
  $this->xml->log("accept_submission($req)");
  $this->xml->log(sprintf("Sub->Author: %s",print_r($sub->author,1)));
  $this->xml->log(sprintf("USER_ID: '%s'",$user_id));
  $this->xml->log(sprintf("REQ = \n%s",print_r($req,1)));

  /*
    LOGIC:
    + if we already have the user, redirect to the accept processor
    + if we don't, then allow user to create this user.
    + Ensure our metadata is associated with the newly created account
    + THEN redirect to the accept processor.
  */

  if (FALSE != $user_id) {
    $url = $hp_base->get_author_edit_url($user_id);
    $msg = sprintf("<br/><br/>The author <b><a href='$url'>%s</a></b> already exists in your database.  Please ensure their information is correct prior to publication.", $sub->author->full_name);
    $message = $this->accept_process_submission($req,$user_id,$msg);
    return $message;
  }
  // If we're still here, we have to create a new user account.  Display the form...
  parent::page('Create New Author Account', '', array($this,'create_new_account_form'), array($sub,$notes));
  return;
}

protected function create_new_account_form($array) {
  global $hp_base;
  $sub = $array[0];
  $notes = $array[1];
  $this->log(sprintf("receives sub and notes: %s",$notes));
  $form_post_url = $hp_base->get_form_post_url_for_page($this->slug);
  $nonce = wp_nonce_field('heypub-bulk-submit');
  $html = <<<EOF
		<p>The author
      <b>
        {$sub->author->first_name} {$sub->author->last_name}
      </b> has not been published by you before.
    </p>
		<p>
      Before you can accept "{$sub->title}" for publication, you need to
      create a user account for this author.
    </p>
    <p>
      We have suggested a username below.
    </p>
		<p><i>
      If this username already exists in your database, this submission will
      be associated with that user.
      <br/>
      If this username does not already
      exist, we will create it here.
    </i></p>
	  <form id="posts-filter" action="{$form_post_url}" method="post">
      <ul>
        <li>
          <input type='hidden' name="post[]" value="{$sub->id}" />
    			<input type='hidden' name="notes" value="{$notes}" />
    			<input type='hidden' name="action" value="create_user" />
    			<label class='heypub' for='username'>Username:</label>
    			<input type='text' name="username" id='username' class='heypub' value="{$sub->author->username}" />
        </li>
      </ul>
      {$nonce}
      <input type="submit" class="heypub-button button-primary" name="doaction" id="doaction" value="Create User &raquo;" />
    </form>
EOF;
  return $html;
}

  // Save For Later Handler - Marks these records for later review.
  private function consider_submission($req) {
    check_admin_referer('heypub-bulk-submit');
    $post = $req['post'];
    $notes = $req['notes'];
    $cnt = 0;
    foreach ($post as $key) {
      if ($this->xml->submission_action($key,'under_consideration',$notes)) {
        $cnt++;
      }
    }
    $message = sprintf('%s successfully saved for later editorial review',$this->pluralize_submission_message($cnt));
    return $message;
  }
  private function create_or_update_post($user_id,$status,$sub) {
    $post_id = $this->get_post_id_by_title("$sub->title",$user_id) ;
    $category = 1;  // the 'uncategorized' category
    $map = $this->xml->get_category_mapping();
    // printf("<pre>Sub object looks like : %s</pre>",print_r($sub,1));
    $cat = sprintf("%s",$sub->category->id);
    if ($map["$cat"]) {
      $category = $map["$cat"]; // local id
    }
    // this piece does not exist - create it
    $post = array();
    $post['post_title'] = $sub->title;
    $post['post_content'] = $sub->body;
    $post['post_status'] = $status;
    $post['post_author'] = $user_id;
    $post['post_category'] = array($category);  # this should always be an array.
    // if the post ID was found for this title/author - then this is an update, not an insert
    // so et the post id appropriately
    if ($post_id) {
      $post['ID'] = $post_id;
    }
    $post_id = wp_insert_post( $post );
    // ensure meta data is updated - this is invisible to the editor, but allows us to find post later
    update_post_meta($post_id, HEYPUB_POST_META_KEY_SUB_ID, "$sub->id");
    return $post_id;
  }
  /**
  * Create a User account prior to Accepting Submission
  *
  * @param array $req Form POST object
  */
  private function create_user($req) {
    global $hp_base;
    if (!$req['username']) {
     $this->xml->error = "Oops - looks like you didn't provide a valid username";
     $this->xml->print_webservice_errors(false);
     $this->accept_submission($req);
     return FALSE;
    }
    $id = $req['post'][0];
    $sub = $this->xml->get_submission_by_id($id);
    $notes = $req['notes'];
    // do we have this author in the db?
    $user_id = $hp_base->create_author($req['username'],$sub->author);
    if (!is_wp_error($user_id)) {
      $url = $hp_base->get_author_edit_url($user_id);
      $msg = sprintf("<br/><br/>The author <b><a href='$url'>%s</a></b> was created in your database.  Please ensure their information is correct prior to publication.", $sub->author->full_name);
      $message = $this->accept_process_submission($req,$user_id,$msg);
      return $message;
    }
    else {
      $err = $user_id->get_error_message();
      $this->xml->error = <<<EOF
        We ran into problems creating the user account for {$req['username']}.
        <br/>
        {$err}
        <br/>
        Please use a different username.
EOF;
      $this->xml->print_webservice_errors(false);
      $this->accept_submission($req);
      return FALSE;
    }
  }

/*
   * Get all the submissions that have already been imported
   */
  private function get_accepted_post_ids() {
    global $wpdb;
    // $id is the HP post id
    $results = $wpdb->get_results($wpdb->prepare("SELECT post_id, meta_value as sub_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value is not null", HEYPUB_POST_META_KEY_SUB_ID));
    $ids = array();
    foreach ($results as $hash) {
      $sub_id = "$hash->sub_id";
      $ids["$sub_id"] = $hash->post_id;
    }
    return $ids;
  }

  //
  // Display the 'Local' description for this category - or the HP value if the internal mapping has not been set
  // $id is the HP genre ID
  // $default is the HP genre to display if we don't find a match
  // otherwise, display the local WP category
  private function get_display_category($id,$default) {
    // $id is the remote category id from HP
    $map = $this->config->get_config_option('category_map');
    // If we don't have an internal value, use the passed in one from HP
    $display = $default;
    if ($map) {
      foreach ($this->wp_categories as $idx=>$hash) {
        if (isset($map["$id"]) && ($map["$id"] == $hash->cat_ID)) {
          $display = $hash->cat_name;
        }
      }
    }
    return $display;
  }
  /*
   * If submission has already been imported, the post ID will be stored in post meta
   */
  private function get_post_id_by_submission_id($id) {
    global $wpdb;
    // $id is the HP post id
    $post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", HEYPUB_POST_META_KEY_SUB_ID,$id));
    if ($post_id) { return $post_id; }
    return false;
  }
  private function get_post_id_by_title($title,$user_id){
    global $wpdb;
    $post_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_author = %s",$title,$user_id));
    return $post_id;
  }
  /*
   * Get the post's modified date for listing in submission status
   * $id = POST id
   */
  private function get_post_mod_date($id) {
    $mod_date = get_post_modified_time( 'Y-m-d', false, $id);
    if ($mod_date) { return $mod_date; }
    return false;
  }
  // This will return the HP key if the post id is found
  private function get_submission_id_by_post_id($post_id) {
    global $wpdb;
    // $id is the HP post id
    $hp_id = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s AND post_id = %s", HEYPUB_POST_META_KEY_SUB_ID, $post_id));
    if ($hp_id) {
      return $hp_id;
    }
    return false;
  }
  // This function is called by the post-processor hook that detects when accepted works are 'trashed' or 'deleted'
  // Mark as withdrawn by writer as that is less 'rejecty'
  public function delete_post_cleanup($post_id) {
    if ($hp_id = $this->get_submission_id_by_post_id($post_id)) {
      $this->xml->submission_action($hp_id,'publisher_withdrew');
    }
    return true;
  }

  // Rejection Handler - these posts may or may not be in the db
  private function reject_submission($req) {
    check_admin_referer('heypub-bulk-submit');
    $post = $req['post'];
    $notes = $req['notes'];
    $cnt = 0;
    foreach ($post as $key) {
      if ($this->xml->submission_action($key,'rejected',$notes)) {
        $cnt++;
        // need to see if this post has been previously 'accepted'
        if ($post_id = $this->get_post_id_by_submission_id($key)) {
          // we force deletes
          wp_delete_post( $post_id, true );
        }
      }
    }

    $message = sprintf('%s successfully rejected',$this->pluralize_submission_message($cnt));
    return $message;
  }
  // Withdraw Handler - these posts may or may not be in the db
  private function withdraw_submission($req) {
    check_admin_referer('heypub-bulk-submit');
    $post = $req['post'];
    $notes = $req['notes'];
    $cnt = 0;
    foreach ($post as $key) {
      if ($this->xml->submission_action($key,'publisher_withdrew',$notes)) {
        $cnt++;
        // need to see if this post has been previously 'accepted'
        if ($post_id = $this->get_post_id_by_submission_id($key)) {
          // we force deletes
          wp_delete_post( $post_id, true );
        }
      }
    }

    $message = sprintf('%s successfully marked as withdrawn',$this->pluralize_submission_message($cnt));
    return $message;
  }
  // Request a revision from the writer
  private function request_revision($req) {
    check_admin_referer('heypub-bulk-submit');
    $post = $req['post'][0];
    $notes = $req['notes'];
    if ($this->xml->submission_action($post,'publisher_revision_requested',$notes)) {
      $message = "An email has been sent to the author requesting they submit a new revision of their work.";
    } else {
      $message = "Unable to send the revision request!";
    }
    return $message;
  }

  private function pluralize_submission_message($cnt) {
    if ($cnt == 1) {
      return '1 submission';
    } else {
      return sprintf('%s submissions',$cnt);
    }
  }

  // This function is called by the post-processor hook that detects when accepted works are 'published'
  public function publish_post($post_id) {
    if ($hp_id = $this->get_submission_id_by_post_id($post_id)) {
      $this->xml->submission_action($hp_id,'published');
    }
    return true;
  }

  /**
  * Manage the content of the revisions box
  */
  public function revisions_meta_box() {
    remove_meta_box( 'revisionsdiv', 'post', 'normal' );
    add_meta_box( 'revisionsdiv', 'Revision History', array($this,'revisions_meta_box_content'), 'post', 'normal', 'high' );
  }
  public function revisions_meta_box_content($post) {
    $sub_id = $this->get_submission_id_by_post_id($post->ID);

    if ($sub_id) {
      $hblock = $this->submission_history_content($sub_id,'asc');
      echo "<h4>Submission:</h4>";
      echo "<p>{$hblock}</p>";
      echo "<h4>Revisions:</h4>";
    }
    wp_list_post_revisions( $post );

  }
  // Normalize the word count, dependent on where it comes in from
  private function normalize_word_count($obj) {
    $wc = '?';
    if (FALSE != $obj->s_word_count && $obj->s_word_count > 0) {
      $wc = number_format((float)"$obj->s_word_count");
    }
    elseif (FALSE != $obj->word_count && $obj->word_count > 0) {
      $wc = number_format((float)"$obj->word_count");
    }
    return $wc;
  }

}
?>
