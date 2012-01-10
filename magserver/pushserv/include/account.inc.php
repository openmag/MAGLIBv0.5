<?php


define("ALL_MODULE", "*");
define("CREATED_AUTOMATIC", "0");
define("CREATED_MANUAL", "1");

class Account {

	public function __construct(&$db, $account, $module) {
		$this->__db = $db;
		$this->__account = strtolower($account);
		$this->__module = $module;

		$this->__init();
	}

/*
create table account_tbl (
        vc_module  varchar(128),
        vc_account varchar(32),
        dt_whencreated datetime,
        vc_pin      varchar(48),
        ti_lockpin  tinyint unsigned,
        bl_config   blob,
        primary key (vc_module, vc_account),
        index(vc_pin)
);
*/

	public static function getGlobalAccount(&$db) {
		return new Account($db, "*", "*");
	}

	public static function isAcceptRegistedOnly(&$db) {
		$account = Account::getGlobalAccount($db);
		return $account->isLockPIN();
	}

	public static function getGlobalConfig(&$db) {
		$account = Account::getGlobalAccount($db);
		return $account->getConfig();
	}

	public static function isAccountInList(&$db, $module, $account) {
		$cnt = $db->get_item_count("account_tbl", "vc_module='{$module}' AND vc_account='{$account}'");
		return ($cnt > 0);
	}

	public static function getAccountLockedPIN(&$db, $module, $account) {
		$dbrow = $db->get_single_assoc("vc_pin", "account_tbl", "vc_module='{$module}' and vc_account='{$account}' and ti_lockpin!=0");
		if(!is_null($dbrow)) {
			return $dbrow['vc_pin'];
		}else {
			return null;
		}
	}

	public static function getPINLockedAccount(&$db, $module, $pin) {
		$dbrow = $db->get_single_assoc("vc_account", "account_tbl", "vc_module='{$module}' and vc_pin='{$pin}' and ti_lockpin!=0");
		if(!is_null($dbrow)) {
			return $dbrow['vc_account'];
		}else {
			return null;
		}
	}

	public static function getModuleList(&$db) {
		$rows = $db->get_arrays("DISTINCT vc_module", "account_tbl", "vc_module!='*'", "vc_module");
		$modules = array();
		if(!is_null($rows)) {
			for($i = 0; $i < count($rows); $i ++) {
				$modules[] = $rows[$i][0];
			}
		}
		return $modules;
	}

	public static function exportAccountCSV(&$db, $module=null) {
		$cond = "vc_module!='*' AND vc_account!='*'";
		if(!is_null($module) && $module != '') {
			if(strlen($cond) > 0) {
				$cond .= " AND ";
			}
			$cond = "vc_module='{$module}'";
		}
		$rows = $db->get_arrays("vc_module, vc_account, vc_pin, ti_lockpin", "account_tbl", $cond);
		if(!is_null($rows)) {
			$output = '';
			for($i = 0; $i < count($rows); $i ++) {
				$output .= $rows[$i][0].", ";
				$output .= $rows[$i][1].", ";
				$output .= $rows[$i][2].", ";
				if($rows[$i][3] == 1) {
					$output .= "true\n";
				}else {
					$output .= "false\n";
				}
			}
			return $output;
		}else {
			return null;
		}
	}

	public function getAssoc() {
		return array(
			"vc_module"      => $this->__module,
			"vc_account"     => $this->__account,
			"dt_whencreated" => $this->__whencreated,
			"vc_pin"         => $this->__pin,
			"ti_lockpin"     => $this->__lockpin?'1':'0',
			"bl_config"      => json_encode($this->__config)
		);
	}

	private function __fetch($row) {
		if(!is_null($row) && array_key_exists('dt_whencreated', $row)) {
			$this->__whencreated = $row['dt_whencreated'];
		}else {
			$this->__whencreated = date("Y-m-d H:i:s");
		}
		if(!is_null($row) && array_key_exists('vc_pin', $row)) {
			$this->__pin = $row['vc_pin'];
		}else {
			$this->__pin = '';
		}
		if(!is_null($row) && array_key_exists('ti_lockpin', $row)) {
			$this->__lockpin = ($row['ti_lockpin'] == 0)?FALSE:TRUE;
		}else {
			$this->__lockpin = FALSE;
		}
		if(!is_null($row) && array_key_exists('bl_config', $row)) {
			$this->__config = json_decode($row['bl_config']);
		}else {
			$this->__config = (object)array();
		}
	}

