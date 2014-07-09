<?php
include("config.php");
// here are the functions for the livestatus-query, taken from pnp4nagios-auth.php and modified to work here
$SOCKET       = NULL;
$socketPath = $nagmap_livestatus_socket;
$socketDOMAIN = NULL;
$socketTYPE   = NULL;
$socketHOST   = NULL;
$socketPORT   = 0;
$socketPROTO  = NULL;
$AUTH_ENABLED = $nagmap_livestatus_auth;
$REMOTE_USER = NULL;
// the 'name'-Column must be first, as this will be used as referece for the array (as it is unique in Nagios/Icinga).
// Possible Collumns for a query with livestatus:
//	$hostquerycolumns = array('name', 'accept_passive_checks', 'acknowledged', 'acknowledgement_type', 'action_url', 'action_url_expanded', 'active_checks_enabled', 'address', 'alias', 'check_command', 'check_command_expanded', 'check_flapping_recovery_notification', 'check_freshness', 'check_interval', 'check_options', 'check_period', 'check_type', 'checks_enabled', 'childs', 'comments', 'comments_with_extra_info', 'comments_with_info', 'contact_groups', 'contacts', 'current_attempt', 'current_notification_number', 'custom_variable_names', 'custom_variable_values', 'custom_variables', 'display_name', 'downtimes', 'downtimes_with_info', 'event_handler', 'event_handler_enabled', 'execution_time', 'filename', 'first_notification_delay', 'flap_detection_enabled', 'groups', 'hard_state', 'has_been_checked', 'high_flap_threshold', 'icon_image', 'icon_image_alt', 'icon_image_expanded', 'in_check_period', 'in_notification_period', 'in_service_period', 'initial_state', 'is_executing', 'is_flapping', 'last_check', 'last_hard_state', 'last_hard_state_change', 'last_notification', 'last_state', 'last_state_change', 'last_time_down', 'last_time_unreachable', 'last_time_up', 'latency', 'long_plugin_output', 'low_flap_threshold', 'max_check_attempts', 'modified_attributes', 'modified_attributes_list', 'next_check', 'next_notification', 'no_more_notifications', 'notes', 'notes_expanded', 'notes_url', 'notes_url_expanded', 'notification_interval', 'notification_period', 'notifications_enabled', 'num_services', 'num_services_crit', 'num_services_hard_crit', 'num_services_hard_ok', 'num_services_hard_unknown', 'num_services_hard_warn', 'num_services_ok', 'num_services_pending', 'num_services_unknown', 'num_services_warn', 'obsess_over_host', 'parents', 'pending_flex_downtime', 'percent_state_change', 'perf_data', 'plugin_output', 'pnpgraph_present', 'process_performance_data', 'retry_interval', 'scheduled_downtime_depth', 'service_period', 'services', 'services_with_info', 'services_with_state', 'staleness', 'state', 'state_type', 'statusmap_image', 'total_services', 'worst_service_hard_state', 'worst_service_state', 'x_3d', 'y_3d', 'z_3d')
// Currently we only need these:
$hostquerycolumns = array('name', 'address', 'alias', 'contacts', 'custom_variables', 'groups', 'notes', 'parents', 'last_hard_state', 'worst_service_hard_state');
if(isset($_SERVER['REMOTE_USER'])){
	$REMOTE_USER = $_SERVER['REMOTE_USER'];
}
if($AUTH_ENABLED === TRUE && $REMOTE_USER === NULL){
	echo '<h1>error.remote_user_missing</h1>';
}
function connect(){
	getSocketDetails($GLOBALS["socketPath"]);
	$GLOBALS["SOCKET"] = socket_create($GLOBALS["socketDOMAIN"], $GLOBALS["socketTYPE"], $GLOBALS["socketPROTO"]);
	if($GLOBALS["SOCKET"] === FALSE) {
		echo '<h1>error.livestatus_socket_error' . socket_strerror(socket_last_error($GLOBALS["SOCKET"])) . ' ' .  $GLOBALS["socketPath"] . '</h1>';
	}
	if($GLOBALS["socketDOMAIN"] === AF_UNIX){
		$result = @socket_connect($GLOBALS["SOCKET"], $GLOBALS["socketPath"]);
	}else{
		$result = @socket_connect($GLOBALS["SOCKET"], $GLOBALS["socketHOST"], $GLOBALS["socketPORT"]);
	}
	if(!$result) {
		echo '<h1>error.livestatus_socket_error' . socket_strerror(socket_last_error($GLOBALS["SOCKET"])) . ' ' .  $GLOBALS["socketPath"] . '</h1>';
	}

}

