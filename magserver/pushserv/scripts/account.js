function showAccounts(records, panel, conf) {
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
			field: 'a.vc_module',
			value: function(c, r) {
				c.innerHTML = r.vc_module; //"<a href='" + location.pathname + "?_menu=ACCOUNT&_module=" + r.vc_module + "'>" + r.vc_module + "</a>";
			}
		},
		{
			title: "账号",
			field: 'a.vc_account',
			value: function(c, r) {
				c.innerHTML = r.vc_account;
			}
		},
		{
			title: "PIN",
			field: 'a.vc_pin',
			value: function(c, r) {
				c.innerHTML = r.vc_pin;
			}
		},
		{
			title: "终端",
			field: 'b.vc_device',
			value: function(c, r) {
				if(r.vc_device == null || r.vc_device == '') {
					c.innerHTML = '无设备';
				}else {
					c.innerHTML = r.vc_device;
				}
			}
		},
		{
			title: "创建时间",
			field: 'a.dt_whencreated',
			value: function(c, r) {
				c.innerHTML = r.dt_whencreated;
			}
		},
		{
			title: "",
			value: function(c, r) {
				if(r.vc_pin != null && r.vc_pin.length > 0) {
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
				}

				var edit_btn = newInputElement('button', '', '编辑');
				edit_btn.rec = r;
				edit_btn.conf = conf;
				edit_btn.__destructor = function() {
					this.rec = null;
					this.conf = null;
				};
				EventManager.Add(edit_btn, 'click', function(ev, obj) {
					var panel = getInlineEditArea(obj);
					editAccount(obj.conf, panel, obj.rec);
					
				});
				c.appendChild(edit_btn);

				var config_btn = newInputElement('button', '', '查看配置');
				c.appendChild(config_btn);
				config_btn.rec = r;
				config_btn.conf = conf;
				config_btn.__destructor = function() {
					this.rec = null;
					this.conf = null;
				};
				EventManager.Add(config_btn, 'click', function(ev, obj) {
					var pane = getInlineEditArea(obj);
					showAccountSettings(obj.rec, obj.conf, pane, false, false);
				});

				var delete_btn = newInputElement('button', '', '删除账号');
				delete_btn.rec = r;
				delete_btn.conf = conf;
				delete_btn.__destructor = function() {
					this.rec = null;
					this.conf = null;
				};
				EventManager.Add(delete_btn, 'click', function(ev, obj) {
					showConfirm({
						msg: '确定清除账号' + obj.rec.vc_account + '的数据？',
						onOK: function() { updatePanelContentRPC(obj.conf.name, 'DELETE_ACCOUNT', {_module:obj.rec.vc_module, _account: obj.rec.vc_account}); return true;},
						onCancel: function() {return true;}
                                        });
				});
				c.appendChild(delete_btn);

				var showvar_btn = newInputElement('button', '', '查看本地变量');
				showvar_btn.rec = r;
				showvar_btn.conf = conf;
				showvar_btn.__destructor = function() {
					this.rec = null;
					this.conf = null;
				};
				EventManager.Add(showvar_btn, 'click', function(ev, obj) {
					var frame = document.getElementById('__showvar_pane');
					removeAllChildNodes(frame);
					var cancel_btn = newInputElement('button', '', '取消');
					frame.appendChild(cancel_btn);
					EventManager.Add(cancel_btn, 'click', function(ev, obj) {
						var frame = document.getElementById('__showvar_pane');
						removeAllChildNodes(frame);
					});
					var div = document.createElement('div');
					div.id = '_showvar_div_pane';
					frame.appendChild(div);
					makeContentPanel(div, {
						frame_scheme: 0,
						expand: 'none',
						title: obj.rec.vc_module + '/' + obj.rec.vc_account + ' - 变量列表',
						content_func: showLocalVars,
						query_vars: "vc_varname, vc_value",
						query_tables: "local_var_tbl",
						query_conditions: "vc_module='" + obj.rec.vc_module + "' AND vc_account='" + obj.rec.vc_account + "'",
						query_order: ""
					});
				});
				c.appendChild(showvar_btn);
			}
		}
		];
		newDBGrid(records, headers, "", panel, conf);
	}
}

