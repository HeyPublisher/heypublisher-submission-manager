<?php
/**
* HeyPublisher class is the root class from which all plugin functionality is extended
*
*/
class HeyPublisher {
  var $my_categories = array();
  var $wp_version = 0;  // Way to store the WP version in the class
  
  public function __construct() {
    $this->get_wp_version();
  }   

  public function __destruct() {

  }

  public function page_title_with_logo($title) {
    $ret = $this->page_title($title);
    $ret .= $this->page_logo();
    return $ret;
  }
  
  public function page_title($title) {
    return "<h2>$title</h2>";
  }
  
  public function get_form_post_url_for_page($page) {
    $url = sprintf('%s/wp-admin/admin.php?page=%s',get_bloginfo('wpurl'),$page);
    return $url;
  }
  
  public function page_logo() {
    global $hp_xml;
    $format = "<div id='heypub_logo'>%s</div>";
    $content = <<<EOF
  <a href='http://heypublisher.com' target='_blank' title='Visit HeyPublisher.com'>
    <img src='{$_CONSTANTS['HEY_BASE_URL']}/images/logo.jpg' border='0'>
    <br/>Visit HeyPublisher.com</a>
    <br/>
    <a href='mailto:{$_CONSTANTS['HEY_BASE_URL']}'>Email Us</a>
EOF;
    $seo = 'foo';
//  $seo = $hp_xml->get_config_option('seo_url');
    if ($seo) {
      $content .= <<<EOF
<hr>
<b><a target='_blank' href="$seo">See Your Site in Our Database</a></b>
EOF;
    }
    $ret = sprintf($format,$content);
    return $ret;
  }

  public function page_layout($content) {
    $ret = <<<EOF
<div class="wrap">
    $content
</div>
EOF;
    return $ret;
  }
  
  // This is a non-printing function.  Output will be returned as a string
  // Two input params : 
  // - the contextual publisher object
  // - the submission object
  public function other_publisher_link($obj,$sub) {
    // loop through values in the object
    $string = false;
    $all = '';
    // printf("<pre>OBJ = %s</pre>",print_r($obj,1));
    if ($obj) {
      foreach ($obj as $key=>$val) {
        $str = '';
        if ($val->url != '') {
          $str .= sprintf("<b><a target=_blank href='%s'>%s</a></b>",$val->url,$val->name);
        } else {
          $str .= sprintf("<b>%s</b>",$val->name);
        }
        if ($val->date != '') {
          $str .= sprintf("&nbsp;<small>[%s]</small>",$val->date);
        }
        if ($val->editor != '' && $val->email != '') {
          $str .= sprintf("<span>edited by <a href='mailto:%s?subject=Question about \"%s\" by %s %s'>%s</a></a></span>",
              $val->email,$sub->title,$sub->author->first_name, $sub->author->last_name,$val->editor);
        }
        $all .= sprintf('<li>%s</li>',$str);
      }
      $string = sprintf('<ul>%s</ul>',$all);
    }
    return $string;
  }
  
  public function get_dashboard_stats() {
    global $hp_xml;
    if (!$hp_xml->is_validated) {
      $data = sprintf("<td colspan='4'><i>Plugin needs to be validated first &nbsp;&nbsp;<a href='%s'>CLICK HERE to VALIDATE</a></i></td>",
        heypub_get_authentication_url());
    } else {
      $p = $hp_xml->get_publisher_info();
      if ($p[total_open_subs]) {
        $p[total_open_subs] = $this->submission_summary_link($p[total_open_subs]);
    }
    $data = <<<EOF
<td class="first b">$p[total_subs]</td>
<td class='t'>Total Subs</td>
<td class='b'>$p[total_open_subs]</td>
<td class='last t waiting'>Pending</td>
</tr>
<tr>
<td class="first b">$p[total_published_subs]</td>
<td class='t approved'>Published</td>
<td class='b'>$p[total_rejected_subs]</td>
<td class='last t spam'>Rejected</td>
</tr>
<tr>
<td class="first b">$p[published_rate]</td>
<td class='t approved'>Published %</td>
<td class='b'>$p[rejected_rate]</td>
<td class='last t spam'>Rejected %</td>
EOF;
// for future ref, if we want to add this in:
// </tr>
// <tr>
// <td colspan=4 class='t'><b>Response Statistics:</b></td>
// </tr>
// <tr>
// <td class="first b">$p[avg_response_days]</td>
// <td class='t'>Avg. Response Days</td>
// <td class='b'>$p[total_thirty_late]</td>
// <td class='last t approved'>30 Days Old</td>
// </tr>
// <tr>
// <td class="first b">$p[total_sixty_late]</td>
// <td class='t waiting'>60 Days Old</td>
// <td class='b'>$p[total_ninety_late]</td>
// <td class='last t spam'>90 Days Old</td>
    }
    return $data;
  }
  
  // for version >= 3.0 stats are displayed in their own dashboard widget
  public function right_now_widget() {
    $data = $this->get_dashboard_stats();
    $str = <<<EOF
<div class='table' id='dashboard_right_now'>
<table>
  <tr class='first'>$data</tr>
</table>
</div>
EOF;
    return $str;
  }

  public function make_license_link() {
    global $hp_xml;
    $url = sprintf('%s/%s',HEYPUB_LICENSE_URL,$hp_xml->get_install_option('publisher_oid'));
    $format = "<a href='%s' target='_blank' title='See the Additional Features Available when you License this Plugin' class='heypub_smart_button green'>License Plugin</a>";
    $str = sprintf($format,$url);
    return $str;
  }

    
  public function make_donation_link($text_only=false) {
    $format = "<a href='".HEYPUB_DONATE_URL."' target='_blank' title='Thank You for Donating to HeyPublisher'>%s</a>";
    if ($text_only) {
      $str = sprintf($format,'Make a Donation');
    } else {
      $str = sprintf($format,"<img id='heypub_donate' style='vertical-align:middle;' src='".HEY_BASE_URL."/images/donate.jpg' border='0'>");
    }
    return $str;
  }

