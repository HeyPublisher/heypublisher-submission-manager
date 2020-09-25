<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

add_filter('the_content', 'heypub_display_form');

/**
* Parse the content and if it matches our place-holder, display our form instead
*/
function heypub_display_form($content='') {


   if (preg_match(HEYPUB_SUBMISSION_PAGE_REPLACER, $content) > 0 ) {
      $sub_form = heypub_display_submission_form();
      $content = str_replace(HEYPUB_SUBMISSION_PAGE_REPLACER,$sub_form,trim($content));
   }
  return $content;
}

function heypub_display_submission_form() {
  global $hp_xml, $hp_config;
  if ($hp_config->get_config_option('accepting_subs')) {
    $src = get_permalink($hp_config->get_config_option('sub_page_id'));
    $url = sprintf("%s/api/v1/submissions/submit/%s",HEYPUB_DOMAIN, $hp_config->get_install_option('publisher_oid'));
    // $style = sprintf("<link rel='stylesheet' href='%sinclude/css/heypublisher.css' type='text/css' />", HEY_BASE_URL);
    $css = get_bloginfo('stylesheet_url');
    $css = urlencode($css);
    $src = urlencode($src);
  $ret = <<<EOF
<iframe id='heypub_submission_iframe' src='$url?css=$css&orig=$src' frameborder='0' scrolling='vertical' style='width: 98%;height: 500px;border: 1px solid silver;'></iframe>

EOF;
  }
  else {
    $ret = '<h3 id="heypub_not_accepting_submissions">We are currently not accepting submissions.<br/>Please check back later.</h3>';
  }
  return $ret;
}

?>
