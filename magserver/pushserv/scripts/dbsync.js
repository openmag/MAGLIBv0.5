var __datatype_opt = ['VARCHAR', 'INT', 'REAL', 'DATE', 'DATETIME', 'BLOB'];

var __current_table_id = -1;

var __current_table_name = null;

var __current_table_columns = null;

function getDataTypString(type, dt_param, isnull, isprimary) {
	var str = type;
	if((type == 'VARCHAR' || type == 'INT' || type == 'REAL') && dt_param != '') {
		str += '(' + dt_param + ')';
	}
	if(Number(isnull) !== 1) {
		str += ' NOT NULL';
	}
	if(Number(isprimary) === 1) {
		str += ' PRIMARY KEY';
	}
	return str;
}

function addSyncTableIndexes(conf, menuText, panel) {
	if(__current_table_columns.length == 0) {
		panel.innerHTML = '表还没有数据域！';
		return;
	}
	var action = 'ADD_SYNC_TABLE_INDEX';
	var form = newDefaultRPCForm(panel, action, conf.name);
	form.appendChild(newInputElement('hidden', '_tbl_id', __current_table_id));
	var tbl = newTableElement('', 0, 0, 2, '', __current_table_columns.length+1, 2, 'left', 'top', form);
	for(var i = 0; i < __current_table_columns.length; i ++) {
		var chkbox = newInputElement('checkbox', '_column[]', __current_table_columns[i].vc_name);
		tblCell(tbl, i, 0).appendChild(chkbox);
		tblCell(tbl, i, 1).innerHTML = __current_table_columns[i].vc_name;
	}
	tblCell(tbl, __current_table_columns.length, 1).appendChild(newInputElement('submit', '', '创建'));
	tblCell(tbl, __current_table_columns.length, 1).appendChild(newCancelConfigButton(conf));
}

function showSyncTableVersions(records, panel, conf) {
	if(records.length == 0) {
		panel.innerHTML = '没有版本！';
	}else {
		var headers = [
			{
				title: "版本号",
				value: function(c, r) {
					c.innerHTML = r.version;
				}
			},
			{
				title: "创建时间",
				value: function(c, r) {
					c.innerHTML = r.created;
				}
			},
			{
				title: "状态",
				value: function(c, r) {
					var state = ['初始化', '同步成功', '同步无变化'];
					c.innerHTML = state[r.state];
				}
			},
			{
				title: "大小",
				value: function(c, r) {
					updateFieldContent(c, 'sync_table_' + __current_table_name + '_size_' + r.version, 'GET_SYNC_TABLE_VERSION_SIZE', {_table: __current_table_name, _version: r.version}, onGetSyncTableVersionSize);
				}
                        },
			{
				title: '',
				value: function(c, r) {
					var del_btn = newInputElement('button', '', '删除该版本');
					c.appendChild(del_btn);
					del_btn.conf = conf;
					del_btn.rec = r;
					del_btn.__destructor = function() {
						this.conf = null;
						this.rec  = null;
					};
					EventManager.Add(del_btn, 'click', function(ev, obj) {
						showConfirm({
							msg: '是否确定删除?',
							onOK: function() { updatePanelContentRPC(obj.conf.name, 'DROP_SYNC_TABLE_OF_VERSION', {_table: __current_table_name, _version: obj.rec.version}); return true;},
							onCancel: function() {return true;}
						});
					});

					var del_all_btn = newInputElement('button', '', '删除以前版本');
					c.appendChild(del_all_btn);
					del_all_btn.conf = conf;
					del_all_btn.rec = r;
					del_all_btn.__destructor = function() {
						this.conf = null;
						this.rec  = null;
					};
					EventManager.Add(del_all_btn, 'click', function(ev, obj) {
						showConfirm({
							msg: '是否确定删除所有以前版本数据?',
							onOK: function() { updatePanelContentRPC(obj.conf.name, 'DROP_SYNC_TABLES_OF_PREV_VERSION', {_table: __current_table_name, _id: __current_table_id, _version: obj.rec.version}); return true;},
							onCancel: function() {return true;}
						});
					});
				}
			}

		];
		newDBGrid(records, headers, "100%", panel);
		commitUpdateFieldContent();
	}
}

