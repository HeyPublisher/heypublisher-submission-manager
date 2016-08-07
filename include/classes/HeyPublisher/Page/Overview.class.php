<?php
namespace HeyPublisher\Page;

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

/**
 * HeyPublisher class for handling main page
 *
 */

// Load the class files and associated scoped functionality
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Page.class.php');
class Overview extends \HeyPublisher\Page {


  public function __construct() {
  	parent::__construct();
    // $this->slug .= '_main';
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

  public function page()  {
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'uninstall_plugin') {
      parent::page('Uninstall HeyPublisher', 'Before you continue...', array($this,'uninstall_prompt'));
    } elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_options') {
      check_admin_referer('heypub_delete_options');
      parent::page('Uninstalled HeyPublisher', '', array($this,'deactivate_prompt'));
    } else {
      parent::page('Overview', 'Welcome', array($this,'content'));
    }
  }

  protected function content() {
    global $hp_base;
    if (!$this->xml->is_validated) {
      $val = "<a href='". heypub_get_authentication_url() . "'>CLICK HERE to VALIDATE</a>";
    } else {
      $val = $this->xml->is_validated;
    }
    $ver = HEYPUB_PLUGIN_VERSION;
    $blog = get_bloginfo('name');
    $html = <<<EOF
      <p>With HeyPublisher you can accept unsolicited submissions from writers without
      having to create user accounts for them in your blog, magazine, or Wordpress-powered site.

      You control the submissions you receive and all communications with your writers are handled automatically.  </p>
      <h3>Statistics</h3>
      <table class="widefat post fixed ll-plugin">
        <tbody id='the-list'>
          <tr class='header alternate'>
            <td>Plugin Version</td>
            <td>Build #</td>
            <td>Build Date</td>
            <td>Plugin Validated</td>
          </tr>
          <tr>
            <td>{$ver}</td>
            <td>{$this->xml->get_install_option('version_current')}</td>
            <td>{$this->xml->get_install_option('version_current_date')}</td>
            <td>{$val}</td>
          </tr>
EOF;

      if ($this->xml->is_validated) {

                  // $pf = "<td>%s &nbsp;&nbsp; [ %s %% ]</td>";
                  // if ($p[total_published_subs] && $p[total_published_subs] > 0) {
                  //   printf( $pf, $p[total_published_subs], $p[published_rate]);
                  // } else {
                  //   printf($pf,0,0);
                  // }
                  // if ($p[total_rejected_subs] && $p[total_rejected_subs] > 0) {
                  //   printf( $pf, $p[total_rejected_subs], $p[rejected_rate]);
                  // } else {
                  //   printf($pf,0,0);
                  // }


        // fetch the publisher info and update the local db with latest stats
        $p = $this->xml->sync_publisher_info();
        $home_last = $this->xml->get_config_option('homepage_last_validated_at');
        $guide_last = $this->xml->get_config_option('guide_last_validated_at');
        $hl = (!empty($home_last)) ? $home_last : '--';
        $gl = (!empty($guide_last)) ? $guide_last : '--';
        $html .= <<<EOF
          <tr class='header alternate'>
            <td>Homepage Last Indexed</td>
            <td>Guidelines Last Indexed</td>
            <td># Comments</td>
            <td># Favorites</td>
          </tr>
          <tr>
            <td>{$hl}</td>
            <td>{$gl}</td>
            <td>{$p['writer_comments']}</td>
            <td>{$p['writer_favorites']}</td>
          </tr>
          <tr class='header alternate'>
            <td>Submissions Received</td>
            <td>Pending Review</td>
            <td>Submissions Published [%]</td>
            <td>Submissions Rejected [%]</td>
          </tr>
          <tr>
            <td>{$p['total_subs']}</td>
            <td>{$hp_base->submission_summary_link($p['total_open_subs'])}</td>
            <td>{$p['published_rate']}</td>
            <td>{$p['rejected_rate']}</td>
          </tr>
          <tr class='header alternate'>
            <td>Avg. Response Time</td>
            <td>Subs Open 30 Days</td>
            <td>Subs Open 60 Days</td>
            <td>Subs Open 90 Days</td>
          </tr>
          <tr>
            <td class='t'>{$p['avg_response_days']} Days</td>
            <td class='approved'>{$p['total_thirty_late']}</td>
            <td class='waiting'>{$p['total_sixty_late']}</td>
            <td class='spam'>{$p['total_ninety_late']}</td>
          </tr>
EOF;
    } // end of the if validated block
    $uninstall = $this->nonced_url(['action' => 'uninstall_plugin']);

    $html .= <<<EOF
        </tbody>
      </table>
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
