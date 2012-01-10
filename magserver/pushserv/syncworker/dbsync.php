#!/usr/bin/php
<?php

$cur_path = dirname(__FILE__)."/";

include_once($cur_path."sync_config.inc.php");
include_once($cur_path."../../include/curl.inc.php");
include_once($cur_path."../../DB.inc");
include_once($cur_path."../include/syncdb.inc.php");


$db = mysql_open();

$dbrow = $db->get_single_array("vc_name, current, next_sync", "syncdb_view", "", "next_sync asc");

if(!is_null($dbrow)) {
	list($tablename, $current, $next) = $dbrow;
	if($current >= $next) {
		_log("Sync $tablename");
		echo "Sync $tablename\n";
		$sync = new SyncTable($tablename, $db);
		$sync->sync();
		echo "Sync $tablename complete\n";
	}else {
		_log("No need to sync $tablename");
		echo "No need to sync!\n";
	}
}

?>
