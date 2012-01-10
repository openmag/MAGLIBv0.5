<?php

include "config.inc.php";
include "../LIBUI.inc";
include "magpush.inc.php";


#foreach($_SERVER as $key=>$val) {
#	_log("NOTIFY: {$key}: {$val}");
#}

function getPushID() {
	if(array_key_exists("HTTP_X_RIM_PUSH_ID", $_SERVER)) {
		return $_SERVER["HTTP_X_RIM_PUSH_ID"];
	}else {
		return FALSE;
	}
}

function getPushDest() {
	if(array_key_exists("HTTP_X_RIM_PUSH_DESTINATION", $_SERVER)) {
		return $_SERVER["HTTP_X_RIM_PUSH_DESTINATION"];
	}else {
		return FALSE;
	}
}

function getDeviceState() {
	if(array_key_exists("HTTP_X_RIM_DEVICE_STATE", $_SERVER)) {
		return $_SERVER["HTTP_X_RIM_DEVICE_STATE"];
	}else {
		return FALSE;
	}
}

function getPushStatus() {
	if(array_key_exists("HTTP_X_RIM_PUSH_STATUS", $_SERVER)) {
		return $_SERVER["HTTP_X_RIM_PUSH_STATUS"];
	}else {
		return FALSE;
	}
}


$pushe = new MAGPushEngine();
if(FALSE !== getPushID()) {
	$pushe->pushlog_state(getPushID(), MAG_PUSH_CONFIRM);
}


?>
