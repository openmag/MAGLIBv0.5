<?php

define("VERSION_STATE_INIT", 0);
define("VERSION_STATE_CHANGE", 1);
define("VERSION_STATE_NOCHANGE", 2);

define("TABLE_SYNC_STATE_START",         0);
define("TABLE_SYNC_STATE_SUCC_CHANGE",   1);
define("TABLE_SYNC_STATE_SUCC_NOCHANGE", 2);
define("TABLE_SYNC_STATE_FAIL",          3);
define("TABLE_SYNC_STATE_DIRTY",         50);

define("DBSYNC_PREFIX", "__syncdb_");

define("SYNC_MAX_ITEM", 400);


function array_get_ignorecase($arr, $key) {
	foreach($arr as $k=>$v) {
		if(strtolower($k) == strtolower($key)) {
			return $v;
		}
	}
	return FALSE;
}

# 'VARCHAR', 'INT', 'REAL', 'DATE', 'DATETIME', 'BLOB'

class SyncColumn {
	public function __construct($name, $type, $notnull) {
		$this->__name = $name;
		$this->__type = $type;
		$this->__notnull = $notnull;
	}

	public function getName() {
		return $this->__name;
	}

	private function isString() {
		if(preg_match('/^(char|varchar|date|datetime|blob|text)/i', $this->__type) > 0) {
			return TRUE;
		}else {
			return FALSE;
		}
	}

	public function getDefString() {
		$def = $this->__name." ".$this->__type;
		if($this->__notnull) {
			$def .= " NOT NULL ";
		}
		return $def;
	}

	public function getValString($val) {
		if($this->isString()) {
			return "'".addslashes($val)."'";
		}else {
			return $val;
		}
	}

	public function matchType($val) {
		if(preg_match('/^varchar/i', $this->__type) > 0) {
			$left_pos = strpos($this->__type, '(');
			$right_pos = strpos($this->__type, ')');
			$bits = substr($this->__type, $left_pos+1, $right_pos - $left_pos - 1);
			if(strlen($val) >= $bits) {
				return "String length ".(strlen($val)+1)." exceeds maximal length ".$bits;
			}else {
				return TRUE;
			}
		}elseif(preg_match('/^int/i', $this->__type) > 0) {
			if(preg_match('/^\d+$/', $val) > 0) {
				return TRUE;
			}else {
				return "\"{$val}\" is not an integer!";
			}
		}elseif(preg_match('/^real/i', $this->__type) > 0) {
			if(preg_match('/^[+-]?\d*(\.\d*)?$/', $val) > 0) {
				return TRUE;
			}else {
				return "\"{$val}\" is not a real number!";
			}
		}elseif(preg_match('/^datetime/i', $this->__type) > 0) {
			if(preg_match('/^\d{4}\-\d{1,2}\-\d{1,2} \d{1,2}:\d{1,2}:\d{1,2}$/', $val) > 0) {
				return TRUE;
			}else {
				return "\"{$val}\" is not a datetime! Shoud be YYYY-mm-dd hh:ii:ss";
			}
		}elseif(preg_match('/^date/i', $this->__type) > 0) {
			if(preg_match('/^\d{4}\-\d{1,2}\-\d{1,2}$/', $val) > 0) {
				return TRUE;
			}else {
				return "\"{$val}\" is not a date! Shoud be YYYY-dd-dd";
			}
		}
		return TRUE;
	}

	public function isNullable() {
		if($this->__notnull) {
			return FALSE;
		}else {
			return TRUE;
		}
	}
}

class SyncTable {
	private $__db    = null;
	private $__table = null;
	private $__url   = null;
	private $__timestamp = null;
	private $__prev_version = null;
	private $__version = null;
	private $__columns= null;
	private $__primary_keys = null;

	public function __construct($tablename, $host=MYSQL_DB_HOST, $user=MYSQL_DB_USER, $passwd=MYSQL_DB_PASS, $dbname=MYSQL_DB_NAME, $port=MYSQL_DB_PORT, $charset=SYSTEM_CHARSET) {
		if(is_string($host)) {
			$this->__db = mysql_open($host, $user, $passwd, $dbname, $port, $charset);
		}else {
			$this->__db = $host;
		}
		$this->__table = $tablename;
		$this->__url = null;
		$this->__timestamp = null;
		$this->__prev_version = -1;
		$this->__version = -1;
		$this->__columns = array();
		$this->__primary_keys = array();
	}

