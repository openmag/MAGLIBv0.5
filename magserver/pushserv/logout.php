<?php

include "../include/utils.inc.php";

session_start();
$_SESSION['_user'] = '';

redirect('admin.php');

?>
