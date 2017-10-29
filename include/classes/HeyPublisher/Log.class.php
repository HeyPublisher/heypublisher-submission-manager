<?php
// Generic logging class.
// Should be instantiated: $this->logger = new \NAMESPACE\HeyPublisher\Log('file.log');
// Then send logs: $this->logger->debug("message"); and it will be written to 'file.log'

namespace HeyPublisher;

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('HeyPublisher: Illegal Page Call!'); }

/**
* Loggin class for all HeyPublisher plugins.  Provides common accessor for logging
*
*/
class Log {
  var $enable = false;
  var $log_file = '';

  public function __construct($file='') {
    if (!$file) { $file = 'error.log'; }
    // this can't be instantiated in var declaration
    $this->log_file = dirname( __FILE__ ) . '/../../../' . $file;
    if (file_exists($this->log_file)) {
      // can only turn on logging if file exists
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
?>
