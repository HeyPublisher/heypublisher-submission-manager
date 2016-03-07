<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

// Show the moderation menu
//
function heypub_show_menu_submissions() {
  global $wpdb, $wp_roles, $hp_xml;

  heypub_submission_handler();

}

/**
* Display the menu of acceptable state transitions in the menu.
*
* @param string $nounce The Nounce for the form
* @param bool $detail If TRUE will display the 'request author revision' and 'accepted' values.  FALSE by default.
*/
function heypub_submission_actions($nounce,$detail=false,$sel='',$published=false) {
  global $hp_base, $hp_sub;
?>
  <div class="actions">
  <select name="action">
    <option value="-1">-- Select Action --</option>
<?php
  if ($detail) {
    if ($published) {
      $accept_text = 'Reimport into WordPress';
      $is_sel = 'selected';
    } else {
      $accept_text = 'Accept for Publication';
      $is_sel = $hp_sub->select_selected('accept',$sel); // this will default to empty string if either not true
    }
?>
  	<option <?php echo $is_sel; ?> value="accept"><?php echo $accept_text; ?></option>
    <option <?php echo $hp_sub->select_selected('request_revision',$sel); ?> value="request_revision">Request Author Revision</option>
<?php
  }
  if (!$published) {
?>
    <option <?php echo $hp_sub->select_selected('review',$sel); ?> value="review">Save for Later Review</option>
<?php
  }
?>  
    <option <?php echo $hp_sub->select_selected('reject',$sel); ?> value="reject">Reject Submission</option>

<?php   //  !!!    request_revision ?>


  </select>
  <br/>
  <input type="submit" class="heypub-button button-primary" value="Update Submission" name="doaction" id="doaction" />
<?php 
  if ($detail) {
    printf('<span id="return_to_summary">%s</span>',$hp_base->submission_summary_link('cancel'));
  }
  wp_nonce_field($nounce);
?>
  </div>
<?php
}

/**
* Display the 'Local' description for this category - or the HP value if the internal mapping has not been set
*/
function heypub_get_display_category($id,$default) {
  global $hp_xml;
  // $id is the remote category id from HP
  // All categories for this install:
  $categories =  get_categories(array('orderby' => 'name','order' => 'ASC'));
  $map = $hp_xml->get_category_mapping();
  $display = $default;
  if ($map) {
    foreach ($categories as $cat=>$hash) {
      if (($map["$id"]) && ($map["$id"] == $hash->cat_ID)) {
        $display = $hash->cat_name;
      }
    }
  }
  return $display;
}

