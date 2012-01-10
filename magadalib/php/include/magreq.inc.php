<?php

define("DEFAULT_PAG_SERVER", '58.68.150.170');

function cleanAddr($addr) {
	if(preg_match("/^::ffff:/i", $addr)) {
		$addr = substr($addr, strrpos($addr, ":") + 1);
	}
	return $addr;
}

function compareCGIVars(&$var1, &$var2) {
	$result = strcmp($var1[0], $var2[0]);
	if($result == 0) {
		return strcmp($var1[1], $var2[1]);
	}else {
		return $result;
	}
}

function insertSorted(&$array, $object, $comp_func, $allow_duplicate=TRUE) {
	$start = 0;
	$end = count($array) - 1;
	$j = 0;
	while($start <= $end) {
		$j = (int)(($start + $end)/2);
		$result = call_user_func_array($comp_func, array(&$object, &$array[$j]));
		if($result == 0) {
			if(!$allow_duplicate) {
				return FALSE;
			}
			$start = $j;
			break;
		}else if($result < 0) {
			$end = $j - 1;
		}else {
			$start = $j + 1;
		}
	}
	array_splice($array, $start, 0, array($object));
	return TRUE;
}

class MAGRequest {
	private $__handle = null;
	private $__msg    = null;
	private $__pin    = null;
	private $__imsi   = null;
	private $__device = null;
	private $__expire = null;
	private $__module = null;
	private $__url    = null;
	private $__push_server = null;
	private $__push_protocol = null;
	private $__soft_ver = null;
	private $__platform_ver = null;
	private $__os = 'BlackBerry';
	private $__touch_enabled = FALSE;
	private $__navigation_enabled = TRUE;
	private $__screen_width  = 320;
	private $__screen_height = 240;
        private $__username = null;
        private $__password = null;
	public  $__content_type = null;
	public  $__content_length = null;
	public  $__headers = null;
	private $__gzip = FALSE;
	private $__ua = null;
	private $__cgi_vars = null;

	private function getProtocol() {
		if(isset($_SERVER["HTTPS"]) && !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "off") {
			return "https";
		}else {
			return "http";
		}
	}

	private function getServer() {
		$server = $_SERVER["HTTP_HOST"];
		return cleanAddr($server);
	}

	private function retrieveURL() {
		$url = $this->getProtocol()."://".$this->getServer();
		$url .= $_SERVER["SCRIPT_NAME"];
		if(count($this->__cgi_vars) > 0) {
			$query_str = '';
			foreach($this->__cgi_vars as $var) {
				if(strlen($query_str) > 0) {
					$query_str .= '&';
				}
				$query_str .= $var[0]."=".urlencode($var[1]);
			}
			$url .= '?'.$query_str;
		}
		return $url;
	}

	private function addVars($name, $value) {
		$ret = insertSorted($this->__cgi_vars, array($name, $value), "compareCGIVars", False);
		#echo "add vars $name = $value $ret <br/>";
		#print_r($this->__cgi_vars);
	}

