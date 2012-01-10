<?php

if(!defined("_DB_INC_")) {
	define("_DB_INC_", "true");

	$__inc_path = dirname(__FILE__)."/include/";

	include_once($__inc_path."sysconfig.inc.php");
	include_once($__inc_path."config.inc.php");
	include_once($__inc_path."db.inc.php");
}

?>
