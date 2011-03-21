<?php
class GangliaXmlParser {
  
  public function parse($xml, $context) {
    //global $error, $parsetime, $clustername, $hostname, $context;
    
    $parser = xml_parser_create();
    switch ($context) {
      case "meta":
      case "control":
      case "tree":
      default:
        xml_set_element_handler($parser, array($this,"start_meta"), array($this,"end_all"));
        $request = "/?filter=summary";
        break;
      case "physical":
      case "cluster":
        xml_set_element_handler($parser, array($this,"start_cluster"), array($this,"end_all"));
        $request = "/$clustername";
        break;
      case "index_array":
        xml_set_element_handler($parser, array($this,"start_everything"), array($this,"end_all"));
        $request = "/";
        break;
      case "cluster-summary":
        xml_set_element_handler($parser, array($this,"start_cluster_summary"), array($this,"end_all"));
        $request = "/$clustername?filter=summary";
        break;
      case "node":
      case "host":
        xml_set_element_handler($parser, array($this,"start_host"), array($this,"end_all"));
        $request = "/$clustername/$hostname";
        break;
    }



    $start = gettimeofday();

    while(!feof($fp)) {
      $data = fread($fp, 16384);
      if (!xml_parse($parser, $data, feof($fp))) {
        $error = sprintf("XML error: %s at %d",
          xml_error_string(xml_get_error_code($parser)),
          xml_get_current_line_number($parser)
        );
        fclose($fp);
        return false;
      }
    }
    
    fclose($fp);

     $end = gettimeofday();
     $parsetime = ($end['sec'] + $end['usec']/1e6) - ($start['sec'] + $start['usec']/1e6);

    return $;
  }
  
  # Returns true if the host is alive. Works for both old and new gmond sources.
  function host_alive($host, $cluster)
  {
     $TTL = 60;

     if ($host['TN'] and $host['TMAX']) {
        if ($host['TN'] > $host['TMAX'] * 4)
           return FALSE;
           $host_up = FALSE;
     }
     else {      # The old method.
        if (abs($cluster["LOCALTIME"] - $host['REPORTED']) > (4*$TTL))
           return FALSE;
     }
     return TRUE;
  }


  # Called with <GANGLIA_XML> attributes.
  function preamble($ganglia)
  {
     global $backend_component;
     $backend_component['source'] = $ganglia['SOURCE'] ;
     $backend_component['version'] = $ganglia['VERSION'];
  }


  function start_meta ($parser, $tagname, $attrs)
  {
     global $metrics, $grid, $self;
     static $sourcename, $metricname;

     switch ($tagname)
        {
           case "GANGLIA_XML":
              preamble($attrs);
              break;

           case "GRID":
           case "CLUSTER":
              # Our grid will be first.
              if (!$sourcename) $self = $attrs['NAME'];

              $sourcename = $attrs['NAME'];
              $grid[$sourcename] = $attrs;

              # Identify a grid from a cluster.
              $grid[$sourcename][$tagname] = 1;
              break;

           case "METRICS":
              $metricname = rawurlencode($attrs['NAME']);
              $metrics[$sourcename][$metricname] = $attrs;
              break;

           case "HOSTS":
              $grid[$sourcename]['HOSTS_UP'] = $attrs['UP'];
              $grid[$sourcename]['HOSTS_DOWN'] = $attrs['DOWN'];
              break;

           default:
              break;
        }
  }


