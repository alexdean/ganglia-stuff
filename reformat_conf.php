<?php
$conf_vars = array(
  'template_name',
  'gmetad_root',
  'rrds',
  'dwoo_compiled_dir',
  'rrdcached_socket',
  'graphdir',
  'graphreport_stats',
  'ganglia_ip',
  'ganglia_port',
  'max_graphs',
  'hostcols',
  'metriccols',
  'show_meta_snapshot',
  'default_refresh',
  'cpu_user_color',
  'cpu_nice_color',
  'cpu_system_color',
  'cpu_wio_color',
  'cpu_idle_color',
  'mem_used_color',
  'mem_shared_color',
  'mem_cached_color',
  'mem_buffered_color',
  'mem_free_color',
  'mem_swapped_color',
  'load_one_color',
  'proc_run_color',
  'cpu_num_color',
  'num_nodes_color',
  'show_cores',
  'jobstart_color',
  'load_colors',
  'load_scale',
  'default_metric_color',
  'default_metric',
  'strip_domainname',
  'time_ranges',
  'default_time_range',
  'graphite_url_base',
  'graphite_rrd_dir',
  'graph_sizes_keys',
  'graph_sizes',
  'default_graph_size',
  'case_sensitive_hostnames',
  'optional_graphs',
  'filter_dir'
);

function reformat_conf_vars( $string, $conf_vars, $depth=0 ) {
  $output = "";
  $tokens = token_get_all($string);
  $in_dbl_quotes = false;
  $line_number = 1;
  
  try {
    foreach($tokens as $value) {
      if(count($value)==3) {
        $token = $value[1];
        $token_name = token_name($value[0]);
        $line_number = $value[2];
      } else {
        if( $value=='"' ) {
          $in_dbl_quotes = !$in_dbl_quotes;
        }
        $token = $value;
        $token_name = false;
      }
    
      $var_name = substr($token,1);
      if( $token_name=='T_VARIABLE' && in_array( $var_name, $conf_vars) ) {
        if( $in_dbl_quotes ) {
          $output .= '${conf[\''.$var_name.'\']}';
        } else {
          $output .= '$conf[\''.$var_name.'\']';
        }
      // some config values may be commented out.  so we parse comments also.
      } else if( $token_name == 'T_COMMENT' ) {
        if( substr($token,0,1)=='#' ) { 
          $initial='#';
        } else if( substr($token,0,2)=='//' ) {
          $initial='//';
        } else if( substr($token,0,2)=='/*' ) {
          $initial='/*';
        }
        $subject = substr($token,strlen($initial));
        // tokenizer won't parse a string w/o php's open/close tags.
        $subject = "<?php ".$subject." ?>";
        $subject = reformat_conf_vars( $subject, $conf_vars, $depth+1 );
        $subject = str_replace( array('<?php ',' ?>'), '', $subject);
        $subject = $initial . $subject;
        $output .= $subject;
      } else if( in_array( $token_name, array('T_STRING_VARNAME','T_CURLY_OPEN','T_DOLLAR_OPEN_CURLY_BRACES') ) ) {
        // Not worth the effort to parse complex variable syntax.  Just bail and tell user to write something simpler.
        throw new Exception( "Config file uses '$token'.\nComplex variable syntax cannot be converted automatically.\nPlease reformat your config file and try again.\n", E_USER_ERROR );
      } else {
        $output .= $token;
      }
    }
  } catch( Exception $e ) {
    if( $depth > 0 ) {
      throw $e;
    } else {
      // Only trigger an error once we're at the top of the stack, so the line number is reported correctly.
      trigger_error( "Near line $line_number of source file.\n".$e->getMessage(), E_USER_ERROR );
    }
  }
  return $output;
}

if( $argc != 2 ) {
  echo "This script will output a version of your conf.php using the \$conf array.\n";
  echo "Example usage: 'php ${argv[0]} conf.php > conf.php-converted'\n";
  exit;
}
var_dump( $argv);
$output = reformat_conf_vars( file_get_contents( $argv[1] ), $conf_vars );

// really should run a syntax check on the generated code...
/* $command = "php -l -r \"".str_replace( array('<?php ',' ?>'), '', $output)."\""; */

echo $output;
?>
