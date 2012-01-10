<?php

define("DB_ENGINE_MSSQL", "MSSQL");

class MSSqlConn extends DBConn {
	public function __construct($charset=SYSTEM_CHARSET) {
		parent::__construct($charset);
	}

	public function open($host, $user, $passwd, $dbname, $port="", $charset=SYSTEM_CHARSET) {
		parent::open($host, $user, $passwd, $dbname, $port, $charset);
		@ini_set('mssql.charset', $charset);
		@ini_set('mssql.textlimit', 2147483647);
		@ini_set('mssql.textsize', 2147483647);
		$connectionInfo = array("UID"=>$user, "PWD"=>$passwd, "Database"=>$dbname, 'MultipleActiveResultSets'=>false, "CharacterSet"=>$charset);
		if(empty($port)) {
			$servername = "{$host}, {$port}";
		}else {
			$servername = $host;
		}
		$this->__db_link = sqlsrv_connect($servername, $connectionInfo);
		if($this->__db_link === false) {
			$this->__db_link = null;
			print_r( sqlsrv_errors());
			return false;
		}
		return true;
	}

	public function get_tables() {
		$sql = "exec {$this->__dbname}.dbo.sp_tables";
		$result = $this->fetch_data($sql, 0, 0, DB_RESULT_NUM);
		if(!is_null($result)) {
			$tables = array();
			foreach($result as $row) {
				$tables[] = $row[2];
			}
			return $tables;
		}else {
			return array();
		}
	}

	public function last_id() {
		$sql = "select @@IDENTITY;";
		$result = $this->fetch_data($sql, 0, 0, DB_RESULT_NUM);
		if(!is_null($result)) {
			return $result[0][0];
		}else {
			return -1;
		}
	}

	public function close() {
		if(!is_null($this->__db_link)) {
			sqlsrv_close($this->__db_link);
		}
	}

	public function query($sql) {
		#echo "Query: ".$sql."<br />";
		if($this->getCharset() != SYSTEM_CHARSET) {
			$sql = iconv(SYSTEM_CHARSET, $this->getCharset(), $sql);
		}
		$stmt = sqlsrv_query($this->__db_link, $sql);
		if($stmt === false) {
			echo "Error in statement execution.\n";
			print_r(sqlsrv_errors(), true);
		}
		return $stmt;
	}

	public function fetch_data($sql, $limit, $offset, $type) {
		if($this->getCharset() != SYSTEM_CHARSET) {
			$sql = iconv(SYSTEM_CHARSET, $this->getCharset(), $sql);
		}
		if(is_numeric($limit) && $limit > 0) {
			if(is_numeric($offset) && $offset > 0) {
				$top = $offset + $limit;
			}else {
				$top = $limit;
			}
			$sql = preg_replace('/select/i', "SELECT TOP {$top}", $sql, 1); 
		}
		$stmt = sqlsrv_query($this->__db_link, $sql, array(), array("Scrollable" => SQLSRV_CURSOR_FORWARD));

		if(is_null($stmt)) {
			_log("no data");
			return null;
		}

		if($type == DB_RESULT_NUM) {
			$ttype = SQLSRV_FETCH_NUMERIC;
		}else {
			$ttype = SQLSRV_FETCH_ASSOC;
		}

		if(is_numeric($offset) && $offset > 0) {
			for($i = 0; $i < $offset; $i ++) {
				sqlsrv_fetch_array($stmt, $ttype, SQLSRV_SCROLL_NEXT);
			}
		}

		$result = array();
		do {
			$row = sqlsrv_fetch_array($stmt, $ttype, SQLSRV_SCROLL_NEXT);
			if($row !== false && !is_null($row)) {
				$result[] = $row;
			}elseif(is_null($row)) {
				break;
			}else {
				_log("fetch_data error: ".$sql);
				die(print_r(sqlsrv_errors(), true));
			}
		}while(1);

		sqlsrv_free_stmt($stmt);

		if($this->getCharset() != SYSTEM_CHARSET) {
			$result = convertCharset($result, $this->getCharset(), SYSTEM_CHARSET);
		}
		//_log(var_export($result, TRUE));
		return $result;
	}
}

?>
