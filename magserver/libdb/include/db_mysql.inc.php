<?php

define("DB_ENGINE_MYSQL", "MYSQL");

class MYSqlConn extends DBConn {

	public function __construct($charset=SYSTEM_CHARSET) {
		parent::__construct($charset);
	}

	public function open($host, $user, $passwd, $dbname, $port="", $charset=SYSTEM_CHARSET) {
		parent::open($host, $user, $passwd, $dbname, $port, $charset);
		if(empty($port)) {
			$port = 3306;
		}
		$this->__db_link = new mysqli($host, $user, $passwd, $dbname, $port);
		if (mysqli_connect_errno()) {
			$this->__db_link = null;
			printf("Connect failed: %s\n", mysqli_connect_error());
			return false;
		}
		return true;
	}

	public function close() {
		if(!is_null($this->__db_link)) {
			$this->__db_link->close();
		}
	}

	public function query($sql) {
		if($this->getCharset() != SYSTEM_CHARSET) {
			$sql = iconv(SYSTEM_CHARSET, $this->getCharset(), $sql);
		}
		$result = $this->__db_link->query($sql, MYSQLI_USE_RESULT);
		if(FALSE === $result) {
			_log("Qeury String {$sql} Error ".$this->__db_link->error);
		}
		return $result;
	}

	public function get_tables() {
		$sql = "show tables";
		$result = $this->fetch_data($sql, 0, 0, DB_RESULT_NUM);
		if(!is_null($result)) {
			$tables = array();
			foreach($result as $row) {
				$tables[] = $row[0];
			}
			return $tables;
		}else {
			return array();
		}
	}

	public function last_id() {
		$sql = "SELECT LAST_INSERT_ID()";
		$result = $this->fetch_data($sql, 0, 0, DB_RESULT_NUM);
		if(!is_null($result)) {
			return $result[0][0];
		}else {
			return -1;
		}
	}

	public function fetch_data($sql, $limit, $offset, $type) {
		if(!is_null($limit) && $limit != "" && is_numeric($limit)) {
			$sql.= " LIMIT $limit";
		}
		if(!is_null($offset) && $offset != "" && is_numeric($offset)) {
			$sql.= " OFFSET $offset";
		}
		$result = $this->query($sql);
		if(false === $result) {
			return null;
		}else {
			$rows = array();
			if($type == DB_RESULT_NUM) {
				$ttype = MYSQLI_NUM;
			}else {
				$ttype = MYSQLI_ASSOC;
			}
			while($row = $result->fetch_array($ttype)) {
				$rows[] = $row;
			}
			$this->free_result($result);
			if($this->getCharset() != SYSTEM_CHARSET) {
				$rows = convertCharset($rows, $this->getCharset(), SYSTEM_CHARSET);
			}
			return $rows;
		}
	}

	private function free_result($result) {
		$result->close();
		while($this->__db_link->more_results()) {
			$l_result = $this->__db_link->store_result();
			if(false !== $l_result) {
				$l_result->close();
			}
		}
	}

}

?>
