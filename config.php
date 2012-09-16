<?php

//set these two lines - they are very important
//in linux run `updatedb` and `locate nagios.cfg` and `locate status.dat` to find these files
$nagios_cfg_file = "/usr/local/nagios/etc/nagios.cfg";
$nagios_status_dat_file = "/usr/local/nagios/var/status.dat";

//set to the centre of your map
$nagmap_map_centre = '57.1633,-2.127';
//default zoom level of the map
$nagmap_map_zoom = 14;
//show some additional links in the bubbles?
$nagmap_bubble_links = 1;
//show sidebar with hosts and their statuses?
$nagmap_sidebar = 1; 
//which google maps type to use?
$nagmap_map_type = 'SATELLITE'; //you can use any of these: ROADMAP or SATELLITE or HYBRID or TERRAIN

###do not edit below unless you know what you are doing
###this is a special file, which is only available on a special installations of Moire WiFI controller system developed by the same folks who develop NagMap - give us a shout if you are in a phase of implementing municipal, or any other type of wifi network :) we might be at your help! maco@blava.net
include("moire.php");

?>
