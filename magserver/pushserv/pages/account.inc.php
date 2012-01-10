<?php

$account_sql = "select * from account_tbl where vc_module!='*' AND vc_account!='*'";

$title = "";

if(isset($_module)) {
	$account_sql .= " AND vc_module='{$_module}'";
	$title = $_module;
}

if(strlen($title) > 0) {
	$title = "<b>账号列表({$title})</b>";
}else {
	$title = "<b>账号列表</b>";
}

$config = array('menus'=>array('添加', '从csv文件导入', '导出为csv文件'), 'func'=>'accountMenuActions');

$confname = '_account_list';

showSearchBox($confname, '按关键字搜索', array("a.vc_module",  'a.vc_account', 'a.vc_pin', 'b.vc_device'), 20);

showPanel2(0, $confname, $title, PANEL_UNEXPANDABLE, $config, 'showAccounts',
array(
'query_vars'       => "a.vc_module, a.vc_account, a.vc_pin, a.ti_lockpin, b.vc_device, a.dt_whencreated, a.bl_config",
'query_tables'     => "({$account_sql}) a left join device_tbl b on a.vc_module=b.vc_module and a.vc_account=b.vc_account and a.vc_pin=b.vc_pin",
'query_conditions' => '',
'query_order'      => ""
),
array('limit'=>25, 'position'=>PAGE_POSITION_BOTH));

?>
<div id='__showvar_pane'></div>
