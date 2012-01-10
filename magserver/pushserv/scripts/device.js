function showDevices(records, panel, conf) {
	if(records.length == 0) {
		panel.innerHTML = "No records!";
	}else {
		var headers = [
		{
			title: "",
			value: function(c, r) {
				if(Number(r.ti_lockpin) != 0) {
					c.innerHTML = "<img src='images/lockicon_small.png'>";
				}
			}
		},
		{
			title: "模块",
			field: "a.vc_module",
			value: function(c, r) {
				c.innerHTML = r.vc_module; //"<a href='" + location.pathname + "?_menu=URLs&_module=" + r.vc_module + "'>" + r.vc_module + "</a>";
			}
		},
		{
			title: "账号",
			field: "a.vc_account",
			value: function(c, r) {
				c.innerHTML = r.vc_account;
			}
		},
		{
			title: "PIN",
			field: "a.vc_pin",
			value: function(c, r) {
				c.innerHTML = r.vc_pin; //"<a href='" + location.pathname + "?_menu=URLs&_pin=" + r.vc_pin + "'>" + r.vc_pin + "</a>";
			}
		},
		{
			title: "设备",
			field: "a.vc_device",
			value: function(c, r) {
				c.innerHTML = r.vc_device + "(" + r.vc_platform + ")";
				if(r.vc_capacity != null) {
					var info = r.vc_capacity.split(";");
					var size_str;
					if(info.length > 5) {
						size_str = info[4] + 'x' + info[5]
					}else {
						size_str = '宽度' + info[4];
					}
					c.title = "IMSI: " + info[0] + ";\n " + ((info[2]=='touch')?'触摸屏':'键盘输入') + ";\n " + ((info[3]=='nav')?'有导航键':'无导航键') + ";\n " + "屏幕" + size_str + "像素;\n ";
				}
			}
		},
		{
			title: "OS",
			value: function(c, r) {
				var os = 'BlackBerry';
				if(r.vc_capacity != null) {
					var info = r.vc_capacity.split(";");
					os = info[1];
				}
				c.innerHTML = os + " " + r.vc_software;
			}
		},
		{
			title: "推送服务器",
			field: "a.vc_mdsserver",
			value: function(c, r) {
				c.innerHTML = r.vc_mdsserver + "(" + r.vc_pushproto + ")";
				if(Number(r.push_server_id) > 0) {
					c.bgColor = 'green';
				}else {
					c.bgColor = 'red';
				}
			}
		},
		{
			title: "创建时间",
			field: 'a.dt_create',
			value: function(c, r) {
				c.innerHTML = r.dt_create;
			}
		},
		{
			title: "上次访问时间",
			field: 'a.dt_lastvisit',
			value: function(c, r) {
				c.innerHTML = r.dt_lastvisit;
			}
		},
		{
			title: "",
			value: function(c, r) {
				var show_btn = newInputElement('button', '', '查看订阅');
				c.appendChild(show_btn);
				show_btn.rec = r;
				show_btn.__destructor = function() {
					this.rec = null;
				};
				EventManager.Add(show_btn, 'click', function(ev, obj) {
					document.location = location.pathname + "?_menu=URLs&_pin=" + obj.rec.vc_pin + "&_module=" + obj.rec.vc_module;
				});
				var show_btn = newInputElement('button', '', '查看日志');
				c.appendChild(show_btn);
				show_btn.rec = r;
				show_btn.__destructor = function() {
					this.rec = null;
				};
				EventManager.Add(show_btn, 'click', function(ev, obj) {
					document.location = location.pathname + "?_menu=LOG&_pin=" + obj.rec.vc_pin + "&_module=" + obj.rec.vc_module;
				});
				if(Number(r.ti_lockpin) == 0) {
					var bind_btn = newInputElement('button', '', '将账号和终端绑定');
					bind_btn.rec = r;
					bind_btn.conf = conf;
					bind_btn.__destructor = function() {
						this.rec = null;
						this.conf = null;
					};
					EventManager.Add(bind_btn, 'click', function(ev, obj) {
						showConfirm({
							msg: '确定将账号' + obj.rec.vc_account + '和设备' + obj.rec.vc_pin + '绑定？绑定后该账号将只能通过该设备登录系统。',
							onOK: function() { updatePanelContentRPC(obj.conf.name, 'BIND_ACCOUNT_DEVICE', {_module:obj.rec.vc_module, _pin: obj.rec.vc_pin, _account: obj.rec.vc_account}); return true;},
							onCancel: function() {return true;}
        	                                });
					});
					c.appendChild(bind_btn);
				}else {
					var bind_btn = newInputElement('button', '', '解除账号和终端绑定');
					bind_btn.rec = r;
					bind_btn.conf = conf;
					bind_btn.__destructor = function() {
						this.rec = null;
						this.conf = null;
					};
					EventManager.Add(bind_btn, 'click', function(ev, obj) {
						showConfirm({
							msg: '确定将账号' + obj.rec.vc_account + '和设备' + obj.rec.vc_pin + '绑定解除？解除绑定后该账号可以通过任意设备登录系统。',
							onOK: function() { updatePanelContentRPC(obj.conf.name, 'UNBIND_ACCOUNT_DEVICE', {_module:obj.rec.vc_module, _pin: obj.rec.vc_pin, _account: obj.rec.vc_account}); return true;},
							onCancel: function() {return true;}
        	                                });
					});
					c.appendChild(bind_btn);
				}

				var logout_btn = newInputElement('button', '', '重设客户端密码');
				logout_btn.rec = r;
				logout_btn.conf = conf;
				logout_btn.__destructor = function() {
					this.rec = null;
					this.conf = null;
				};
				EventManager.Add(logout_btn, 'click', function(ev, obj) {
					/*showConfirm({
						msg: '确定重置密码？',
						onOK: function() { updatePanelContentRPC(obj.conf.name, 'RESET_APP_PASSWORD', {_module:obj.rec.vc_module, _pin: obj.rec.vc_pin}, onForceReauthDeviceSucc); return true;},
						onCancel: function() {return true;}
					});*/
					resetAppPassword(obj.rec.vc_module, obj.rec.vc_account, obj.rec.vc_pin);
				});
				c.appendChild(logout_btn);

				var delete_btn = newInputElement('button', '', '删除设备');
				delete_btn.rec = r;
				delete_btn.conf = conf;
				delete_btn.__destructor = function() {
					this.rec = null;
					this.conf = null;
				};
				EventManager.Add(delete_btn, 'click', function(ev, obj) {
					showConfirm({
						msg: '确定清除PIN码为' + obj.rec.vc_account + '的设备数据？',
						onOK: function() { updatePanelContentRPC(obj.conf.name, 'UNREGISTER_DEVICE', {_module:obj.rec.vc_module, _pin: obj.rec.vc_pin}); return true;},
						onCancel: function() {return true;}
					});
				});
				c.appendChild(delete_btn);

				if(r.vc_pushproto == 'PAG') {
					var mdm_btn = newInputElement('button', '', '设备管理');
					c.appendChild(mdm_btn);
					mdm_btn.rec = r;
					mdm_btn.conf = conf;
					mdm_btn.__destructor = function() {
						this.rec = null;
						this.conf = null;
					};
					EventManager.Add(mdm_btn, 'click', function(ev, obj) {
						showMDMPanel(obj.rec.vc_pin, obj.rec.vc_mdsserver, obj.rec.vc_pushproto);
					});
				}
			}
		}
		];
		newDBGrid(records, headers, "", panel, conf);
	}
}

