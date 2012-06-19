<?php 
$moire_config = "/etc/moire/gui.php";
if (file_exists($moire_config)) {
  include($moire_config);
  if (isset($google_maps_api_key)) { $nagmap_google_api_key = $google_maps_api_key; };
  if (isset($apc_vpn_location)) { $nagmap_map_centre = $apc_vpn_location; };
  $nagmap_map_zoom = 9;
}
?>
