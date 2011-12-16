<?php
/**
 * Kindle Feed Template for displaying RSS2 Posts in a way Kindle can consume.
 *
 * @package WordPress
 */

// The class with set options should already be loaded.
global $kf;
$category = get_the_category();
// Range of content should be for entire 'month' or 'week' - and only for this category.
query_posts( $kf->query_string_for_posts(array('cat'=>$category[0]->cat_ID)) );
  
header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0">
<channel>
  <title><?php echo $category[0]->cat_name; ?></title>
<?php while( have_posts()) : the_post(); ?>
  <item>
    <link><?php printf("%skindle_article",get_permalink()) ; ?></link>
  </item>
<?php endwhile; ?>
</channel>
</rss>
