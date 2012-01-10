<?php

define("MAG_PUSH_INIT", 0);
define("MAG_PUSH_SUCC", 1);
define("MAG_PUSH_FAIL", 2);
define("MAG_PUSH_CONFIRM", 3);

define("PUSH_RESULT_FAIL",   0);
define("PUSH_RESULT_SUCC",   1);
define("PUSH_RESULT_NOPUSH", 2);

function createCacheUpdateView(&$db, $fast_interval_sec, $slow_interval_min, $poll_threshold_day) {
	$sql = "drop view if exists cache_update_view";
	if(!$db->query($sql)) {
		return FALSE;
	}
	$fail_poll_threshold = 60; # minutes
	$sql = "create view cache_update_view as
SELECT cache_tbl.id, cache_tbl.vc_url, cache_tbl.vc_module, cache_tbl.vc_pin, cache_tbl.bl_output, cache_tbl.iu_expire, cache_tbl.dt_change, 
IF(
    cache_tbl.iu_tries > 0,
    DATE_ADD(cache_tbl.dt_lastvisit, INTERVAL {$slow_interval_min}*cache_tbl.iu_tries MINUTE),
    IF(
       DATE_ADD(cache_tbl.dt_change, INTERVAL {$poll_threshold_day} DAY) > NOW(),
       DATE_ADD(cache_tbl.dt_lastvisit, INTERVAL {$fast_interval_sec} SECOND),
       DATE_ADD(cache_tbl.dt_lastvisit, INTERVAL {$slow_interval_min} MINUTE)
    )
) as dt_deadline,
         device_tbl.vc_account, device_tbl.vc_password,
         device_tbl.vc_device, device_tbl.vc_software,
         device_tbl.vc_platform, device_tbl.vc_capacity
FROM cache_tbl LEFT JOIN device_tbl on
       (cache_tbl.vc_pin=device_tbl.vc_pin) AND
       cache_tbl.vc_module=device_tbl.vc_module
WHERE 
     cache_tbl.tiu_state=0 OR DATE_ADD(cache_tbl.dt_lastvisit, INTERVAL {$fail_poll_threshold} MINUTE) < NOW();";
	if($db->query($sql)) {
		return TRUE;
	}else {
		return FALSE;
	}
}

function getAlarmConfig($type) {
	$conf = array(
		CHANGE_TYPE_NONE => "false",
		CHANGE_TYPE_ADD  => "true",
		CHANGE_TYPE_DEL  => "false",
		CHANGE_TYPE_MOVE => "false",
		CHANGE_TYPE_MIX  => "true"
	);
	if(array_key_exists($type, $conf)) {
		#echo $conf[$type]."\n";
		return $conf[$type];
	}else {
		#echo "No $type, return false\n";
		return "false";
	}
}

function compareString($str1, $str2) {
	$lcs = new LCSTool($str1, $str2, array('{', '}', '[', ']'));
	return $lcs->getChangeType();
}

function cleanString($string) {
	$string = preg_replace('/[[:cntrl:]]+/', '', $string);
	return trim($string);
}

class MAGPushEngine {
	private $__db = null;

	public function __construct(&$db=null) {
		if(!is_null($db)) {
			$this->__db = $db;
		}else {
			$this->__db = mysql_open();
		}
	}

	#function __destruct() {
	#	if(!is_null($this->__db)) {
	#		$this->__db->close();
	#	}
	#}

	function cleanCache($module, $pin) {
		_log("cleanCache: {$module}/{$pin}");
		if($this->__db->delete("cache_tbl", "vc_module='{$module}' and vc_pin='{$pin}'")) {
			return TRUE;
		}else {
			_log("Fail to clean Cache for {$module}/{$pin}");
		}
	}