function showSyncTableIndexes(records, panel, conf) {
	if(records.length == 0) {
		panel.innerHTML = '没有数据！';
		return;
	}
	var headers = [
		{
			title: "包含列",
			value: function(c, r) {
				c.innerHTML = r.vc_colnames;
			}
		},
		{
			title: "",
			value: function(c, r) {
				var del_btn = newInputElement('button', '', '删除');
				c.appendChild(del_btn);
				del_btn.conf = conf;
				del_btn.rec = r;
				del_btn.__destructor = function() {
					this.conf = null;
					this.rec  = null;
				};
				EventManager.Add(del_btn, 'click', function(ev, obj) {
					showConfirm({
						msg: '是否确定删除?',
						onOK: function() { updatePanelContentRPC(obj.conf.name, 'DELETE_SYNC_TABLE_INDEX', {_tbl_id:obj.rec.tbl_id, _id: obj.rec.id}); return true;},
						onCancel: function() {return true;}
					});
				});
			}
		}
	];
	newDBGrid(records, headers, "100%", panel);
	redrawPanelContent('_sync_tables', true, false);
}

function showSyncTableColumns(records, panel, conf) {
	if(records.length == 0) {
		panel.innerHTML = '没有数据！';
		return;
	}
	__current_table_columns = records;
	var headers = [
		{
			title: "列名",
			value: function(c, r) {
				c.innerHTML = r.vc_name;
			}
		},
		{
			title: "数据类型",
			value: function(c, r) {
				c.innerHTML = getDataTypString(r.vc_datatype, r.vc_dtparam, r.ti_isnull, r.ti_isprimary);
			}
		},
		{
			title: "",
			value: function(c, r) {
				/*var edit_btn = newInputElement('button', '', '修改');
				c.appendChild(edit_btn);
				edit_btn.conf = conf;
				edit_btn.rec = r;
				edit_btn.__destructor = function() {
					this.conf = null;
					this.rec  = null;
				};
				EventManager.Add(edit_btn, 'click', function(ev, obj) {
					var config_div = getInlineEditArea(c);
					editSyncTableColumn(obj.conf, config_div, obj.rec);
				});*/

				var del_btn = newInputElement('button', '', '删除');
				c.appendChild(del_btn);
				del_btn.conf = conf;
				del_btn.rec = r;
				del_btn.__destructor = function() {
					this.conf = null;
					this.rec  = null;
				};
				EventManager.Add(del_btn, 'click', function(ev, obj) {
					showConfirm({
						msg: '是否确定删除?',
						onOK: function() { updatePanelContentRPC(obj.conf.name, 'DELETE_SYNC_TABLE_COLUMN', {_tbl_id: obj.rec.tbl_id, _vc_name: obj.rec.vc_name}); return true;},
						onCancel: function() {return true;}
					});
				});
			}
		}
	];
	newDBGrid(records, headers, "", panel);
	redrawPanelContent('_sync_tables', true, false);
}

function addSyncTableColumn(conf, menuText, panel) {
	editSyncTableColumn(conf, panel);
}

function onSyncTableColumnSettingValidate(form) {
	with(form) {
		if(!checkDOM(_vc_name, /[a-zA-Z0-9_]{1,64}/, '请输入正确数据表列名', true)) {
			return false;
		}
		if(_vc_datatype.options[_vc_datatype.selectedIndex].value == 'VARCHAR') {
			if(!checkDOMNonempty(_vc_dtparam, '请输入VARCHAR位数!')) {
				return false;
			}
		}
	}
	return true;
}

