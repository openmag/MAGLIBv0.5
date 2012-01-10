<?php
    print_header("MAG Administration Platform - 登录");
?>
<div id="maindiv" class="main">
<table id="content_wrapper" width="564" border="0" align="center" cellpadding="0" cellspacing="0" >
<tr>
  <td id="content_td" width="564" height="247" valign="top" background="images/login_bg.jpg" >
  </td>
</tr>
</table>
</div>

<!--div id='_login_panel' align='center'></div-->
<script language='JavaScript'>
<!--

function init_login_panel_callback(msg) 
{
	if(isErrorMsg(msg)) {
		showAlert(msg);
	} else {
		refresh();
	}
}

function init_login_panel(panel) 
{
    var form = newRPCForm({
	action: 'LOGIN',
	callback: 'init_login_panel_callback'
    }, panel);
    
    var tbl = newTableElement("565", 0, 0, 2, '', 5, 3, 'center', 'middle', form);
    tbl.height = '201';

    tblCell(tbl, 0, 0).width = '244';
    tblCell(tbl, 0, 0).height = '61';
    tblCell(tbl, 0, 0).innerHTML = '&nbsp;';
    mergeCell(tbl, 0, 1, 1, 2);
    tblCell(tbl, 0, 1).innerHTML = '&nbsp;';
    
    tblCell(tbl, 1, 0).width = '244';
    tblCell(tbl, 1, 0).height = '19';
    tblCell(tbl, 1, 0).innerHTML = '&nbsp;';
    mergeCell(tbl, 1, 1, 1, 2);
    tblCell(tbl, 1, 1).innerHTML = '&nbsp;';
    
    tblCell(tbl, 2, 0).height = '33';
    tblCell(tbl, 2, 0).innerHTML = '&nbsp;';
    tblCell(tbl, 2, 1).width = '56';
    tblCell(tbl, 2, 1).align = 'left';
    tblCell(tbl, 2, 1).fontSize = '14';
    tblCell(tbl, 2, 1).color = '#797a7e';
    tblCell(tbl, 2, 1).innerHTML = '账  号：';
    tblCell(tbl, 2, 2).width = '265';
    tblCell(tbl, 2, 2).align = 'left';
    tblCell(tbl, 2, 2).appendChild(newHintTextInput('_mag_user', 'admin', 'MAG管理账号', '180px'));
    
    tblCell(tbl, 3, 0).height = '38';
    tblCell(tbl, 3, 0).innerHTML = '&nbsp;';
    tblCell(tbl, 3, 1).align = 'left';
    tblCell(tbl, 3, 1).fontSize = '14';
    tblCell(tbl, 3, 1).color = '#797a7e';
    tblCell(tbl, 3, 1).innerHTML = '密  码：';
    tblCell(tbl, 3, 2).align = 'left';
    tblCell(tbl, 3, 2).appendChild(newHintPasswordInput('_mag_password', 'MAG管理账号密码', '180px'))

    tblCell(tbl, 4, 0).innerHTML = '&nbsp;';
    tblCell(tbl, 4, 1).innerHTML = '&nbsp;';
    tblCell(tbl, 4, 2).align = 'left';
    //tblCell(tbl, 4, 2).width = '101';
    //tblCell(tbl, 4, 2).height = '32';
    submit_button = newInputElement('image', '', '提交');
    submit_button.src = 'images/submit.png';
    submit_button.style.border = '0px';
    //submit_button.id = "_submit_button";
    tblCell(tbl, 4, 2).appendChild(submit_button);

    ///document.getElementById("content_td").appendChild(form);
}

EventManager.Add(window, 'load', function(ev, obj) {
	init_login_panel(document.getElementById('content_td'));
});

//-->
</script>
