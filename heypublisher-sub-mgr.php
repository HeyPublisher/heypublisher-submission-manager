<?php
/*
Plugin Name: HeyPublisher Submission Manager
Plugin URI: https://www.heypublisher.com
Description: HeyPublisher is a better way of managing unsolicited submissions directly within WordPress.
Author: HeyPublisher
Author URI: https://www.heypublisher.com
Version: 2.8.2

  Copyright 2010-2014 Loudlever, Inc. (wordpress@loudlever.com)
  Copyright 2014-2018 Richard Luck (https://github.com/aguywithanidea/)

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

---------------------------------------------------------------------------------
*/

// Configs specific to the plugin
// Build Number (must be a integer)
define('HEY_BASE_URL', get_option('siteurl').'/wp-content/plugins/'.HEY_DIR.'/');
define("HEYPUB_PLUGIN_BUILD_DATE", "2018-05-09");
// Version Number (can be text)
define("HEYPUB_PLUGIN_BUILD_NUMBER", "74");  // This controls whether or not we get upgrade prompt
define("HEYPUB_PLUGIN_VERSION", "2.8.2");

# Base domain
$domain = 'https://www.heypublisher.com';
$debug = (getenv('HEYPUB_DEBUG') === 'true');
if ($debug) {
  $domain = 'http://127.0.0.1:3000';
}
define('HEYPUB_PLUGIN_ERROR_CONTACT','support@heypublisher.com');

define('HEYPUB_DONATE_URL','https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6XSRBYF4B3RH6');

define('HEYPUB_PLUGIN_FULLPATH', dirname(__FILE__));
// How to connect to the service
define('HEYPUB_FEEDBACK_EMAIL_VALUE','support@heypublisher.com?subject=HeyPublisher%20Wordpress%20Plugin');
// define('HEYPUB_FEEDBACK_GETSATISFACTION','http://getsatisfaction.com/hey');
define('HEYPUB_FEEDBACK_GETSATISFACTION','mailto:support@heypublisher.com?Subject=HeyPublisher%20Submission%20Manager');
// define('HEYPUB_SVC_URL_STYLE_GUIDE','http://blog.heypublisher.com/docs/plugins/wordpress/style_guide/');     # designates the URL of the style guide
# designates the base URL and version of API
define('HEYPUB_SVC_URL_BASE', $domain . '/api/v1');
define('HEYPUB_API', $domain . '/api/v2');
# Stylesheet for plugin resides on HP server now
define('HEYPUB_SVC_STYLESHEET_URL',$domain . '/stylesheets/wordpress/plugin.css?' . HEYPUB_PLUGIN_VERSION);

// TODO: remove these defines and reference directly from XML code that makes query
define('HEYPUB_SVC_URL_SUBMIT_FORM','submissions');
define('HEYPUB_SVC_URL_AUTHENTICATE','publishers/fetch_or_create');           # initial plugin authentication
define('HEYPUB_SVC_URL_GET_PUBLISHER','publishers/show');                     # update the options
define('HEYPUB_SVC_URL_UPDATE_PUBLISHER','publishers/update_publisher');      # update the options
define('HEYPUB_SVC_URL_GET_GENRES','publishers/fetch_categories');            # fetch categories publisher accepts
define('HEYPUB_SVC_URL_GET_PUB_TYPES','publishers/fetch_publisher_types');    # fetch publisher types
define('HEYPUB_SVC_URL_GET_SUBMISSIONS','submissions/fetch_pending_submissions');           # fetch all pending submissions
define('HEYPUB_SVC_URL_RESPOND_TO_SUBMISSION','submissions/submission_action');             # accept/reject/publish action
define('HEYPUB_SVC_READ_SUBMISSION','submissions/show');                      # fetch a single submission for reading.  also sets the 'read' status

# if this changes, plugin will not work.  You have been warned
define('HEYPUB_SVC_TOKEN_VALUE','534ba1c699ca9310d7acf4832e12bed87c4d5917c5063c58382e9766bca11800');

// Locally stored option keys
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
global $hp_xml;
global $hp_base;
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/HeyPublisherXML/HeyPublisherXML.class.php');
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/HeyPublisher/HeyPublisher.class.php');
$hp_xml = new HeyPublisherXML;
$hp_base = new HeyPublisher;
$hp_xml->debug = $debug;
$hp_xml->log("Loading plugin::");

// These files are required for basic functions
require_once(HEYPUB_PLUGIN_FULLPATH.'/include/heypub-template-functions.php');

// Load the classes
// Main page
require_once(HEYPUB_PLUGIN_FULLPATH . '/admin/heypub-main.php');
// load_template(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Page/Overview.class.php');
// $hp_main = new \HeyPublisher\Page\Overview;

