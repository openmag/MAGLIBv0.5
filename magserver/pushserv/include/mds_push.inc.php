<?php

if(!defined("_CURL_INC_")) {
	die("curl.inc.php should be included before mds_push.inc.php");
}

if(!defined("MDS_PUSH_NOTIFY_URI")) {
	die("MDS_PUSH_NOTIFY_URI is not defined!");
}

function mds_push($id, $server, $port, $pins, $dport, $title, $content, $content_type="text/plain", $notify=null) {
	#_log("id: $id sever: $server port: $port pins $pins dport: $dport, content: $content content_type: $content_type notify: $notify");
	if(is_null($notify)) {
		$notify = MDS_PUSH_NOTIFY_URI;
	}

	$curl = new cURL();

	$url = "http://{$server}:{$port}/push?";

	if(is_array($pins)) {
		for($i = 0; $i < count($pins); $i ++) {
			if($i > 0) {
				$url .= '&';
			}
			$url .= "DESTINATION={$pins[$i]}";
		}
	}else {
		$url .= "DESTINATION={$pins}";
	}
	$url .= "&PORT={$dport}&REQUESTURI=/";

	$headers = array(
		'Content-Type'            => $content_type,
		'X-RIM-Push-Reliability-Mode' => 'APPLICATION-PREFERRED',
		'X-RIM-Push-Use-Coverage' => 'true'
	);
	if(isset($id) && !empty($id) && $id > 0) {
		$headers['X-RIM-Push-ID'] = $id;
	}
	if(!is_null($notify) && $notify != "") {
		$headers['X-RIM-Push-NotifyURL'] = $notify;
	}

	if(!empty($title)) {
		$headers['X-Rim-Push-Description'] = $title;
	}

	foreach($headers as $key=>$val) {
		$curl->header("{$key}: {$val}");
	}

	$ret = $curl->post($url, $content);

	if($ret === FALSE || $curl->getResponseCode() != 200) {
		return $ret;
	}else {
		return TRUE;
	}
}

?>
