<?php

include_once("config.inc.php");
include_once("../LIBUI.inc");
include_once("../include/curl.inc.php");
include_once("include/lcs.inc.php");
include_once("include/mds_push.inc.php");
include_once("include/aog_push.inc.php");
include_once("include/pag_push.inc.php");
include_once("magpush.inc.php");
include_once("include/syncdb.inc.php");
include_once("include/account.inc.php");

$handler = new RequestHandler();

function forcePushURL(&$req) {
	$pushe = new MAGPushEngine();
	$err = "";
	$result = $pushe->push_cache($req->_id, TRUE, $err);
	if(PUSH_RESULT_SUCC === $result || PUSH_RESULT_NOPUSH === $result) {
		$req->responseJSON(json_encode(array("confname"=>$req->_confname)));
		return TRUE;
	}else {
		$req->error($err);
		return FALSE;
	}
}
$handler->addFunction("forcePushURL");

function GET_SYNC_TABLE_VERSION(&$req) {
	$sync = new SyncTable($req->_vc_name);
	$ver = $sync->getCurrentVersion();
	if($ver > 0 && $sync->setCurrentVersion($ver)) {
		$cnt = $sync->getCurrentRowCount();
	}else {
		$cnt = 0;
	}
	$output['_version']   = $ver;
	$output['_row_count'] = $cnt;
        $output['_fn'] = $req->_fn;
        $req->responseJSON(json_encode($output));
        return TRUE;
}
$handler->addFunction('GET_SYNC_TABLE_VERSION');

function GET_SYNC_TABLE_VERSION_SIZE(&$req) {
	$sync = new SyncTable($req->_table);
	$sync->setCurrentVersion($req->_version);
	$output['_row_count'] = $sync->getCurrentRowCount();
        $output['_fn'] = $req->_fn;
	$req->responseJSON(json_encode($output));
	return TRUE;
}
$handler->addFunction('GET_SYNC_TABLE_VERSION_SIZE');

function GET_MODULE_LIST(&$req) {
	$output = array();
	$db = mysql_open();
	$output['_modules'] = Account::getModuleList($db);
	$output['_fn'] = $req->_fn;
	$req->responseJSON(json_encode($output));
	return TRUE;
}
$handler->addFunction('GET_MODULE_LIST');

function GET_GLOBAL_ACCOUNT_SETTINGS(&$req) {
	$db = mysql_open();
	if($req->_module == '*') {
		$account = Account::getGlobalAccount($db);
	}else {
		$account = new Module($db, $req->_module);
	}
	$output = array("_fn"=>$req->_fn);
	$output['_settings'] = $account->getAssoc();
	$req->responseJSON(json_encode($output));
	return TRUE;
}
$handler->addFunction('GET_GLOBAL_ACCOUNT_SETTINGS');

function GETSYSTEMTIME(&$req) {
	$result = array("date" => date('d日H:i:s'));
	$req->responseJSON(json_encode($result));
	return TRUE;
}
$handler->addFunction('GETSYSTEMTIME');

$handler->accept();

?>
