function showMDMPanel(pin, server, pushproto) {
	var diag = showDialog({
		id: '_mdm_panel_',
                is_modular: true,
		width: 480,
		caption: '设备管理(' + pin + ')',
		cancelText: '关闭',
                onCancel: function() { return true; }
        });

	var frame = newTableElement('', 0, 0, 4, '', 8, 1, 'left', 'middle', diag);
	tblCell(frame, 0, 0).innerHTML = '重置锁屏密码';
	tblCell(frame, 0, 0).style.fontWeight = 'bold';
	tblCell(frame, 0, 0).style.borderBottom = '1px solid #888888';
	tblCell(frame, 2, 0).innerHTML = '远程擦除';
	tblCell(frame, 2, 0).style.fontWeight = 'bold';
	tblCell(frame, 2, 0).style.borderBottom = '1px solid #888888';
	tblCell(frame, 4, 0).innerHTML = '远程锁屏';
	tblCell(frame, 4, 0).style.fontWeight = 'bold';
	tblCell(frame, 4, 0).style.borderBottom = '1px solid #888888';
	tblCell(frame, 6, 0).innerHTML = '终端安全策略配置';
	tblCell(frame, 6, 0).style.fontWeight = 'bold';
	tblCell(frame, 6, 0).style.borderBottom = '1px solid #888888';

	var form = newMDMForm('MDM_COMMAND_RESET_PASSWORD', 'onMDMCommandResetPassowordReturn', onMDMCommandResetPasswordSubmit, tblCell(frame, 1, 0), server, pin, pushproto);

	var tbl = newTableElement('', 0, 0, 2, '', 4, 2, 'left', 'middle', form);
	tblCell(tbl, 1, 0).innerHTML = "输入密码：";
        tblCell(tbl, 1, 1).appendChild(newInputElement('password', '_password', ''));
        tblCell(tbl, 2, 0).innerHTML = "重新输入：";
        tblCell(tbl, 2, 1).appendChild(newInputElement('password', '_password2', ''));
        tblCell(tbl, 3, 1).appendChild(newInputElement('submit', '', '重置'));
        tblCell(tbl, 3, 1).align = 'center';

	var form = newMDMForm('MDM_COMMAND_REMOTE_WIPE', 'onMDMCommandRemoteWipeReturn', onMDMCommandRemoteWipeSubmit, tblCell(frame, 3, 0), server, pin, pushproto);
	var tbl = newTableElement('', 0, 0, 2, '', 1, 3, 'left', 'middle', form);
	tblCell(tbl, 0, 1).innerHTML = '远程擦除';
	tblCell(tbl, 0, 2).appendChild(newInputElement('submit', '', '执行'));

	var form = newMDMForm('MDM_COMMAND_REMOTE_LOCK', 'onMDMCommandRemoteLockReturn', onMDMCommandRemoteLockSubmit, tblCell(frame, 5, 0), server, pin, pushproto);
	var tbl = newTableElement('', 0, 0, 2, '', 1, 3, 'left', 'middle', form);
	tblCell(tbl, 0, 1).innerHTML = '远程锁屏';
	tblCell(tbl, 0, 2).appendChild(newInputElement('submit', '', '执行'));

	var form = newMDMForm('MDM_COMMAND_SET_POLICY', 'onMDMCommandSetPolicyReturn', onMDMCommandSetPolicySubmit, tblCell(frame, 7, 0), server, pin, pushproto);
	var tbl = newTableElement('', 0, 0, 2, '', 5, 2, 'left', 'middle', form);
	tblCell(tbl, 0, 0).innerHTML = '密码重试次数：';
	tblCell(tbl, 0, 0).align = 'right';
	tblCell(tbl, 0, 1).appendChild(newInputElement('text', '_max_failed_pwds', ''));
	tblCell(tbl, 1, 0).innerHTML = '自动锁屏时间（秒）：';
	tblCell(tbl, 1, 0).align = 'right';
	tblCell(tbl, 1, 1).appendChild(newInputElement('text', '_max_time_lock', ''));
	tblCell(tbl, 2, 0).innerHTML = '密码最短长度：';
	tblCell(tbl, 2, 0).align = 'right';
	tblCell(tbl, 2, 1).appendChild(newInputElement('text', '_min_pwd_len', ''));
	tblCell(tbl, 3, 0).innerHTML = '密码复杂度要求：';
	tblCell(tbl, 3, 0).align = 'right';
	var pwd_quality_sel_opt = {
		"0": "不对密码进行任何限制",
		"65536": "需要某种类型的密码但是并不care",
		"131072": "密码最起码需要包含数字",
		"393216": "密码必须同时包含数字&字母&特殊符号",
		"327680": "密码必须同时包含数字&字母或符号",
		"262144": "密码必须包含字母或符号"
		};
	var pwd_quality_sel = newSelector(pwd_quality_sel_opt, '', '_pwd_quality');
	tblCell(tbl, 3, 1).appendChild(pwd_quality_sel);
	tblCell(tbl, 4, 1).appendChild(newInputElement('submit', '', '设置'));
	tblCell(tbl, 4, 1).align = 'center';
}

