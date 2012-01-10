<?php

include_once("include/syscheck.inc.php");

$ret = system_check();

if($ret !== TRUE) {
	echo "System check failed!.....\n";

	echo $ret;

	echo "\nPlease correct system settings and try again!\n\n";

	exit(1);
}else {
	exit(0);
}

?>
