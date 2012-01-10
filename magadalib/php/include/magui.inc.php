<?php

# LINK
define("LINK_TARGET_SELF", "__self_");
define("LINK_TARGET_NEW",  "__new_");
define("LINK_TARGET_BROWSER", "__browser_");
define("LINK_TARGET_CUSTOMCONTROL", "__custom_control_");
define("LINK_TARGET_SCRIPT", "__script_");

#
define("MAGPANEL_EXPAND_DISABLE", "__disable_");
define("MAGPANEL_EXPAND_EXPAND", "__expand_");
define("MAGPANEL_EXPAND_COLLAPSE", "__collapse_");

#
define("MAGDATE_UI_DATE",     "__date_");
define("MAGDATE_UI_TIME",     "__time_");
define("MAGDATE_UI_DATETIME", "__datetime_");
define("MAGDATE_UI_DEFAULT",  MAGDATE_UI_DATETIME);

#
define("MAGSELECT_UI_RADIO", "__radio_");
define("MAGSELECT_UI_LIST",  "__list_");
define("MAGSELECT_UI_AUTO",  "__auto_");

# MAGGadget
define("MAG_GADGET_BATTERY", "battery");
define("MAG_GADGET_SIGNAL", "signal");
define("MAG_GADGET_TIME", "time");

# EmailAddressTextFilter, FilenameTextFilter, HexadecimalTextFilter, IPTextFilter, LowercaseTextFilter, NumericTextFilter, PhoneTextFilter, UppercaseTextFilter, URLTextFilter 
define("TEXTINPUT_FILTER_EMAIL",      "_email");
define("TEXTINPUT_FILTER_FILENAME",   "_filename");
define("TEXTINPUT_FILTER_HEXDECIMAL", "_hexdecimal");
define("TEXTINPUT_FILTER_IP",         "_ip");
define("TEXTINPUT_FILTER_LOWERCASE",  "_lowercase");
define("TEXTINPUT_FILTER_NUMERIC",    "_numeric");
define("TEXTINPUT_FILTER_PHONE",      "_phone");
define("TEXTINPUT_FILTER_UPPERCASE",  "_uppercase");
define("TEXTINPUT_FILTER_URL",        "_url");
define("TEXTINPUT_FILTER_PASSWORD",   "_password");
define("TEXTINPUT_FILTER_BASIC",      "_basic");
define("TEXTINPUT_FILTER_NOTES",      "");
define("TEXTINPUT_FILTER_DEFAULT",    TEXTINPUT_FILTER_BASIC);

class MAGStyle {
	var $dat;
	public function __construct() {
		$this->dat = array();
	}
	public function setStyle($name, $val) {
		$this->dat[$name] = $val;
	}
	public function setAlignLeft() {
		$this->setStyle("align", "left");
	}
	public function setAlignCenter() {
		$this->setStyle("align", "center");
	}
	public function setAlignRight() {
		$this->setStyle("align", "right");
	}
	public function setVAlignTop() {
		$this->setStyle("valign", "top");
	}
	public function setVAlignMiddle() {
		$this->setStyle("valign", "middle");
	}
	public function setVAlignBottom() {
		$this->setStyle("valign", "bottom");
	}
	public function setWidth($width) {
		$this->setStyle("width", $width);
	}
	public function setHeight($height) {
		$this->setStyle("height", $height);
	}
	public function setIWidth($iw) {
		$this->setStyle("iwidth", $iw);
	}
	public function setIHeight($ih) {
		$this->setStyle("iheight", $ih);
	}
	public function setBorder($w, $clr, $dir='') {
		if($w > 0) {
			$k = "border-width";
			if($dir != '') {
				$k .= "-".$dir;
			}
			$this->setStyle($k, $w);
		}
		if($clr != null && $clr != '' && substr($clr, 0, 1)=='#') {
			$k = "border-color";
			if($dir != '') {
				$k .= "-".$dir;
			}
			$this->setStyle($k, $clr);
		}
	}
	public function setBorderLeft($w, $clr) {
		$this->setBorder($w, $clr, "left");
	}
	public function setBorderTop($w, $clr) {
		$this->setBorder($w, $clr, "top");
	}
	public function setBorderRight($w, $clr) {
		$this->setBorder($w, $clr, "right");
	}
	public function setBorderBottom($w, $clr) {
		$this->setBorder($w, $clr, "bottom");
	}
	public function setFocusBorderColor($clr, $dir='') {
		$k = 'focus-border-color';
		if($dir != '') {
			$k .= '-'.$dir;
		}
		$this->setStyle($k, $clr);
	}
	public function setFocusBorderColorTop($clr) {
		$this->setFocusBorderColor($clr, 'top');
	}
	public function setFocusBorderColorLeft($clr) {
		$this->setFocusBorderColor($clr, 'left');
	}
	public function setFocusBorderColorRight($clr) {
		$this->setFocusBorderColor($clr, 'right');
	}
	public function setFocusBorderColorBottom($clr) {
		$this->setFocusBorderColor($clr, 'bottom');
	}
	public function setPadding($w, $dir='') {
		$k = "padding";
		if($dir != '') {
			$k .= "-".$dir;
		}
		if(is_numeric($w) && $w > 0) {
			$this->setStyle($k, (int)$w);
		}
	}
	public function setBackground($val) {
		$this->setStyle("background", $val);
	}
	public function setFocusBackground($val) {
		$this->setStyle("focus-background", $val);
	}
	public function setBgcolor($val) {
		$this->setStyle("bgcolor", $val);
	}
	public function setColor($val) {
		$this->setStyle("color", $val);
	}
	public function setBold($val) {
		$this->setStyle("fontweight", $val);
	}
	public function &getStyle() {
		return $this->dat;
	}
}

