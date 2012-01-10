var _client_start_time = -1;
var _system_start_time = -1;

function getSystemTime() {
	//ajaxRPC('GETSYSTEMTIME', {}, onGetSystemTimeSucc, null, false);
	var pane = document.getElementById('_system_time_tray');
	if(pane != null) {
		_system_start_time = Number(pane.innerHTML)*1000;
		_client_start_time = (new Date()).getTime();
		setSystemTime();
	}
}

function setSystemTime() {
	sys_now = new Date();
	sys_now.setTime(sys_now.getTime() - _client_start_time + _system_start_time);
	document.getElementById('_system_time_tray').innerHTML = '系统时间：' + sys_now.getDate() + '日' + zeroFill(sys_now.getHours(), 2) + ':' + zeroFill(sys_now.getMinutes(), 2) + ':' + zeroFill(sys_now.getSeconds(), 2);
	setTimeout(setSystemTime, 1000);
}

/*function onGetSystemTimeSucc(msg) {
	var pane = document.getElementById('_system_time_tray');
	pane.innerHTML = '服务器时间：' + msg.date;
}*/

EventManager.Add(window, 'load', function(ev, obj) {
	getSystemTime();
});
