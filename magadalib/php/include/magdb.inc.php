<?php

class DBSyncConfig {
	private $__sync_path;
	private $__params;
	private $__tables;

	public function __construct(&$req, $sync_path) {
		if($req->isRequestByPushServer()) {
			$this->__config = getUserConfig($req);
		}else {
			$this->__config = registerPush($req);
		}
		$this->__sync_path = $sync_path;
		$this->__tables = array();
		$this->__params = array();
	}

	public function addTable($table) {
		$this->__tables[] = $table;
	}

	public function addParam($name, $value) {
		$this->__params[$name] = $value;
	}

	public function toJSON() {
		if(is_null($this->__config)) {
			$this->__config = (object)array();
		}
		$this->__config->_type = "__auto_db_config__";
		$this->__config->_title = "MAG Wireless Sync Database Configurations";
		$this->__config->_sync_uri = $this->__sync_path;
		$this->__config->_tables = $this->__tables;
		/*$json = array(
			"_type"     => "__auto_config__",
			"_sync_uri" => $this->__sync_path,
			"_tables"   => $this->__tables
			); */
		if(count($this->__params) > 0) {
			$this->__config->_params = $this->__params;
			#$json["_params"] = $this->__params;
		}
		return json_encode($this->__config);
	}
}

?>
