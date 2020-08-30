<?php
// Generic logging class.
// Should be instantiated: $this->logger = new \HeyPublisher\Base\Log('file.log');
// Then send logs: $this->logger->debug("message"); and it will be written to 'file.log'

namespace HeyPublisher\Base;
global $HEYPUB_LOGGER;

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

// Logging class for all HeyPublisher plugins.
// This class is instantiated automatically when loaded and will be accessible
// via the global $HEYPUB_LOGGER
class Log {
  var $enable = false;
  var $log_file = '';

  // Pass in fully pathed file if you want to override default
  public function __construct($file='') {
    if (!$file) { $file = '/tmp/heypub_plugin_error.log'; }
    // this can't be instantiated in var declaration
    $this->log_file = $file;
    if (file_exists($this->log_file)) {
      // can only turn on logging if server-side environment exists
      $this->enable = (getenv('HEYPUB_DEBUG') === 'true');
    }
  }

  // Logging function
  public function debug($msg) {
    if ($this->enable) {
      error_log(sprintf("%s\n",$msg),3,$this->log_file);
    }
  }
}
// This class sets a global accessor
if (!isset($HEYPUB_LOGGER)) {
  $HEYPUB_LOGGER  = new \HeyPublisher\Base\Log();
  $HEYPUB_LOGGER->debug("Instantiating HeyPublisher\Base\Log...");
}
?>