	public function initTable() {
		if($this->__db->table_exists("syncdb_tbl")) {
			$dbrow = $this->__db->get_single_array("vc_dataurl, vc_timestamp", "syncdb_tbl", "vc_name='{$this->__table}'");
			if(!is_null($dbrow)) {
				$this->__url = $dbrow[0];
				$this->__timestamp = $dbrow[1];
			}else {
				return "No data source set!";
			}
			$dbrows = $this->__db->get_arrays("a.vc_name, a.vc_datatype, a.vc_dtparam, a.ti_isnull, a.ti_isprimary", "syncdb_column_tbl a INNER JOIN syncdb_tbl b ON a.tbl_id=b.id", "b.vc_name='{$this->__table}'", "dt_whenadd, a.vc_name");
			if(!is_null($dbrows) && count($dbrows) > 0) {
				for($i = 0; $i < count($dbrows); $i ++) {
					list($name, $type, $type_param, $isnull, $isprimary) = $dbrows[$i];
					if($type_param != '') {
						$type .= '('.$type_param.')';
					}
					if(is_numeric($isnull) && $isnull == 1) {
						$notnull = FALSE;
					}else {
						$notnull = TRUE;
					}
					if(is_numeric($isprimary) && $isprimary == 1) {
						$primary = TRUE;
					}else {
						$primary = FALSE;
					}
					$this->addColumn($name, $type, $notnull, $primary);
				}
				return TRUE;
			}else {
				return "Get column definition fails";
			}
		}else {
			return "No syncdb_tbl";
		}
	}

	public function getColumnDef() {
		$def = array();
		$dbrows = $this->__db->get_arrays("a.vc_name, a.vc_datatype, a.vc_dtparam, a.ti_isnull, a.ti_isprimary", "syncdb_column_tbl a INNER JOIN syncdb_tbl b ON a.tbl_id=b.id", "b.vc_name='{$this->__table}'", "a.dt_whenadd, a.vc_name");
		for($i = 0; !is_null($dbrows) && $i < count($dbrows); $i ++) {
			list($name, $type, $type_param, $isnull, $isprimary) = $dbrows[$i];
			if($type_param != '') {
				$type .= '('.$type_param.')';
			}
			if(is_numeric($isnull) && $isnull == 1) {
				$notnull = FALSE;
			}else {
				$notnull = TRUE;
			}
			if(is_numeric($isprimary) && $isprimary == 1) {
				$primary = TRUE;
			}else {
				$primary = FALSE;
			}
			$def[] = array("_name"=>$name, "_type"=>$type, "_notnull"=>$notnull, "_primary"=>$primary);
		}
		return $def;
	}

	public function getIndexDef() {
		$def = array();
		$dbrows = $this->__db->get_arrays("a.vc_colnames", "syncdb_index_tbl a INNER JOIN syncdb_tbl b ON a.tbl_id=b.id", "b.vc_name='{$this->__table}'", "a.dt_whenadd, a.vc_colnames");
		for($i = 0; !is_null($dbrows) && $i < count($dbrows); $i ++) {
			$def[] = $dbrows[$i][0];
		}
		return $def;
	}

	private function isPrimaryKey(&$col) {
		if(array_key_exists($col->getName(), $this->__primary_keys)) {
			return TRUE;
		}else {
			return FALSE;
		}
	}

	public function getTimeStamp() {
		return $this->__timestamp;
	}

	private function addColumn($name, $type, $notnull=FALSE, $primary=FALSE) {
		if($primary) {
			$notnull = TRUE;
		}
		$col = new SyncColumn($name, $type, $notnull);
		$this->__columns[] = $col;
		if($primary) {
			$this->__primary_keys[$col->getName()] = $col;
		}
		#echo "Add column {$name}\n";
	}

	public function getDefString($table=NULL) {
		$colstr = "";
		foreach($this->__columns as $col) {
			if(strlen($colstr) > 0) {
				$colstr .= ",";
			}
			$colstr .= $col->getDefString();
		}
		if(count($this->__primary_keys) > 0) {
			$primary_str = implode(',', array_keys($this->__primary_keys));
			$primary_str = ", PRIMARY KEY ({$primary_str})";
		}
		if(is_null($table)) {
			$table = $this->__table;
		}
		return "CREATE TABLE IF NOT EXISTS {$table} ({$colstr}{$primary_str})";
	}

