<?php

define("DB_RESULT_NUM", 0);
define("DB_RESULT_ASSOC", 1);

function convertCharset($array, $charset1, $charset2) { 
	foreach ($array as  $key=>$item) { 
		if(is_array($item)) {
			$array[$key]=convertCharset($item, $charset1, $charset2); 
		} elseif(is_string($item)) {
			$array[$key]=iconv($charset1, $charset2, $item);
		}
    	}
	return $array; 
}

include_once("db_conn.inc.php");
include_once("db_mssql.inc.php");
include_once("db_mysql.inc.php");
include_once("db_oci8.inc.php");

function db_escape($str) {
	return str_replace(array("%", "_"), array("\\%", "\\_"), $str);
}

function db_get_date() {
	$microtime = gettimeofday();
	$datetime = date('Y-m-d H:i:s', $microtime['sec']);
	$micro = $microtime['usec'];
	return array($datetime, $micro);
}

class DBLink {
	private $__db = null;

	public function __construct($engine, $charset=SYSTEM_CHARSET) {
		if($engine == DB_ENGINE_MSSQL) {
			$this->__db = new MSSqlConn($charset);
		}elseif($engine == DB_ENGINE_MYSQL) {
			$this->__db = new MYSqlConn($charset);
		}elseif($engine == DB_ENGINE_OCI8) {
			$this->__db = new OCI8Conn($charset);
		}else {
			die("unsupported DB engine {$engine}\n");
		}
	}

	public function open($host, $user, $passwd, $dbname, $port) {
		return $this->__db->open($host, $user, $passwd, $dbname, $port);
	}

	public function close() {
		if(!is_null($this->__db)) {
			$this->__db->close();
		}
	}

	public function query($sql) {
		return $this->__db->query($sql);
	}

	public function getCharset() {
		return $this->__db->getCharset();
	}

	public function setCharset($charset) {
		$this->__db->setCharset($charset);
	}

	function get_tables() {
		return $this->__db->get_tables();
	}

