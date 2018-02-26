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

  public function page_title($title) {
    return "<h2>$title</h2>";
  }
  // TODO: Why should this create an absolute URL - why not a relative url?
  // TODO: Replace this with function in Page class
  public function get_form_post_url_for_page($page) {
    $url = sprintf('%s/wp-admin/admin.php?page=%s',get_bloginfo('wpurl'),$page);
    return $url;
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
    global $hp_subs;
    $str = sprintf("<a href='admin.php?page=%s'>%s</a>",$hp_subs->slug,$text);
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
    if (FALSE != $a->bio) {
      $this->update_author_meta($uid,'description',sprintf("%s",$a->bio));
    }
    // Update the writer website value
    if (FALSE != $a->website) {
      // we don't care about errors at this stage
      wp_update_user( array( 'ID' => $uid, 'user_url' => $a->website ) );
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
        $msg[] = "Setting user ID '$uid' by OID";
      } else {
        // then by HP User ID
        $users = get_users(array('meta_key'=> HEYPUB_USER_META_KEY_AUTHOR_ID, 'meta_value' => $a->email ));
  			// printf("<pre>users array = %s</pre>",print_r($users,1));
        if (FALSE != $users) {
          $uid = $users[0]->ID;
          $msg[] = "Setting user ID '$uid' by Author ID";
        }
      }
    }

    // If we're still FALSE, attempt to find by email address
    if (FALSE == $uid) {
      $msg[] = "attempting to find by email address";
      $msg[] = sprintf("looking for email: %s",$a->email );
      $uid = username_exists( "$a->email" );
      $msg[] = sprintf("user ID from username: %", $uid);
      if (FALSE == $uid) {
        $uid = email_exists("$a->email");
        $msg[] = sprintf("user ID from email: %", $uid);
      }
    }
    // printf("<pre>message from user create: %s</pre>",print_r($msg,1));
    // printf("<pre>$uid: %s</pre>",print_r($uid,1));
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

    // $user = get_user_by('login',$username);
    $uid = username_exists( $username );
    if ( !$uid ) {
      $uid = email_exists($username);
    }
    if (!$uid) {
      // create the record
    	$random_password = wp_generate_password( 12, false );
    	$uid = wp_create_user( $username, $random_password, $a->email );
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
