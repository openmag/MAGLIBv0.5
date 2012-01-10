#!/usr/bin/php
<?php

$cur_path = dirname(__FILE__)."/";

include_once($cur_path."pusher_config.inc.php");
include_once($cur_path."../../include/curl.inc.php");
include_once($cur_path."../../include/utils.inc.php");
include_once($cur_path."../../libdb/libdb.inc");
include_once($cur_path."../include/lcs.inc.php");
include_once($cur_path."../include/mds_push.inc.php");
include_once($cur_path."../include/aog_push.inc.php");
include_once($cur_path."../include/pag_push.inc.php");
include_once($cur_path."../magpush.inc.php");

#var_dump($argv);

if(count($argv) > 1 && $argv[1] == 'debug') {
	$debug = TRUE;
}else {
	$debug = FALSE;
}

$mysqli = new mysqli(MYSQL_DB_HOST, MYSQL_DB_USER, MYSQL_DB_PASS, MYSQL_DB_NAME, MYSQL_DB_PORT);

$pushe = new MAGPushEngine();

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit(-1);
}

$query = "LOCK TABLES cache_update_view READ, cache_tbl WRITE";

if($mysqli->query($query) === FALSE) {
	printf("Start transaction failed: %s", mysqli_connect_error());
	exit(-1);
}

$query = "SELECT id, vc_url, vc_pin, bl_output, iu_expire, vc_account, vc_password, vc_module, vc_device, vc_software, vc_platform, vc_capacity FROM cache_update_view WHERE dt_deadline < NOW() ORDER BY dt_deadline ASC LIMIT 1 OFFSET 0";

if($result = $mysqli->query($query)) {
	if($row = $result->fetch_row()) {
		list($id, $url, $pin, $old_cont, $expire, $uname, $passwd, $module, $device, $softver, $platver, $capacity) = $row;
	}
	$result->close();
}

if(isset($id) && $id > 0) {
	$query = "UPDATE cache_tbl SET tiu_state=255, dt_lastvisit=NOW(), iu_tries=iu_tries+1 WHERE id={$id}";
	if($mysqli->query($query) === FALSE) {
		_log("Cannot update cache_tbl tiu_state: ".$query);
		$id = 0;
	}
}

$query = "UNLOCK TABLES";
$mysqli->query($query);

/* close connection */
$mysqli->close();

if(isset($id) && $id > 0) {
	#_log("[{$pin}/{$id}] ".$url);
	$err = "";
	# ($id, $url, $pin, $module, $uname, $passwd, $old_cont, $expire, $forced, &$errMsg)
	if($debug) {
		echo "Push {$url} to {$pin}/{$module}/{$uname}\n";
	}
	#_push_cache_content($id, $url, $pin, $module, $uname, $passwd, $device, $softver, $platver, $old_cont, $expire, $forced, &$errMsg)
	$push_result = $pushe->_push_cache_content($id, $url, $pin, $module, $uname, $passwd, $device, $softver, $platver, $capacity, $old_cont, $expire, FALSE, $err);
	$reset_tries = TRUE;
	if(PUSH_RESULT_FAIL == $push_result) {
		$reset_tries = FALSE;
	}
	$pushe->update_cache_state($id, 0, $reset_tries);

	if(PUSH_RESULT_FAIL == $push_result) {
		if($debug) {
			echo "Fail to push {$row[0]}:{$row[1]}:{$err}"."\n";
		}
		_log("Fail to push {$row[0]}:{$row[1]}:{$err}");
	}elseif(PUSH_RESULT_SUCC == $push_result) {
		if($debug) {
			echo "Push {$row[0]}:{$row[1]}:Success!"."\n";
		}
		_log("Push {$row[0]}:{$row[1]}:Success!");
	}else {
		#_log("Push {$row[0]}:{$row[1]}: No change!");
		if($debug) {
			echo "No change!\n";
		}
	}
}else {
	#echo "No data to push\n";
	#_log("No data to push\n");
}


?>