	private function createVersionTable() {
		$sql  = "CREATE TABLE {$this->getVersionTableName()} ( ";
		$sql .= "version INT UNSIGNED NOT NULL, ";
		$sql .= "created DATETIME, ";
		$sql .= "state SMALLINT UNSIGNED, ";
		$sql .= "PRIMARY KEY (version) ";
		$sql .= ")";
		#echo "create version table ".$sql."\n";
		if($this->__db->query($sql)) {
			#echo "SUCCESS!\n";
			return TRUE;
		}else {
			#echo "FAILED!\n";
			return FALSE;
		}
	}

	public function getCurrentVersion() {
		if(!$this->__db->table_exists($this->getVersionTableName())) {
			return 0;
		}else {
			$dbrow = $this->__db->get_single_array("version", $this->getVersionTableName(), "state!=".VERSION_STATE_INIT, "version desc");
			if(!is_null($dbrow)) {
				return $dbrow[0];
			}else {
				#echo "Failed to get table version";
				return FALSE;
			}
		}
		return FALSE;
	}

	public function setCurrentVersion($ver) {
		if($this->__db->table_exists($this->getVersionTableName())) {
			if($this->__db->get_item_count($this->getVersionTableName(), "version={$ver} AND state!=".VERSION_STATE_INIT) == 1) {
				$this->__version = $ver;
				$this->__prev_version = 0;
				return TRUE;
			}
		}
		$this->__version = 0;
		$this->__prev_version = -1;
		return FALSE;
	}

	public function getCurrentRowCount() {
		return $this->__db->get_item_count($this->getTableName(), "");
	}

	public function setPreviousVersion($ver) {
		if($this->__db->table_exists($this->getVersionTableName())) {
			if($this->__db->get_item_count($this->getVersionTableName(), "version={$ver} AND state!=".VERSION_STATE_INIT) == 1) {
				$this->__prev_version = $ver;
				return TRUE;
			}
		}
		$this->__prev_version = 0;
		return FALSE;
	}

	private function getSyncVersion() {
		$this->__prev_version = -1;
		$this->__version = -1;
		if(!$this->__db->table_exists($this->getVersionTableName())) {
			$this->__prev_version = 0;
			$this->__version = 1;
			$this->createVersionTable();
			if(!$this->__db->query('LOCK TABLES '.$this->getVersionTableName().' WRITE')) {
				return "getSyncVersion: Fail to lock table";
			}
		}else {
			if(!$this->__db->query('LOCK TABLES '.$this->getVersionTableName().' WRITE')) {
				return "getSyncVersion: Fail to lock table";
			}
			$dbrow = $this->__db->get_single_array("version", $this->getVersionTableName(), "state!=".VERSION_STATE_INIT, "version desc");
			if(!is_null($dbrow)) {
				$this->__prev_version = $dbrow[0];
			}else {
				$this->__prev_version = 0;
			}
			$dbrow = $this->__db->get_single_array("version", $this->getVersionTableName(), "", "version desc");
			if(!is_null($dbrow)) {
				$this->__version = $dbrow[0]+1;
			}else {
				$ret = "Failed to get table version";
			}
		}
		if($this->__version > 0) {
			$sql = "INSERT INTO {$this->getVersionTableName()} (version, created, state) VALUES({$this->__version}, NOW(), ".VERSION_STATE_INIT.")";
			#echo $sql;
			if(!$this->__db->query($sql)) {
				$this->__version = -1;
				$ret = "Fail to insert new version\n";
			}
		}
		$this->__db->query("UNLOCK TABLES");
		if($this->__version > 0) {
			#echo "prev_version: {$this->__prev_version} version: {$this->__version}\n";
			return TRUE;
		}else {
			return $ret;
		}
	}

	private function getVersionTableName() {
		return "__syncdb_".$this->__table."_version";
	}
	private function getPrevTableName() {
		return $this->_get_table_name($this->__prev_version);
	}
	private function getTableName() {
		return $this->_get_table_name($this->__version);
	}
	private function _get_table_name($ver) {
		return "__syncdb_".$this->__table."_".$ver;
	}

