<?php
/*
Plugin Name: HeyPublisher Submission Manager
Plugin URI: http://loudlever.com
Description: This plugin allows you as a publisher or blog owner to accept unsolicited submissions from writers without having to create an account for them.  You can define reading periods, acceptable genres, and other filters to ensure you only receive the submissions that meet your publication's needs.
Version: 1.3.0
Author: Loudlever, Inc.
Author URI: http://www.loudlever.com


  $Id: heypublisher-sub-mgr.php 145 2010-12-16 22:20:13Z rluck $

  Copyright 2010 Loudlever, Inc. (wordpress@loudlever.com)

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
---------------------------------------------------------------------------------
*/  

// Configs specific to the plugin
// Build Number (must be a integer)
define('HEY_BASE_URL', get_option('siteurl').'/wp-content/plugins/'.HEY_DIR.'/');
define("HEYPUB_PLUGIN_BUILD_NUMBER", "40");  // This controls whether or not we get upgrade prompt
define("HEYPUB_PLUGIN_BUILD_DATE", "2010-11-01");  
// Version Number (can be text)
define("HEYPUB_PLUGIN_VERSION", "1.3.0");

# Base domain 
define('HEYPUB_DOMAIN','http://heypublisher.com');    
# Base domain for testing
// define('HEYPUB_DOMAIN','http://localhost:3000');

define('HEYPUB_PLUGIN_ERROR_CONTACT','Please contact <a href="mailto:wordpress@loudlever.com?subject=plugin%20error">wordpress@loudlever.com</a> to report this error');

define('HEYPUB_DONATE_URL','https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6XSRBYF4B3RH6');

// which method handles the not-authenticated condition?
define('HEYPUB_PLUGIN_NOT_AUTHENTICATED_ACTION','heypub_show_menu_options');

define('HEYPUB_PLUGIN_FULLPATH', WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.HEY_DIR.DIRECTORY_SEPARATOR);

// How to connect to the service
define('HEYPUB_FEEDBACK_EMAIL_VALUE','wordpress@loudlever.com?subject=HeyPublisher%20Wordpress%20Plugin');
define('HEYPUB_FEEDBACK_GETSATISFACTION','http://getsatisfaction.com/hey');
define('HEYPUB_SVC_URL_STYLE_GUIDE','http://www.loudlever.com/docs/plugins/wordpress/style_guide');     # designates the URL of the style guide
define('HEYPUB_SVC_URL_BASE', HEYPUB_DOMAIN . '/api/v1');                 # designates the base URL and version of API
# Stylesheet for plugin resides on HP server now
define('HEYPUB_SVC_STYLESHEET_URL',HEYPUB_DOMAIN . '/stylesheets/wordpress/plugin.css?R11.1');

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
define('HEYPUB_POST_META_KEY_SUB_ID','_heypub_post_meta_key_sub_id');

/**
* Load all of the plugin files
*/
require_once(HEYPUB_PLUGIN_FULLPATH.'include'.DIRECTORY_SEPARATOR.'HeyPublisherXML'.DIRECTORY_SEPARATOR.'HeyPublisherXML.class.php');
require_once(HEYPUB_PLUGIN_FULLPATH.'include'.DIRECTORY_SEPARATOR.'HeyPublisher'.DIRECTORY_SEPARATOR.'HeyPublisher.class.php');

global $hp_xml;
global $hp_base;
global $hp_sub;

$hp_xml = new HeyPublisherXML;
$hp_base = new HeyPublisher;

// These files are required for basic functions
require_once(HEYPUB_PLUGIN_FULLPATH.'include'.DIRECTORY_SEPARATOR.'heypub-template-functions.php');

// Only need this pages if you're modifying the plugin
require_once(HEYPUB_PLUGIN_FULLPATH.'admin'.DIRECTORY_SEPARATOR.'heypub-main.php');
require_once(HEYPUB_PLUGIN_FULLPATH.'admin'.DIRECTORY_SEPARATOR.'heypub-options.php');
require_once(HEYPUB_PLUGIN_FULLPATH.'admin'.DIRECTORY_SEPARATOR.'heypub-uninstall.php');

