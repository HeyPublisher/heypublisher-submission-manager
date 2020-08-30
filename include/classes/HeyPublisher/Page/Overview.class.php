<?php
namespace HeyPublisher\Page;

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

/**
 * HeyPublisher class for handling main page
 *
 */

// Load the class files and associated scoped functionality
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Page.class.php');
require_once(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/API/Publisher.class.php');
class Overview extends \HeyPublisher\Page {
  var $pubapi = null;
  var $page = '-submission-manager';

  public function __construct() {
  	parent::__construct();
    $this->pubapi = new \HeyPublisher\API\Publisher;
    $this->slug .= $this->page;
    $this->log("slug = {$this->slug}");
  }

  public function __destruct() {
  	parent::__destruct();
  }

  protected function deactivate_prompt() {
    $this->log("deactivate_prompt");
    // only deleting the options and possibly deactivating the plugin
    heypub_uninit();
    // TODO: move this define to central place at some point
    $plugin_name = HEY_DIR.'/heypublisher-sub-mgr.php';
    $url = wp_nonce_url("plugins.php?action=deactivate&plugin=$plugin_name","deactivate-plugin_$plugin_name");
    $html = <<<EOF
      <p>
        <span class='uninstall'>Deleting HeyPublisher Options ... </span>
        <span class='uninstall ok'>DONE</span>
      </p>
      <p>
        <a href='{$url}' title='Deactivate HeyPublisher Plugin' class="delete">
          Click HERE to deactivate HeyPublisher Plugin
        </a>
      </p>
EOF;
    return $html;
  }
  protected function uninstall_prompt() {
    $url = $this->nonced_url(['action' => 'delete_options'],'heypub_delete_options');
    $html = <<<EOF
      <p>
        You can uninstall the HeyPublisher plugin at anytime.
        The works you have already published <i>will not</i>
        be affected.
      </p>
      <p>
        After uninstalling this plugin you will no longer be able to accept
        unsolicited submissions from HeyPublisher authors.
        <br/>
        The ability to control reading periods, specific genres and
        other features of this plugin will no longer be active.
      </p>
      <p>
        <a href="{$url}" class='uninstall'>Uninstall HeyPublisher Plugin</a>
      </p>
EOF;
    return $html;
  }

  public function page_prep()  {
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'uninstall_plugin') {
      parent::page('Uninstall HeyPublisher', 'Before you continue...', array($this,'uninstall_prompt'));
    } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_options') {
      check_admin_referer('heypub_delete_options');
      parent::page('Uninstalled HeyPublisher', '', array($this,'deactivate_prompt'));
    } else {
      parent::page('Overview', 'Welcome', array($this,'content'));
    }
  }

  protected function get_editor_history() {
    $html = '';
    if ($this->config->is_validated) {
      $args = array('role__in' => array('Editor', 'Administrator'), 'orderby' => 'display_name');
      $editors = get_users( $args );
      $history = $this->pubapi->get_editor_history();
      $this->log(sprintf("EDITORS: %s", print_r($editors,1)));

      $html .= <<<EOF
      <h3>Editor Statistics</h3>
      <p>The number of submissions each Editor / Administrator has taken action on in the last 30 days</p>
      <table class="widefat post fixed ll-plugin">
        <thead>
          <tr>
            <th>Name</th>
            <th>Read</th>
            <th>Considered</th>
            <th>Rejected</th>
            <th>Accepted</th>
          </tr>
        </thead>
        <tbody id='the-list'>

EOF;
      foreach($editors as $idx=>$editor) {
        $class = '';
        if ($idx & 1) {
          $class= ' class="alternate"';
        }
        $data = $this->get_editor_stats($history,$editor->ID);
        $html .= <<<EOF
          <tr {$class}>
            <td>{$editor->display_name}</td>
            <td>{$data['read']}</td>
            <td>{$data['under_consideration']}</td>
            <td>{$data['rejected']}</td>
            <td>{$data['accepted']}</td>
          </tr>
EOF;
      }
      $html .= <<<EOF
        </tbody>
      </table>
EOF;
    }
    return $html;
  }

  private function get_editor_stats($history,$id){
    $data = array('read' => 0, 'under_consideration' => 0, 'rejected' => 0, 'accepted' => 0);
    if ($history['editors'] && in_array($id,$history['editors'])) {
      foreach($history['history'] as $set) {
        if ($set['editor_id'] == $id) {
          $data = $set;
          break;
        }
      }
    }
    return $data;
  }

  protected function content() {
    global $hp_base;
    if (!$this->config->is_validated) {
      $val = "<a href='". heypub_get_authentication_url() . "'>CLICK HERE to VALIDATE</a>";
    } else {
      $val = date('F jS, Y',strtotime($this->config->is_validated));
    }
    $ver = HEYPUB_PLUGIN_VERSION;
    $blog = get_bloginfo('name');
    $verdate = date('F jS, Y',strtotime($this->config->get_install_option('version_current_date')));
    $editors = $this->get_editor_history(); // this can only be launched after 1.6.0 has been live 30 days
    $stats = $this->pubapi->get_publisher_stats();
    // ensure they're saved locally for posterity ... for some reason, though not sure why
    $this->xml->sync_publisher_info($stats);

    $html = <<<EOF
      <p>With HeyPublisher you can accept unsolicited submissions from writers without
      having to create user accounts for them in your blog, magazine, or Wordpress-powered site.

      You control the submissions you receive and all communications with your writers are handled automatically.  </p>
      {$editors}
      <h3>Plugin Statistics</h3>
      <table class="widefat post fixed ll-plugin">
        <thead>
          <tr>
            <th>Plugin Version</th>
            <th>Build #</th>
            <th>Build Date</th>
            <th>Plugin Validated</th>
          </tr>
        </thead>
        <tbody id='the-list'>
          <tr>
            <td>{$ver}</td>
            <td>{$this->config->get_install_option('version_current')}</td>
            <td>{$verdate}</td>
            <td>{$val}</td>
          </tr>
        </tbody>
      </table>

EOF;

      if ($this->config->is_validated) {
        // fetch the publisher info and update the local db with latest stats
        $home_last = $stats['homepage']['updated'];
        $guide_last = $stats['guidelines']['updated'];
        $hl = (!empty($home_last)) ? date('F jS, Y',strtotime($home_last)) : '--';
        $gl = (!empty($guide_last)) ? date('F jS, Y',strtotime($guide_last)) : '--';
        $html .= <<<EOF
        <h3>Publication Statistics</h3>
        <table class="widefat post fixed ll-plugin">
          <tbody id='the-list'>
          <tr class='header'>
            <td>Homepage Indexed</td>
            <td>Guidelines Indexed</td>
            <td># Comments</td>
            <td># Favorites</td>
          </tr>
          <tr>
            <td>{$hl}</td>
            <td>{$gl}</td>
            <td>n/a</td>
            <td>n/a</td>
          </tr>
          <tr class='header alternate'>
            <td>Submissions Received</td>
            <td>Pending Review</td>
            <td>Published</td>
            <td>Rejected</td>
          </tr>
          <tr>
            <td>{$stats['submissions']['total']}</td>
            <td>{$hp_base->submission_summary_link($stats['submissions']['open'])}</td>
            <td>{$stats['submissions']['published']}</td>
            <td>{$stats['submissions']['rejected']}</td>
          </tr>
          <tr class='header alternate'>
            <td>Avg. Response Time</td>
            <td>Subs Open 30 Days</td>
            <td>Subs Open 60 Days</td>
            <td>Subs Open 90 Days</td>
          </tr>
          <tr>
            <td class='t'>{$stats['response_time']['rejected']} Days</td>
            <td class='approved'>{$stats['aging']['thirty']}</td>
            <td class='waiting'>{$stats['aging']['sixty']}</td>
            <td class='spam'>{$stats['aging']['ninety']}</td>
          </tr>
        </tbody>
      </table>

EOF;
    } // end of the if validated block
    $uninstall = $this->nonced_url(['action' => 'uninstall_plugin']);

    $html .= <<<EOF
    <!--
    <h3>How to Control the Style of the Submission Form</h3>
    <p>This plugin uses your current theme stylesheet to control the layout of the submission form.</p>
    <p>If you want to customize how the submission form looks, please <a href="{HEYPUB_SVC_URL_STYLE_GUIDE}" target=_new title='Click to open the style guide in a new window'>read the style guide</a>.</p>
    -->
    <h3>Uninstall Plugin</h3>
    <p>
      <a href='{$uninstall}' title='Uninstall HeyPublisher Plugin' class="uninstall">
        Click to Uninstall HeyPublisher Plugin
      </a>
    </p>


EOF;
    return $html;
  }


}
?>
