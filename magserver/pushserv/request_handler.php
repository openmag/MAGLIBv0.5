<?php

include_once("config.inc.php");
include_once("../LIBUI.inc");
include_once("../include/curl.inc.php");
include_once("include/syncdb.inc.php");
include_once("include/account.inc.php");
include_once("include/pag_push.inc.php");
include_once("magpush.inc.php");

$handler = new RequestHandler();

function addMDSServer(&$req) {
	$db = mysql_open();
	/*if($req->_itu_state == 1) {
		if($db->update("itu_state=0", "push_config_tbl")) {
		}else {
			$req->error("更新状态错误！");
			return FALSE;
		}
	}else {
		if($db->get_item_count("push_config_tbl", "itu_state=1") == 0) {
			$req->_itu_state = 1;
		}
	}*/
	$state = $req->_itu_state;
	if(empty($state)) {
		$state = 0;
	}
	$sql = "insert into push_config_tbl(vc_protocol, vc_mdsserver, iu_mdsport, itu_state, iu_interval, dt_create) values('{$req->_vc_protocol}', '{$req->_vc_mdsserver}', {$req->_iu_mdsport}, {$state}, {$req->_iu_interval}, NOW())";
	if($db->query($sql)) {
	}else {
		$req->error("添加数据出错！{$sql}");
		return FALSE;
	}
	return TRUE;
}
$handler->register("ADD_MDSSERVER", "addMDSServer");