	function resetAppPassword($module, $pin, $account, $passwd) {
		_log("resetAppPassword: {$module}/{$pin}/$account/$passwd");
		if(!is_null($pin) && strlen($pin) > 0) {
			$cond = "a.vc_module='{$module}' AND a.vc_pin='{$pin}'";
		}elseif (!is_null($account) && strlen($account) > 0) {
			$cond = "a.vc_module='{$module}' AND a.vc_account='{$account}'";
		}else {
			return "必须指定pin或账号！";
		}
		$dbrow = $this->__db->get_single_array("a.vc_account, a.vc_pin, a.vc_device, a.vc_software, a.vc_platform, a.vc_capacity, b.vc_url, b.iu_expire", "device_tbl a, cache_tbl b", "{$cond} AND a.vc_module=b.vc_module AND a.vc_pin=b.vc_pin AND b.vc_url LIKE '%_action=LOGIN%'");
		if(is_null($dbrow)) {
			return "该账号没有在推送服务器注册！";
		}
		list($user, $pin, $device, $softver, $platver, $capacity, $url, $expire) = $dbrow;
		_log("Device INFO: {$module}/{$user}/{$pin}/{$url}/{$expire}/{$passwd}/{$device}/{$softver}/{$platver}/{$capacity}");

		$errMsg = "";
		$content = $this->_get_push_content($module, $user, $passwd, $pin, $device, $platver, $softver, $capacity, $url, $errMsg);

		if($content === FALSE) {
			$json = json_decode($errMsg);
			if(!is_null($json)) {
				return $json->_msg;
			}else {
				return $errMsg;
			}
		}else {
			if($this->__db->update("vc_password='{$passwd}'", "device_tbl", "vc_module='{$module}' AND vc_account='{$user}' AND vc_pin='{$pin}'")) {
				return TRUE;
			}else {
				return "更新数据库出错！";
			}
		}
		/*$curl = new cURL();
		$curl->header("X-Anhe-Handheld-INFO: {$pin};{$device};{$platver};{$softver};{$module};{$capacity}");
		$curl->header("X-Anhe-Account-Username: {$user}");
		$curl->header("X-Anhe-Account-Password: {$passwd}");
		$curl->header("X-Anhe-User-Agent: PushServer");
		$ret = $curl->get($url);
		if($ret !== FALSE && $curl->getResponseCode() == 200) {
			$content = cleanString($curl->getResponse());
			$hres1 = $curl->getResponseHeader("X-Anhe-MAG-Result");
			$hres2 = $curl->getResponseHeader("X-Anhe-Result");
			if($hres1 == "TRUE" || $hres2 == "TRUE") {
				if($this->__db->update("vc_password='{$passwd}'", "device_tbl", "vc_module='{$module}' AND vc_account='{$user}' AND vc_pin='{$pin}'")) {
					return TRUE;
				}else {
					return "更新数据库出错！";
				}
			}else {
				$json = json_decode($content);
				if(!is_null($json)) {
					return $json->_msg;
				}else {
					return $content;
				}
			}
		}else if($ret === FALSE) {
			return "请求错误！".$curl->getError();
		}else {
			return "请求错误！".$ret;
		}*/
	}

	function unregisterDevice($module, $pin) {
		_log("unregisterDevice: {$module}/{$pin}");
		if($this->__db->delete("device_tbl", "vc_module='{$module}' and vc_pin='{$pin}'")) {
			return $this->cleanCache($module, $pin);
		}else {
			_log("Fail to delete Device for Module={$module} PIN={$pin}");
			return FALSE;
		}
	}

	function registerDevice($module, $uname, $passwd, $pin, $device, $software, $platform, $mdsserver, $protocol, $imsi, $os, $touch, $nav, $screen_width, $screen_height) {
		_log("registerDevice");

		$_db_row = $this->__db->get_single_array("vc_account", "device_tbl", "vc_module='{$module}' and vc_pin='{$pin}'");
		if(!is_null($_db_row) && $_db_row[0] != $uname) {
			# not the same account
			$this->unregisterDevice($module, $pin);
		}
		$_db_row = $this->__db->get_single_array("vc_pin", "device_tbl", "vc_module='{$module}' and vc_account='{$uname}'");
		#_log("registerDevice");
		$capacity = "{$imsi};{$os};{$touch};{$nav};{$screen_width};{$screen_height}";
		if(!is_null($_db_row)) {
			# this account is registered with other device
			$this->cleanCache($module, $_db_row[0]);
			if($this->__db->update("vc_password='{$passwd}', vc_pin='{$pin}', vc_device='{$device}', vc_software='{$software}', vc_platform='{$platform}', vc_mdsserver='{$mdsserver}', vc_pushproto='{$protocol}', vc_capacity='{$capacity}', dt_lastvisit=NOW()", "device_tbl", "vc_module='{$module}' and vc_account='{$uname}'")) {
				return TRUE;
			}else {
				return FALSE;
			}
		}else {
			$sql = "insert into device_tbl(vc_module, vc_account, vc_password, vc_pin, vc_device, vc_software, vc_platform, vc_mdsserver, vc_pushproto, vc_capacity, dt_create, dt_lastvisit) values('{$module}', '{$uname}', '{$passwd}', '{$pin}', '{$device}', '{$software}', '{$platform}', '{$mdsserver}', '{$protocol}', '{$capacity}', NOW(), NOW())";
			#_log($sql);
			if($this->__db->query($sql)) {
				return TRUE;
			}else {
				return FALSE;
			}
		}
	}

