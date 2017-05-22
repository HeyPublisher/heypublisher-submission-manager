<?php
/**
* HeyPublisherXML class for publishing/parsing XML
*
*/
class HeyPublisherXML {

  var $debug = false;
  var $svc_url = HEYPUB_SVC_URL_BASE;
  var $curl = false;
  var $error = false;
  var $user_oid = false;
  var $pub_oid = false;
  // map submission state transitions to meaningful values in UI
  var $submission_status = array(
    'unread' => 'New',
    'read' => 'Read',
    'under_consideration' => 'Under Review',
    'accepted' => 'Accepted',
    'rejected' => 'Rejected',
    'published' => 'Published',
    'publisher_revision_requested' => 'Revision Req.',
    'writer_revision_provided' => 'Revised by Author'
    );
  var $is_validated = false;

  var $config = array();
  var $install = array();
  /**
  * Ensure the CURL constructor is getting created/destroyed properly
  */
  public function __construct() {
    $this->curl = curl_init();
    $this->config = get_option(HEYPUB_PLUGIN_OPT_CONFIG);
    $this->install = get_option(HEYPUB_PLUGIN_OPT_INSTALL);
    $this->set_is_validated();
    // $this->log(sprintf("construct INSTALL Opts: %s",print_r($this->install,1)));
    // $this->log(sprintf("construct CONFIG Opts: %s",print_r($this->config,1)));
    register_shutdown_function(array($this,'save_option_state'));
  }
  public function __destruct() {

  }
  public function save_option_state() {
    // Register the shutdown functions
    // http://stackoverflow.com/questions/33231656/register-static-class-method-as-shutdown-function-in-php
    // http://us.php.net/manual/en/function.register-shutdown-function.php
    curl_close($this->curl);
    if ($this->install) {
      update_option(HEYPUB_PLUGIN_OPT_INSTALL,$this->install);
    }
    if ($this->config) {
      update_option(HEYPUB_PLUGIN_OPT_CONFIG,$this->config);
    }
  }

  protected function clean_local_vars($array) {
    $tmp = array();
    foreach ($array as $key=>$val) {
      if (is_scalar($val)) {
        $tmp[$key] = htmlentities(stripslashes($val));
      } else {
        $tmp[$key] = $val;
      }
    }
    return $tmp;
  }

  public function get_category_mapping() {
    if ($this->config[categories]) {
      return $this->config[categories];
    }
    else {
      return array();
    }
  }

  public function set_category_mapping($map) {
    $this->config[categories] = $map;
    return;
  }

  public function initialize_plugin() {
    $this->init_install_options();
    add_option(HEYPUB_PLUGIN_OPT_INSTALL,$this->install);
    $this->init_config_options();
    add_option(HEYPUB_PLUGIN_OPT_CONFIG,$this->config);
  }

  private function init_install_options(){
    $this->install = array(
      'version_last'    => 0,
      'version_current' => 0,
      'is_validated'    => null,
      'user_oid'        => null,
      'publisher_oid'   => null
    );
  }

  public function set_install_option($key,$val){
    $this->install[$key] = $val;
  }

  public function get_install_option($key){
    if ($this->install[$key]) {
      return $this->install[$key];
    }
    return false;
  }

  // Defines all of the allowable option keys
  private function config_options_definition() {
    $hash = array(
      'categories' => array(),
      'name'  => null,
      'url'   => null,
      'circulation' => null,
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
      'address' => null,
      'city' => null,
      'state' => null,
      'zipcode' => null,
      'country' => null,
      'twitter' => null,
      'facebook' => null,
      'rss' => null,
      'sub_page_id' => null,
      'sub_guide_id' => null,
      'seo_url' => null,
      'homepage_first_validated_at' => null,
      'homepage_last_validated_at' => null,
      'guide_first_validated_at' => null,
      'guide_last_validated_at' => null,
      // need to match default config in DB
      'notify_submitted' => true,
      'notify_read' => true,
      'notify_rejected' => true,
      'notify_published' => true,
      'notify_accepted' => true,
      'notify_under_consideration' => true,
      'turn_off_tidy' => false,
      'link_sub_to_edit' => true,           # don't think we're using this one??
      'display_download_link' => false,      # this is a local-only config
      'mailchimp_active' => false,
      'mailchimp_api_key' => null,
      'mailchimp_list_id' => null
    );
    return $hash;
  }
  private function init_config_options() {
    $this->config = $this->config_options_definition();
  }

  public function set_config_option($key,$val){
    $this->config[$key] = $val;
  }