// Plugin configuration options
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Page/Options.class.php');
$hp_opt = new \HeyPublisher\Page\Options;
$hp_opt->domain = $domain;

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
if ($hp_xml->install['version_current'] != HEYPUB_PLUGIN_BUILD_NUMBER) {
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
  global $hp_xml, $hp_opt, $hp_subs, $hp_main, $hp_email;
  // Initilise the plugin for the first time here. This gets called when you click the HeyPublisher link.
  $admin_menu = add_menu_page('HeyPublisher Stats','HeyPublisher', 8, HEY_DIR, array($hp_main,'page_prep'), 'dashicons-book-alt');
  add_action("admin_print_styles-$admin_menu", 'HeyPublisherAdminHeader' );

  // Configure Options
  $admin_opts = add_submenu_page( HEY_DIR , 'Configure HeyPublisher', 'Plugin Options', 'manage_options', $hp_opt->slug, array($hp_opt,'options'));
  // add_action("load-$admin_opts", array($hp_opt,'help_menu') );
  add_action("admin_print_styles-$admin_opts", 'HeyPublisherAdminHeader' );
  add_action("admin_print_scripts-$admin_opts", 'HeyPublisherAdminInit');

  if ($hp_xml->is_validated) {
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

/*
-------------------------------------------------------------------------------
Initialize / Upgrade
-------------------------------------------------------------------------------
*/
function heypub_init(){
  global $hp_xml;

  // we're referencing the old key names here directly - as we no longer need the defines in the system
  // and this is simply for backwards upgrade compatibility
  if (get_option('_heypub_opt_plugin_version_current') != false) {
    // this user is upgrading from build version <= 28
    $hp_xml->initialize_plugin();
    // need to transfer over all of their install configs to the new hash and clean the db:
    // plugin version and install date
    $hp_xml->set_install_option('version_current',get_option('_heypub_opt_plugin_version_current'));
    delete_option('_heypub_opt_plugin_version_current');
    $hp_xml->set_install_option('version_current_date',get_option('_heypub_opt_plugin_version_date'));
    delete_option('_heypub_opt_plugin_version_date');
    delete_option('_heypub_opt_plugin_version_last');

    // plugin / publisher oid info
    $hp_xml->set_install_option('is_validated',get_option('_heypub_opt_svc_isvalidated'));
    delete_option('_heypub_opt_svc_isvalidated');
    $hp_xml->set_install_option('user_oid',get_option('_heypub_opt_svc_user_oid'));
    delete_option('_heypub_opt_svc_user_oid');
    $hp_xml->set_install_option('publisher_oid',get_option('_heypub_opt_svc_publisher_oid'));
    delete_option('_heypub_opt_svc_publisher_oid');
    // This key was never used in the code and can simply be cleaned up
    delete_option('_heypub_opt_svc_publisher');


    //  NEED to migrate these CONFIG options
    $hp_xml->set_config_option('name',get_option('_heypub_opt_publication_name'));
    delete_option('_heypub_opt_publication_name');
    $hp_xml->set_config_option('url',get_option('_heypub_opt_publication_url'));
    delete_option('_heypub_opt_publication_url');
    $hp_xml->set_config_option('editor_name',get_option('_heypub_opt_editor_name'));
    delete_option('_heypub_opt_editor_name');
    $hp_xml->set_config_option('editor_email',get_option('_heypub_opt_editor_email'));
    delete_option('_heypub_opt_editor_email');
    $hp_xml->set_config_option('accepting_subs',get_option('_heypub_opt_accepting_subs'));
    delete_option('_heypub_opt_accepting_subs');
    $hp_xml->set_config_option('reading_period',get_option('_heypub_opt_reading_period'));
    delete_option('_heypub_opt_reading_period');
    $hp_xml->set_config_option('simu_subs',get_option('_heypub_opt_simultaneous_sumbmissions'));
    delete_option('_heypub_opt_simultaneous_sumbmissions');
    $hp_xml->set_config_option('multi_subs',get_option('_heypub_opt_multiple_sumbmissions'));
    delete_option('_heypub_opt_multiple_sumbmissions');
    $hp_xml->set_config_option('paying_market',get_option('_heypub_opt_paying_market'));
    delete_option('_heypub_opt_paying_market');
    $hp_xml->set_config_option('address',get_option('_heypub_opt_publication_address'));
    delete_option('_heypub_opt_publication_address');
    $hp_xml->set_config_option('city',get_option('_heypub_opt_publication_city'));
    delete_option('_heypub_opt_publication_city');
    $hp_xml->set_config_option('state',get_option('_heypub_opt_publication_state'));
    delete_option('_heypub_opt_publication_state');
    $hp_xml->set_config_option('zipcode',get_option('_heypub_opt_publication_zip'));
    delete_option('_heypub_opt_publication_zip');
    $hp_xml->set_config_option('country',get_option('_heypub_opt_publication_country'));
    delete_option('_heypub_opt_publication_country');
    $hp_xml->set_config_option('sub_page_id',get_option('_heypub_opt_submission_page_id'));
    delete_option('_heypub_opt_submission_page_id');
    $hp_xml->set_config_option('sub_guide_id',get_option('_heypub_opt_submission_guide_id'));
    delete_option('_heypub_opt_submission_guide_id');
    delete_option('_heypub_opt_sub_guide_url');
    $hp_xml->set_is_validated();
  }
  elseif (get_option(HEYPUB_PLUGIN_OPT_INSTALL) == false) {
    // NEW Install Path
    $hp_xml->initialize_plugin();
    if (function_exists('get_bloginfo')) {
      $hp_xml->set_config_option('name',get_bloginfo('name'));
      $hp_xml->set_config_option('url',get_bloginfo('url'));
      $hp_xml->set_config_option('editor_email',get_bloginfo('admin_email'));
      if (function_exists('get_feed_permastruct')) {
        $hp_xml->set_config_option('rss',get_bloginfo('rss2_url'));
      }
    }
    $hp_xml->set_install_option('version_current_date',null);
    $hp_xml->set_config_option('editor_name','Editor');
  }

  // now check for a normal upgrade path
  $opts = $hp_xml->install;
  if ($opts['version_current'] != HEYPUB_PLUGIN_BUILD_NUMBER) {
    // this is the 'normal' upgrade path.
    if ($opts['version_current'] < 40) {  // upgrade to 1.3.0 options
      $hp_xml->set_config_option('notify_submitted','1');
      $hp_xml->set_config_option('notify_read','1');
      $hp_xml->set_config_option('notify_rejected','1');
      $hp_xml->set_config_option('notify_published','1');
      $hp_xml->set_config_option('notify_accepted','1');
      $hp_xml->set_config_option('notify_under_consideration','1');
      $hp_xml->set_config_option('reprint_subs','0');
      if (function_exists('get_bloginfo')) {
        // for feed info, also need to test for 'get_feed_permastruct()')
        if (function_exists('get_feed_permastruct')) {
          $hp_xml->set_config_option('rss',get_bloginfo('rss2_url'));
        }
      }
    }
    // not all of our 1.3.1 version users have the bloginfo for rss set, upgrade them
    if ($opts['version_current'] < 42) {  // upgrade to 1.3.2 options
      if (function_exists('get_bloginfo') && function_exists('get_feed_permastruct')) {
          $hp_xml->set_config_option('rss',get_bloginfo('rss2_url'));
      }
    }
    // ensure 2.3.0 versons have base config for mailchimp, even if not used
    if ($opts['version_current'] < 64) {  // upgrade to 2.3.0 options
      $hp_xml->set_config_option('mailchimp_active', false);
      $hp_xml->set_config_option('mailchimp_api_key', null);
      $hp_xml->set_config_option('mailchimp_list_id', null);
    }
    if ($opts['version_current'] < 65) {  // upgrade to 2.4.0 options
      $hp_xml->set_config_option('notify_withdrawn','1');
    }

    // For future reference, just keep adding new hash keys that are version specific by following same logic
    // if ($opts['version_current'] < 50) {  // upgrade to 1.4.x options
    //    ... do something here
    // }

    // finally - ensure that the last version and current version are set
    $hp_xml->set_install_option('version_last',$opts['version_current']);
    $hp_xml->set_install_option('version_last_date',$opts['version_current_date']);
    $hp_xml->set_install_option('version_current',HEYPUB_PLUGIN_BUILD_NUMBER);
    $hp_xml->set_install_option('version_current_date',date('Y-m-d'));
  }
  // otherwise we keep chugging along
}

function heypub_uninit() {
  global $hp_xml;
  $opts = $hp_xml->config;
  $install = $hp_xml->install;
  if ($install != FALSE && isset($install['user_oid']) ) {
    // disable the publisher in the db
    $opts['accepting_subs'] = false;
    $opts['genres_list'] = false;
    $opts['guide'] = get_permalink($opts['sub_guide_id']);
    $hp_xml->update_publisher($opts,true);
  }
  $hp_xml->install = false;
  $hp_xml->config = false;
  delete_option(HEYPUB_PLUGIN_OPT_INSTALL);
  delete_option(HEYPUB_PLUGIN_OPT_CONFIG);
  return;
}



?>