// required for managing submissions
require_once(HEYPUB_PLUGIN_FULLPATH.'admin'.DIRECTORY_SEPARATOR.'heypub-submissions.php');


// Initiate the callbacks
register_activation_hook (__FILE__, 'heypub_init');
register_deactivation_hook( __FILE__, 'heypub_uninit');
// Register the adminstration menu
add_action('admin_menu', 'RegisterHeyPublisherAdminMenu');
// Hook into the 'dashboard' to display some stats
add_action('wp_dashboard_setup', 'RegisterHeyPublisherDashboardWidget' );

// IMPORTANT: If you have custom posts and want to have HeyPublisher send a notice
// to the writer when the submission is either published, or rejected after acceptance,
// you will need to modify the 3 add_action() statements below.
// Simply change '_post' to '_your_custom_post type'
// For example, if your custom post type is called 'story', 
// Change 
//          add_action('publish_post','heypub_publish_post');
// to read:
//          add_action('publish_story','heypub_publish_post');

// Marks the submission as 'published' in HeyPublisher and removes it from your Submission Summary screen:
add_action('publish_post','heypub_publish_post');
// Marks a previously accepted submission as 'rejected' in HeyPublisher and removes it from your Submission Summary screen:
// this one executes when the submission is moved to 'trash'
add_action('trash_post','heypub_reject_post');
// this one executes when you skip the trash an simply 'delete' the submission
add_action('delete_post','heypub_reject_post');

// Ensure we go through the upgrade path even if the user simply installs 
// a new version of the plugin over top of the old plugin.
if ($hp_xml->install['version_current'] != HEYPUB_PLUGIN_BUILD_NUMBER) {
  heypub_init();
}

/**
*  Configure and Register the Admin Menu
*  Invoke the hook, sending function name
*/
function RegisterHeyPublisherAdminMenu(){
  global $hp_xml;
  // Initilise the plugin for the first time here. This gets called when you click the HeyPublisher link.
	$countHTML = '';
  //   $count = 25;
  // if($count) {
  //  $countHTML = ' <span id="awaiting-mod" class="count-1"><span class="pending-count">'.$count.'</span></span>';
  // }

    $admin_menu = add_menu_page('HeyPublisher Stats','HeyPublisher', 8, HEY_DIR, 'heypub_menu_main', HEY_BASE_URL.'images/heypub-icon.png');
    add_action("admin_print_styles-$admin_menu", 'HeyPublisherAdminHeader' );

  if ($hp_xml->is_validated) {
      // Submission Queue
      $admin_sub = add_submenu_page(HEY_DIR , 'HeyPublisher Submissions', 'Submissions'.$countHTML, 'edit_others_posts', 'heypub_show_menu_submissions', 'heypub_show_menu_submissions');
      add_action("admin_print_styles-$admin_sub", 'HeyPublisherAdminHeader' );
      add_action("admin_print_scripts-$admin_sub", 'HeyPublisherAdminInit');
  }
    // Configure Options
    $admin_opts = add_submenu_page( HEY_DIR , 'Configure HeyPublisher', 'Plugin Options', 'manage_options', 'heypub_show_menu_options', 'heypub_show_menu_options');
    add_action("admin_print_styles-$admin_opts", 'HeyPublisherAdminHeader' );
    add_action("admin_print_scripts-$admin_opts", 'HeyPublisherAdminInit');

    if ($hp_xml->is_validated) {
      // Response Templates
      $admin_temps = add_submenu_page( HEY_DIR , 'HeyPublisher Response Templates', 'Response Templates', 'manage_options', 'heypub_response_templates', 'heypub_response_templates');
      add_action("admin_print_styles-$admin_temps", 'HeyPublisherAdminHeader' );
      add_action("admin_print_scripts-$admin_temps", 'HeyPublisherAdminInit');
    }
    // Uninstall Plugin
    $admin_unin = add_submenu_page( HEY_DIR , 'Uninstall HeyPublisher', 'Uninstall Plugin', 'manage_options', 'heypub_menu_uninstall', 'heypub_menu_uninstall');
    add_action("admin_print_styles-$admin_unin", 'HeyPublisherAdminHeader' );

}