  public function set_config_option_bulk($hash){
    $allowed = array_keys($this->config_options_definition());
    foreach ($hash as $key=>$val) {
      if (in_array($key,$allowed)) {
        $this->config[$key] = $val;
      }
    }
  }

  public function get_config_option($key){
    if ($this->config[$key]) {
      return $this->config[$key];
    }
    return false;
  }

  public function set_is_validated() {
    $this->user_oid = $this->install['user_oid'];
    $this->pub_oid = $this->install['publisher_oid'];
    if ($this->user_oid && $this->pub_oid) { $this->is_validated = $this->install['is_validated']; }
  }

  public function send($path,$post) {
    $return = false;
    $url = sprintf("%s/%s",$this->svc_url,$path);
    curl_setopt($this->curl, CURLOPT_URL, $url);
    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($this->curl, CURLOPT_TIMEOUT, 10);
    curl_setopt ($this->curl, CURLOPT_HTTPHEADER, array(
        "Accept: application/xml",
        "Content-Type: application/xml; charset=utf-8",
        'Content-Length: ' . strlen($post),
        'Connection: close'));
    curl_setopt($this->curl, CURLOPT_POSTFIELDS, $post);
    // Execute the request and also time the transaction
    $result = curl_exec($this->curl);
    $this->log(sprintf("send():\URL = %s\nPOST = %s",$url,print_r($post,1)));
    $this->log(sprintf("INFO:\n%s",print_r(curl_getinfo($this->curl),1)));
    // $this->log(sprintf("RESULT: %s",$result));
    // Check for errors
    if ( curl_errno($this->curl) ) {
      $this->error = 'HeyPublisher Service ERROR : ' . curl_error($this->curl);
    }
    else {
     $http_code = (int)curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
     switch($http_code){
       case 200:
        $return = $result;
         break;
       default:
         $this->error = 'HeyPublisher POST ERROR Code : ' . $http_code;
         break;
     }
    }
    if (FALSE != $this->error) {
      $trace=debug_backtrace();
      $this_func = array_shift($trace);
      $caller=array_shift($trace);
      $this->error = sprintf('%s<br/>BAD RETURN: %s()',$this->error,$caller['function']);
    }
    return $return;
  }

  /**
  * This is the only method which does not call prepare_request_xml - as we have a custom <account> section
  */
  function authenticate($user) {
    $return = false;
    // authentication is based upon username, password, and token
    $xml_ops = array(
      'token'         => HEYPUB_SVC_TOKEN_VALUE,
      # no htmlentities here, otherwise " becomes %quote; and cronks the seo name
      'publishername' => stripslashes($this->get_config_option('name')),
      'url'           => htmlentities(stripslashes($this->get_config_option('url'))),
      'email'         => htmlentities(stripslashes($user['username'])),
      'password'      => htmlentities(stripslashes($user['password'])),
      'version'       => HEYPUB_PLUGIN_VERSION,
      'build'         => HEYPUB_PLUGIN_BUILD_NUMBER
      );

    $xml_parts = '';
    foreach($xml_ops as $key=>$val) {
      $xml_parts .= "<$key>".htmlentities($val)."</$key>";
    }

    $post = "<?xml version='1.0' encoding='UTF-8'?><request><account>$xml_parts</account></request>";
    $ret = $this->send(HEYPUB_SVC_URL_AUTHENTICATE,$post);
    if (FALSE == $ret) {
      $this->print_webservice_errors();
    }
    else {
      $xml = new SimpleXMLElement($ret);
      // printf( "<pre>XML = %s</pre>",print_r($xml,1));
      # this is an object, convert to string
      if ($xml->account->oid && $xml->publisher->oid) {
        $this->user_oid = sprintf('%s',$xml->account->oid);
        $this->pub_oid = sprintf('%s',$xml->publisher->oid);
        $return = true;  # calling code will need to get the oids out of the class directly
      }
      else {
        $err = $xml->error->message;
        if ($err) {
          $this->error = "$err";
        } else {
          $this->error = 'Did not receive authentication from HeyPublisher.com';
        }
        $this->print_webservice_errors();
      }
    }
    return $return;
  }

  function update_publisher_mailchimp($post) {
    $xml = null;
    $active =   htmlentities(stripslashes($post[mailchimp_active]));
    $api_key =  htmlentities(stripslashes($post[mailchimp_api_key]));
    $list_id =  htmlentities(stripslashes($post[mailchimp_list_id]));
    // if ($post[mailchimp_active]) {
    $xml =<<< EOF
    <mailchimp>
      <active>{$active}</active>
      <api_key>{$api_key}</api_key>
      <list_id>{$list_id}</list_id>
    </mailchimp>
EOF;
    // }
    return $xml;
  }

