<?php
/**
* HeyPublisherXML class for publishing/parsing XML
*
*/
class HeyPublisherXML {

  var $debug = false;
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
    'writer_revision_provided' => 'Revised by Author',
    'publisher_withdrew' => 'Withdrawn by Author',
    );

  var $config = array();
  var $install = array();
  /**
  * Ensure the CURL constructor is getting created/destroyed properly
  */
  public function __construct() {
    global $hp_config;
    $this->curl = curl_init();
    // TODO: Remove this!!
    $this->config = $hp_config->config;
    $this->install = $hp_config->install;
    $this->user_oid = $hp_config->uoid;
    $this->pub_oid = $hp_config->poid;

    // $this->log(sprintf("construct INSTALL Opts: %s",print_r($this->install,1)));
    // $this->log(sprintf("construct CONFIG Opts: %s",print_r($this->config,1)));
    register_shutdown_function(array($this,'save_option_state'));
  }
  public function __destruct() {

  }
  // Ensure any open cUrl connections are closed
  public function save_option_state() {
    // Register the shutdown functions
    // https://stackoverflow.com/questions/33231656/register-static-class-method-as-shutdown-function-in-php
    // https://us.php.net/manual/en/function.register-shutdown-function.php
    curl_close($this->curl);
  }

  //  fetch the mapping of categories to genres from local db
  // TODO: this is should be updated to pull from Config class configuration
  public function get_category_mapping() {
    if ($this->config['categories']) {
      return $this->config['categories'];
    }
    else {
      return array();
    }
  }


  public function send($path,$post) {
    $return = false;
    $svc_url = sprintf("%s/api/v1",HEYPUB_DOMAIN);
        $url = sprintf("%s/%s",$svc_url,$path);
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
    $this->log(sprintf("HeyPublisherXML => send():\nURL = %s\nPOST = %s",$url,print_r($post,1)));
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
    global $hp_config;
    $return = false;
    // authentication is based upon username, password, and token
    $xml_ops = array(
      'token'         => HEYPUB_SVC_TOKEN_VALUE,
      # no htmlentities here, otherwise " becomes %quote; and cronks the seo name
      'publishername' => stripslashes($hp_config->get_config_option('name')),
      // TODO: Fix this!! url is now a nested var
      'url'           => htmlentities(stripslashes($hp_config->get_config_option('url'))),
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
    $active =   htmlentities(stripslashes($post['mailchimp_active']));
    $api_key =  htmlentities(stripslashes($post['mailchimp_api_key']));
    $list_id =  htmlentities(stripslashes($post['mailchimp_list_id']));
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
    if ($post['accepting_subs'] && $post['genres_list']) {
      $cat_array = array();
      foreach ($post['genres_list'] as $name => $id) {
        $cat_array[] = sprintf('<category>%s</category>', $id);
      }
      if (FALSE != $cat_array) {
        $ret = sprintf('<categories>%s</categories>',join('',$cat_array));
      }
    }
    return $ret;
  }

  function update_publisher_reading_period($post) {
    $bool = $this->boolean($post['reading_period']);
    if ($post['reading_period']) {
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
    $bool = $this->boolean($post['paying_market']);
    if ($post['paying_market']) {
      $val = htmlentities(stripslashes($post['paying_market_range']));
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
          You can check <a href='https://uptime.statuscake.com/?TestID=oQBfCXVK2A' target='_blank'>our status page</a>
          for more information.  Or contact
          <a href="mailto:{$email}?subject=plugin%20error%20{$id}">
            support@heypublisher.com
          </a>
          for assistance.
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
  * Fetch the hash of 'all' categories, plus the categories this publisher belongs to, and return as a hash
  * suitable for making checkboxes with.
  */
  // Deprecated with 3.0
  // TODO: Need to remove all calls to this function
  // Remove prior to 3.0.1
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
      if (!$this->submission_status["$action"]) {
        $this->error = sprintf('%s is an invalid action',$action);
        // $this->print_webservice_errors();
        return $return;
      }
      if ($message) {
        $notify = sprintf('<notify_author><message><![CDATA[%s]]></message></notify_author>', htmlentities(stripslashes($message)));
      } else {
        $notify = '<notify_author>false</notify_author>';
      }
      $editor_id = get_current_user_id();
      $post = <<<EOF
<submission>
    <id>$id</id>
    <action>$action</action>
    $notify
    <editor_id>{$editor_id}</editor_id>
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
  // TODO: reorganize these keys and deprecate prior to 3.0.1
  // These are referenced in 2 places in code??
  // Called after calling HeyPublisher.  This will ensure whatever data is on
  // remote server is synced locally 'after' a save with remote server.
  // only select fields are synced this way
  public function sync_publisher_info($stats) {
    if ($statistics['homepage']) {
      if ($statistics['homepage']['added']) {
        $this->set_config_option('homepage_first_validated_at',$statistics['homepage']['added']);
      }
      if ($statistics['homepage']['updated']) {
        $this->set_config_option('homepage_last_validated_at',$statistics['homepage']['updated']);
      }
    }
    if ($statistics['guidelines']) {
      if ($statistics['guidelines']['added']) {
        $this->set_config_option('guide_first_validated_at',$statistics['guidelines']['added']);
      }
      if ($statistics['guidelines']['updated']) {
        $this->set_config_option('guide_last_validated_at',$statistics['guidelines']['updated']);
      }
    }
  }
}
?>
