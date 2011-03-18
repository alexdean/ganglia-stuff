#!/usr/bin/php
<?php
/* reformat_conf.php
 * Copyright 2011, Alex Dean <alexATcrackpotDOTorg>
 * 
 * Use this script to convert a Ganglia PHP file to use a $user array instead of top-level variables.
 * Example: $cluster_name => $user['cluster_name']
 *
 * Resulting file will be checked for syntax errors, and to ensure that
 * all required configuration values are defined.
 *
 * Usage: 'php reformat_user.php -i input-file.php -o output-file.php'
 */

require_once 'reformat.inc.php';

// TODO
// convert to $user
// $conf['hostcols']
// $conf['metriccols']

// from get_context.php
$user_vars = array(
  'clustername',
  'gridname',
  'hostname',
  'range',
  'metricname',
  'metrictitle',
  'sort',
  'controlroom',
  'showhosts',
  'physical',
  'tree',
  'jobrange',
  'jobstart',
  'cs',
  'ce',
  'gridwalk',
  'clustergraphsize',
  'gridstack',
  'choose_filter',
  'size',
);

// from graph.php
$graph_user_vars = array(
  'graph',
  'grid',
  'self',
  'vlabel',
  'value',
  'metric_name',
  'max',
  'min',
  'sourcetime',
  'load_color',
  'summary',
  'debug',
  'raw_host'
);

$options = parse_input();
$output = reformat_vars( file_get_contents( $options['i'] ), array_merge( $user_vars, $graph_user_vars ), 'user' );

$result = file_put_contents( $options['o'], $output );
if( !$result ) {
  echo "Failed to write new file to '${options['o']}'.\n";
  echo "Permissions problem?\n";
} else {
  echo "Wrote converted file to '${options['o']}'.\n";
}

echo "Running syntax check on '${options['o']}'\n";
system( "php -l ${options['o']}", $return );
if( $return > 0 ) {
  exit(1);
}

echo "Finished.\n";
?>