	private function __init() {
		$row = $this->__db->get_single_assoc("dt_whencreated, vc_pin, ti_lockpin, bl_config", "account_tbl", "vc_account='{$this->__account}' and vc_module='{$this->__module}'");
		if(!is_null($row)) {
			$this->__fetch($row);
			return TRUE;
		}else if($this->__module != ALL_MODULE) {
			$row = $this->__db->get_single_assoc("dt_whencreated, vc_pin, ti_lockpin, bl_config", "account_tbl", "vc_account='{$this->__account}' and vc_module='{$this->__module}'");
			if(!is_null($row)) {
				$this->__fetch($row);
				return TRUE;
			}
		}
		$this->__fetch(null);
		$this->__commit(TRUE);
		return FALSE;
	}

	private function __commit($insert) {
		if($this->__lockpin) {
			$lock = 1;
		}else {
			$lock = 0;
		}
		$config_str = json_encode($this->__config);
		if($insert) {
			$sql = "INSERT INTO account_tbl(vc_module, vc_account, dt_whencreated, vc_pin, ti_lockpin, bl_config) values('{$this->__module}', '{$this->__account}', now(), '{$this->__pin}', {$lock}, '{$config_str}')";
			return $this->__db->query($sql);
		}else {
			return $this->__db->update("dt_whencreated='{$this->__whencreated}', vc_pin='{$this->__pin}', ti_lockpin={$lock}, bl_config='{$config_str}'", "account_tbl", "vc_module='{$this->__module}' AND vc_account='{$this->__account}'");
		}
	}

	public function isSamePIN($pin) {
		if($this->__pin == strtolower($pin)) {
			return TRUE;
		}else {
			return FALSE;
		}
	}

	public function getModule() {
		return $this->__module;
	}

	public function getAccount() {
		return $this->__account;
	}

	public function save() {
		return $this->__commit(FALSE);
	}

	public function setPIN($pin) {
		$this->__pin = strtolower($pin);
	}

	public function setLockPIN($lock) {
		$this->__lockpin = $lock;
	}

	public function getPIN() {
		return $this->__pin;
	}

	public function isLockPIN() {
		return $this->__lockpin;
	}

	public function setConfig($config) {
		$this->__config = $config;
	}

	public function getConfig() {
		return $this->__config;
	}

	public function getMergedConfig() {
		$gconf = Account::getGlobalConfig($this->__db);
		$module = new Module($this->__db, $this->__module);
		$mconf = $module->getConfig();
		$newconf = (object)array();
		$properties = array(
				"_cache_enabled",
				"_cache_default_expire",
				"_relay_enabled",
				"_relay_server_uri",
				"_service_uri",
				"_password_protected",
				"_attachment_service_uri",
				"_push_protocol",
				"_push_server",
				"_http_request_timeout"
				);
		foreach($properties as $prop) {
			if(property_exists($this->__config, $prop)) {
				$newconf->{$prop} = $this->__config->{$prop};
			}else if(property_exists($mconf, $prop)) {
				$newconf->{$prop} = $mconf->{$prop};
			}else if(property_exists($gconf, $prop)) {
				$newconf->{$prop} = $gconf->{$prop};
			}
		}
		return $newconf;
	}

	public function delete() {
		if($this->__db->delete("local_var_tbl", "vc_module='{$this->__module}' AND vc_account='{$this->__account}'") === FALSE) {
			return FALSE;
		}
		return $this->__db->delete("account_tbl", "vc_module='{$this->__module}' AND vc_account='{$this->__account}'");
	}

	public function setVar($varname, $value) {
		$var_value = $this->getVar($varname);
		if(!is_null($var_value)) {
			if($var_value != $value) {
				if($this->__db->update("vc_value='{$value}'", "local_var_tbl", "vc_module='{$this->__module}' AND vc_account='{$this->__account}' AND vc_varname='{$varname}'")) {
					return TRUE;
				}else {
					return FALSE;
				}
			}else {
				return TRUE;
			}
		}else{
			$sql = "INSERT INTO local_var_tbl(vc_module, vc_account, vc_varname, vc_value) values('{$this->__module}', '{$this->__account}', '{$varname}', '{$value}')";
			if($this->__db->query($sql)) {
				return TRUE;
			}else {
				return FALSE;
			}
		}
	}

	public function getVar($varname) {
		$_db_row = $this->__db->get_single_array("vc_value", "local_var_tbl", "vc_module='{$this->__module}' AND vc_account='{$this->__account}' AND vc_varname='{$varname}'");
		if(!is_null($_db_row) && count($_db_row) > 0) {
			return $_db_row[0];
		}else {
			return null;
		}
	}

}

class Module extends Account {
	public function __construct(&$db, $module) {
		parent::__construct($db, '*', $module);
	}

	public function acceptRegistedOnly() {
		return parent::isLockPIN();
	}
}

?>
