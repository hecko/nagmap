<?php
$page = $_SERVER['PHP_SELF'];
$sec = "300"; 
header("Refresh: $sec; url=$page");
$nagmap_version = '0.10';
include('./config.php');
include('./call.php');
?>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>NagMap</title>
    <?php include("style.php"); ?>
    <script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
    <script type="text/javascript">
    function initialize() {
        var myOptions = {
          zoom: <?php echo ("$nagmap_map_zoom"); ?>, 
          center: new google.maps.LatLng(<?php echo ("$nagmap_map_centre"); ?>),
          mapTypeId: google.maps.MapTypeId.HYBRID
        };
        var map = new google.maps.Map(document.getElementById("map_canvas"),myOptions);
	
var red_blank = new google.maps.MarkerImage(
  'http://www.google.com/mapfiles/marker.png', 
  new google.maps.Size(20,34), 
  new google.maps.Point(10,34));

var blue_blank = new google.maps.MarkerImage(
  'http://www.google.com/mapfiles/marker_white.png',
  new google.maps.Size(20,34),
  new google.maps.Point(10,34));

var green_blank = new google.maps.MarkerImage(
  'http://www.google.com/mapfiles/marker_green.png',
  new google.maps.Size(20,34),
  new google.maps.Point(10,34));

var yellow_blank = new google.maps.MarkerImage(
  'http://www.google.com/mapfiles/marker_yellow.png',
  new google.maps.Size(20,34),
  new google.maps.Point(10,34));

var grey_blank = new google.maps.MarkerImage(
  'http://www.google.com/mapfiles/marker_grey.png',
  new google.maps.Size(20,34),
  new google.maps.Point(10,34));

<?php include("marker.php"); ?>

};

    </script>
  </head>

  <body style="margin:0px; padding:0px;" onload="initialize()">
    <div id="map_canvas" style="width:100%; height:100%"></div>
  </body>
</html>