	private function startSync() {
		$ret = $this->initTable();
		if($ret !== TRUE) {
			return $ret;
		}
		if(is_null($this->__timestamp) || strlen($this->__timestamp) == 0) {
			$tmstamp = md5(rand().'/'.time()."/".php_uname()."/".phpversion());
			if($this->__db->update("vc_timestamp='{$tmstamp}', ti_isdirty=0", "syncdb_tbl", "vc_name='{$this->__table}'")) {
			}else {
				return "Failed to Update timestamp for {$this->__table}!";
			}
		}
		if($this->__db->update("dt_lastsync=NOW(), ti_status=".TABLE_SYNC_STATE_START, "syncdb_tbl", "vc_name='{$this->__table}'")) {
			$ret = $this->getSyncVersion();
			if($ret === TRUE) {
				$sql = "DROP TABLE IF EXISTS {$this->getTableName()}";
				if($this->__db->query($sql)) {
					$sql = $this->getDefString($this->getTableName());
					if($this->__db->query($sql)) {
						return TRUE;
					}else {
						return "Failed to create table $sql\n";
					}
				}else {
					return "Failed to drop old table".$sql."\n";
				}
			}else {
				return $ret;
			}
		}else {
			return "failed to update syncdb_tbl";
		}
	}

	private function fetchRows($url) {
		$curl = new cURL();
		$result = $curl->get($url);
		#echo $result."\n";
		if($result !== FALSE && $curl->getResponseCode() == 200) {
			#echo $curl->getResponse();
			$data = json_decode($curl->getResponse());
			#echo count($data);
			if(!is_null($data)) {
				return $this->saveRows($data);
			}else {
				return "Illegal data source format: ".$curl->getResponse();
			}
		}else {
			return "Failed to get data from $url";
		}
	}

	private function saveRow($data) {
		$ret = $this->checkRow($data);
		if($ret !== TRUE) {
			return $ret;
		}
		return $this->__saveRow($data);
	}

	private function __saveRow($data) {
		if(is_object($data)) {
			$data = (array)$data;
		}
		#var_dump($data);
		$defstr = '';
		$colstr = '';
		for($i = 0; $i < count($this->__columns); $i ++) {
			$key = $this->__columns[$i]->getName();
			$val = array_get_ignorecase($data, $key);
			if($val !== FALSE) {
				if(strlen($defstr) > 0) {
					$defstr .= ",";
					$colstr .= ",";
				}
				$defstr .= $key;
				$colstr .= $this->__columns[$i]->getValString($val);
			}elseif(!$this->__columns[$i]->isNullable()) {
				return "Missing value for column ".$col->getName();
			}
		}
		$sql = "INSERT INTO {$this->getTableName()} ({$defstr}) VALUES({$colstr})";
		if($this->__db->query($sql)) {
			return TRUE;
		}else {
			return "Insert error for ".$sql;
		}
	}

	private function saveRows($data) {
		$ret = $this->checkRows($data);
		if($ret !== TRUE) {
			return $ret;
		}
		foreach($data as $row) {
			$ret = $this->__saveRow($row);
			if($ret !== TRUE) {
				return $ret;
			}
		}
		return TRUE;
	}

	private function checkRow($data) {
		if(is_object($data)) {
			$data = (array)$data;
		}
		for($i = 0; $i < count($this->__columns); $i ++) {
			$key = $this->__columns[$i]->getName();
			$val = array_get_ignorecase($data, $key);
			if($val !== FALSE) {
				$ret = $this->__columns[$i]->matchType($val);
				if($ret !== TRUE) {
					return $this->__columns[$i]->getName().": ".$ret;
				}
			}elseif(!$this->__columns[$i]->isNullable()) {
				return "Missing value for column ".$col->getName();
			}
		}
		return TRUE;
	}

	private function checkRows($data) {
		foreach($data as $row) {
			$ret = $this->checkRow($row);
			if($ret !== TRUE) {
				return $ret;
			}
		}
		return TRUE;
	}

	private function endSync() {
		if($this->tableDiff()) {
			if($this->__db->update("state=".VERSION_STATE_CHANGE, $this->getVersionTableName(), "version=".$this->__version)) {
				return TABLE_SYNC_STATE_SUCC_CHANGE;
			}else {
				return "Update version state error";
			}
		}else {
			#echo "No change\n";
			$this->cleanTable();
			return TABLE_SYNC_STATE_SUCC_NOCHANGE;
		}
	}

