<?php

if(!defined("_CURL_INC_")) {
	die("curl.inc.php should be included before pag_push.inc.php");
}

function pag_push($id, $server, $port, $pins, $dport, $title, $content, $content_type="text/plain", $notify=null) {
	#_log("id: $id sever: $server port: $port pins $pins dport: $dport, content: $content content_type: $content_type notify: $notify");

	if(is_null($notify) && defined("PAG_PUSH_NOTIFY_URI")) {
		$notify = PAG_PUSH_NOTIFY_URI;
	}

	$curl = new cURL();

	$url = "http://{$server}:{$port}/noti?";

	if(is_array($pins)) {
		for($i = 0; $i < count($pins); $i ++) {
			if($i > 0) {
				$url .= '&';
			}
			$url .= "DEST=".urlencode($pins[$i]);
		}
	}else {
		$url .= "DEST=".urlencode($pins);
	}
	$url .= "&APP=".urlencode($dport);

	$headers = array(
		'Content-Type'            => $content_type
	);

	#if(!empty($title)) {
	#	$headers['X-Rim-Push-Description'] = $title;
	#}

	if(isset($id) && !empty($id) && $id > 0) {
		$headers['X-Anhe-PAG-MSGID'] = $id;
	}
	if(!is_null($notify) && !empty($notify)) {
		$headers['X-Anhe-PAG-URI'] = $notify;
	}

	foreach($headers as $key=>$val) {
		$curl->header("{$key}: {$val}");
	}

	$ret = $curl->post($url, $content);

	if($ret !== FALSE && $curl->getResponseCode() == 200) {
		return TRUE;
	}else {
		return $ret;
	}
}

?>
