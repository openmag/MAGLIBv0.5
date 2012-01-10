<?php

$__inc_path = dirname(__FILE__)."/include/";

define("MAG_COMPRESS_AUTO", FALSE);
define("MAG_COMPRESS_THRESHOLD", 1024*8);

include_once($__inc_path."curl.inc.php");
include_once($__inc_path."maglog.inc.php");
include_once($__inc_path."magpushclient.inc.php");
include_once($__inc_path."magreq.inc.php");
include_once($__inc_path."magstd.inc.php");
include_once($__inc_path."magui.inc.php");
include_once($__inc_path."magdb.inc.php");

?>
