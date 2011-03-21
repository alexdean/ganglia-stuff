<?php
require '../lib/GangliaMetricCollection.php';

class GangliaMetricCollectionTest extends PHPUnit_Framework_TestCase {
  public function testTruth() {
    $foo = new GangliaMetricCollection('host','port');
    $this->assertTrue($foo);
  }
}
?>