  public function submission_summary_link($text='See All Submissions') {
    $str = sprintf("<a href='admin.php?page=heypub_show_menu_submissions'>%s</a>",$text);
    return $str;
  }
  
  public function get_yes_no_checkbox($label,$key,$val,$alt=false) {
    $no = ($val == '0' || $val == FALSE) ? 'selected=selected' : null;
    $yes = ($val == '1' || $val == TRUE) ? 'selected=selected' : null;
    $str = <<<EOF
<label class='heypub' for='hp_$key'>$label</label>
<select name="heypub_opt[$key]" id="hp_$key">
<option value='0' $no>No</option>
<option value='1' $yes>Yes</option>
</select>
&nbsp;<small>$alt</small>
EOF;
    return $str;
  }
  
  public function truncate($string, $limit=15) {
    $break=" "; 
    $pad="...";
    // return with no change if string is shorter than $limit  
    if(strlen($string) <= $limit) return $string; 
    $string = substr($string, 0, $limit); 
    if(false !== ($breakpoint = strrpos($string, $break))) { 
      $string = substr($string, 0, $breakpoint); 
    } 
    return $string . $pad; 
  }
  
  public function blank($str='Not Provided') {
   $ret = sprintf('<span class="heypub_empty">%s</span>',$str);
   return $ret;  
  }
  
  public function tabbed_nav($key,$label) {
    $class = '';
    if ($key == 'p') { // Initializer
      $class = 'heypub-tab-pressed';
    }
    $ret = sprintf("<a href='#' class='heypub-tab %s' id='heypub_%s_tab' onclick='heypub_toggle_tabs(\"%s\");return false;'>%s</a>", $class,$key,$key,$label);
    return $ret;
  }
  public function blockquote($content){
    $ret = sprintf("<blockquote class='heypub_summary'>%s</blockquote>",$content);
    return $ret;
  }
  
  /**
  * Called by constructor to set the WP version number in memory
  */
  public function get_wp_version() {
    global $wp_version;
    $this->wp_version = $wp_version += 0;
  }
  
  /**
  * Update Meta Info on this Author in the WP database
  *
  * @param int $uid Record ID of WP User record
  * @param string $key The keyname of the meta value to be updated
  * @param string $val The value to set for the keyname.
  */ 
  public function update_author_info($uid,$key,$val) {
    
    
    
    // update the user's bio, too - if we have it.
  	$this->update_author_info($user_id,'description',sprintf("%s",$a->bio));
    //  right now - this is the only unique key we will share with plugins.  OIDs coming soon...
    // define('HEYPUB_USER_META_KEY_AUTHOR_OID','_heypub_user_meta_key_author_oid');
    
  	$this->update_author_info($user_id,HEYPUB_USER_META_KEY_AUTHOR_ID,sprintf("%s",$a->email));
    // And the user's first/last name if we have it
  	if ($a->full_name) {
  	  wp_update_user( array ('ID' => $user_id, 'display_name' => $a->full_name) ) ;
    	$this->update_author_info($user_id,'first_name',sprintf("%s",$a->first_name));
    	$this->update_author_info($user_id,'last_name',sprintf("%s",$a->last_name));
    }
    
    
    
    
    
    // The function changed in WP 3.0!!
    // Conver to an int
    if ($val) {
      if ($this->wp_version >= 3) {
        update_user_meta($uid,$key,"$val");
      } else {
        update_usermeta($uid,$key,"$val");
      }	
    }
  }

  /**
  * Attempt to find the author in the WP database by one of numerous attributes we have on file.
  * Edge Case: If the HeyPub user record does not have an email address, we create one for them based upon OID.
  *
  * @param object $a The User Object returned by the HeyPublisher webservice.
  * @return bool|int $uid Return the User ID of the author (if found) or return FALSE.
  */ 
  public function get_author_id($a) {
    global $wpdb;
    // If we already have this user in the database with meta data that matches our known meta values 
    // for this submission, then we auto-map that user to this piece.
    // Otherwise we'll return false which will trigger a prompt for the admin to create a 'user' account.
    if (function_exists('get_users')) {
      // First look by OID 
      $users = get_users(array('meta_key'=> HEYPUB_USER_META_KEY_AUTHOR_OID, 'meta_value' => $a->oid));
      if (FALSE != $users) { return $users[0][ID]; }
      // then by HP User ID
      $users = get_users(array('meta_key'=> HEYPUB_USER_META_KEY_AUTHOR_ID, 'meta_value' => $a->email ));
			// printf("<pre>users array = %s</pre>",print_r($users,1));
      if (FALSE != $users) { return $users[0]->ID; }
    }
    // If we're still here, attempt to find by email address
    $user = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_email= %s",$a->email));
    if (FALSE != $user) { return $user; }
    return FALSE;
  }

  /**
  * Create the User Account if we don't already have it
  * 
  * @param object $a The User Object returned by the HeyPublisher webservice.
  */
  function create_or_update_author($a) {
    $user_id = false;
    if ($a) {
      // fetch the user id
      $user_id = $this->get_author_id( $a );

      if ( !$user_id ) {
      	$random_password = wp_generate_password( 12, false );
      	$user_id = wp_create_user( $user_name, $random_password, $user_name );
      } 

      return $user_id;


      // update the user's meta information
    	$this->update_author_info($user_id,$a);
    }
    return $user_id;
  }
  
}
