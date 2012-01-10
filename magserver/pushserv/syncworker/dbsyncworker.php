#!/usr/bin/php
<?php

$exts = array("json", "curl", "mysqli", "gd", "xml");
foreach($exts as $ext) {
	if(!extension_loaded($ext)) {
		echo ("PHP Extension {$ext} not loaded!");
		exit(-1);
	}
}

while(1) {
	system("php ".dirname(__FILE__).DIRECTORY_SEPARATOR."dbsync.php");
	sleep(60);
}

?>
