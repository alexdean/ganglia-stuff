<?php
function reformat_vars( $string, $conf_vars, $new_array_name, $depth=0 ) {
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
          $output .= '${'.$new_array_name.'[\''.$var_name.'\']}';
        } else {
          $output .= '$'.$new_array_name.'[\''.$var_name.'\']';
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
        $subject = reformat_vars( $subject, $conf_vars, $new_array_name, $depth+1 );
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
      // Only trigger an error once we're at the top of the stack, so the line number can be reported correctly.
      trigger_error( "Near line $line_number of source file.\n".$e->getMessage(), E_USER_ERROR );
    }
  }
  return $output;
}

function usage() {
  return "This script will output a version of your conf.php using the \$conf array.\n" .
         "Example usage: 'php ${argv[0]} -i conf.php -o conf.php-converted'\n" .
         " -i : Input file\n" .
         " -o : Output file\n" .
         " -f : Force.  Overwrite output file if it already exists.\n\n";
}

function parse_input() {
  $options = getopt( "i:o:f" );
  if( !isSet( $options['i'] ) ) {
    echo usage();
    echo "Missing -i (input file) option.\n";
    exit;
  }
  if( !file_exists( $options['i'] ) ) {
    echo usage();
    echo "Input file '${options['i']}' does not exist.\n";
    exit;
  }
  if( !isSet( $options['o'] ) ) {
    echo usage();
    echo "Missing -o (output file) option.\n";
    exit;
  }
  if( file_exists( $options['o'] ) ) {
    if( array_key_exists( 'f', $options ) ) {
      echo "Overwriting existing '${options['o']}' due to usage of -f.\n";
    } else {
      echo usage();
      echo "Output file '${options['o']}' already exists.\n";
      echo "Please remove it, or use -f (force).\n";
      exit;
    }
  }
  return $options;
}

?>