	public function sync() {
		$result = $this->startSync();
		if($result === TRUE) {
			$result = $this->fetchRows($this->__url);
			if($result === TRUE) {
				$result = $this->endSync();
				if(is_numeric($result)) {
					if($this->__db->update("ti_status={$result}", "syncdb_tbl", "vc_name='{$this->__table}'")) {
						return TRUE;
					}else {
						$result = "Failed to update syncdb_tbl after endSync";
					}
				}
			}
		}
		$this->cleanTable();
		$this->__db->update("ti_status=".TABLE_SYNC_STATE_FAIL, "syncdb_tbl", "vc_name='{$this->__table}'");
		return $result;
	}

	private function tableDiff() {
		if($this->__prev_version <= 0) {
			return TRUE;
		}
		if($this->getDeleteRowCount() > 0) {
			return TRUE;
		}
		if($this->getUpdateRowCount() > 0) {
			return TRUE;
		}
		if($this->getInsertRowCount() > 0) {
			return TRUE;
		}
		return FALSE;
	}

	private function getTableUpdateQuery($tbl1, $tbl2) {
		$colstr = "";
		$joinstr = "";
		$constr = "";
		foreach($this->__primary_keys as $key=>$col) {
			if(strlen($joinstr) > 0) {
				$joinstr .= " AND ";
			}
			$joinstr .= "{$tbl1}.{$key}={$tbl2}.{$key}";
		}
		foreach($this->__columns as $col) {
			if(strlen($colstr) > 0) {
				$colstr .= ",";
			}
			$colstr .= "{$tbl1}.{$col->getName()}";
			if(!$this->isPrimaryKey($col)) {
				if(strlen($constr) > 0) {
					$constr .= " OR ";
				}
				$constr .= "{$tbl1}.{$col->getName()}<>{$tbl2}.{$col->getName()}";
			}
		}
		return array("_fields"=>$colstr, "_tables"=>"{$tbl1} INNER JOIN {$tbl2} ON {$joinstr}", "_conditions"=>$constr);
	}

	private function getTableDiffQuery($tbl1, $tbl2) {
		$colstr = "";
		$joinstr = "";
		$constr = "";
		foreach($this->__columns as $col) {
			if(strlen($colstr) > 0) {
				$colstr .= ",";
			}
			$colstr .= "{$tbl1}.{$col->getName()}";
		}
		foreach($this->__primary_keys as $key=>$col) {
			if(strlen($joinstr) > 0) {
				$joinstr .= " AND ";
				$constr .= " OR ";
			}
			$joinstr .= "{$tbl1}.{$key}={$tbl2}.{$key}";
			$constr .= "({$tbl2}.{$key} IS NULL AND {$tbl1}.{$key} IS NOT NULL)";
		}
		#echo "SELECT {$colstr} FROM {$tbl1} LEFT OUTER JOIN {$tbl2} ON {$joinstr} WHERE {$constr}\n";
		return array("_fields"=>$colstr, "_tables"=>"{$tbl1} LEFT OUTER JOIN {$tbl2} ON {$joinstr}", "_conditions"=>$constr);
	}

	private function getTableDiffCount($tbl1, $tbl2) {
		$query = $this->getTableDiffQuery($tbl1, $tbl2);
		$ret = $this->__db->get_item_count($query["_tables"], $query["_conditions"]);
		#echo $ret."\n";
		return $ret;
	}

	private function getTableDiffData($tbl1, $tbl2, $offset, $limit) {
		$query = $this->getTableDiffQuery($tbl1, $tbl2);
		return $this->__db->get_assocs($query["_fields"], $query["_tables"], $query["_conditions"], $query["_fields"], $limit, $offset);
	}

	private function getUpdateRowCount() {
		if($this->__prev_version <= 0) {
			return 0;
		}else {
			$query = $this->getTableUpdateQuery($this->getTableName(), $this->getPrevTableName());
			if($query["_conditions"] == '') {
				return 0;
			}else {
				$ret = $this->__db->get_item_count($query["_tables"], $query["_conditions"]);
				return $ret;
			}
		}
	}

	private function getUpdateRows($offset, $limit) {
		if($this->__prev_version <= 0) {
			return array();
		}else {
			$query = $this->getTableUpdateQuery($this->getTableName(), $this->getPrevTableName());
			if($query["_conditions"] == '') {
				return array();
			}else {
				$ret = $this->__db->get_assocs($query["_fields"], $query["_tables"], $query["_conditions"], $query["_fields"], $limit, $offset);
				return $ret;
			}
		}
	}

