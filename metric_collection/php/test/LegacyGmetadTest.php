<?php
/**
 * Usage: phpunit LegacyGmetadTest
 *   scripts/test_gmetad.rb must be running on localhost:8699
 *
 * Tests use ganglia.php to make a request to gmetad.  ganglia.php places output
 * in global variables, and assertions are made against these variables.
 *
 * Expected output is produced by scripts/generate_expected_output.php.
 *
 * The purpose of these tests it to ensure that ganglia.php's behavior remains the
 * same while refactoring work changes its underlying implementation.
 */

$gweb_dir = '/Users/alex/Code/alexdean-ganglia-misc/ganglia-web';
require "$gweb_dir/functions.php";
require "$gweb_dir/ganglia.php";

$base_dir = dirname(__FILE__);
require "$base_dir/scripts/expected_output.php";

class LegacyGmetadTest extends PHPUnit_Framework_TestCase {
  
  public function setup() {
    global $index_array, $error, $parsetime, $grid, $cluster, $hosts_up, $hosts_down, $metrics, $self, $backend_component, $clustername, $hostname, $context;
    $index_array = array();
    $error="";
    $parsetime = 0;
    $grid = array();
    $cluster = array();
    $hosts_up = array();
    $hosts_down = array();
    $metrics = array();
    $self = " ";
    $backend_component = array();
    $clustername = "cluster1";
    $hostname = "cluster1-host1";
  }
  
  public function testMetaContext() {
    global $index_array, $error, $parsetime, $grid, $cluster, $hosts_up, $hosts_down, $metrics, $self, $backend_component, $clustername, $hostname, $context;
    
    $expected = meta_output();    
    $context = "meta";
    $this->performTest($expected);
  }
  
  public function testClusterContext() {
    global $index_array, $error, $parsetime, $grid, $cluster, $hosts_up, $hosts_down, $metrics, $self, $backend_component, $clustername, $hostname, $context;
    
    $expected = cluster_output();
    $context = "cluster";
    $this->performTest($expected);
  }
  
  public function testIndexArrayContext() {
    global $index_array, $error, $parsetime, $grid, $cluster, $hosts_up, $hosts_down, $metrics, $self, $backend_component, $clustername, $hostname, $context;
    
    $expected = index_array_output();
    $context = "index_array";
    $this->performTest($expected);
  }
  
  public function testClusterSummaryContext() {
    global $index_array, $error, $parsetime, $grid, $cluster, $hosts_up, $hosts_down, $metrics, $self, $backend_component, $clustername, $hostname, $context;
    
    $expected = cluster_summary_output();
    $context = "cluster-summary";
    $this->performTest($expected);
  }
  
  public function testNodeContext() {
    global $index_array, $error, $parsetime, $grid, $cluster, $hosts_up, $hosts_down, $metrics, $self, $backend_component, $clustername, $hostname, $context;
    
    $expected = node_output();
    $context = "node";
    $this->performTest($expected);
  }
  
  
  private function performTest($expected) {
    global $index_array, $error, $parsetime, $grid, $cluster, $hosts_up, $hosts_down, $metrics, $self, $backend_component, $clustername, $hostname, $context;
    
    $ok = Gmetad( 'localhost', 8699 );
    $this->assertTrue( $ok );
    
    $this->assertEquals($expected['grid'], $grid);
    $this->assertEquals($expected['cluster'], $cluster);
    $this->assertEquals($expected['hosts_up'], $hosts_up);
    $this->assertEquals($expected['hosts_down'], $hosts_down);
    $this->assertEquals($expected['metrics'], $metrics);
    $this->assertEquals($expected['self'], $self);
    $this->assertEquals($expected['backend_component'], $backend_component);
    $this->assertEquals($expected['index_array'], $index_array);
  }
}
?>