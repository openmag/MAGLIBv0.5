<?php

/***************************************************
 ***************************************************
 *
 *      **    **        **       *********
 *     ****  ****      ****     **       *
 *    **  ****  **    **  **   **   ******
 *   **    **    **  ********   **  *   **
 *  **            ****      **   ********
 *
 *
 * MAG Server Main configuration
 *
 * This file is read ONLY
 *
 * End users: !! Do not modify !!
 *
 * Developers: Modify with caution!!
 *
 ***************************************************
 ***************************************************
 *
 * CopyRight (c) Anhe Innovation Technology
 *
 ***************************************************
 ***************************************************/

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 'On');
ini_set('session.gc_probability', '0');
ini_set('default_socket_timeout', '1200');
ini_set('mysql.connect_timeout', '-1');

define("LOCAL_CONFIG_DIR", dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."local");
define("CUSTOM_CONFIG", LOCAL_CONFIG_DIR.DIRECTORY_SEPARATOR."custom_config.php");

$_mysql_host = "127.0.0.1";
$_mysql_user = "root";
$_mysql_password = "";
$_mysql_port = "3306";
$_mysql_db = "mag";

if(file_exists(CUSTOM_CONFIG)) {
	include_once(CUSTOM_CONFIG);
}

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."magversion.inc");

define("MYSQL_DB_HOST", $_mysql_host);
define("MYSQL_DB_USER", $_mysql_user);
define("MYSQL_DB_PASS", $_mysql_password);
define("MYSQL_DB_PORT", $_mysql_port);

define("MYSQL_DB_NAME", $_mysql_db);

define("SITE_BASE_DIR", dirname(__FILE__));

if(!defined("SITE_BASE_URL")) {
	if(isset($_SERVER) && array_key_exists('HTTP_HOST', $_SERVER) && !empty($_SERVER['HTTP_HOST'])) {
		define("SITE_BASE_URL", dirname($_SERVER['SCRIPT_NAME']));
	}
}

if(!defined("LOG_DIR_NAME")) {
	define("LOG_DIR_NAME",  "log");
}

if(!defined("LOG_DIR")) {
	define("LOG_DIR", LOCAL_CONFIG_DIR.DIRECTORY_SEPARATOR.LOG_DIR_NAME);
}

if(!defined("TMP_DIR")) {
	define("TMP_DIR", LOCAL_CONFIG_DIR.DIRECTORY_SEPARATOR."tmp");
}

define("LIBUI_USER_SCRIPTS_DIR", "scripts");

if(defined("SITE_BASE_URL")) {
	define("LIBUI_USER_SCRIPTS_DIR_URL", SITE_BASE_URL."/".LIBUI_USER_SCRIPTS_DIR);

	define("LIBUI_DB_QUERY_SCRIPT", SITE_BASE_URL."/db_query.php");

	define("LIBUI_REQUEST_HANDLER_SCRIPT", SITE_BASE_URL."/request_handler.php");

	define("LIBUI_RPC_SCRIPT", SITE_BASE_URL."/rpc_handler.php");

	if(!defined("MDS_PUSH_NOTIFY_URI")) {
		define("MDS_PUSH_NOTIFY_URI", "http://".$_SERVER['HTTP_HOST'].SITE_BASE_URL."/mdspush_notifier.php");
	}

	define("NOTIFY_SERVICE_URI", "http://".$_SERVER['HTTP_HOST'].SITE_BASE_URL."/notify.php");
}else {
	define("MDS_PUSH_NOTIFY_URI", "");
}

define("MDS_PUSH_CLIENT_PORT", "22294");
define("AOG_PUSH_CLIENT_PORT", "100031100099");

define("ANDROID_MDM_PORT", "22295");

define("LIBUI_AJAX_DEBUG", FALSE);
define("LIBUI_SCRIPTS_EMBEDDED", TRUE);
define("LIBDB_REMOVE_EMPTY_FIELDS", TRUE);
define("LIBUI_DB_QUERY_DEBUG", FALSE);

?>
