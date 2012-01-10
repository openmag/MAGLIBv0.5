<?php

$cond = "";
$title = "";

if(isset($_pin)) {
	$cond = "vc_pin='{$_pin}'";
	$title = $_pin;
}
if(isset($_module)) {
	if(strlen($cond) > 0) {
		$cond .= " and ";
	}
	$cond .= "vc_module='{$_module}'";
	if(strlen($title) > 0) {
		$title .= "/";
	}
	$title .= $_module;
}
/*if(isset($_account)) {
	if(strlen($cond) > 0) {
		$cond .= " and ";
	}
	$cond .= "vc_account='{$_account}'";
	if(strlen($title) > 0) {
		$title .= "/";
	}
	$title .= $_account;
}*/

if(strlen($cond) > 0) {
	$cache_sql = "(select * from cache_tbl where {$cond})";
}else {
	$cache_sql = "cache_tbl";
}

if(strlen($title) > 0) {
	$title = "<b>订阅列表({$title})</b>";
}else {
	$title = "<b>订阅列表</b>";
}

$confname = '_subscrib_urls';

showSearchBox($confname, '按关键字搜索', array("a.vc_module", 'a.vc_pin', 'b.vc_account', 'a.vc_url', 'a.vc_title'), 20, array("layout"=>"onAvancedSearchCacheLayout", "fields"=>array('a.vc_module', 'a.vc_pin', 'b.vc_account', 'a.vc_url', 'a.dt_expire', 'a.vc_title', 'a.dt_change', 'c.dt_deadline'), "callback"=>'onPreciseSearchCallback'));

showPanel2(0, $confname, $title, PANEL_UNEXPANDABLE, null, 'showCacheURLs', 
array(
'query_vars'       => 'a.id, a.vc_module, a.vc_pin, b.vc_account, a.vc_url, a.dt_expire, a.vc_title, length(a.bl_output) as content_len, a.dt_change, a.tiu_state, a.iu_tries, c.dt_deadline, IF(DATE_ADD(c.dt_deadline, INTERVAL 1000 SECOND) > NOW(), 0, 1) over_due',
'query_tables'     => "{$cache_sql} a join device_tbl b on a.vc_pin=b.vc_pin AND a.vc_module=b.vc_module join cache_update_view c on a.id=c.id",
'query_conditions' => '',
'query_order'      => ''
),
array('limit'=>25, 'position'=>PAGE_POSITION_BOTH));

?>
