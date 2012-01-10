<?php

if(!defined("_UI_INC_")) {
	define("_UI_INC_", "true");

	$__libui_path = _MAG_INC_PATH_."/libui/";
	$__inc_path = $__libui_path."include/";

	include_once($__inc_path."config.inc.php");
	include_once($__inc_path."const.inc.php");
	include_once($__inc_path."format.inc.php");
	include_once($__inc_path."cookie.inc.php");
	include_once($__inc_path."utils.inc.php");
	include_once($__inc_path."get_form_data.inc.php");
}

?>
