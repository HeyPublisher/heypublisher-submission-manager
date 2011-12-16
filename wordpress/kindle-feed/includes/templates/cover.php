<?php
/**
 * Kindle Feed Template for displaying RSS2 Posts in a way Kindle can consume.
 *
 * @package WordPress
 */

// The class with set options should already be loaded.
global $kf;
// This is static, simply providing the xml file that Kindle requires to route to the actual artwork.
header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>
<rss version="2.0">
<channel>
	<title>Cover</title>
  <item>
    <link><?php printf("%s/kindle_cover_details", site_url()); ?></link>
  </item>
</channel>
</rss>
