<table border=0><tr>
<td valign='top'>
<?php

$config = array('menus'=>array('导入新表', '新增'), 'func'=>'addSyncTable');

showPanel2(0, '_sync_tables', '<b>同步数据表</b>', PANEL_UNEXPANDABLE, $config, 'showSyncTables', 
array(
'query_vars'       => 'a.id, a.vc_name, a.vc_timestamp, a.vc_dataurl, a.dt_firstsync, a.ui_updateintv, a.bl_description, a.dt_lastsync, a.ti_status, a.ti_enable, a.ti_isdirty, b.colcount, c.pricount, d.idxcount, e.next_sync',
'query_tables'     => 'syncdb_tbl a LEFT JOIN (SELECT tbl_id, count(*) AS colcount FROM syncdb_column_tbl GROUP BY tbl_id) AS b ON a.id=b.tbl_id LEFT JOIN (SELECT tbl_id, count(*) AS pricount FROM syncdb_column_tbl WHERE ti_isprimary=1 GROUP BY tbl_id) AS c ON a.id=c.tbl_id LEFT JOIN (SELECT tbl_id, count(*) AS idxcount FROM syncdb_index_tbl GROUP BY tbl_id) AS d ON a.id=d.tbl_id LEFT JOIN (SELECT id, next_sync FROM syncdb_view) AS e ON a.id=e.id',
'query_conditions' => '',
'query_order'      => ''
),
array('limit'=>25, 'position'=>PAGE_POSITION_BOTH));

?>
</td>
<td id='_sync_table_edit_pane' valign='top' align='left'>
</td></tr></table>
