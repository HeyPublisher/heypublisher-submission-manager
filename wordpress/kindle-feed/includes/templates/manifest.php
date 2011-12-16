<?php
/**
 * Kindle Feed Template for displaying RSS2 Posts in a way Kindle can consume.
 *
 * @package WordPress
 */

// The class with set options should already be loaded.
global $kf;
// Fetch the posts that meet our requirements
query_posts( $kf->query_string_for_posts() );

$categories = array();
while ( have_posts()) : the_post(); 
  $cats = get_the_category();
  $categories[$cats[0]->category_nicename] = get_category_link( $cats[0]->cat_ID );
endwhile;

header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0">
<channel>
  <title><?php echo $kf->feed_title(); ?></title>
  <link><?php bloginfo_rss('url') ?></link>
  <description><?php bloginfo_rss("description") ?></description>
  <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', $kf->options['static']['published'], false); ?></pubDate>
  <lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', $kf->options['static']['build'], false); ?></lastBuildDate>
  <item>
    <link><?php printf("%s/kindle_cover", site_url()); ?></link>
  </item>
<?php
  foreach ($categories as $slug=>$link) { 
?>
  <item>
    <link><?php printf("%skindle_section", $link); ?></link>
  </item>
<?php 
  }; 
	// Need to add the masthead here

?>
</channel>
</rss>
