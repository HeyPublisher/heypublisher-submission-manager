<?php
namespace HeyPublisher\Page;

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }
/**
 * HeyPublisher class for displaying email templates
 */
// Load the class files and associated scoped functionality
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Page.class.php');
require_once(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/API/Email.class.php');

class Email extends \HeyPublisher\Page {

  var $emailapi = null;
  var $page = '_email';

  public function __construct() {
  	parent::__construct();
    $this->slug .= $this->page;
    $this->emailapi = new \HeyPublisher\API\Email;

  }

  public function __destruct() {
  	parent::__destruct();
  }

  public function action_handler() {
    if (isset($_REQUEST['action'])) {
      if ($_REQUEST['action'] == 'create' || $_REQUEST['action'] == 'update' ) {
        $this->validate_nonced_field();
        $this->message = $this->emailapi->update_template($_POST);
      }
      elseif ($_REQUEST['action'] == 'delete') {
        $this->validate_nonced_field();
        $this->message = $this->emailapi->delete_template($_REQUEST['delete']);
      }
      else {
        // Display the create / edit form
        parent::page('Email Template', '', array($this,'edit_email_form'),$_REQUEST['action']);
        return;
        // early exit for good behavior
      }
    }
    if ($this->message) {
      if ($this->emailapi->api->error) {
        $this->xml->error = $this->emailapi->api->error; # TODO: Fix this!!
        $this->xml->print_webservice_errors(true);
      } else {
        $this->print_message_if_exists();
      }
    }
    parent::page('Email Templates', '', array($this,'list_emails'));
  }

  // generate the Help menu
  public function help_menu() {
    $screen = get_current_screen();

    $screen->add_help_tab(
      array(
        'id'	    => sprintf('%s_help', $this->slug),
        'title'	  => __('Keyword Substitution'),
        'content' => $this->help_substitution_text()
      )
    );
    $screen->add_help_tab(
      array(
        'id'	    => sprintf('%s_help_example', $this->slug),
        'title'	  => __('Example'),
        'content' => $this->help_example()
      )
    );
    $screen->add_help_tab(
      array(
        'id'	    => sprintf('%s_help_sub_states', $this->slug),
        'title'	  => __('Submission States'),
        'content' => $this->help_submission_states()
      )
    );

  }
  public function help_submission_states() {
    $img = sprintf('<img src="%s/images/submission_states.gif">',HEY_BASE_URL);
    $html = <<<EOF
    <h2>
      The Submission States
    </h2>
    <p>
      Submissions generally flow through these submission states within HeyPublisher.
      You can create custom email responses for almost all of these.
    </p>
    <p>
      {$img}
    </p>


EOF;
    return $html;
  }

  public function help_example() {
    $html = <<<EOF
    <h2>
      Example of Keyword Substitution
    </h2>
    <p>
      <b>Creating an email with the following:</b>
    </p>
    <pre>
Dear [WriterFirstName],

Thank you so much for submitting "[SubmissionTitle]" to us.  All of us here at [PublisherName]
really enjoyed reading this piece and we can't wait to publish it.

We will be in touch shortly.

Sincerely,
[EditorName]
</pre>
<p>
  <b>Would be translated to something like this when sent:</b>
</p>
<pre>
Dear Janet,

Thank you so much for submitting "A Night to Remember" to us.  All of us here at Amazing Stories
really enjoyed reading this piece and we can't wait to publish it.

We will be in touch shortly.

Sincerely,
Brad
</pre>


EOF;
    return $html;
  }

  public function help_substitution_text() {
    $html = <<<EOF
    <h2>
      Keyword Substitutions Explained
    </h2>
    <p>
      To personalize the emails sent to your writers, insert any of the following
      keywords into the subject or body of the email you create.
    </p>
    <p>
      The keyword will be substituted with actual values from the submission or your
      publication at the time the email is sent out.
    </p>
    <p>Keywords are case-sensitive and must include the enclosing square braces when used.</p>
    <table class="widefat post fixed ll-plugin" cellspacing="0">
      <thead>
        <tr>
          <th style='width:20%;'>Keyword</th>
          <th style='width:80%;'>Converted To</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><b>[SubmissionTitle]</b></td>
          <td>The title of the submission.</td>
        </tr>
        <tr>
          <td><b>[SubmissionURL]</b></td>
          <td>The URL to access the submission on HeyPublisher.com.  This URL is used by writers to update their submission, if requested.</td>
        </tr>
        <tr>
          <td><b>[WriterFirstName]</b></td>
          <td>The first name of the writer who submitted the work for consideration.</td>
        </tr>
        <tr>
          <td><b>[WriterLastName]</b></td>
          <td>The last name of the writer.</td>
        </tr>
        <tr>
          <td><b>[PublisherName]</b></td>
          <td>The title of your publication.</td>
        </tr>
        <tr>
          <td><b>[PublisherURL]</b></td>
          <td>The URL of your publication.</td>
        </tr>
        <tr>
          <td><b>[EditorName]</b></td>
          <td>The first and last name of your editor, as set on the <b>Plugin Options</b> screen.</td>
        </tr>
        <tr>
          <td><b>[EditorNoteToWriter]</b></td>
          <td>The contents of the note written to the author on the Submission Review screen.</td>
        </tr>
        <tr>
          <td><b>[UserID]</b></td>
          <td>A unique ID identifying the author in HeyPublisher.  This value is guaranteed to be unique across all HeyPublisher accounts and is not changable once set.  It could be used by you as an alternative ID for the author.</td>
        </tr>
        <tr>
          <td><b>[Username]</b></td>
          <td>A unique username picked by the writer.  Guaranteed to be unique within HeyPublisher, but may be changed by the writer if they choose.  If you accept work by the author, HeyPublisher will attempt to create an account using this same username in WordPress, if available.</td>
        </tr>
      </tbody>
    </table>
EOF;
    return $html;
  }

  protected function edit_email_form($id) {
    $cancel = $this->get_form_url_for_page();
    $state = ucwords($id);
    if ($id == 'new') {
      $title = 'Create New Email Template';
      $email = [];
      $button = 'create';
      $block1 = <<<EOF
      <p>
        To create a new email template, simply fill out the form below with the message you want delivered to the writer.
      </p>
EOF;
      $block2 = <<<EOF
      <p>
        If the <b>Submission State</b> you want is not listed, ensure you have set the writer notification option to <code>YES</code> on the <b>Plugin Options</b> page.
      </p>
EOF;
      $submission_states = $this->emailapi->get_submission_states();
      if (!$submission_states && $this->emailapi->api->error) {
        $this->xml->error = $this->emailapi->api->error; # TODO: Fix this!!
        $this->xml->print_webservice_errors(true);
      }
      $options = '';
      foreach($submission_states as $x => $hash) {
        $options .=<<< EOF
          <option value='{$hash['id']}'>{$hash['submission_state']}</option>
EOF;
      }

      $states = <<<EOF
      <select name="hp_email[submission_state]" id="hp_submission_state" class='heypub' />
        {$options}
      </select>
EOF;
    }
    else {
      $button = 'update';
      $block1 = '';
      $block2 = '';
      $states = <<<EOF
      <input type='hidden' name="hp_email[submission_state]" value="{$id}" />
      <input type="text" name="hp_email_disabled" id="hp_submission_state" class='heypub' value="{$state}" disabled="disabled" />
EOF;
      $title = sprintf('Edit %s Template',$state);
      $email = $this->emailapi->get_email($id);
    }

    $nonce = $this->get_nonced_field();
    $action = $this->get_form_url_for_page($button);
    $html = <<<EOF
    <h3 class='first'>{$title}</h3>
    {$block1}
    <p>Click on the <b>HELP</b> link above to learn how to customize this email.</p>
    {$block2}
    <form method="post" action="{$action}">
    <ul>
      <li>
        <label class='heypub' for='hp_submission_state'>Submission State</label>
        {$states}
      </li>
      <li>
        <label class='heypub' for='hp_subject'>Email Subject</label>
        <input type="text" name="hp_email[subject]" id="hp_subject" class='heypub' value="{$email['subject']}" />
      </li>
      <li>
        <label class='heypub' for='hp_body'>Email Body</label>
        <textarea name="hp_email[body]" id="hp_body" class='heypub'>{$email['body']}</textarea>
      </li>
    </ul>
    {$nonce}
    <input type="submit" class="heypub-button button-primary" name="{$button}_template" id="{$button}_template" value="{$button} &raquo;" />
    <a href="{$cancel}">cancel</a>
    </form>

EOF;
    return $html;
  }

  protected function list_emails() {
    $res = $this->emailapi->get_emails();
    if (!$res && $this->emailapi->api->error) {
      $this->xml->error = $this->emailapi->api->error; # TODO: Fix this!!
      $this->xml->print_webservice_errors(true);
    }
    $emails = $res['email_templates'];
    $nonce = $this->get_nonced_field();
    $action = $this->get_form_url_for_page('new');
    if ($res['meta']['total'] > $res['meta']['returned']) {
      $button = <<<EOF
        <input type="submit" class="heypub-button button-primary" name="create_button" id="create_button" value="Add New &raquo;" />
EOF;
    }
    else {
      $button = <<<EOF
        <input type="submit" class="heypub-button button-primary" disabled='disabled' name="create_button" id="create_button" value="No More Templates" />
EOF;
    }
    $html .= <<<EOF
      <script type='text/javascript'>
        jQuery(function() {
          HeyPublisher.emailListInit();
        });
      </script>
      <h3 class='first'>All Templates</h3>
      <p>To add a new custom email template, click on the 'Add New' button below.</p>
      <p>Click on the pencil icon to edit an existing template</p>
      <table class="widefat post fixed ll-plugin" cellspacing="0" id='heypub_emails'>
        <thead>
        	<tr>
          	<th style='width:25%;'>Submission State</th>
          	<th style='width:65%;'>Email Subject Line</th>
          	<th style='width:10%;'>Action</th>
        	</tr>
        </thead>
        <tfoot />
        <tbody>
        {$this->format_email_list($emails)}
        </tbody>
      </table>
      <form method="post" action="{$action}">
        {$nonce}
        {$button}
      </form>
EOF;
    // '
    return $html;
  }
  private function format_email_list($emails) {
    $html = '';
    if (!empty($emails)) {
      foreach($emails as $x => $hash) {
        $s = implode(' ',explode('_',$hash['submission_state']));
        $state = ucwords($s);
        $edit = $this->get_form_url_for_page($hash['submission_state']);
        $delete = $this->get_form_url_for_page('delete',$hash['submission_state']);
        $html .= <<<EOF
          <tr>
            <td>{$state}</td>
            <td>{$hash['subject']}</td>
            <td>
              <a href="{$edit}" title="Edit email template" style="">
                <span class="heypub-icons dashicons dashicons-edit"></span>
              </a>
              <a data-email='{$state}' href="{$delete}" title="Delete email template" style="">
                <span class="heypub-icons dashicons dashicons-trash"></span>
              </a>
            </td>
          </tr>
EOF;
      }
    } else {
      $html .= <<<EOF
        <tr><td colspan=3 class='heypub_no_emails'>No Templates Defined</td></tr>
EOF;
    }
    return $html;
  }

}
?>
