<?php

include('nagios_cfg.php');

$files = get_config_files();

foreach ($files as $file) {
  $raw_data[$file] = file($cfg_dir.'/'.$file);
}

$comment = ";";
$comment2 = "#";

include("status.php");

$s = nagmap_status();
 
foreach ($raw_data as $file) {
 foreach ($file as $line) {
  //remove blank spaces
  $line = trim($line);
  if ($line && !ereg("^$comment", $line) && !ereg("^$comment2", $line)) {
    if ((ereg("^define host{", $line)) OR (ereg("^define host {", $line))) {
      //starting a new host definition
      $i++;
//      $host[$i] = $i;
    } elseif (!ereg("}",$line)) {
      $line = trim($line);
      //exchange tabs 
      $line = preg_replace('/\t+/', ' ', $line);
      $line = preg_replace('/\s+/', ' ', $line);
      $pieces = explode(" ", $line, 2);
      $option = trim($pieces[0]);
      $value = trim($pieces[1]);
      $data[$i][$option] = $value;
    }
  }
 }
}
unset($i);

#echo '<pre>';
#print_r($data);

#hosts definition
foreach ($data as $host) {
  $nagios_host_name = $host["host_name"];
  $hostname = trim($host["host_name"]);
  $hostname = str_replace('-','_',$hostname); 
  $hostname = str_replace('.','_',$hostname);
  $hostname = str_replace('/','_',$hostname);
  $hostname = str_replace('(','_',$hostname);
  $hostname = str_replace(')','_',$hostname);
  $hostname = str_replace(' ','_',$hostname);
  $hostname = "x".$hostname."x";
  $host["host_name"] = $hostname;
  foreach ($host as $option => $value) {
    if ($option == "parents") {
      $value = trim($value);
      $value = str_replace('-','_',$value);
      $value = str_replace('.','_',$value);
      $value = str_replace('/','_',$value);
      $value = str_replace('(','_',$value);
      $value = str_replace(')','_',$value);
      $value = str_replace(' ','_',$value);
      $value = "x".$value."x";
    }
    if (($option == "notes") && (ereg("latlng",$value))) { 
      $value = explode(":",$value); 
      $value = $value[1];
      $option = "latlng";
    };
    if (($option != "latlng") && ($option != "nagios_host_name") && (ereg("-",$value))) {
      $value = str_replace('-','_',$value);
      $value = str_replace('.','_',$value);
    };
    $hosts[$hostname]["nagios_host_name"] = $nagios_host_name;
    $hosts[$hostname][$option] = $value;
  };
};

