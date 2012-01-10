function showPushLog(records, panel, conf) {
	if(records.length == 0) {
		panel.innerHTML = "No records!";
	}else {
		var headers = [
			{
				title: "#",
				value: function(c, r) {
					c.innerHTML = r.push_id;
				}
			},
			{
				title: "模块",
				field: 'b.vc_module',
				value: function(c, r) {
					c.innerHTML = r.vc_module;
				}
			},
			{
				title: "账号",
				field: 'c.vc_account',
				value: function(c, r) {
					c.innerHTML = r.vc_account;
				}
			},
			{
				title: "PIN",
				field: 'b.vc_pin',
				value: function(c, r) {
					c.innerHTML = r.vc_pin;
				}
			},
			{
				title: "订阅内容",
				field: 'b.vc_title',
				value: function(c, r) {
					c.innerHTML = r.vc_title + "(" + r.cache_id + ")";
					c.title = r.vc_url;
				}
			},
			{
				title: "推送原因",
				field: 'a.vc_reason',
				value: function(c, r) {
					c.innerHTML = getChangeReason(r.vc_reason);
				}
			},
			{
				title: "推送时间",
				field: 'a.dt_push',
				value: function(c, r) {
					c.innerHTML = r.dt_push;
				}
			},
			{
				title: "推送结果",
				field: 'a.itu_state',
				value: function(c, r) {
					var state = ['开始推送', '成功推送', '推送失败', '确认'];
					c.innerHTML = state[r.itu_state];
				}
			}
		];
		newDBGrid(records, headers, "", panel, conf);
		var clean_btn = newInputElement('button', '', '清除所有日志');
		panel.appendChild(clean_btn);
		EventManager.Add(clean_btn, 'click', function(ev, obj) {
			updatePanelContentRPC(conf.name, "CLEAN_PUSHLOG", {});
		});
	}
}

function getChangeReason(reason) {
	if(reason == 'nochange') {
		return '强制推送';
	}else if(reason == 'add') {
		return '新增内容';
	}else if(reason == 'delete') {
		return '减少内容';
	}else if(reason == 'move') {
		return '内容迁移';
	}else if(reason == 'mix') {
		return '复杂变化';
	}else {
		return '';
	}
}

function onAdvancedSearchPushLogLayout(panel) {
	var headers = [
	{
		title: '模块',
		value: function(c, r) {
			c.appendChild(newInputElement('text', 'b.vc_module', ''));
		}
	},
	{
		title: '账号',
		value: function(c, r) {
			c.appendChild(newInputElement('text', 'c.vc_account', ''));
		}
	},
	{
		title: 'PIN',
		value: function(c, r) {
			var pin_txt = newInputElement('text', 'b.vc_pin', '');
			pin_txt.size = 8;
			c.appendChild(pin_txt);
		}
	},
	{
		title: '订阅内容标题',
		value: function(c, r) {
			c.appendChild(newInputElement('text', 'b.vc_title', ''));
		}
	},
	{
		title: '订阅内容URL',
		value: function(c, r) {
			c.appendChild(newInputElement('text', 'b.vc_url', ''));
		}
	},
	{
		title: '推送结果',
		value: function(c, r) {
			var result_opt = [
				{txt: '', val: ''},
				{txt: '开始推送', val: '0'}, 
				{txt: '成功推送', val: '1'},
				{txt: '推送失败', val: '2'},
				{txt: '确认', val: '3'}
			];
			var result_sel = newSelector(result_opt, '', 'a.itu_state');
			c.appendChild(result_sel);
		}
	},
	{
		title: '推送时间',
		value: function(c, r) {
			var dt_tbl=newTableElement('', 0, 0, 0,'', 3, 1,'center','middle',c);
			tblCell(dt_tbl,1,0).innerHTML='至';
			newCalendarControl(tblCell(dt_tbl,0,0), 'a.dt_push_min', '0000-00-00 00:00:00', 'datetime');
			newCalendarControl(tblCell(dt_tbl,2,0), 'a.dt_push_max', '0000-00-00 00:00:00', 'datetime');
		}
	}
	];
	showInfoDetail(panel, null, '精确搜索', headers, 3, '');
}

function onPreciseSearchCallback(conf, form, match_all) {
	var cat_str = " OR ";
	if(match_all) {
		cat_str = " AND ";
	}
	var search_str = '';
	for(var i = 0; i < form.params.length; i ++) {
		if('undefined' !== typeof(form[form.params[i]])) {
			var val = trimString(form[form.params[i]].value);
			if(val != '') {
				if(search_str != '') {
					search_str += cat_str;
				}
				search_str += form.params[i] + ' REGEXP \'' + val + '\'';
                        }
			form[form.params[i]].value = val;
		}else if('undefined' !== typeof(form[form.params[i] + '_max'])) {
			var val_min = trimString(form[form.params[i] + '_min'].value);
			var val_max = trimString(form[form.params[i] + '_max'].value);
			if(val_min != '') {
				if(isDateStr(val_min)) {
					if(val_min.indexOf('0000-00-00') < 0) {
						if(search_str != '') {
							search_str += cat_str;
						}
						search_str += form.params[i] + '>=\'' + val_min + '\'';
					}
				}else {
					val_min = Number(val_min);
					form[form.params[i] + '_min'].value = val_min;
					if(val_min != 0) {
						if(search_str != '') {
							search_str += cat_str;
						}
						search_str += form.params[i] + '>=' + val_min;
					}
				}
			}
			if(val_max != '') {
				if(isDateStr(val_max)) {
					if(val_max.indexOf('0000-00-00') < 0) {
						if(search_str != '') {
							search_str += cat_str;
						}
						search_str += form.params[i] + '<=\'' + val_max + '\'';
					}
				}else {
					val_max = Number(val_max);
					form[form.params[i] + '_max'].value = val_max;
					if(val_max != 0) {
						if(search_str != '') {
							search_str += cat_str;
						}
						search_str += form.params[i] + '<=' + val_max;
					}
				}
			}
		}
	}
	if(search_str == '') {
		return false;
	}else {
		conf.query_conditions = search_str;
		return true;
	}
}
