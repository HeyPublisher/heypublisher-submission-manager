<?php
/**
* Script called by main menu option
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

load_template(dirname(__FILE__) . '/../include/classes/HeyPublisher/Page/Overview.class.php');
$hp_main = new \HeyPublisher\Page\Overview;
// $hp_main = new Main();

/**
* Helper to consistently get the page title and logo displayed.
* This function prints to the screen.
*/
function heypub_display_page_title($title,$supress_logo=false) {
  global $hp_xml, $hp_main;
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
    $seo = '';  // this value is incorrect and is referencing the domain of the publication - not the seo url in heypub :(
    // $seo = $hp_xml->get_config_option('seo_url');
    if ($seo) {
?>
      <b><a target=_blank href="<?php echo $seo; ?>">See Your Site in Our Database</a></b>
<?php
    } else {
?>
      <i>Help Support HeyPublisher</i>
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
	global $hp_main;
  $hp_main->page();
}

// TODO: I think this is a duplicate of other logic.  Research
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
// TODO: I know this is a duplicate of logic in the Options class
function heypub_get_authentication_url($page=false) {
  global $hp_opt;
  if ($page == FALSE) {
    $page = $hp_opt->slug;
  }
  $url = sprintf('%s/%s?page=%s',get_bloginfo('wpurl'),'wp-admin/admin.php',$page);
  return $url;
}


/**
* Initialize the upgrade of the plugin
*/
function heypub_upgrade_notice() {
  global $hp_xml, $hp_subs;
    $ver_cur = $hp_xml->get_install_option('version_current');
    if($ver_cur != false && $ver_cur != HEYPUB_PLUGIN_BUILD_NUMBER) {
      $html = <<<EOF
        <div id="message" class="updated" ><p>You've recently upgraded HeyPublisher Submission Manager. To finalise the upgrade process, <a href="admin.php?page={$hp_subs->slug}">please visit the plugin configuration page</a>.</p></div>
EOF;
      print($html);
    }
}

?>