//put markers and bubbles
foreach ($hosts as $h) {
  if ((isset($h["latlng"])) and (isset($h["host_name"]))) {
    // position the host to the map
    echo("var ".$h["host_name"]."_pos = new google.maps.LatLng(".$h["latlng"].");\n");

    // display different icons for the host (according to the status in nagios)
    // if host is in state OK and it is a special type of host (for wifi hotspot networks)
    if (($h["use"] == "wifi_hotspot") && ($s[$h["nagios_host_name"]]['status'] == 0)) {
      echo('var '.$h["host_name"]."_mark = new google.maps.Marker({".
        "\n  position: ".$h["host_name"]."_pos,".
        "\n  icon: 'http://www.google.com/mapfiles/marker_white.png',".
        "\n".'  '."map: map,".
	"\n  zIndex: 1,".
        "\n  title: \"".$h["nagios_host_name"]."\"".
        "\n  });"."\n\n");
    // if host is in state OK
    } elseif ($s[$h["nagios_host_name"]]['status'] == 0) {
      echo('var '.$h["host_name"]."_mark = new google.maps.Marker({".
        "\n  position: ".$h["host_name"]."_pos,".
        "\n  icon: 'http://www.google.com/mapfiles/marker_green.png',".
        "\n  map: map,".
        "\n  zIndex: 2,".
        "\n  title: \"".$h["nagios_host_name"]."\"".
        "});"."\n\n");
    // if host is in state WARNING 
    } elseif ($s[$h["nagios_host_name"]]['status'] == 1) {
      echo('var '.$h["host_name"]."_mark = new google.maps.Marker({".
        "\n  position: ".$h["host_name"]."_pos,".
        "\n  icon: 'http://www.google.com/mapfiles/marker_yellow.png',".
        "\n  map: map,".
        "\n  zIndex: 3,".
        "\n  title: \"".$h["nagios_host_name"]."\"".
        "});"."\n\n");
    // if host is in state CRITICAL / UNREACHABLE
    } elseif ($s[$h["nagios_host_name"]]['status'] == 2) {
      echo('var '.$h["host_name"]."_mark = new google.maps.Marker({".
        "\n  position: ".$h["host_name"]."_pos,".
        "\n  icon: 'http://www.google.com/mapfiles/marker.png',".
        "\n  map: map,".
        "\n  zIndex: 4,".
        "\n  title: \"".$h["nagios_host_name"]."\"".
        "});"."\n\n");
    // if host is in state UNKNOWN
    } elseif ($s[$h["nagios_host_name"]]['status'] == 3) {
      echo('var '.$h["host_name"]."_mark = new google.maps.Marker({".
        "\n  position: ".$h["host_name"]."_pos,".
        "\n  icon: 'http://www.google.com/mapfiles/marker_grey.png',".
        "\n  map: map,".
        "\n  zIndex: 2,".
        "\n  title: \"".$h["nagios_host_name"]."\"".
        "});"."\n\n");
    } else {
    // if host is in any other (unknown to nagmap) state
      echo('var '.$h["host_name"]."_mark = new google.maps.Marker({".
        "\n  position: ".$h["host_name"]."_pos,".
        "\n  icon: 'http://www.google.com/mapfiles/marker_grey.png',".
        "\n  map: map,".
        "\n  zIndex: 6,".
        "\n  title: \"".$h["nagios_host_name"]."\"".
        "});"."\n\n");
    };
    //generate google maps info bubble
    $info = '<div class=\"bubble\"><b>'.$h["nagios_host_name"]."</b><br>Type: ".$h["use"]
         .'<br>Address: '.$h["address"]
         .'<br>Parents: '.$h["parents"]
         .'<br>Host status: '.$s[$h["nagios_host_name"]]["hoststatus"]["last_hard_state"]
         .'<br>Services status: '.$s[$h["nagios_host_name"]]["servicestatus"]["last_hard_state"]
         .'<br>Combined / NagMap status: '.$s[$h["nagios_host_name"]]['status'].' : '.$s[$h["nagios_host_name"]]['status_human']
         .'<br><a href=\"/nagios/cgi-bin/statusmap.cgi\?host='.$h["nagios_host_name"].'\">Nagios map page</a>'
         .'<br><a href=\"/nagios/cgi-bin/extinfo.cgi\?type=1\&host='.$h["nagios_host_name"].'\">Nagios host page</a>';
    $links = '<br><a href=\"../cgi-bin/smokeping.cgi?target=LAN.'.$h["nagios_host_name"].'\">Smokeping statistics</a>'
         .'<br><a href=\"../devices/modules/mrtg_uptime/workdir/'.$h["nagios_host_name"].'.html\">Uptime Graph</a>';
    if ($nagmap_bubble_links == 1) {
      $info = $info.$links
        .'<br><span style=\"font-size: 7pt\">NagMap by blava.net</span>'
        .'</div>';
    } else {
      $info = $info
        .'<br><span style=\"font-size: 7pt\">NagMap by blava.net</span>'
        .'</div>';
    };

    echo("var ".$h["host_name"]."_mark_infowindow = new google.maps.InfoWindow({
      content: '$info'
      })\n");

    echo("google.maps.event.addListener(".$h["host_name"]."_mark, 'click', function() {
      ".$h["host_name"]."_mark_infowindow.open(map,".$h["host_name"]."_mark);
      });\n\n");

  };
};

#echo ("<pre>");
#print_r($hosts);

//create parent connection links
foreach ($hosts as $h) {
  if ((isset($h["parents"]) AND (isset($h["latlng"])) AND (isset($hosts[$h["parents"]]["latlng"])))) {
    echo("\nvar ".$h["host_name"].'_to_'.$h["parents"]." = new google.maps.Polyline({\n".
      "  path: [".$h["host_name"].'_pos,'.$h["parents"]."_pos],\n".
      "  strokeColor: \"#ee1212\",\n".
      "  strokeOpacity: 0.9,\n".
      "  strokeWeight: 2});\n");
    echo ($h["host_name"].'_to_'.$h["parents"].".setMap(map);\n\n");
  };
};

?>