/*
* Display the summary of submissions available for review
*
* If submission has been accepted, then need to show an optional 're-import' button.
*/
function heypub_list_submissions() {
  global $hp_xml, $hp_base;
  // This is a SimpleXML object being returned, with key the sub-id
  $subs = $hp_xml->get_recent_submissions();
  $form_post_url = sprintf('%s/%s',get_bloginfo('wpurl'),'wp-admin/admin.php?page=heypub_show_menu_submissions');
  $cats = $hp_xml->get_my_categories_as_hash();
  $accepted = heypub_get_accepted_post_ids();
  // $hp_xml->log(sprintf("Accepted POSTs = %s\nID 19470 = %s",print_r($accepts,true),$accepts['19470']));

?>
<div class="wrap">
  <h2>Submissions</h2>
  <div id='heypub_header'>
    <?php heypub_display_page_logo(); ?>
    <div id="heypub_content">
      <p>Below are the most recent <b><i><?php bloginfo('name'); ?></i></b> submissions sent in by your writers.</p>
      <p>Click on the title to read the submission.  This will open the submission in a new window.</p>
      <p>Click on the author's name to show or hide their bio.</p>
      <p>If you are unable to see the author's name or bio it means they did not provide this information when submitting their work.</p>
    </div>
  </div>
<form id="posts-filter" action="<?php echo $form_post_url; ?>" method="post">
<table class="widefat post fixed" cellspacing="0" id='heypub_submissions'>
<thead>
	<tr>
  	<th style='width:4%;' id='heypub_sub_cb' class='checkbox'>
  	  <input type="checkbox" onclick="heypub_auto_check(this,'posts-filter');"/>
  	</th>
  	<th style='width:20%;'>Title</th>
  	<th style='width:13%;'>Genre</th>
  	<th style='width:18%;'>Author</th>
  	<th style='width:15%;'>Email</th>
  	<th style='width:9%;'>Submitted</th>
  	<th style='width:5%;white-space:nowrap'>Words</th>
  	<th style='width:15%;'>Status</th>
	</tr>
</thead>

<tfoot />
<tbody>
<?php
if(!empty($subs)) {
  
  foreach($subs as $x => $hash) {
    $count++;
    $class = null;
    if(($count%2) != 0) { $class = 'alternate'; }

    // overide to highlight submissions where author has provided a rewrite
    if ($hash->status == 'writer_revision_provided') {
      $class .= ' revised';
    } elseif  ($hash->status == 'publisher_revision_requested') {
      $class .= ' requested';
    }

    $url = sprintf('%s/wp-admin/admin.php?page=heypub_show_menu_submissions&show=%s',get_bloginfo('wpurl'),"$x");
    $repub_url = $url;
    $disabled_url = '';
    // if ($accepted["$x"] || "$hash->status" == 'accepted') {
    //   // link to the editor screen
    //   if (!$accepted["$x"]) { // the post was accepted but not imported
    //     $disabled_url = ' onclick="alert(\'An error occurred importing this submission.\n\nTry reimporting it.\'); return false;"';
    //   }
    //   $url = sprintf('%s/wp-admin/post.php?action=edit&post=%s',get_bloginfo('wpurl'),$accepted["$x"]);
    // }
?>

    <tr id='post-<?php echo "$x"; ?>' class='<?php echo $class; ?>' valign="top">
      <th scope="row"><input type="checkbox" name="post[]" id='heypub_sub_id' value="<?php echo "$x"; ?>" /></th>
      <td class="heypub_list_title"><a href="<?php echo $url; ?>" title="Review '<?php echo $hash->title; ?>'"><?php echo $hp_base->truncate($hash->title,30); ?></a></td>
      <td><?php printf("%s", heypub_get_display_category($hash->category->id,$hash->category->name)); ?></td>

<?php if ($hash->author->bio != '') { ?>
      <td class="heypub_list_title">
        <span id="show_bio_<?php echo "$x"; ?>"><a href="#" onclick="
        $('post_bio_<?php echo "$x"; ?>').show();
        $('show_bio_<?php echo "$x"; ?>').hide();
        $('hide_bio_<?php echo "$x"; ?>').show();
        return false;" title="View author bio"><?php printf("%s", $hash->author->full_name,HEY_BASE_URL); ?>
        <span class="heypub-icons fa fa-plus-square"></span></a></span>
        <span id="hide_bio_<?php echo "$x"; ?>" style="display:none;"><a href="#" onclick="
        $('post_bio_<?php echo "$x"; ?>').hide();
        $('show_bio_<?php echo "$x"; ?>').show();
        $('hide_bio_<?php echo "$x"; ?>').hide();
        return false;" title="Hide author bio"><?php printf("%s", $hash->author->full_name,HEY_BASE_URL); ?>
        <span class="heypub-icons fa fa-minus-square"></span></a></span>
      </td>
<?php } else {
        printf("<td>%s %s</td>", $hash->author->first_name, $hash->author->last_name);
      }
?>
      <td>
<?php
  if (FALSE == $hash->author->email) {
    echo $hp_base->blank();
  } else {
    printf('<a title="Email the Author"  href="mailto:%s?subject=Your%%20submission%%20to%%20%s">%s</a>',$hash->author->email,get_bloginfo('name'),$hp_base->truncate($hash->author->email));
  }
?>
      </td>
      <td nowrap><?php printf("%s", $hash->submission_date); ?></td>
      <td><?php if (FALSE != $hash->word_count) { echo number_format("$hash->word_count");} else {echo '?';} ?></td>

      <td nowrap>
<?php
        $status = $hp_xml->normalize_submission_status($hash->status);
        if ($accepted["$x"] || "$hash->status" == 'accepted') {
          if (!$accepted["$x"]) { // the post was accepted but not imported
            $disabled_url = ' onclick="alert(\'An error may have occurred when importing this submission.\n\nTry Re-Accepting it.\'); return false;"';
          }
          $uri = sprintf('%s/wp-admin/post.php?action=edit&post=%s',get_bloginfo('wpurl'),$accepted["$x"]);
          printf("<a %s href='%s' title='This submission has already been imported.\nClick to view.'>%s <span class='heypub-icons fa fa-file-text-o'></span></a>", $disabled_url, $uri, $status);
        } else {
          echo $status;
        }
        
?>    </td>
    </tr>
<?php if ($hash->author->bio != '') { ?>
    <tr id='post_bio_<?php echo "$x"; ?>' style='display:none;'>
      <td colspan='8'><div class='heypub_author_bio_preview'><b>Author Bio:</b> <?php printf("%s", $hash->author->bio); ?></div></td>
    </tr>
<?php }
  }
}
else {
?>
    <tr><td colspan=6 class='heypub_no_subs'>No Submissions At This Time</td></tr>
<?php
}
?>
</tbody>
</table>

<?php
if ( $page_links )
	echo "<div class='tablenav-pages'>$page_links_text</div>";
?>

<?php
if(count($subs) > 0) {
  heypub_submission_actions('heypub-bulk-submit');
}
?>
  <div style='clear:both;'> </div>
  <h3>Bulk Actions Explained</h3>
  <div id='heypub_instructions'>
    <h4>You can perform the following bulk actions on the listed submissions:</h4>
    <table id='heypub_instructions_list'>
    <tr>
      <td>Save for Later Review</td><td>Marks the submission as under review in the HeyPublisher system, but does not copy it over into your Wordpress installation.<br/>  <i>If you do not accept simultaneous submissions, this also prevents the author from sending the work to another publisher while you are reviewing it.</i></td>
    </tr>
    <tr>
      <td>Reject Submission</td><td>Will inform the author that you do not intend to publish their work at this time and they are free to submit it to another publisher.</td>
    </tr>
    </table>
  </div>
   <br/>
</form>
</div> <!-- end of 'wrap' -->

<?php
}

