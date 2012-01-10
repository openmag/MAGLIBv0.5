<?php

class DBConn {
	private $__db_link = null;
	private $__charset = null;

	public function __construct($charset) {
		$this->__charset = $charset;
		$this->__db_link = null;
	}

	public function open($host, $user, $passwd, $dbname, $port="", $charset=SYSTEM_CHARSET) {
		$this->__hostname = $host;
		$this->__user = $user;
		$this->__password = $passwd;
		$this->__dbname = $dbname;
		$this->__port = $port;
		$this->__charset = $charset;
	}

	public function getCharset() {
		return $this->__charset;
	}

	public function setCharset($charset) {
		$this->__charset = $charset;
	}
}

?>
