<?php


function safe_name($in) {
  $out = trim($in);
  $out = mb_convert_encoding( $out, "ASCII");
  $out = str_replace('-','_',$out);
  $out = str_replace('.','_',$out);
  $out = str_replace('/','_',$out);
  $out = str_replace('(','_',$out);
  $out = str_replace(')','_',$out);
  $out = str_replace(' ','_',$out);
  $out = str_replace(',','_',$out);
  return $out;
}

function nagmap_status() {
  include('config.php');
  if (!file_exists($nagios_status_dat_file)) {
    echo "</script>$nagios_status_dat_file does not exist! Please set the proper \$nagios_status_dat_file variable in NagMap config file!\n";
    die;
  }
  $fp = fopen($nagios_status_dat_file,"r");
  $type = "";
  $data = Array();
  while (!feof($fp)) {
    $line = trim(fgets($fp));
    //ignore all commented lines - hop to the next itteration
    if (empty($line) OR preg_match("/^;/", $line) OR preg_match("/^#/", $line)) {
      continue;
    }
    //if end of definition, skip to next itteration
    if (preg_match("/}/",$line)) {
      $type = "0";
      unset($host);
      continue;
    }
    if (preg_match("/^hoststatus {/", $line)) {
      $type = "hoststatus";
    };
    if (preg_match("/^servicestatus {/", $line)) {
      $type = "servicestatus";
    };
    if(!preg_match("/}/",$line) && ($type == "hoststatus" | $type == "servicestatus")) {
      $line = trim($line);
      $pieces = explode("=", $line, 2);
      //do not bother with invalid data
      if (count($pieces)<2) { continue; };
      $option = trim($pieces[0]);
      $value = trim($pieces[1]);
      if (($option == "host_name")) {
        $host = $value;
      }
      //get the worst service state for the host from all of its services
      if (!isset($data[$host]['servicestatus']['last_hard_state'])) {
        $data[$host]['servicestatus']['last_hard_state'] = "0";
      }
      if ($option == "last_hard_state") {
        if ($value >= @$data[$host][$type][$option]) {
          $data[$host][$type][$option] = $value;
        }
        if (($data[$host]['hoststatus']['last_hard_state'] == 0) && ($data[$host]['servicestatus']['last_hard_state'] == 0)) {
          $data[$host]['status'] = 0;
          $data[$host]['status_human'] = 'OK';
          $data[$host]['status_style'] = 'ok';
        } elseif (($data[$host]['hoststatus']['last_hard_state'] == 2) | ($data[$host]['servicestatus']['last_hard_state'] == 1)) {
          $data[$host]['status'] = 1;
          $data[$host]['status_human'] = 'WARNING / UNREACHABLE';
          $data[$host]['status_style'] = 'warning';
        } elseif (($data[$host]['hoststatus']['last_hard_state'] == 1) | ($data[$host]['servicestatus']['last_hard_state'] == 2)) {
          $data[$host]['status'] = 2;
          $data[$host]['status_human'] = 'CRITICAL / DOWN';
          $data[$host]['status_style'] = 'critical';
        } else {
          $data[$host]['status'] = 3;
          $data[$host]['status_human'] = 'UNKNOWN - NagMap bug - please report to maco@blava.net !';
          $data[$host]['status_style'] = 'critical';
        }
      } 
    }
  }
  return $data;
}

// This is a function listing all files with Nagios configuration files into an array
// It reads nagios config file and parses out all directions for configuration directories or files
function get_config_files() {
  include('config.php');
  $cfg_raw = file($nagios_cfg_file);

  $comment = ";";
  $comment2 = "#";
  foreach ($cfg_raw as $line) {
    $line = trim($line);
    if (preg_match("/^cfg_file/i",$line)) {
      $file = explode('=',$line,2);
      $file[1] = trim($file[1]);
      $files[] = $file[1];
      //echo "// including Nagios config file ".$file[1].", config reference $line\n";
      unset($file);
    } elseif (preg_match("/^cfg_dir/i",$line)) {
      $dir = explode('=',$line,2);
      $dir[1] = trim($dir[1]);
      $dir_recursive = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir[1]));
      foreach($dir_recursive as $file => $object){
        if(preg_match("/.cfg$/i",$file)) {
          $files[] = $file;
        //echo "// including Nagios config file ".$file.", config reference ".$line."\n";
        }
      }
    }
  }
  //echo "// end of reading config file $nagios_cfg_file\n\n";
  $file_list = array_unique($files);
  return $file_list;
}

?>