  function update_publisher_categories($post) {
    $ret = null;
    if ($post[accepting_subs] && $post[genres_list]) {
      $cat_array = array();
      foreach ($post[genres_list] as $name => $id) {
        $cat_array[] = sprintf('<category>%s</category>', $id);
      }
      if (FALSE != $cat_array) {
        $ret = sprintf('<categories>%s</categories>',join('',$cat_array));
      }
    }
    return $ret;
  }

  function update_publisher_reading_period($post) {
    $bool = $this->boolean($post[reading_period]);
    if ($post[reading_period]) {
      $start = $post['start_date'];
      $end = $post['end_date'];
      $ret = "<reading_period><reading_start_date>$start</reading_start_date><reading_end_date>$end</reading_end_date></reading_period>";
    } else {
      $ret = "<reading_period>$bool</reading_period>";
    }
    return $ret;
  }
  /**
  * convert boolean vals into strings reading 'true' or 'false'
  */
  function boolean($val) {
    if (isset($val) and $val != FALSE) {
      return 'true';
    } else {
      return 'false';
    }
  }
  function update_publisher_paying_market($post) {
    $bool = $this->boolean($post[paying_market]);
    if ($post[paying_market]) {
      $val = htmlentities(stripslashes($post[paying_market_range]));
      $ret = "<paying_market><paying_market_amount>$val</paying_market_amount></paying_market>";
    } else {
      $ret = "<paying_market>$bool</paying_market>";
    }
    return $ret;
  }

  function prepare_request_xml($post,$suppress_publisher=false) {
    $account = $this->get_account_request_header();
    if (FALSE == $suppress_publisher) {
      $publisher = $this->get_publisher_request_header();
    }
    $ret = sprintf('<?xml version="1.0" encoding="UTF-8"?><request>%s%s%s</request>',$account,$publisher,$post);
    return $ret;
  }

  function get_publisher_request_header() {
    $version = HEYPUB_PLUGIN_VERSION;
    $ret = <<<EOF
    <publisher>
        <oid>$this->pub_oid</oid>
        <version>$version</version>
    </publisher>
EOF;
    return $ret;
  }

  function get_account_request_header() {
    $ret = <<<EOF
    <account>
        <oid>$this->user_oid</oid>
    </account>
EOF;
    return $ret;
  }

