<?php
require_once 'GangliaXmlParser.php';

class GangliaMetricCollection {
  public function __construct($host, $port, $config=array()) {
    $this->host = $host;
    $this->port = $port;
    $this->parser = new GangliaXmlParser();
    $this->data = array();
    
    // config eager/lazy loading of data
    // cache enabled/disabled, cache location & lifetime
  }
  
  private function getData($context) {
    $timeout = 3.0;
    $errstr = "";
    $errno  = "";
    
    $fp = fsockopen($this->host, $this->port, $errno, $errstr, $timeout);
    if (!$fp) {
      throw new Exception("fsockopen error: $errstr");
    }
    
    switch ($context) {
      case "meta":
      case "control":
      case "tree":
      default:
        $request = "/?filter=summary";
        break;
      case "physical":
      case "cluster":
        $request = "/$clustername";
        break;
      case "index_array":
        $request = "/";
        break;
      case "cluster-summary":
        $request = "/$clustername?filter=summary";
        break;
      case "node":
      case "host":
        $request = "/$clustername/$hostname";
        break;
    }
    
    if ($this->port == 8649) {
      // We are connecting to a gmond. Non-interactive.
      // TODO: When do we do this?
      // xml_set_element_handler($parser, array($this,"start_cluster"), array($this,"end_all"));
    } else {
      $request .= "\n";
      $rc = fputs($fp, $request);
      if (!$rc) {
        $error = "Could not sent request to gmetad: $errstr";
        return FALSE;
      }
    }
  }
}
?>