class MAGComponent {
	var $dat;
	public function __construct($title, $id="") {
		$this->dat = array("_type" => get_class($this));
		if(strlen($title) > 0) {
			$this->setTitle($title);
		}
		if(strlen($id) > 0) {
			$this->setId($id);
		}
	}
	public function &getAttr($key) { /* return by reference */
		if(array_key_exists($key, $this->dat)) {
			return $this->dat[$key];
		}else {
			$null = null;
			return $null;
		}
	}
	public function setStyle($style) {
		$val = &$style->getStyle();
		if(count($val) > 0) {
			$this->setAttr("_style", $val);
		}
	}
	public function setClass($name) {
		$this->setAttr("_class", $name);
	}
	public function setHint($hint) {
		$this->setAttr("_hint", $hint);
	}
	public function setStatus($status) {
		$this->setAttr("_status", $status);
	}
	public function setAttr($key, $val) {
		$this->dat[$key] = $val;
	}
	public function setTitle($val) {
		$this->dat["_title"] = $val;
	}
	public function setId($val) {
		$this->dat["_id"] = $val;
	}
	public function content() {
		return $this->dat;
	}
	public function toJSON() {
		return json_encode($this->dat);
	}
}

abstract class MAGContainer extends MAGComponent {
	public function __construct($title, $id="") {
		parent::__construct($title, $id);
		$this->setAttr("_content", array());
	}

	public function add($comp) {
		$content = &$this->getAttr("_content");
		$content[] = $comp->content();
	}
}

class MAGDocument extends MAGContainer {

	public function addClass($name, $style) {
		$style_tbl = &$this->getAttr("_style_tbl");
		if(is_null($style_tbl)) {
			$style_tbl = array();
			$this->setAttr("_style_tbl", $style_tbl);
			$style_tbl = &$this->getAttr("_style_tbl");
		}
		$val = &$style->getStyle();
		if(count($val) > 0) {
			$style_tbl[] = array("_name"=>$name, "_style"=>$val);
		}
	}

}

class MAGPanel extends MAGContainer {
	public function __construct($title, $expand=MAGPANEL_EXPAND_DISABLE, $id="") {
		parent::__construct($title, $id);
		$this->setAttr("_expand", $expand);
	}
}

class MAGTabPanel extends MAGContainer {
	public function __construct($default=0, $id="") {
		parent::__construct("", $id);
		if($default > 0) {
			$this->setAttr("_default", $default);
		}
	}
}

class MAGText extends MAGComponent {
	public function __construct($title, $text, $id="") {
		parent::__construct($title, $id);
		$this->setAttr("_text", $text);
	}
}

class MAGGadget extends MAGComponent {
	public function __construct($gadget, $id="") {
		parent::__construct("", $id);
		$this->setAttr("_gadget", $gadget);
	}
}

class MAGLinkableComponent extends MAGComponent {
	public function __construct($title, $link, $expire=0, $target=LINK_TARGET_SELF, $id="") {
		parent::__construct($title, $id);
		/**
		 * 图标改到样式中，通过text-style设置
		 */
		#$this->setAttr("_icon", $icon);
		$this->setAttr("_target", $target);
		if(is_string($link)) {
			$this->setAttr("_link", $link);
		}else {
			$this->setAttr("_link", $link->getURL());
		}
		$this->setAttr("_expire", $expire);
	}

	public function setNotify($notify) {
		if($notify) {
			$this->setAttr('_notify', 'true');
		}else {
			$this->setAttr('_notify', 'false');
		}
	}

