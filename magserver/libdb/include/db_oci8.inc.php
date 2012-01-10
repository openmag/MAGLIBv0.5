<?php

define("DB_ENGINE_OCI8", "OCI8");

class OCI8Conn extends DBConn {

	public function __construct($charset=SYSTEM_CHARSET) {
		parent::__construct($charset);
	}

	private function getErrorString() {
		if(is_null($this->__db_link)) {
			$e = oci_error();
		}else {
			$e = oci_error($this->__db_link);
		}
		var_dump($e);
		$msg = $e['code'].": ".$e['message'];
		if(!empty($e['sqltext'])) {
			$msg .= "(".$e['sqltext']."/".$e['offset'].")";
		}
		return $msg;
	}

	private static function ociCharsetMap($syschar) {
		/**
			* JA16EUC
			* EUC 24-bit Japanese

			* JA16EUCTILDE
			* The same as JA16EUC except for the way that the wave dash and the tilde are mapped to and from Unicode

			* JA16SJIS
			* Shift-JIS 16-bit Japanese. The same as JA16SJISTILDE except for the way that the wave dash and the tilde are mapped to and from Unicode

			* JA16SJISTILDE
			* Microsoft Windows Code Page 932 Japanese

			* KO16KSC5601
			* KSC5601 16-bit Korean

			* KO16MSWIN949
			* Microsoft Windows Code Page 949 Korean

			* TH8TISASCII
			* Thai Industrial Standard 620-2533 - ASCII 8-bit

			* VN8MSWIN1258
			* Microsoft Windows Code Page 1258 8-bit Vietnamese

			* ZHS16CGB231280
			* CGB2312-80 16-bit Simplified Chinese

			* ZHS16GBK
			* GBK 16-bit Simplified Chinese

			* ZHS32GB18030
			* GB18030-2000

			* ZHT16BIG5
			* BIG5 16-bit Traditional Chinese

			* ZHT16HKSCS
			* Microsoft Windows Code Page 950 with Hong Kong Supplementary Character Set HKSCS-2001 (character set conversion to and from Unicode is based on Unicode 3.0)

			* ZHT16MSWIN950
			* Microsoft Windows Code Page 950 Traditional Chinese

			* ZHT32EUC
			* EUC 32-bit Traditional Chinese
		
			* AL16UTF16
			* Unicode 4.0 UTF-16 Universal character set

			* AL32UTF8
			* Unicode 4.0 UTF-8 Universal character set
			*
		 */
		if(preg_match('/^UTF-8/i', $syschar) > 0) {
			return "AL32UTF8";
		}elseif(preg_match('/^UTF-16/i', $syschar) > 0) {
			return "AL16UTF16";
		}elseif(preg_match('/^GB2312/i', $syschar) > 0) {
			return "ZHS16CGB231280";
		}elseif(preg_match('/^GB18030/i', $syschar) > 0) {
			return "ZHS32GB18030";
		}elseif(preg_match('/^GBK/i', $syschar) > 0) {
			return "ZHS16GBK";
		}elseif(preg_match('/^BIG5/i', $syschar) > 0) {
			return "ZHT16BIG5";
		}else {
			trigger_error("OCI: Unknown charset!".$syschar, E_USER_ERROR);
			exit;
		}
	}

	public function open($host, $user, $passwd, $dbname, $port="", $charset=SYSTEM_CHARSET) {
		parent::open($host, $user, $passwd, $dbname, $port, $charset);
		$conn_str = "";
		if(strlen($host) > 0) {
			$conn_str = "//".$host;
			if(!empty($port)) {
				$conn_str .= ":".$port;
			}
		}
		if(strlen($conn_str) > 0) {
			$conn_str .= "/";
		}
		$conn_str .= $dbname;
		$this->__db_link = oci_connect($user, $passwd, $conn_str, OCI8Conn::ociCharsetMap($charset));
		if (!$this->__db_link) {
			$this->__db_link = null;
			printf("Connect failed: %s\n", $this->getErrorString());
			return false;
		}
		return true;
	}

	public function close() {
		if(!is_null($this->__db_link)) {
			oci_close($this->__db_link);
		}
	}

	public function query($sql) {
		if($this->getCharset() != SYSTEM_CHARSET) {
			$sql = iconv(SYSTEM_CHARSET, $this->getCharset(), $sql);
		}
		#echo $sql."\n";
		$stid = oci_parse($this->__db_link, $sql);
		if($stid !== FALSE && !is_null($stid)) {
			if(oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
				return $stid;
			}else {
				_log("OCI8 exe error: ".$sql);
			}
		}else {
			_log("OCI8 parse error: ".$sql);
		}
		return FALSE;
	}

	public function get_tables() {
		$sql = "select table_name from tabs";
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
		return -1;
	}

	public function fetch_data($sql, $limit, $offset, $type) {
		if(!is_null($offset) && $offset != "" && is_numeric($offset) && $offset > 0) {
			$min = $offset;
		}else {
			$min = 0;
		}
		if(!is_null($limit) && $limit != "" && is_numeric($limit) && $limit > 0) {
			$max = $min + $limit;
		}else {
			$max = 0;
		}
		$magic = null;
		if($min > 0 || $max > 0) {
			$magic = 'ANHEORA20110214';
			$magic_rownum = $magic."ROWNUM";
			$magic_table = $magic."ALIASTBL";
			$cond = "";
			if($min+1 == $max) {
				$cond .= $magic_rownum." = ".$max;
			}else {
				if($min > 0) {
					$cond .= $magic_rownum." >= ".($min+1);
				}
				if($max > 0) {
					if(strlen($cond) > 0) {
						$cond .= " AND ";
					}
					$cond .= $magic_rownum." <= ".$max;
				}
			}
			$sql = "SELECT * FROM (SELECT {$magic_table}.*, ROWNUM {$magic_rownum} FROM ({$sql}) {$magic_table}) WHERE ".$cond;
		}
		$stid = $this->query($sql);
		if(false === $stid) {
			return null;
		}else {
			$rows = array();
			$ttype = OCI_RETURN_NULLS + OCI_RETURN_LOBS;
			if($type == DB_RESULT_NUM) {
				$ttype += OCI_NUM;
			}else {
				$ttype += OCI_ASSOC;
			}
			while($row = oci_fetch_array($stid, $ttype)) {
				if(!is_null($magic)) {
					if($type == DB_RESULT_NUM) {
						array_splice($row, count($row)-1, 1);
					}else {
						unset($row[$magic_rownum]);
					}
				}
				$rows[] = $row;
			}
			$this->free_result($stid);
			if($this->getCharset() != SYSTEM_CHARSET) {
				$rows = convertCharset($rows, $this->getCharset(), SYSTEM_CHARSET);
			}
			return $rows;
		}
	}

	private function free_result($stid) {
		oci_free_statement($stid);
	}

}

?>
