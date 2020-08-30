<?php
namespace Loudlever;

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('Loudlever: Illegal Page Call!'); }

/**
* Loudlever :  base class for all loudlever plugins.  provides common functionality.
* TODO: Convert this over to using HeyPublisher/Base class
*/
class Loudlever {
  var $debug = true;
  var $help = false;
  var $i18n = 'loudlever';             // key for internationalization stubs
  var $log_file = '';
  var $plugin = array(
    'url' => '',
    'home' => 'https://github.com/HeyPublisher/',
    'support' => 'https://github.com/HeyPublisher/',
    'contact' => 'mailto:support@heypublisher.com',
    'more' => 'https://github.com/HeyPublisher'
  );

  public function __construct() {
    // this can't be instantiated in var declaration
    $this->plugin['url'] = plugins_url('../../../',__FILE__);
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
   * Logging function
   */
  public function log($msg) {
    if ($this->debug && $this->log_file) {
      error_log(sprintf("%s\n",$msg),3,$this->log_file);
    }
  }

  /**
   * Style the side-bar link appropriately
   */
  public function sidebar_link($key,$link,$text) {
    $text = sprintf('<a class="loudlever-button" href="%s" target="_blank"><span class="dashicons dashicons-%s"></span>%s</a>',$link,$key,__($text,$this->i18n));
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
      // $html .= "</ul>";
      // $html .= $this->box_footer();
    }
    // $this->log(sprintf("HTML = %s",$html));
    return $html;
  }
}
?>
