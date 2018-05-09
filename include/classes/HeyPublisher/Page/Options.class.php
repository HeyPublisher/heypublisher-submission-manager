<?php
namespace HeyPublisher\Page;

// TODO: Research metaboxes : http://www.themoyles.co.uk/2013/03/using-meta-boxes-on-plugin-admin-pages/
// https://shellcreeper.com/wp-settings-meta-box/
// useful info but not recent: http://www.onextrapixel.com/2009/07/01/how-to-design-and-style-your-wordpress-plugin-admin-panel/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

/**
 * HeyPublisher class for handling options updates and editing page
 *
 */

// Load the class files and associated scoped functionality
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Page.class.php');
class Options extends \HeyPublisher\Page {
  var $domain = '';

  public function __construct() {
  	parent::__construct();
    $this->slug .= '_options';
  }

  public function __destruct() {
  	parent::__destruct();
  }

  // TODO: need a better way of doing this :(
  public function options() {
    //   Possibly process form post
    $message = $this->process_options();
    if ($message) {
      printf('<div id="message" class="updated fade"><p>%s</p></div>',$message);
    }
    $this->page_prep();
  }

  public function page_prep()  {
    parent::page('Plugin Options', '', array($this,'content'));
  }
  // TODO: Replace calls to this to get_form_url_for_page()
  private function form_action() {
    // $action = $this->get_form_url_for_page();
    $action = sprintf('admin.php?page=%s',$this->slug);
    return $action;
  }

  protected function content() {
  	global $wpdb,$wp_roles,$hp_base;
    $nonce = wp_nonce_field('heypub-save-options');
    if (!$this->xml->is_validated) {
      $content = $this->not_validated_form();
      $button = "Create Account";
    } else {
      $content = $this->validated_form();
      $button = 'Update';
    }
    $action = $this->form_action();
    $html = <<<EOF
      <form method="post" action="{$action}">
        {$nonce}
        {$content}
        <input type="hidden" name="save_settings" value="0" />
        <input type="submit" class="heypub-button button-primary" name="save_button" id="save_button" value="{$button} &raquo;" />
      </form>
EOF;
    return $html;
  }

  private function get_years_for_select($current) {
    $cy = $this->strip($current);
    $start = date('Y');
    $end  =date('Y', strtotime('-90 year'));
    $opts = '';
    for($start; $start >= $end; $start--) {

      $sel = '';
      if ($start == $cy) { $sel = ' selected="selected"'; }
      $opts .= sprintf('<option value="%s" %s>%s</option>',$start, $sel, $start);
    }
    return $opts;
  }

  private function not_validated_form() {
    $opts = $this->xml->config;
    // $this->log(sprintf("not_validated_form opts: %s",print_r($opts,1)));
    $searchable = sprintf('%s',$this->xml->searchable($opts['name']));

    $html = <<<EOF
      <h2>HeyPublisher Account Info</h2>
      <p>If your publication is <a href="{$this->domain}/publishers/search/all/none/{$searchable}" target=_new> listed in HeyPublisher's database</a>, please enter your publication's name and URL <i>exactly</i> as it appears on HeyPublisher.</p>
      <p>If your publication is not already in our database, tell us the name and URL and we will add it.  The defaults listed below are based upon your current WordPress settings.</p>
      <p><b>IMPORTANT:</b> The email address and password you use below will be used to create an 'administrator' account in our system so you can better manage the plugin.</p>

      <ul>
        <li>
          <label class='heypub' for='hp_name'>Publication Name</label>
          <input type="text" name="hp_user[name]" id="hp_name" class='heypub' value="{$opts['name']}" />
        </li>
        <li>
          <label class='heypub' for='hp_url'>Publication URL</label>
          <input type="text" name="hp_user[url]" id="hp_url" class='heypub' value="{$opts['url']}" />
        </li>
        <li>
          <label class='heypub' for='hp_username'>Your Email Address</label>
          <input type="text" name="hp_user[username]" id="hp_username" class='heypub' value="{$opts['editor_email']}"/>
        </li>
        <li>
          <label class='heypub' for='hp_password'>Password</label>
          <input type="password" name="hp_user[password]" id="hp_password" class='heypub' autocomplete="off"
          />
        </li>
      </ul>
EOF;
    return $html;
  }

