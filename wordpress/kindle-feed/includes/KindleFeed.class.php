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
  var $opt_values = array('kindle_feed_next_period','kindle_feed_pre_number','kindle_feed_pre_period','kindle_feed_category_order'); // form options
  var $feed = false;
  var $feed_key = '_kindle_feed';

  public function __construct() {
	  // initialize the dates
    $this->date_range_for_feed();
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

	/**
	* Input an array of overrides for the query
	*/
  public function query_string_for_posts($custom=array()) {
		// Date Range Params
		$params = array(
			'monthnum' 	=> $this->feed['month'],
			'year' 			=> $this->feed['year'],
			'posts_per_page' => -1,
			'post_status'  => 'publish'
			);

		foreach ($custom as $key=>$val) {
			// overwrite with our custom vars
			$params[$key] = $custom[$key];
		}
		// Add the future post to the query if we're still pending publication of this issue
		// $params['post_status'] = 'future';
		// return the array
    return $params;
  }
  
  // The entry point manifest format.  This is the primary entry point for the primary manifest
  public function format_manifest() {
   // load the feed template
   load_template(dirname(__FILE__) . '/templates/manifest.php');
  }

  // The section manifest format.  This is the primary entry point for the categories manifest
  public function format_section() {
   // load the feed template
   load_template(dirname(__FILE__) . '/templates/section.php');
  }

  public function format_article() {
   // load the feed template
   load_template(dirname(__FILE__) . '/templates/article.php');
  }
  
	// Contextual Help for the Plugin Configuration Screen
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
  
  // Display the Plugin Configuration Screen
	// v.1 will display minimal info
	// v.2 will display configurations for future dates
  public function configuration_screen() {
    $opts = $this->get_options();

		$cat_order = array('alphabetically');
		// Not currently in use
    // $periods = array('month');
    // $days = range(1,5);
    
?>  
<div class="wrap">
  <h2>Kindle Feed Settings</h2>
	<p>Need to know what these fields mean?  Simply click the "Help" link at the top of your screen.</p> 
	  <form method="post" action="options.php">
    <?php settings_fields( $this->opt_key ); ?>
    <table class="form-table">
			<tr valign="top">
        <th scope="row">Organize Categories by:</th>
        <td>
          <select name="kindle_feed_category_order">
        <?php 
          foreach ($cat_order as $val) {
            echo $this->build_option_string($val,$opts['kindle_feed_category_order']);
          }
        ?>
          </select>
        </td>
      </tr>

<?php
/*
			// Future functionality here  - not currently in use
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
*/
?>
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
	
		// For pulling ALL content from current month/year
    $today = date('Y-m-d',time());
    $opts = $this->get_options();
		$this_build_month = sprintf("%s-01",date('Y-m', strtotime($today)));

    $opts = $this->get_options();
		$this->feed = get_option($this->feed_key);
		$this->feed[live] = true;
		$this->feed[build_period] = $this_build_month;
    $this->feed[published] = date(DATE_ATOM, mktime(0, 0, 0, date('n'), 1)); 
		$this->feed[pub] = date('F, Y', strtotime($today));
		$this->feed[month] = date('n', strtotime($today));
		$this->feed[year] = date('Y', strtotime($today));
		$this->feed[build] = date(DATE_ATOM, time());  // This needs to be static - so we'll need to store in db
		update_option($this->feed_key,$this->feed);


		//  ALL OF THE BELOW IF FOR WHEN WE START FUTURE-PUBLISHING
		//     $this->feed = get_option($this->feed_key);
		// $this->feed[live] = false;
		// $last_build_month = $this->feed[build_period];
		// 
		//     $today = date('Y-m-d',time());
		//     $opts = $this->get_options();
		//     $next_period = (FALSE != $opts[kindle_feed_next_period]) ? $opts[kindle_feed_next_period] : 'month';
		//     $pre_number = (FALSE != $opts[kindle_feed_pre_number]) ? $opts[kindle_feed_pre_number] : '1';
		//     $pre_period = (FALSE != $opts[kindle_feed_pre_period]) ? $opts[kindle_feed_pre_period] : 'week';
		// 
		//     $next_month = date('Y-m-d', strtotime("+1 $next_period", strtotime($today)));
		//     $build = date('Y-m-d', strtotime("-$pre_number $pre_period", strtotime($next_month)));
		//     // printf("build = %s\nnext_month = %s\ntoday = %s\n",$build,$next_month,$today);
		//     // printf("feed = %s\n",print_r($this->feed,1));
		//     if (date('n') == 12) {
		//       $this->feed[published] = date(DATE_ATOM, mktime(0, 0, 0, 0, 0, date('Y')+1)); 
		//     } else {
		//       $this->feed[published] = date(DATE_ATOM, mktime(0, 0, 0, date('n')+1, 1)); 
		//     }
		// $this_build_month = sprintf("%s-01",date('Y-m', strtotime("+1 $next_period", strtotime($today))));
		// // only update if we're within the build period AND we haven't built already
		//     if (strtotime($build) <= strtotime($today)) {
		// 	$this->feed[live] = true;
		// 	      // we update the build options
		// 	      $this->feed[pub] = date('F, Y', strtotime("+1 $next_period", strtotime($today)));
		// 	      $this->feed[month] = date('n', strtotime("+1 $next_period", strtotime($today)));
		// 	      $this->feed[year] = date('Y', strtotime("+1 $next_period", strtotime($today)));
		// 	      $this->feed[build] = date(DATE_ATOM, time());  // This needs to be static - so we'll need to store in db
		// 		$this->feed[build_period] = $this_build_month;
		// 	      update_option($this->feed_key,$this->feed);
		//     }
  }
	// Get the first sentance of excerpt, if there, and strip all HTML from the inbound string
	public function strip_excerpt($string) {
		$parts = split('\.',strip_tags($string));
		return sprintf('%s.',$parts[0]);
	}
	// Format the content according to Kindle publishing rules.
	public function strip_content($data_str) {
		// define allowable tags
		$allowable_tags = '<p><a><strong><em><img><ul><ol><li><table><thead><tbody><tr><th><td>';
		// define allowable attributes
		$allowable_atts = array('src','href');

		// strip collector
		$strip_arr = array();

		// load XHTML with SimpleXML
		$data_sxml = simplexml_load_string('<root>'. $data_str .'</root>', 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOXMLDECL);

		if ($data_sxml ) {
		    // loop all elements with an attribute
		    foreach ($data_sxml->xpath('descendant::*[@*]') as $tag) {
		        // loop attributes
		        foreach ($tag->attributes() as $name=>$value) {
		            // check for allowable attributes
		            if (!in_array($name, $allowable_atts)) {
		                // set attribute value to empty string
		                $tag->attributes()->$name = '';
		                // collect attribute patterns to be stripped
		                $strip_arr[$name] = '/ '. $name .'=""/';
		            }
		        }
		    }
		}
		// ALL <p> tags must be <p align='left'>
		// All <strong> must be <b>; all <em> must be <i>
		// strip unallowed attributes and root tag
		$data_str = strip_tags(preg_replace($strip_arr,array(''),$data_sxml->asXML()), $allowable_tags);
		return $data_str;

	}
}

?>
