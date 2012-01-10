<?php

if(basename($_SERVER['SCRIPT_FILENAME'])==basename(__FILE__))
        exit;

/**
 * MagServices provides standard WSDL/SOAP Web Service interfaces
 * 
 * @service MagServices
 */

class MagServices {

	/**
	 * Notify push engine to check the changes of relevant URLs and push the updates to client
	 *
	 * @param string $module the name of MAG module
	 * @param string $account the account name
	 * @param string $keywords the keywords that the URLs or MAGML titles might contain (or an empty string)
	 * @param boolean $force force push even though no change found (true|false)
	 * @return string error message if error occurs, otherwise an empty string
	 */

	public function notify($module, $account, $keywords=null, $force=FALSE) {
		_log("MAGService: notify is called");
		if(is_null($module) || strlen($module) == 0) {
			_log("MAGService: Module must be present!");
			return "Module must be present!";
		}
		if(is_null($account) || strlen($account) == 0) {
			_log("MAGService: Account must be present!");
			return "Accunt must be present!";
		}

		$db = mysql_open();
		$pushe = new MAGPushEngine($db);
		$err = "";
		$cond = "a.vc_module='{$module}' AND b.vc_account='{$account}'";
		if(!is_null($keywords) && strlen($keywords) > 0) {
			$cond .= " AND (a.vc_url REGEXP '".jsstr($keywords)."' OR a.vc_title REGEXP '".jsstr($keywords)."')";
		}
		$dbrows = $db->get_arrays("a.id, a.vc_url, a.vc_pin, b.vc_password, b.vc_device, b.vc_software, b.vc_platform, b.vc_capacity, a.bl_output, a.iu_expire", "cache_tbl a JOIN device_tbl b ON a.vc_module=b.vc_module AND a.vc_pin=b.vc_pin", $cond);
		if(!is_null($dbrows) && count($dbrows) > 0) {
			for($i = 0; $i < count($dbrows); $i ++) {
				list($id, $url, $pin, $passwd, $device, $softver, $platver, $capacity, $old_cont, $expire) = $dbrows[$i];
				$errMsg = "";
				$ret = $pushe->push_cache_content($id, $url, $pin, $module, $account, $passwd, $device, $softver, $platver, $capacity, $old_cont, $expire, $force, $errMsg);
				if($ret == PUSH_RESULT_FAIL) {
					_log("MAGService: ".$errMsg."::".$url);
					return $errMsg."::".$url;
				}else if($ret == PUSH_RESULT_SUCC) {
				}else if($ret == PUSH_RESULT_NOPUSH) {
				}
			}
			_log("MAGService: Notify service success!");
			return "";
		}else {
			_log("MAGService: Notify none!");
			return "No matching records!";
		}
	}

	/**
	 * Push a notification message onto the client screen
	 *
	 * @param string $_server the push server url, format as [protocol://]server_address[:servier_port]
	 * @param string $_account the destination account to push
         * @param string $_msg the message to push
	 * @param string $_module the module name to wake up (or an empty string)
         * @param string $_app the application name to wake up (or an empty string)
	 * @return string error message if error occurs, otherwise an empty string
         */

	public function pushmsg($_server, $_account, $_msg, $_module=null, $_app=null) {
		_log("MAGService: pushmsg is called");
		if(is_null($_server) || strlen($_server) == 0) {
			_log("MAGService: Server must be present!");
			return "Server must be present";
		}
		if(is_null($_account) || strlen($_account) == 0) {
			_log("MAGService: Account must be present!");
			return "Account must be present";
		}
		if(is_null($_msg) || strlen($_msg) == 0) {
			_log("MAGService: Message must be present!");
			return "Message must be present";
		}

		$payload = array('alert'=>$_msg);
                $payload['sound'] = 'default';

		if(!is_null($_module) && strlen($_module) > 0) {
			$payload['module'] = $_module;
			if(!is_null($_app) && strlen($_app) > 0) {
				$payload['app'] = $_app;
			}
		}

		$content = json_encode($payload);

		$slash_pos = strpos($_server, '://');
		if($slash_pos !== FALSE) {
			$protocol = substr($_server, 0, $slash_pos);
			$_server = substr($_server, $slash_pos+3);
		}else {
			$protocol = 'MDS';
		}
		$comma_pos = strpos($_server, ':');
		if($comma_pos !== FALSE) {
			$server = substr($_server, 0, $comma_pos);
			$port = substr($_server, $comma_pos+1);
		}else {
			$server = $_server;
			$port = 8080;
		}

		if(!isset($port) || empty($port) || $port <= 0) {
			$port = 8080;
		}

		#echo $content;

		if($protocol == 'MDS') {
			$ret = mds_push(0, $server, $port, $_account, MDS_PUSH_CLIENT_PORT, "Notification", $content);
		}else if($protocol == 'PAG') {
			$ret = pag_push(0, $server, $port, $_account, MDS_PUSH_CLIENT_PORT, "Notification", $content);
        	}else {
			$ret = "Push protocol not supported: ".$protocol;
		}
		if($ret === TRUE) {
			_log("MAGService: Pushmsg service success!");
			return "";
		}else {
			_log("MAGService: called failure: $ret");
			return $ret;
		}
	}

	/**
	 * Reset password for a specific MAG client
	 *
	 * @param string $module MAG application module name
	 * @param string $pin PIN of the device that the account login to, either pin or accout must be present
         * @param string $account account name, either pin or account must be present
	 * @param string $password the new password
	 * @return string error message if error occurs, otherwise an empty string
         */

	public function resetAppPassword($module, $pin, $account, $password) {
		_log("MAGService: resetAppPassword is called");
		if(is_null($module) || strlen($module) == 0) {
			_log("MAGService: Module must be present!");
			return "Module must be present!";
		}
		if((is_null($pin) || strlen($pin) == 0) && (is_null($account) || strlen($account) == 0)) {
			_log("MAGService: Either Pin or Account must be present!");
			return "Either Pin or Accunt must be present!";
		}

		$pushe = new MAGPushEngine();
		$result = $pushe->resetAppPassword($module, $pin, $account, $password);
		if($result === TRUE) {
			_log("MAGService: resetAppPassword service success! $result");
			return "";
		}else {
			_log("MAGService: called failure: $result");
			return $result;
		}
	}
}

?>
