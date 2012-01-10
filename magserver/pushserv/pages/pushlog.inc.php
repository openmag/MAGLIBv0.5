<?php

$title = ""; //<b>推送日志</b>";

if(isset($_cache_id)) {
	$pushlog_tbl = "(select * from pushlog_tbl where cache_id={$_cache_id})";
	if(strlen($title) > 0) {
		$title .= "/";
	}
	$title .= $_cache_id;
}else {
	$pushlog_tbl = "pushlog_tbl";
}

$cond = "";

if(isset($_pin)) {
	if(strlen($cond) > 0) {
		$cond .= " AND ";
	}
	$cond .= "vc_pin='{$_pin}'";
	if(strlen($title) > 0) {
		$title .= "/";
	}
	$title .= $_pin;
}

if(isset($_module)) {
	if(strlen($cond) > 0) {
		$cond .= " AND ";
	}
	$cond .= "vc_module='{$_module}'";
	if(strlen($title) > 0) {
		$title .= "/";
	}
	$title .= $_module;
}

if(strlen($cond) > 0) {
	$device_tbl = "(select * from device_tbl where {$cond})";
}else {
	$device_tbl = "device_tbl";
}

if(strlen($title) > 0) {
	$title = "<b>推送日志({$title})</b>";
}else {
	$title = "<b>推送日志</b>";
}

$confname = '_pushlogs';

showSearchBox($confname, '按关键字搜索', array('b.vc_module', 'b.vc_pin', 'b.vc_title', 'b.vc_url', 'a.vc_reason', 'c.vc_account'), 20, array('layout'=>'onAdvancedSearchPushLogLayout', 'fields'=>array('b.vc_module', 'c.vc_account', 'b.vc_pin', 'b.vc_title', 'b.vc_url', 'a.dt_push', 'a.itu_state', 'a.vc_reason'), 'callback'=>'onPreciseSearchCallback'));

showPanel2(0, $confname, $title, PANEL_UNEXPANDABLE, null, 'showPushLog', 
array(
'query_vars'       => 'a.id push_id, a.cache_id, b.vc_module, b.vc_pin, b.vc_title, b.vc_url, a.dt_push, a.itu_state, a.vc_reason, c.vc_account',
'query_tables'     => "{$pushlog_tbl} a JOIN cache_tbl b on a.cache_id=b.id JOIN {$device_tbl} c ON b.vc_module=c.vc_module and b.vc_pin=c.vc_pin",
'query_conditions' => '',
'query_order'      => 'a.dt_push desc'
),
array('limit'=>25, 'position'=>PAGE_POSITION_BOTH));

?>