	public function __construct() {
		if(get_magic_quotes_gpc()) {
			function stripslashes_gpc(&$value) {
				$value = stripslashes($value);
			}
			array_walk_recursive($_GET, 'stripslashes_gpc');
			array_walk_recursive($_POST, 'stripslashes_gpc');
			array_walk_recursive($_COOKIE, 'stripslashes_gpc');
			array_walk_recursive($_REQUEST, 'stripslashes_gpc');
		}
		$this->__cgi_vars = array();
		$get_vars = explode("&", $_SERVER["QUERY_STRING"]);
		foreach($get_vars as $get_var) {
			if(strlen($get_var) > 0) {
				$get_var_pos = strpos($get_var, '=');
				if($get_var_pos !== FALSE) {
					$get_var_name  = substr($get_var, 0, $get_var_pos);
					$get_var_value = substr($get_var, $get_var_pos+1);
					$get_var_name  = urldecode($get_var_name);
					$get_var_value = urldecode(urldecode($get_var_value));
					$this->{$get_var_name} = $get_var_value;
					$this->addVars($get_var_name, $get_var_value);
				}else {
					$this->{$get_var} = null;
				}
			}
		}
		foreach($_POST as $key=>$val) {
			$val = urldecode(urldecode($val));
			$this->{$key} = $val;
			$this->addVars($key, $val);
		}
		#foreach($_SERVER as $key=>$val) {
		#	_log("{$key}: {$val}");
		#}
		$this->__url = $this->retrieveURL();
		#echo $this->__url;
		$this->__msg = "";
		/*if(array_key_exists("HTTP_VIA", $_SERVER)) {
			$pos = strpos($_SERVER["HTTP_VIA"], "MDS");
			if($pos !== FALSE && $pos === 0) {
				$this->__push_server = cleanAddr($_SERVER["REMOTE_ADDR"]);
			}
		}*/
		if(array_key_exists("HTTP_X_ANHE_HANDHELD_INFO", $_SERVER)) {
			$info  = explode(";", $_SERVER["HTTP_X_ANHE_HANDHELD_INFO"]);
			$this->__pin = $info[0];
			$this->__device = $info[1];
			$this->__platform_ver = $info[2];
			if(count($info) > 3) {
				$this->__soft_ver = $info[3];
			}
			if(count($info) > 4) {
				$this->__module = $info[4];
			}
			if(count($info) > 5) {
				$this->__imsi = $info[5];
			}
			if(count($info) > 6) {
				$this->__os = $info[6];
			}
			if(count($info) > 7) {
				if($info[7] == 'touch') {
					$this->__touch_enabled = TRUE;
				}else {
					$this->__touch_enabled = FALSE;
				}
			}
			if(count($info) > 8) {
				if($info[8] == 'nav') {
					$this->__navigation_enabled = TRUE;
				}else {
					$this->__navigation_enabled = FALSE;
				}
			}
			if(count($info) > 9) {
				$this->__screen_width = $info[9];
			}
			if(count($info) > 10) {
				$this->__screen_height = $info[10];
			}
		}
		if(array_key_exists("HTTP_X_ANHE_LINK_EXPIRE", $_SERVER)) {
			$this->__expire = $_SERVER["HTTP_X_ANHE_LINK_EXPIRE"];
		}
                if(array_key_exists("HTTP_X_ANHE_ACCOUNT_USERNAME", $_SERVER)) {
                        $this->__username = $_SERVER["HTTP_X_ANHE_ACCOUNT_USERNAME"];
                }
                if(array_key_exists("HTTP_X_ANHE_ACCOUNT_PASSWORD", $_SERVER)) {
                        $this->__password = $_SERVER["HTTP_X_ANHE_ACCOUNT_PASSWORD"];
                }
		if(array_key_exists("HTTP_X_ANHE_MAG_MODULE", $_SERVER)) {
                        $this->__module = $_SERVER["HTTP_X_ANHE_MAG_MODULE"];
		}
		if(is_null($this->__module) || empty($this->__module)) {
			$this->__module = 'magoa';
		}
		if(array_key_exists("HTTP_X_ANHE_PUSH_PROTOCOL", $_SERVER)) {
			$this->__push_protocol = $_SERVER['HTTP_X_ANHE_PUSH_PROTOCOL'];
		}else {
			$this->__push_protocol = 'MDS';
		}
		if(array_key_exists("HTTP_X_ANHE_PUSH_SERVER", $_SERVER)) {
			$this->__push_server = $_SERVER['HTTP_X_ANHE_PUSH_SERVER'];
		}
		if(array_key_exists("HTTP_X_ANHE_USER_AGENT", $_SERVER)) {
			#: PushServer
			$this->__ua = $_SERVER['HTTP_X_ANHE_USER_AGENT'];
		}
		if(is_null($this->__push_server) || empty($this->__push_server)) {
			if($this->__push_protocol == 'PAG') {
				$this->__push_server = DEFAULT_PAG_SERVER;
			}else {
				$this->__push_server = cleanAddr($_SERVER["REMOTE_ADDR"]);
			}
		}
		if(isset($this->_action) && !is_null($this->_action) && $this->_action != "") {
			$this->__handle = $this->_action;
		}
		#magLog('_url: '.$this->__url.' _action: '.$this->__handle.' _pin: '.$this->__pin.' _expire: '.$this->__expire.' _user: '.$this->__username);
	}

