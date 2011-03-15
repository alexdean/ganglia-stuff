#!/usr/bin/php
<?php
/* reformat_conf.php
 * Copyright 2011, Alex Dean <alexATcrackpotDOTorg>
 * 
 * Use this script to convert a Ganglia PHP file to use a $conf array instead of top-level variables.
 * Example: $template_name => $conf['template_name']
 *
 * Resulting file will be checked for syntax errors, and to ensure that
 * all required configuration values are defined.
 *
 * Usage: 'php reformat_conf.php -i input-conf.php -o output-conf.php'
 */

require_once 'reformat.inc.php';

$required_conf_vars = array(
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
  'case_sensitive_hostnames'
);
$optional_conf_vars = array(
  'optional_graphs',
  'filter_dir'
);

$options = parse_input();
$output = reformat_vars( file_get_contents( $options['i'] ), array_merge( $required_conf_vars, $optional_conf_vars ), 'conf' );

$result = file_put_contents( $options['o'], $output );
if( !$result ) {
  echo "Failed to write new config file to '${options['o']}'.\n";
  echo "Permissions problem?\n";
} else {
  echo "Wrote converted configuration to '${options['o']}'.\n";
}

echo "Running syntax check on '${options['o']}'\n";
system( "php -l ${options['o']}", $return );
if( $return > 0 ) {
  exit(1);
}

// suppress warnings: we don't care if version.php is missing, etc.
// we're only interested in $conf
// @require $options['o'];
// $missing = array_diff( $required_conf_vars, array_keys( $conf ) );
// if( count($missing) ) {
//   echo "Generated config file is missing these required config values: ".implode( $missing, ',' );
//   exit(1);
// } else {
//   echo "All required config values are defined in '${options['o']}'.\n";
// }
echo "Finished.\n";
?>