  function start_cluster ($parser, $tagname, $attrs)
  {
     global $metrics, $cluster, $self, $grid, $hosts_up, $hosts_down;
     static $hostname;

     switch ($tagname)
        {
           case "GANGLIA_XML":
              preamble($attrs);
              break;
           case "GRID":
              $self = $attrs['NAME'];
              $grid = $attrs;
              break;

           case "CLUSTER":
              $cluster = $attrs;
              break;

           case "HOST":
              $hostname = $attrs['NAME'];

              if (host_alive($attrs, $cluster))
                 {
  		  isset($cluster['HOSTS_UP']) or $cluster['HOSTS_UP'] = 0;
                    $cluster['HOSTS_UP']++;
                    $hosts_up[$hostname] = $attrs;
                 }
              else
                 {
  		  isset($cluster['HOSTS_DOWN']) or $cluster['HOSTS_DOWN'] = 0;
                    $cluster['HOSTS_DOWN']++;
                    $hosts_down[$hostname] = $attrs;
                 }
              # Pseudo metrics - add useful HOST attributes like gmond_started & last_reported to the metrics list:
              $metrics[$hostname]['gmond_started']['NAME'] = "GMOND_STARTED";
              $metrics[$hostname]['gmond_started']['VAL'] = $attrs['GMOND_STARTED'];
              $metrics[$hostname]['gmond_started']['TYPE'] = "timestamp";
              $metrics[$hostname]['last_reported']['NAME'] = "REPORTED";
              $metrics[$hostname]['last_reported']['VAL'] = uptime($cluster['LOCALTIME'] - $attrs['REPORTED']);
              $metrics[$hostname]['last_reported']['TYPE'] = "string";
              $metrics[$hostname]['ip_address']['NAME'] = "IP";
              $metrics[$hostname]['ip_address']['VAL'] = $attrs['IP'];
              $metrics[$hostname]['ip_address']['TYPE'] = "string";
              $metrics[$hostname]['location']['NAME'] = "LOCATION";
              $metrics[$hostname]['location']['VAL'] = $attrs['LOCATION'];
              $metrics[$hostname]['location']['TYPE'] = "string";
              break;

           case "METRIC":
              $metricname = rawurlencode($attrs['NAME']);
              $metrics[$hostname][$metricname] = $attrs;
              break;

           default:
              break;
        }
  }

  function start_everything ($parser, $tagname, $attrs)
  {
     global $index_array, $hosts, $metrics, $cluster, $self, $grid, $hosts_up, $hosts_down;
     static $hostname, $cluster_name;

     switch ($tagname)
        {
           case "GANGLIA_XML":
              preamble($attrs);
              break;
           case "GRID":
              $self = $attrs['NAME'];
              $grid = $attrs;
              break;

           case "CLUSTER":
  #	    $cluster = $attrs;
              $cluster_name = $attrs['NAME'];
              break;

           case "HOST":
              $hostname = $attrs['NAME'];
  	    $index_array['cluster'][$hostname] = $cluster_name;

           case "METRIC":
              $metricname = rawurlencode($attrs['NAME']);
  	    if ( $metricname != $hostname ) 
  	      $index_array['metrics'][$metricname][] = $hostname;
              break;

           default:
              break;
        }

  }

  function start_cluster_summary ($parser, $tagname, $attrs)
  {
     global $metrics, $cluster, $self, $grid;

     switch ($tagname)
        {
           case "GANGLIA_XML":
              preamble($attrs);
              break;
           case "GRID":
              $self = $attrs['NAME'];
              $grid = $attrs;
           case "CLUSTER":
              $cluster = $attrs;
              break;

           case "HOSTS":
              $cluster['HOSTS_UP'] = $attrs['UP'];
              $cluster['HOSTS_DOWN'] = $attrs['DOWN'];
              break;

           case "METRICS":
              $metrics[$attrs['NAME']] = $attrs;
              break;

           default:
              break;
        }
  }


  function start_host ($parser, $tagname, $attrs)
  {
     global $metrics, $cluster, $hosts_up, $hosts_down, $self, $grid;
     static $metricname;

     switch ($tagname)
        {
           case "GANGLIA_XML":
              preamble($attrs);
              break;
           case "GRID":
              $self = $attrs['NAME'];
              $grid = $attrs;
              break;
           case "CLUSTER":
              $cluster = $attrs;
              break;

           case "HOST":
              if (host_alive($attrs, $cluster))
                 $hosts_up = $attrs;
              else
                 $hosts_down = $attrs;
              break;

           case "METRIC":
              $metricname = rawurlencode($attrs['NAME']);
              $metrics[$metricname] = $attrs;
              break;

           case "EXTRA_DATA":
              break;

           case "EXTRA_ELEMENT":
              if ( isset($attrs['NAME']) && isset($attrs['VAL']) && ($attrs['NAME'] == "GROUP")) { 
                 if ( isset($metrics[$metricname]['GROUP']) ) {
                    $group_array = array_merge( (array)$attrs['VAL'], $metrics[$metricname]['GROUP'] );
                 } else {
                    $group_array = (array)$attrs['VAL'];
                 }
                 $attribarray = array($attrs['NAME'] => $attrs['VAL']);
                 $metrics[$metricname] = array_merge($metrics[$metricname], $attribarray);
                 $metrics[$metricname]['GROUP'] = $group_array;
              } else {
                 $attribarray = array($attrs['NAME'] => $attrs['VAL']);
                 $metrics[$metricname] = array_merge($metrics[$metricname], $attribarray);
              }
              break;

           default:
              break;
        }
  }


  private function end_all ($parser, $tagname) {}
}
?>