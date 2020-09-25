<?php
namespace HeyPublisher\Page;

// TODO: Research metaboxes : https://www.themoyles.co.uk/2013/03/using-meta-boxes-on-plugin-admin-pages/
// https://shellcreeper.com/wp-settings-meta-box/
// useful info but not recent: https://www.onextrapixel.com/2009/07/01/how-to-design-and-style-your-wordpress-plugin-admin-panel/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

/**
 * HeyPublisher class for handling options updates and editing page
 *
 */

// Load the class files and associated scoped functionality
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Page.class.php');
require_once(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/API/Publisher.class.php');

class Options extends \HeyPublisher\Page {
  var $domain = null;
  var $pubapi = null;
  var $page = '_options';


  public function __construct() {
  	parent::__construct();
    $this->domain = HEYPUB_DOMAIN;
    // TODO: Move this to a global so we're not instantiating multiple times
    $this->pubapi = new \HeyPublisher\API\Publisher;
    $this->slug .= $this->page;
  }

  public function __destruct() {
  	parent::__destruct();
  }

  // TODO: need a better way of doing this :(
  // Likely update to match other classes: public function action_handler() {
  public function options() {
    //   Possibly process form post
    $this->message = $this->process_options();
    $this->page_prep();
  }

  public function page_prep()  {
    // $this->print_message_if_exists(); // this is being called in content()
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
    $html = '';
    $nonce = wp_nonce_field('heypub-save-options');

    $this->logger->debug(sprintf("Page::Options#content()\n\tis_validated = %",$this->config->is_validated));
    if (!$this->config->is_validated) {
      // Display the form to register the plugin
      $content = $this->not_validated_form();
      $button = "Create Account";
    } else {
      // Display the form to update options
      $content = $this->options_capture_form();
      $button = 'Update';
    }
    $action = $this->form_action();
    $this->logger->debug(sprintf("in pageprep\n\terrors = %\n\tmessage = %s",$this->pubapi->api->error,$this->message));
    if ($this->pubapi->api->error) {
      $this->xml->error = $this->pubapi->api->error; # TODO: Fix this!!
      $this->xml->print_webservice_errors(true);
    }

    $this->print_message_if_exists();
    if ($content) {
      $html = <<<EOF
        <form method="post" action="{$action}">
          {$nonce}
          {$content}
          <input type="hidden" name="save_settings" value="0" />
          <input type="submit" class="heypub-button button-primary" name="save_button" id="save_button" value="{$button} &raquo;" />
        </form>
EOF;
    } else {
      $html = "<h2>We're currently experiencing issues.  Please check back again in a few minutes.</h2>";
    }
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

  // Display form for non-validated plugins for publisher to register publication and editor contact info
  private function not_validated_form() {
    $opts = $this->config->get_config_options();
    // $this->logger->debug(sprintf("not_validated_form opts: %s",print_r($opts,1)));
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

  // TODO: fix call to `publication_types`
  private function publication_block($data) {
    // $this->logger->debug(sprintf("$results publication_block: %s",print_r($data,1)));
    $name = htmlentities($this->strip(@$data['name']));
    $years = $this->get_years_for_select($this->strip(@$data['established']));
    // $this->warning = "This stuff don't match";
    $link = $this->get_external_url_with_icon($this->strip(@$data['urls']['website']));
    $pid = null;
    // $this->logger->debug("Options::publication_block()");
    // $this->logger->debug(sprintf("\npublication_block() mediums= \n%s",print_r($data['mediums']['data'][0]['id'],1)));

    if ($data['mediums']['data'][0]['id']) {
      $pid = $data['mediums']['data'][0]['id'];
    }

    $html = <<<EOF
    <!-- Publication Block -->
    <h3 class='first'>Publication Information</h3>
    <!--p>Tell us more about your publication.</p-->
    <ul>
      <li>
        <label class='heypub' for='hp_type'>Publication Type</label>
        <select name="heypub_opt[publisher_type][id]" id="hp_type">
        {$this->publication_types($pid)}
        </select>
      </li>
      <li>
        <label class='heypub' for='hp_name'>Publication Name</label>
        <input type="text" name="heypub_opt[name]" id="hp_name" value="{$name}" class='heypub'/>
      </li>
      <li>
        <label class='heypub' for='hp_url'>Publication URL</label>
        <input type="text" name="heypub_opt[urls][website]" id="hp_url" value="{$this->strip(@$data['urls']['website'])}" class='heypub'/> {$link}
      </li>
      <li>
        <label class='heypub' for='hp_issn'>ISSN</label>
        <input type="text" name="heypub_opt[issn]" id="hp_issn" value="{$this->strip(@$data['issn'])}" class='heypub'/>
      </li>
      <li>
        <label class='heypub' for='hp_established'>Year Established</label>
        <select name="heypub_opt[established]" id="hp_established" class='heypub'>
        {$years}
        </select>
      </li>
      <li>
        <label class='heypub' for='hp_readership'>Monthly Circulation (Readership)</label>
        <input type="text" name="heypub_opt[readership]" id="hp_readership" class='heypub' value="{$this->strip(@$data['readership'])}" />
      </li>
    </ul>
EOF;
    // '

    return $html;
  }

  // TODO: add verification icons to the URLs
  // TODO: change twitter to be URL, not just handle
  // TODO: add duotrope and dynamic other URLs like we used ot have on website.
  private function social_media($data) {
    $fb = $this->get_external_url_with_icon($this->strip(@$data['urls']['facebook']));
    $tw = $this->get_external_url_with_icon($this->strip(@$data['urls']['twitter']));
    $html = <<<EOF
      <!-- Social Block -->
      <h3>Social Media Information</h3>
      <!--p>Tell us how to find your Facebook Fan Page and follow your tweets on Twitter.</p-->
      <ul>
        <li>
          <label class='heypub' for='hp_facebook'>Facebook</label>
          <input type="text" name="heypub_opt[urls][facebook]" id="hp_facebook" class='heypub' value="{$this->strip(@$data['urls']['facebook'])}" /> {$fb}
        </li>
        <li>
          <label class='heypub' for='hp_twitter'>Twitter</label>
          <input type="text" name="heypub_opt[urls][twitter]" id="hp_twitter" class='heypub' value="{$this->strip(@$data['urls']['twitter'])}" /> {$tw}
        </li>
      </ul>
EOF;
    return $html;
  }

  // TODO: Multiple editors management - should pull from the editors user table
  private function contact_information($data){

    $editors = $data['editors']['data'][0];
    require_once(HEYPUB_PLUGIN_FULLPATH.'/include/country_list.php');
    $country = $this->strip(@$data['address']['country']);
    $options = '';

    foreach ($countries as $key=>$val) {
      $sel = '';
      if ($key == $country || $val == $country) { // we have some old stle country entries.
        $sel = "selected='selected'";
      }
      $options .= "<option value='$key' $sel>$val</option>";
    }

    $html = <<<EOF
    <h3>Contact Information</h3>
    <ul>
      <li>
        <label class='heypub' for='hp_editor_name'>Managing Editor Name</label>
        <input type="text" name="heypub_opt[editors][0][name]" id="hp_editor_name" class='heypub' value="{$this->strip($editors['name'])}" />
      </li>
      <li>
        <label class='heypub' for='hp_editor_email'>Email Address</label>
        <input type="text" name="heypub_opt[editors][0][email]" id="hp_editor_email" class='heypub' value="{$this->strip($editors['email'])}" />
      </li>
      <li>
        <label class='heypub' for='hp_address'>Street Address</label>
        <input type="text" name="heypub_opt[address][street]" id="hp_address" class='heypub' value="{$this->strip(@$data['address']['street'])}" />
      </li>
      <li>
        <label class='heypub' for='hp_city'>City</label>
        <input type="text" name="heypub_opt[address][city]" id="hp_city" class='heypub' value="{$this->strip(@$data['address']['city'])}" />
      </li>
      <li>
        <label class='heypub' for='hp_state'>State/Region</label>
        <input type="text" name="heypub_opt[address][state]" id="hp_state" class='heypub' value="{$this->strip(@$data['address']['state'])}" />
      </li>
      <li>
        <label class='heypub' for='hp_zipcode'>Zip Code</label>
        <input type="text" name="heypub_opt[address][zipcode]" id="hp_zipcode" class='heypub' value="{$this->strip(@$data['address']['zipcode'])}" />
      </li>
      <li>
        <label class='heypub' for='hp_country'>Country</label>
        <select name="heypub_opt[address][country]" id="hp_country" class="heypub">
          {$options}
        </select>
      </li>
    </ul>
EOF;
    // '
    return $html;
  }

  private function submission_guidelines($opts,$data){
    $pages = get_pages();
    $hidden = (@$data['guidelines']['custom']) ? null : "style='display:none;' ";
    $select = '';
    $link = $this->get_edit_url_for_page($opts['sub_guide_id']);
    $text = $this->get_external_url_with_icon(@$data['urls']['heypublisher']);
    foreach ($pages as $p) {
      $select .= sprintf('<option value="%s" %s>%s</option>', $p->ID, ($p->ID == $opts['sub_guide_id']) ? 'selected=selected' : null, $p->post_title);
    }
    $html = <<<EOF
    <h3>Submission Guidelines</h3>
    <!--p>Where are your submission guidelines?</p-->
    <ul>
      <li>
        <label class='heypub' for='hp_sub_guide'>Submission Guidelines Page</label>
        <select name="heypub_opt[sub_guide_id]" id="hp_sub_guide" class='heypub'>
          <option value="">-- NONE --</option>
          {$select}
        </select>
        {$link}
      </li>
      <li>
        <label class='heypub' for='hp_sub_guide_text_active'>Customize Submission Guidelines?</label>
        <select name="heypub_opt[guidelines][custom]" id="hp_guidelines_custom" onchange="HeyPublisher.selectToggle(this,'#heypub_show_guidelines_text');">
          {$this->boolean_options('custom',@$data['guidelines'])}
        </select>
      </li>

       <div id='heypub_show_guidelines_text' {$hidden}>
          <!-- Editor is overriding scraped guidelines -->
          <p>By default we display submission guidelines from the page you selected above.  You can customize the text we display here.  500 words maximum.</p>
          <ul>
          <li>
            <label class='heypub' for='hp_sub_guide_text'>Submission Guidelines to Display</label>
            <textarea name="heypub_opt[guidelines][text]" id="hp_body" class='heypub'>{$this->strip(@$data['guidelines']['text'])}</textarea>
            {$text}
          </li>
          </ul>
        </div>
    </ul>
EOF;
    return $html;


  //   private function integrations($data) {
  //     $mailchimp = @$data['integrations']['mailchimp'];
  //     $html = <<<EOF
  //       <!-- MailChimp -->
  //       <h3>MailChimp Mailing List Subscriptions</h3>
  //       <p>
  //         If you use <a href='https://mailchimp.com/' target='_blank'>MailChimp</a> to manage your mailing list, setting to <code>Yes</code> will prompt new writers to subscribe to your mailing list when they submit their work.
  //       </p>
  //       <ul>
  //         <li>
  //           <label class='heypub' for='hp_mailchimp_active'>Prompt to Subscribe?</label>
  //           <select name="heypub_opt[mailchimp_active]" id="hp_mailchimp_active" onchange="HeyPublisher.selectToggle(this,'#heypub_show_mailchimp_list');">
  //             {$this->boolean_options('active',$mailchimp)}
  //           </select>
  //         </li>
  //       </ul>
  //       <div id='heypub_show_mailchimp_list' {$hidden}>
  //         <!-- Content Specific for the MailChimp Config -->
  //         <p>Read more on how to <a href='https://kb.mailchimp.com/integrations/api-integrations/about-api-keys' target='_blank'>Find or Generate Your API Key</a> and how to <a href='https://kb.mailchimp.com/lists/manage-contacts/find-your-list-id' target="_blank">Find Your List ID</a> before continuing.
  //         </p>
  //         <ul>
  //           <li>
  //             <label class='heypub' for='hp_mailchimp_apikey'>API Key</label>
  //             <input type="text" name="heypub_opt[mailchimp_api_key]" id="hp_mailchimp_apikey" class='heypub' value="{$this->strip(@$mailchimp['api_key'])}" />
  //           </li>
  //           <li>
  //             <label class='heypub' for='hp_mailchimp_listid'>List ID</label>
  //             <input type="text" name="heypub_opt[mailchimp_list_id]" id="hp_mailchimp_listid" class='heypub' value="{$this->strip(@$mailchimp['list_id'])}" />
  //           </li>
  //         </ul>
  //       </div>
  // EOF;
  //     return $html;
  //   }




  }


  private function submission_page($opts){
    $replacer = HEYPUB_SUBMISSION_PAGE_REPLACER;
    // TODO: Replace this will call to $this->get_form_url_for_page('create_form_page')
    $link_url = sprintf('%s&action=create_form_page',$this->form_action());
    $link = $this->get_edit_url_for_page($opts['sub_page_id']);
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
      <p {$yespage} id='heypub-yes-guidelines'>Where should your submission form be displayed?</p>
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
          {$link}
        </li>
      </ul>
      <p class='heypub-subtext'>Ensure that the following shortcode is contained somewhere within this page.
      This code will be replaced by the actual submission form when a writer views the page.</p>
      <blockquote class='heypub'><b>{$replacer}</b></blockquote>
EOF;
    return $html;
  }

  private function submission_criteria($data) {
    $hidden = (@$data['active']) ? null : "style='display:none;' ";
    $mapped_genres = $this->genre_map(@$data['genres']);

    $html = <<<EOF
      <h3>Submission Criteria</h3>
      <ul>
        <li>
          <label class='heypub' for='hp_accepting_subs'>Currently Accepting Submissions?</label>
          <select name="heypub_opt[active]" id="hp_accepting_subs" onchange="HeyPublisher.selectToggle(this,'#heypub_show_genres_list');">
            {$this->boolean_options('active',@$data)}
          </select>
        </li>
      </ul>
      <div id='heypub_show_genres_list' {$hidden}>
        <!-- Genres -->
        <h3>Select all categories of work your publication accepts.</h3>
        {$mapped_genres}
        <br/>
      </div>
      <ul>
        <li>
          {$this->boolean_select('Accept Reprints?','reprints',@$data['accepts'],'accepts')}
        </li>
        <li>
          {$this->boolean_select('Simultaneous Submissions?','simultaneous',@$data['accepts'],'accepts')}
        </li>
        <li>
          {$this->boolean_select('Email Submissions?','email',@$data['accepts'],'accepts')}
        </li>
        <li>
          {$this->boolean_select('Multiple Submissions?','multiple',@$data['accepts'],'accepts')}
        </li>
      </ul>
EOF;
    return $html;
  }

  // Marries the list of HP genres to the ones that have been activated in plugin
  // Returns array where key is the HP genre ID and val is the WP category id
  // @updated 2020-03-25
  private function merged_genre_map($my_genres) {
    $this->logger->debug("Options::merged_genre_map()");
    $all_genres = $this->pubapi->get_genres();
    $this->logger->debug(sprintf("\tmerged_genre_map() \$my_genres = \n%s",print_r($my_genres,1)));
    // $this->logger->debug(sprintf("\tmerged_genre_map() \$all_genres = \n%s",print_r($all_genres,1)));
    // Extract the ids from the genres passed in by publisher data
    $has = array_reduce($my_genres, function($accumulator,$item) {
      $id = $item['id'];
      $accumulator[$id] = ($item['wp_id']) ? $item['wp_id'] : $saved_genres["$id"];
      return $accumulator;
    });
    $test = array();
    if (is_array($has)) { $test = array_filter($has); }
    // $this->logger->debug(sprintf("\t\$test = \n%s\n\t\$has = \n%s",print_r($test,1),print_r($has,1)));
    if (count($has) != count($test)) {
      // need to grab the saved category mapping and set properly - this is a one-off
      $saved_genres = $this->xml->get_category_mapping();
      // $this->logger->debug(sprintf("\tmerged_genre_map() \$saved_genres = \n%s",print_r($saved_genres,1)));
      foreach ($saved_genres as $id=>$val) {
        if (array_key_exists($id,$has)) {
          $has[$id] = $val;
        }
      }
    }
    $this->logger->debug(sprintf("\tmerged_genre_map() \$has (reduced) = \n%s",print_r($has,1)));

    // We should have local and remote mapping
    $map = array();
    foreach ($all_genres as $key=>$hash) {
      $name = $hash['name'];
      $id = $hash['id'];
      //  the value may be null - so don't use isset() here!!
      // TODO: this errors if $has is empty
      $was = isset($has) && array_key_exists($id,$has) ? true : false;
      $map[$name] = array('name' => $name, 'id' => $id, 'has' => $was, 'wp_id' => $has[$id]);
    }
    return $map;
  }


  // Map internal categories to HeyPublisher Genres
  // includes the sub object 'genres'
  private function genre_map($data) {
    $cols = 2; // colums for mapping table
    // Get the full list of HP genres from the API
    $genres = $this->merged_genre_map($data['data']);

    $this->logger->debug(sprintf("Options::genre_map() \n\tMapped genres = %s",print_r($genres,1)));

    // $cats = $this->xml->get_my_categories_as_hash();
    // $this->logger->debug(sprintf("Options::genre_map() \$cats = %s",print_r($cats,1)));
    if (empty($genres)) { return ''; }
    $header = '';
    for ($x=0;$x<$cols;$x++) {
      $header .= "<th>Genre</th><th>Your Category</th>";
    }
    $cnt = 0;
    $count = 1;
    $mapping = '';
    foreach ($genres as $id=>$hash) {
      $count++;
      $class = null;
      if(($count%($cols*2)) != 0) { $class = 'alternate';}
      if ($cnt % $cols == 0) {
        $cnt = 0;
        $mapping .= sprintf("</tr><tr class='%s'>",$class);
      }
      $mapping .= sprintf('
        <td>%s &nbsp; <input id="cat_%s"type="checkbox" name="heypub_opt[genres][%s]" value="1" %s onclick="HeyPublisher.clickCheck(this,\'chk_%s\');"/></td>
        <td>%s</td>',
          $hash['name'],$hash['id'],$hash['id'],($hash['has']) ? "checked=checked" : null,$hash['id'],
          $this->get_wp_category_dropdown($hash['id'],$hash['has'],$hash['wp_id'])
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


  private function writer_notifications($data) {
    $sub_states = sprintf('%s/about/submission_states',$this->domain);
    $notes = @$data['notifications'];
    $html = <<<EOF
      <h3>Writer Notifications</h3>
      <p>
        HeyPublisher will automatically send an email to your writer when their submission transitions into one of the
        <a href='{$sub_states}' target='_blank'>states in the submission cycle</a>.
      </p>
      <p>
        You can disable sending email by setting the value to <code>No</code> below.</p>
      <p>
        Customize the content of the emails you want sent through the
        <a href='admin.php?page=heypublisher_email' target=_top>Email Templates</a>
        screen.
      </p>
      <input type='hidden' name='notify_submitted' value='1'>
      <ul>
        <li>
          {$this->boolean_select('Read?','read',$notes,'notifications','Sent when the submission is first read by an Editor.')}
        </li>
        <li>
          {$this->boolean_select('Under Review?','considered',$notes,'notifications','Sent when the submission is being held for consideration.')}
        </li>
        <li>
          {$this->boolean_select('Accepted?','accepted',$notes,'notifications','Sent if the submission is Accepted for publication.')}
        </li>
        <li>
          {$this->boolean_select('Rejected?','rejected',$notes,'notifications','Sent if the submission is Rejected by an Editor.')}
        </li>
        <li>
          {$this->boolean_select('Published?','published',$notes,'notifications','Sent when a submission is Published, or on the actual publication date if Scheduled.')}
        </li>
        <li>
          {$this->boolean_select('Withdrawn?','withdrawn',$notes,'notifications','Confirmation sent when a submission is marked as Withdrawn.')}
        </li>
      </ul>
EOF;
    return $html;
  }

  private function integrations($data) {
    $mailchimp = @$data['integrations']['mailchimp'];
    $hidden = (@$mailchimp['active']) ? null : "style='display:none;' ";
    $html = <<<EOF
      <!-- MailChimp -->
      <h3>MailChimp Mailing List Subscriptions</h3>
      <p>
        If you use <a href='https://mailchimp.com/' target='_blank'>MailChimp</a> to manage your mailing list, setting to <code>Yes</code> will prompt new writers to subscribe to your mailing list when they submit their work.
      </p>
      <ul>
        <li>
          <label class='heypub' for='hp_mailchimp_active'>Prompt to Subscribe?</label>
          <select name="heypub_opt[integrations][mailchimp][active]" id="hp_mailchimp_active" onchange="HeyPublisher.selectToggle(this,'#heypub_show_mailchimp_list');">
            {$this->boolean_options('active',$mailchimp)}
          </select>
        </li>
      </ul>
      <div id='heypub_show_mailchimp_list' {$hidden}>
        <!-- Content Specific for the MailChimp Config -->
        <p>Read more on how to <a href='https://kb.mailchimp.com/integrations/api-integrations/about-api-keys' target='_blank'>Find or Generate Your API Key</a> and how to <a href='https://kb.mailchimp.com/lists/manage-contacts/find-your-list-id' target="_blank">Find Your List ID</a> before continuing.
        </p>
        <ul>
          <li>
            <label class='heypub' for='hp_mailchimp_apikey'>API Key</label>
            <input type="text" name="heypub_opt[integrations][mailchimp][api_key]" id="hp_mailchimp_apikey" class='heypub' value="{$this->strip(@$mailchimp['api_key'])}" />
          </li>
          <li>
            <label class='heypub' for='hp_mailchimp_listid'>List ID</label>
            <input type="text" name="heypub_opt[integrations][mailchimp][list_id]" id="hp_mailchimp_listid" class='heypub' value="{$this->strip(@$mailchimp['list_id'])}" />
          </li>
        </ul>
      </div>
EOF;
    return $html;
  }

  private function experimental_options($data) {
    $html = <<<EOF
      <!-- Experimental Options -->
      <h3>Disable HTML Clean-Up</h3>
      <p>
        Generally you should keep this set to <code>No</code>.  If you accept submissions from writers using symbol-based languages (ie: Japanese, Chinese, etc.) you may need to set this to <code>Yes</code>.
      </p>
      <ul>
        <li>
          {$this->boolean_select('Turn Off HTML Clean-Up?','multibyte',@$data['accepts'],'accepts')}
        </li>
      </ul>
EOF;
    return $html;
  }

  // Display the form that captures all of the options.
  private function options_capture_form() {
    $html = '';
    // load the existing configuration
    // Need this for submission form and guidelines page IDs
    $opts = $this->config->get_config_options();

    // Load the data from HeyPublisher db
    $settings = $this->pubapi->get_publisher_info();
    // update the configs with latest parsing data from server
    $this->xml->sync_publisher_info($settings);

    // $this->logger->debug(sprintf("Options::options_capture_form() \$opts = %s",print_r($opts,1)));
    // $this->logger->debug(sprintf("Options::options_capture_form() \$settings = %s",print_r($settings,1)));
    // $this->logger->debug(" => dislaying Options page");
    if ($settings) {
      $html = <<<EOF
        <input type="hidden" name="heypub_opt[isvalidated]" value="1" />
        <input type="hidden" name="save_settings" value="0" />
        {$this->publication_block($settings)}
        {$this->contact_information($settings)}
        {$this->social_media($settings)}
        {$this->submission_guidelines($opts,$settings)}
        {$this->submission_page($opts)}
        {$this->submission_criteria($settings)}
        {$this->writer_notifications($settings)}
        {$this->integrations($settings)}
        {$this->experimental_options($settings)}

EOF;
    }
    return $html;
  }

  /*
   * Process the form post options, if present
   */
  function process_options() {
    // $this->logger->debug(sprintf("POST = %s",print_r($_POST,1)));
    $this->logger->debug("Page:Options #process_option()");
    $message = null; // default is null message
    if(isset($_REQUEST['save_settings']) && check_admin_referer('heypub-save-options')) {
      $this->logger->debug("\tSaving Settings from form POST");
      if (isset($_POST['hp_user'])) {
        $this->logger->debug("\tcalling validate_user()");
        $message = $this->validate_user($_POST);
      }
      elseif (isset($_POST['heypub_opt']) && $_POST['heypub_opt']['isvalidated'] == '1') {
        $this->logger->debug("\tcalling update_options()");
        $message = $this->update_options($_POST);
      }
    }
    elseif(isset($_REQUEST['action']) && ($_REQUEST['action'] == 'create_form_page')) {
      $this->logger->debug('creating the POST form!');
       check_admin_referer('create_form');
       $page_id = $this->heypub_create_submission_page();
       // Ensure this id is saved to db
       $this->config->set_config_option('sub_page_id',$page_id);
       $message = sprintf("A Submission Form page has been created for you. <a href='%s' target='_blank'>View page &raquo;</a><br/>",get_permalink($page_id));
    }
    return $message;
  }

  // New Install Path
  // process the form post and validate the user and publisher
  private function validate_user($post) {
    $this->logger->debug("Page::Options#validate_user()");
    $message = null;
    $user = $post['hp_user'];
    // store the username and password they provided
    $this->config->set_config_option('name',$user['name']);
    $this->config->set_config_option('url',$user['url']);
    // Call out to the the webservice to validate
    if ($this->xml->authenticate($user)) {
      $data = array(
        'is_validated'  => date('Y-m-d'),
        'user_oid'      => $this->xml->user_oid,
        'publisher_oid' => $this->xml->pub_oid
      );
      // TODO: Clean this up - but for now, need to be explicitly setting all instances of uoid/poid
      $this->config->uoid = $this->xml->user_oid;
      $this->config->poid = $this->xml->pub_oid;
      $this->config->set_install_options($data);
      $this->config->set_is_validated();  // ensures that this page load has correct value

      $this->logger->debug(sprintf("\n\tinstall options now = %s\n\t is_validated = %s",print_r($this->config->install,1),$this->config->is_validated));

      // Fetch Publisher INFO from HeyPublisher API and pre-populate the layout, if we can
      // The options have not yet been loaded at this stage, so need to be explicitly set:
      $this->pubapi->api->uoid = $this->xml->user_oid;
      $this->pubapi->api->poid = $this->xml->pub_oid;

      // This is happening in page prep
      $pub = $this->pubapi->get_publisher_info();
      $message = 'Account validation succeeded! You can now configure your account.';
      if ($pub) {
        // TODO: Change this to pull from JSON endpoint
        $cats = $this->xml->get_my_categories_as_hash();
        $has_genres = false;
        foreach ($cats as $id=>$hash) {
          if ($hash['has']) { $has_genres = true; }
        }
        // For a re-install, this will be based on the categories returned from HP servers
        $pub['accepting_subs'] = $has_genres;

        $message .= "<br/><br/>To help you get started we've pre-populated the form with information we already have.";

        $this->config->set_config_options($pub);
        // now only the boolean overrides
        // need to hack this for now
        // TODO: Need to reintroduce this tracker or remove
        if (!$pub['paying_market'] == '0') {
          $this->config->set_config_option('paying_market_range',null);
        }
      }
    }
    return $message;
  }

  // After form POST - sync all options into local WP database as well as push
  // to the remote server.  This keeps the two databases in sync
  // TODO: Make this use JSON endpoint
  // TODO: ensure we're only saving items that are a MUST for making plugin work -- everything else is remotely accessed
  private function update_options($post) {
    $this->logger->debug(sprintf("Page::Options#update_options(): \n\t\$post = %s",print_r($post,1)));
    $message = null;
    // Processing a form post of Option Updates
    // Get options from the post
    $opts = $post['heypub_opt'];
    $this->logger->debug(sprintf("\t\$opts = %s",print_r($opts,1)));
    // Need to detect the values in category_map where:
    //  a) value is < 0
    //  b) key is not present in genres array
    $this->clean_genres_category_map($opts);
    // $this->logger->debug(sprintf("\tabout to test accepting subs = %s\n\tactive = ",print_r($opts['category_map'],1),$opts['active']));
    $opts['accepting_subs'] = false;
    if (count(array_keys($opts['category_map'])) > 0 && $opts['active']) {
       $opts['accepting_subs'] = true;
    }

    //  Bulk update the form post, saving into local WP db
    $this->config->set_config_options($opts);
    // This does not update the category map, because we lock down only permitted keys
    $this->config->set_config_option('category_map',$opts['category_map']);
    // TODO: Are these still necessary?
    // $this->config->set_config_option('categories',$cats);
    // if (!$opts['paying_market']) {
    //   $this->config->set_config_option('paying_market_range',null);
    // }

    // get the URL for the sub guidelines
    $opts['urls']['guideline'] = get_permalink($opts['sub_guide_id']);
    // get the URL for the sub form itself
    $opts['urls']['submission'] = get_permalink($opts['sub_page_id']);
    // Blog's RSS feed is:
    $opts['urls']['rss'] = get_bloginfo('rss2_url');
    // now attempt to sync with HeyPublisher.com
    unset($opts['category_map']);
    $success = $this->pubapi->update_publisher($opts);
    if ($success) {
      $message = 'Your changes have been saved and syncronized with HeyPublisher.';
    }
    return $message;
  }

  private function publication_types($pid=null) {
    $pub_types = $this->pubapi->get_publisher_types();
    $html = '';
    if (empty($pub_types)) { return $html; }
    foreach ($pub_types as $id=>$hash){
      $html .= sprintf('<option value="%s" %s>%s</option>',$hash['id'],($pid == $hash['id']) ? "selected=selected" : null, $hash['name']);
    }
    return $html;
  }

  // Generate the dropdown of all categories in this WP install
  // vars:
  //  id: HP genre ID
  //  show: boolean simply to control whether or not to display
  //  wp_id: WordPress category id (may be null)
  private function get_wp_category_dropdown($id,$show,$wp_id) {
    $list = wp_dropdown_categories(
      array(
        'selected' => ($wp_id) ? $wp_id : 0,
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
    $this->logger->debug(sprintf("the POST_ID is %s",$post_ID));
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

  // Need to delete the values in category_map where:
  //  a) value is < 0
  //  b) key is not present in genres array
  private function clean_genres_category_map(&$opts){
    // get all HP ids we may have - this is the keys from the category_map as it will include ALL on a form post
    $all_keys = array_keys($opts['category_map']);
    $cats = array();
    $genres = array();
    $this->logger->debug(sprintf("Options#clean_genres_category_map()\n\t\$all_keys = %s",print_r($all_keys,1)));
    foreach ($all_keys as $id) {
      if (isset($opts['genres'][$id]) && isset($opts['category_map'][$id]) && $opts['category_map'][$id] > 0) {
        $cats[$id] = $opts['category_map'][$id];
        $g = array('id' => $id, 'wp_id' => $opts['category_map'][$id]);
        array_push($genres,$g);
      }
    }
    $this->logger->debug(sprintf("\n\t\$category_map = %s\n\t\$genres = %s",print_r($cats,1),print_r($genres,1) ));
    $opts['category_map'] = $cats;
    $opts['genres'] = $genres;
  }

}
?>
