<?php

include_once("config.inc.php");

$push_engine_uri = ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS']=='off')?"http":"https")."://".$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/pushengine.php";
$dbengine_uri = ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS']=='off')?"http":"https")."://".$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/dbengine.php";

define("MAG_PUSHENGINE_URI", $push_engine_uri);
define("MAG_DBENGINE_URI", $dbengine_uri);
define("MAG_LOG_DIR", LOG_DIR);

include_once("../../magadalib/php/maglibada.inc");
include_once("../DB.inc");
include_once("include/syncdb.inc.php");

function sync_db(&$req) {
	if(isset($req->_table)) {
		$sync = new SyncTable($req->_table);
		$ret = $sync->initTable();
		if($ret !== TRUE) {
			$req->error("Init table error: ".$ret);
			return FALSE;
		}
		if(!isset($req->_version) || is_null($req->_version)) {
			$timestamp = $sync->getTimeStamp();
			$ver = $sync->getCurrentVersion();
			if($ver === FALSE) {
				$req->error("failed to get table version!");
				return FALSE;
			}else {
				$coldef = $sync->getColumnDef();
				if(is_null($coldef) || count($coldef) == 0) {
					$req->error("No table column definition");
					return FALSE;
				}
				$result = array(
					"_title"=>"MAG Wireless Sync Configurations for Table ".$req->_table,
					"__content_type"=>"__mag_db_sync__",
					"__timestamp"=>$timestamp,
					"__version"=>$ver,
					"__table"=>$req->_table,
					"__dbsync_url"=>MAG_DBENGINE_URI,
					"__columns"=>$coldef
				);
				$indexes = $sync->getIndexDef();
				if(count($indexes) > 0) {
					$result['__indexes'] = $indexes;
				}
				$req->response(json_encode($result));
				return TRUE;
			}
		} else {
			if($sync->setCurrentVersion($req->_version)) {
				if(isset($req->_prev)) {
					$sync->setPreviousVersion($req->_prev);
				}
				$result = array();
				if(!isset($req->_range) || is_null($req->_range)) {
					$result = array();
					$diff = $sync->getDiffCount();
					#echo $diff."<br/>";
					for($i = 0; $i < $diff; $i += SYNC_MAX_ITEM) {
						$end = $i + SYNC_MAX_ITEM;
						if($end > $diff) {
							$end = $diff;
						}
						$result[] = $i.'-'.$end;
					}
					$req->response(json_encode($result));
					return TRUE;
				}else {
					list($start, $end) = explode('-', $req->_range);
					#$result = $sync->getDiffSQLs($start, $end - $start);
					$result = $sync->getDiffData($start, $end - $start);
					if(!isset($req->_deflate)) {
						$req->enableGZip();
					}
					$req->response(json_encode($result));
					return TRUE;
				}
			}else {
				$req->error("No such version {$req->_version}!");
				return FALSE;
			}
		}
	}else {
		$req->error("_table must be specified!");
		return FALSE;
	}
}
registerHandler("SYNC", "sync_db");

acceptRequest();

?>
