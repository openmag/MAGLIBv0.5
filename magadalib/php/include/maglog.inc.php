<?php

function magLog($msg) {
	if(!defined("MAG_LOG_DIR")) {
		die("MAG_LOG_DIR is not defined!");
	}
	$filepath = MAG_LOG_DIR.DIRECTORY_SEPARATOR."mag.log.".date("Y-m-d").".txt";
	$fh = fopen($filepath, 'aw') or die("can't open log file $filepath");
	fwrite($fh, "[".date("H:i:s")."] ".$msg."\n");
	fclose($fh);
}

function magLogReq($req, $msg) {
	magLog("(".($req->getPIN())."/".($req->getHandler()).") ".$msg);
}

?>