function delMDSServer(&$req) {
	$db = mysql_open();
	if($db->delete("push_config_tbl", "id={$req->_id}")) {
	}else {
		$req->error("删除数据出错！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("DEL_MDSSERVER", "delMDSServer");

function enableMDSServer(&$req) {
	$db = mysql_open();
	if($db->update("itu_state=1", "push_config_tbl", "id={$req->_id}")) {
	}else {
		$req->error("设置数据出错！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("ENABLE_MDSSERVER", "enableMDSServer");

function disableMDSServer(&$req) {
	$db = mysql_open();
	if($db->update("itu_state=0", "push_config_tbl", "id={$req->_id}")) {
	}else {
		$req->error("设置数据出错！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("DISABLE_MDSSERVER", "disableMDSServer");

function cleanPushLog(&$req) {
	$db = mysql_open();
	if($db->delete("pushlog_tbl", "")) {
	}else {
		$req->error("删除推送日志出错！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("CLEAN_PUSHLOG", "cleanPushLog");

function rawInit(&$req) {
	$db = mysql_open($req->_mysql_host, $req->_mysql_user, $req->_mysql_password, '', $req->_mysql_port);
	if(is_null($db)) {
		$req->error("MySQL数据库设置错误！");
		return FALSE;
	}
	$rebuild_db = FALSE;
	if(isset($req->_rebuild_db) && $req->_rebuild_db == 'TRUE') {
		$rebuild_db = TRUE;
	}
	if($db->query("use {$req->_mysql_db}")) {
	} else {
		if($db->query("create database {$req->_mysql_db}")) {
			if($db->query("use {$req->_mysql_db}")) {
				$rebuild_db = TRUE;
			}else {
				$req->error("无法使用数据库{$req->_mysql_db}");
				return FALSE;
			}
		}else {
			$req->error("无法创建数据库{$req->_mysql_db}！");
			return FALSE;
		}
	}
	if($rebuild_db) {
		$sql_file = dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."mag.sql";
		if(file_exists($sql_file)) {
			#echo $sql_file;
		}else {
			$req->error("数据库定义文件不存在！");
			return FALSE;
		}
		$command = "mysql -u {$req->_mysql_user} -h $req->_mysql_host --password={$req->_mysql_password} --port={$req->_mysql_port} {$req->_mysql_db} < {$sql_file}";
		#echo $command;
		$retline = system($command, $retval);
		if($retval != 0) {
			$req->error("导入数据库数据失败！".$retline);
			return FALSE;
		}
	}

	if(createCacheUpdateView($db, $req->_fast_interval, $req->_slow_interval, $req->_poll_threshold)) {
	}else {
		$req->error("创建cache_update_view失败！");
		return FALSE;
	}

	$fh = fopen(CUSTOM_CONFIG, "w");
	if(FALSE !== $fh) {
		fwrite($fh, "<?php\n");
		fwrite($fh, "\$_admin_password=\"".encrypt_password($req->_admin_password)."\";\n");
		fwrite($fh, "\$_mysql_host=\"{$req->_mysql_host}\";\n");
		fwrite($fh, "\$_mysql_user=\"{$req->_mysql_user}\";\n");
		fwrite($fh, "\$_mysql_password=\"{$req->_mysql_password}\";\n");
		fwrite($fh, "\$_mysql_port=\"{$req->_mysql_port}\";\n");
		fwrite($fh, "\$_mysql_db=\"{$req->_mysql_db}\";\n");
		fwrite($fh, "?>\n");

		$push_home = SITE_BASE_DIR.DIRECTORY_SEPARATOR."pushworker".DIRECTORY_SEPARATOR;
		$sync_home = SITE_BASE_DIR.DIRECTORY_SEPARATOR."syncworker".DIRECTORY_SEPARATOR;
		$etc_dir = LOCAL_CONFIG_DIR.DIRECTORY_SEPARATOR."etc".DIRECTORY_SEPARATOR;
		$OS = strtoupper(substr(PHP_OS, 0, 3));

		if($OS == 'LIN' || $OS == 'DAR') { # Linux, Darwin
			# make Linux push startup
			$content = file_get_contents($push_home."push_start.sh.tmpl");
			$content = str_replace('%%PUSH_HOME%%', $push_home, $content);
			file_put_contents($etc_dir."push_start.sh", $content);
			chmod($etc_dir."push_start.sh", 755);
			# make Linux sync startup
			$content = file_get_contents($sync_home."sync_start.sh.tmpl");
			$content = str_replace('%%SYNC_HOME%%', $sync_home, $content);
			file_put_contents($etc_dir."sync_start.sh", $content);
			chmod($etc_dir."sync_start.sh", 755);
		}elseif($OS == 'WIN') { # Windows
			# make windows push startup
			$content = file_get_contents($push_home."push_service_install.bat.tmpl");
			$content = str_replace('%%PUSH_HOME%%', $push_home, $content);
			file_put_contents($etc_dir."push_service_install.bat", $content);
			$content = file_get_contents($push_home."push_service_uninstall.bat.tmpl");
			$content = str_replace('%%PUSH_HOME%%', $push_home, $content);
			file_put_contents($etc_dir."push_service_uninstall.bat", $content);
			# make windows sync startup
			$content = file_get_contents($sync_home."sync_service_install.bat.tmpl");
			$content = str_replace('%%SYNC_HOME%%', $sync_home, $content);
			file_put_contents($etc_dir."sync_service_install.bat", $content);
			$content = file_get_contents($sync_home."sync_service_uninstall.bat.tmpl");
			$content = str_replace('%%SYNC_HOME%%', $sync_home, $content);
			file_put_contents($etc_dir."sync_service_uninstall.bat", $content);
		}else {
			$req->error("未知操作系统");
			return FALSE;
		}
	}else {
		$req->error("不能写".CUSTOM_CONFIG);
		return FALSE;
	}
	return TRUE;
}
$handler->register("RAW_INIT", "rawInit");

function login(&$req) {
	global $_admin_password;
	if($req->_mag_user == 'admin') {
		if(encrypt_password($req->_mag_password) !== $_admin_password) {
			$req->error("密码不正确!");
			return FALSE;
		}
	}else {
		$db = mysql_open();
		if($db->get_item_count("user_tbl", "vc_user='{$req->_mag_user}' and vc_password='".encrypt_password($req->_mag_password)."'") == 0) {
			$req->error("密码不正确!");
			return FALSE;
		}
	}
	session_start();
	$_SESSION['_user'] = $req->_mag_user;
	return TRUE;
}
$handler->register("LOGIN", "login");


function change_admin_password(&$req) {
        $fh = fopen(CUSTOM_CONFIG, "w");
        if(FALSE !== $fh) {
                fwrite($fh, "<?php\n");
                fwrite($fh, "\$_admin_password=\"".encrypt_password($req->_admin_password)."\";\n");
                fwrite($fh, "\$_mysql_host=\"".MYSQL_DB_HOST."\";\n");
                fwrite($fh, "\$_mysql_user=\"".MYSQL_DB_USER."\";\n");
                fwrite($fh, "\$_mysql_password=\"".MYSQL_DB_PASS."\";\n");
                fwrite($fh, "\$_mysql_port=\"".MYSQL_DB_PORT."\";\n");
                fwrite($fh, "\$_mysql_db=\"".MYSQL_DB_NAME."\";\n");
                fwrite($fh, "?>\n");
        }else {
                $req->error("不能写".CUSTOM_CONFIG);
                return FALSE;
        }
	return TRUE;
}
$handler->register("CHANGE_ADMIN_PASSWORD", "change_admin_password");

function CHANGE_CACHE_UPDATE_VIEW(&$req) {
	$db = mysql_open();
	if(createCacheUpdateView($db, $req->_fast_interval, $req->_slow_interval, $req->_poll_threshold)) {
		return TRUE;
	}else {
		$req->error("更新数据库失败！");
		return FALSE;
	}
}
$handler->register("CHANGE_CACHE_UPDATE_VIEW", "CHANGE_CACHE_UPDATE_VIEW");

function add_user(&$req) {
	$db = mysql_open();
	if($db->get_item_count("user_tbl", "vc_user='{$req->_vc_user}'") == 0) {
		$sql = "insert into user_tbl(vc_user, vc_password, vc_roles, iu_group, vc_groups, vc_name) values('{$req->_vc_user}', '".encrypt_password($req->_vc_password)."', '', 0, '', '{$req->_vc_name}')";
		if(!$db->query($sql)) {
			$req->error("插入用户数据出错！");
			return FALSE;
		}
	}else {
		$req->error("账号{$req->_vc_user}已经被人使用！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("ADD_USER", "add_user");

function del_user(&$req) {
	$db = mysql_open();
	if(!$db->delete("user_tbl", "id={$req->_id}")) {
		$req->error("该账号不存在！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("DEL_USER", "del_user");

function update_user(&$req) {
	$db = mysql_open();
	if($db->get_item_count("user_tbl", "id!={$req->_id} and vc_user='{$req->_vc_user}'") > 0) {
		$req->error("该用户名已经被使用！");
		return FALSE;
	}else {
		if(!$db->update("vc_user='{$req->_vc_user}', vc_name='{$req->_vc_name}'", "user_tbl", "id={$req->_id}")) {
			$req->error("更新用户信息出错！"."vc_name='{$req->_vc_name}'"."id={$req->_id}");
			return FALSE;
		}
	}
	return TRUE;
}
$handler->register("UPDATE_USER", "update_user");

function update_user_password(&$req) {
	$db = mysql_open();
	$passwd = encrypt_password($req->_vc_password);
	if(!$db->update("vc_password='{$passwd}'", "user_tbl", "id={$req->_id}")) {
		$req->error("更新密码出错！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("UPDATE_USER_PASSWD", "update_user_password");


function enable_sync_table(&$req) {
	$db = mysql_open();
	if($db->update("ti_enable=1", "syncdb_tbl", "id={$req->_id}")) {
	}else {
		$req->error("开启表同步出错！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("ENABLE_SYNC_TABLE", 'enable_sync_table');

function disable_sync_table(&$req) {
	$db = mysql_open();
	if($db->update("ti_enable=0", "syncdb_tbl", "id={$req->_id}")) {
	}else {
		$req->error("开启表同步出错！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("DISABLE_SYNC_TABLE", 'disable_sync_table');

function __add_sync_table_internal(&$db, $name, $dataurl, $firstsync, $intval, $desc) {
	if($db->get_item_count("syncdb_tbl", "vc_name='{$name}'") > 0) {
		return "表{$name}已经存在！";
	}
	$sql = "insert into syncdb_tbl(vc_name, vc_dataurl, dt_firstsync, ui_updateintv, bl_description, ti_enable, ti_isdirty) values('{$name}', '{$dataurl}', '{$firstsync}', {$intval}, '{$desc}', 0, 1)";
	if($db->query($sql)) {
		return TRUE;
	}else {
		return "插入数据失败！";
	}
}

function add_sync_table(&$req) {
	$db = mysql_open();
	$ret = __add_sync_table_internal($db, $req->_vc_name, $req->_vc_dataurl, $req->_dt_firstsync, $req->_ui_updateintv, $req->_bl_description);
	if($ret === TRUE) {
		return TRUE;
	}else{
		$req->error($ret);
		return FALSE;
	}
}
$handler->register("ADD_SYNC_TABLE", "add_sync_table");

function update_sync_table(&$req) {
	$db = mysql_open();
	if($db->update("vc_dataurl='{$req->_vc_dataurl}', dt_firstsync='{$req->_dt_firstsync}', ui_updateintv={$req->_ui_updateintv}, bl_description='{$req->_bl_description}'", "syncdb_tbl", "id={$req->_id}")) {
	}else {
		$req->error("更新失败!");
		return FALSE;
	}
	return TRUE;
}
$handler->register("UPDATE_SYNC_TABLE", "update_sync_table");

function rebuild_sync_table(&$req) {
	$db = mysql_open();
	$syncdb = new SyncTable($req->_vc_name, $db);
	if($syncdb->destroy()) {
		$syncdb = new SyncTable($req->_vc_name, $db);
		$result = $syncdb->sync();
		if($result !== TRUE) {
			$req->error("同步失败！".$result);
			return FALSE;
		}
	}else {
		$req->error("清除同步数据失败！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("REBUILD_SYNC_TABLE", "rebuild_sync_table");

function delete_sync_table(&$req) {
	$db = mysql_open();
	$syncdb = new SyncTable($req->_vc_name, $db);
	if($syncdb->destroy()) {
		if($db->delete("syncdb_column_tbl", "tbl_id={$req->_id}")) {
			if($db->delete("syncdb_tbl", "id={$req->_id}")) {
			}else {
				$req->error("删除数据表失败！");
				return FALSE;
			}
		}else {
			$req->error("删除表列失败！");
			return FALSE;
		}
	}else {
		$req->error("清除同步数据失败！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("DELETE_SYNC_TABLE", "delete_sync_table");

function __add_sync_table_column_internal(&$db, $id, $name, $datatype, $dtparam, $isnull, $isprimary) {
	if($db->get_item_count("syncdb_column_tbl", "tbl_id={$id} and vc_name='{$name}'") > 0) {
		return "列名已经存在！";
		return FALSE;
	}
	if(!is_numeric($isnull) || $isnull != 1) {
		$isnull = 0;
	}
	if(!is_numeric($isprimary) || $isprimary != 1) {
		$isprimary = 0;
	}
	$sql = "insert into syncdb_column_tbl(tbl_id, vc_name, vc_datatype, vc_dtparam, ti_isnull, ti_isprimary, dt_whenadd) values({$id}, '{$name}', '{$datatype}', '{$dtparam}', {$isnull}, {$isprimary}, NOW())";
	if($db->query($sql)) {
		$db->update("ti_isdirty=1", "syncdb_tbl", "id={$id}");
		return TRUE;
	}else {
		return "插入数据失败";
	}
}

function add_sync_table_column(&$req) {
	$db = mysql_open();
	if(!isset($req->_ti_isnull) || !is_numeric($req->_ti_isnull) || $req->_ti_isnull != 1) {
		$req->_ti_isnull = 0;
	}
	if(!isset($req->_ti_isprimary) || !is_numeric($req->_ti_isprimary) || $req->_ti_isprimary!= 1) {
		$req->_ti_isprimary = 0;
	}
	$ret = __add_sync_table_column_internal($db, $req->_tbl_id, $req->_vc_name, $req->_vc_datatype, $req->_vc_dtparam, $req->_ti_isnull, $req->_ti_isprimary);
	if($ret === TRUE) {
		return TRUE;
	}else {
		$req->error($ret);
		return FALSE;
	}
}
$handler->register("ADD_SYNC_TABLE_COLUMN", "add_sync_table_column");

function update_sync_table_column(&$req) {
	$db = mysql_open();
	if(!is_numeric($req->_ti_isnull) || $req->_ti_isnull != 1) {
		$req->_ti_isnull = 0;
	}
	if(!is_numeric($req->_ti_isprimary) || $req->_ti_isprimary != 1) {
		$req->_ti_isprimary = 0;
	}
	if($db->update("vc_datatype='{$req->_vc_datatype}', vc_dtparam='{$req->_vc_dtparam}', ti_isnull={$req->_ti_isnull}, ti_isprimary={$req->_ti_isprimary}", "syncdb_column_tbl", "tbl_id={$req->_tbl_id} and vc_name='{$req->_vc_name}'")) {
		$db->update("ti_isdirty=1", "syncdb_tbl", "id={$req->_tbl_id}");
	}else {
		$req->error("更新失败！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("UPDATE_SYNC_TABLE_COLUMN", "update_sync_table_column");

function delete_sync_table_column(&$req) {
	$db = mysql_open();
	if($db->delete("syncdb_column_tbl", "tbl_id={$req->_tbl_id} and vc_name='{$req->_vc_name}'")) {
		$db->update("ti_isdirty=1", "syncdb_tbl", "id={$req->_tbl_id}");
	}else {
		$req->error("删除错误！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("DELETE_SYNC_TABLE_COLUMN", "delete_sync_table_column");

function __add_sync_table_index(&$db, $tblid, $colnames) {
	$sql = "INSERT INTO syncdb_index_tbl(tbl_id, vc_colnames, dt_whenadd) values({$tblid}, '{$colnames}', NOW())";
	if($db->query($sql)) {
		$db->update("ti_isdirty=1", "syncdb_tbl", "id={$tblid}");
		return TRUE;
	}else {
		return "创建索引出错！";
	}
}

function add_sync_table_index(&$req) {
	$db = mysql_open();
	if(count($req->_column) > 0) {
		$colnames = implode(", ", $req->_column);
		$ret = __add_sync_table_index($db, $req->_tbl_id, $colnames);
		if($ret !== TRUE) {
			$req->error($ret);
			return FALSE;
		}
	}else {
		$req->error("索引必须包含至少一个数据域！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("ADD_SYNC_TABLE_INDEX", "add_sync_table_index");

function delete_sync_table_index(&$req) {
	$db = mysql_open();
	if($db->delete("syncdb_index_tbl", "id={$req->_id}")) {
		$db->update("ti_isdirty=1", "syncdb_tbl", "id={$req->_tbl_id}");
	}else {
		$req->error("删除错误！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("DELETE_SYNC_TABLE_INDEX", "delete_sync_table_index");

function manual_sync_table(&$req) {
	$sync = new SyncTable($req->_vc_name);
	$result = $sync->sync();
	if($result !== TRUE) {
		$req->error("同步失败！".$result);
		return FALSE;
	}
	return TRUE;
}
$handler->register("MANUAL_SYNC_TABLE", "manual_sync_table");

function drop_sync_table_of_version(&$req) {
	$sync = new SyncTable($req->_table);
	if($sync->getCurrentVersion() == $req->_version) {
		$req->error("不能删除当前版本！");
		return FALSE;
	}else {
		$sync->setCurrentVersion($req->_version);
		$ret = $sync->dropTable();
		if($ret !== TRUE) {
			$req->error($ret);
			return FALSE;
		}
	}
	return TRUE;
}
$handler->register("DROP_SYNC_TABLE_OF_VERSION", "drop_sync_table_of_version");

function drop_sync_tables_of_prev_version(&$req) {
	$db = mysql_open();
	$db_rows = $db->get_arrays("version", "__syncdb_".$req->_table."_version", "version < {$req->_version}", "version asc");
	if(!is_null($db_rows)) {
		$sync = new SyncTable($req->_table);
		for($i = 0; $i < count($db_rows); $i ++) {
			$sync->setCurrentVersion($db_rows[$i][0]);
			$ret = $sync->dropTable();
			if($ret !== TRUE) {
				$req->error("删除表版本".$db_rows[$i][0]."出错: ".$ret);
				return FALSE;
			}
		}
	}else {
		$req->error("获取更早版本数据表出错！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("DROP_SYNC_TABLES_OF_PREV_VERSION", "drop_sync_tables_of_prev_version");

function export_table_definition(&$req) {
	$db = mysql_open();

#        vc_name VARCHAR(64) NOT NULL,
#        vc_dataurl VARCHAR(255) NOT NULL,
#        vc_timestamp VARCHAR(40) NOT NULL DEFAULT '',
#        ui_updateintv INT UNSIGNED NOT NULL,
#        bl_description BLOB,
#        dt_lastsync DATETIME,
#        ti_status TINYINT UNSIGNED,
#        ti_enable TINYINT UNSIGNED,

#        vc_name VARCHAR(64) NOT NULL,
#        vc_datatype VARCHAR(128) NOT NULL,
#        vc_dtparam  VARCHAR(32) NOT NULL,
#        ti_isnull TINYINT UNSIGNED,
#        ti_isprimary TINYINT UNSIGNED,

	$db_row = $db->get_single_assoc("vc_name, vc_dataurl, dt_firstsync, ui_updateintv, bl_description", "syncdb_tbl", "id={$req->_id}");
	if(!is_null($db_row)) {
		$rows = $db->get_assocs("vc_name, vc_datatype, vc_dtparam, ti_isnull, ti_isprimary", "syncdb_column_tbl", "tbl_id={$req->_id}", "dt_whenadd asc");
		if(!is_null($rows)) {
			$db_row["_columns"] = $rows;
			$rows = $db->get_assocs("vc_colnames", "syncdb_index_tbl", "tbl_id={$req->_id}", "dt_whenadd asc");
			if(!is_null($rows)) {
				$db_row["_indexes"] = $rows;
				$req->setContentType("application/json");
				$req->setDownloadFileName($db_row["vc_name"].".dat");
				$req->response(json_encode($db_row));
				return TRUE;
			}
		}
	}
	$req->error("获取数据表信息出错！");
	return FALSE;
}
$handler->register("EXPORT_TABLE_DEFINITION", "export_table_definition");

function import_table_definition(&$req) {
	if(isset($req->_table_definition) && $req->_table_definition->isSuccess()) {
		$db = mysql_open();
		$def = json_decode($req->_table_definition->getContents());
		$ret = __add_sync_table_internal($db, $def->vc_name, $def->vc_dataurl, $def->dt_firstsync, $def->ui_updateintv, $def->bl_description);
		if($ret === TRUE) {
			$id = $db->last_id();
			foreach($def->_columns as $col) {
				__add_sync_table_column_internal($db, $id, $col->vc_name, $col->vc_datatype, $col->vc_dtparam, $col->ti_isnull, $col->ti_isprimary);
			}
			foreach($def->_indexes as $index) {
				__add_sync_table_index($db, $id, $index->vc_colnames);
			}
			return TRUE;
		}else {
			$msg = $ret;
		}
		
	}else {
		$msg = "上载文件出错！";
	}
	$req->error($msg);
	return FALSE;
}
$handler->register("IMPORT_TABLE_DEFINITION", "import_table_definition");

function unregister_device(&$req) {
	$push_engine = new MAGPushEngine();
	if($push_engine->unregisterDevice($req->_module, $req->_pin)) {
		return TRUE;
	}else {
		$req->error("注销设备出错！");
		return FALSE;
	}
}
$handler->register("UNREGISTER_DEVICE", "unregister_device");

function DELETE_ACCOUNT(&$req) {
	$db = mysql_open();
	$account = new Account($db, $req->_account, $req->_module);
	if($account->getPIN() != '') {
		$push_engine = new MAGPushEngine($db);
		if($push_engine->unregisterDevice($req->_module, $account->getPIN())) {
		}else {
			$req->error("注销设备出错！");
			return FALSE;
		}
	}
	if($account->delete()) {
	}else {
		$req->error("删除账号出错！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("DELETE_ACCOUNT", "DELETE_ACCOUNT");

function BIND_ACCOUNT_DEVICE(&$req) {
	__bind_account_device($req->_account, $req->_pin, $req->_module, TRUE);
	return TRUE;
}
function __bind_account_device($account, $pin, $module, $bind) {
	$db = mysql_open();
	$account = new Account($db, $account, $module);
	if($bind) {
		$account->setPIN($pin);
	}
	$account->setLockPin($bind);
	$account->save();
}
$handler->register("BIND_ACCOUNT_DEVICE", "BIND_ACCOUNT_DEVICE");

function UNBIND_ACCOUNT_DEVICE(&$req) {
	__bind_account_device($req->_account, $req->_pin, $req->_module, FALSE);
	return TRUE;
}
$handler->register("UNBIND_ACCOUNT_DEVICE", "UNBIND_ACCOUNT_DEVICE");

function _edit_account(&$db, $account, $module, $pin, $lockpin) {
	$account = new Account($db, $account, $module);
	if(!is_null($pin) && $pin != '') {
		if(!$account->isSamePIN($pin)) {
			$account->setPIN($pin);
		}
	}else {
		$lockpin = FALSE;
	}
	$account->setLockPIN($lockpin);
	$account->save();
}

function EDIT_ACCOUNT(&$req) {
	$db = mysql_open();
	if(empty($req->_ti_lockpin)) {
		$req->_ti_lockpin = 0;
	}
	_edit_account($db, $req->_vc_account, $req->_vc_module, $req->_vc_pin, $req->_ti_lockpin!=0);
	return TRUE;
}
$handler->register("EDIT_ACCOUNT", "EDIT_ACCOUNT");

function EXPORT_ACCOUNT_SETTINGS(&$req) {
	$db = mysql_open();
	$output = Account::exportAccountCSV($db);
	if(!is_null($output)) {
		$req->setContentType("text/csv");
		$req->setDownloadFileName("account_settings.csv");
		$req->response($output);
	}else {
		$req->error("导出错误！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("EXPORT_ACCOUNT_SETTINGS", "EXPORT_ACCOUNT_SETTINGS");

function IMPORT_ACCOUNT_SETTINGS(&$req) {
	if(isset($req->_account_list) && $req->_account_list->isSuccess()) {
		$db = mysql_open();
		$content = $req->_account_list->getContents();
		while(strlen($content) > 0) {
			$line = getLine($content);
			if(strlen($line) > 0) {
				list($module, $account, $pin, $lockpin) = parsecsv($line);
				if($lockpin == 'true' || $lockpin == '1') {
					$lockpin = TRUE;
				}else {
					$lockpin = FALSE;
				}
				_edit_account($db, $account, $module, $pin, $lockpin);
			}
		}
	}
	return TRUE;
}
$handler->register("IMPORT_ACCOUNT_SETTINGS", "IMPORT_ACCOUNT_SETTINGS");

function EDIT_ACCOUNT_SETTINGS(&$req) {
	$db = mysql_open();
	$account = new Account($db, $req->_vc_account, $req->_vc_module);
	$account->setLockPIN($req->_ti_lockpin=='1');
	$config = array();
	if(isset($req->_cache_enabled) && strlen($req->_cache_enabled) > 0) {
		$config['_cache_enabled'] = $req->_cache_enabled;
	}
	if(isset($req->_cache_default_expire) && strlen($req->_cache_default_expire) > 0) {
		$config['_cache_default_expire'] = "".($req->_cache_default_expire*3600*1000);
	}
	if(isset($req->_relay_enabled) && strlen($req->_relay_enabled) > 0) {
		$config['_relay_enabled'] = $req->_relay_enabled;
	}
	if(isset($req->_relay_server_uri) && strlen($req->_relay_server_uri) > 0) {
		$config['_relay_server_uri'] = $req->_relay_server_uri;
	}
	if(isset($req->_service_uri) && strlen($req->_service_uri) > 0) {
		$config['_service_uri'] = $req->_service_uri;
	}
	if(isset($req->_password_protected) && strlen($req->_password_protected) > 0) {
		$config['_password_protected'] = $req->_password_protected;
	}
	if(isset($req->_attachment_service_uri) && strlen($req->_attachment_service_uri) > 0) {
		$config['_attachment_service_uri'] = $req->_attachment_service_uri;
	}
	if(isset($req->_push_protocol) && strlen($req->_push_protocol) > 0) {
		$config['_push_protocol'] = $req->_push_protocol;
	}
	if(isset($req->_push_server) && strlen($req->_push_server) > 0) {
		$config['_push_server'] = $req->_push_server;
	}
	if(isset($req->_http_request_timeout) && strlen($req->_http_request_timeout) > 0) {
		$config['_http_request_timeout'] = $req->_http_request_timeout;
	}
	$account->setConfig((object)$config);
	$account->save();
	return TRUE;
}
$handler->register("EDIT_ACCOUNT_SETTINGS", "EDIT_ACCOUNT_SETTINGS");


function reset_app_password(&$req) {
	if(isset($req->_module) && isset($req->_password) && (isset($req->_pin) || isset($req->_account))) {
		$pushe = new MAGPushEngine();
		$result = $pushe->resetAppPassword($req->_module, $req->_pin, $req->_account, $req->_password);
		if($result !== TRUE) {
			$req->error($result);
			return FALSE;
		}
	}else {
		$req->error("请求参数错误，正确格式为：_action=RESET_APP_PASSWORD&_module=MODULE&_pin=PIN&_password=PASSWORD");
		return FALSE;
	}
	return TRUE;
}
$handler->register("RESET_APP_PASSWORD", "reset_app_password");


function SEND_NOTIFICATION(&$req) {
	$curl = new cURL();
	$query = array("_server" => $req->_server,
			"_account" => $req->_account,
			"_sound" => $req->_sound,
			"_msg"   => $req->_msg);
	$ret = $curl->post(NOTIFY_SERVICE_URI, http_build_query($query));
	if($ret !== FALSE && $curl->getResponseCode() == 200) {
		#echo $ret;
		$hres2 = $curl->getResponseHeader("X-Anhe-Result");
		if($hres2 == 'TRUE') {
		}else {
			$req->error("推送出错！");
			return FALSE;
		}
	}else if($ret === FALSE) {
		$req->error("访问推送服务URI出错！");
		return FALSE;
	}else {
		$req->error("服务错误！".$curl->getResponseCode());
		return FALSE;
	}
	return TRUE;
}
$handler->register("SEND_NOTIFICATION", "SEND_NOTIFICATION");

function MDM_COMMAND_RESET_PASSWORD(&$req) {
	$command = array("pwd"=>$req->_password);
	return sendMDMCommand($req, $command);
}
$handler->register("MDM_COMMAND_RESET_PASSWORD", "MDM_COMMAND_RESET_PASSWORD");

function MDM_COMMAND_REMOTE_WIPE(&$req) {
	$command = array("wipe" => "enable");
	return sendMDMCommand($req, $command);
}
$handler->register("MDM_COMMAND_REMOTE_WIPE", "MDM_COMMAND_REMOTE_WIPE");

function MDM_COMMAND_REMOTE_LOCK(&$req) {
	$command = array("lock" => "enable");
	return sendMDMCommand($req, $command);
}
$handler->register("MDM_COMMAND_REMOTE_LOCK", "MDM_COMMAND_REMOTE_LOCK");

function MDM_COMMAND_SET_POLICY(&$req) {
	$command = array("maxfailedpwdsforwipe" => $req->_max_failed_pwds,
			"maxtimetolock" => $req->_max_time_lock*1000,
			"pwdminlen" => $req->_min_pwd_len,
			"pwdquality" => $req->_pwd_quality,
		);
	return sendMDMCommand($req, $command);
}
$handler->register("MDM_COMMAND_SET_POLICY", "MDM_COMMAND_SET_POLICY");

function sendMDMCommand(&$req, $command) {
	$db = mysql_open();
	$db_row = $db->get_single_array("iu_mdsport", "push_config_tbl", "vc_mdsserver='{$req->_server}' AND vc_protocol='{$req->_proto}' AND itu_state<>0");
	if(!is_null($db_row)) {
		list($port) = $db_row;
		$ret = pag_push(0, $req->_server, $port, $req->_pin, ANDROID_MDM_PORT, "MDM", json_encode($command));
		if($ret === FALSE) {
			$req->error("推送出错！");
			return FALSE;
		}
	}else {
		$req->error("找不到推送服务器");
		return FALSE;
	}
	return TRUE;
}

$handler->accept();

?>
