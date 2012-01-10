function showMDSServers(records, panel, panel_conf) {
	if(records.length == 0) {
		panel.innerHTML = "No records!";
	}else {
		var tbl_conf = [
			{
				title: "#",      
				value: function(c, r) { c.innerHTML = r.id; if(r.itu_state==0) c.parentNode.style.backgroundColor='#E0E0E0'; }
			},
			{
				title: '推送协议',
				value: function(c, r) { c.innerHTML = r.vc_protocol; }
			},
			{
				title: "服务器地址",
				value: function(c, r) { c.innerHTML = r.vc_mdsserver; }
			},
			{
				title: "服务器端口",
				value: function(c, r) { c.innerHTML = r.iu_mdsport; }
			},
			/*{title: "轮询间隔（分钟）",
			 value: function(c, r) { c.innerHTML = r.iu_interval; if(r.itu_state==1) c.style.backgroundColor='#E0E0E0';}
			},*/
			{
				title: "",
				value: function(c, r) {
					var enable_txt = '停用';
					var action = 'DISABLE_MDSSERVER';
					if(Number(r.itu_state) == 0) {
						enable_txt = '启用';
						action = 'ENABLE_MDSSERVER';
					}
					var enable_btn = newInputElement('button', '', enable_txt);
					c.appendChild(enable_btn);
					enable_btn.confname = panel_conf.name;
					enable_btn.rec = r;
					enable_btn.__destruct = function() {
						this.confname = null;
						this.rec      = null;
					};
					EventManager.Add(enable_btn, 'click', function(ev, obj) {
						updatePanelContentRPC(obj.confname, action, {_id: obj.rec.id});
					});
					c.appendChild(enable_btn);
					if(Number(r.itu_state) != 0) {
						var notify_btn = newInputElement('button', '', '发送通知');
						c.appendChild(notify_btn);
						notify_btn.conf = panel_conf;
						notify_btn.rec = r;
						notify_btn.__destructor = function() {
							this.conf = null;
							this.rec = null;
						};
						EventManager.Add(notify_btn, 'click', function(ev, obj) {
							sendNotifyMessage(obj.rec.vc_protocol, obj.rec.vc_mdsserver, obj.rec.iu_mdsport);
						});
					}
					if(Number(r.itu_state) == 0) {
						var del_btn = newInputElement('button', '', '删除');
						c.appendChild(del_btn);
						del_btn.confname = panel_conf.name;
						del_btn.rec = r;
						del_btn.__destruct = function() {
							this.confname = null;
							this.rec      = null;
						};
						EventManager.Add(del_btn, 'click', function(ev, obj) {
							showConfirm({
								msg: '是否确定删除?',
								onOK: function() { updatePanelContentRPC(obj.confname, 'DEL_MDSSERVER', {_id: obj.rec.id}); return true;},
								onCancel: function() {return true;}
							});
						});
					}
			 	}
			}
		];
		newDBGrid(records, tbl_conf, "", panel);
	}
}

function mdsServerValidationForm(form) {
	if(!checkDOM(form._vc_mdsserver, '.+', '服务器地址不能为空！')) {
		return false;
	}
	if(!checkDOM(form._iu_mdsport, '^\\d{2,}$', '服务器端口必须为整数！')) {
		return false;
	}
	if(form._iu_mdsport.value == '') {
		form._iu_mdsport.value = '8080';
	}
	if(!checkDOM(form._iu_interval, '^\\d+$', '请设置轮询间隔！')) {
		return false;
	}
	return true;
}

function addMDSServerRecord(conf, menuText, panel) {
	var form = newDefaultRPCForm(panel, 'ADD_MDSSERVER', conf.name, mdsServerValidationForm);

	var tbl = newTableElement('', 0, 0, 2, '', 6, 2, 'left', 'middle');
	form.appendChild(tbl);

	tblCell(tbl, 0, 0).innerHTML = '推送协议：';
	tblCell(tbl, 1, 0).innerHTML = '推送服务器地址：';
	tblCell(tbl, 2, 0).innerHTML = '推送服务器端口：';
	//tblCell(tbl, 3, 0).innerHTML = '轮询间隔（分钟）：';
	tblCell(tbl, 4, 0).innerHTML = '启用：';

	var proto_sel = newSelector(['MDS', 'PAG', 'AOG'], '', '_vc_protocol');
	tblCell(tbl, 0, 1).appendChild(proto_sel);
	tblCell(tbl, 1, 1).appendChild(newInputElement('text', '_vc_mdsserver', ''));
	tblCell(tbl, 2, 1).appendChild(newInputElement('text', '_iu_mdsport', ''));
	tblCell(tbl, 3, 1).appendChild(newInputElement('hidden', '_iu_interval', '5'));
	tblCell(tbl, 4, 1).appendChild(newInputElement('checkbox', '_itu_state', '1'));

	tblCell(tbl, 5, 1).appendChild(newInputElement('submit', '', '添加'));
	tblCell(tbl, 5, 1).appendChild(newCancelConfigButton(conf));
}

function sendNotifyMessage(protocol, server, port) {
	if(protocol != 'MDS' && protocol != 'PAG') {
		showAlert('还不支持该推送协议！');
		return;
	}
	var diag = showDialog({
		id: '_send_notification',
                is_modular: true,
		width: 350,
		caption: '推送通知消息(' + server + ':' + port + ')',
		//cancelText: '取消',
		onCancel: function() { return true; }
	});
	var form = newRPCForm({
		action: 'SEND_NOTIFICATION',
		callback: 'onSendNotificationMessageReturn',
		onsubmit: onSendNotificationMessageSubmit,
		params: {
			_server: protocol + '://' + server + ':' + port,
			_sound: 'default'
		}
	}, diag);
	var tbl = newTableElement('', 0, 0, 2, '', 4, 1, 'left', 'middle', form);
	tblCell(tbl, 0, 0).appendChild(newTextNode("接收人PIN/Email："));
	tblCell(tbl, 0, 0).appendChild(newInputElement('text', '_account', ''));
	tblCell(tbl, 1, 0).innerHTML = "通知内容：";
	var msg_area = newTextArea('_msg', '', 43, 5, '_msg');
	tblCell(tbl, 2, 0).appendChild(msg_area);
	tblCell(tbl, 3, 0).appendChild(newInputElement('submit', '', '提交'));
	tblCell(tbl, 3, 0).align = 'center';
}

function onSendNotificationMessageSubmit(form) {
	with(form) {
		if(_server.value.indexOf('MDS') >= 0) {
			if(_account.value.length == 0) {
				showTips(_account, '必须填写接收人Emai或PIN!');
				return false;
			}
			var email_pattern = new RegExp(_email_reg);
			var pin_pattern = new RegExp(_pin_reg);
			if(!email_pattern.test(_account.value) && !pin_pattern.test(_account.value)) {
			showTips(_account, '请填写正确的接收人Emai或PIN!');
			return false;
			}
		}else if(_server.value.indexOf('PAG') >= 0) {
			if(!checkDOMNonempty(_account, '请填写接收人！')) {
				return false;
			}
		}
		if(!checkDOMNonempty(_msg, '请填写推送消息!')) {
			return false;
		}
	}
	return true;
}

function onSendNotificationMessageReturn(msg) {
	if(isErrorMsg(msg)) {
		showAlert(msg);
	}else {
		closeDiagWindow('_send_notification');
		var showmsg = '推送消息成功！';
		showAsyncMsg(showmsg);
	}
}
