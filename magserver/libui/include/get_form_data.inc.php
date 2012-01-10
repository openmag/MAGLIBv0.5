<?php

# get POST and GET variables
foreach ($_GET as $key => $val) {
	${$key} = $val;
}
foreach ($_POST as $key => $val) {
	${$key} = $val;
}

if(isset($_x)) {
	parse_str(base64_decode($_x));
}

?>
