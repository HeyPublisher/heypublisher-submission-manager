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
* @param bool $bool If TRUE will display the 'request author revision' and 'accepted' values.  FALSE by default.
*/ 
function heypub_submission_actions($nounce,$bool=false) {
  global $hp_base;
?>  
  <div class="actions">
  <select name="action">
    <option value="-1" selected="selected">-- Select Action --</option>
<?php
  if ($bool) {
?>
  	<option value="accept">Accept for Publication</option>
    <option value="request_revision">Request Author Revision</option>
<?php
  }
?>
    <option value="review">Save for Later Review</option>
    <option value="reject">Reject Submission</option>
    
<?php   //  !!!    request_revision ?>


  </select>
  <br/>
  <input type="submit" value="Update Submission" name="doaction" id="doaction" />
  <?php wp_nonce_field($nounce); ?>
<?php
  if ($bool) {
    printf('<div id="return_to_summary">%s</div>',$hp_base->submission_summary_link('Return to Submissions List'));
  }
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


function heypub_list_submissions() {  
  global $hp_xml, $hp_base;
  // This is a SimpleXML object being returned, with key the sub-id
  $subs = $hp_xml->get_recent_submissions();
  $form_post_url = sprintf('%s/%s',get_bloginfo('wpurl'),'wp-admin/admin.php?page=heypub_show_menu_submissions');
  $cats = $hp_xml->get_my_categories_as_hash();
  
?>
<div class="wrap">
  <h2>Submissions</h2>
  <div id='heypub_header'>
    <?php heypub_display_page_logo(); ?>
    <div id="heypub_content">
      <p>Below are the most recent submissions sent to <b><i><?php bloginfo('name'); ?></i></b> by HeyPublisher writers.</p>
      <p>To read the submission, click on the title.  This will open the submission in a new window.</p>
      <p>To view the author's bio, click on the author's name.  The bio will display immediately below.  Click again on the author's name to hide their bio.</p>
      <p>If you are unable to see the author's bio it means the author did not provide one when submitting their work.</p>
    </div>
  </div>
<form id="posts-filter" action="<?php echo $form_post_url; ?>" method="post">
<table class="widefat post fixed" cellspacing="0" id='heypub_submissions'>
<thead>
	<tr>
  	<th style='width:4%;' id='heypub_sub_cb' class='checkbox'><input type="checkbox" onclick="heypub_auto_check(this,'posts-filter');"/></th>
  	<th style='width:25%;'>Title</th>
  	<th style='width:8%;'>Genre</th>
  	<th style='width:18%;'>Author</th>
  	<th style='width:15%;'>Email</th>
  	<th style='width:9%;'>Submitted</th>
  	<th style='width:5%;'>Words</th>
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
    if ("$hash->status" == 'accepted') {
      // link to the editor screen
      $post_id = heypub_get_post_id_by_submission_id("$x");
      $url = sprintf('%s/wp-admin/post.php?action=edit&post=%s',get_bloginfo('wpurl'),$post_id);
    }
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
        return false;" title="View Author Bio"><?php printf("%s <img src='%s/images/add.png' class='heypub_bio_expand'>", $hash->author->full_name,HEY_BASE_URL); ?></a></span>
        <span id="hide_bio_<?php echo "$x"; ?>" style="display:none;"><a href="#" onclick="
        $('post_bio_<?php echo "$x"; ?>').hide();
        $('show_bio_<?php echo "$x"; ?>').show();
        $('hide_bio_<?php echo "$x"; ?>').hide();
        return false;" title="Hide Author Bio"><?php printf("%s <img src='%s/images/minus.png' class='heypub_bio_expand'>", $hash->author->full_name,HEY_BASE_URL); ?></a></span>
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
      
      <td nowrap><?php printf("%s", $hp_xml->normalize_submission_status($hash->status)); ?></td>
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
*/
function heypub_show_submission($id) {
  global $hp_xml, $hp_base, $hp_sub;
  // We should move this inclusion up a level at somepoint - but for right now, we just need it here.
      
  // Reading a submission marks it as 'read' in HeyPublisher
  if ($hp_xml->submission_action($id,'read')) {
    $sub = $hp_xml->get_submission_by_id($id);
    $form_post_url = $hp_base->get_form_post_url_for_page('heypub_show_menu_submissions');
    $days_pending = ($sub->days_pending >= 60) ? 'late': (($sub->days_pending >= 30) ? 'warn' : 'ok');
    // printf("<pre>Sub = \n%s</pre>",print_r($sub,1));
?>    
  <div class="wrap">
  <h2>Preview: <?php echo $sub->title; ?></h2>
  <table id='heypub_summary_review'>
    <tr><td id='heypub_submission'>
      <div id="hey-content">
        <h3><?php printf('%s', $sub->category); ?> by <?php printf("%s %s", $sub->author->first_name, $sub->author->last_name); ?> 
          <small>(
<?php 
    if (FALSE == $sub->author->email) {
      print "<i><small>No Email Provided</small></i>";
    } else { 
      printf('<a href="mailto:%s?subject=Your%%20submission%%20to%%20%s">%s</a>',$sub->author->email,get_bloginfo('name'),$sub->author->email);
    } 
?>
          )</small></h3>
<?php
			$block = '';
      if ($sub->description != '') {
				$block .= sprintf('<b>Summary:</b> %s<br/>',$sub->description);
      } 
      if (FALSE != $sub->word_count) {
				// getting weird errors about the type of val for word_count, so explicitly cast here
				$block .= sprintf('<b>Word Count:</b> %s words<br/>',number_format("$sub->word_count"));
      } 
			if ($block != '') {
        echo $hp_base->blockquote($block);
			}
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
<?php
        if (FALSE != $sub->author->bio) {
          $bio = $sub->author->bio;
        } else {
          $bio = 'None provided';
        }
        echo $hp_base->blockquote(sprintf('<b>Author Bio:</b> %s',$sub->author->bio));
?>        
      </div>
    </td>
    <td valign='top' id='heypub_submission_nav'>
      <?php heypub_display_page_logo(); ?>

<?php 
/*
  // Future functionality - downloads of original docs are coming....
  if ($hp_xml->get_config_option('display_download_link')) { 
    <a class='heypub_smart_button' href='<?php echo $sub->document->url; ?>' title="Download '<?php echo $sub->title; ?>'">Download Original Document</a>
*/
?>

      <h3>Submission Status:</h3>
      <p>
        <small>
					<b><?php echo ucwords(str_replace('_',' ', $sub->status)); ?> : <?php echo $sub->status_date; ?></b><br/>
          Submitted on: <?php echo $sub->submission_date; ?><br/>
          <span class='days_pending_<?php echo $days_pending; ?>'>Days pending:  <?php echo $sub->days_pending; ?></span>
        </small>
      </p>
<?php  if (!in_array($sub->status,$hp_sub->disallowed_states)) { ?>
      <form id="posts-filter" action="<?php echo $form_post_url; ?>" method="post">
        <?php echo $hp_sub->editor_note_text_area($id); ?>
        <input type='hidden' name="post[]" value="<?php echo "$id"; ?>" />
        <?php heypub_submission_actions('heypub-bulk-submit',1,1); ?>
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
  }
}

function pluralize_submission_message($cnt) {
  if ($cnt == 1) {
    return '1 submission';
  } else {
    return sprintf('%s submissions',$cnt);
  }
}

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
  if (!$post_id) {
    $post = array();
    $post['post_title'] = $sub->title;
    $post['post_content'] = $sub->body;
    $post['post_status'] = $status;
    $post['post_author'] = $user_id;
    $post['post_category'] = array($category);  # this should always be an array.
    // printf("<pre>POST category  : %s</pre>",print_r($post[post_category],1));
    // Insert the post into the database
    $post_id = wp_insert_post( $post );
  }
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
  // If we don;t, allow user to create this user - then ensure our metadata is associated with the newly created account
  // THNE redirect to the accept processor.
  // 

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
  if ($hp_xml->submission_action($id,'accepted',$notes)) {
    $sub = $hp_xml->get_submission_by_id($id);
    $post_id = heypub_create_or_update_post($uid,'pending',$sub);
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