function resetAppPassword(module, account, pin) {
	var diag = showDialog({
		id: '_reset_app_password',
		is_modular: true,
		width: 300,
		caption: '重置密码',
		//cancelText: '取消',
		onCancel: function() { return true; }
	});
	var form = newRPCForm({
		action: 'RESET_APP_PASSWORD',
		callback: 'onResetAppPasswordReturn',
		onsubmit: onResetAppPasswordSubmit,
		params: {
			_module: module,
			_account: account,
			_pin: pin
		}
	}, diag);
	var tbl = newTableElement('', 0, 0, 2, '', 3, 2, 'left', 'middle', form);
	tblCell(tbl, 0, 0).innerHTML = "输入密码：";
	tblCell(tbl, 0, 1).appendChild(newInputElement('password', '_password', ''));
	tblCell(tbl, 1, 0).innerHTML = "重新输入：";
	tblCell(tbl, 1, 1).appendChild(newInputElement('password', '_password2', ''));
	tblCell(tbl, 2, 1).appendChild(newInputElement('submit', '', '重置密码'));
	tblCell(tbl, 2, 1).align = 'center';
}

function onResetAppPasswordSubmit(form) {
	with(form) {
		if(!checkDOMEqual(_password, _password2.value, '两次输入密码不一致！')) {
			return false;
		}
	}
	return true;
}

function onResetAppPasswordReturn(msg) {
        if(isErrorMsg(msg)) {
                showAlert(msg);
        }else {
		closeDiagWindow('_reset_app_password');
                var showmsg = '重置密码成功！';
                showAsyncMsg(showmsg);
        }
}

function showLocalVars(records, panel, conf) {
        if(records.length == 0) {
                panel.innerHTML = "No records!";
        }else {
		var headers = [
		{
			title: "Variable",
			value: function(c, r) {
				c.innerHTML = r.vc_varname;
			}
		},
		{
			title: "Value",
			value: function(c, r) {
				c.innerHTML = r.vc_value;
			}
		}
		];
		newDBGrid(records, headers, "", panel);
	}
}