	/*function deviceRegistered($module, $user, $pin) {
		if($this->__db->get_item_count("device_tbl", "vc_module='{$module}' and vc_account='{$user}' and vc_pin='{$pin}'") > 0) {
			return TRUE;
		}else {
			return FALSE;
		}
	}*/

	/* expire: milliseconds */
	function registerURL($module, $user, $pin, $url, $expire) {
		$expire = $expire/1000; /* seconds */
		$_db_row = $this->__db->get_single_array("vc_password, vc_device, vc_software, vc_platform, vc_capacity", "device_tbl", "vc_module='{$module}' and vc_account='{$user}' and vc_pin='{$pin}'");
		if(!is_null($_db_row)) {
			list($passwd, $device, $softver, $platver, $capacity) = $_db_row;
			_log("Device INFO: {$module}/{$user}/{$pin}/{$url}/{$expire}/{$passwd}/{$device}/{$softver}/{$platver}/{$capacity}");

			$errMsg = "";
			$content = $this->_get_push_content($module, $user, $passwd, $pin, $device, $platver, $softver, $capacity, $url, $errMsg);

			if($content === FALSE) {
				_log("registerULR: ".$url." failed to get content!".$errMsg);
				return FALSE;
			}else {
				_log("registerURL: ".$url." content-length: ".strlen($content));
				$title = '';
				$json_content = json_decode($content);
				if(property_exists($json_content, '_title')) {
					$title = $json_content->_title;
				}
				# this device has been registered
				$_db_row = $this->__db->get_single_array("id", "cache_tbl", "vc_module='{$module}' and vc_pin='{$pin}' and vc_url='".jsstr($url)."'");
				if(!is_null($_db_row)) {
					# registered, update information
					$id = $_db_row[0];
					if($this->__db->update("iu_expire={$expire}, dt_expire=DATE_ADD(NOW(), INTERVAL {$expire} SECOND), bl_output='".jsstr($content)."', vc_title='".jsstr($title)."', dt_change=NOW(), dt_lastvisit=NOW(), tiu_state=0, iu_tries=0", "cache_tbl", "id={$id}")) {
						return TRUE;
					}else {
						return FALSE;
					}
				}else {
					$sql = "insert into cache_tbl(vc_url, vc_module, vc_pin, iu_expire, dt_expire, bl_output, vc_title, dt_change, dt_lastvisit, tiu_state, iu_tries) values('".jsstr($url)."', '{$module}', '{$pin}', {$expire}, DATE_ADD(NOW(), INTERVAL {$expire} SECOND), '".jsstr($content)."', '".jsstr($title)."', NOW(), NOW(), 0, 0)";
					if($this->__db->query($sql)) {
						return TRUE;
					}else {
						return FALSE;
					}
				}
			}
		}else {
			return FALSE;
		}
	}

	public function pushlog($id, $content, $reason) {
		$sql = "insert into pushlog_tbl(cache_id, dt_push, itu_state, bl_pushcontent, vc_reason) values($id, NOW(), ".MAG_PUSH_INIT.", '".jsstr($content)."', '{$reason}')";
		if($this->__db->query($sql)) {
			return $this->__db->last_id();
		}else {
			return FALSE;
		}
	}

	public function pushlog_state($id, $state) {
		$update_str = "itu_state={$state}";
		return $this->__db->update($update_str, "pushlog_tbl", "id={$id}");
	}

	public function push_cache($id, $forced, &$errMsg) {
		$_db_row = $this->__db->get_single_array("device_tbl.vc_module, device_tbl.vc_account, device_tbl.vc_password, device_tbl.vc_device, device_tbl.vc_software, device_tbl.vc_platform, device_tbl.vc_capacity, cache_tbl.vc_url, cache_tbl.vc_pin, cache_tbl.bl_output, cache_tbl.iu_expire", "cache_tbl,device_tbl", "device_tbl.vc_module=cache_tbl.vc_module and device_tbl.vc_pin=cache_tbl.vc_pin and cache_tbl.id={$id}");
		if(!is_null($_db_row)) {
			list($module, $uname, $passwd, $device, $softver, $platver, $capacity, $url, $pin, $old_cont, $expire) = $_db_row;
			return $this->push_cache_content($id, $url, $pin, $module, $uname, $passwd, $device, $softver, $platver, $capacity, $old_cont, $expire, $forced, $errMsg);
		}else {
			$errMsg = "No cache record {$id}";
			return PUSH_RESULT_FAIL;
		}
	}

