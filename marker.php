<?php
include("functions.php");
// pre-define variables so the E_NOTICES do not show in webserver logs
$javascript = "";
$sidebar['ok'] = Array();
$sidebar['critical'] = Array();
$sidebar['warning'] = Array();
$sidebar['unknown'] = Array();
$stats['ok'] = 0;
$stats['critical'] = 0;
$stats['warning'] = 0;
$stats['unknown'] = 0;

// Get list of all Nagios configuration files into an array
$files = get_config_files();

// Read content of all Nagios configuration files into one huge array
foreach ($files as $file) {
  $raw_data[$file] = file($file);
}

$data = filter_raw_data($raw_data);

// hosts definition - we are only interested in hostname, parents and notes with position information
foreach ($data as $host) {
  if (((!empty($host["host_name"])) && (!preg_match("/^\\!/", $host['host_name']))) | ($host['register'] == 0)) {
    $hostname = 'x'.safe_name($host["host_name"]).'x';
    $hosts[$hostname]['host_name'] = $hostname;
    $hosts[$hostname]['nagios_host_name'] = $host["host_name"];
    $hosts[$hostname]['alias'] = "<i>(".$host["alias"].")</i>";
  
    // iterate for every option for the host
    foreach ($host as $option => $value) {
      // get parents information
      if ($option == "parents") {
        $parents = explode(',', $value); 
        foreach ($parents as $parent) {
          $parent = safe_name($parent);
          $hosts[$hostname]['parents'][] = "x".$parent."x";
        }
        continue;
      }
      // we are only interested in latlng values from notes
      if ($option == "notes") {
        if (preg_match("/latlng/",$value)) { 
          $value = explode(":",$value); 
          $hosts[$hostname]['latlng'] = trim($value[1]);
          continue;
        } else {
          continue;
        }
      };
      // another few information we are interested in
      if (($option == "address")) {
        $hosts[$hostname]['address'] = trim($value);
      };
      if (($option == "hostgroups")) {
        $hostgroups = explode(',', $value);
        foreach ($hostgroups as $hostgroup) {
          $hosts[$hostname]['hostgroups'][] = $hostgroup;
        }
      };
      // another few information we are interested in - this is a user-defined nagios variable
      if (preg_match("/^_/", trim($option))) {
        $hosts[$hostname]['user'][] = $option.':'.$value;
      };
      unset($parent, $parents);
    }
  }
}
unset($data);

if ($nagmap_filter_hostgroup) {
  foreach ($hosts as $host) {
    if (!in_array($nagmap_filter_hostgroup, $hosts[$host["host_name"]]['hostgroups'])) {
      unset($hosts[$host["host_name"]]);
    }
  }
}

// get host statuses
$s = nagmap_status();
// remove hosts we are not able to render and combine those we are able to render with their statuses 
foreach ($hosts as $h) {
  if ((isset($h["latlng"])) AND (isset($h["host_name"])) AND (isset($s[$h["nagios_host_name"]]['status']))) {
    $data[$h["host_name"]] = $h;
    $data[$h["host_name"]]['status'] = $s[$h["nagios_host_name"]]['status'];
    $data[$h["host_name"]]['status_human'] = $s[$h["nagios_host_name"]]['status_human'];
    $data[$h["host_name"]]['status_style'] = $s[$h["nagios_host_name"]]['status_style'];
  } else {
    if ($nagmap_debug) { 
      echo('// ignoring the following host:'.$h['host_name'].":".$h['latlng'].":".$s[$h["nagios_host_name"]]['status_human'].":\n");
    }
  }
}
unset($hosts);
unset($s);