function queryLivestatus($query) {
	if($GLOBALS["SOCKET"] === NULL) {
		connect();
	}
	@socket_write($GLOBALS["SOCKET"], $query."\nOutputFormat: json\nKeepAlive: on\nResponseHeader: fixed16\n\n");
	// Read 16 bytes to get the status code and body size
	$read = @socket_read($GLOBALS["SOCKET"],16);
	if(!$read) {
		echo '<h1>error.livestatus_socket_error' . socket_strerror(socket_last_error($GLOBALS["SOCKET"])) . ' ' .  $GLOBALS["socketPath"] . '</h1>';
	}
	$status = substr($read, 0, 3);

	// Extract content length
	$len = intval(trim(substr($read, 4, 11)));

	// Read socket until end of data
	$read = @socket_read($GLOBALS["SOCKET"],$len);

	// Catch problem while reading
	if($read === false) {
		echo '<h1>error.livestatus_socket_error' . socket_strerror(socket_last_error($GLOBALS["SOCKET"])) . ' ' .  $GLOBALS["socketPath"] . '</h1>';
	}
	//var_dump($read);
	// Decode the json response
	$obj = json_decode(utf8_encode($read));
	socket_close($GLOBALS["SOCKET"]);
	$GLOBALS["SOCKET"] = NULL;
	return $obj;

}

function getSocketDetails($string=FALSE){

	if(preg_match('/^unix:(.*)$/',$string,$match) ){
		$GLOBALS["socketDOMAIN"] = AF_UNIX;
		$GLOBALS["socketTYPE"]   = SOCK_STREAM;
		$GLOBALS["socketPath"]   = $match[1];
		$GLOBALS["socketPROTO"]  = 0;
		return;
	}
	if(preg_match('/^tcp:([a-zA-Z0-9-\.]+):([0-9]+)$/',$string,$match) ){
		$GLOBALS["socketDOMAIN"] = AF_INET;
		$GLOBALS["socketTYPE"]   = SOCK_STREAM;
		$GLOBALS["socketHOST"]   = $match[1];
		$GLOBALS["socketPORT"]   = $match[2];
		$GLOBALS["socketPROTO"]  = SOL_TCP;
		return;
	}
	# Fallback
	if(preg_match('/^\/.*$/',$string,$match) ){
		$GLOBALS["socketDOMAIN"] = AF_UNIX;
		$GLOBALS["socketTYPE"]   = SOCK_STREAM;
		$GLOBALS["socketPath"]   = $string;
		$GLOBALS["socketPROTO"]  = 0;
		return;
	}
	return FALSE;
}
// end of the functions for the livestatus-query, taken from pnp4nagios-auth.php and modified to work here

function get_livestatus_raw_data() {
	$query = "GET hosts\nColumns: " . implode(" ",$GLOBALS["hostquerycolumns"]);
	if ($nagmap_debug) {
		echo("In function get_livestatus_raw_data<br>\n");
		echo("Query: " . $query ."<br>\n");
	}
	if($GLOBALS["AUTH_ENABLED"] AND isset($GLOBALS["REMOTE_USER"])){
		$query .= "\nAuthUser: ".$GLOBALS["REMOTE_USER"];
	}
	$result = queryLivestatus($query);
	if(sizeof($result) > 0){
		$data = array();
		foreach ($result as $host) {
			$data[$host[0]]['host_name'] = $host[0];
			$data[$host[0]]['register'] = 1;
			$data[$host[0]]['user'] = array();
			for ( $i = 1; $i < sizeof($GLOBALS["hostquerycolumns"]); $i++ ) {
				$data[$host[0]][$GLOBALS["hostquerycolumns"][$i]] = $host[$i];
			}
			if (isset($data[$host[0]]['custom_variables'])) {
				$data[$host[0]]['custom_variables'] = (array) $data[$host[0]]['custom_variables'];
			}
			if (isset($data[$host[0]]['parents'])) {
				$data[$host[0]]['parents'] = $parents = implode(',', $data[$host[0]]['parents']);
			}
			if (isset($data[$host[0]]['groups'])) {
				$data[$host[0]]['hostgroups'] = implode(',', $data[$host[0]]['groups']);
			}
			if (($data[$host[0]]['last_hard_state'] == 0) && ($data[$host[0]]['worst_service_hard_state'] == 0)) {
				$data[$host[0]]['status'] = 0;
				$data[$host[0]]['status_human'] = 'OK';
				$data[$host[0]]['status_style'] = 'ok';
			} elseif (($data[$host[0]]['last_hard_state'] == 2) | ($data[$host[0]]['worst_service_hard_state'] == 1)) {
				$data[$host[0]]['status'] = 1;
				$data[$host[0]]['status_human'] = 'WARNING / UNREACHABLE';
				$data[$host[0]]['status_style'] = 'warning';
			} elseif (($data[$host[0]]['last_hard_state'] == 1) | ($data[$host[0]]['worst_service_hard_state'] == 2)) {
				$data[$host[0]]['status'] = 2;
				$data[$host[0]]['status_human'] = 'CRITICAL / DOWN';
				$data[$host[0]]['status_style'] = 'critical';
			} else {
				$data[$host[0]]['status'] = 3;
				$data[$host[0]]['status_human'] = 'UNKNOWN - NagMap bug - please report to maco@blava.net !';
				$data[$host[0]]['status_style'] = 'critical';
			}
		}
		if ($nagmap_debug) {
			var_dump($data);
		}
		return $data;
	}else{
		return FALSE;
	}
}
?>