        /**
	图标改到样式中，通过visited-text-style设置
	public function setVisitedIcon($icon) {
		$this->setAttr("_icon_visited", $icon);
	}*/

	public function setSave($save) {
		if($save) {
			$this->setAttr("_save", "true");
		}else {
			$this->setAttr("_save", "false");
		}
	}
}

class MAGFrame extends MAGComponent {
	public function __construct($link, $id) {
		parent::__construct("", $id);
		$this->setAttr("_link", $link->getURL());
		$this->setAttr("_expire", $link->getExpireMilliseconds());
		$this->setAttr("_notify", $link->isNotify());
		$this->setAttr("_save", $link->isSaveHistory());
	}
}

class MAGLink extends MAGLinkableComponent {
}

class MAGMenuItem extends MAGLinkableComponent {
}

class MAGFileLink extends MAGComponent {
	public function __construct($title, $icon, $link, $id="") {
		parent::__construct($title, $id);
		$this->setAttr("_icon", $icon);
		if(is_string($link)) {
			$this->setAttr("_link", $link);
		}else {
			$this->setAttr("_link", $link->getURL());
		}
	}
}

class MAGNote extends MAGComponent {
	public function __construct($title, $note, $id="") {
		parent::__construct($title, $id);
		$this->setAttr("_note", $note);
	}
}

class MAGScript extends MAGComponent {
	public function __construct($scripts, $id="") {
		parent::__construct("MAGScript", $id);
		$this->setAttr("_scripts", $scripts);
	}
}

class MAGImage extends MAGComponent {
	public function __construct($title, $src, $expire=0, $id="") {
		parent::__construct($title, $id);
		$this->setAttr("_src", $src);
		$this->setAttr("_expire", $expire);
	}
}

define("ORDER_FIELD_TYPE_NUMERIC", "NUMERIC");
define("ORDER_FIELD_TYPE_TEXT", "TEXT");

class MAGListOrderFields {
	var $__fields = null;
	public function __construct() {
		$this->__fields = array();
	}

	public function add($label, $field, $type=null) {
		$info = array("_label"=>$label, "_field"=>$field);
		if(!is_null($type)) {
			$info["_type"] = $type;
		}
		$this->__fields[] = $info;
	}

	public function content() {
		return $this->__fields;
	}
}

class MAGList extends MAGContainer {
	public function setOrderFields($fieldinfo, $descending) {
		$this->setAttr("_orderby", $fieldinfo->content());
		if($descending) {
			$this->setAttr("_descending", "true");
		}
	}

	public function setItemsPerPage($num) {
		$this->setAttr("_items_per_page", $num);
	}

	public function setFooter($str) {
		$this->setAttr("_footer", $str);
	}
}

class MAGInputList extends MAGList {
	public function setValue($value) {
		if(!is_string($value)) {
			$value = json_encode($value);
		}
		if(strlen($value) > 0) {
			$this->setAttr("_value", $value);
		}
	}

	public function readOnly() {
		$this->setAttr("_readonly", "true");
	}

	public function nonEmpty() {
		$this->setAttr("_nonempty", "true");
	}

	public function verifyMessage($msg) {
		$this->setAttr("_vmsg", $msg);
	}
}

abstract class MAGInput extends MAGComponent {
	public function __construct($title, $id, $value="") {
		parent::__construct($title, $id);
		if(!is_string($value)) {
			$value = json_encode($value);
		}
		if(strlen($value) > 0) {
			$this->setAttr("_value", $value);
		}
	}

	public function readOnly() {
		$this->setAttr("_readonly", "true");
	}

	public function nonEmpty() {
		$this->setAttr("_nonempty", "true");
	}

	public function verifyMessage($msg) {
		$this->setAttr("_vmsg", $msg);
	}
}

class MAGTextinput extends MAGInput {
	public function __construct($title, $id, $value="", $filter=TEXTINPUT_FILTER_DEFAULT) {
		parent::__construct($title, $id, $value);
		$this->setAttr("_filter", $filter);
	}
}

class MAGPassword extends MAGInput {
	public function __construct($title, $id, $value="") {
		parent::__construct($title, $id, $value);
	}
}

class MAGDate extends MAGInput {
	# $date: in milliseconds
	public function __construct($title, $id, $value=0, $ui=MAGDATE_UI_DEFAULT) {
		parent::__construct($title, $id, $value);
		$this->setAttr("_ui", $ui);
	}
}

class MAGSelect extends MAGInput {
	public function __construct($title, $id, $value, $ui=MAGSELECT_UI_AUTO) {
		parent::__construct($title, $id, $value);
		$this->setAttr("_options", array());
		$this->setAttr("_ui", $ui);
	}