function HeyPublisherAdminHeader() {
?>
  <!-- HeyPublisher Header -->
  <link rel='stylesheet' href='<?php echo HEYPUB_SVC_STYLESHEET_URL; ?>' type='text/css' />
<?php  
}
function HeyPublisherAdminInit() {
  wp_enqueue_script('heypublisher', WP_PLUGIN_URL . '/heypublisher-submission-manager/include/js/heypublisher.js',array('prototype')); 
}

/*
Handler for display the Response Templates admin page
*/
function heypub_response_templates() {
require_once(HEYPUB_PLUGIN_FULLPATH.'include'.DIRECTORY_SEPARATOR.'HeyPublisher'.DIRECTORY_SEPARATOR.'HeyPublisherResponse.class.php');
  $hp_res = new HeyPublisherResponse;
  $hp_res->handler();
}

function RegisterHeyPublisherDashboardWidget() {
  wp_add_dashboard_widget('heypub_dash_widget', 'HeyPublisher Statistics', 'heypub_right_now');	
}
function heypub_right_now() {
 global $hp_base;
 print $hp_base->right_now_widget();
}
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
  } elseif (get_option(HEYPUB_PLUGIN_OPT_INSTALL) == false) {
    // this user has not installed the plugin yet - this is a fresh install
    $hp_xml->initialize_plugin();
    // this will be needed later
    $hp_xml->set_install_option('version_current_date',null);
    
    // $hp_xml->set_config_option('name',get_bloginfo('name'));
    // $hp_xml->set_config_option('url',get_bloginfo('url'));
    $hp_xml->set_config_option('editor_name','Editor');
    // $hp_xml->set_config_option('editor_email',get_bloginfo('admin_email'));
    $hp_xml->set_config_option('accepting_subs','0');
    $hp_xml->set_config_option('reading_period','0');
    $hp_xml->set_config_option('simu_subs','0');
    $hp_xml->set_config_option('multi_subs','0');
    $hp_xml->set_config_option('paying_market','0');
    $hp_xml->set_config_option('address',false);
    $hp_xml->set_config_option('city',false);
    $hp_xml->set_config_option('state',false);
    $hp_xml->set_config_option('zipcode',false);
    $hp_xml->set_config_option('country',false);
    $hp_xml->set_config_option('sub_page_id',false);
    $hp_xml->set_config_option('sub_guide_id',false);
    // added with 1.3.0
    $hp_xml->set_config_option('reprint_subs','0');
    // $hp_xml->set_config_option('rss',get_bloginfo('rss2_url'));
    $hp_xml->set_config_option('notify_submitted',true);
    $hp_xml->set_config_option('notify_read',true);
    $hp_xml->set_config_option('notify_rejected',true);
    $hp_xml->set_config_option('notify_published',true);
    $hp_xml->set_config_option('notify_accepted',true);
    $hp_xml->set_config_option('notify_under_consideration',true);
  } 
  
  // now check for a normal upgrade path
  $opts = $hp_xml->install;
  if ($opts['version_current'] != HEYPUB_PLUGIN_BUILD_NUMBER) {
    // this is the 'normal' upgrade path.
    if ($opts['version_current'] <= 40) {  // upgrade to 1.3.0 options
      $hp_xml->set_config_option('notify_submitted',true);
      $hp_xml->set_config_option('notify_read',true);
      $hp_xml->set_config_option('notify_rejected',true);
      $hp_xml->set_config_option('notify_published',true);
      $hp_xml->set_config_option('notify_accepted',true);
      $hp_xml->set_config_option('notify_under_consideration',true);
      $hp_xml->set_config_option('reprint_subs','0');
      // $hp_xml->set_config_option('rss',get_bloginfo('rss2_url'));
    }
    // For future reference, just keep adding new hash keys that are version specific by following same logic
    // if ($opts['version_current'] < 50) {  // upgrade to 1.4.0 options
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
    $opts[accepting_subs] = false;
    $opts[genres_list] = false;
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