function editSyncTableColumn(conf, panel, rec) {
	var action = 'UPDATE_SYNC_TABLE_COLUMN';
	if('undefined' === typeof(rec)) {
		action = 'ADD_SYNC_TABLE_COLUMN';
		rec = {vc_name: '', vc_datatype: '', vc_dtparam: '', ti_isnull: 1, ti_isprimary: 0};
	}
	var form = newDefaultRPCForm(panel, action, conf.name, onSyncTableColumnSettingValidate);
	form.appendChild(newInputElement('hidden', '_tbl_id', __current_table_id));
	form.appendChild(newInputElement('hidden', '_ti_isnull', '1'));
	var tbl = newTableElement('', 0, 0, 2, '', 6, 2, 'left', 'top', form);
	tblCell(tbl, 0, 0).innerHTML = '列名：';
	if(action == 'UPDATE_SYNC_TABLE_COLUMN') {
		form.appendChild(newInputElement('hidden', '_vc_name', rec.vc_name));
		tblCell(tbl, 0, 1).innerHTML = rec.vc_name;
	}else {
		tblCell(tbl, 0, 1).appendChild(newInputElement('text', '_vc_name', rec.vc_name));
	}
	tblCell(tbl, 1, 0).innerHTML = '数据类型：';
	var datatype_sel = newSelector(__datatype_opt, rec.vc_datatype, '_vc_datatype');
	tblCell(tbl, 1, 1).appendChild(datatype_sel);
	tblCell(tbl, 2, 0).innerHTML = '数据类型参数：';
	tblCell(tbl, 2, 1).appendChild(newInputElement('text', '_vc_dtparam', rec.vc_dtparam));
	//tblCell(tbl, 3, 0).innerHTML = '是否允许空值：';
	//var isnull_chkbox = newInputElement('checkbox', '_ti_isnull', '1');
	//tblCell(tbl, 3, 1).appendChild(isnull_chkbox);
	//if(Number(rec.ti_isnull) === 1) {
	//	isnull_chkbox.checked = true;
	//}
	tblCell(tbl, 4, 0).innerHTML = '是否主键：';
	var isprimary_chkbox = newInputElement('checkbox', '_ti_isprimary', '1');
	tblCell(tbl, 4, 1).appendChild(isprimary_chkbox);
	if(Number(rec.ti_isprimary) === 1) {
		isprimary_chkbox.checked = true;
	}
	tblCell(tbl, 5, 1).appendChild(newInputElement('submit', '', '保存'));
	tblCell(tbl, 5, 1).appendChild(newCancelConfigButton(conf));
}

function getSyncStatusString(code) {
	if(code === null) {
		return '未初始化';
	}else if(code == 0) {
		return '正在同步';
	}else if(code == 1) {
		return '同步成功';
	}else if(code == 2) {
		return '同步无变化';
	}else if(code == 3) {
		return '同步失败';
	}else {
		return '未知';
	}
}

