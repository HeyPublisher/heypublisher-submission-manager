<?php
/*
Plugin Name: Kindle Feed Manager
Plugin URI: http://loudlever.com
Description: This plugin allows you to create feeds of 'scheduled' Posts to feed to Kindle for publication.
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

function kindle_custom_feed() {
  // require_once(dirname(__FILE__) . '/includes/feed-template.php');
  load_template(ABSPATH.PLUGINDIR.'/kindle-feed/includes/feed-template.php');
}
add_action('do_feed_kindle', 'kindle_custom_feed', 10, 1);
add_filter('plugin_action_links', 'kindle_settings_link', 10, 2 );

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


function kindle_admin_settings() {
 	//create Options
	if (function_exists('add_options_page')) {
		add_options_page('Kindle Feed Settings','Kindle Feed', 'administrator', 'kindle-feed', 'kindle_settings_page');
  }
  if (function_exists('add_action')) {
  	add_action( 'admin_init', 'kindle_register_options' );
	}
	if (function_exists('plugin_action_links')) {
	}
}

function kindle_settings_link($links, $file) {
  static $this_plugin;
  if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
 
  if ($file == $this_plugin){
  $settings_link = '<a href="admin.php?page=kindle-feed">'.__("Settings", "kindle-feed").'</a>';
   array_unshift($links, $settings_link);
  }
  return $links;
}

function kindle_settings_page() {
?>  
<div class="wrap">
  <h2>Tour Search Settings</h2>
  <p>Ensure the following code is pasted into each Post and Page where you want the Tour Search Form to display:
  <blockquote>
    <?php echo TS_PAGE_REPLACER; ?>
  </blockquote>
  </p>
  <form method="post" action="options.php">
    <?php settings_fields( '_kindle_feed_settings' ); ?>
    <table class="form-table">
      <tr valign="top">
        <th scope="row">Arrival Date Starts How Many Days From Today?</th>
        <td>
          <input type="text" name="days_from_today" value="<?php echo get_option('next_number'); ?>" />
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Number of Days Between Arrival and Departure Date?</th>
        <td>
          <input type="text" name="days_from_tomorrow" value="<?php echo get_option('next_period'); ?>" />
        </td>
      </tr>
    </table>
    <p class="submit">
      <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
  </form>
</div>  
<?php  
}

function kindle_register_options() {
  //register our settings
	register_setting( '_kindle_feed_settings', 'next_number' );
	register_setting( '_kindle_feed_settings', 'next_period' );
}




?>
