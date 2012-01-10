<table border=0 width='100%'><tr>
<td valign=top>
<?php

$config = array('menus'=>array('添加'), 'func'=>'addMDSServerRecord');

showPanel2(0, '_push_config', '<b>推送服务器配置</b>', PANEL_EXPANDABLE_EXPAND, $config, 'showMDSServers', 
array(
'query_vars'       => 'id, vc_protocol, vc_mdsserver, iu_mdsport, iu_interval, itu_state',
'query_tables'     => 'push_config_tbl',
'query_conditions' => '',
'query_order'      => 'dt_create desc'
),
array('limit'=>25, 'position'=>PAGE_POSITION_BOTH));

?>
</td><td valign=top>
<!--?php

showPanel2(0, '_global_account_setting', '<b>全局账户设置</b>', PANEL_EXPANDABLE_EXPAND, null, 'showGlobalAccountSettings',
array(
'query_vars'       => '*',
'query_tables'     => 'account_tbl',
'query_conditions' => "vc_module='*' AND vc_account='*'",
'query_order'      => ''
),
null);

?-->
<div id='_change_admin_password'></div>
</td></tr></table>
<?php

if($_SESSION['_user'] == 'admin') {
?>
<script language='JavaScript'>
<!--

function init_change_admin_password_callback(msg) {
	if(isErrorMsg(msg)) {
		showAlert(msg);
	}else {
		showAsyncMsg('更改成功！');
	}
}

function init_change_admin_password_validate(form) {
	with(form) {
		if(!checkDOM(_admin_password, ".+", "密码不能为空！")) {
			return false;
		}
		if(!checkDOM(_admin_password2, "^" + _admin_password.value + "$", "密码不一致！")) {
			return false;
		}
	}
	return true;
}

function init_change_admin_password(panel) {
	var form = newRPCForm({
		action: 'CHANGE_ADMIN_PASSWORD',
		callback: 'init_change_admin_password_callback',
		onsubmit: init_change_admin_password_validate
	}, panel);

	var tbl = newTableElement('', 0, 0, 2, '', 4, 2, ['right', 'left'], 'middle', form);

	tblCell(tbl, 0, 0).innerHTML = '管理员(admin)密码设置';
	tblCell(tbl, 0, 0).style.fontWeight = 'bold';
        tblCell(tbl, 0, 0).align = 'left';

	tblCell(tbl, 1, 0).innerHTML = '修改管理员密码：';
	tblCell(tbl, 1, 1).appendChild(newInputElement('password', '_admin_password', ''));

	tblCell(tbl, 2, 0).innerHTML = '重复密码：';
	tblCell(tbl, 2, 1).appendChild(newInputElement('password', '_admin_password2', ''));

	tblCell(tbl, 3, 1).appendChild(newInputElement('submit', '', '修改'));
}

function init_change_cache_update_view(panel) {
	var form = newRPCForm({
		action: 'CHANGE_CACHE_UPDATE_VIEW',
		callback: 'init_change_admin_password_callback'
	}, panel);

	var tbl = newTableElement('', 0, 0, 2, '', 5, 2, ['right', 'left'], 'middle', form);

	tblCell(tbl, 0, 0).innerHTML = '主动推送轮询设置';
	tblCell(tbl, 0, 0).style.fontWeight = 'bold';
        tblCell(tbl, 0, 0).align = 'left';

	tblCell(tbl, 1, 0).innerHTML = '快轮训间隔（秒）：';
	var fast_poll_interval_opt = newSelector([10, 30, 60, 90, 120, 300, 900, 1800, 3600], '30', '_fast_interval');
	tblCell(tbl, 1, 1).appendChild(fast_poll_interval_opt);

	tblCell(tbl, 2, 0).innerHTML = '慢轮询间隔（分钟）';
	var slow_poll_interval_opt = newSelector([5, 10, 15, 30, 60, 120, 240, 720], '30', '_slow_interval');
	tblCell(tbl, 2, 1).appendChild(slow_poll_interval_opt);

	tblCell(tbl, 3, 0).innerHTML = '慢轮询阈值（天）：';
	var poll_threshold_opt = newSelector([1, 2, 3, 4, 5, 6, 7], '1', '_poll_threshold');
	tblCell(tbl, 3, 1).appendChild(poll_threshold_opt);

	tblCell(tbl, 4, 1).appendChild(newInputElement('submit', '', '修改'));
}

EventManager.Add(window, 'load', function(ev, obj) {
	init_change_admin_password(document.getElementById('_change_admin_password'));
	init_change_cache_update_view(document.getElementById('_change_admin_password'));
});

//-->
</script>
<?php
}

?>
