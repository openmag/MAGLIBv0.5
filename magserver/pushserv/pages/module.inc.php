<table border=0 width= 100%><tr><td valign=top>
<?php

$title = "<b>模块列表</b>";
$config = null; #array('menus'=>array('添加'), 'func'=>'moduleMenuActions');

showPanel2(0, '_module_list', $title, PANEL_UNEXPANDABLE, $config, 'showModules',
array(
'query_vars'       => "a.vc_module, a.account_count, b.device_count, c.ti_lockpin",
'query_tables'     => "account_count_view a left join device_count_view b on a.vc_module=b.vc_module left join account_tbl c on a.vc_module=c.vc_module and c.vc_account='*'",
'query_conditions' => "",
'query_order'      => ""
),
null);

?></td>
<td id='_config_view_pane' valign=top></td></tr></table>
