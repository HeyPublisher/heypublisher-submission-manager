<?php
namespace HeyPublisher\Page;

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }
/**
 * HeyPublisher class for displaying email templates
 */
// Load the class files and associated scoped functionality
load_template(dirname(__FILE__) . '/../Page.class.php');
class Email extends \HeyPublisher\Page {

  public function __construct() {
  	parent::__construct();
    $this->slug .= '_email';
  }

  public function __destruct() {
  	parent::__destruct();
  }

  public function action_handler() {
    parent::page('Email Templates', '', array($this,'list_emails'));
  }
  public function help_menu() {
    $screen = get_current_screen();

    $screen->add_help_tab(
      array(
        'id'	    => $this->slug .= '_help',
        'title'	  => __('Keyword Substitution'),
        'content' => $this->help_substitution_text()
      )
    );
    $screen->add_help_tab(
      array(
        'id'	    => $this->slug .= '_help_example',
        'title'	  => __('Example'),
        'content' => $this->help_example()
      )
    );

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

Thank you so much for submitting "An Night to Remember" to us.  All of us here at Amazing Stories
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
          <td>The title of the submission</td>
        </tr>
        <tr>
          <td><b>[WriterFirstName]</b></td>
          <td>The first name of the writer who sent the submission</td>
        </tr>
        <tr>
          <td><b>[WriterLastName]</b></td>
          <td>The last name of the writer who sent the submission</td>
        </tr>
        <tr>
          <td><b>[PublisherName]</b></td>
          <td>The name of your publication</td>
        </tr>
        <tr>
          <td><b>[PublisherURL]</b></td>
          <td>The URL of your publication</td>
        </tr>
        <tr>
          <td><b>[EditorName]</b></td>
          <td>The first and last name of your editor</td>
        </tr>
        <tr>
          <td><b>[EditorNoteToWriter]</b></td>
          <td>The contents of the note written to the author on the submission review screen</td>
        </tr>
      </tbody>
    </table>
EOF;
    return $html;
  }

  protected function list_emails() {
    $base = HEYPUB_SVC_URL_BASE;
    $uid = $this->xml->user_oid;
    $pid = $this->xml->pub_oid;
    $admin_url = sprintf("%s/wp-admin/load-styles.php?c=1&dir=ltr&load=admin-bar,wp-admin,buttons&ver=%s",get_bloginfo('wpurl'), get_bloginfo('version'));
    $url = sprintf('%s/response_template/index/%s/%s?v=%s&css=%s',$base,$uid,$pid,HEYPUB_PLUGIN_BUILD_NUMBER,urlencode($admin_url));
    $html = <<<EOF
<iframe src="$url" width='100%' height='500' scrolling='auto'> </iframe>
EOF;
    return $html;
  }
}
?>
