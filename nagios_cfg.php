<?php

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
      unset($file);
    } elseif (preg_match("/^cfg_dir/i",$line)) {
      $dir = explode('=',$line,2);
      $dir[1] = trim($dir[1]);
      $dir_recursive = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir[1]));
      foreach($dir_recursive as $file => $object){
        if(preg_match("/.cfg$/i",$file)) {
          $files[] = $file;
        }
      }
    }
  }
  $file_list = array_unique($files);
  return $file_list;
}


?>
