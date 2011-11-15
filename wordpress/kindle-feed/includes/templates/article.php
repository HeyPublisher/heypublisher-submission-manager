<?php
/**
 * Kindle Feed Template for displaying Future Posts in a way Kindle can consume.
 *
 * @package WordPress
 */

// The class with set options should already be loaded.
global $kf, $post;
query_posts( $kf->query_string_for_posts(array('p'=> $post->ID )));
while( have_posts()) : the_post();
	// $temp_query = $wp_query->query_vars;
	$post = get_post(the_ID(), OBJECT);
	printf("<pre>ID = %s\nPOST = \n%s</pre>",$id,print_r($post,1));
endwhile;
print "ending start<br>";

?>
	
