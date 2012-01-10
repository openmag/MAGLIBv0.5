<?php

define("MDS_PUSH_NOTIFY_URI", "");

require_once("config.inc.php");
require_once("../LIBUI.inc");
require_once("../include/curl.inc.php");
require_once("include/lcs.inc.php");
require_once("include/mds_push.inc.php");
require_once("include/aog_push.inc.php");
require_once("include/pag_push.inc.php");
require_once("magpush.inc.php");
require_once('include/class.magservices.php');

if(isset($_server) && isset($_account) && isset($_msg)) {
	if(!isset($_module)) {
		$_module = null;
	}
	if(!isset($_app)) {
		$_app = null;
	}

	$service = new MagServices();

	$ret = $service->pushmsg($_server, $_account, $_msg, $_module, $_app);

	if(strlen($ret) == 0) {
		header("X-Anhe-Result: TRUE");
	} else {
		header("X-Anhe-Result: FALSE");
		echo "ERROR: ".$ret;
	}
} else {
	header("X-Anhe-Result: FALSE");
	echo "ERROR: Not enough parameters!";
}

?>