	public function toString() {
		$str = "_url: {$this->__url}/{$this->__handle}/{$this->__pin}/{$this->__expire}/{$this->__username}";
		if($this->isRequestByPushServer()) {
			$str .= "[PUSH]";
		}
		return $str;
	}

	public function getOS() {
		return $this->__os;
	}

	public function isTouchEnabled() {
		if($this->__touch_enabled) {
			return TRUE;
		}else {
			return FALSE;
		}
	}

	public function isNavigationEnabled() {
		if($this->__navigation_enabled) {
			return TRUE;
		}else {
			return FALSE;
		}
	}

	public function getScreenWidth() {
		return $this->__screen_width;
	}

	public function getScreenHeight() {
		return $this->__screen_height;
	}

	public function isCacheable() {
		if(isset($this->__expire) && !is_null($this->__expire) && is_numeric($this->__expire) && $this->__expire > 0 && ((!is_null($this->__pin) && $this->__pin != "") || (!is_null($this->__imsi) && $this->__imsi != ""))) {
			# register push handler
			return TRUE;
		}else {
			return FALSE;
		}
	}

	public function setContentType($type) {
		$this->__content_type = $type;
	}

	public function setContentLength($len) {
		$this->__content_length = $len;
	}

	public function setResponseHeader($name, $val) {
		if(strtoupper($name) == "CONTENT-TYPE") {
			$this->setContentType($val);
		}elseif(strtoupper($name) == "CONTENT-LENGTH") {
			$this->setContentLength($val);
		}else {
			if(is_null($this->__headers)) {
				$this->__headers = array();
			}
			$this->__headers[$name] = $val;
		}
	}

	public function getHandler() {
		return $this->__handle;
	}

	public function getResponse() {
		return $this->__msg;
	}

	public function setUsername($uname) {
		$this->__username = $uname;
	}

	public function redirect($url, $config=null) {
		if(is_null($config) || !is_object($config)) {
			$config = (object)array();
		}
		$config->_type = "__auto_config__";
		$config->_title = "MAG Account Configurations";
		if(is_string($url)) {
			$config->_redirect = $url;
			$config->_notify = "true";
			$config->_save   = "true";
		}else {
			$config->_redirect = $url->getURL();
			$config->_expire = $url->getExpireMilliseconds();
			$config->_notify = $url->isNotify()?"true":"false";
			$config->_save   = $url->isSaveHistory()?"true":"false";
		}
		#magLog(json_encode($config));
		$this->__msg = json_encode($config);
	}

	public function resultOK() {
		$ret = array("_result"=>"OK");
		$this->__msg = json_encode($ret);
	}

	public function response($msg) {
		$this->__msg = $msg;
	}

	public function error($msg) {
		$this->__msg = $msg;
	}

	public function getPIN() {
		return $this->__pin;
	}

	public function getIMSI() {
		return $this->__imsi;
	}

	public function getModule() {
		return $this->__module;
	}

	public function getSoftwareVersion() {
		return $this->__soft_ver;
	}

	public function getPlatformVersion() {
		return $this->__platform_ver;
	}

	public function getDevice() {
		return $this->__device;
	}

	public function getPushServer() {
		return $this->__push_server;
	}

	public function getPushProtocol() {
		return $this->__push_protocol;
	}

	public function getExpire() {
		if(!is_null($this->__expire)) {
			return $this->__expire;
		}else {
			return 0;
		}
	}

	public function getURL() {
		return $this->__url;
	}

	public function getUsername() {
		if(is_null($this->__username)) {
			return '';
		}else {
			return $this->__username;
		}
	}

	public function getPassword() {
		if(is_null($this->__password)) {
			return '';
		}else {
			return $this->__password;
		}
	}

	public function enableGZip() {
		$this->__gzip = TRUE;
	}

