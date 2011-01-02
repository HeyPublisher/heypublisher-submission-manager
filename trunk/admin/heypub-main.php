<?php
/**
* Script called by main menu option
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }


/**
* Helper to consistently get the page title and logo displayed.
* This function prints to the screen.
*/
function heypub_display_page_title($title,$supress_logo=false) {
  global $hp_xml;
?>  
  <h2><?php echo $title; ?></h2>
<?php  
  if (!$supress_logo) {
    heypub_display_page_logo();
  }
}

function heypub_display_page_logo() {
  global $hp_xml, $hp_base;
?>
    <div id='heypub_logo'><a href='http://heypublisher.com' target='_blank' title='Visit HeyPublisher.com'><img src='<?php echo HEY_BASE_URL.'/images/logo.jpg'; ?>' border='0'></a><br/>
    <a class='heypub_smart_button' href='<?php echo HEYPUB_FEEDBACK_GETSATISFACTION; ?>' target='_blank' title="Need Support?  We're here to help!">Questions?  Contact Us!</a>
<?php
      $seo = $hp_xml->get_config_option('seo_url');
      if ($seo) {
?>      
      <b><a target=_blank href="<?php echo $seo; ?>">See Your Site in Our Database</a></b>
<?php 
    }
    ?>
    <div id="heypub_donate">
      <?php echo $hp_base->make_donation_link(); ?>
    </div>
  </div>
<?php  
  
}
// Show the page
function heypub_menu_main()  {
	global $wpdb,$wp_roles, $hp_xml, $hp_base;

	// get feed_messages
  require_once(ABSPATH . WPINC . '/rss.php');
  
  if(!isset($wp_roles)) {
  	$wp_roles = new WP_Roles();
  }
  $roles = $wp_roles->role_objects;

?>
  <div class="wrap">
    <?php heypub_display_page_title('HeyPublisher Overview'); ?>
    <div id="hey-content">
      <h3>Welcome</h3>

      <p>HeyPublisher allows you to accept unsolicited submissions from writers who are not registered users of your blog, magazine, or online Wordpress-powered site.</p>
      <p>HeyPublisher is the premier online site for writers to discover new writing markets.  By using this plugin you join a large and well-respected group of online publishers.  Best of all, you help ensure copyright protection for both the author and your site, as HeyPublisher provides independent 3rd party auditing of all submission transactions.</p>
      <p>As the publisher of <b><i><?php bloginfo('name'); ?></i></b> you control:
      <ul class='heypub-list'>
        <li>the genres of work you will accept</li>
        <li>whether or not to accept simultaneous submissions</li>
        <li>whether or not to accept previously published works</li>
        <li><i>... and much, much more ... </i></li>
      </ul>
      </p>

  	  <h3>Plugin Statistics</h3>
  <table class="widefat post fixed">
  <thead>
    <tr>
      <th>Plugin Version</th>
      <th>Build #</th>
      <th>Build Date</th>
      <th>Plugin Validated</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><?php echo HEYPUB_PLUGIN_VERSION; ?></td>
      <td><?php echo $hp_xml->get_install_option('version_current'); ?></td>
      <td><?php echo $hp_xml->get_install_option('version_current_date'); ?></td>
      <td>
<?php 
  if (!$hp_xml->is_validated) {
    echo "<a href='". heypub_get_authentication_url() . "'>CLICK HERE to VALIDATE</a>";
  } else {
    echo $hp_xml->is_validated;
  }
?>
      </td>
    </tr>
    </tbody>
  </table>
<?php
if ($hp_xml->is_validated) {
  // fetch the publisher info and update the local db with latest stats
  $p = $hp_xml->get_publisher_info();
  if ($p) {
    $hp_xml->set_config_option('seo_url',$p[seo_url]);
    $hp_xml->set_config_option('homepage_first_validated_at',$p[homepage_first_validated_at]);
    $hp_xml->set_config_option('homepage_last_validated_at',$p[homepage_last_validated_at]);
    $hp_xml->set_config_option('guide_first_validated_at',$p[guide_first_validated_at]);
    $hp_xml->set_config_option('guide_last_validated_at',$p[guide_last_validated_at]);
    // we won't store total subs and open subs
    
    // xml.avg_response_days(-1)   # we'll calculate this later
    // xml.avg_acceptance_rate(-1) # we'll calculate this later
    // xml.writer_comments(@pub.comments.count(:include => [:comment_type],:conditions=>["comment_types.name = 'public'"]))
    // xml.writer_favorites(@pub.user_publishers.count)

  }
  //  now print it out:
?>  
<h3>HeyPublisher Statistics for <i><?php bloginfo('name'); ?></i></h3>
<table class="widefat post fixed">
<thead>
  <tr>
    <th>Homepage Last Indexed</th>
    <th>Guidelines Last Indexed</th>
    <th># Comments</th>
    <th># Favorites</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td><?php echo $hp_xml->get_config_option('homepage_last_validated_at'); ?></td>
    <td><?php echo $hp_xml->get_config_option('guide_last_validated_at'); ?></td>
    <td><?php echo $p['writer_comments']; ?></td>
    <td><?php echo $p['writer_favorites']; ?></td>
  </tr>
</tbody>
</table>
<table class="widefat post fixed">
<thead>
  <tr>
    <th>Submissions Received</th>
    <th>Pending Review</th>
    <th>Submissions Published (%)</th>
    <th>Submissions Rejected (%)</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td><?php echo $p['total_subs']; ?></td>
    <td><?php echo $hp_base->submission_summary_link($p['total_open_subs']); ?></td>
    <td><?php ($p[total_published_subs]) ? printf("%s &nbsp;&nbsp; (%s)", $p[total_published_subs], $p['published_rate']) : 'N/A'; ?>%</td>
    <td><?php ($p[total_rejected_subs]) ? printf("%s &nbsp;&nbsp; (%s)", $p[total_rejected_subs], $p['rejected_rate']) : 'N/A'; ?>%</td>
  </tr>
</tbody>
</table>  

<table class="widefat post fixed">
<thead>
  <tr>
    <th>Avg. Response Time</th>
    <th>Subs Open 30 Days</th>
    <th>Subs Open 60 Days</th>
    <th>Subs Open 90 Days</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td class='t'><?php echo $p['avg_response_days']; ?> Days</td>
    <td class='approved'><?php echo $p['total_thirty_late']; ?></td>
    <td class='waiting'><?php echo $p['total_sixty_late']; ?></td>
    <td class='spam'><?php echo $p['total_ninety_late']; ?></td>
  </tr>
</tbody>
</table>  
<?php  
}
?>    
  
  <h3>How to Control the Style of the Submission Form</h3>
  <p>This plugin uses your current theme's <!-- (<i><?php echo get_current_theme(); ?></i>) --> stylesheet to control the layout of the submission form.</p>
  <p>If you want to customize how the submission form looks, please <a href="<?php echo HEYPUB_SVC_URL_STYLE_GUIDE; ?>" target=_new title='Click to open the style guide in a new window'>read the style guide</a>.</p> 
  
  </div>
<?php
}


function heypub_not_authenticated($page) {
?>  
  <div class="wrap">
    <?php heypub_display_page_title('Not Authenticated!'); ?>
    <div id="hey-content">
      It appears you have not yet authenticated.  Please <a href='<?php heypub_get_authentication_url($page);?>'>CLICK HERE</a> to authenticate.</p>
    </div>
  </div>
<?php  
}

function heypub_get_authentication_url($page=false) {
  if ($page == FALSE) {
    $page = HEYPUB_PLUGIN_NOT_AUTHENTICATED_ACTION;
  }
  $url = sprintf('%s/%s?page=%s',get_bloginfo('wpurl'),'wp-admin/admin.php',$page);
  return $url;
}


/**
* Initialize the upgrade of the plugin
*/
function heypub_upgrade_notice() {
  global $hp_xml;
    $ver_cur = $hp_xml->get_install_option('version_current');
    if($ver_cur != false && $ver_cur != HEYPUB_PLUGIN_BUILD_NUMBER) { 
?>
        <div id="message" class="updated" ><p>You've recently upgraded HeyPublisher Submission Manager. To finalise the upgrade process, <a href="admin.php?page=heypub_show_menu_submissions">please visit the plugin configuration page</a>.</p></div>
<?php 
    }
}

?>