	public function addOption($text, $val) {
		$options = &$this->getAttr("_options");
		$options[] = array("_text"=>$text, "_value"=>$val);
	}
}

class MAGMultiselect extends MAGInput {
	public function __construct($title, $id) {
		parent::__construct($title, $id);
		$this->setAttr("_options", array());
	}

	public function addOption($text, $val, $checked) {
		$options = &$this->getAttr("_options");
		if(!is_bool($checked)) {
			$checked = false;
		}
		$options[] = array("_text"=>$text, "_value"=>$val, "_checked"=>$checked);
	}
}

class MAGSubmit extends MAGInput {
	public function __construct($title, $action, $url, $target=LINK_TARGET_SELF, $id="") {
		parent::__construct($title, $id);
		$this->setAttr("_action", $action);
		$this->setAttr("_url", $url);
		$this->setAttr("_target", $target);
	}

	public function setConfirm($msg) {
		$this->setAttr("_confirm", $msg);
	}

	public function setRequire($list) {
		$this->setAttr("_require", $list);
	}
}

class MAGCustominput extends MAGInput {
	public function __construct($title, $classname, $value, $params, $id) {
		parent::__construct($title, $id, $value);
		$this->setAttr("_classname", $classname);
		$this->setAttr("_params", $params);
	}
}

class MAGHiddenInput extends MAGInput {
	public function __construct($id, $value) {
		parent::__construct("", $id, $value);
	}
}

class MAGRadio extends MAGInput {
	public function __construct($label, $group, $value, $id) {
		parent::__construct($label, $id, $value);
		$this->setAttr("_group", $group);
	}

 	public function check() {
                $this->setAttr("_checked", "true");
        }
}

define("MAGINFOGRID_DATA_TYPE_STRING", "string");
define("MAGINFOGRID_DATA_TYPE_DATE", "date");
define("MAGINFOGRID_DATA_TYPE_NUMBER", "int");

class MAGInfoGrid extends MAGInput {
	public function __construct($label, $fields, $data, $limit, $id="", $value="") {
		parent::__construct($label, $id, $value);
		$this->setAttr("_fields", $fields);
		$this->setAttr("_data", $data);
		$this->setAttr("_number", $limit);
	}
}

class MAGKeywordFilterSelect extends MAGInput{
	public function __construct($label, $multichoice, $options, $keyvalue, $src, $id) {
		parent::__construct($label, $id, $keyvalue);
		if($multichoice || $multichoice=="true" || $multichoice=="false") {
			$this->setAttr("_multichoice", $multichoice);
		}
		if(!is_null($src) && strlen($src) > 0) {
			$this->setAttr("_src", $src);
		}else if(is_array($options) && count($options) > 0){
			$this->setAttr("_options", $options);
		}else {
			die("either _src or _options must present!");
		}
	}
}

class MAGTieredSelectSuboption {
	public function __construct($options, $title=null) {
		$this->_options = $options;
		if(!is_null($title) && strlen($title) > 0) {
			$this->_title = $title;
		}
	}
}

class MAGTieredSelectOption {
	public function __construct($text, $value, $sub=null) {
		$this->_text = $text; 
		$this->_value = $value;
		if(!is_null($sub)) {
			$this->_suboption = $sub;
		}
	}
}

class MAGTieredSelect extends MAGInput{
	public function __construct($label, $options, $value="", $id=""){
		parent::__construct($label, $id, $value);
		$this->setAttr("_options", $options);
	}
}

class MAGCombo extends MAGContainer {
	public function __construct($id="") {
		parent::__construct("", $id);
	}
}

class MAGInputCombo extends MAGCombo {
	public function __construct($id, $value="") {
		parent::__construct("", $id);
		if(!is_string($value)) {
			$value = json_encode($value);
		}
		if(strlen($value) > 0) {
			$this->setAttr("_value", $value);
		}
	}

	public function readOnly() {
		$this->setAttr("_readonly", "true");
	}

	public function nonEmpty() {
		$this->setAttr("_nonempty", "true");
	}

	public function verifyMessage($msg) {
		$this->setAttr("_vmsg", $msg);
	}
}

class MAGBarcodeScanner extends MAGInput {
	public function __construct($label, $id) {
		parent::__construct($label, $id, "");
	}
}

class MAGInputDuplicator extends MAGInput {
	public function __construct($label, $id, $template, $value="") {
		parent::__construct($label, $id, $value);
		$this->setAttr("_template", $template->content());
	}

	public function setInsertable($can) {
		if ($can) {
			$this->setAttr("_insert", "true");
		} else {
			$this->setAttr("_insert", "false");
		}
	}

