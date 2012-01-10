<?php

$cur_dir = dirname(__FILE__);

if(isset($_SERVER) && array_key_exists("DOCUMENT_ROOT", $_SERVER)) {

	$root_dir = $_SERVER["DOCUMENT_ROOT"];
	$cur_url = substr($cur_dir, strlen($root_dir));
	if(substr($cur_url, 0, 1) != DIRECTORY_SEPARATOR) {
        	$cur_url = DIRECTORY_SEPARATOR.$cur_url;
	}
	if(substr($cur_url, strlen($cur_url)-1) != DIRECTORY_SEPARATOR) {
        	$cur_url .= DIRECTORY_SEPARATOR;
	}

	if(DIRECTORY_SEPARATOR != '/') {
		$cur_url = str_replace(DIRECTORY_SEPARATOR, '/', $cur_url);
	}

	define("_MAG_INC_URL_",  $cur_url);
	define("_JIALIB_INC_URL_", _MAG_INC_URL_);

}

if(substr($cur_dir, strlen($cur_dir)-1) != DIRECTORY_SEPARATOR) {
        $cur_dir .= DIRECTORY_SEPARATOR;
}

define("_MAG_INC_PATH_", $cur_dir);

?>