function newMDMForm(action, callback, onsubmit, diag, server, pin, pushproto) {
	return newRPCForm({
		action: action,
		callback: callback,
		onsubmit: onsubmit,
		params: {
			_server: server,
			_proto: pushproto,
			_pin: pin
		}
	}, diag);
}

function onMDMCommandResetPasswordSubmit(form) {
	with(form) {
		if(!checkDOMNonempty(_password, '密码不能为空！')) {
			return false;
		}
		if(!checkDOMEqual(_password2, _password.value, '重复密码不一致！')) {
			return false;
		}
	}
	return true;
}

function onMDMCommandResetPasswordReturn(msg) {
	if(isErrorMsg(msg)) {
		showAlert(msg);
	}else {
		closeDiagWindow('_mdm_panel_');
		var showmsg = '重置密码成功！';
		showAsyncMsg(showmsg);
	}
}

function onMDMCommandRemoteWipeReturn(msg) {
	if(isErrorMsg(msg)) {
		showAlert(msg);
	}else {
		closeDiagWindow('_mdm_panel_');
		var showmsg = '发送远程擦除成功！';
		showAsyncMsg(showmsg);
	}
}

function onMDMCommandRemoteLockReturn(msg) {
	if(isErrorMsg(msg)) {
		showAlert(msg);
	}else {
		closeDiagWindow('_mdm_panel_');
		var showmsg = '发送远程锁屏成功！';
		showAsyncMsg(showmsg);
	}
}

function onMDMCommandSetPolicyReturn(msg) {
	if(isErrorMsg(msg)) {
		showAlert(msg);
	}else {
		closeDiagWindow('_mdm_panel_');
		var showmsg = '重置IT策略成功！';
		showAsyncMsg(showmsg);
	}
}

function onMDMCommandSetPolicySubmit(form) {
	with(form) {
		if(!checkDOMInteger(_max_failed_pwds, '必须为正整数！', true)) {
			return false;
		}
		if(!checkDOMInteger(_max_time_lock, '必须为正整数！', true)) {
			return false;
		}
		if(!checkDOMInteger(_min_pwd_len, '必须为正整数！', true)) {
			return false;
		}
	}
	return true;
}

function onMDMCommandRemoteWipeSubmit(form) {
	if(prompt('请输入"I understand the risk"确认执行远程擦除：') == 'I understand the risk') {
		return true;
	}else {
		alert('远程擦除被取消');
		return false;
	}
}

function onMDMCommandRemoteLockSubmit(form) {
	if(confirm('确定执行远程锁屏？')) {
		return true;
	}else {
		return false;
	}
}