	public function setDeleteable($can) {
		if ($can) {
			$this->setAttr("_delete", "true");
		} else {
			$this->setAttr("_delete", "false");
		}
	}

	public function setSortable($can) {
		if ($can) {
			$this->setAttr("_sort", "true");
		} else {
			$this->setAttr("_sort", "false");
		}
	}

	public function setMinCount($min) {
		$this->setAttr("_min_count", $min);
	}

	public function setMaxCount($max) {
		$this->setAttr("_max_count", $max);
	}
}

class MAGLinkURL {

	public function __construct($url=null) {
		$this->_scheme = '';
		$this->_host = '';
		$this->_port = '';
		$this->_user = '';
		$this->_pass = '';
		$this->_path = '';
		$this->_query = array();
		$this->_fragment = '';

		$this->_notify = TRUE;
		$this->_save   = TRUE;
		$this->_expire = 0;

		if(!is_null($url) && strlen($url) > 0) {
			$result = parse_url($url);
			if($result !== FALSE) {
				if(isset($result['scheme']) && !is_null($result['scheme']) && strlen($result['scheme']) > 0) {
					$this->_scheme = strtolower($result['scheme']);
				}
				if(isset($result['host']) && !is_null($result['host']) && strlen($result['host']) > 0) {
					$this->_host = $result['host'];
				}
				if(isset($result['port']) && !is_null($result['port']) && strlen($result['port']) > 0) {
					$this->_port = $result['port'];
				} 
				if(isset($result['user']) && !is_null($result['user']) && strlen($result['user']) > 0) {
					$this->_user = $result['user'];
				} 
				if(isset($result['pass']) && !is_null($result['pass']) && strlen($result['pass']) > 0) {
					$this->_pass = $result['pass'];
				} 
				if(isset($result['path']) && !is_null($result['path']) && strlen($result['path']) > 0) {
					$this->_path = $result['path'];
				} 
				if(isset($result['query']) && !is_null($result['query']) && strlen($result['query']) > 0) {
					parse_str($result['query'], $this->_query);
					if(get_magic_quotes_gpc()) {
						function stripslashes_gpc(&$value) {
							$value = stripslashes($value);
						}
						array_walk_recursive($this->_query, 'stripslashes_gpc');
                			}
				}
				if(isset($result['fragment']) && !is_null($result['fragment']) && strlen($result['fragment']) > 0) {
					$this->_fragment = $result['fragment'];
				}
			}
		}
	}

	public function getURL() {
		$str = '';
		if(strlen($this->_scheme) > 0) {
			$str .= $this->_scheme."://";
		}
		if(strlen($this->_user) > 0 || strlen($this->_pass) > 0) {
			$auth = urlencode($this->_user).":".urlencode($this->_pass);
		}else {
			$auth = '';
		}
		if(strlen($auth) > 0) {
			$str .= $auth.'@';
		}
		if(strlen($this->_host) > 0) {
			$str .= $host;
		}
		if(strlen($this->_port) > 0 && (($this->_scheme == "http" && $this->_port != "80") || ($this->_scheme == "https" && $this->_port != "443"))) {
			$str .= ":".$this->_port;
		}
		if(strlen($this->_path) > 0) {
			$str .= $this->_path;
		}
		$query = http_build_query($this->_query);
		if(strlen($query) > 0) {
			$str.= '?'.$query;
		}
		if(strlen($this->_fragment) > 0) {
			$str.= '#'.$this->_fragment;
		}
		return $str;
	}

	public function &setHandler($handle) {
		$this->_query['_action'] = $handle;
		return $this;
	}

	public function &addParam($key, $value) {
		$this->_query[$key] = $value;
		return $this;
	}

	public function &setNotify($notify) {
		$this->_notify = $notify;
		return $this;
	}

	public function isNotify() {
		return $this->_notify;
	}

	public function &setSaveHistory($save) {
		$this->_save = $save;
		return $this;
	}

	public function isSaveHistory() {
		return $this->_save;
	}

	public function &setExpireHours($hours) {
		$this->_expire = $hours*3600*1000;
		return $this;
	}

	public function &setExpireDays($days) {
		$this->_expire = $days*24*3600*1000;
		return $this;
	}

	public function getExpireMilliseconds() {
		return $this->_expire;
	}

	public function toObject() {
		$obj = (object)array();
		$obj->_link   = $this->getURL();
		$obj->_expire = $this->getExpireMilliseconds();
		$obj->_notify = $this->isNotify()?"true":"false";
		$obj->_save   = $this->isSaveHistory()?"true":"false";
		return $obj;
	}

}


?>