/**
* Display the individual submission.  Requires a HeyPublisher submission ID
*
* If the submission body is blank, then we don't allow editors to change state
*
*/
function heypub_show_submission($id) {
  global $hp_xml, $hp_base, $hp_sub;
  // We should move this inclusion up a level at somepoint - but for right now, we just need it here.

  // Reading a submission marks it as 'read' in HeyPublisher
  if ($hp_xml->submission_action($id,'read')) {
    $sub = $hp_xml->get_submission_by_id($id);
    $hp_xml->log(sprintf("heypub_show_submission \n%s",print_r($sub,1)));
    $form_post_url = $hp_base->get_form_post_url_for_page('heypub_show_menu_submissions');
    $days_pending = ($sub->days_pending >= 60) ? 'late': (($sub->days_pending >= 30) ? 'warn' : 'ok');
    if ($sub) {
      $post_id = heypub_get_post_id_by_submission_id($id);
      
      
?>
  <div class="wrap">
  <h2>Preview: "<?php echo $sub->title; ?>"</h2>
  <table id='heypub_summary_review'>
    <tr><td id='heypub_submission'>
      <div id="hey-content">
        <h3><?php printf('%s', $sub->category); ?> by <?php printf("%s %s", $sub->author->first_name, $sub->author->last_name); ?>
          <small>(
<?php
    if (FALSE == $sub->author->email) {
      print "<i><small>No Email Provided</small></i>";
    } else {
      printf('<a href="mailto:%s?subject=Your%%20submission%%20to%%20%s" target="_blank">%s</a>',$sub->author->email,get_bloginfo('name'),$sub->author->email);
    }
?>
          )</small></h3>
<?php
			$block = '';
      // author bio
      if (FALSE != $sub->author->bio) {
        $bio = $sub->author->bio;
      } else {
        $bio = '<i>None provided</i>';
      }
      // $block .= sprintf('<b>Author Bio:</b> %s<br/>',$bio);
      $block .= sprintf('<dt>Author Bio:</dt><dd>%s</dd>',$bio);
      // submission summary
      if ($sub->description != '') {
				$block .= sprintf('<dt>Summary:</dt><dd>%s</dd>',$sub->description);
      }
      // submission word-count
      if (FALSE != $sub->word_count) {
				// getting weird errors about the type of val for word_count, so explicitly cast here
				$block .= sprintf('<dt>Word Count:</dt><dd>%s words</dd>',number_format("$sub->word_count"));
      }
      echo $hp_base->blockquote(sprintf('<dl>%s</dl>',$block));
?>
        <div id='heypub_submission_body'>
<?php
        if (in_array($sub->status,$hp_sub->disallowed_states)) {
          printf('<h4 class="error">%s</h4>',$sub->body);
        }
        else {
          printf('%s',$sub->body);
        }
?>
        </div>
      </div>
    </td>
    <td valign='top' id='heypub_submission_nav'>
      <?php heypub_display_page_logo(); ?>

<?php
/*
  // Future functionality - downloads of original docs are coming....
  if ($hp_xml->get_config_option('display_download_link')) {
    <a class='heypub_smart_button' href='<?php echo $sub->document->url; ?>' title="Download '<?php echo $sub->title; ?>'">Download Original Document</a>

// display the current status
<?php echo ucwords(str_replace('_',' ', $sub->status)); ?>
*/

?>

      <h3>Submission Status:</h3>
      <dl class='heypub-sub-status'>
<?php
        if ($post_id) {
          printf("<dt class='days_pending_warn'>Imported on</dt><dd>%s</dd>",heypub_get_post_mod_date($post_id));
        }
?>
        <dt>Submitted on</dt><dd><?php echo $sub->submission_date; ?></dd>
        <dt><?php echo $hp_xml->normalize_submission_status($sub->status); ?></dt><dd><?php echo $sub->status_date; ?></dd>
        <dt class='days_pending_<?php echo $days_pending; ?>'>Days pending</dt><dd><?php echo $sub->days_pending; ?></dd>
      </dl>

<?php  if (!in_array($sub->status,$hp_sub->disallowed_states)) { ?>
      <form id="posts-filter" action="<?php echo $form_post_url; ?>" method="post">
        <?php echo $hp_sub->editor_note_text_area($id); ?>
        <input type='hidden' name="post[]" value="<?php echo "$id"; ?>" />
        <?php heypub_submission_actions('heypub-bulk-submit',true,$sub->status,$post_id); ?>
      </form>
<?php
    if ($sub->manageable_count > 1) {
?>
  <h4>Currently with <?php echo ($sub->manageable_count - 1); ?> other <?php echo (($sub->manageable_count - 1) == 1) ? 'publisher' : 'publishers'; ?>:</h4>
<?php
  echo $hp_base->other_publisher_link($sub->manageable->publisher, $sub);
?>
  <p>You can disallow simultaneous submissions in <a href='<?php
   printf('%s/wp-admin/admin.php?page=heypub_show_menu_options#simu_subs',get_bloginfo('wpurl')); ?>'>Plugin Options</a></>
<?php
    }
?>
<?php
    if ($sub->published_count > 0) {
?>
    <h4>This work has been previously published by <?php echo ($sub->published_count); ?> other <?php echo (($sub->published_count) == 1) ? 'publisher' : 'publishers'; ?>:</h4>
<?php
    echo $hp_base->other_publisher_link($sub->published->publisher,$sub);
    }
?>

<!-- Editor Voting -->
    <br/>
<?php echo $hp_sub->editor_vote_box(); ?>

<?php  }  // end of conditional testing whether submission is in allowed state or not
 ?>

      </td>
      </tr>
    </table>

  </div>
<?php
    }  // end if $sub
  } // end submission_action
} // end function

