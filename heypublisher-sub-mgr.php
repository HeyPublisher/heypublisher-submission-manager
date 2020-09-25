<?php
/*
Plugin Name: HeyPublisher Submission Manager
Plugin URI: https://github.com/HeyPublisher/heypublisher-submission-manager
Description: HeyPublisher is a better way of managing submissions for your WordPress-powered publication.
Author: HeyPublisher
Author URI: https://www.heypublisher.com
Version: 3.1.1
Requires at least: 4.0


  Copyright 2010-2014 Loudlever, Inc. (wordpress@loudlever.com)
  Copyright 2014-2018 Richard Luck (https://github.com/aguywithanidea/)
  Copyright 2019-2020 HeyPublisher, LLC (https://www.heypublisher.com/)

  Permission is hereby granted, free of charge, to any person
  obtaining a copy of this software and associated documentation
  files (the "Software"), to deal in the Software without
  restriction, including without limitation the rights to use,
  copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the
  Software is furnished to do so, subject to the following
  conditions:

  The above copyright notice and this permission notice shall be
  included in all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
  OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
  HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
  WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
  OTHER DEALINGS IN THE SOFTWARE.

*/

/*
 Older versions of PHP may not define DIRECTORY_SEPARATOR so define it here,
 just in case.
*/
if(!defined('DIRECTORY_SEPARATOR')) {
  define('DIRECTORY_SEPARATOR','/');
}
/**
*  DEFINITIONS
*/
define('HEY_DIR', dirname(plugin_basename(__FILE__)));


/*
---------------------------------------------------------------------------------
  OPTION SETTINGS

  1.1.0 => 29
  1.2.0 => 35
  1.2.4 => 38
  1.3.0 => 40
  1.4.1 => 45
  1.4.2 => 48
  1.4.3 => 49
  1.4.4 => 50
  1.4.5 => 51
  1.5.0 => 52
  1.5.1 => 53
  2.0.0 => 60
  2.0.1 => 61
  2.1.0 => 62
  2.2.0 => 63
  2.3.0 => 64
  2.4.0 => 65
  2.5.0 => 66
  2.6.0 => 67
  2.6.1 => 68
  2.6.2 => 69
  2.6.3 => 70
  2.7.0 => 71
  2.8.0 => 72
  2.8.1 => 73
  2.8.2 => 74
  2.8.3 => 75
  2.9.0 => 76
  3.0.0 => 80
  3.0.1 => 81
  3.1.0 => 82
  3.1.1 => 83


---------------------------------------------------------------------------------
*/

// Configs specific to the plugin
// Build Number (must be a integer)
define('HEY_BASE_URL', get_option('siteurl').'/wp-content/plugins/'.HEY_DIR.'/');
define("HEYPUB_PLUGIN_BUILD_DATE", "2020-08-30");
// Version Number (can be text)
define("HEYPUB_PLUGIN_BUILD_NUMBER", "83");  // This controls whether or not we get upgrade prompt
define("HEYPUB_PLUGIN_VERSION", "3.1.1");
define("HEYPUB_PLUGIN_TESTED", "5.5.0");

# Base domain
$domain = 'https://www.heypublisher.com';
$debug = (getenv('HEYPUB_DEBUG') === 'true');
if ($debug) {
  $domain = 'http://127.0.0.1:3000';
}
define('HEYPUB_PLUGIN_ERROR_CONTACT','support@heypublisher.com');

// TODO: This has been deprecated - but needs to be traced through code to remove references
define('HEYPUB_DONATE_URL','https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6XSRBYF4B3RH6');

define('HEYPUB_PLUGIN_FULLPATH', dirname(__FILE__));
// How to connect to the service
define('HEYPUB_FEEDBACK_EMAIL_VALUE','support@heypublisher.com?subject=HeyPublisher%20Wordpress%20Plugin');
define('HEYPUB_FEEDBACK_GETSATISFACTION','mailto:support@heypublisher.com?Subject=HeyPublisher%20Submission%20Manager');
// define('HEYPUB_SVC_URL_STYLE_GUIDE','https://blog.heypublisher.com/docs/plugins/wordpress/style_guide/');     # designates the URL of the style guide
define('HEYPUB_DOMAIN', $domain);
# Stylesheet for plugin resides on HP server now
define('HEYPUB_SVC_STYLESHEET_URL',HEYPUB_DOMAIN . '/stylesheets/wordpress/plugin.css?' . HEYPUB_PLUGIN_VERSION);