  // When uninstalling plugin we also supress errors.
  function update_publisher($post,$uninstall_plugin=false) {
    $categories = $this->update_publisher_categories($post);
    $mailchimp = $this->update_publisher_mailchimp($post);
    $reading = $this->update_publisher_reading_period($post);
    $simulsubs = $this->boolean($post[simu_subs]);
    $multisubs = $this->boolean($post[multi_subs]);
    $reprints = $this->boolean($post[reprint_subs]);
    $accepting_subs  = $this->boolean($post[accepting_subs]);
    $paying = $this->update_publisher_paying_market($post);
    # no htmlentities here, otherwise " becomes %quote; and cronks the seo name
    $name = stripslashes($post[name]);
    $issn = htmlentities(stripslashes($post[issn]));
    $established = htmlentities(stripslashes($post[established]));
    $editor = htmlentities(stripslashes($post[editor_name]));
    $editor_email = htmlentities(stripslashes($post[editor_email]));
    $address = htmlentities(stripslashes($post[address]));
    $city = htmlentities(stripslashes($post[city]));
    $state = htmlentities(stripslashes($post[state]));
    $zipcode = htmlentities(stripslashes($post[zipcode]));
    $country = htmlentities(stripslashes($post[country]));
    $twitter = htmlentities(stripslashes($post[twitter]));
    $facebook = htmlentities(stripslashes($post[facebook]));
    $url = htmlentities(stripslashes($post[url]));

    $uninstall = '';
    if ($uninstall_plugin) {
      $uninstall = '<uninstall_plugin>true</uninstall_plugin>';
    }

    $post = <<<EOF
<publisher>
    <oid>$this->pub_oid</oid>
    <publishertype_id>$post[pub_type]</publishertype_id>
    <name>$name</name>
    <url>$url</url>
    <issn>$issn</issn>
    <established>$established</established>
    <circulation>$post[circulation]</circulation>
    <sub_guideline>$post[guide]</sub_guideline>
    <editor>$editor</editor>
    <editor_email>$editor_email</editor_email>
    <accepts_simultaneous_submissions>$simulsubs</accepts_simultaneous_submissions>
    <accepts_multiple_submissions>$multisubs</accepts_multiple_submissions>
    <accepts_reprints>$reprints</accepts_reprints>
    <now_accepting_submissions>$accepting_subs</now_accepting_submissions>
    <address>$address</address>
    <city>$city</city>
    <state>$state</state>
    <zipcode>$zipcode</zipcode>
    <country>$country</country>
    <twitter>$twitter</twitter>
    <facebook>$facebook</facebook>
    <submission_url>$post[submission_url]</submission_url>
    <submission_product>HeyPublisher</submission_product>
    <platform>wordpress</platform>
    <turn_off_tidy>$post[turn_off_tidy]</turn_off_tidy>
    <notify_submitted>1</notify_submitted>
    <notify_read>$post[notify_read]</notify_read>
    <notify_rejected>$post[notify_rejected]</notify_rejected>
    <notify_published>$post[notify_published]</notify_published>
    <notify_accepted>$post[notify_accepted]</notify_accepted>
    <notify_under_consideration>$post[notify_under_consideration]</notify_under_consideration>
    {$categories}
    {$reading}
    {$paying}
    {$uninstall}
    {$mailchimp}
</publisher>
EOF;

    $ret = $this->send(HEYPUB_SVC_URL_UPDATE_PUBLISHER,$this->prepare_request_xml($post,true));
    $this->log(sprintf("updating publisher results = \n%s",print_r($ret,1)));
    if (FALSE == $ret && FALSE == $uninstall_plugin) {
      $this->print_webservice_errors();
    }
    else {
      $xml = new SimpleXMLElement($ret);
      // $this->log(sprintf("XML results from update_publisher() \n%s",print_r($xml,1)));
      // printf( "<pre>XML = %s</pre>",print_r($xml,1));
      # this is an object, convert to string
      if ($xml->success->message) {
        $ret = $xml->success->message;
        $return = "$ret";
      }
      else {
        $err = $xml->error->message;
        if ($err) {
          $this->error = "$err";
        } else {
          $this->error = 'Error updating publisher data at HeyPublisher.com';
        }
        if (FALSE == $uninstall_plugin) {
          $this->print_webservice_errors();
        }
      }
    }
    return $return;
  }

function get_publisher_info() {
  $post = '';
  $return = array();
  $ret = $this->send(HEYPUB_SVC_URL_GET_PUBLISHER,$this->prepare_request_xml($post));
  // $this->log(sprintf("get_publisher_info() params = \n%s\nget_publisher_info() RESULTS: %s",print_r($this->prepare_request_xml($post),1),$ret));
  if (FALSE == $ret) {
    $this->print_webservice_errors();
  }
  else {
    $xml = new SimpleXMLElement($ret);
    // printf("<pre>RAW XML = %s</pre>",htmlentities($ret));
    # this is an object, convert to string
    if ($xml->success->message) {
      foreach ($xml->publisher->children() as $x) {
        $name = $x->getName();
        $return["$name"] = "$x";
      }
    }
    else {
      $err = $xml->error->message;
      if ($err) {
        $this->error = "$err";
      } else {
        $this->error = 'Error retrieving publisher info from HeyPublisher.com';
      }
      $this->print_webservice_errors();
    }
  }
  return $return;
}


  function normalize_submission_status($val) {
    if ($this->submission_status["$val"]) {
      return $this->submission_status["$val"];
    } else {
      return 'Unknown';
    }
  }

  function get_recent_submissions() {
    $post = <<<EOF
<submissions>
    <sort>date</sort>
    <sort_direction>DESC</sort_direction>
    <filter>unread</filter>
</submissions>
EOF;

    $ret = $this->send(HEYPUB_SVC_URL_GET_SUBMISSIONS,$this->prepare_request_xml($post));
    if (FALSE == $ret) {
      $this->print_webservice_errors();
    }
    else {
      $this->log(sprintf("RAW XML = \n%s",$ret));
      $xml = new SimpleXMLElement($ret);
      // printf( "<pre>XML = %s</pre>",print_r($xml,1));
      # this is an object, convert to string
      if ($xml->success->message) {
        $cnt = $xml->success->records;
        if ("$cnt" > 0) {
          $hash = array();
          foreach ($xml->submission as $x) {
            $hash["$x->id"] = $x;
          }
        }
        if ($hash != FALSE) {
          $return = $hash;
        }
      }
      else {
        $err = $xml->error->message;
        if ($err) {
          $this->error = "$err";
        } else {
          $this->error = 'Error updating publisher data at HeyPublisher.com';
        }
        $this->print_webservice_errors();
      }
    }
    return $return;
  }

