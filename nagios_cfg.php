<?php

function get_config_files() {
  include('config.php');
  $cfg_raw = file($nagios_cfg_file);

  $comment = ";";
  $comment2 = "#";
  foreach ($cfg_raw as $line) {
    $line = trim($line);
    if (eregi("^cfg_file",$line)) {
      $file = explode('=',$line,2);
      $files[] = $file[1];
      unset($file);
    } elseif (eregi("^cfg_dir",$line)) {
      $dir = explode('=',$line,2);
      $dir_handle = opendir($dir[1]);
      while (false !== ($file = readdir($dir_handle))) {
        if (ereg(".cfg$",$file)) {
          $files[] = $dir[1].'/'.$file;
        }
      }
    }
  }
  $file_list = array_unique($files);
  return $file_list;
}


?>
