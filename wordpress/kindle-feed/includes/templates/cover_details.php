<?php
/**
 * Kindle Feed Template for displaying RSS2 Posts in a way Kindle can consume.
 *
 * @package WordPress
 */

// Eventually - this should allow users to select different cover art per 'issue'
global $kf;
$opts = get_option( $kf->config_val );

// This is mostly static, provide URL to actual artwork.
header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>
<html>
	<head>
		<title>Cover</title>
		<meta name="abstract" content=""/>
		<meta name="author" content=""/>
		<meta name="dc.date.issued" content="<?php echo mysql2date('Ymd', $kf->options['static']['published'], false); ?>"/>
	</head>
		<body><img src="<?php echo $opts['cover_art_url']; ?>"/></body>
</html>	

