<?php

function readCookie($id) {
	if(isset($_COOKIE[$id])) {
		return $_COOKIE[$id];
	}else {
		return null;
	}
}

function createCookie($id, $val, $day) {
	if(is_null($day)) {
		setCookie($id, $val);
	}else {
		setCookie($id, $val, time() + $day*24*3600);
	}
}

function eraseCookie($id) {
	setCookie($id, '', time() - 3600*24);
}

?>
