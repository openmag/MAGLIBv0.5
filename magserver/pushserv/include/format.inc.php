<?php

$_menu_config = array(
	"MODULE" => array("TITLE" => "模块列表", "MENU" => "模块", "PAGE" => "module.inc.php"), 
	"ACCOUNT" => array("TITLE" => "账号列表", "MENU" => "账号", "PAGE" => "account.inc.php"), 
	"DEVICE" => array("TITLE" => "终端列表", "MENU" => "终端", "PAGE" => "device.inc.php"), 
	"URLs" => array("TITLE" => "订阅列表", "MENU" => "订阅","PAGE" => "cache.inc.php"), 
	"LOG" => array("TITLE" => "推送日志", "MENU" => "日志","PAGE" => "pushlog.inc.php"), 
	"SETUP" => array("TITLE" => "配置", "MENU" => "配置", "PAGE" => "setup.inc.php"), 
	"DBSYNC" => array("TITLE" => "数据库同步", "MENU" => "同步", "PAGE" => "dbsync.inc.php"), 
	"USER" => array("TITLE" => "管理账号", "MENU" => "","PAGE" => "user.inc.php")
);

$_default_menu = "DEVICE";
if (array_key_exists('_menu', $_REQUEST))
{
    $_current_menu = $_REQUEST["_menu"];
}
else
{
    $_current_menu = '';
}
if (empty($_current_menu))
{
    $_current_menu = $_default_menu;
}

function get_menu($mid, $txt)
{
    global $_menu_config, $_default_menu, $_current_menu;
    if ($_current_menu == $mid)
    {
        return $txt;
    }
    else
    {
        return "<a href='{$_SERVER["PHP_SELF"]}?_menu={$mid}' style='color:#FFF;'>{$txt}</a>";
    }
}

function print_title_old()
{
    global $_menu_config, $_menu;

    $title = $_menu_config[$_menu]["TITLE"];

    print_header("MAG Administration Platform - {$title}");
    echo "<table border=0 width='100%'>";
    echo "<tr><td style='border-bottom: 1px solid #000000;'>";
    echo "<ul class='menu_list'>";
    echo "<li><a href='logout.php'>退出</a></li>";
    echo "<li>" . get_menu("USER", "管理账号") . "</li>";
    echo "</ul>";
    echo "</td></tr>";
    echo "<tr><td align='center'><h1>MAG网关Push Engine管理与配置</h1></td></tr>";
    echo "<tr><td align='left'><table border=0><tr><td align=left><table border=0 cellspacing=5><tr>";
    foreach ($_menu_config as $key => $val)
    {
        echo "<td align=center>";
        if (!empty($val["MENU"]))
        {
            echo get_menu($key, $val["MENU"]);
        }
        echo "</td>";
    }
    echo "</tr></table></td>";
    echo "<td align=right></td>";
    echo "</tr></table></td></tr>";
    echo "</table>";
}

function print_title()
{
    global $_menu_config, $_menu, $_default_menu, $_current_menu;

    $title = $_menu_config[$_menu]["TITLE"];
    
    print_header("MAG Administration Platform - {$title}");
    $custom_setting = get_menu("USER", "管理账号");
    $system_time = time();
    $title_content_top = <<<TITLE_CONTENT_TOP
    <table width='100%' border='0' align='center' cellpadding='0' cellspacing='0' >
      <tr>
        <td height='86' valign='top' background='images/top_bg.jpg'>
        	<table width='96%' border='0' align='center' cellpadding='0' cellspacing='0'>
        		<tr>
        			<td width='85%'><img src='images/logo.jpg' width='447' height='86' /></td>
            	    <td width='15%' valign='top'>
            		<table border='0' align='right' cellpadding='0' cellspacing='0'>
              		<tr>
                		<td width='66' height='25' align='center' background='images/top_bg_right.jpg' style='color:#FFF;font-size:12px'>$custom_setting</td>
                		<td width='10'>&nbsp;</td>
                		<td width='66' align='center' background='images/top_bg_right.jpg'>
                			<a href='logout.php' style='color:#FFF;font-size:12px'>退出</a>
                		</td>
              		</tr>
			<tr><td align='right' colspan=3>
			<div id='_system_time_tray' style='padding-top: 10px; color:#888888;font-size:8pt;'>$system_time</div>
			</td></tr>
            		</table>
            	</td>
          	</tr>
        	</table>
        </td>
      </tr>
      <tr>
        <td height='40' valign='top' background='images/nav_bg.jpg'>
        	<table width='96%' border='0' align='center' cellpadding='0' cellspacing='0'>
    	      <tr>
    	        <td>
    	        	<table height='40' border='0' cellspacing='0' cellpadding='0'>
    	          	<tr>
TITLE_CONTENT_TOP;
    $title_content_bottom = <<<TITLE_CONTENT_BOTTOM
    </tr>
    	        	</table>
    	        </td>
    	      </tr>
        	</table>
        </td>
      </tr>
    </table>
TITLE_CONTENT_BOTTOM;

    echo $title_content_top;
    foreach ($_menu_config as $key => $val)
    {
        if (!empty($val["MENU"]))
        {
            $menubar = get_menu($key, $val["MENU"]);
            
            $tbl_cell = "<td width=\"68\" align=\"center\" background=\"images/nav_title_bg.jpg\" class=\"menu_bar_n\"><strong><font color='#239809'>$menubar</strong></td>";
            if ($_current_menu != $key)
            {
                $tbl_cell = "<td width=\"68\" align=\"center\" class=\"menu_bar_n\">$menubar</td>";
            }
            
            echo $tbl_cell;
        }
    }
    echo $title_content_bottom;
}

function print_footnote()
{
	$footnote = "<table id=\"footer\" width=\"565\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\"><tr><td height=\"80\" align=\"center\"><p><a href=\"http://www.anhe-inno.com\">安和创新科技（北京）有限公司</a>&nbsp;&nbsp;版权所有（".MAG_REVERSION_DATE."） 版本: ".MAG_VERSION."</p></td></tr></table>";
	echo $footnote;
	//echo "<hr><p style='font-size:12px;'><a href='http://www.anhe-inno.com'>安和创新科技（北京）有限公司</a>&nbsp;&nbsp;版权所有（2009年11月）  版本: " . VERSION . "</p>\n";
	print_footer();
}

?>