function pluralize_submission_message($cnt) {
  if ($cnt == 1) {
    return '1 submission';
  } else {
    return sprintf('%s submissions',$cnt);
  }
}

/* 
 * Get all the submissions that have already been imported
 */
function heypub_get_accepted_post_ids() {
  global $wpdb;
  // $id is the HP post id
  $results = $wpdb->get_results($wpdb->prepare("SELECT post_id, meta_value as sub_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value is not null", HEYPUB_POST_META_KEY_SUB_ID));
  $ids = array();
  foreach ($results as $hash) {
    $sub_id = "$hash->sub_id";
    $ids[$sub_id] = $hash->post_id;
  }
  return $ids;
}

/*
 * Get the post's modified date for listing in submission status
 */
function heypub_get_post_mod_date($id) {
  $mod_date = get_post_modified_time( 'Y-m-d', false, $id);
  if ($mod_date) { return $mod_date; }
  return false;
}

/*
 * If submission has already been imported, the post ID will be stored in post meta
 */
function heypub_get_post_id_by_submission_id($id) {
  global $wpdb;
  // $id is the HP post id
  $post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", HEYPUB_POST_META_KEY_SUB_ID,$id));
  if ($post_id) { return $post_id; }
  return false;
}

// This will return the HP key if the post id is found
function heypub_get_submission_id_by_post_id($post_id) {
  global $wpdb;
  // $id is the HP post id
  $hp_id = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s AND post_id = %s", HEYPUB_POST_META_KEY_SUB_ID, $post_id));
  if ($hp_id) {
    return $hp_id;
  }
  return false;
}