  function get_submission_by_id($id) {
    $post = <<<EOF
<submission>
    <id>$id</id>
</submission>
EOF;

    $ret = $this->send(HEYPUB_SVC_READ_SUBMISSION,$this->prepare_request_xml($post));
    if (FALSE == $ret) {
      $this->print_webservice_errors();
    }
    else {
      $xml = new SimpleXMLElement($ret);
      $this->log(sprintf("RAW XML = \n%s",$ret));
      // printf( "<pre>XML = %s</pre>",print_r($xml,1));
      # this is an object, convert to string
      if ($xml->success->message) {
        $return = $xml->submission;
      }
      else {
        $err = $xml->error->message;
        if ($err) {
          if ($err == '403 Forbidden') {
            $this->error = "The content of this submission is temporarily unavailable (# $id).";
            // $return = $xml->submission; // don't return content - nothing editor can do with this
          } else {
            $this->error = "$err";
          }
        } else {
          $this->error = 'Error retrieving submission for reading from HeyPublisher.com';
        }
        $this->print_webservice_errors(true,$id);
      }
    }
    return $return;
  }

  // TODO: consolidate this with Page::print_message()
  function print_webservice_errors($show_contact=true,$id=null) {
    $contact = null;
    if ($show_contact) {
      $email = HEYPUB_PLUGIN_ERROR_CONTACT;
      $contact = <<<EOF
        <p>
          Please contact
          <a href="mailto:{$email}?subject=plugin%20error%20{$id}">
            support@heypublisher.com
          </a>
          for further information.
        </p>
EOF;
    }
    $e = <<<EOF
      <div id="message" class="error">
        <p>
          <b>ERROR:</b>
          {$this->error}
        </p>
        {$contact}
      </div>
EOF;
    print($e);
  }

  /**
  * Pass in the SimpleXML element object and the 'key' wanting to fetch
  */
  function get_attribute_value_by_name($elem,$key){
    foreach ($elem->attributes() as $a => $b) {
      if ($a == $key) {
        return $b;
      }
    }
    return false;
  }


  /**
  * Fetch the hash of 'all' publisher types, plus the publisher type associated with this publication suitable for making drop-down list with.
  */
  function get_my_publisher_types_as_hash() {
      $return = array();
      $post = <<<EOF
<publisher_types>
    <sort>name</sort>
    <sort_direction>ASC</sort_direction>
    <filter>both</filter>
</publisher_types>
EOF;
    $ret = $this->send(HEYPUB_SVC_URL_GET_PUB_TYPES,$this->prepare_request_xml($post));
    if (FALSE == $ret) {
      $this->print_webservice_errors();
    }
    else {
      $xml = new SimpleXMLElement($ret);
      // printf("<pre>RAW XML = %s</pre>",htmlentities($ret));
      # this is an object, convert to string
        if ($xml->success->message) {
          // First get ALL of the possible categories
          foreach ($xml->all->publisher_type as $x) {
            $id = $this->get_attribute_value_by_name($x,'id');
            if ($id) {
              $return["$x"] = array('name' => "$x", 'id' => "$id");
            }
          }
          // We man not yet have submission categories defined remotely (if this is an initial install) - so account for that.
          if ($xml->mine->publisher_type) {
            foreach ($xml->mine->publisher_type as $x) {
              $id = $this->get_attribute_value_by_name($x,'id');
              if ($id) {
                $return["$x"]['has'] = 1;
              }
            }
          }
        }
        else {
          $err = $xml->error->message;
          if ($err) {
            $this->error = "$err";
          } else {
            $this->error = 'Error getting publisher data from HeyPublisher.com';
          }
          $this->print_webservice_errors();
        }
      }
      ksort($return);
      // printf("<pre>Hash = %s</pre>",print_r($return,1));
      return $return;
  }

