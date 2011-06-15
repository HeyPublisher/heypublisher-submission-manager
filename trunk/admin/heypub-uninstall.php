<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }


/**
* Delete all of the pre-defined and user defined HeyPublisher Options
*/
function heypub_delete_all_options() {
  global $hp_xml; 

?>
   <span class='uninstall'>Deleting HeyPublisher Options ... </span>
<?php  

  heypub_uninit();

?>
   <span class='uninstall ok'>DONE</span><br/>
<?php  
}

/**
* Menu to display uninstall options
*
* This page also handles redirects from this page
*
*/
function heypub_menu_uninstall() {
  $plugin_name = HEY_DIR.'/heypublisher-sub-mgr.php';
  // URL to deactivate the plugin
  $deactivate_url = wp_nonce_url("plugins.php?action=deactivate&plugin=$plugin_name","deactivate-plugin_$plugin_name");
  $uninstall_url = wp_nonce_url("admin.php?page=heypub_menu_uninstall&action=delete_options",'heypub_delete_options');
?>

  <div class="wrap">
    <?php heypub_display_page_title('Uninstall HeyPublisher'); ?>    
    <div id="heypub_content">
<?php   
    if($_REQUEST['action'] == "delete_options") {
      // only deleting the options and possibly deactivating the plugin
      check_admin_referer('heypub_delete_options');
      heypub_delete_all_options();
?>
      <p>
        <a href='<?php echo $deactivate_url; ?>' title='Deactivate HeyPublisher Plugin' class="delete">
          Click HERE to deactivate HeyPublisher Plugin
        </a>
      </p>
<?php
    } else { 
      // Default info to display to user
?>
    <p>You can uninstall the HeyPublisher plugin at anytime.  The works that you have already published <i>will not</i> be affected in anyway.</p>
    <p>After uninstalling this plugin, you will no longer be able to accept unsolicited submissions from the HeyPublisher authors.  The ability to control reading periods, specific genres and other features of this plugin will no longer be active.</p>
    <p><a href="<?php echo  $uninstall_url; ?>" class='uninstall'>Uninstall HeyPublisher Plugin</a></p>
            
<?php 
    } 
?>
    </div>
  </div>
<?php
}
?>
