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

function kindle_custom_feed() {
  require_once(dirname(__FILE__) . '/includes/feed-template.php');
}
add_action('do_feed_kindle', 'kindle_custom_feed', 10, 1);


function kindle_feed_rewrite($wp_rewrite) {
$feed_rules = array(
'feed/(.+)' => 'index.php?feed=' . $wp_rewrite->preg_index(1),
'(.+).xml' => 'index.php?feed='. $wp_rewrite->preg_index(1)
);
$wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;
}
add_filter('generate_rewrite_rules', 'kindle_feed_rewrite');


?>
