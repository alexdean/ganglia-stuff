<?php
require '/Users/alex/Code/alexdean-ganglia-misc/ganglia-web/functions.php';
require '/Users/alex/Code/alexdean-ganglia-misc/ganglia-web/ganglia.php';

$contexts = array("meta", "cluster", "index_array", "cluster-summary", "node");
foreach($contexts as $context) {
  Gmetad('rhel5-dev',8612);
  $out['metrics'] = $metrics;
  $out['grid'] = $grid;
  $out['cluster'] = $cluster;
  $out['hosts_up'] = $hosts_up;
  $out['hosts_down'] = $hosts_down;
  $out['self'] = $self;
  $out['backend_component'] = $backend_component;
  
  $content = "function get_output_$context() { return ".var_export($out, true)."; }\n\n";
  file_put_contents("output-$context.php", $content);
}
?>