<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('Kindle-Feed: Illegal Page Call!'); }

/**
* KindleFeed class for publishing future scheduled content to Kindle
*
*/
class KindleFeed {

  var $slug = 'kindle-custom-feed';
  var $plugin_file = 'kindle-feed/kindle-feed.php';  # this helps us with the plugin_links
  var $opt_key = '_kindle_feed_settings';
  var $help = false;
  var $opt_values = array('kindle_feed_next_period','kindle_feed_pre_number','kindle_feed_pre_period'); // form options
  var $feed = false;
  var $feed_key = '_kindle_feed';

  public function __construct() {

  }   

  public function __destruct() {

  }
  
  public function register_options() {
    foreach ($this->opt_values as $key) {
      register_setting( $this->opt_key, $key );
    }
  }
  
  public function plugin_links($links, $file) {
    if ($file == $this->plugin_file) {
      $settings_link = '<a href="admin.php?page='.$this->slug.'">'.__("Settings", "kindle-feed").'</a>';
      array_unshift($links, $settings_link);
    }
    return $links;
  }

  public function feed_title() {
    $string = sprintf('%s : %s',get_bloginfo('name'), $this->feed[pub]);
    return $string;
  }

  public function query_string_for_posts() {
		$this->date_range_for_feed();
		// $query = "post_status=published";
    // if ($this->feed[live]) {
	    $query = sprintf('year=%s&monthnum=%s&post_status=future&posts_per_page=100',$this->feed[year],$this->feed[month]);
    // }
    return $query;
  }
  
  // The hierarchical manifest format.  This is the primary entry point for the primary manifest
  public function format_manifest() {
   // load the feed template
   load_template(dirname(__FILE__) . '/templates/manifest.php');
  }
  
  public function configuration_screen_help($contextual_help, $screen_id, $screen) {
    if ($screen_id == $this->help) {
      $contextual_help = <<<EOF
<h2>Overview</h2>      
<p>This plugin will create a Feed of Scheduled Posts that you can send to Kindle prior to publication on your website.  Doing this will enable you to "pre-release" a version of your publication on Kindle before it goes live on your website.
</p>
<h3>Collect Posts for Next:</h3>
<p>This determines whether the plugin should be looking at next "Month" or next "Week" to find eligable content.  To be eligible for inclusion in the feed, the post must be "Scheduled" and have a date that occurs within the Next Period.</p>
<h3>Update Kindle Feed:</h3>
<p>This determines <i>when</i> the Kindle feed will be updated with the next batch of content.  You must ensure that all content to be included in the feed is "Scheduled" for publication by this date, otherwise the content will not be included when the feed is updated.</p>
<h3>Example:</h3>
<p>Assume that it is currently November and you have a bunch of content "Schduled" for publication on December 1st, the date your next issue will be published online.  Also assume you publish on a monthly schedule.</p>
<p>If your Kindle publication schedule is 2 weeks prior to the publication of content on your website, you would configure this plugin as follows:</p>
<ul>
<li>Collect Posts for Next:  <b>Month</b></li>
<li>Update Kindle Feed: <b>2</b> <b>Weeks</b> prior to publication.</li>
</ul>

EOF;
    }
  	return $contextual_help;
  }
  
  
  public function configuration_screen() {
	  // initialize the dates
    $this->date_range_for_feed();
  
    $opts = $this->get_options();
    $periods = array('month');
    $days = range(1,5);
    
?>  
<div class="wrap">
  <h2>Kindle Feed Settings</h2>
	<p>Need to know what these fields mean?  Simply click the "Help" link at the top of your screen.</p> 
	  <form method="post" action="options.php">
    <?php settings_fields( $this->opt_key ); ?>
    <table class="form-table">
      <tr valign="top">
        <th scope="row">Collect Posts for Next:</th>
        <td>
          <select name="kindle_feed_next_period">
        <?php 
          foreach ($periods as $val) {
            echo $this->build_option_string($val,$opts['kindle_feed_next_period']);
          }
        ?>
          </select>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Update Kindle Feed:</th>
        <td>
          <select name='kindle_feed_pre_number'>
        <?php
          foreach ($days as $int) {
            echo $this->build_option_string($int,$opts['kindle_feed_pre_number']);
          }
        ?>
          </select>
          <select name='kindle_feed_pre_period'>
        <?php
        $periods = array('days','weeks');
          foreach ($periods as $val) {
            echo $this->build_option_string($val,$opts['kindle_feed_pre_period']);
          }
        ?>
          </select>
          prior to scheduled publication date.
        </td>
      </tr>
      
    </table>
    <p class="submit">
      <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

  </form>
</div>  
<?php  
    
  }
  
  public function get_options() {
    $opts = array();
    foreach ($this->opt_values as $key) {
      $opts[$key] = get_option($key);
    }
    return $opts;
  }
  
	/**
	* Ensure our configuration gets cleaned out if this plugin is uninstalled
	*/
	public function deactivate_plugin() {
		delete_option($this->feed_key);
	  foreach ($this->opt_values as $key) {
			delete_option($key);
		}
	  return;
	}

  private function build_option_string($val,$selected) {
    $string = sprintf("<option value='%s' %s >%s</option>",
      $val,($val == $selected) ? 'selected=selected' : '',ucfirst($val)
    );
    return $string;
  }
  
  /**
  * Get the valid date ranges for this feed's content, based upon the plugin configuration.
  */
  private function date_range_for_feed() {
    $this->feed = get_option($this->feed_key);
		$this->feed[live] = false;
		$last_build_month = $this->feed[build_period];
		
    $today = date('Y-m-d',time());
    $opts = $this->get_options();
    $next_period = (FALSE != $opts[kindle_feed_next_period]) ? $opts[kindle_feed_next_period] : 'month';
    $pre_number = (FALSE != $opts[kindle_feed_pre_number]) ? $opts[kindle_feed_pre_number] : '1';
    $pre_period = (FALSE != $opts[kindle_feed_pre_period]) ? $opts[kindle_feed_pre_period] : 'week';

    $next_month = date('Y-m-d', strtotime("+1 $next_period", strtotime($today)));
    $build = date('Y-m-d', strtotime("-$pre_number $pre_period", strtotime($next_month)));
    // printf("build = %s\nnext_month = %s\ntoday = %s\n",$build,$next_month,$today);
    // printf("feed = %s\n",print_r($this->feed,1));
    if (date('n') == 12) {
      $this->feed[published] = date(DATE_ATOM, mktime(0, 0, 0, 0, 0, date('Y')+1)); 
    } else {
      $this->feed[published] = date(DATE_ATOM, mktime(0, 0, 0, date('n')+1, 1)); 
    }
		$this_build_month = sprintf("%s-01",date('Y-m', strtotime("+1 $next_period", strtotime($today))));
		// only update if we're within the build period AND we haven't built already
    if (strtotime($build) <= strtotime($today)) {
			$this->feed[live] = true;
	      // we update the build options
	      $this->feed[pub] = date('F, Y', strtotime("+1 $next_period", strtotime($today)));
	      $this->feed[month] = date('n', strtotime("+1 $next_period", strtotime($today)));
	      $this->feed[year] = date('Y', strtotime("+1 $next_period", strtotime($today)));
	      $this->feed[build] = date(DATE_ATOM, time());  // This needs to be static - so we'll need to store in db
				$this->feed[build_period] = $this_build_month;
	      update_option($this->feed_key,$this->feed);
    }
  }
  
}

?>
