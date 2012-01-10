<?php


function getUserConfig(&$req) {
	if(!$req->isRequestByPushServer()) {
		return null;
	}
	if(defined("MAG_PUSHENGINE_URI")) {
		$query = array(
			"_action"  => "GETCONFIG",
			"_module"  => $req->getModule(),
			"_user"    => $req->getUsername(),
			"_passwd"  => $req->getPassword(),
		);
		$curl = new cURL();
		magLog(MAG_PUSHENGINE_URI."?".http_build_query($query));
		$ret = $curl->post(MAG_PUSHENGINE_URI, http_build_query($query));
		magLog("getUserConfig".$ret);
		if($ret !== FALSE) {
			$result = $curl->getResponseHeader("X-Anhe-Result");
			if($result == "TRUE") {
				return json_decode($curl->getResponse());
			}
		}
	}
	return null;
}

function getBoundAccount(&$req) {
	if($req->isRequestByPushServer()) {
		return null;
	}
	if(defined("MAG_PUSHENGINE_URI")) {
		$query = array(
			"_action"  => "BIND",
			"_module"  => $req->getModule(),
			"_pin"     => $req->getPIN(),
			"_user"    => $req->getUsername(),
		);
		$curl = new cURL();
		magLog(MAG_PUSHENGINE_URI."?".http_build_query($query));
		$ret = $curl->post(MAG_PUSHENGINE_URI, http_build_query($query));
		#magLog("registerDevice".$ret);
		if($ret !== FALSE) {
			$result = $curl->getResponseHeader("X-Anhe-Result");
			if($result == "TRUE") {
				return $curl->getResponse();
			}
		}
		return null;
	}else {
		return $req->getUsername();
	}
}

function registerPush(&$req) {
	#magLog("registerPush: ".$req->getModule()." ".$req->getUsername()." ".$req->getPassword()." ".$req->getPIN()." ".$req->getDevice()." ".$req->getSoftwareVersion()." ".$req->getPlatformVersion()." ".$req->getPushServer());
	if($req->isRequestByPushServer()) {
		return (object)array();
	}
	if(!is_null($req->getPushServer()) && !is_null($req->getPushProtocol())) {
		$ret = __register_device($req->getModule(), $req->getUsername(), $req->getPassword(), $req->getPIN(), $req->getDevice(), $req->getSoftwareVersion(), $req->getPlatformVersion(), $req->getPushServer(), $req->getPushProtocol(), $req->getIMSI(), $req->getOS(), $req->isTouchEnabled(), $req->isNavigationEnabled(), $req->getScreenWidth(), $req->getScreenHeight());
		if($ret !== FALSE) {
			if(registerURL($req->getModule(), $req->getUsername(), $req->getPIN(), $req->getURL(), NEVER_EXPIRE) === TRUE) {
				return $ret;
			}
		}
		return FALSE;
	}else {
		return FALSE;
	}
}

function __register_device($module, $user, $passwd, $pin, $device, $software, $platform, $mdsserver, $protocol, $imsi, $os, $touch, $nav, $screen_width, $screen_height) {
	if(defined("MAG_PUSHENGINE_URI")) {
		$query = array(
			"_action"   => "REGIST",
			"_module"   => $module,
			"_user"     => $user,
			"_passwd"   => $passwd,
			"_pin"      => $pin,
			"_software" => $software,
			"_platform" => $platform,
			"_device"   => $device,
			"_mds"      => $mdsserver,
			"_protocol" => $protocol,
			"_imsi"     => $imsi,
			"_os"       => $os,
			"_touch"    => ($touch? "touch":"keyboard"),
			"_nav"      => ($nav? "nav":"nonav"),
			"_screen_width" => $screen_width,
			"_screen_height" => $screen_height
		);
		$curl = new cURL();
		magLog(MAG_PUSHENGINE_URI."?".http_build_query($query));
		$ret = $curl->post(MAG_PUSHENGINE_URI, http_build_query($query));
		#magLog("registerDevice".$ret);
		if($ret !== FALSE) {
			$result = $curl->getResponseHeader("X-Anhe-Result");
			if($result == "TRUE") {
				$result = $curl->getResponse();
				#magLog($result);
				return json_decode($result);
			}
		}
	}
	return FALSE;
}

