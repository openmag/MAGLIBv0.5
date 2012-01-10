<?php

require_once("config.inc.php");
require_once("../LIBUI.inc");
require_once("../include/curl.inc.php");
require_once("magpush.inc.php");
require_once("include/account.inc.php");
require_once("include/lcs.inc.php");
require_once("include/mds_push.inc.php");
require_once("include/aog_push.inc.php");
require_once("include/pag_push.inc.php");
require_once("include/class.magservices.php");

$handler = new RequestHandler();

function get_locked_account(&$req) {
	if(isset($req->_module) && isset($req->_pin)) {
		$pin = strtolower($req->_pin);
		if(isset($req->_user) && strtolower($req->_user) != strtolower($req->_pin)) {
			$user = strtolower($req->_user);
		} else {
			$user = '';
		}
		$db = mysql_open();
		if(strlen($user) > 0) {
			$module = new Module($db, $req->_module);
			if(($module->acceptRegistedOnly() || Account::isAcceptRegistedOnly($db)) && !Account::isAccountInList($db, $req->_module, $user)) {
				$req->error("Account is not in List");
				return FALSE;
			}
			$lpin = Account::getAccountLockedPIN($db, $req->_module, $user);
			if(!is_null($lpin) && $lpin != $pin) {
				$req->error("Account is locked to a different device!");
				return FALSE;
			}
		}
		$locked_account = Account::getPINLockedAccount($db, $req->_module, $pin);
		if(!is_null($locked_account)) {
			if(strlen($user) > 0 && $locked_account != $user) {
				$req->error("Device is locked to a different account!");
				return FALSE;
			}
			$req->response($locked_account);
		}else {
			if(strlen($user) > 0) {
				$req->response($user);
			}else {
				$req->error("Account must be present!");
				return FALSE;
			}
		}
	}else{
		$req->error("Insufficient parameters!");
		return FALSE;
	}
	return TRUE;
}
$handler->register("BIND", "get_locked_account");

function _getConfig(&$account, &$req) {
	$config = $account->getMergedConfig();
	$result = (object)array();
	$result->_module = $account->getModule();
	$result->_account = $account->getAccount();
	$result->_password = $req->_passwd;
	$result->_config = $config;
	if($account->isLockPIN()) {
		$result->_lock_account = TRUE;
	}
	return $result;
}

function register(&$req) {
	_log(var_export($req, TRUE));
	if(isset($req->_module) && isset($req->_user) && isset($req->_passwd) && isset($req->_pin)) {
		$db = mysql_open();
		$account = new Account($db, $req->_user, $req->_module);
		if(!$account->isSamePIN($req->_pin)) {
			$account->setPIN($req->_pin);
			$account->save();
		}
		$pushe = new MAGPushEngine($db);
		if($pushe->registerDevice($req->_module, $req->_user, $req->_passwd, $req->_pin, $req->_device, $req->_software, $req->_platform, $req->_mds, $req->_protocol, $req->_imsi, $req->_os, $req->_touch, $req->_nav, $req->_screen_width, $req->_screen_height)) {
			$result = _getConfig($account, $req);
			_log(json_encode($result));
			$req->response(json_encode($result));
			return TRUE;
		}else {
			_log("register: registerDevice error!");
			$req->error("registerDevice error!");
			return FALSE;
		}
	}else {
		_log("register: Insufficient parameters");
		$req->error("Insufficient parameters");
		return FALSE;
	}
}
$handler->register("REGIST", "register");

function getConfig(&$req) {
	$db = mysql_open();
	$account = new Account($db, $req->_user, $req->_module);
	$result = _getConfig($account, $req);
	_log(json_encode($result));
	$req->response(json_encode($result));
	return TRUE;
}
$handler->register("GETCONFIG", "getConfig");

function unregister(&$req) {
	if(isset($req->_module) && isset($req->_pin)) {
		$pushe = new MAGPushEngine();
		if($pushe->unregisterDevice($req->_module, $req->_pin)) {
			_log("unregister: OK");
			$req->response("OK");
			return TRUE;
		}else {
			_log("unregister: unregisterDevice error!");
			$req->error("unregisterDevice error!");
			return FALSE;
		}
	}else {
		_log("unregister: Insufficient paramters");
		$req->error("Insufficient paramters");
		return FALSE;
	}
}
$handler->register("UNREG", "unregister");

function cache(&$req) { # in milliseconds
	if(isset($req->_pin) && isset($req->_url) && isset($req->_expire)) {
		$pushe = new MAGPushEngine();
		#magLog($req->_content);
		if($pushe->registerURL($req->_module, $req->_user, $req->_pin, $req->_url, $req->_expire)) {
			$req->response("OK");
			return TRUE;
		}else {
			$req->error("registerURL error!");
			return FALSE;
		}
	}else {
		$req->error("Insufficient parameters");
		return FALSE;
	}
}
$handler->register("CACHE", "cache");

function getVar(&$req) {
	#_log(var_export($req, TRUE));
	if(isset($req->_module) && isset($req->_user) && isset($req->_var)) {
		$db = mysql_open();
		$account = new Account($db, $req->_user, $req->_module);
		$value = $account->getVar($req->_var);
		if(!is_null($value)) {
			#_log("GetVar: ".$value);
			$req->response($value);
			return TRUE;
		}else {
			#_log("GetVar: No such variable!");
			$req->error("No such variable");
			return FALSE;
		}
	}else {
		#_log("GetVar: Insufficient parameters!");
		$req->error("Insufficient parameters");
		return FALSE;
	}
}
$handler->register("GETVAR", "getVar");

function setVar(&$req) {
	if(isset($req->_module) && isset($req->_user) && isset($req->_var) && isset($req->_value)) {
		$db = mysql_open();
		$account = new Account($db, $req->_user, $req->_module);
		if($account->setVar($req->_var, $req->_value)) {
			$req->response("OK");
			return TRUE;
		}else {
			$req->error("registerVariable error!");
			return FALSE;
		}
	}else {
		$req->error("Insufficient parameters");
		return FALSE;
	}
}
$handler->register("SETVAR", "setVar");

function notifyChange(&$req) {
	if(isset($req->_module) && isset($req->_account)) {
		if(!isset($req->_keywords)) {
			$keywords = null;
		}else {
			$keywords = $req->_keywords;
		}
		if(isset($req->_force) && ($req->_force == 1 || strcasecmp($req->_force, "true") == 0)) {
			$force = TRUE;
		}else {
			$force = FALSE;
		}
		$service = new MagServices();

		$ret = $service->notify($req->_module, $req->_account, $keywords, $force);
		if(strlen($ret) > 0) {
			$req->error($ret);
			return FALSE;
		}
		
	} else {
		$req->error("Insufficient parameters");
		return FALSE;
	}
	return TRUE;
}
$handler->register("NOTIFY", "notifyChange");

$handler->accept();

?>