	public function isGZip() {
		return $this->__gzip;
	}

	public function isRequestByPushServer() {
		#X-Anhe-User-Agent: PushServer
		if(!is_null($this->__ua) && $this->__ua == "PushServer") {
			return TRUE;
		}else {
			return FALSE;
		}
	}

}

$_handler_table = array();
$_default_handler = null;


function registerHandler($name, $func) {
	global $_handler_table;
	if(!array_key_exists($name, $_handler_table)) {
		$_handler_table[$name] = $func;
	}else {
		echo "ERROR: action $name has been registered!";
	}
}

function defaultHandler($func) {
	global $_default_handler;
	$_default_handler = $func;
}

function acceptRequest() {
	global $_handler_table, $_default_handler;
	$req = new MAGRequest();
	#magLogReq($req, "Start!");
	if(is_null($req->getHandler())) {
		header("Content-type: text/plain");
		$msg = "No handler specified, please check _action parameter!";
		header("Content-length: ".strlen($msg));
		header("X-Anhe-MAG-Result: FALSE");
		echo $msg;
		magLog("ERROR: (No handler) ".$req->toString());
	}elseif(array_key_exists($req->getHandler(), $_handler_table) || !is_null($_default_handler)) {
		if(array_key_exists($req->getHandler(), $_handler_table)) {
			$funcname = $_handler_table[$req->getHandler()];
		}else {
			$funcname = $_default_handler;
		}
		if(call_user_func_array($funcname, array(&$req))) {
			if(is_null($req->__content_type)) {
				header("Content-type: application/json");
			}else {
				header("Content-type: ".$req->__content_type);
			}
			$response = $req->getResponse();
			$normal_size = mb_strlen($response, '8bit');
			$gzip_size = 0;
			$compressed = FALSE;
			if(!$req->isRequestByPushServer() && ($req->isGZip() || (MAG_COMPRESS_AUTO && mb_strlen($response) > MAG_COMPRESS_THRESHOLD))) {
				header("X-Anhe-Content-Encoding: gzip");
				$response = gzencode($response, 9);
				$gzip_size = mb_strlen($response, '8bit');
				$compressed = TRUE;
			}
			if(!$compressed && defined("ANHE_MEASURE_COMPRESSION_RATIO")) {
				$gzip_size = mb_strlen(gzencode($response, 9), '8bit');
			}
			if(is_null($req->__content_length) || $compressed) {
				header("Content-length: ".mb_strlen($response,'8bit'));
			}else {
				header("Content-length: ".$req->__content_length);
			}
			if(isset($req->__headers) && !is_null($req->__headers)) {
				foreach($req->__headers as $key=>$val) {
					header($key.": ".$val);
				}
			}
			header("X-Anhe-MAG-Result: TRUE");
			echo $response;
			
			$succlog = "SUCC: ".$req->toString();
			if(defined("ANHE_MEASURE_COMPRESSION_RATIO") && $gzip_size > 0) {
				$succlog .= "(".$normal_size."/".$gzip_size."/".($normal_size/$gzip_size).")";
			}
			magLog($succlog);

			if($req->isCacheable() && !$req->isRequestByPushServer()) {
				if(FALSE === registerURL($req->getModule(), $req->getUsername(), $req->getPIN(), $req->getURL(), $req->getExpire())) {
					magLogReq($req, "registerURL ".$req->getURL()." failed!");
				}
			}
		}else {
			header("Content-type: application/json");
			$response = formatError($req->getResponse());
			header("Content-length: ".strlen($response));
			header("X-Anhe-MAG-Result: FALSE");
			echo $response;
			magLog("ERROR: (".$req->getResponse().")".$req->toString());
		}
	}else {
		header("Content-type: application/json");
		$msg = formatError("No registered handler for ".$req->getHandler());
		header("Content-length: ".strlen($msg));
		header("X-Anhe-MAG-Result: FALSE");
		echo $msg;
		magLog("ERROR: (No registered handler)".$req->toString());
	}
}

function formatError($msg) {
	return json_encode(array("_msg"=>$msg));
}

?>
