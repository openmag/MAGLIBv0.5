<?

include "include/config.php";
include "include/db.inc.php";
include "include/utils.inc.php";
include "include/relation.inc.php";
include "include/image.inc.php";
include "include/mail.inc.php";
include "include/message.inc.php";
include "include/wedding.inc.php";
include "include/partner.inc.php";
include "include/get_form_data.inc.php";

$_db_conn = db_open();

if(!isset($_action) || !isset($_callback)) {
	echo "Unknown action!";
	exit();
}

$success = 0;

if($_action=="ADD_COMMENT" && isset($_visitor_id) && isset($_obj_id) && isset($_comment_texts) && $_comment_texts != "") {
}

db_close($_db_conn);

if($success == 1) {
	if($_callback == '__nocallback') {
		echo "---jiajia__nocallback_boundary---\n\r";
		echo $msg;
	}else {
		if(isset($_callback_id)) {
			$script = "parent.$_callback('{$_callback_id}', '{$msg}');\n";
		}else {
			$script = "parent.$_callback('{$msg}');\n";
		}
		send_script($script);
	}
}

?>
