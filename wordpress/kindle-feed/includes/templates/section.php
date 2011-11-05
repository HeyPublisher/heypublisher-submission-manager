<?php
/**
 * Kindle Feed Template for displaying RSS2 Posts in a way Kindle can consume.
 *
 * @package WordPress
 */

// The class with set options should already be loaded.
global $kf;
// If we're in the future we don't need dates.  If we're current, we need dates and not future.
// $this->date_range_for_feed();
// // $query = "post_status=published";
//     // if ($this->feed[live]) {
//       $category = '';
//       if ($cat != 0) {
//         // Limit to the requested category
//         $category = sprintf("&cat=%s",$cat);
//       }
// 	    $query = sprintf('year=%s&monthnum=%s&post_status=future&posts_per_page=100%s',
// 	      $this->feed[year],$this->feed[month],$category);
//     // }

$category = get_the_category();
// Range of content should be for entire 'month' or 'week' - and only for this category.
query_posts( $kf->query_string_for_posts(array('cat'=>$category[0]->cat_ID)) );
$categories = array();
  
header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0">
<channel>
  <title><?php echo $category[0]->cat_name; ?></title>
<?php while( have_posts()) : the_post(); ?>
  <item>
    <link><?php printf("%s&feed=kindle_article",wp_get_shortlink()) ; ?></link>
  </item>
<?php endwhile; ?>
</channel>
</rss>