// This function is called by the post-processor hook that detects when accepted works are 'trashed'
function heypub_reject_post($post_id) {
  global $hp_xml;
  if ($hp_id = heypub_get_submission_id_by_post_id($post_id)) {
    $hp_xml->submission_action($hp_id,'rejected');
  }
}

// This function is called by the post-processor hook that detects when accepted works are 'published'
function heypub_publish_post($post_id) {
  global $hp_xml;
  if ($hp_id = heypub_get_submission_id_by_post_id($post_id)) {
    $hp_xml->submission_action($hp_id,'published');
  }
  return true;
}
// Bulk Status handlers

// Rejection Handler - these posts may or may not be in the db
function heypub_reject_submission($req) {
  global $hp_xml;
  check_admin_referer('heypub-bulk-submit');
  $post = $req[post];
  $notes = $req[notes];
  $cnt = 0;
  foreach ($post as $key) {
    if ($hp_xml->submission_action($key,'rejected',$notes)) {
      $cnt++;
      // need to see if this post has been previously 'accepted'
      if ($post_id = heypub_get_post_id_by_submission_id($key)) {
        // we force deletes
        wp_delete_post( $post_id, true );
      }
    }
  }

  $message = sprintf('%s successfully rejected',pluralize_submission_message($cnt));
  return $message;
}
// Save For Later Handler - Marks these records for later review.
function heypub_consider_submission($req) {
  global $hp_xml;
  check_admin_referer('heypub-bulk-submit');
  $post = $req[post];
  $notes = $req[notes];
  $cnt = 0;
  foreach ($post as $key) {
    if ($hp_xml->submission_action($key,'under_consideration',$notes)) {
      $cnt++;
    }
  }
  $message = sprintf('%s successfully saved for later editorial review',pluralize_submission_message($cnt));
  return $message;
}

// Request a revision from the writer
function heypub_request_revision($req) {
  global $hp_xml;
  check_admin_referer('heypub-bulk-submit');
  $post = $req[post][0];
  $notes = $req[notes];
  if ($hp_xml->submission_action($post,'publisher_revision_requested',$notes)) {
    $message = "An email has been sent to the author requesting they submit a new revision of their work.";
  } else {
    $message = "Unable to send the revision request!";
  }
  return $message;
}