	private function getUpdateSQLs($offset, $limit) {
		$result = array();
		$rows = $this->getUpdateRows($offset, $limit);
		foreach($rows as $row) {
			$setstr = '';
			$where = '';
			foreach($this->__columns as $col) {
				if($this->isPrimaryKey($col)) {
					if(strlen($where) > 0) {
						$where .= ' AND ';
					}
					$where .= "{$col->getName()}=".$col->getValString($row[$col->getName()]);
				} else {
					if(strlen($setstr) > 0) {
						$setstr .= ', ';
					}
					$setstr .= "{$col->getName()}=".$col->getValString($row[$col->getName()]);
				}
			}
			$result[] = "UPDATE {$this->__table} SET {$setstr} WHERE {$where}";
		}
		return $result;
	}

	private function getUpdateData($offset, $limit) {
		$result = array(
			"_type"=>"UPDATE",
			"_data"=>$this->getUpdateRows($offset, $limit)
		);
		return $result;
	}

	private function getDeleteRowCount() {
		if($this->__prev_version <= 0) {
			return 0;
		}else {
			return $this->getTableDiffCount($this->getPrevTableName(), $this->getTableName());
		}
	}

	private function getDeleteRows($offset, $limit) {
		if($this->__prev_version <= 0) {
			return array();
		}else {
			return $this->getTableDiffData($this->getPrevTableName(), $this->getTableName(), $offset, $limit);
		}
	}

	private function getDeleteSQLs($offset, $limit) {
		$result = array();
		$rows = $this->getDeleteRows($offset, $limit);
		foreach($rows as $row) {
			$where = '';
			foreach($this->__columns as $col) {
				if(strlen($where) > 0) {
					$where .= ' AND ';
				}
				$where .= $col->getName()."=".$col->getValString($row[$col->getName()]);
			}
			$result[] = "DELETE FROM {$this->__table} WHERE {$where}";
		}
		return $result;
	}

	private function getDeleteData($offset, $limit) {
		$result = array(
			"_type"=>"DELETE",
			"_data"=>$this->getDeleteRows($offset, $limit)
		);
		return $result;
	}

	private function getInsertRowCount() {
		if($this->__prev_version <= 0) {
			return $this->__db->get_item_count($this->getTableName(), "");
		}else {
			return $this->getTableDiffCount($this->getTableName(), $this->getPrevTableName());
		}
	}

	private function getInsertRows($offset, $limit) {
		if($this->__prev_version <= 0) {
			$colstr = '';
			foreach($this->__columns as $col) {
				if(strlen($colstr) > 0) {
					$colstr .= ",";
				}
				$colstr .= $col->getName();
			}
			return $this->__db->get_assocs($colstr, $this->getTableName(), "", $colstr, $limit, $offset);
		}else {
			return $this->getTableDiffData($this->getTableName(), $this->getPrevTableName(), $offset, $limit);
		}
	}

	private function getInsertSQLs($offset, $limit) {
		$result = array();
		#echo "$offset, $limit\n";
		$rows = $this->getInsertRows($offset, $limit);
		if($offset == 0 && $this->__prev_version <= 0) {
			$result[] = $this->getDefString();
		}
		foreach($rows as $row) {
			$colstr = '';
			$valstr = '';
			foreach($this->__columns as $col) {
				if(strlen($colstr) > 0) {
					$colstr .= ',';
					$valstr .= ',';
				}
				$colstr .= $col->getName();
				$valstr .= $col->getValString($row[$col->getName()]);
			}
			$result[] = "INSERT INTO {$this->__table}({$colstr}) VALUES({$valstr})";
		}
		return $result;
	}

	private function getInsertData($offset, $limit) {
		$result = array(
			"_type"=>"INSERT",
			"_data"=>$this->getInsertRows($offset, $limit)
		);
		return $result;
	}

	public function dropTable() {
		return $this->cleanTable();
	}

	private function cleanTable() {
		$sql = "DROP TABLE IF EXISTS {$this->getTableName()}";
		if($this->__db->query($sql)) {
			if($this->__db->query("LOCK TABLES ".$this->getVersionTableName()." WRITE")) {
				$ret = $this->__db->delete($this->getVersionTableName(), "version=".$this->__version);
				$this->__db->query("UNLOCK TABLES");
				if($ret === TRUE) {
					return TRUE;
				}else {
					return "cleanTable: Failed to delete table in cleanTable";
				}
			}else{
				return "cleanTable: FAILED TO lock table";
			}
		}else {
			return "cleanTable: Failed to drop table ".$sql;
		}
	}

