<?php
/*
Plugin Name: Kindle Periodical Manager
Plugin URI: http://www.loudlever.com/wordpress-plugins/kindle-periodical-manager/
Description: This plugin creates the XML feed of 'Posts' that Amazon requires for publication on <a href='https://kindlepublishing.amazon.com/gp/vendor/kindlepubs/kpp/kpp-home' target='_blank'>Kindle for Periodicals</a>.
Version: 0.1.2
Author: Loudlever, Inc.
Author URI: http://www.loudlever.com

  $Id$

  Copyright 2010-2011 Loudlever, Inc. (wordpress@loudlever.com)

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

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

// Load the KindleFeed class and associated scoped functionality
load_template(dirname(__FILE__) . '/includes/KindleFeed.class.php');
$kf = new KindleFeed();



// enable our link to the settings
add_filter('plugin_action_links', array(&$kf,'plugin_links'), 10, 2 );
add_action('init', 'kindle_feed_rules');
// Enable the Admin Menu and Contextual Help
add_action('admin_menu', 'kindle_admin_settings');
add_filter('contextual_help', array(&$kf,'configuration_screen_help'), 10, 3);

function kindle_feed_rules() {
	global $kf;
	add_feed('kindle_manifest', array(&$kf,'format_manifest'));
	add_feed('kindle_section', array(&$kf,'format_section'));
	add_feed('kindle_article', array(&$kf,'format_article'));
	add_feed('kindle_cover', array(&$kf,'format_cover'));
	add_feed('kindle_cover_details', array(&$kf,'format_cover_details'));
	add_feed('kindle_masthead', array(&$kf,'format_masthead'));
}
function kindle_admin_settings() {
  global $kf;
 	//create Options Management Screen
	if (function_exists('add_options_page')) {
		$kf->help = add_options_page('Kindle Feed Settings','Kindle Feed', 'administrator', $kf->slug, 'kindle_settings_page');
  }
  if (function_exists('add_action')) {
  	add_action( 'admin_init', array(&$kf,'register_options') );
	}
}

// This callback does not handle class functions, thus we wrap it....
function kindle_settings_page() {
  global $kf;
  $kf->configuration_screen();
}

register_deactivation_hook( __FILE__, array(&$kf,'deactivate_plugin'));


// outputs SQL queries to a log
// add_action('shutdown', 'sql_logger');
function sql_logger() {
    global $wpdb;
    $log_file = fopen(ABSPATH.'/sql_log.txt', 'a');
    fwrite($log_file, "//////////////////////////////////////////\n\n" . date("F j, Y, g:i:s a")."\n");
    foreach($wpdb->queries as $q) {
        fwrite($log_file, $q[0] . " - ($q[1] s)" . "\n\n");
    }
    fclose($log_file);
}



?>