	public function update_cache_state($id, $state, $reset=FALSE) {
		if($this->__db->query('LOCK TABLES cache_tbl WRITE')) {
			$update_str = "dt_lastvisit=NOW(), tiu_state={$state}";
			if($state > 0) {
				$update_str .= ", iu_tries=iu_tries+1";
			}else {
				if($reset) {
					$update_str .= ", iu_tries=0";
				}
			}
			if($this->__db->update($update_str, "cache_tbl", "id={$id}")) {
				$this->__db->query('UNLOCK TABLES');
				return TRUE;
			}else {
				return FALSE;
			}
		}else {
			return FALSE;
		}
	}

	public function push_cache_content($id, $url, $pin, $module, $uname, $passwd,  $device, $softver, $platver, $capacity, $old_cont, $expire, $forced, &$errMsg) {
		# _log("push_cache_content($id, $url, $pin, $module, $uname, $passwd, $old_cont, $expire, $forced)");
		$this->update_cache_state($id, 255);
		$ret = $this->_push_cache_content($id, $url, $pin, $module, $uname, $passwd, $device, $softver, $platver, $capacity, $old_cont, $expire, $forced, $errMsg);
		$reset = TRUE;
		if($ret == PUSH_RESULT_FAIL) {
			$reset = FALSE;
		}
		$this->update_cache_state($id, 0, $reset);
		return $ret;
	}

	private function _get_push_content($module, $uname, $passwd, $pin, $device, $platver, $softver, $capacity, $url, &$errMsg) {
		/*
		 * 获取$url内容，需要填充HTTP表头内容
		 * X-Anhe-Handheld-INFO
		 * X-Anhe-MAG-Module
		 * X-Anhe-Account-Username
		 * X-Anhe-Account-Password
		 */
		$curl = new cURL();
		$curl->header("X-Anhe-Handheld-INFO: {$pin};{$device};{$platver};{$softver};{$module};{$capacity}");
		#$curl->header("X-Anhe-MAG-Module: {$module}");
		$curl->header("X-Anhe-Account-Username: {$uname}");
		$curl->header("X-Anhe-Account-Password: {$passwd}");
		# indicate that the request is made by push server itself
		$curl->header("X-Anhe-User-Agent: PushServer");

		$query_str_start = strpos($url, '?');
		if($query_str_start !== FALSE) {
			$query_str = substr($url, $query_str_start+1);
			$url = substr($url, 0, $query_str_start);
			$ret = $curl->post($url, $query_str);
		}else {
			$ret = $curl->get($url);
		}

		/* 如果获取$url内容成功 */
		if($ret !== FALSE && $curl->getResponseCode() == 200) {
			$hres1 = $curl->getResponseHeader("X-Anhe-MAG-Result");
			$hres2 = $curl->getResponseHeader("X-Anhe-Result");
			if($hres1 == "TRUE" || $hres2 == "TRUE") {
				/* 清理获取的内容 */
				return cleanString($curl->getResponse());
			}else {
				_log("fail to get content: ".$ret);
				$errMsg = $curl->getResponse();
				return FALSE;
			}
		}else if($ret === FALSE) {
			_log("fail to get content: ".$curl->getError());
			$errMsg = "Fail to get content: ".$curl->getError();
			return FALSE;
		}else {
			_log("fail to get content: ".$ret);
			$errMsg = "fail to get content: ".$ret;
			return FALSE;
		}
	}