function showSyncTables(records, panel, conf) {
	if(records.length == 0) {
		panel.innerHTML = "No records!";
	}else {
		var headers = [
			{
				title: "数据表名",
				value: function(c, r) {
					c.innerHTML= r.vc_name;
					if(Number(r.ti_enable) == 1) {
						c.parentNode.style.backgroundColor = '#FFFFFF';
					}else {
						c.parentNode.style.backgroundColor = '#BBBBBB';
					}
				}
			},
			/*{
				title: "数据源",
				value: function(c, r) {
					c.innerHTML = r.vc_dataurl;
				}
			},*/
			/*{
				title: "时间戳",
				value: function(c, r) {
					c.innerHTML = r.vc_timestamp;
				}
			},*/
			{
				title: "更新周期",
				value: function(c, r) {
					c.innerHTML = r.ui_updateintv;
				}
			},
			{
				title: "上次同步时间",
				value: function(c, r) {
					c.innerHTML = r.dt_lastsync;
				}
			},
			{
				title: "下次自动同步时间",
				value: function(c, r) {
					if(r.next_sync == null) {
						c.innerHTML = '未启动自动同步';
					}else {
						c.innerHTML = r.next_sync;
					}
				}
			},
			{
				title: "同步状态",
				value: function(c, r) {
					c.innerHTML = getSyncStatusString(r.ti_status);
				}
			},
			{
				title: "#表列",
				value: function(c, r) {
					c.innerHTML = Number(r.colcount) + '(' + Number(r.pricount) + '/' + Number(r.idxcount) + ')';
				}
			},
			{
				title: "其他信息",
				value: function(c, r) {
					updateFieldContent(c, 'sync_table_version_' + r.vc_name, 'GET_SYNC_TABLE_VERSION', {_vc_name: r.vc_name}, onGetSyncTableVersion);
				}
			},
			{
				title: '',
				value: function(c, r) {
					if(Number(r.ti_enable) == 1) {
						var disable_btn = newInputElement('button', '', '停止自动同步');
						c.appendChild(disable_btn);
						disable_btn.conf = conf;
						disable_btn.rec = r;
						disable_btn.__destructor = function() {
							this.conf = null;
							this.rec  = null;
						};
						EventManager.Add(disable_btn, 'click', function(ev, obj) {
							updatePanelContentRPC(obj.conf.name, 'DISABLE_SYNC_TABLE', {_id: obj.rec.id});
						});
					}else {
						var edit_btn = newInputElement('button', '', '修改');
						c.appendChild(edit_btn);
						edit_btn.conf = conf;
						edit_btn.rec = r;
						edit_btn.__destructor = function() {
							this.conf = null;
							this.rec  = null;
						};
						EventManager.Add(edit_btn, 'click', function(ev, obj) {
							var config_div = getInlineEditArea(c);
							editSyncTable(obj.conf, config_div, obj.rec);
						});

						var modify_btn = newInputElement('button', '', '编辑表');
						c.appendChild(modify_btn);
						modify_btn.conf = conf;
						modify_btn.rec = r;
						modify_btn.__destructor = function() {
							this.conf = null;
							this.rec  = null;
						};
						EventManager.Add(modify_btn, 'click', function(ev, obj) {
							var frame = getTableEditFrame(obj.rec.id, obj.rec.vc_name);
							var div = document.createElement('div');
							div.id = '_syncdb_detailed_config';
							frame.appendChild(div);
							makeContentPanel(div, {
								frame_scheme: 0,
								expand: 'none',
								title: '表格' + obj.rec.vc_name + '列设置',
								content_func: showSyncTableColumns,
								menus: ['新增'],
								config_func: addSyncTableColumn,
								query_vars: "tbl_id, vc_name, vc_datatype, vc_dtparam, ti_isnull, ti_isprimary",
								query_tables: "syncdb_column_tbl",
								query_conditions: "tbl_id=" + obj.rec.id,
								query_order: "dt_whenadd"
							});
							var div = document.createElement('div');
							div.id = '_syncdb_index_config';
							frame.appendChild(div);
							makeContentPanel(div, {
								frame_scheme: 0,
								expand: 'none',
								title: '表格' + obj.rec.vc_name + '索引设置',
								content_func: showSyncTableIndexes,
								menus: ['新增'],
								config_func: addSyncTableIndexes,
								query_vars: "id, tbl_id, vc_colnames",
								query_tables: "syncdb_index_tbl",
								query_conditions: "tbl_id=" + obj.rec.id,
								query_order: "dt_whenadd"
							})
						});

						var del_btn = newInputElement('button', '', '删除');
						c.appendChild(del_btn);
						del_btn.conf = conf;
						del_btn.rec = r;
						del_btn.__destructor = function() {
							this.conf = null;
							this.rec  = null;
						};
						EventManager.Add(del_btn, 'click', function(ev, obj) {
							showConfirm({
								msg: '是否确定删除?',
								onOK: function() { updatePanelContentRPC(obj.conf.name, 'DELETE_SYNC_TABLE', {_id: obj.rec.id, _vc_name: obj.rec.vc_name}, onDeleteSyncTableSuccess, true); return true;},
								onCancel: function() {return true;}
							});
						});


						if(Number(r.colcount) > 0 && Number(r.pricount) > 0) {
							if(Number(r.ti_isdirty) == 0 || r.vc_timestamp == null || r.vc_timestamp.length == 0) {
								var enable_btn = newInputElement('button', '', '开启自动同步');
								c.appendChild(enable_btn);
								enable_btn.conf = conf;
								enable_btn.rec = r;
								enable_btn.__destructor = function() {
									this.conf = null;
									this.rec  = null;
								};
								EventManager.Add(enable_btn, 'click', function(ev, obj) {
									updatePanelContentRPC(obj.conf.name, 'ENABLE_SYNC_TABLE', {_id: obj.rec.id}, onEnableSyncTableSucc, true);
								});

								var sync_btn = newInputElement('button', '', '手动同步');
								c.appendChild(sync_btn);
								sync_btn.conf = conf;
								sync_btn.rec = r;
								sync_btn.__destructor = function() {
									this.conf = null;
									this.rec  = null;
								};
								EventManager.Add(sync_btn, 'click', function(ev, obj) {
									showConfirm({
										msg: '是否进行手动同步?',
										onOK: function() { updatePanelContentRPC(obj.conf.name, 'MANUAL_SYNC_TABLE', {_vc_name: obj.rec.vc_name}, onManualSyncTableSucc, true); return true;},
										onCancel: function() {return true;}
									});
								});
							}

							if(r.vc_timestamp != null && r.vc_timestamp.length > 0) {
								var rebuild_btn = newInputElement('button', '', '重建表');
								c.appendChild(rebuild_btn);
								rebuild_btn.conf = conf;
								rebuild_btn.rec = r;
								rebuild_btn.__destructor = function() {
									this.conf = null;
									this.rec  = null;
								};
								EventManager.Add(rebuild_btn, 'click', function(ev, obj) {
									showConfirm({
										msg: '是否重新建表?',
										onOK: function() { updatePanelContentRPC(obj.conf.name, 'REBUILD_SYNC_TABLE', {_vc_name: obj.rec.vc_name}, onRebuildSyncTableSucc, true); return true;},
										onCancel: function() {return true;}
									});
								});
							}
						}else {
							if(Number(r.colcount) == 0) {
								msg = '还未定义数据表列！';
							}else if(Number(r.pricount) == 0) {
								msg = '表没有主键！';
							}
							c.appendChild(newTextNode(msg));
						}
					}
					var export_btn = newInputElement('button', '', '导出');
					c.appendChild(export_btn);
					export_btn.conf = conf;
					export_btn.rec = r;
					export_btn.__destructor = function() {
						this.conf = null;
						this.rec  = null;
					};
					EventManager.Add(export_btn, 'click', function(ev, obj) {
						sendURLRequest({
							url: LIBUI_REQUEST_HANDLER_SCRIPT,
							method: 'POST',
							target: '_new',
							params: {
								_action: 'EXPORT_TABLE_DEFINITION',
								_id: obj.rec.id
							}
						});
					});

					var version_btn = newInputElement('button', '', '查看版本');
					c.appendChild(version_btn);
					version_btn.conf = conf;
					version_btn.rec = r;
					version_btn.__destructor = function() {
						this.conf = null;
						this.rec  = null;
					};
					EventManager.Add(version_btn, 'click', function(ev, obj) {
						var frame = getTableEditFrame(obj.rec.id, obj.rec.vc_name);
						var div = document.createElement('div');
						div.id = '_syncdb_version_list_';
						frame.appendChild(div);
						makeContentPanel(div, {
							frame_scheme: 0,
							expand: 'none',
							title: '表格' + obj.rec.vc_name + '版本列表',
							content_func: showSyncTableVersions,
							query_vars: "version, created, state",
							query_tables: "__syncdb_" + obj.rec.vc_name + "_version",
							query_conditions: "",
							query_order: "created desc"
						});
					});
				}
			}
		];
		newDBGrid(records, headers, "", panel);
		commitUpdateFieldContent();
	}
}