  private function publication_block($opts) {

    $this->log(sprintf("opts in publication_block: %s",print_r($opts,1)));
    $name = htmlentities(stripslashes($opts['name']));

    $years = $this->get_years_for_select($opts['established']);

    $html = <<<EOF
    <!-- Publication Block -->
    <h3 class='first'>Publication Information</h3>
    <!--p>Tell us more about your publication.</p-->
    <ul>
      <li>
        <label class='heypub' for='hp_type'>Publication Type</label>
        <select name="heypub_opt[pub_type]" id="hp_type">
        {$this->publication_types()}
        </select>
      </li>
      <li>
        <label class='heypub' for='hp_name'>Publication Name</label>
        <input type="text" name="heypub_opt[name]" id="hp_name" value="{$name}" class='heypub'/>
      </li>
      <li>
        <label class='heypub' for='hp_url'>Publication URL</label>
        <input type="text" name="heypub_opt[url]" id="hp_url" value="{$this->strip($opts['url'])}" class='heypub'/>
      </li>
      <li>
        <label class='heypub' for='hp_issn'>ISSN</label>
        <input type="text" name="heypub_opt[issn]" id="hp_issn" value="{$this->strip($opts['issn'])}" class='heypub'/>
      </li>
      <li>
        <label class='heypub' for='hp_established'>Year Established</label>
        <select name="heypub_opt[established]" id="hp_established" class='heypub'>
        {$years}
        </select>
      </li>
      <li>
        <label class='heypub' for='hp_circulation'>Monthly Circulation (Visitors)</label>
        <input type="text" name="heypub_opt[circulation]" id="hp_circulation" class='heypub' value="{$this->strip($opts['circulation'])}" /> (000's)
      </li>
    </ul>
EOF;
    // '

    return $html;
  }

  private function social_media($opts) {
    $html = <<<EOF
      <!-- Social Block -->
      <h3>Social Media Information</h3>
      <!--p>Tell us how to find your Facebook Fan Page and follow your tweets on Twitter.</p-->
      <ul>
        <li>
          <label class='heypub' for='hp_facebook'>Facebook Fan Page URL</label>
          <input type="text" name="heypub_opt[facebook]" id="hp_facebook" class='heypub' value="{$this->strip($opts['facebook'])}" />
        </li>
        <li>
          <label class='heypub' for='hp_twitter'>Twitter ID @</label>
          <input type="text" name="heypub_opt[twitter]" id="hp_twitter" class='heypub' value="{$this->strip($opts['twitter'])}" />
        </li>
      </ul>
EOF;
    return $html;
  }

  private function contact_information($opts){
    require_once(HEYPUB_PLUGIN_FULLPATH.'/include/country_list.php');

    $options = '';

    foreach ($countries as $key=>$val) {
      $sel = '';
      if ($key == $opts['country'] || $val == $opts['country']) { // we have some old stle country entries.
        $sel = "selected='selected'";
      }
      $options .= "<option value='$key' $sel>$val</option>";
    }

    $html = <<<EOF
    <h3>Contact Information</h3>
    <ul>
      <li>
        <label class='heypub' for='hp_editor_name'>Editor Name</label>
        <input type="text" name="heypub_opt[editor_name]" id="hp_editor_name" class='heypub' value="{$this->strip($opts['editor_name'])}" />
      </li>
      <li>
        <label class='heypub' for='hp_editor_email'>Email Address</label>
        <input type="text" name="heypub_opt[editor_email]" id="hp_editor_email" class='heypub' value="{$this->strip($opts['editor_email'])}" />
      </li>
      <li>
        <label class='heypub' for='hp_address'>Street Address</label>
        <input type="text" name="heypub_opt[address]" id="hp_address" class='heypub' value="{$this->strip($opts['address'])}" />
      </li>
      <li>
        <label class='heypub' for='hp_city'>City</label>
        <input type="text" name="heypub_opt[city]" id="hp_city" class='heypub' value="{$this->strip($opts['city'])}" />
      </li>
      <li>
        <label class='heypub' for='hp_state'>State/Region</label>
        <input type="text" name="heypub_opt[state]" id="hp_state" class='heypub' value="{$this->strip($opts['state'])}" />
      </li>
      <li>
        <label class='heypub' for='hp_zipcode'>Zip Code</label>
        <input type="text" name="heypub_opt[zipcode]" id="hp_zipcode" class='heypub' value="{$this->strip($opts['zipcode'])}" />
      </li>
      <li>
        <label class='heypub' for='hp_country'>Country</label>
        <select name="heypub_opt[country]" id="hp_country" class="heypub">
          {$options}
        </select>
      </li>
    </ul>
EOF;
    // '
    return $html;
  }

  private function submission_guidelines($opts){
    $pages = get_pages();
    $select = '';
    foreach ($pages as $p) {
      $select .= sprintf('<option value="%s" %s>%s</option>', $p->ID, ($p->ID == $opts['sub_guide_id']) ? 'selected=selected' : null, $p->post_title);
    }
    $html = <<<EOF
    <h3>Submission Guidelines</h3>
    <p>Tell us where to find your submission guidelines.</p>
    <ul>
      <li>
        <label class='heypub' for='hp_sub_guide'>Submission Guidelines Page</label>
        <select name="heypub_opt[sub_guide_id]" id="hp_sub_guide" class='heypub'>
          <option value="">-- NONE --</option>
          {$select}
        </select>
      </li>
    </ul>
EOF;
    return $html;
  }

  private function submission_page($opts){
    $replacer = HEYPUB_SUBMISSION_PAGE_REPLACER;
    // TODO: Replace this will call to $this->get_form_url_for_page('create_form_page')
    $link_url = sprintf('%s&action=create_form_page',$this->form_action());
    if(function_exists('wp_nonce_url')){
      $link_url = wp_nonce_url($link_url,'create_form');
    }
    $html = "<h3>Submission Form</h3>";
    $nopage = 'style="display:none;"';
    $yespage = '';
    if (!$opts['sub_page_id']) {
      $yespage = 'style="display:none;"';
      $nopage = '';
    }
    $html .= <<<EOF
      <p {$nopage} id='heypub-no-guidelines'>
        Select the page that contains your submission form.
        If you have not yet created this page, don't worry.
        <br/>
        Just <a href="{$link_url}">CLICK HERE &raquo; </a> to create the page now.
        You can change the content and title of this page at any time.
      </p>
      <p {$yespage} id='heypub-yes-guidelines'>This is the Page where the submission form will be displayed.</p>
EOF;
    $select = '';
    $pages = get_pages();
    foreach ($pages as $p) {
      $select .= sprintf('<option value="%s" %s>%s</option>', $p->ID, ($p->ID == $opts['sub_page_id']) ? 'selected=selected' : null, $p->post_title);
    }
    $html .= <<<EOF
      <ul>
        <li>
          <label class='heypub' for='hp_submission_page'>Submission Form Page</label>
          <select name="heypub_opt[sub_page_id]" id="hp_submission_page" class='heypub' onchange='HeyPublisher.toggleGuidelines(this)'>
            <option value="">-- Select --</option>
            {$select}
          </select>
        </li>
      </ul>
      <p class='heypub-subtext'>Ensure that the following shortcode is contained somewhere within this page.</p>
      <blockquote class='heypub'><b>{$replacer}</b></blockquote>
      <p class='heypub-subtext'>This code will be replaced by the actual submission form when writers visit this page.</p>
EOF;
    return $html;
  }

  private function submission_criteria($opts) {
    $html = <<<EOF
      <h3>Submission Criteria</h3>
      <!--p>
        What are the types of work you accept from writers? Do you accept simultaneous submissions?
        Do you accept multiple submissions?
      </p-->
      <!-- Genres -->
      <ul>
        <li>
          <label class='heypub' for='hp_accepting_subs'>Currently Accepting Submissions?</label>
          <select name="heypub_opt[accepting_subs]" id="hp_accepting_subs" onchange="HeyPublisher.selectToggle(this,'#heypub_show_genres_list');">
            {$this->boolean_options('accepting_subs',$opts)}
          </select>
        </li>
      </ul>
EOF;
    $hidden = ($opts['accepting_subs']) ? null : "style='display:none;' ";
    $html .= <<<EOF
      <div id='heypub_show_genres_list' {$hidden}>
        <!-- Content Specific for the Genres -->
        <h3>Select all categories of work your publication accepts.</h3>
        {$this->genre_map()}
        <br/>
      </div>

EOF;
    $html .= <<<EOF
      <ul>
        <li>
          {$this->boolean_select('Simultaneous Submissions?','simu_subs',$opts)}
        </li>
        <li>
          {$this->boolean_select('Multiple Submissions?','multi_subs',$opts)}
        </li>
        <li>
          {$this->boolean_select('Accept Reprints?','reprint_subs',$opts)}
        </li>
      </ul>
EOF;
    return $html;
  }

  private function genre_map() {
    $cols = 2; // colums for mapping table
    $cats = $this->xml->get_my_categories_as_hash();
    if (empty($cats)) { return ''; }
    $header = '';
    for ($x=0;$x<$cols;$x++) {
      $header .= "<th>Genre</th><th>Your Category</th>";
    }
    $cnt = 0;
    $count = 1;
    // printf("<pre>Cats: %s</pre>",print_r($cats,1));
    $mapping = '';
    foreach ($cats as $id=>$hash) {
      $count++;
      $class = null;
      if(($count%($cols*2)) != 0) { $class = 'alternate';}
      if ($cnt % $cols == 0) {
        $cnt = 0;
        $mapping .= sprintf("</tr><tr class='%s'>",$class);
      }
      $mapping .= sprintf('
        <td>%s &nbsp; <input id="cat_%s"type="checkbox" name="heypub_opt[genres_list][]" value="%s" %s onclick="HeyPublisher.clickCheck(this,\'chk_%s\');"/></td>
        <td>%s</td>',
          $hash['name'],$hash['id'],$hash['id'],($hash['has']) ? "checked=checked" : null,$hash['id'],$this->heypub_get_category_mapping($hash['id'],$hash['has'])
      );
      $cnt ++;
    }
    // fill in the blank spaces
    if ($cnt < $cols) {
      for ($x=($cols-$cnt);$x<$cols;$x++) {
        $mapping .= "<td>&nbsp;</td><td>&nbsp;</td>";
      }
    }
    $html = <<<EOF
      <table id='heypub_category_list' cellspacing='0' border='0' cellpadding='0'>
        <thead>
          <tr>
            {$header}
          </tr>
        </thead>
        <tfoot/>
        <tbody>
          <tr>
            {$mapping}
          </tr>
        </tbody>
      </table>
EOF;
    return $html;
  }


  private function writer_notifications($opts) {
    $sub_states = sprintf('%s/about/submission_states',$this->domain);
    $html = <<<EOF
      <h3>Writer Notifications</h3>
      <p>
        Automatically send an email to the author when their submission transitions into any one of the
        <a href='{$sub_states}' target='_blank'>states in the submission cycle</a>.
      </p>
      <p>
        Disable sending of email by setting the value to <code>No</code> below.</p>
      <p>
        Customize the emails sent to the author through the
        <a href='admin.php?page=heypublisher_email' target=_top>Email Templates</a>
        screen.
      </p>
      <input type='hidden' name='notify_submitted' value='1'>
      <ul>
        <li>
          {$this->boolean_select('Read?','notify_read',$opts,'Sent when the submission is first read by an Editor.')}
        </li>
        <li>
          {$this->boolean_select('Under Review?','notify_under_consideration',$opts,'Sent when the submission is being held for consideration.')}
        </li>
        <li>
          {$this->boolean_select('Accepted?','notify_accepted',$opts,'Sent if the submission is Accepted for publication.')}
        </li>
        <li>
          {$this->boolean_select('Rejected?','notify_rejected',$opts,'Sent if the submission is Rejected by an Editor.')}
        </li>
        <li>
          {$this->boolean_select('Published?','notify_published',$opts,'Sent when a submission is Published, or on the actual publication date if Scheduled.')}
        </li>
        <li>
          {$this->boolean_select('Withdrawn?','notify_withdrawn',$opts,'Confirmation sent when a submission is marked as Withdrawn.')}
        </li>
      </ul>
EOF;
    return $html;
  }

  private function experimental_options($opts) {
    $hidden = ($opts['mailchimp_active']) ? null : "style='display:none;' ";
    $html = <<<EOF
      <!-- MailChimp -->
      <h3>MailChimp Mailing List Subscriptions</h3>
      <p>
        If you use <a href='https://mailchimp.com/' target='_blank'>MailChimp</a> to manage your mailing list, setting to <code>Yes</code> will prompt new writers to subscribe to your mailing list when they submit their work.
      </p>
      <ul>
        <li>
          <label class='heypub' for='hp_mailchimp_active'>Prompt to Subscribe?</label>
          <select name="heypub_opt[mailchimp_active]" id="hp_mailchimp_active" onchange="HeyPublisher.selectToggle(this,'#heypub_show_mailchimp_list');">
            {$this->boolean_options('mailchimp_active',$opts)}
          </select>
        </li>
      </ul>
      <div id='heypub_show_mailchimp_list' {$hidden}>
        <!-- Content Specific for the MailChimp Config -->
        <p>Read more on how to <a href='http://kb.mailchimp.com/integrations/api-integrations/about-api-keys' target='_blank'>Find or Generate Your API Key</a> and how to <a href='http://kb.mailchimp.com/lists/manage-contacts/find-your-list-id' target="_blank">Find Your List ID</a> before continuing.
        </p>
        <ul>
          <li>
            <label class='heypub' for='hp_mailchimp_apikey'>API Key</label>
            <input type="text" name="heypub_opt[mailchimp_api_key]" id="hp_mailchimp_apikey" class='heypub' value="{$this->strip($opts['mailchimp_api_key'])}" />
          </li>
          <li>
            <label class='heypub' for='hp_mailchimp_listid'>List ID</label>
            <input type="text" name="heypub_opt[mailchimp_list_id]" id="hp_mailchimp_listid" class='heypub' value="{$this->strip($opts['mailchimp_list_id'])}" />
          </li>
        </ul>
      </div>
      <!-- Experimental Options -->
      <h3>Disable HTML Clean-Up</h3>
      <p>
        Set to <code>YES</code> if you accept submissions from writers that use a different character set than your own.  Otherwise leave set to <code>No</code>.
      </p>
      <ul>
        <li>
          {$this->boolean_select('Turn Off HTML Clean-Up?','turn_off_tidy',$opts)}
        </li>
      </ul>
EOF;
    return $html;
  }

  private function validated_form() {
    $opts = $this->xml->config;
    // $this->log(sprintf("Options::validated_form() $opts = %s",print_r($opts,1)));
    // $this->log(" => dislaying Options page");
    $html = <<<EOF
      <input type="hidden" name="heypub_opt[isvalidated]" value="1" />
      <input type="hidden" name="save_settings" value="0" />
      {$this->publication_block($opts)}
      {$this->contact_information($opts)}
      {$this->social_media($opts)}
      {$this->submission_guidelines($opts)}
      {$this->submission_page($opts)}
      {$this->submission_criteria($opts)}
      {$this->writer_notifications($opts)}
      {$this->experimental_options($opts)}

EOF;

    return $html;
  }

  /*
   * Process the form post options, if present
   */
  function process_options() {
    // $this->log(sprintf("POST = %s",print_r($_POST,1)));
    $message = null; // default is null message
    if(isset($_REQUEST['save_settings']) && check_admin_referer('heypub-save-options')) {
      $this->log("we passed conditional");
      if (isset($_POST['hp_user'])) {
        $message = $this->validate_user($_POST);
      }
      elseif (isset($_POST['heypub_opt']) && $_POST['heypub_opt']['isvalidated'] == '1') {
        $message = $this->update_options($_POST);
      }
    }
    elseif(isset($_REQUEST['action']) && ($_REQUEST['action'] == 'create_form_page')) {
      $this->log('creating the POST form!');
       check_admin_referer('create_form');
       $page_id = $this->heypub_create_submission_page();
       // Ensure this id is saved to db
       $this->xml->set_config_option('sub_page_id',$page_id);
       $message = sprintf("A Submission Form page has been created for you. <a href='%s' target='_blank'>View page &raquo;</a><br/>",get_permalink($page_id));
    }
    return $message;
  }

  // process form post and validate user
  private function validate_user($post) {
    $message = null;
    $user = $post['hp_user'];
    // store the username and password they provided
    $this->xml->set_config_option('name',$user['name']);
    $this->xml->set_config_option('url',$user['url']);
    // Call out to the the webservice to validate
    if ($this->xml->authenticate($user)) {
      $this->xml->set_install_option('is_validated',date('Y-m-d'));
      $this->xml->set_install_option('user_oid',$this->xml->user_oid);
      $this->xml->set_install_option('publisher_oid',$this->xml->pub_oid);
      $this->xml->set_is_validated();  // ensures that this page load has correct value

      // Fetch Publisher INFO from Webservice and pre-populate the layout, if we can
      $pub = $this->xml->get_publisher_info();
      $message = 'Account validation succeeded!<br/>You can now configure your account.';
      if ($pub) {
        $cats = $this->xml->get_my_categories_as_hash();
        $has_genres = '0';
        foreach ($cats as $id=>$hash) {
          if ($hash['has']) { $has_genres = '1'; }
        }

        $message .= "<br/><br/>To help you get started we've pre-populated the form with information we already have.";
        $this->xml->set_config_option_bulk($pub);
        // now only the boolean overrides
        $this->xml->set_config_option('accepting_subs',$has_genres);

        // need to hack this for now
        if (!$pub['paying_market'] == '0') {
          $this->xml->set_config_option('paying_market_range',null);
        } // end is paying market
      } // end has publisher info
    }  // end successful auth
    return $message;
  }

  // After form POST - sync all options into local WP database as well as push
  // to the remote server.  This keeps the two databases in sync
  private function update_options($post) {
    // $this->log(sprintf("IN update_options(): $post = %s",print_r($post,1)));
    $message = null;
    // Processing a form post of Option Updates
    // Get options from the post
    $opts = $post['heypub_opt'];
    //  Bulk update the form post
    $this->xml->set_config_option_bulk($opts);
    // update the category mapping
    $cats = $this->set_category_mapping($opts);
    $this->xml->set_config_option('categories',$cats);
    if ($cats) {
      $this->xml->set_config_option('accepting_subs','1');
    }

    if (!$opts['paying_market']) {
      $this->xml->set_config_option('paying_market_range',null);
    }

    // get the URL for the sub guidelines
    $opts['guide'] = get_permalink($opts['sub_guide_id']);
    // get the URL for the sub form itself
    $opts['submission_url'] = get_permalink($opts['sub_page_id']);
    // Blog's RSS feed is:
    $opts['rss'] = get_bloginfo('rss2_url');
    // now attempt to sync with HeyPublisher.com
    $success = $this->xml->update_publisher($opts);
    // fetch the info back because we want to store seo_url and other stats locally.
    $this->xml->sync_publisher_info();
    if ($success) {
      $message = 'Your changes have been saved and syncronized with HeyPublisher.';
    }
    return $message;
  }
  // map the internal categories to HeyPub categories
  private function set_category_mapping($post) {
    $result = array();
    if ($post['accepting_subs'] && $post['genres_list']) {
      $map = $post['category_map'];
      $genres = $post['genres_list'];
      foreach ($genres as $x) {
        if ($map["$x"]) {
          $result["$x"] = $map["$x"];
        }
      }
    }
    return $result;
  }
  private function publication_types() {
    $pub_types = $this->xml->get_my_publisher_types_as_hash();
    $html = '';
    if (empty($pub_types)) { return $html; }
    foreach ($pub_types as $id=>$hash){
      $html .= sprintf('<option value="%s" %s>%s</option>',$hash['id'],($hash['has']) ? "selected=selected" : null, $hash['name']);
    }
    return $html;
  }
  private function heypub_get_category_mapping($id,$show) {
    // global $hp_base;
    // $id is the remote category id from HP
    // All categories for this install:
    // $categories =  $hp_base->get_categories();
    $map = $this->xml->get_category_mapping();
    $list = wp_dropdown_categories(
      array(
        'selected' => ($map["$id"]) ? $map["$id"] : 0,
        'id' => "chk_$id",
        'hide_empty' => 0,
        'name' => "heypub_opt[category_map][$id]",
        'orderby' => 'name',
        'hierarchical' => true,
        'echo' => 0,
        'show_option_none' => __('--- Select ---')
        )
      );
    $ret = sprintf('<div id="chk_%s" %s>%s</div>', $id, ($show) ? null : 'style="display:none;"', $list);
    return $ret;
  }
  /**
  * Create the 'Page' in Wordpress for displaying the HeyPublisher submission form
  */
  private function heypub_create_submission_page() {
    global $current_user;

    $title = HEYPUB_SUBMISSION_PAGE_TITLE;
    $content = HEYPUB_SUBMISSION_PAGE_REPLACER;

    // Create the page
    $post = array (
      "post_content"   => $content,
      "post_title"     => $title,
      "post_author"    => $current_user->ID,
      "post_status"    => 'publish',
      "post_type"      => "page"
    );
    $post_ID = wp_insert_post($post);
    $this->log(sprintf("the POST_ID is %s",$post_ID));
    return $post_ID;
  }

  public function help_menu() {
    $screen = get_current_screen();
    $screen->add_help_tab(
      array(
        'id'	    => $this->slug .= '_help_pub_info',
        'title'	  => __('Publication Information'),
        'content' => $this->help_pub_info()
      )
    );
    $screen->add_help_tab(
      array(
        'id'	    => $this->slug .= '_help_contact_info',
        'title'	  => __('Contact Information'),
        'content' => $this->help_contact_info()
      )
    );
  }
  public function help_pub_info() {
    $html = <<<EOF
    <h2>
      Publication Info
    </h2>
    <p>
      Coming Soon.
    </p>
EOF;
    return $html;
  }
  public function help_contact_info() {
    $html = <<<EOF
    <h2>
      Contact Info
    </h2>
    <p>
      Coming Soon.
    </p>
EOF;
    return $html;
  }

}
?>
