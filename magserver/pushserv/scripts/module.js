function showModules(records, panel, conf) {
	if(records.length == 0) {
		panel.innerHTML = "No modules!";
	}else {
		var headers = [
			{
				title: "",
				value: function(c, r) {
					if(Number(r.ti_lockpin) == 1) {
						c.innerHTML = "<img src='images/lockicon_small.png'>";
					}
				}
			},
			{
				title: "模块",
				value: function(c, r) {
					if(r.vc_module == '*') {
						c.innerHTML = '总计';
					}else {
						c.innerHTML = r.vc_module;
					}
				}
			},
			{
				title: "#账号数",
				field: "a.account_count",
				value: function(c, r) {
					c.align = 'right'
					var count = Number(r.account_count);
					if(r.vc_module != '*' && count > 0) {
						c.innerHTML = "<a href='" + location.pathname + "?_menu=ACCOUNT&_module=" + r.vc_module + "'>" + count + "</a>";
					}else if(r.vc_module == '*') {
						c.innerHTML = "<a href='" + location.pathname + "?_menu=ACCOUNT'>" + count + "</a>";
					}else {
						c.innerHTML = count;
					}
				}
			},
			{
				title: "#终端",
				field: "b.device_count",
				value: function(c, r) {
					c.align = 'right'
					var count = Number(r.device_count);
					if(r.vc_module != '*' && count > 0) {
						c.innerHTML = "<a href='" + location.pathname + "?_menu=DEVICE&_module=" + r.vc_module + "'>" + count + "</a>";
					}else if(r.vc_module == '*') {
						c.innerHTML = "<a href='" + location.pathname + "?_menu=DEVICE'>" + count + "</a>";
					}else {
						c.innerHTML = count;
					}
				}
			},
			{
				title: "",
				value: function(c, r) {
					if(r.vc_module != '*') {
						if(Number(r.account_count) > 0) {
							var show_btn = newInputElement('button', '', '查看账号');
							c.appendChild(show_btn);
							show_btn.rec = r;
							show_btn.__destructor = function() {
								this.rec = null;
							};
							EventManager.Add(show_btn, 'click', function(ev, obj) {
								document.location = location.pathname + "?_menu=ACCOUNT&_module=" + obj.rec.vc_module;
							}); 
						}
						if(Number(r.device_count) > 0) {
							var show_btn = newInputElement('button', '', '查看设备');
							c.appendChild(show_btn);
							show_btn.rec = r;
							show_btn.__destructor = function() {
								this.rec = null;
							};
							EventManager.Add(show_btn, 'click', function(ev, obj) {
								document.location = location.pathname + "?_menu=DEVICE&_module=" + obj.rec.vc_module;
							});
						}
					}
					var label = '查看账号配置';
					if(r.vc_module == '*') {
						label = '查看全局账号配置';
					}
					var config_btn = newInputElement('button', '', label);
					c.appendChild(config_btn);
					config_btn.rec = r;
					config_btn.conf = conf;
					config_btn.__destructor = function() {
						this.rec = null;	
						this.conf = null;
					};
					EventManager.Add(config_btn, 'click', function(ev, obj) {
						var frame = document.getElementById('_config_view_pane');
						removeAllChildNodes(frame);
						var cancel_btn = newInputElement('button', '', '取消');
						frame.appendChild(cancel_btn);
						EventManager.Add(cancel_btn, 'click', function(ev, obj) {
							var frame = document.getElementById('_config_view_pane');
							removeAllChildNodes(frame);
						});
						var div = document.createElement('div');
						div.id = '_config_view_div_pane';
						frame.appendChild(div);
						var module_name = obj.rec.vc_module;
						if(module_name == '*') {
							module_name = '全局';
						}
						makeContentPanel(div, {
							frame_scheme: 0,
							expand: 'none',
							params: obj.rec,
							onchange: onConfigChange,
							title: module_name + ' - 账号配置',
							content_func: showGlobalAccountSettings,
							query_vars: "*",
							query_tables: "account_tbl",
							query_conditions: "vc_module='" + obj.rec.vc_module + "' AND vc_account='*'",
							query_order: ""
                                   	     });
					});

				}
			}
		];
		newDBGrid(records, headers, "", panel, conf);
	}
}

function onConfigChange(conf) {
	redrawPanelContent('_module_list', true, false);
}