// put markers and bubbles onto a map
foreach ($data as $h) {
    if ($nagmap_debug) {
      echo('<!--positioning host:'.$h['host_name'].":".$h['latlng'].":".$h['status'].":".$h['status_human']."-->\n");
    }
    // position the host on the map
    $javascript .= ("window.".$h["host_name"]."_pos = new google.maps.LatLng(".$h["latlng"].");\n");

    // display different icons for the host (according to the status in nagios)
    // if host is in state OK
    if ($h['status'] == 0) {
      $javascript .= ('window.'.$h["host_name"]."_mark = new google.maps.Marker({".
        "\n  position: ".$h["host_name"]."_pos,".
        "\n  icon: 'https://www.google.com/mapfiles/marker_green.png',".
        "\n  map: map,".
        "\n  zIndex: 2,".
        "\n  title: \"".$h["nagios_host_name"]."\"".
        "});"."\n\n");
        $stats['ok']++;
        $sidebar['ok'][] = '<a href="javascript:'.$h["host_name"].'_mark_infowindow.open(map,'.$h["host_name"].'_mark)" class="'.$h['status_style'].'">'.$h["nagios_host_name"]." ".$h["alias"]."</a><br>\n";
    // if host is in state WARNING
    } elseif ($h['status'] == 1) {
      $javascript .= ('window.'.$h["host_name"]."_mark = new google.maps.Marker({".
        "\n  position: ".$h["host_name"]."_pos,".
        "\n  icon: 'https://www.google.com/mapfiles/marker_yellow.png',".
        "\n  map: map,".
        "\n  zIndex: 3,".
        "\n  title: \"".$h["nagios_host_name"]."\"".
        "});"."\n\n");
        $stats['warning']++;
        $sidebar['warning'][] = '<a href="javascript:'.$h["host_name"].'_mark_infowindow.open(map,'.$h["host_name"].'_mark)" class="'.$h['status_style'].'">'.$h["nagios_host_name"]." ".$h["alias"]."</a><br>\n";
    // if host is in state CRITICAL / UNREACHABLE
    } elseif ($h['status'] == 2) {
      $javascript .= ('window.'.$h["host_name"]."_mark = new google.maps.Marker({".
        "\n  position: ".$h["host_name"]."_pos,".
        "\n  icon: 'https://www.google.com/mapfiles/marker.png',".
        "\n  map: map,".
        "\n  zIndex: 4,".
        "\n  title: \"".$h["nagios_host_name"]."\"".
        "});"."\n\n");
        $stats['critical']++;
        $sidebar['critical'][] = '<a href="javascript:'.$h["host_name"].'_mark_infowindow.open(map,'.$h["host_name"].'_mark)" class="'.$h['status_style'].'">'.$h["nagios_host_name"]." ".$h["alias"]."</a><br>\n";
    // if host is in state UNKNOWN
    } elseif ($h['status'] == 3) {
      $javascript .= ('window.'.$h["host_name"]."_mark = new google.maps.Marker({".
        "\n  position: ".$h["host_name"]."_pos,".
        "\n  icon: 'https://www.google.com/mapfiles/marker_grey.png',".
        "\n  map: map,".
        "\n  zIndex: 2,".
        "\n  title: \"".$h["nagios_host_name"]."\"".
        "});"."\n\n");
        $stats['unknown']++;
        $sidebar['unknown'][] = '<a href="javascript:'.$h["host_name"].'_mark_infowindow.open(map,'.$h["host_name"].'_mark)" class="'.$h['status_style'].'">'.$h["nagios_host_name"]."</a><br>\n";
    } else {
    // if host is in any other (unknown to nagmap) state
      $javascript .= ('window.'.$h["host_name"]."_mark = new google.maps.Marker({".
        "\n  position: ".$h["host_name"]."_pos,".
        "\n  icon: 'https://www.google.com/mapfiles/marker_grey.png',".
        "\n  map: map,".
        "\n  zIndex: 6,".
        "\n  title: \"".$h["nagios_host_name"]."\"".
        "});"."\n\n");
    };
    //generate google maps info bubble
    if (!isset($h["parents"])) { $h["parents"] = Array(); }; 
    if (!isset($nagios_cgi_url_base)) {
        $nagios_cgi_url_base = '/nagios/cgi-bin'; // ensures we don't break old installations whose config.php doesn't contain this variable
    }
    $info = '<div class=\"bubble\">'.$h["nagios_host_name"]."<br><table>"
         .'<tr><td>alias</td><td>'.$h["alias"].'</td></tr>'
         .'<tr><td>hostgroups</td><td>'.join('<br>', $h["hostgroups"]).'</td></tr>'
         .'<tr><td>address</td><td>'.$h["address"].'</td></tr>'
         .'<tr><td>other</td><td>'.join("<br>",$h['user']).'</td></tr>'
         .'<tr><td>parents</td><td>'.join('<br>', $h["parents"]).'</td></tr>'
         .'<tr><td>status</td><td>('.$h['status'].') '.$h['status_human'].'</td></tr>'
         .'<tr><td colspan=2><a href=\"'.$nagios_cgi_url_base.'/statusmap.cgi\?host='.$h["nagios_host_name"].'\">Nagios map page</a></td></tr>'
         .'<tr><td colspan=2><a href=\"'.$nagios_cgi_url_base.'/extinfo.cgi\?type=1\&host='.$h["nagios_host_name"].'\">Nagios host page</a></td></tr>'
         .'</table>';

    if ($nagmap_bubble_extra) {
        $info .= $nagmap_bubble_extra;
    }

    $javascript .= ("window.".$h["host_name"]."_mark_infowindow = new google.maps.InfoWindow({ content: '$info'})\n");

    $javascript .= ("google.maps.event.addListener(".$h["host_name"]."_mark, 'click', function() {"
      .$h["host_name"]."_mark_infowindow.open(map,".$h["host_name"]."_mark);\n
      });\n\n");
};

// create (multiple) parent connection links between nodes/markers
$javascript .= "// generating links between hosts\n";
foreach ($data as $h) {
  // if we do not have any parents, just create an empty array
  if (!isset($h["latlng"]) OR (!is_array($h["parents"]))) {
    continue;
  }
  foreach ($h["parents"] as $parent) {
    if (isset($data[$parent]["latlng"])) {
      // default colors for links
      $stroke_color = "#ADDFFF";
      // links in warning state
      if ($h['status'] == 1) { $stroke_color ='#ffff00'; }
      // links in problem state
      if ($h['status'] == 2) { $stroke_color ='#ff0000'; }
      $javascript .= "\n";
      $javascript .= ('window.'.$h["host_name"].'_to_'.$parent.' = new google.maps.Polyline({'."\n".
        ' path: ['.$h["host_name"].'_pos,'.$parent.'_pos],'."\n".
        "  strokeColor: \"$stroke_color\",\n".
        "  strokeOpacity: 0.9,\n".
        "  strokeWeight: 2});\n");
      $javascript .= ($h["host_name"].'_to_'.$parent.".setMap(map);\n\n");
    }
  }
}

?>
