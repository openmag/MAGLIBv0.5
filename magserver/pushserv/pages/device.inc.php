<?php

$device_sql = "device_tbl";

$cond = "";
$title = "";

if(isset($_account)) {
	if(strlen($cond) > 0) {
		$cond .= " AND ";
	}
	$cond .= "vc_account='{$_account}'";
	if(strlen($title) > 0) {
		$title .= "/";
	}
	$title .= $_account;
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
	$device_sql = "(select * from device_tbl where {$cond})";
}

if(strlen($title) > 0) {
        $title = "<b>设备列表({$title})</b>";
}else {
        $title = "<b>设备列表</b>";
}

$confname = '_push_device';

showSearchBox($confname, '按关键字搜索', array('a.vc_module', 'a.vc_pin', 'a.vc_account', 'a.vc_device', 'a.vc_software', 'a.vc_platform', 'a.vc_capacity', 'a.vc_mdsserver', 'a.vc_pushproto'), 20);

showPanel2(0, $confname, $title, PANEL_UNEXPANDABLE, null, 'showDevices', 
array(
'query_vars'       => 'a.vc_module, a.vc_account, a.vc_pin, a.vc_device, a.vc_software, a.vc_platform, a.vc_capacity, a.vc_mdsserver, a.vc_pushproto, a.dt_create, a.dt_lastvisit, b.id as push_server_id, c.ti_lockpin',
'query_tables'     => "{$device_sql} a left join push_config_tbl b on a.vc_mdsserver=b.vc_mdsserver and a.vc_pushproto=b.vc_protocol left join account_tbl c on a.vc_module=c.vc_module and a.vc_pin=c.vc_pin and a.vc_account=c.vc_account",
'query_conditions' => "",
'query_order'      => ''
),
array('limit'=>25, 'position'=>PAGE_POSITION_BOTH));

?>