  /**
  * Fetch the hash of 'all' categories, plus the categories this publisher belongs to, and return as a hash
  * suitable for making checkboxes with.
  */
  function get_my_categories_as_hash() {
      $return = false;
      $post = <<<EOF
<categories>
    <sort>name</sort>
    <sort_direction>ASC</sort_direction>
    <filter>both</filter>
</categories>
EOF;
    $ret = $this->send(HEYPUB_SVC_URL_GET_GENRES,$this->prepare_request_xml($post));
    if (FALSE == $ret) {
      $this->print_webservice_errors();
    }
    else {
      $xml = new SimpleXMLElement($ret);
      // printf( "<pre>XML = %s</pre>",print_r($xml,1));
      # this is an object, convert to string
        if ($xml->success->message) {
          // First get ALL of the possible categories
          foreach ($xml->all->category as $x) {
            $id = $this->get_attribute_value_by_name($x,'id');
            if ($id) {
              $return["$x"] = array('name' => "$x", 'id' => "$id");
            }
          }
          // We may not yet have submission categories defined remotely (if this is an initial install) - so account for that.
          if ($xml->mine->category) {
            foreach ($xml->mine->category as $x) {
              $id = $this->get_attribute_value_by_name($x,'id');
              if ($id) {
                $return["$x"]['has'] = 1;
              }
            }
          }
        }
        else {
          $err = $xml->error->message;
          if ($err) {
            $this->error = "$err";
          } else {
            $this->error = 'Error getting publisher data from HeyPublisher.com';
          }
          $this->print_webservice_errors();
        }
      }
      if (FALSE != $return) {
        ksort($return);
      }
      // printf("<pre>Hash = %s</pre>",print_r($return,1));
      return $return;
  }

  // Process the submission action with an optional message
  function submission_action($id,$action,$message=false) {
      $return = false;
      if (!$this->submission_status[$action]) {
        $this->error = sprintf('%s is an invalid action',$action);
        // $this->print_webservice_errors();
        return $return;
      }
      if ($message) {
        $notify = sprintf('<notify_author><message><![CDATA[%s]]></message></notify_author>', htmlentities(stripslashes($message)));
      } else {
        $notify = '<notify_author>false</notify_author>';
      }
      $post = <<<EOF
<submission>
    <id>$id</id>
    <action>$action</action>
    $notify
</submission>
EOF;

    // printf("<pre>XML request to webservice = %s</pre>",htmlentities($post));
    $ret = $this->send(HEYPUB_SVC_URL_RESPOND_TO_SUBMISSION,$this->prepare_request_xml($post));
    if (FALSE == $ret) {
      $this->print_webservice_errors();
    }
    else {
      $xml = new SimpleXMLElement($ret);
      // printf("<pre>RAW XML = %s</pre>",htmlentities($ret));
      // printf( "<pre>XML = %s</pre>",print_r($xml,1));
      # this is an object, convert to string
        if ($xml->success->message) {
          $ret = $xml->success->message;
          $return = "$ret";
        }
        else {
          $err = $xml->error->message;
          if ($err) {
            $this->error = "$err";
          } else {
            $this->error = 'Error updating submission status at HeyPublisher.com';
          }
          $this->print_webservice_errors();
        }
      }
      return $return;
  }

  // Get a publisher name into the format expected by HeyPub search
  public function searchable($string) {
    $string = preg_replace('/[^0-9a-zA-Z\s]+/','',html_entity_decode($string,ENT_QUOTES));
    $string = preg_replace('/ /','+',$string);
    return $string;
  }
  // logging function
  public function log($msg) {
    if ($this->debug) {
      error_log(sprintf("%s\n",$msg),3,HEYPUB_PLUGIN_FULLPATH . '/error.log');
    }
  }
  // Called after calling HeyPublisher.  This will ensure whatever data is on
  // remote server is synced locally 'after' a save with remote server.
  // only select fields are synced this way
  public function sync_publisher_info() {
    $p = $this->get_publisher_info();
    if ($p) {
      $this->set_config_option('seo_url',$p[seo_url]);
      $this->set_config_option('homepage_first_validated_at',$p[homepage_first_validated_at]);
      $this->set_config_option('homepage_last_validated_at',$p[homepage_last_validated_at]);
      $this->set_config_option('guide_first_validated_at',$p[guide_first_validated_at]);
      $this->set_config_option('guide_last_validated_at',$p[guide_last_validated_at]);
      // we won't store total subs and open subs
      // xml.avg_response_days(-1)   # we'll calculate this later
      // xml.avg_acceptance_rate(-1) # we'll calculate this later
      // xml.writer_comments(@pub.comments.count(:include => [:comment_type],:conditions=>["comment_types.name = 'public'"]))
      // xml.writer_favorites(@pub.user_publishers.count)
    }
    return $p;
  }
}
?>
