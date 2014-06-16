<?php
// set these two lines - they are very important
// in linux run `updatedb` and `locate nagios.cfg` and `locate status.dat` to find these files
// they are usually placed in /usr/local/nagios/etc/nagios.cfg and /usr/local/nagios/var/status.dat
$nagios_cfg_file = "/opt/nagios/etc/nagios.cfg";
$nagios_status_dat_file = "/opt/nagios/var/status.dat";

// hostgroup filter - only show hosts from this hotgroup
// leave empty for not filtering
$nagmap_filter_hostgroup  = '';

// set to the centre of your map
$nagmap_map_centre = '66.17,-15.21';

// default zoom level of the map
$nagmap_map_zoom = 14;

// show sidebar with hosts and their statuses? 1=yes, 0=no
$nagmap_sidebar = 1; 

// which google maps type to use?
$nagmap_map_type = 'SATELLITE'; //you can use any of these: ROADMAP or SATELLITE or HYBRID or TERRAIN

// use this only to generate extra information for support - this will add a lot of information into the rendered index file
$nagmap_debug = 0;
?>