	function table_exists($tablename) {
		$tablename = strtolower($tablename);
		$table = $this->get_tables();
		foreach($table as $tbl) {
			if(strtolower($tbl) == $tablename) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 *
	 *
	 */
	function get_items($colstr, $table, $condition, $order, $limit, $offset, $type) {
		if($colstr == "") {
			$colstr = "*";
		}
		$sql = "SELECT $colstr FROM $table";
		if(!is_null($condition) && $condition != "") {
			$sql.=" WHERE $condition";
		}
		if(!is_null($order) && $order != "") {
			$sql.= " ORDER BY $order";
		}
		return $this->__db->fetch_data($sql, $limit, $offset, $type);
	}

	function get_item_count($table, $condition) {
		$sql = "select count(*) from $table";
		if($condition != "") {
			$sql.= " where $condition";
		}
		$row = $this->__db->fetch_data($sql, 1, 0, DB_RESULT_NUM);
		if(!is_null($row) && count($row) == 1) {
			return $row[0][0];
		}else {
			return 0;
		}
	}

	function get_single_assoc($colstr, $table, $condition, $order="", $offset=0) {
		$rows = $this->get_items($colstr, $table, $condition, $order, 1, $offset, DB_RESULT_ASSOC);
		if(!is_null($rows) && count($rows) == 1) {
			return $rows[0];
		}else {
			return null;
		}
	}

	function get_assocs($colstr, $table, $condition, $order="", $limit=0, $offset=0) {
		return $this->get_items($colstr, $table, $condition, $order, $limit, $offset, DB_RESULT_ASSOC);
	}

	function get_single_array($colstr, $table, $condition, $order="", $offset=0) {
		$rows = $this->get_items($colstr, $table, $condition, $order, 1, $offset, DB_RESULT_NUM);
		if(!is_null($rows) && count($rows) == 1) {
			return $rows[0];
		}else {
			return null;
		}
	}

	function get_arrays($colstr, $table, $condition, $order="", $limit=0, $offset=0) {
		return $this->get_items($colstr, $table, $condition, $order, $limit, $offset, DB_RESULT_NUM);
	}

	function last_id() {
		return $this->__db->last_id();
	}

	function update($setstr, $table, $condition) {
		$sql = "UPDATE {$table} SET {$setstr}";
		if($condition != "") {
			$sql.=" WHERE {$condition}";
		}
		#_log("update {$sql}");
		return ($this->__db->query($sql) !== false);
	}

	function delete($table, $condition) {
		$sql = "DELETE FROM $table";
		if($condition != "") {
			$sql.= " WHERE $condition";
		}
		return ($this->__db->query($sql) !== false);
	}

	function query_result($_query_str, $_table_name, $_query_cond, $_query_order, $_query_limit, $_query_offset) {
		$row_num = $this->get_item_count($_table_name, $_query_cond);
		if($row_num > 0) {
			$db_rows = $this->get_assocs($_query_str, $_table_name, $_query_cond, $_query_order, $_query_limit, $_query_offset);
			if(!is_null($db_rows) && count($db_rows) > 0) {
				$rows = $db_rows;
				return $this->data_encode($row_num, $rows);
			}else {
				die("Query ".$_query_str." error!");
			}
		}
		return $this->data_encode(0, null);
	}

	function query_json($_query_str, $_table_name, $_query_cond="", $_query_order="", $_query_limit=0, $_query_offset=0) {
		$json_dat = $this->query_result($_query_str, $_table_name, $_query_cond, $_query_order, $_query_limit, $_query_offset);
		return json_encode($json_dat);
	}

	function data_encode($num_row, $rows) {
		if($num_row > 0) {
			return array('data_len' => $num_row, 'data'=>$rows);
		}else {
			return array('data_len' => 0, 'data'=>array());
		}
	}

	function proc_json($proc) {
		$sql= "CALL {$proc}";
		$row = $this->__db->fetch_data($sql, 0, 0, DB_RESULT_ASSOC);
		return josn_encode($this->data_encode(count($row), $row));
	}

	function func_json($func_name, $params, $limit=0, $offset=0) {
		list($num_row, $rows) = $func_name($this, $params, $limit, $offset);
		return json_encode($this->data_encode($num_row, $rows));
	}

}

$__db__ = null;

function db_open($host, $user, $passwd, $dbname, $port, $engine, $charset=SYSTEM_CHARSET) {
	global $__db__;
	$__db__ = new DBLink($engine, $charset);
	if(false === $__db__->open($host, $user, $passwd, $dbname, $port)) {
		$__db__ = null;
	}
	return $__db__;
}

function db_close() {
	global $__db__;
	$__db__->close();
}

function db_query($sql) {
	global $__db__;
	return $__db__->query($sql);
}

function db_get_item_count($table, $condition) {
	global $__db__;
	return $__db__->get_item_count($table, $condition);
}

function db_query_json($_query_str, $_table_name, $_query_cond="", $_query_order="", $_query_limit=0, $_query_offset=0) {
	global $__db__;
	return $__db__->query_json($_query_str, $_table_name, $_query_cond, $_query_order, $_query_limit, $_query_offset);
}

function db_proc_json($proc) {
	global $__db__;
	return $__db__->proc_json($proc);
}

function db_func_json($func, $param, $limit=0, $offset=0) {
	global $__db__;
	return $__db__->func_json($func, $param, $limit, $offset);
}

function mysql_open($host=MYSQL_DB_HOST, $user=MYSQL_DB_USER, $passwd=MYSQL_DB_PASS, $dbname=MYSQL_DB_NAME, $port=MYSQL_DB_PORT, $charset=SYSTEM_CHARSET) {
	if(!defined("MYSQL_DB_HOST")) {
		die("MYSQL_DB_HOST is not defined!");
	}
	return db_open($host, $user, $passwd, $dbname, $port, DB_ENGINE_MYSQL, $charset);
}

function mssql_open($host=MSSQL_DB_HOST, $user=MSSQL_DB_USER, $passwd=MSSQL_DB_PASS, $dbname=MSSQL_DB_NAME, $port=MSSQL_DB_PORT, $charset=SYSTEM_CHARSET) {
	if(!defined("MSSQL_DB_HOST")) {
		die("MSSQL_DB_HOST is not defined!");
	}
	return db_open($host, $user, $passwd, $dbname, $port, DB_ENGINE_MSSQL, $charset);
}

function oci8_open($host=OCI8_DB_HOST, $user=OCI8_DB_USER, $passwd=OCI8_DB_PASS, $dbname=OCI8_DB_NAME, $port=OCI8_DB_PORT, $charset=SYSTEM_CHARSET) {
	if(!defined("OCI8_DB_HOST")) {
		die("OCI8_DB_HOST is not defined!");
	}
	return db_open($host, $user, $passwd, $dbname, $port, DB_ENGINE_OCI8, $charset);
}

?>