function onManualSyncTableSucc(conf, msg) {
	showAsyncMsg('同步成功！');
}

function onRebuildSyncTableSucc(conf, msg) {
	showAsyncMsg('重建表成功！');
}

function getTableEditFrame(table_id, table_name) {
	__current_table_id = table_id;
	__current_table_name = table_name;
	var frame = document.getElementById('_sync_table_edit_pane');
	removeAllChildNodes(frame);
	var cancel_btn = newInputElement('button', '', '取消');
	frame.appendChild(cancel_btn);
	EventManager.Add(cancel_btn, 'click', function(ev, obj) {
		var frame = document.getElementById('_sync_table_edit_pane');
		removeAllChildNodes(frame);
	});
	return frame;
}

function onGetSyncTableVersion(c, r, params) {
        c.innerHTML = "ver:" + r._version + " row:" + r._row_count;
}

function onGetSyncTableVersionSize(c, r, params) {
	c.innerHTML = r._row_count;
}

function onDeleteSyncTableSuccess(conf, msg) {
	var frame = document.getElementById('_sync_table_edit_pane');
	removeAllChildNodes(frame);
}

function onEnableSyncTableSucc(conf, msg) {
        var frame = document.getElementById('_sync_table_edit_pane');
        removeAllChildNodes(frame);
}

