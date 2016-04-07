<?php
namespace HeyPublisher;

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

/**
 * HeyPublisher base class for all admin pages
 *
 */

// Load the class files and associated scoped functionality
load_template(dirname(__FILE__) . '/../Loudlever/Loudlever.class.php');
class Page extends \Loudlever\Loudlever {
  var $i18n = 'heypublisher';
  var $logo_block = '';
  var $xml = null;  # the pointer for $hp_xml
  var $strip = 'strip';
  var $slug = 'heypublisher';  # the slug used for constructing URLs
  var $message = '';
  var $additional_side_nav = null;


  public function __construct() {
    global $hp_xml;
  	parent::__construct();
    $this->plugin['home'] = 'https://wordpress.org/plugins/heypublisher-submission-manager/';
    $this->plugin['support'] = 'https://wordpress.org/support/plugin/heypublisher-submission-manager';
    $this->plugin['contact'] = 'mailto:support@heypublisher.com';
    $this->log_file = dirname(__FILE__) . '/../../../error.log';
    $this->xml = $hp_xml;
  }

  public function __destruct() {
  	parent::__destruct();
  }

  protected function strip($var) {
    return stripslashes($var);
  }

  // Page wrapper
  protected function page($title, $subtitle, callable $content, $args=null) {
    $body = $content($args);  // this way side-nav var can be set
    echo $this->page_header($title);
    echo $this->two_column_header();
    echo $this->two_column_right_block();
    echo $this->two_column_left_block($subtitle,$body);
    echo $this->two_column_footer();
    echo $this->page_footer();
  }

  // display sidenav in consistent way
  public function sidenav_block() {
    $header = sprintf('<img src="%s/images/logo.jpg">',HEY_BASE_URL);
    return $this->standard_sidenav_info_block('about',$header,$this->additional_side_nav);
  }

  // Consistent wrapper for classes that extend Page
  protected function page_header($title) {
    $html = <<<EOF
    <div class="wrap">
      <h2>{$title}</h2>
EOF;
    return $html;
  }
  protected function page_footer() {
    $html = "</div>";
    return $html;
  }
  protected function two_column_header() {
    $html = '<div id="poststuff" class="metabox-holder has-right-sidebar">';
    return $html;
  }
  protected function two_column_footer() {
    $html = '</div>';
    return $html;
  }
  protected function two_column_right_block() {
    $html = <<<EOF
    <!-- START Right Side -->
    <div class="inner-sidebar ll-plugin">
      <div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">
        {$this->sidenav_block()}
      </div>
    </div>
    <!-- END Right Side -->
EOF;
    return $html;
  }
  protected function two_column_left_block($title,$content) {
    $html = <<<EOF
    <!-- START Left Side -->
    <div class="has-sidebar sm-padded">
      <div id="post-body-content" class="has-sidebar-content ll-plugin">
        <div class="meta-box-sortabless">
          {$this->box_header('snb_default_asins',__($title,$this->i18n))}
          {$content}
          {$this->box_footer()}
        </div>
      </div>
    </div>
EOF;
    return $html;
  }
  // Display yes/no select list in consistent way
  protected function boolean_options($key,$opts) {
    $html = '';
    $bools = array(FALSE,TRUE);  # we may have true boolean
    $elems = array('No','Yes');
    for ($i=0; $i<2; $i++) {
      $sel = '';
      if ($opts[$key] == $i || $opts[$key] == $bools[$i] ) { $sel = 'selected=selected'; }
      $html .= <<<EOF
        <option value='{$i}' {$sel}>$elems[$i]</option>
EOF;
    }
    return $html;
  }
  // Wrapper for these options when the select form is consistent
  protected function boolean_select($label,$key,$opts,$alt=false) {
    $html = <<<EOF
      <label class='heypub' for='hp_{$key}'>{$label}</label>
      <select name="heypub_opt[{$key}]" id="hp_{$key}">
      {$this->boolean_options($key,$opts)}
      </select>
EOF;
    if ($alt) {
      $html .= sprintf('&nbsp;<small>%s</small>',$alt);
    }
    return $html;
  }

  protected function print_message_if_exists() {
    if (!empty($this->message)) {
      $e = <<<EOF
        <div id="message" class="notice updated">
          <p>
            {$this->message}
          </p>
        </div>
EOF;
      print($e);
    }
  }
  protected function nonced_url($action=[],$nonce=null) {
    $url = sprintf('%s/wp-admin/admin.php?page=%s',get_bloginfo('wpurl'),$this->slug);
    if (is_array($action) && !empty($action)) {
      foreach($action as $key => $val) {
        $url .= sprintf('&%s=%s',$key,$val);
      }
    }
    if(function_exists('wp_nonce_url') && $nonce){
      $url = wp_nonce_url($url,$nonce);
    }
    return $url;
  }

}
?>
