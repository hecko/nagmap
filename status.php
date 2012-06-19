<?php

function nagmap_status() {
  include('config.php');
  $fp = fopen($nagios_status_dat_file,"r");
  $c = ";";
  $c2 = "#";
  while (!feof($fp)) {
    $line = trim(fgets($fp));
    if ($line && !ereg("^$c", $line) && !ereg("^$c2", $line)) {
      if (ereg("^hoststatus {", $line)) {
        $type = "hoststatus";
      };
      if (ereg("^servicestatus {", $line)) {
        $type = "servicestatus";
      };
      if(!ereg("}",$line) && ($type == "hoststatus" | $type == "servicestatus")) {
        $line = trim($line);
        $pieces = explode("=", $line, 2);
        $option = trim($pieces[0]);
        $value = trim($pieces[1]);
        if (($option == "host_name")) {
          $host = $value;
        }
        if ($option == "last_hard_state") {
          if ($value >= $data[$host][$type][$option]) {
            $data[$host][$type][$option] = $value;
          }
          if (($data[$host]['hoststatus']['last_hard_state'] == 0) && ($data[$host]['servicestatus']['last_hard_state'] == 0)) {
            $data[$host]['status'] = 0;
            $data[$host]['status_human'] = 'OK';
          } elseif (($data[$host]['hoststatus']['last_hard_state'] == 2) | ($data[$host]['servicestatus']['last_hard_state'] == 1)) {
            $data[$host]['status'] = 1;
            $data[$host]['status_human'] = 'WARNING / UNREACHABLE';
          } elseif (($data[$host]['hoststatus']['last_hard_state'] == 1) | ($data[$host]['servicestatus']['last_hard_state'] == 2)) {
            $data[$host]['status'] = 2;
            $data[$host]['status_human'] = 'CRITICAL / DOWN';
          } else {
            $data[$host]['status'] = 3;
            $data[$host]['status_human'] = 'UNKNOWN - NagMap bug!';
          }
        }
      } elseif ($option == '}') {
        $type = "0";
        unset($host);
      }
    }
  }
  return $data;
}

?>