function onSyncTableSettingValidate(form) {
	with(form) {
		if(!checkDOM(_vc_name, /[a-zA-Z0-9_]{5,64}/, '请输入正确数据表名', true)) {
			return false;
		}
		if(!checkDOMURL(_vc_dataurl, '请输入正确的数据源URL', true)) {
			return false;
		}
	}
	return true;
}

function addSyncTable(conf, menuText, panel) {
	if(menuText == '新增') {
		editSyncTable(conf, panel);
	}else {
		showImportTableDefinitionPanel(conf, panel);
	}
}

function showImportTableDefinitionPanel(conf, panel) {
	var form = newDefaultUploadControl(panel, 'IMPORT_TABLE_DEFINITION', '_table_definition', 1024*1024, ['dat'], conf.name);
	form.appendChild(newParagraph('请选择导出的.dat文件上传！'));
}

function editSyncTable(conf, panel, rec) {
	var action = 'UPDATE_SYNC_TABLE';
	if('undefined' === typeof(rec)) {
		rec = {'vc_name': '', 'ui_updateintv': 3600, 'bl_description': '', 'vc_dataurl': '', 'dt_firstsync': ''};
		action = 'ADD_SYNC_TABLE';
	}
	var form = newDefaultRPCForm(panel, action, conf.name, onSyncTableSettingValidate);
	var tbl = newTableElement('', 0, 0, 2, '', 6, 2, 'left', 'top', form);
	tblCell(tbl, 0, 0).innerHTML = '数据表名：';
	if(action == 'UPDATE_SYNC_TABLE') {
		form.appendChild(newInputElement('hidden', '_id', rec.id));
		form.appendChild(newInputElement('hidden', '_vc_name', rec.vc_name));
		tblCell(tbl, 0, 1).innerHTML = rec.vc_name;
	}else {
		var tblname_txt = newInputElement('text', '_vc_name', rec.vc_name);
		tblCell(tbl, 0, 1).appendChild(tblname_txt);
	}
	tblCell(tbl, 1, 0).innerHTML = '数据源URL：';
	var url_txt = newInputElement('text', '_vc_dataurl', rec.vc_dataurl);
	url_txt.size = 45;
	tblCell(tbl, 1, 1).appendChild(url_txt);
	tblCell(tbl, 2, 0).innerHTML = '初次更新时间：';
	newCalendarControl(tblCell(tbl, 2, 1), '_dt_firstsync', rec.dt_firstsync, 'datetime', false);
	tblCell(tbl, 3, 0).innerHTML = '更新间隔：';
	var intvl_sel_opt = [{'val': 300, 'txt': '5分钟'}, {'val': 3600, 'txt': '1小时'}, {'val': (3600*3), 'txt': '3小时'}, {'val': (3600*6), 'txt': '6小时'}, {'val': (3600*12), 'txt': '12小时'}, {'txt': '24小时', 'val': 3600*24}, {'txt': '2天', 'val': 3600*24*2}, {'txt': '3天', 'val': 3600*24*3}, {'txt': '1周', 'val': 3600*24*7}];
	var intvl_sel = newSelector(intvl_sel_opt, rec.ui_updateintv, '_ui_updateintv');
	tblCell(tbl, 3, 1).appendChild(intvl_sel);
	tblCell(tbl, 4, 0).innerHTML = '描述：';
	tblCell(tbl, 4, 1).appendChild(newTextArea('_bl_description', rec.bl_description, 40, 3));
        tblCell(tbl, 5, 1).appendChild(newInputElement('submit', '', '保存'));
        tblCell(tbl, 5, 1).appendChild(newCancelConfigButton(conf));
}