function heypub_create_or_update_post($user_id,$status,$sub) {
  global $hp_xml;
  $post_id = heypub_get_post_id_by_title("$sub->title",$user_id) ;
  $category = 1;  // the 'uncategorized' category
  $map = $hp_xml->get_category_mapping();
  // printf("<pre>Sub object looks like : %s</pre>",print_r($sub,1));
  $cat = sprintf("%s",$sub->category->id);
  if ($map[$cat]) {
    $category = $map[$cat]; // local id
  }
  // this piece does not exist - create it
  $post = array();
  $post['post_title'] = $sub->title;
  $post['post_content'] = $sub->body;
  $post['post_status'] = $status;
  $post['post_author'] = $user_id;
  $post['post_category'] = array($category);  # this should always be an array.
  // if the post ID was found for this title/author - then this is an update, not an insert
  // so et the post id appropriately
  if ($post_id) {
    $post['ID'] = $post_id;
  }
  $post_id = wp_insert_post( $post );
  // ensure meta data is updated
  update_post_meta($post_id, HEYPUB_POST_META_KEY_SUB_ID, "$sub->id");
  return $post_id;
}

function heypub_get_post_id_by_title($title,$user_id){
  global $wpdb;
  $post_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_author = %s",$title,$user_id));
  return $post_id;
}

// Pre-Accept Handler - Prompt the Admin to create a new user or use an existing user account.
function heypub_accept_submission($req) {
  global $hp_xml, $hp_base;
  check_admin_referer('heypub-bulk-submit');
  $id = $req[post][0];
  $sub = $hp_xml->get_submission_by_id($id);
  $notes = $req[notes];
	// do we have this author in the db?
  $user_id = $hp_base->get_author_id($sub->author);

  // LOGIC: if we already have the user, redirect to the accept processor
  // If we don't then allow user to create this user - then ensure our metadata is associated with the newly created account
  // THEN redirect to the accept processor.
  //
  $hp_xml->log(sprintf("heypub_accept_submission req = \n%s\n USER_ID: %s",print_r($req,1),$user_id));
  if (FALSE != $user_id) {
    $url = $hp_base->get_author_edit_url($user_id);
    $msg = sprintf("<br/><br/>The author <b><a href='$url'>%s</a></b> already exists in your database.  Please ensure their information is correct prior to publication.", $sub->author->full_name);
    $message = heypub_accept_process_submission($req,$user_id,$msg);
    return $message;
  }
  // If we're still here, we have to create a new user account.  Display the form...
  $form_post_url = $hp_base->get_form_post_url_for_page('heypub_show_menu_submissions');
?>
	 <div class="wrap">
 		<h2>Create New Author</h2>
	  <table id='heypub_summary_review'>
	    <tr><td id='heypub_submission'>
				<p>The author <b><?php printf('%s %s',$sub->author->first_name, $sub->author->last_name); ?></b> does not currently exist in your database.</p>
				<p>Before you can accept "<?php echo $sub->title; ?>" for publication, you need to create a user account for this author.</p>
				<p>Please indicate the desired username below.</p>
				<p><i>* If this username already exists in your database, this submission will be associated with that user.  If this username does not already exist, we will create it here.</i></p>
			  <form id="posts-filter" action="<?php echo $form_post_url; ?>" method="post">
					<input type='hidden' name="post[]" value="<?php echo "$id"; ?>" />
					<input type='hidden' name="notes" value="<?php echo "$notes"; ?>" />
					<input type='hidden' name="action" value="create_user" />
					<label for='username'>Username:</label>
					<input type='text' name="username" id='username' value="<?php echo $sub->author->email; ?>" />
				  <input type="submit" value="Create User" name="doaction" id="doaction" />
 				  <?php wp_nonce_field('heypub-bulk-submit'); ?>
	      </form>
			</td></tr>
		</table>
	</div>
<?php
}