// TODO: remove these defines and reference directly from XML code that makes query
define('HEYPUB_SVC_URL_AUTHENTICATE','publishers/fetch_or_create');           # initial plugin authentication
define('HEYPUB_SVC_URL_GET_PUBLISHER','publishers/show');                     # update the options
define('HEYPUB_SVC_URL_UPDATE_PUBLISHER','publishers/update_publisher');      # update the options
define('HEYPUB_SVC_URL_GET_GENRES','publishers/fetch_categories');            # fetch categories publisher accepts
define('HEYPUB_SVC_URL_GET_PUB_TYPES','publishers/fetch_publisher_types');    # fetch publisher types
define('HEYPUB_SVC_URL_GET_SUBMISSIONS','submissions/fetch_pending_submissions');           # fetch all pending submissions
define('HEYPUB_SVC_URL_RESPOND_TO_SUBMISSION','submissions/submission_action');             # accept/reject/publish action
define('HEYPUB_SVC_READ_SUBMISSION','submissions/show');                      # fetch a single submission for reading.  also sets the 'read' status

# if this changes, plugin will not work.  You have been warned
// TODO: tie token value to version / host
define('HEYPUB_SVC_TOKEN_VALUE','534ba1c699ca9310d7acf4832e12bed87c4d5917c5063c58382e9766bca11800');

// Locally stored in database
// install key contains info about the install iteself
// config key contains cached responses from server for faster page loads
define('HEYPUB_PLUGIN_OPT_INSTALL', '_heypub_plugin_opt_install');
define('HEYPUB_PLUGIN_OPT_CONFIG', '_heypub_plugin_options');

define('HEYPUB_SVC_URL','_heypub_service_url');

// messages for sending to the User
// define('HEYPUB_OPT_MSG_REJECT','_heypub_opt_msg_reject');     # Text of rejection notice

// Info about the Page that will be created to house submissions, if needed.
define('HEYPUB_SUBMISSION_PAGE_TITLE','Submission Form');
define('HEYPUB_SUBMISSION_PAGE_REPLACER','[__HEYPUBLISHER_SUBMISSION_FORM_GOES_HERE__]');

// These keys are stored in the usermeta table and are NOT 'deleted' when we uninstall the plugin
// This way we can always track which users/posts have been affected by HP plugin
define('HEYPUB_USER_META_KEY_AUTHOR_ID','_heypub_user_meta_key_author_id');
define('HEYPUB_USER_META_KEY_AUTHOR_OID','_heypub_user_meta_key_author_oid');
define('HEYPUB_POST_META_KEY_SUB_ID','_heypub_post_meta_key_sub_id');

/**
* Load all of the plugin files
*/
global $hp_xml, $hp_base, $hp_config;
global $hp_updater;

