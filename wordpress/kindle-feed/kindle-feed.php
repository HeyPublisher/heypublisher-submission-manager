<?php
/*
Plugin Name: Kindle Feed Manager
Plugin URI: http://www.loudlever.com/wordpress-plugins/kindle-feed-manager/
Description: This plugin allows you to create a feed of 'Scheduled' Posts that can be sent to Amazon for publication on Kindle prior to the content going live on your website.
Version: 0.1.0
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

add_action('do_feed_kindle', array(&$kf,'format_feed'), 10, 1);
add_filter('plugin_action_links', array(&$kf,'plugin_links'), 10, 2 );

function kindle_feed_rewrite($wp_rewrite) {
  $feed_rules = array(
    'feed/(.+)' => 'index.php?feed=' . $wp_rewrite->preg_index(1),
    '(.+).xml' => 'index.php?feed='. $wp_rewrite->preg_index(1)
    );
    $wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;
    // printf("<pre>Rewrite Rules\n%s</pre>",print_r($wp_rewrite->rules,1));

  // $feed_rules = array(
  // 'feed/kindle' => 'index.php?feed=kindle',
  // '/kindle/' => 'index.php?feed=kindle',
  // '/kindle.xml' => 'index.php?feed=kindle'
  // );
  // $rules = $feed_rules + $rules;
  // return $rules;
}

// add_filter('transient_rewrite_rules','kindle_feed_rewrite');
// add_filter('rewrite_rules_array','kindle_feed_rewrite');
add_filter('generate_rewrite_rules', 'kindle_feed_rewrite');

add_action('admin_menu', 'kindle_admin_settings');
add_filter('contextual_help', array(&$kf,'configuration_screen_help'), 10, 3);

function kindle_admin_settings() {
  global $kf;
 	//create Options
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


?>