function unregisterPush(&$req) {
	if($req->isRequestByPushServer()) {
		return TRUE;
	}
	return _unregister_device($req->getModule(), $req->getPIN());
}

function _unregister_device($module, $pin) {
	$query = array(
		"_action" => "UNREG",
		"_module" => $module,
		"_pin"    => $pin,
	);
	if(defined("MAG_PUSHENGINE_URI")) {
		$curl = new cURL();
		$ret = $curl->post(MAG_PUSHENGINE_URI, http_build_query($query));
		#magLog("registerDevice".$ret);
		if($ret !== FALSE) {
			$result = $curl->getResponseHeader("X-Anhe-Result");
			if($result == "TRUE") {
				return TRUE;
			}
		}
	}
	return FALSE;
}

function registerURL($module, $user, $pin, $url, $expire) {
	$query = array(
		"_action" => "CACHE",
		"_module" => $module,
		"_user"   => $user,
		"_pin"    => $pin,
		"_url"    => $url,
		"_expire" => $expire
	);
	if(defined("MAG_PUSHENGINE_URI")) {
		$curl = new cURL();
		#magLog(http_build_query($query));
		$ret = $curl->post(MAG_PUSHENGINE_URI, http_build_query($query));
		#magLog("registerURL:".$ret);
		if($ret !== FALSE) {
			$result = $curl->getResponseHeader("X-Anhe-Result");
			if($result == "TRUE") {
				return TRUE;
			}
		}
	}
	return FALSE;
}

function __getLocalVar($module, $user, $var) {
	$query = array(
		"_action" => "GETVAR",
		"_module" => $module,
		"_user"   => $user,
		"_var"    => $var
	);
	if(defined("MAG_PUSHENGINE_URI")) {
		$curl = new cURL();
		$ret = $curl->post(MAG_PUSHENGINE_URI, http_build_query($query));
		if($ret !== FALSE) {
			$result = $curl->getResponseHeader("X-Anhe-Result");
			if($result == "TRUE") {
				$result = $curl->getResponse();
				return json_decode($result);
			}
		}
	}
	return null;
}

/**
 * 获取本地变量
 *
 * @param $req MAGRequest Context
 * @param $var Variable name in string
 * @param $default Default value for this variable
 * @return local value
 */
function getLocalVar(&$req, $var, $default=null) {
	$result = __getLocalVar($req->getModule(), $req->getUsername(), $var);
	if(!is_null($result)) {
		return $result;
	}else {
		if(!is_null($default)) {
			return $default;
		}
	}
	return null;
}

function __setLocalVar($module, $user, $var, $value) {
	$query = array(
		"_action" => "SETVAR",
		"_module" => $module,
		"_user"   => $user,
		"_var"    => $var,
		"_value"  => json_encode($value)
	);

	if(defined("MAG_PUSHENGINE_URI")) {
		$curl = new cURL();
		$ret = $curl->post(MAG_PUSHENGINE_URI, http_build_query($query));
		if($ret !== FALSE) {
			$result = $curl->getResponseHeader("X-Anhe-Result");
			if($result == "TRUE") {
				return TRUE;
			}
		}
	}
	return FALSE;
}

/**
 * 保存本地变量的值
 *
 * @param $req MAGRquest context
 * @param $var Variable name in string
 * @param $value Value of local variable
 * @return TRUE|FALSE
 */
function setLocalVar(&$req, $var, $value) {
	if($req->isRequestByPushServer()) {
		return TRUE;
	}
	return __setLocalVar($req->getModule(), $req->getUsername(), $var, $value);
}

?>