/**
* Create a User account prior to Accepting Submission
*
* @param array $req Form POST object
*/
function heypub_create_user($req) {
  global $hp_xml,$hp_base;
  if (!$req[username]) {
   $hp_xml->error = "Oops - looks like you didn't provide a valid username";
   $hp_xml->print_webservice_errors(false);
   heypub_accept_submission($req);
   return FALSE;
  }
  $id = $req[post][0];
  $sub = $hp_xml->get_submission_by_id($id);
  $notes = $req[notes];
  // do we have this author in the db?
  $user_id = $hp_base->create_author($req[username],$sub->author);
  if (FALSE != $user_id) {
    $url = $hp_base->get_author_edit_url($user_id);
    $msg = sprintf("<br/><br/>The author <b><a href='$url'>%s</a></b> was created in your database.  Please ensure their information is correct prior to publication.", $sub->author->full_name);
    $message = heypub_accept_process_submission($req,$user_id,$msg);
    return $message;
  } else {
    $hp_xml->error = "We ran into problems creating the user account for $req[username].<br/>$hp_base->error<br/><br/>Please use a different username.";
    $hp_xml->print_webservice_errors(false);
    heypub_accept_submission($req);
    return FALSE;
  }
}

// Accept Processing Handler - these posts may or may not be in the db already
function heypub_accept_process_submission($req,$uid,$msg=FALSE) {
  global $hp_xml, $hp_base;
  check_admin_referer('heypub-bulk-submit');
  $id = $req[post][0];
  $notes = $req[notes];
  $hp_xml->log(sprintf("heypub_accept_process_submission req = \n%s\n USER_ID: %s\nMSG: %s",print_r($req,1),$uid,$msg));

  if ($hp_xml->submission_action($id,'accepted',$notes)) {
    $hp_xml->log("WE are in the UPDATE/CREATE");
    $sub = $hp_xml->get_submission_by_id($id);
    $post_id = heypub_create_or_update_post($uid,'pending',$sub);
    $hp_xml->log(sprintf("POST ID = %s",$post_id));
  }

  $message = sprintf("%s successfully accepted.<br/><br/>%s been moved to your Posts and put in a 'Pending' status. .",pluralize_submission_message(1),
  ($cnt > 1) ? "These works have" : "This work has" );

  if ($msg) {
    $message .= $msg;
  }
  return $message;
}

// Handle operations for this form
function heypub_submission_handler() {
  global $hp_xml, $hp_sub;
  $ds = DIRECTORY_SEPARATOR;
  require_once(HEYPUB_PLUGIN_FULLPATH.'include'.$ds.'HeyPublisher'.$ds.'HeyPublisherSubmission.class.php');
  $hp_sub = new HeyPublisherSubmission;

  $message = "";

  // printf("<pre>request = %s</pre>",print_r($_REQUEST,1));
  if (!$hp_xml->is_validated) {
    heypub_not_authenticated();
    return;
  }
  if (isset($_REQUEST[show])) {
    heypub_show_submission($_REQUEST[show]);
    return;
  }
  elseif (isset($_REQUEST[action]) && ($_REQUEST[action] == 'reject')) {
    $message = heypub_reject_submission($_REQUEST);
  }
  elseif (isset($_REQUEST[action]) && ($_REQUEST[action] == 'review')) {
    $message = heypub_consider_submission($_REQUEST);
  }
  elseif (isset($_REQUEST[action]) && ($_REQUEST[action] == 'accept')) {
    $message = heypub_accept_submission($_REQUEST);
    if (!$message) { return; }
    // We exit if we don't have a message, that means we had to prompt for user.
  }
  elseif (isset($_REQUEST[action]) && ($_REQUEST[action] == 'create_user')) {
    $message = heypub_create_user($_REQUEST);
    if (!$message) { return; }
  }
  elseif (isset($_REQUEST[action]) && ($_REQUEST[action] == 'request_revision')) {
    $message = heypub_request_revision($_REQUEST);
  }
  elseif (isset($_REQUEST[action])) {
    $hp_xml->error = "Oops - looks like you didn't select an action from the dropdown.";
    if ($_REQUEST[notes] != '') {
      $hp_xml->error .= '<br/>Your note to the author was not sent.';
    }
    $hp_xml->print_webservice_errors(false);
  }

  if(!empty($message)) { ?>
    <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
<?php
  }
  heypub_list_submissions();
}

?>
