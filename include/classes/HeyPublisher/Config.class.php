<?php
namespace HeyPublisher;

require_once(HEYPUB_PLUGIN_FULLPATH . '/include/classes/HeyPublisher/Log.class.php');

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

/**
 * HeyPublisher Config class is for storing/retrieving configs from WP database
 */

class Config {
  var $config = array();
  var $install = array();
  var $logger = null;
  var $error = false;
  var $uoid = null;
  var $poid = null;

  public function __construct() {
    $this->logger = new \HeyPublisher\Log();
    $this->logger->debug("Config#__construct");
    $this->install = get_option(HEYPUB_PLUGIN_OPT_INSTALL);
    $this->logger->debug(sprintf("\tloading install options : %s",print_r($this->install,1)));

    $this->config = get_option(HEYPUB_PLUGIN_OPT_CONFIG);

    $this->uoid = $this->install['user_oid'];
    $this->poid = $this->install['publisher_oid'];
    $this->logger->debug(sprintf("\tuoid = %s\n\tpoid = %s",$this->uoid, $this->poid));
    register_shutdown_function(array($this,'shutdown'));
  }

  public function __destruct() {

  }

  // ------------------------------------------------------
  // PUBLIC FUNCTIONS
  // ------------------------------------------------------
  // Initialize the plugin by creating the default keys in the WP db
  public function initialize() {
    $this->install = $this->install_hash_init();
    add_option(HEYPUB_PLUGIN_OPT_INSTALL,$this->install);

    $this->config = $this->config_hash_init();
    add_option(HEYPUB_PLUGIN_OPT_CONFIG,$this->config);
  }

  // Get the requested key from the install hash
  public function get_install_option($key){
    if ($this->install["$key"]) {
      return $this->install["$key"];
    }
    return false;
  }

  // Get the requested key from the config hash
  public function get_config_option($key){
    if ($this->config["$key"]) {
      return $this->config["$key"];
    }
    return false;
  }

  // Fetch the current state of the options hash
  public function get_config_options(){
    return $this->config;
  }

  // Set a single key in the config option hash
  // We don't care about validation here, as validation is done prior to save to DB
  public function set_config_option($key,$val){
    $this->config["$key"] = $val;
  }

  // Set multiple keys in the config option hash
  // We don't care about validation here, as validation is done prior to save to DB
  public function set_config_options($hash){
    $existing = $this->config;
    $new = array_merge($existing,$hash);
    $this->logger->debug(sprintf("HeyPublisherXML#set_config_options():\n\texisting config %s\n\tnew config",print_r($existing,1),print_r($new,1)));
    $this->config = $new;
  }

  // Set a value for an install key, if valid
  public function set_install_options($hash){
    $existing = $this->install;
    $new = array_merge($existing,$hash);
    $this->logger->debug(sprintf("HeyPublisherXML#set_install_options():\n\texisting install %s\n\tnew install",print_r($existing,1),print_r($new,1)));
    $this->install = $new;
  }

  // Register the shutdown functions - must be public function
  public function shutdown() {
    $this->logger->debug("HeyPublisher::Config#shutdown");
    if ($this->install) {
      $insta = array();
      $allowed = $this->install_hash_init();
      foreach ($this->install as $key=>$val) {
        if (array_key_exists($key,$allowed)) {
          $insta["$key"] = $val;
        }
      }
      $this->logger->debug(sprintf("\tsaving install options : %s",print_r($insta,1)));
      update_option(HEYPUB_PLUGIN_OPT_INSTALL,$insta);
    }
    // Saves the configuration options (see: config_hash_init() for definition)
    if ($this->config) {
      $config = array();
      $allowed = $this->config_hash_init();
      $override = $this->config_hash_override();

      // $this->logger->debug(sprintf("\tpre-save config : %s",print_r($this->config,1)));

      foreach ($this->config as $key=>$val) {
        if (in_array($key,$override)) { // stuff it in
          // $this->logger->debug(sprintf("\tkey '%s' in override",$key));
          $config["$key"] = $val;
        }
        elseif (array_key_exists($key,$allowed)) {
          if ( is_array($val)  ) {
            foreach ($val as $key2=>$val2) {
              if (array_key_exists($key2,$allowed["$key"])) {
                // $this->logger->debug(sprintf("\tkey '%s' in nested hash",$key2));
                $config["$key"]["$key2"] = $val2;
              }
            }
          } else {
            // $this->logger->debug(sprintf("\tkey '%s' in main hash",$key));
            $config["$key"] = $val;
          }
        }
      }
      $this->logger->debug(sprintf("\tsaving config : %s",print_r($config,1)));
      update_option(HEYPUB_PLUGIN_OPT_CONFIG,$config);
    }
  }

  // ------------------------------------------------------
  // PRIVATE FUNCTIONS
  // ------------------------------------------------------

  // Defines all of the allowable install keys
  private function install_hash_init(){
    $install = array(
      'is_validated'          => null,
      'version_current'       => 0,
      'version_current_date'  => null,
      'version_last'          => 0,
      'version_last_date'     => null,
      'publisher_oid'         => null,
      'user_oid'              => null
    );
    return $install;
  }

  // Defines hash keys we won't check when saving (used for caching)
  private function config_hash_override() {
    $hash = array(
      'category_map',
      'genre_types',
      'publication_types'
    );
    return $hash;
  }


  // Defines all of the allowable option keys
  private function config_hash_init() {
    $hash = array(
      'name'  => null,
      'readership' => null,
      'issn' => null,
      'established' => null,
      'editor_name' => null,
      'editor_email' => null,
      'accepting_subs' => true,
      'reading_period' => null,
      'simu_subs' => true,
      'multi_subs' => true,
      'reprint_subs' => true,
      'paying_market' => false,
      'paying_market_range' => null,
      'address' => array(
        'street'  => null,
        'city'    => null,
        'state'   => null,
        'country' => null,
        'zipcode' => null
      ),
      'urls'  => array (
        'website' => null,
        'twitter' => null,
        'facebook' => null,
        'rss' => null
      ),
      'notifications' => array(
        'read' => true,
        'considered' => true,
        'accepted' => true,
        'rejected' => true,
        'published' => true,
        'withdrawn' => true
      ),
      'sub_page_id' => null,
      'sub_guide_id' => null,
      'seo_url' => null,
      'homepage_first_validated_at' => null,
      'homepage_last_validated_at' => null,
      'guide_first_validated_at' => null,
      'guide_last_validated_at' => null,
      // need to match default config in DB
      'turn_off_tidy' => false,
      'link_sub_to_edit' => true,           # don't think we're using this one??
      'display_download_link' => false,     # this is a local-only config
      'mailchimp_active' => false,
      'mailchimp_api_key' => null,
      'mailchimp_list_id' => null
    );
    return $hash;
  }



}
?>
