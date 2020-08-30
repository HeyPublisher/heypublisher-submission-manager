<?php
namespace HeyPublisher;

if (!class_exists("\HeyPublisher\Base\Log")) {
  require_once( dirname(__FILE__) . '/Base/Log.class.php');
}

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

/**
* Base class for all HeyPublisher plugins.  Provides common layout and functionality
*
*/
class Base {
  var $logger = null;
  var $help = false;
  var $i18n = 'heypublisher';  // key for internationalization stubs
  var $plugin = array(
    'url' => 'https://www.heypublisher.com',
    'home' => 'https://wordpress.org/plugins/',
    'support' => 'https://wordpress.org/support/',
    'contact' => 'mailto:wordpress@heypublisher.com',
    'more' => 'https://profiles.wordpress.org/heypublisher#content-plugins'
  );
  var $slug = '';   // should be defined in constructor of any class that extends this class.

  public function __construct() {
    global $HEYPUB_LOGGER;
    // this can't be instantiated in var declaration
    // $this->plugin['url'] = plugins_url('../../',__FILE__);
    $this->logger = $HEYPUB_LOGGER;
    $this->logger->debug("HeyPublisher::Base loaded");
  }

  public function __destruct() {

  }

  /**
   * Style the header of the content box in a consistent way
   */
  public function box_header($id, $title) {
    $handle = '';
    if ($title) {
      $handle = <<<EOF
        <h3 class="hndle"><span>{$title}</span></h3>
EOF;
    }

    $text = <<<EOF
  		<div id="{$id}" class="postbox">
  			{$handle}
  			<div class="inside ll-inside">
EOF;
    return $text;
	}
  /**
   * Style the header of the content box in a consistent way
   */
	public function box_footer() {
    $text = <<<EOF
				</div>
			</div>
EOF;
    return $text;
	}

  /**
   * Style the side-bar link appropriately
   */
  public function sidebar_link($key,$link,$text) {
    $text = sprintf('<a class="heypublisher-button" href="%s" target="_blank"><span class="dashicons dashicons-%s"></span>%s</a>',$link,$key,__($text,$this->i18n));
    return $text;
  }

  // TODO: move styles to included stylesheet
  public function standard_sidenav_info_block($id,$title,$additional='') {
    $html = '';
    if ($id && $title) {
      $html .= <<<EOF
        {$this->box_header($id,$title)}
        <ul>
          <li>{$this->sidebar_link('admin-home',$this->plugin['home'],'Plugin Homepage')}</li>
          <li>{$this->sidebar_link('testimonial',$this->plugin['support'],'Support')}</li>
          <li>{$this->sidebar_link('email',$this->plugin['contact'],'Contact Us')}</li>
          <li>{$this->sidebar_link('search',$this->plugin['more'],'More Plugins by Us')}</li>
        </ul>
        {$additional}
        {$this->box_footer()}
EOF;
    }
    return $html;
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

  protected function form_action() {
    $action = sprintf('admin.php?page=%s',$this->slug);
    return $action;
  }

  // display sidenav in consistent way
  public function sidenav_block() {
    $url = plugins_url($this->slug);
    $header =<<< EOF
    <a style='heypublisher-home' href="{$this->plugin['url']}" target='_blank'>
      <img src="{$url}/images/logo.jpg">
    </a>
EOF;
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
    $id = sprintf("%s-left",$this->slug);
    $html = <<<EOF
    <!-- START Left Side -->
    <div class="has-sidebar sm-padded">
      <div id="post-body-content" class="has-sidebar-content ll-plugin">
        <div class="meta-box-sortabless">
          {$this->box_header($id,__($title,$this->i18n))}
          {$content}
          {$this->box_footer()}
        </div>
      </div>
    </div>
EOF;
    return $html;
  }

}
?>
