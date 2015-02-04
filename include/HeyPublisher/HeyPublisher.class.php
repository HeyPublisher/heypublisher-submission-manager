<?php
/**
* HeyPublisher class is the root class from which all plugin functionality is extended
*
*/
class HeyPublisher {
  var $my_categories = array();
  var $error = false;
  public function __construct() {

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
  * Wrapper for the get_user_meta() function that changed in WP 3.0
  */
  private function get_author_meta($uid,$key) {
    if (function_exists('get_user_meta')) {
      $val = get_user_meta($uid,$key,true);
    } else {
      $val = get_usermeta($uid,$key,true);
    }	
    return $val;
  }
  /** 
  * Wrapper for the update_user_meta() function that changed in WP 3.0
  */
  private function update_author_meta($uid,$key,$val) {
    if ($val) {
      if (function_exists('update_user_meta')) {
        update_user_meta($uid,$key,"$val");
      } else {
        update_usermeta($uid,$key,"$val");
      }	
    }
  }
  
  /**
  * Update Meta Info on this Author in the WP database.  We will already have a valid User ID at this stage.
  *
  * @param int $uid Record ID of WP User record
  * @param object $a The Author object returned by HeyPublisher API
  */ 
  public function update_author_info($uid,$a) {
    // Get the existing Author Info, including Meta
    $author = get_userdata($uid);
    if ($a->full_name) {
      // printf("<pre>Checking display name '%s' vs full name '%s'\nAuthor object : %s</pre>",$author->display_name,$a->full_name,print_r($a,1));
      // Check to see that 'display names' match
      if ((!$author->display_name) || ($author->display_name != $a->full_name)) {
	      wp_update_user( array ('ID' => $uid, 'display_name' => $a->full_name) ) ;
	      $this->update_author_meta($uid,'first_name',sprintf("%s",$a->first_name));
      	$this->update_author_meta($uid,'last_name',sprintf("%s",$a->last_name));
      }
    }
    // Update bio for this author if we have it.  No need to test for existing value; we want latest pushed.
    if ($a->bio) {
      $this->update_author_meta($uid,'description',sprintf("%s",$a->bio));
    }
    
    // Finally, ensure that all meta keys used for lookup are set properly.
    // The 'old' method of mapping on email....
    $meta1 = $this->get_author_meta($uid,HEYPUB_USER_META_KEY_AUTHOR_ID);
    if ((!$meta1) ||($meta1 != sprintf("%s",$a->email))) {
      $this->update_author_meta($uid,HEYPUB_USER_META_KEY_AUTHOR_ID,sprintf("%s",$a->email));
    }
    // The 'new' method of mapping on user OID generated by HeyPub
    $meta2 = $this->get_author_meta($uid,HEYPUB_USER_META_KEY_AUTHOR_OID);
    if ((!$meta2) ||($meta2 != sprintf("%s",$a->oid))) {
      $this->update_author_meta($uid,HEYPUB_USER_META_KEY_AUTHOR_OID,sprintf("%s",$a->oid));
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
    $uid = FALSE;
    $msg = array();
    if (function_exists('get_users')) {
      // First look by OID 
      $users = get_users(array('meta_key'=> HEYPUB_USER_META_KEY_AUTHOR_OID, 'meta_value' => $a->oid));
      if (FALSE != $users) { 
        $uid = $users[0]->ID; 
        // $msg[] = "Setting user ID '$uid' by OID";
      } else {
        // then by HP User ID
        $users = get_users(array('meta_key'=> HEYPUB_USER_META_KEY_AUTHOR_ID, 'meta_value' => $a->email ));
  			// printf("<pre>users array = %s</pre>",print_r($users,1));
        if (FALSE != $users) {
          $uid = $users[0]->ID; 
          // $msg[] = "Setting user ID '$uid' by Author ID";
        }
      }
    }
    
    // If we're still FALSE, attempt to find by email address
    if (FALSE == $uid) {
      $user = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_email= %s",$a->email));
      if (FALSE != $user) { 
        $uid = $user; 
        // $msg[] = "Setting user ID '$uid' by Email in WP";
      }
    }
    // printf("<pre>message from user create: %s</pre>",print_r($msg,1));
    if (FALSE != $uid) {
      // If we're NOT FALSE, update the author info.
      $this->update_author_info($uid,$a);
      return $uid;
    } else { 
      return FALSE;
    }
  }

  /**
  * Create the User Account if we don't already have it
  * 
  * @param object $a The User Object returned by the HeyPublisher webservice.
  */
  public function create_author($username,$a) {
    $uid = false;
    $user = get_user_by('login',$username);
    if (!$user) {
      // create the record
    	$random_password = wp_generate_password( 12, false );
    	$uid = wp_create_user( $username, $random_password, $a->email );
    } else {
      $uid = $user->ID;
    }
    
    // SANITY CHECK.  If we're creating a 'new' user and we already have META data for that user, throw an error
    // The 'old' method of mapping on email....
    $this->error = false;
    $meta1 = $this->get_author_meta($uid,HEYPUB_USER_META_KEY_AUTHOR_ID);
    $meta2 = $this->get_author_meta($uid,HEYPUB_USER_META_KEY_AUTHOR_OID);
    if (($meta1) && ($meta1 != sprintf("%s",$a->email))) {
      $this->error = 'A different HeyPublisher author with this username already exists in database (1)';
    } elseif (($meta2) && ($meta2 != sprintf("%s",$a->oid))) {
      $this->error = 'A different HeyPublisher author with this username already exists in database  (2)';
    }
    if ($this->error) {
      return FALSE;
    }
    
    if (FALSE != $uid) {
      // If we're NOT FALSE, update the author info.
      $this->update_author_info($uid,$a);
      return $uid;
    } else { 
      $this->error = 'Unable to create the user record';
      return FALSE;
    }
  }
  
  /**
  * Helper function to get the URL to edit the author record
  */
  public function get_author_edit_url($id) {
    $url = sprintf('%s/%s?user_id=%s',get_bloginfo('wpurl'),'wp-admin/user-edit.php',$id); 
    return $url;
  }
}
