<?php

print_header("MAG Administration Platform - 初始配置");


?>
<h1 align='center' style='padding:30px;'>MAG初始化设置</h1>
<div id='_raw_init_panel' align='center'></div>
<script language='JavaScript'>
<!--

function init_raw_panel_callback(msg) {
	if(isErrorMsg(msg)) {
		showAlert(msg);
	}else {
		refresh();
	}
}

function init_raw_panel_validate(form) {
	with(form) {
		if(!checkDOM(_admin_password, ".+", '必须输入管理员密码！')) {
			return false;
		}
		if(!checkDOM(_admin_password2, "^" + _admin_password.value + "$", '管理员密码不一致！')) {
			return false;
		}
		if(!checkDOM(_mysql_host, ".+", '请输入MySQL服务器地址！')) {
			return false;
		}
		if(!checkDOM(_mysql_user, ".+", '请输入MySQL账号！')) {
			return false;
		}
		if(!checkDOM(_mysql_password, ".+", '请输入MySQL账号密码！')) {
			return false;
		}
		if(!checkDOM(_mysql_port, ".+", '请输入MySQL服务器端口号！')) {
			return false;
		}
		if(!checkDOM(_mysql_db, ".+", '请输入MySQL数据库名称！')) {
			return false;
		}
	}
	return true;
}

function init_raw_panel(panel) {

	var form = newRPCForm({
		action: 'RAW_INIT',
		callback: 'init_raw_panel_callback',
		onsubmit: init_raw_panel_validate
	}, panel);

	var tbl = newTableElement("", 0, 0, 2, '', 13, 2, ['right', 'left'], 'middle', form);

	tblCell(tbl, 0, 0).innerHTML = '管理员密码：';
	tblCell(tbl, 0, 1).appendChild(newInputElement('password', '_admin_password', ''));
	tblCell(tbl, 1, 0).innerHTML = '重复密码：';
	tblCell(tbl, 1, 1).appendChild(newInputElement('password', '_admin_password2', ''));

	tblCell(tbl, 2, 0).innerHTML = 'MySQL服务器地址：';
	tblCell(tbl, 2, 1).appendChild(newInputElement('text', '_mysql_host', '<?php echo $_mysql_host; ?>'));
	tblCell(tbl, 3, 0).innerHTML = 'MySQL账号：';
	tblCell(tbl, 3, 1).appendChild(newInputElement('text', '_mysql_user', '<?php echo $_mysql_user; ?>'));
	tblCell(tbl, 4, 0).innerHTML = 'MySQL账号密码：';
	tblCell(tbl, 4, 1).appendChild(newInputElement('password', '_mysql_password', ''));
	tblCell(tbl, 5, 0).innerHTML = 'MySQL端口号：';
	tblCell(tbl, 5, 1).appendChild(newInputElement('text', '_mysql_port', '<?php echo $_mysql_port; ?>'));
	tblCell(tbl, 6, 0).innerHTML = 'MySQL数据库：';
	tblCell(tbl, 6, 1).appendChild(newInputElement('text', '_mysql_db', '<?php echo $_mysql_db; ?>'));

	tblCell(tbl, 7, 0).innerHTML = '是否重建数据表';
	tblCell(tbl, 7, 1).appendChild(newInputElement('checkbox', '_rebuild_db', 'TRUE'));

	tblCell(tbl, 8, 0).innerHTML = '主动推送轮询设置';
	tblCell(tbl, 8, 0).style.fontWeight = 'bold';
	tblCell(tbl, 8, 0).align = 'left';

	tblCell(tbl, 9, 0).innerHTML = '快轮训间隔（秒）：';
	var fast_poll_interval_opt = newSelector([10, 30, 60, 90, 120, 300, 900, 1800, 3600], '30', '_fast_interval');
	tblCell(tbl, 9, 1).appendChild(fast_poll_interval_opt);
	tblCell(tbl, 10, 0).innerHTML = '慢轮询间隔（分钟）';
	var slow_poll_interval_opt = newSelector([5, 10, 15, 30, 60, 120, 240, 720], '30', '_slow_interval');
	tblCell(tbl, 10, 1).appendChild(slow_poll_interval_opt);
	tblCell(tbl, 11, 0).innerHTML = '慢轮询阈值（天）：';
	var poll_threshold_opt = newSelector([1, 2, 3, 4, 5, 6, 7], '1', '_poll_threshold');
	tblCell(tbl, 11, 1).appendChild(poll_threshold_opt);

	tblCell(tbl, 12, 1).align = 'center';
	tblCell(tbl, 12, 1).appendChild(newInputElement('submit', '', '提交'));
}

EventManager.Add(window, 'load', function(ev, obj) {
	init_raw_panel(document.getElementById('_raw_init_panel'));
});

//-->
</script>
