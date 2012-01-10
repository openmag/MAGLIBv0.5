<?php

function system_check() {
	$exts = array("json", "curl", "mysqli", "gd", "xml");
	foreach($exts as $ext) {
		if(!extension_loaded($ext)) {
			return "PHP Extension {$ext} not loaded!";
		}
	}

	$OS = strtoupper(substr(PHP_OS, 0, 3));
	if($OS == 'LIN' || $OS == 'DAR') {
		# Linux or Darwin
		$php_exe = "php";
		$mysql_exe = "mysql";
	}elseif($OS == 'WIN') {
		# Windows
		$php_exe = "php.exe";
		$mysql_exe = "mysql.exe";
	}else {
		return "Unsupported Operating System!".PHP_OS;
	}

	$php_path = null;
	$mysql_path = null;
	$server_path = null;
	foreach($_SERVER as $key=>$val) {
		if(strtoupper($key) == "PATH") {
			$server_path = $val;
		}
	}
	if(is_null($server_path)) {
		return "Cannot find environment variable PATH";
	}

	$paths = explode(PATH_SEPARATOR, $server_path);
	foreach($paths as $syspath) {
		$syspath = trim($syspath);
		if(!empty($syspath)) {
			if(is_null($php_path) && file_exists($syspath.DIRECTORY_SEPARATOR.$php_exe)) {
				$php_path = $syspath.DIRECTORY_SEPARATOR.$php_exe;
			}
			if(is_null($mysql_path) && file_exists($syspath.DIRECTORY_SEPARATOR.$mysql_exe)) {
				$mysql_path = $syspath.DIRECTORY_SEPARATOR.$mysql_exe;
			}
		}
	}

	if(is_null($php_path)) {
		return "Cannot find php executive in PATH";
	}
	if(is_null($mysql_path)) {
		return "Cannot find mysql executive in PATH";
	}

	if($OS == "LIN") {
		if(!file_exists("/usr/bin/php")) {
			return "No /usr/bin/php presents, please make a symbol link of php executive to /usr/bin/php";
		}
	}

	return TRUE;
}

?>