function addAccountValidationForm(form) {
	with(form) {
		if(_vc_module == null) {
			showTips(form, "请指定模块！");
			return false;
		}
		if(!checkDOMNonempty(_vc_module, "请指定模块！")) {
			return false;
		}
		if(!checkDOMUser(_vc_account, "请指定账号！", true)) {
			return false;
		}
		if(_vc_pin.value == '') {
			_ti_lockpin.checked = false;
		}
	}
	return true;
}

function accountMenuActions(conf, menuText, panel) {
	if(menuText == '添加') {
		addAccount(conf, panel);
	}else if(menuText == '从csv文件导入') {
		importAccount(conf, panel);
	}else if(menuText == '导出为csv文件') {
		exportAccount(conf, panel);
	}
}

function addAccount(conf, panel) {
	var rec = {
		vc_module: '',
		vc_account: '',
		vc_pin: '',
		ti_lockpin: '0'
	};
	editAccount(conf, panel, rec);
}

function editAccount(conf, panel, rec) {
	var form = newDefaultRPCForm(panel, 'EDIT_ACCOUNT', conf.name, addAccountValidationForm);
	var tbl = newTableElement('', 0, 0, 2, '', 5, 2, 'left', 'middle', form);
	tblCell(tbl, 0, 0).innerHTML = '模块：';
	tblCell(tbl, 1, 0).innerHTML = '账号：';
	tblCell(tbl, 2, 0).innerHTML = '设备ID（可选）：';
	tblCell(tbl, 3, 0).innerHTML = '账号和设备绑定：';

	if(rec.vc_module == '') {
		updateFieldContent(tblCell(tbl, 0, 1), 'module_select_field', 'GET_MODULE_LIST', {_sel: rec.vc_module}, onGetModuleList);

		tblCell(tbl, 1, 1).appendChild(newInputElement('text', '_vc_account', rec.vc_account));
	}else {
		form.appendChild(newInputElement('hidden', '_vc_module', rec.vc_module));
		form.appendChild(newInputElement('hidden', '_vc_account', rec.vc_account));
		tblCell(tbl, 0, 1).innerHTML = rec.vc_module;
		tblCell(tbl, 1, 1).innerHTML = rec.vc_account;
	}
	tblCell(tbl, 2, 1).appendChild(newInputElement('text', '_vc_pin', rec.vc_pin));
	var lockpin = newInputElement('checkbox', '_ti_lockpin', '1');
	tblCell(tbl, 3, 1).appendChild(lockpin);
	if(Number(rec.ti_lockpin) != 0) {
		lockpin.checked = true;
	}
	tblCell(tbl, 4, 1).appendChild(newInputElement('submit', '', '提交'));
	tblCell(tbl, 4, 1).appendChild(newCancelConfigButton(conf));

	commitUpdateFieldContent();
}

function onGetModuleList(c, r, params) {
	if(r._modules.length > 0) {
		var modules = r._modules;
		modules[modules.length] = '增加新模块...';
		var module_sel = newSelector(r._modules, params._sel, '_vc_module');
		module_sel.cell = c;
		module_sel.default_val = params._sel;
		module_sel.__destructor = function() {
			this.cell = null;
			this.default_val = null;
		};
		c.appendChild(module_sel);
		EventManager.Add(module_sel, 'change', function(ev, obj) {
			if(obj.selectedIndex == obj.options.length-1) {
				var cell = obj.cell;
				var defval = obj.default_val;
				removeAllChildNodes(cell);
				cell.appendChild(newInputElement('text', '_vc_module', defval));
			}
		});
	}else {
		c.appendChild(newInputElement('text', '_vc_module', params._sel));
	}
}

function importAccount(conf, panel) {
	var form = newDefaultUploadControl(panel, 'IMPORT_ACCOUNT_SETTINGS', '_account_list', 2048*1024, ['csv'], conf.name);
        form.appendChild(newParagraph('请选择导入的.csv文件。文件每一行格式为"<b>module_name, account, pin, bind_pin</b>"。其中，module_name为模块名字符串，account为账号字符串，pin为设备PIN码，bind_pin指示账号和设备是否绑定，值为"true"或"false"'));
	form.appendChild(newCancelConfigButton(conf));
}

function exportAccount(conf, panel) {
	sendURLRequest({
		url: LIBUI_REQUEST_HANDLER_SCRIPT,
		method: 'POST',
		target: '_new',
		params: {
			_action: 'EXPORT_ACCOUNT_SETTINGS'
		}
	});
	hideContentConfigPanel(conf);
}
