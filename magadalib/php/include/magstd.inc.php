<?php

define("NEVER_EXPIRE", 20*365*24*3600*1000);

function __cleanSession() {
	session_start();

	// Unset all of the session variables.
	$_SESSION = array();

	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
	}

	// Finally, destroy the session.
	session_regenerate_id();
	session_destroy();
}

function __logout__(&$req) {
	if(unregisterPush($req)) {
		__cleanSession();
		$req->resultOK();
		return TRUE;
	}else {
		$req->response("Fail to unregister device!");
		return FALSE;
	}
}
registerHandler("LOGOUT", "__logout__");

function __auth__(&$req) {
	magLog("User: {$req->getUsername()} Password: {$req->getPassword()}");
	$account = getBoundAccount($req);
	if(is_null($account)) {
		if(is_null($req->getUsername()) || $req->getUsername() == '' || strtolower($req->getUsername()) == strtolower($req->getPIN())) {
			$req->error("请提供账号名称！");
			return FALSE;
		}else {
			$req->error("该账号禁止从该设备登录！");
			return FALSE;
		}
	} else {
		if(is_null($req->getUsername()) || $req->getUsername() == '' || strtolower($req->getUsername()) == strtolower($req->getPIN())) {
			$req->setUsername($account);
		}
	}
	return __push_auth__($req, TRUE);
}

function __get_config__(&$req) {
	return __push_auth__($req, FALSE);
}

function __push_auth__(&$req, $request_by_client) {
	global $__authenticator;
	global $__prefetch_urls;
	magLog("User: {$req->getUsername()} Password: {$req->getPassword()}");
	if (!is_null($req->getUsername()) && strlen($req->getUsername()) > 0) {
		if(!is_null($__authenticator)) {
			$def_url = call_user_func_array($__authenticator, array($req->getUsername(), $req->getPassword(), &$req));
			if($def_url !== FALSE) {
				$config = null; #(object)array();
				if(isset($req->_bind) && $req->_bind == "true") {
					if($request_by_client) {
						$config = registerPush($req);
					}else {
						$config = getUserConfig($req);
					}
					if($config === FALSE) {
						$req->error("在推送服务器注册设备失败，无法记住账号和手机的绑定！");
						return FALSE;
					}
					if (!is_null($__prefetch_urls)) {
						if(is_null($config) || $config === FALSE) {
							$config = (object)array();
						}
						$config->_prefetch = $__prefetch_urls;
					}
				}
				#magLog(json_encode($config));
				$req->redirect($def_url, $config);
				return TRUE;
			}else {
				$req->error("错误的账号或密码！");
				return FALSE;
			}
		}else {
			$req->error("没有注册验证函数！");
			return FALSE;
		}
	}else {
		$req->error("账号为空！");
		return FALSE;
	}
}

function __login__(&$req) {
	if($req->isRequestByPushServer()) {
		return __get_config__($req);
	}else {
		return __auth__($req);
	}
}
registerHandler("LOGIN", "__login__");

/**
 function authenticate($user, $password) {
	return $default_url;
 }
 */
$__authenticator = null;

function registerAuthenticator($auth) {
	global $__authenticator;
	$__authenticator = $auth;
}

$__prefetch_urls = null;
function registerPrefetchURL($url) {
	global $__prefetch_urls;
	if(is_null($__prefetch_urls)) {
		$__prefetch_urls = array();
	}
	if(is_string($url)) {
		$url = new MAGLinkURL($url);
	}
	$__prefetch_urls[] = $url->toObject();
}

?>
