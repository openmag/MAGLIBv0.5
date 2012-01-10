<table width=100% border=0><tr>
<td width=30% valign='top'>
<?php

showPanel2(0, '_group_panel', '组', PANEL_UNEXPANDABLE, null, 'showGroups', 
array(
'query_vars'       => '',
'query_tables'     => 'group_tbl',
'query_conditions' => '',
'query_order'      => ''
),
null);

?>
</td><td width=30% valign='top'>
<?php

showPanel2(0, '_role_panel', '角色', PANEL_UNEXPANDABLE, null, 'showRoles', 
array(
'query_vars'       => '',
'query_tables'     => 'role_tbl',
'query_conditions' => '',
'query_order'      => ''
),
null);

?>
</td><td width=40% valign='top'>
<?php

if($_SESSION['_user'] == 'admin') {
	$config = array('menus'=>array('添加'), 'func'=>'addUserRecord');
}else {
	$config = null;
}

if($_SESSION['_user'] !== 'admin') {
	$cond = "vc_user='{$_SESSION['_user']}'";
}else {
	$cond = '';
}

showPanel2(0, '_user_panel', '管理账号', PANEL_UNEXPANDABLE, $config, 'showUsers', 
array(
'query_vars'       => "id, vc_user, vc_name",
'query_tables'     => 'user_tbl',
'query_conditions' => $cond,
'query_order'      => 'id'
),
array('limit'=>25, 'position'=>PAGE_POSITION_BOTH));

?>
</td></tr></table>
