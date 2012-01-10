<?php

if(!defined("_CURL_INC_")) {
	die("curl.inc.php should be included before aog_push.inc.php");
}

function imsi2mobile($imsi) {
	$table = array(
		"460001804124519" => "13911785165",
		"460000014130986" => "15810033786",
		"460028100534215" => "13426002934",
	);
	if(array_key_exists($imsi, $table)) {
		return $table[$imsi];
	}else {
		return "13800000000";
	}
}

function aog_push($id, $server, $port, $users, $dport, $title, $content, $content_type="text/plain") {
	_log("AOG_PUSH");

	$curl = new cURL();

	$url = "http://{$server}:{$port}/push?";

	if(is_array($users)) {
		for($i = 0; $i < count($users); $i ++) {
			if($i > 0) {
				$url .= '&';
			}
			$url .= "DESTINATION=".urlencode(imsi2mobile($users[$i]));
		}
	}else {
		$url .= "DESTINATION=".urlencode(imsi2mobile($users));
	}
	#$url .= "DESTINATION=15810033786";
	$url .= "&PORT=".urlencode($dport)."&REQUESTURI=/";

	$headers = array(
		'Content-Type'            => $content_type,
		'Content-Length'          => mb_strlen($content, '8bit'),
		'X-RIM-Push-ID'           => $id,
	);

	if(!empty($title)) {
		$headers['X-Rim-Push-Description'] = $title;
	}

	foreach($headers as $key=>$val) {
		$curl->header("{$key}: {$val}");
	}

	_log("AOG_PUSH: {$url}");

	$ret = $curl->post($url, $content);

	if($ret === FALSE || $curl->getResponseCode() != 200) {
		_log($curl->getResponse());
		#echo ($ret)."\n";
		#echo $curl->getResponseCode()."\n";
		return FALSE;
	}else {
		return TRUE;
	}
}

?>