	public function _push_cache_content($id, $url, $pin, $module, $uname, $passwd, $device, $softver, $platver, $capacity, $old_cont, $expire, $forced, &$errMsg) {
		$errMsg = "";
		$content = $this->_get_push_content($module, $uname, $passwd, $pin, $device, $platver, $softver, $capacity, $url, $errMsg);
		if($content === FALSE) {
			$errMsg = "请求URL: {$url} 失败！".$errMsg;
			return PUSH_RESULT_FAIL;
		}

		_log("push_cache_content: ".$url." content-length: ".strlen($content));

		/* 比较前后的内容差异 */
		$diff = compareString($old_cont, $content);
		#echo "[push_difference]: {$url}: ".$diff."\n";
		#_log("[push_difference]: {$url}: ".$diff);

		/* 如果是强制推送，或者前后有差异，则推送 */
		if($forced || $diff != CHANGE_TYPE_NONE) {
			_log("[push_difference]: {$url}: ".$diff);

			/* 获取pushlog日志ID */
			$push_id = $this->pushlog($id, $content, $diff);
			if($push_id !== FALSE) {
				/* 获取推送服务器配置信息 */
				$_db_row = $this->__db->get_single_array("push_config_tbl.vc_protocol, push_config_tbl.vc_mdsserver, push_config_tbl.iu_mdsport", "push_config_tbl,device_tbl", "device_tbl.vc_pin='{$pin}' and device_tbl.vc_module='{$module}' and device_tbl.vc_account='{$uname}' and device_tbl.vc_pushproto=push_config_tbl.vc_protocol and device_tbl.vc_mdsserver=push_config_tbl.vc_mdsserver and push_config_tbl.itu_state=1");
				if(!is_null($_db_row)) {
					if(!defined("MDS_PUSH_CLIENT_PORT")) {
						$errMsg = "MDS_PUSH_CLIENT_PORT is not defined";
						$this->pushlog_state($push_id, MAG_PUSH_FAIL);
						return PUSH_RESULT_FAIL;
					}else {
						list($protocol, $server, $port) = $_db_row;
						$alarm = getAlarmConfig($diff);
						if($forced) {
							$alarm = "true";
						}
						#$push_content = json_encode(array("_alarm"=>$alarm, "_url"=>$url, "_expire" => $expire*1000, "_doc"=>$json_content));
						$json_content = json_decode($content);
						$push_content = array("_alarm"=>$alarm, "_url"=>$url, "_expire" => $expire*1000, "_module"=>$module);
						if(mb_strlen($content, '8bit') < 7*1024) {
							$push_content["_doc"] = $json_content;
						}
						$push_content = json_encode($push_content);
						#$push_ret = mds_push($push_id, $_db_row[0], $_db_row[1], $pin, MDS_PUSH_CLIENT_PORT, $push_content, "application/json", MDS_PUSH_NOTIFY_URI);
						#$push_content = gzencode($push_content, 9);
						#$content_type = "application/octet-stream";
						$content_type = "plain/text";
						if($protocol == 'MDS') {
							#_log("start mds_push");
							$push_ret = mds_push($push_id, $server, $port, $pin, MDS_PUSH_CLIENT_PORT, "", $push_content, $content_type, MDS_PUSH_NOTIFY_URI);
						} elseif($protocol == 'PAG') {
							#_log("start aog_push");
							$push_ret = pag_push($push_id, $server, $port, $pin, MDS_PUSH_CLIENT_PORT, "", $push_content, $content_type, MDS_PUSH_NOTIFY_URI);
						} elseif($protocol == 'AOG') {
							#_log("start aog_push");
							$push_ret = aog_push($push_id, $server, $port, $pin, AOG_PUSH_CLIENT_PORT, "", $push_content, $content_type);
						} else{
							$push_ret = 'Not supported protocol'.$protocol;
						}
						if($push_ret !== TRUE) {
							$errMsg = "推送出错！".$push_ret;
							$this->pushlog_state($push_id, MAG_PUSH_FAIL);
							return PUSH_RESULT_FAIL;
						} else {
							$title = '';
							if(property_exists($json_content, '_title')) {
								$title = $json_content->_title;
							}
							$update_str = "dt_expire=DATE_ADD(NOW(), INTERVAL {$expire} SECOND), dt_change=NOW(), bl_output='".jsstr($content)."', vc_title='".jsstr($title)."'";
							if($this->__db->update($update_str, 'cache_tbl', "id={$id}")) {
								$this->pushlog_state($push_id, MAG_PUSH_SUCC);
								return PUSH_RESULT_SUCC;
							}else {
								$errMsg = "更新缓存信息出错！{$update_str}";
								$this->pushlog_state($push_id, MAG_PUSH_FAIL);
								return PUSH_RESULT_FAIL;
							}
						}
					}
				}else {
					$errMsg = "没有可用推送服务器！";
					$this->pushlog_state($push_id, MAG_PUSH_FAIL);
					return PUSH_RESULT_FAIL;
				}
			}else {
				$errMsg = "创建日志出错！";
				return PUSH_RESULT_FAIL;
			}
		}else {
			#echo "Identical exit...\n";
			return PUSH_RESULT_NOPUSH;
		}
	}


}

?>
