<?php
require '/Users/alex/Code/alexdean-ganglia-misc/ganglia-web/functions.php';
require '/Users/alex/Code/alexdean-ganglia-misc/ganglia-web/ganglia.php';

function format($data,$depth=1){
  $out = "";
  if(is_array($data)) {
    foreach($data as $key=>$value) {
      
      $outval = $key=="NAME" ? "<metrics>" : $key;
      if(! in_array($key, array('VAL','TYPE','UNITS','TN','TMAX','DMAX','SLOPE','GROUP','SOURCE','DESC','TITLE') ) ) {
        $out .= str_repeat("-", $depth) . $outval . "\n";
        if(is_array($value) && $depth<=3) {
          $out .= format($value, $depth+1);
        }
      }
      
    }
  }
  return $out;
}
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
  $out['index_array'] = $index_array;
  
  $keys = format($out);
  echo "*** $context ***\n";
  echo $keys . "\n";
  $content = "<?php\nfunction get_output_$context() { return ".var_export($out, true)."; }\n?>";
  file_put_contents("output-$context.php", $content);
}

switch ($context)
   {
      case "meta":
         $request = "_-filter-summary.xml";
         break;
      case "cluster":
         $request = "_cluster1.xml";
          break;
      case "index_array":
         $request = "_.xml";
          break;
      case "cluster-summary":
         $request = "_cluster1-filter-summary.xml";
         break;
      case "node":
         $request = "_cluster1_cluster1-host1.xml";
         break;
   }

?>