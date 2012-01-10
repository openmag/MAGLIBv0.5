<?php

define("JIALIB_NAME", "jialib");
define("JIALIB_VERSION", '0.3');

define("LIB_ROOT",    _JIALIB_INC_URL_."/libui/");

define("IMAGE_ROOT",  _JIALIB_INC_URL_."/libui/images/");

define("SCRIPT_ROOT", _JIALIB_INC_URL_."/libui/scripts/");
define("DEBUG_SCRIPT_ROOT", _JIALIB_INC_URL_."/libui/scripts/");

define("SCRIPT_ROOT_DIR",       $__libui_path."scripts/");
define("DEBUG_SCRIPT_ROOT_DIR", $__libui_path."scripts/");

define("SITE_KEYWORDS", "");
define("SITE_DESCRIPTIONS", "");

if(!defined("LIBUI_DB_QUERY_SCRIPT")) {
	die("LIBUI_DB_QUERY_SCRIPT is not defined!");
}

if(!defined("LIBUI_REQUEST_HANDLER_SCRIPT")) {
	die("LIBUI_REQUEST_HANDLER_SCRIPT is not defined!");
}

if(!defined("LIBUI_RPC_SCRIPT")) {
	die("LIBUI_RPC_SCRIPT is not defined!");
}

if(!defined("LIBUI_AJAX_DEBUG")) {
	define("LIBUI_AJAX_DEBUG", FALSE);
}

if(!defined("LIBUI_DB_QUERY_DEBUG")) {
	define("LIBUI_DB_QUERY_DEBUG", FALSE);
}

if(!defined("LIBUI_SCRIPTS_EMBEDDED")) {
	define("LIBUI_SCRIPTS_EMBEDDED", FALSE);
}

if(defined("LIBUI_DB_QUERY_FUNC_SCRIPT_PATH")) {
	include_once(LIBUI_DB_QUERY_FUNC_SCRIPT_PATH);
}

?>
