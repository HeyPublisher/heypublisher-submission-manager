<?php
namespace HeyPublisher;

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

/**
 * HeyPublisher base class for all admin pages
 *
 */

// Load the class files and associated scoped functionality
load_template(HEYPUB_PLUGIN_FULLPATH . '/include/classes/Loudlever/Loudlever.class.php');
class Page extends \Loudlever\Loudlever {
  var $i18n = 'heypublisher';
  var $logo_block = '';
  var $xml = null;  # the pointer for $hp_xml
  var $strip = 'strip';
  var $slug = 'heypublisher';  # the slug used for constructing URLs
  var $page = '';
  var $nonce = '';
  var $message = '';
  var $warning = '';
  var $additional_side_nav = null;
  var $config = null;
  var $options = null;

  public function __construct() {
    global $hp_xml,$hp_config;
  	parent::__construct();
    $this->plugin['home'] = 'https://github.com/HeyPublisher/heypublisher-submission-manager';
    $this->plugin['support'] = 'https://github.com/HeyPublisher/heypublisher-submission-manager/issues';
    $this->plugin['contact'] = 'mailto:support@heypublisher.com';
    $this->logger = $hp_config->logger;
    $this->config = $hp_config;
    $this->xml = $hp_xml;
    $this->nonce = sprintf('hp_nonce%s',$this->page);

    // TODO: Deprecate this!
    $this->log_file = HEYPUB_PLUGIN_FULLPATH . '/error.log';
  }

  public function __destruct() {
  	parent::__destruct();
  }

  protected function strip($var, $default = '') {
    return isset($var) ? stripslashes($var) : $default;
  }

  // Page wrapper
  public function page($title, $subtitle, callable $content, $args=null) {
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
    <div class="wrap heypub-page">
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
  protected function boolean_select($label,$key,$opts,$pre=false,$alt=false) {
    $name = 'heypub_opt';
    if ($pre) {
      $name = "heypub_opt[{$pre}]";
    }

    $html = <<<EOF
      <label class='heypub' for='hp_{$key}'>{$label}</label>
      <select name="{$name}[{$key}]" id="hp_{$key}">
      {$this->boolean_options($key,$opts)}
      </select>
EOF;
    if ($alt) {
      $html .= sprintf('&nbsp;<small>%s</small>',$alt);
    }
    return $html;
  }

  protected function print_message_if_exists() {
    if (!empty($this->warning)) {
      $this->display_message($this->warning,'warning');
    }
    if (!empty($this->message)) {
      $this->display_message($this->message,'success');
    }
  }

  private function display_message($message,$class) {
    $valid = array('success','error','warning');
    if (!in_array($class,$valid)) {
      $class = 'info';
    }
    $e = <<<EOF
      <div class="notice notice-{$class} is-dismissible">
        <p>
          {$message}
        </p>
      </div>
EOF;
    print($e);
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

  protected function get_nonced_field() {
    $nonce = wp_nonce_field($this->nonce);
    return $nonce;
  }

  protected function validate_nonced_field() {
    check_admin_referer($this->nonce);
  }

  // @since 2.8.0
  // Get the form action url as a relative url
  // Replaces function of same name in HeyPublisher class
  protected function get_form_url_for_page($action=null,$delete=null) {
    $additional = '';
    if ($action) {
      $additional = sprintf('&action=%s',$action);
    }
    $url = sprintf('admin.php?page=%s%s',$this->slug,$additional);
    if ($delete) {
      $url = sprintf('%s&delete=%s',$url,$delete);
      $url = wp_nonce_url($url,$this->nonce);
    }
    return $url;
  }
  // Get the page edit url as a relative url
  protected function get_edit_url_for_page($id){
    $url = '';
    if (!empty($id)) {
      $edit = get_edit_post_link($id);
      $view = get_permalink($id);
      $link = $this->get_external_url_with_icon($view);
      $url = sprintf(" <a href='%s' class='dashicons dashicons-edit
' title='Edit this Page'> </a> %s",$edit,$link);
    }
    return $url;
  }
  // Get external URL as a link
  protected function get_external_url_with_icon($link,$icon='dashicons-external'){
    $url = '';
    if (!empty($link)) {
      $url = sprintf(" <a href='%s' class='dashicons {$icon}' title='Opens in new page' target='_new'> </a>",$link);
    }
    return $url;
  }

}
?>
