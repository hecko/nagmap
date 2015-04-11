<?php
error_reporting(E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
$page = $_SERVER['PHP_SELF'];
$sec = "300";
header("Refresh: $sec; url=$page");
$nagmap_version = '1.3';
include('config.php');
include('marker.php');

if ($javascript == "") {
  echo "There is no data to display. You either did not set NagMap properly or there is a software bug.<br>".
       "Please contact maco@blava.net for free assistance.";
  die("Cannot continue");
}

?>
<html>
  <head>
    <link rel="shortcut icon" href="favicon.ico" />
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<link rel=StyleSheet href="style.css" type="text/css" media=screen>
    <title>NagMap <?php echo $nagmap_version ?></title>
    <script src="https://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
    <script type="text/javascript">

    //static code from index.pnp
    function initialize() {
      var myOptions = {
        zoom: <?php echo ("$nagmap_map_zoom"); ?>,
        center: new google.maps.LatLng(<?php echo $nagmap_map_centre ?>),
        mapTypeId: google.maps.MapTypeId.<?php echo $nagmap_map_type ?>
      };
      window.map = new google.maps.Map(document.getElementById("map_canvas"),myOptions);

      //defining marker images
      var red_blank = new google.maps.MarkerImage(
        'https://www.google.com/mapfiles/marker.png',
        new google.maps.Size(20,34),
        new google.maps.Point(10,34));

      var blue_blank = new google.maps.MarkerImage(
        'https://www.google.com/mapfiles/marker_white.png',
        new google.maps.Size(20,34),
        new google.maps.Point(10,34));

      var green_blank = new google.maps.MarkerImage(
        'https://www.google.com/mapfiles/marker_green.png',
        new google.maps.Size(20,34),
        new google.maps.Point(10,34));

      var yellow_blank = new google.maps.MarkerImage(
        'https://www.google.com/mapfiles/marker_yellow.png',
        new google.maps.Size(20,34),
        new google.maps.Point(10,34));

      var grey_blank = new google.maps.MarkerImage(
        'https://www.google.com/mapfiles/marker_grey.png',
        new google.maps.Size(20,34),
        new google.maps.Point(10,34));

// generating dynamic code from here
// if the page ends here, there is something seriously wrong, please contact maco@blava.net for help

<?php
  // print the body of the page here
  echo $javascript;
  echo '};'; //end of initialize function
  echo '
    </script>
    </head>
    <body style="margin:0px; padding:0px;" onload="initialize()">';
  if ($nagmap_sidebar == '1') {
    sort($sidebar['ok']);
    sort($sidebar['warning']);
    sort($sidebar['critical']);
    sort($sidebar['unknown']);
    echo '<div id="map_canvas" style="width:85%; height:100%; float: left"></div>';
    echo '<div id="sidebar" class="sidebar" style="padding-left: 10px; background: black; height:100%; overflow:auto;">';
    if ($nagmap_sidebar_top_extra) {
        echo $nagmap_sidebar_top_extra;
    }
    echo '<span class="ok">ok:'.$stats['ok']
        ." (".round((100/($stats['warning']+$stats['critical']+$stats['unknown']+$stats['ok']))*($stats['ok']))."%)</span><br>"
        .'<span class="problem">problem:'.($stats['warning']+$stats['critical']+$stats['unknown'])
        ." (".round((100/($stats['warning']+$stats['critical']+$stats['unknown']+$stats['ok']))*($stats['warning']+$stats['critical']+$stats['unknown']))."%)</span><hr noshade>";
    foreach (array('critical','unknown','warning','ok') as $severity) {
      foreach ($sidebar[$severity] as $entry) {
        echo $entry;
      }
    }
    echo '</div>';
  } else {
    echo '<div id="map_canvas" style="width:100%; height:100%; float: left"></div>';
  }

?>

</body>
</html>
