<?php

#calls home

$call_handle = fopen("http://labs.shmu.org.uk/nagmap/call/?v=$nagmap_version","r");
$current_version = fgets($call_handle);
if ($current_version!="") {
  if ($current_version != $nagmap_version) {
      echo ('<span style="font-family: arial; font-size: 9pt; color: black; background-color: orange">Different STABLE NagMap version is available, please check http://labs.shmu.org.uk/nagmap for more information. Your version is v'.$nagmap_version.', latest STABLE release is v'.$current_version.'.</span>');
  };
};
fclose($call_handle);

?>