	public function destroy() {
		if($this->__db->table_exists($this->getVersionTableName())) {
			$dbrows = $this->__db->get_arrays("version, created, state", $this->getVersionTableName(), "");
			if(!is_null($dbrows)) {
				foreach($dbrows as $row) {
					list($ver, $when, $state) = $row;
					$sql = "DROP TABLE IF EXISTS {$this->_get_table_name($ver)}";
					if(!$this->__db->query($sql)) {
						return "Failed to drop table {$sql}\n";
					}
				}
			}else {
				return "failed to query version table!";
			}
			$sql = "DROP TABLE IF EXISTS {$this->getVersionTableName()}";
			if(!$this->__db->query($sql)) {
				return "Failed to drop table {$sql}\n";
			}
			if(!$this->__db->update("vc_timestamp=''", "syncdb_tbl", "vc_name='{$this->__table}'")) {
				return "Failed to reset timestamp for {$this->__table}\n";
			}
			return TRUE;
		}else {
			#echo "No version table!";
			return TRUE;
		}
	}

	public function getDiffCount() {
		return $this->getDeleteRowCount() + $this->getInsertRowCount() + $this->getUpdateRowCount();
	}

	public function getDiffSQLs($offset, $limit) {
		$result = array();
		$delc = $this->getDeleteRowCount();
		if($offset < $delc) {
			if($offset + $limit > $delc) {
				$dlimit = $delc - $offset;
			}else {
				$dlimit = $limit;
			}
			$limit -= $dlimit;
			$sqls = $this->getDeleteSQLs($offset, $dlimit);
			foreach($sqls as $sql) {
				$result[] = $sql;
			}
			$offset += $dlimit;
		}
		$updc = $this->getUpdateRowCount();
		if($offset < $delc + $updc) {
			if($offset + $limit > $delc + $updc) {
				$ulimit = $delc + $updc - $offset;
			}else {
				$ulimit = $limit;
			}
			$limit -= $ulimit;
			$sqls = $this->getUpdateSQLs($offset - $delc, $ulimit);
			foreach($sqls as $sql) {
				$result[] = $sql;
			}
			$offset += $ulimit;
		}
		if($limit > 0) {
			$insc = $this->getInsertRowCount();
			if($offset + $limit > $insc + $delc + $updc) {
				$limit = $insc + $delc + $updc - $offset;
			}
			$sqls = $this->getInsertSQLs($offset - $delc - $updc, $limit);
			foreach($sqls as $sql) {
				$result[] = $sql;
			}
		}
		return $result;
	}

	public function getDiffData($offset, $limit) {
		$result = array();
		$delc = $this->getDeleteRowCount();
		#echo "offset=".$offset." limit=".$limit." delcount=".$delc."<br/>";
		if($offset < $delc) {
			if($offset + $limit > $delc) {
				$dlimit = $delc - $offset;
			}else {
				$dlimit = $limit;
			}
			$limit -= $dlimit;
			$result[] = $this->getDeleteData($offset, $dlimit);
			$offset += $dlimit;
		}
		if($limit > 0) {
			$updc = $this->getUpdateRowCount();
			#echo "offset=".$offset." limit=".$limit." delcount=".$delc." updatecount=".$updc."<br/>";
			if($updc > 0 && $limit > 0 && $offset < $delc + $updc) {
				if($offset + $limit > $delc + $updc) {
					$ulimit = $delc + $updc - $offset;
				}else {
					$ulimit = $limit;
				}
				$limit -= $ulimit;
				$result[] = $this->getUpdateData($offset - $delc, $ulimit);
				$offset += $ulimit;
			}
			if($limit > 0) {
				$insc = $this->getInsertRowCount();
				#echo "offset=".$offset." limit=".$limit." delcount=".$delc." updatecount=".$updc." insertcount=".$insc."<br/>";
				if($offset + $limit > $insc + $delc + $updc) {
					$limit = $insc + $delc + $updc - $offset;
				}
				$result[] = $this->getInsertData($offset - $delc - $updc, $limit);
			}
		}
		return $result;
	}
}


?>