if (!class_exists("\HeyPublisher\Base\Updater")) {
  require_once(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Base/Updater.class.php');
}
// initialize the updater and test for update
$hp_updater = new \HeyPublisher\Base\Updater( __FILE__ );
$hp_updater->set_repository( 'heypublisher-submission-manager' ); // set repo
$hp_updater->initialize(HEYPUB_PLUGIN_TESTED); // initialize the updater

// This class fetches data from and stores data in WP db
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Config.class.php');
// XML api v1
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/HeyPublisherXML/HeyPublisherXML.class.php');
// dunno ?!
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/HeyPublisher/HeyPublisher.class.php');
$hp_config = new \HeyPublisher\Config;
$hp_xml = new HeyPublisherXML;
$hp_base = new HeyPublisher;
$hp_xml->debug = $debug;
$hp_xml->log("Loading plugin::");
$hp_xml->log(sprintf("\n HEYPUB_PLUGIN_FULLPATH = %s",HEYPUB_PLUGIN_FULLPATH));
// These files are required for basic functions
require_once(HEYPUB_PLUGIN_FULLPATH.'/include/heypub-template-functions.php');

// Load the classes
// Main page
// TODO: Convert this over to the normal way of doing things
require_once(HEYPUB_PLUGIN_FULLPATH . '/admin/heypub-main.php');
// load_template(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Page/Overview.class.php');
// $hp_main = new \HeyPublisher\Page\Overview;

// Plugin configuration options
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Page/Options.class.php');
$hp_opt = new \HeyPublisher\Page\Options;

// Submissions
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Page/Submissions.class.php');
$hp_subs = new \HeyPublisher\Page\Submissions;

// Email Templates
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Page/Email.class.php');
$hp_email = new \HeyPublisher\Page\Email;

// Initiate the callbacks
register_activation_hook (__FILE__, 'heypub_init');
register_deactivation_hook( __FILE__, 'heypub_uninit');
// Register the adminstration menu
add_action('admin_init', 'RegisterHeyPublisherAdminStyle');
add_action('admin_menu', 'RegisterHeyPublisherAdminMenu');

// IMPORTANT: If you have custom posts and want to have HeyPublisher send a notice
// to the writer when the submission is either published, or rejected after acceptance,
// you will need to modify the 3 add_action() statements below.
// Simply change '_post' to '_your_custom_post type'
// For example, if your custom post type is called 'story',
// Change
//          add_action('publish_post',array($hp_subs,'publish_post'));
// to read:
//          add_action('publish_story',array($hp_subs,'publish_post'));

// Marks the submission as 'published' in HeyPublisher and removes it from your Submission Summary screen:
add_action('publish_post',array($hp_subs,'publish_post'));
// Marks a previously accepted submission as 'rejected' in HeyPublisher and removes it from your Submission Summary screen:
// this one executes when the submission is moved to 'trash'
add_action('trash_post',array($hp_subs,'delete_post_cleanup'));
// this one executes when you skip the trash an simply 'delete' the submission
add_action('delete_post',array($hp_subs,'delete_post_cleanup'));

// Ensure the post edit screen shows pre-acceptance actions in the 'revisions' metabox
add_action( 'do_meta_boxes', array($hp_subs,'revisions_meta_box'));

// Ensure we go through the upgrade path even if the user simply installs
// a new version of the plugin over top of the old plugin.
if ($hp_config->install['version_current'] != HEYPUB_PLUGIN_BUILD_NUMBER) {
  heypub_init();
}

// register the admin styles
function RegisterHeyPublisherAdminStyle() {
  wp_register_style( 'heypublisher', plugins_url( 'include/css/heypublisher.css', __FILE__ ), array(), HEYPUB_PLUGIN_VERSION );
}

/**
*  Configure and Register the Admin Menu
*  Invoke the hook, sending function name
*/
function RegisterHeyPublisherAdminMenu(){
  global $hp_xml, $hp_opt, $hp_subs, $hp_main, $hp_email, $hp_config;
  // Initilise the plugin for the first time here. This gets called when you click the HeyPublisher link.
  $admin_menu = add_menu_page('HeyPublisher Stats','HeyPublisher', 8, HEY_DIR, array($hp_main,'page_prep'), 'dashicons-book-alt');
  add_action("admin_print_styles-$admin_menu", 'HeyPublisherAdminHeader' );

  // Configure Options
  $admin_opts = add_submenu_page( HEY_DIR , 'Configure HeyPublisher', 'Plugin Options', 'manage_options', $hp_opt->slug, array($hp_opt,'options'));
  // add_action("load-$admin_opts", array($hp_opt,'help_menu') );
  add_action("admin_print_styles-$admin_opts", 'HeyPublisherAdminHeader' );
  add_action("admin_print_scripts-$admin_opts", 'HeyPublisherAdminInit');

  if ($hp_config->is_validated) {
    // Submission Queue
    $admin_sub = add_submenu_page(HEY_DIR , 'HeyPublisher Submissions', 'Submissions', 'edit_others_posts', $hp_subs->slug, array($hp_subs,'action_handler'));
    add_action("admin_print_styles-$admin_sub", 'HeyPublisherAdminHeader' );
    add_action("admin_print_scripts-$admin_sub", 'HeyPublisherAdminInit');

    // Response Templates
    $admin_temps = add_submenu_page( HEY_DIR , 'HeyPublisher Email Templates', 'Email Templates', 'manage_options', $hp_email->slug, array($hp_email,'action_handler'));
    add_action("load-$admin_temps", array($hp_email,'help_menu') );
    add_action("admin_print_styles-$admin_temps", 'HeyPublisherAdminHeader' );
    add_action("admin_print_scripts-$admin_temps", 'HeyPublisherAdminInit');
  }
  // Uninstall Plugin - now moved to Overview page :)
}

// Load the custom .css that's already been registered
function HeyPublisherAdminHeader() {
  wp_enqueue_style( 'heypublisher' );
}
// Load the custom .js
function HeyPublisherAdminInit() {
	$parts = array(WP_PLUGIN_URL,HEY_DIR,'include','js','heypublisher.js');
	$url = implode('/',$parts);
  wp_enqueue_script('heypublisher', $url, array('jquery'), HEYPUB_PLUGIN_VERSION );
  // wp_enqueue_style('heypub_font_css', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', array(), HEYPUB_PLUGIN_VERSION);
}

// /*
// Handler for display the Response Templates admin page
// */
// function heypub_response_templates() {
// require_once(HEYPUB_PLUGIN_FULLPATH.'/include/HeyPublisher/HeyPublisherResponse.class.php');
//   $hp_res = new HeyPublisherResponse;
//   $hp_res->handler();
// }

// -----------------------------------------------------------------------------
// Initialize / Upgrade
// -----------------------------------------------------------------------------
function heypub_init(){
  global $hp_xml, $hp_config;
  $hp_xml->log("heypub_init()");

  // NEW Install Path
  if (get_option(HEYPUB_PLUGIN_OPT_INSTALL) == false) {
    $hp_config->initialize();
    if (function_exists('get_bloginfo')) {
      $hp_config->set_config_option('name',get_bloginfo('name'));
      // TODO: these need to change
      $hp_config->set_config_option('url',get_bloginfo('url'));
      $hp_config->set_config_option('editor_email',get_bloginfo('admin_email'));
      if (function_exists('get_feed_permastruct')) {
        $hp_config->set_config_option('rss',get_bloginfo('rss2_url'));
      }
    }
    $hp_config->set_config_option('editor_name','Editor');
  }

  // now check for a normal upgrade path
  $opts = $hp_config->install;
  if ($opts['version_current'] != HEYPUB_PLUGIN_BUILD_NUMBER) {
    $hp_config->logger->debug("\tupgrading plugin");
    // this is the 'normal' upgrade path.
    if ($opts['version_current'] < 40) {  // upgrade to 1.3.0 options
      $hp_config->logger->debug("\tupgrading to 40");
      $hp_config->set_config_option('notify_submitted','1');
      $hp_config->set_config_option('notify_read','1');
      $hp_config->set_config_option('notify_rejected','1');
      $hp_config->set_config_option('notify_published','1');
      $hp_config->set_config_option('notify_accepted','1');
      $hp_config->set_config_option('notify_under_consideration','1');
      $hp_config->set_config_option('reprint_subs','0');
      if (function_exists('get_bloginfo')) {
        // for feed info, also need to test for 'get_feed_permastruct()')
        if (function_exists('get_feed_permastruct')) {
          $hp_config->set_config_option('rss',get_bloginfo('rss2_url'));
        }
      }
    }
    // not all of our 1.3.1 version users have the bloginfo for rss set, upgrade them
    if ($opts['version_current'] < 42) {  // upgrade to 1.3.2 options
      $hp_config->logger->debug("\tupgrading to 42");
      if (function_exists('get_bloginfo') && function_exists('get_feed_permastruct')) {
          $hp_config->set_config_option('rss',get_bloginfo('rss2_url'));
      }
    }
    // ensure 2.3.0 versons have base config for mailchimp, even if not used
    if ($opts['version_current'] < 64) {  // upgrade to 2.3.0 options
      $hp_config->logger->debug("\tupgrading to 64");
      $hp_config->set_config_option('mailchimp_active', false);
      $hp_config->set_config_option('mailchimp_api_key', null);
      $hp_config->set_config_option('mailchimp_list_id', null);
    }
    if ($opts['version_current'] < 65) {  // upgrade to 2.4.0 options
      $hp_config->logger->debug("\tupgrading to 65");
      $hp_config->set_config_option('notify_withdrawn','1');
    }
    // Upgraded to the 3.0.0 vesion of options
    if ($opts['version_current'] < 80) {
      $hp_config->logger->debug("\tupgrading to 80");
      // need to migrate keys
      $address = array(
        'street'  => $hp_config->get_config_option('address'),
        'city'    => $hp_config->get_config_option('city'),
        'state'   => $hp_config->get_config_option('state'),
        'country'   => $hp_config->get_config_option('country'),
        'zipcode'   => $hp_config->get_config_option('zipcode')
      );
      $hp_config->set_config_option('address',$address);
      // we no longer need these keys set in config
      $hp_config->kill_config_option('city');
      $hp_config->kill_config_option('state');
      $hp_config->kill_config_option('zipcode');
      $hp_config->kill_config_option('country');
      $notifications = array(
        'read'        => $hp_config->get_config_option('notify_read'),
        'considered'  => $hp_config->get_config_option('notify_under_consideration'),
        'accepted'    => $hp_config->get_config_option('notify_accepted'),
        'rejected'    => $hp_config->get_config_option('notify_rejected'),
        'published'   => $hp_config->get_config_option('notify_published'),
        'withdrawn'   => $hp_config->get_config_option('notify_withdrawn'),
      );
      $hp_config->set_config_option('notifications',$notifications);
      // we no longer need these keys set in config
      $hp_config->kill_config_option('notify_submitted');
      $hp_config->kill_config_option('notify_read');
      $hp_config->kill_config_option('notify_rejected');
      $hp_config->kill_config_option('notify_accepted');
      $hp_config->kill_config_option('notify_published');
      $hp_config->kill_config_option('notify_under_consideration');
      $hp_config->kill_config_option('notify_withdrawn');

      // TODO: Migrate the 'accepts'
      $accepts = array(
        'reprints'      => $hp_config->get_config_option('reprint_subs'),
        'simultaneous'  => $hp_config->get_config_option('simu_subs'),
        'email'         => false,  # we didn't have a default for this
        'multiple'      => $hp_config->get_config_option('multi_subs'),
        'multibyte'     => $hp_config->get_config_option('turn_off_tidy')
      );
      $hp_config->set_config_option('accepts',$accepts);
      $hp_config->kill_config_option('reprint_subs');
      $hp_config->kill_config_option('simu_subs');
      $hp_config->kill_config_option('multi_subs');
      $hp_config->kill_config_option('turn_off_tidy');

      //  Need to migrate from $saved_genres = $this->xml->get_category_mapping(); to remote mapping
      // $hp_config->set_config_option('category_map',$hp_config->get_config_option('categories'));
      $map = $hp_xml->get_category_mapping();
      $hp_xml->log(sprintf("heypub_init() \$map = \n\t%s",print_r($map,1)));

    }


    // finally - ensure that the last version and current version are set
    $data = array(
      'version_last'      => $opts['version_current'],
      'version_last_date' => $opts['version_current_date'],
      'version_current'   => HEYPUB_PLUGIN_BUILD_NUMBER,
      'version_current_date' => date('Y-m-d')
    );
    $hp_config->set_install_options($data);
  }
  // otherwise we keep chugging along
}
// TODO: This needs to be fixed for new API support
function heypub_uninit() {
  global $hp_xml, $hp_config, $hp_opt;
  $opts = $hp_config->config;
  $install = $hp_config->install;
  if ($install != FALSE && isset($install['user_oid']) ) {
    // disable the publisher in the db
    $opts['accepting_subs'] = false;
    $opts['genres_list'] = false;
    $opts['guide'] = get_permalink($opts['sub_guide_id']);
    // TODO: This should change to api->update_publisher();
    $hp_opt->pubapi->deactivate();
  }
  $hp_config->install = false;
  $hp_config->config = false;
  delete_option(HEYPUB_PLUGIN_OPT_INSTALL);
  delete_option(HEYPUB_PLUGIN_OPT_CONFIG);
  return;
}



?>
