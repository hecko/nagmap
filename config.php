<?php

//set these two lines - they are very important
//in linux run `updatedb` and `locate nagios.cfg` and `locate status.dat` to find these files
$nagios_cfg_file = "/usr/local/nagios/etc/nagios.cfg";
$nagios_status_dat_file = "/usr/local/nagios/var/status.dat";

//set to the centre of your map
$nagmap_map_centre = '57.1633,-2.127';
//default zoom level of the map
$nagmap_map_zoom = 14;
//show some additional links in the bubbles? 1=yes, 0=no
$nagmap_bubble_links = 1;
//show sidebar with hosts and their statuses? 1=yes, 0=no
$nagmap_sidebar = 1; 
//which google maps type to use?
$nagmap_map_type = 'SATELLITE'; //you can use any of these: ROADMAP or SATELLITE or HYBRID or TERRAIN
//use this only to generate extra information for support - this will add a lot of information into the rendered index file
$nagmap_debug = 0;

?>
