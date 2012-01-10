function showCacheURLs(records, panel, conf) {
	if(records.length == 0) {
		panel.innerHTML = "No records!";
	}else {
		var headers = [
			{
				title: "#",
				value: function(c, r) {
					c.innerHTML = r.id;
				}
			},
			{
				title: "模块",
				field: 'a.vc_module',
				value: function(c, r) {
					c.innerHTML = r.vc_module; //"<a href='" + location.pathname + "?_menu=URLs&_module=" + r.vc_module + "'>" + r.vc_module + "</a>";
				}
			},
			{
				title: "账号",
				field: 'b.vc_account',
				value: function(c, r) {
					c.innerHTML = r.vc_account; //"<a href='" + location.pathname + "?_menu=URLs&_account=" + r.vc_account + "'>" + r.vc_account + "</a>";
				}
			},
			{
				title: "PIN",
				field: 'a.vc_pin',
				value: function(c, r) {
					c.innerHTML = r.vc_pin; //"<a href='" + location.pathname + "?_menu=URLs&_pin=" + r.vc_pin + "'>" + r.vc_pin + "</a>";
				}
			},
			{
				title: "订阅内容",
				field: 'a.vc_title',
				value: function(c, r) {
					c.innerHTML = r.vc_title + "(" + r.content_len + ")"; //"<a href='" + location.pathname + "?_menu=LOG&_cache_id=" + r.id + "'>" + r.vc_title + "(" + r.content_len + ")" + "</a>";
					c.title = r.vc_url;
				}
			},
			{
				title: "过期时间",
				field: 'a.dt_expire',
				value: function(c, r) {
					c.innerHTML = r.dt_expire;
				}
			},
			{
				title: "推送时间",
				field: 'a.dt_change',
				value: function(c, r) {
					c.innerHTML = r.dt_change;
				}
			},
			{
				title: "预计轮询时间",
				field: 'c.dt_deadline',
				value: function(c, r) {
					c.innerHTML = r.dt_deadline;
					if(Number(r.iu_tries) > 0) {
						c.title = '重试' + r.iu_tries + '次';
					}
					if(Number(r.tiu_state) > 0) {
						c.parentNode.bgColor='#888888';
					}else if(Number(r.over_due) > 0) {
						c.parentNode.bgColor='#FF8888';
					}
				}
			},
			{
				title: "",
				value: function(c, r) {
					var push_btn = newInputElement('button', '', '强制推送');
					push_btn.rec = r;
					push_btn.confname = conf.name;
					push_btn.__destruct = function() {
						this.rec = null;
						this.confname = null;
					};
					c.appendChild(push_btn);
					EventManager.Add(push_btn, 'click', function(e, obj) {
						ajaxRPC('forcePushURL', {_id: obj.rec.id, _confname: obj.confname}, onForcePushURLSuccess, onForcePushURLFail, true);
					});
					var show_btn = newInputElement('button', '', '查看日志');
					show_btn.rec = r;
					show_btn.__destruct = function() {
						this.rec = null;
					};
					c.appendChild(show_btn);
					EventManager.Add(show_btn, 'click', function(e, obj) {
						document.location = document.location.pathname + "?_menu=LOG&_cache_id=" + obj.rec.id;
					});
				}
			}
		];
		newDBGrid(records, headers, "", panel, conf);
	}
}

function onForcePushURLSuccess(obj) {
	//showAlert(obj.confname);
	redrawPanelContent('_subscrib_urls', false);
	showAsyncMsg('成功推送！');
}

function onForcePushURLFail(msg) {
	redrawPanelContent('_subscrib_urls', false);
	showAsyncMsg('推送失败:' + msg + '！');
}

function onAvancedSearchCacheLayout(panel) {
	var headers = [
	{
		title: '模块',
		value: function(c, r) {
			c.appendChild(newInputElement('text', 'a.vc_module', ''));
		}
	},
        {
                title: '账号',
                value: function(c, r) {
                        c.appendChild(newInputElement('text', 'b.vc_account', ''));
                }
        },
        {
                title: 'PIN',
                value: function(c, r) {
                        var pin_txt = newInputElement('text', 'a.vc_pin', '');
                        pin_txt.size = 8;
                        c.appendChild(pin_txt);
                }
        },
        {
                title: '订阅内容标题',
                value: function(c, r) {
                        c.appendChild(newInputElement('text', 'a.vc_title', ''));
                }
        },
        {
                title: '订阅内容URL',
                value: function(c, r) {
                        c.appendChild(newInputElement('text', 'a.vc_url', ''));
                }
        },
        {
                title: '',
                value: function(c, r) {
                }
        },
        {
                title: '订阅过期时间',
                value: function(c, r) {
                        var dt_tbl=newTableElement('', 0, 0, 0,'', 3, 1,'center','middle',c);
                        tblCell(dt_tbl,1,0).innerHTML='至';
                        newCalendarControl(tblCell(dt_tbl,0,0), 'a.dt_expire_min', '0000-00-00 00:00:00', 'datetime');
                        newCalendarControl(tblCell(dt_tbl,2,0), 'a.dt_expire_max', '0000-00-00 00:00:00', 'datetime');
                }
        },
	{
                title: '上次推送时间',
                value: function(c, r) {
                        var dt_tbl=newTableElement('', 0, 0, 0,'', 3, 1,'center','middle',c);
                        tblCell(dt_tbl,1,0).innerHTML='至';
                        newCalendarControl(tblCell(dt_tbl,0,0), 'a.dt_change_min', '0000-00-00 00:00:00', 'datetime');
                        newCalendarControl(tblCell(dt_tbl,2,0), 'a.dt_change_max', '0000-00-00 00:00:00', 'datetime');
                }
	},
	{
                title: '预计轮询时间',
                value: function(c, r) {
                        var dt_tbl=newTableElement('', 0, 0, 0,'', 3, 1,'center','middle',c);
                        tblCell(dt_tbl,1,0).innerHTML='至';
                        newCalendarControl(tblCell(dt_tbl,0,0), 'c.dt_deadline_min', '0000-00-00 00:00:00', 'datetime');
                        newCalendarControl(tblCell(dt_tbl,2,0), 'c.dt_deadline_max', '0000-00-00 00:00:00', 'datetime');
                }
	}
        ];
        showInfoDetail(panel, null, '精确搜索', headers, 3, '');
}
