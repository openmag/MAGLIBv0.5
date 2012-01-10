function showGlobalAccountSettings(records, panel, conf) {
	if(records.length == 0) {
		updateFieldContent(panel, '_global_account_settings_field', 'GET_GLOBAL_ACCOUNT_SETTINGS', {conf: conf, _module: conf.params.vc_module}, onGetGlobalAccountSettings);
		commitUpdateFieldContent();
		return;
	}
	showAccountSettings(records[0], conf, panel, true, false);
}

function onEditAccountSettingsValidate(form) {
	with(form) {
		if(!checkDOMInteger(_cache_default_expire, '请输入整数!', false)) {
			return false;
		}
		if(!checkDOMURL(_relay_server_uri, '请输入正确的URI!', false)) {
			return false;
		}
		if(!checkDOMURL(_service_uri, '请输入正确的URI!', false)) {
			return false;
		}
		if(!checkDOMURL(_attachment_service_uri, '请输入正确的URI!', false)) {
			return false;
		}
	}
	return true;
}

function showAccountSettings(rec, conf, panel, is_global, is_edit) {
	var tbl = newTableElement('', 0, 0, 2, '', 1, 2, 'left', 'middle', panel);
	if(is_edit) {
		var form = newDefaultRPCForm(tblCell(tbl, 0, 0), "EDIT_ACCOUNT_SETTINGS", conf.name, onEditAccountSettingsValidate);
		form.appendChild(newInputElement('hidden', '_vc_module', rec.vc_module));
		form.appendChild(newInputElement('hidden', '_vc_account', rec.vc_account));
	}else {
		var form = tblCell(tbl, 0, 0);
		var btn_area = tblCell(tbl, 0, 1);
	}
	if(!is_global) {
		form.appendChild(newParagraph('<b>' + rec.vc_module + '/' + rec.vc_account + '账号配置</b>'));
	}
	var tbl = newTableElement('', 0, 0, 2, '', 12, 2, 'left', 'middle', form);
	var row = 0;
	if(is_global) {
		tblCell(tbl, row, 0).innerHTML = "不在账号列表的账号登录系统：";
		if(is_edit) {
			var lockpin_sel_opt = {'0': '允许', '1': '禁止'};
			var lockpin_sel = newSelector(lockpin_sel_opt, rec.ti_lockpin, '_ti_lockpin');
			tblCell(tbl, row, 1).appendChild(lockpin_sel);
		}else {
			if(Number(rec.ti_lockpin) != 0) {
				tblCell(tbl, row, 1).innerHTML = "禁止";
			}else {
				tblCell(tbl, row, 1).innerHTML = "允许";
			}
		}
	}else {
		tblCell(tbl, row, 0).innerHTML = "账号和设备绑定：";
		if(is_edit) {
			var lockpin_sel_opt = {'0': '不绑定', '1': '绑定'};
			var lockpin_sel = newSelector(lockpin_sel_opt, rec.ti_lockpin, '_ti_lockpin');
			tblCell(tbl, row, 1).appendChild(lockpin_sel);
		}else {
			if(Number(rec.ti_lockpin) != 0) {
				tblCell(tbl, row, 1).innerHTML = "绑定";
			}else {
				tblCell(tbl, row, 1).innerHTML = "不绑定";
			}
		}
	}
	row++;
	var config = JSON.parse(rec.bl_config);
	tblCell(tbl, row, 0).innerHTML = "缓存功能：";
	if(is_edit) {
		if(typeof(config._cache_enabled) == 'undefined') {
			config._cache_enabled = '';
		}
		var cache_enabled_opt = {'true': '使能', 'false': '禁用', '': '不设置'};
		var cache_enabled_sel = newSelector(cache_enabled_opt, config._cache_enabled, '_cache_enabled');
		tblCell(tbl, row, 1).appendChild(cache_enabled_sel);
	}else {
		if(config._cache_enabled) {
			if(config._cache_enabled == 'true') {
				tblCell(tbl, row, 1).innerHTML = "使能";
			}else {
				tblCell(tbl, row, 1).innerHTML = "禁用";
			}
		}
	}
	row++;
	tblCell(tbl, row, 0).innerHTML = "缓存缺省过期时间(小时)：";
	if(is_edit) {
		var hours = '';
		if(typeof(config._cache_default_expire) != 'undefined') {
			hours = '' + config._cache_default_expire/3600/1000;
		}
		tblCell(tbl, row, 1).appendChild(newInputElement('text', '_cache_default_expire', hours));
	}else {
		if(typeof(config._cache_default_expire) != 'undefined') {
			var hours = config._cache_default_expire/1000/3600;
			tblCell(tbl, row, 1).innerHTML = '' + hours;
		}
	}
	row++;
	tblCell(tbl, row, 0).innerHTML = "中继功能：";
	if(is_edit) {
		if(typeof(config._relay_enabled) == 'undefined') {
			config._relay_enabled = '';
		}
		var relay_enabled_opt = {'true': '使能', 'false': '禁用', '': '不设置'};
                var relay_enabled_sel = newSelector(relay_enabled_opt, config._relay_enabled, '_relay_enabled');
                tblCell(tbl, row, 1).appendChild(relay_enabled_sel);
	}else {
		if(config._relay_enabled) {
			if(config._relay_enabled == 'true') {
			 	tblCell(tbl, row, 1).innerHTML = "使能";
			}else {
				tblCell(tbl, row, 1).innerHTML = "禁用";
			}
		}
	}
	row++;
	tblCell(tbl, row, 0).innerHTML = "中继服务URI：";
	if(is_edit) {
		if('undefined' == typeof(config._relay_server_uri)) {
			config._relay_server_uri = '';
		}
		tblCell(tbl, row, 1).appendChild(newInputElement('text', '_relay_server_uri', config._relay_server_uri));
	}else {
		if(config._relay_server_uri) {
			tblCell(tbl, row, 1).innerHTML = config._relay_server_uri;
		}
	}
	row++;
	tblCell(tbl, row, 0).innerHTML = "服务入口URI：";
	if(is_edit) {
		if('undefined' == typeof(config._service_uri)) {
			config._service_uri = '';
		}
		tblCell(tbl, row, 1).appendChild(newInputElement('text', '_service_uri', config._service_uri));
	}else {
		if(config._service_uri) {
			tblCell(tbl, row, 1).innerHTML = config._service_uri;
		}
	}
	row++;
	tblCell(tbl, row, 0).innerHTML = "密码保护功能：";
	if(is_edit) {
		if(typeof(config._password_protected) == 'undefined') {
			config._password_protected = '';
		}
		var password_protected_opt = {'true': '使能', 'false': '禁用', '': '不设置'};
                var password_protected_sel = newSelector(password_protected_opt, config._password_protected, '_password_protected');
                tblCell(tbl, row, 1).appendChild(password_protected_sel);
	}else {
		if(config._password_protected) {
			if(config._password_protected == "true") {
				tblCell(tbl, row, 1).innerHTML = "启用";
			}else {
				tblCell(tbl, row, 1).innerHTML = "禁用";
			}
		}
	}
	row++;
	tblCell(tbl, row, 0).innerHTML = "附件处理服务URI：";
	if(is_edit) {
		if('undefined' == typeof(config._attachment_service_uri)) {
			config._attachment_service_uri = '';
		}
		tblCell(tbl, row, 1).appendChild(newInputElement('text', '_attachment_service_uri', config._attachment_service_uri));
	}else {
		if(config._attachment_service_uri) {
			tblCell(tbl, row, 1).innerHTML = config._attachment_service_uri;
		}
	}
	row++;
	tblCell(tbl, row, 0).innerHTML = "推送协议：";
	if(is_edit) {
		if('undefined' == typeof(config._push_protocol)) {
			config._push_protocol = '';
		}
		var push_protocol_opt = {'MDS': 'MDS', 'AOG': 'AOG', 'PAG': 'PAG', 'APNs': 'APNs', '': '不设置'};
		var push_protocol_sel = newSelector(push_protocol_opt, config._push_protocol, '_push_protocol');
		tblCell(tbl, row, 1).appendChild(push_protocol_sel);
	}else {
		if(config._push_protocol) {
			tblCell(tbl, row, 1).innerHTML = config._push_protocol;
		}
	}
	row++;
	tblCell(tbl, row, 0).innerHTML = "推送服务器地址：";
	if(is_edit) {
		if('undefined' == typeof(config._push_server)) {
			config._push_server = '';
		}
		tblCell(tbl, row, 1).appendChild(newInputElement('text', '_push_server', config._push_server));
	}else {
		if(config._push_server) {
			tblCell(tbl, row, 1).innerHTML = config._push_server;
		}
	}
	row++;
	tblCell(tbl, row, 0).innerHTML = "HTTP请求超时(秒)：";
	if(is_edit) {
		var seconds = '';
		if(typeof(config._http_request_timeout) != 'undefined') {
			seconds = '' + config._http_request_timeout;
		}
		var to_opts = {'':'不设置', '30': '30', '60':'60', '90': '90', '120':'120', '150': '150', '180':'180'};
		var to_sel = newSelector(to_opts, seconds, '_http_request_timeout');
		tblCell(tbl, row, 1).appendChild(to_sel);
	}else {
		if(typeof(config._http_request_timeout) != 'undefined') {
			tblCell(tbl, row, 1).innerHTML = '' + config._http_request_timeout;
		}
	}
	row++;

	if(!is_edit) {
		btn_area.vAlign='bottom';
		var edit_btn = newInputElement('button', '', '修改');
		btn_area.appendChild(edit_btn);
		edit_btn.rec = rec;
		edit_btn.conf = conf;
		edit_btn.is_global = is_global;
		edit_btn.__destructor = function() {
			this.rec = null;
			this.conf = null;
			this.is_global = null;
		};
		EventManager.Add(edit_btn, 'click', function(ev, obj) {
			var pane = getInlineEditArea(obj);
			showAccountSettings(obj.rec, obj.conf, pane, obj.is_global, true);
		});
		if(!is_global) {
			var cancel_btn = newInputElement('button', '', '取消');
			btn_area.appendChild(cancel_btn);
			cancel_btn.conf = conf;
			cancel_btn.__destructor = function() {
				this.conf = null;
			};
			EventManager.Add(cancel_btn, 'click', function(ev, obj) {
				refreshPanelContent(obj.conf.name);
			});
		}
	}else {
		tblCell(tbl, row, 1).appendChild(newInputElement('submit', '', '保存'));
		tblCell(tbl, row, 1).appendChild(newCancelConfigButton(conf));
	}
}

function onGetGlobalAccountSettings(panel, data, params) {
	showAccountSettings(data._settings, params.conf, panel, true, false);
}
