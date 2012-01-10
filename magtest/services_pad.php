<?php

define("ANHE_MEASURE_COMPRESSION_RATIO", 1);

include_once("../magversion.inc");
include_once("config.php");
include_once("../magadalib/php/maglibada.inc");
include_once("utils.inc.php");

function auth_func($user, $passwd, &$req) {
	if($user == 'admin' && $passwd == '123') {
		$url = new MAGLinkURL("services.php");
		$url->setHandler("QUOTATION");
		$url->setNotify(FALSE);
		$url->setExpireHours(72);
		registerPrefetchURL($url);

		$ret_url = new MAGLinkURL();
		$ret_url->setHandler("MAINSCREENPAD");
		$ret_url->setNotify(FALSE);
		$ret_url->setExpireHours(72);
		return $ret_url;
	}else {
		return FALSE;
	}
}
registerAuthenticator("auth_func");


function mainscreen_pad(&$req) {
	$doc = new MAGDocument("MAIN - ".$req->getUsername()."/PAD");

	$doc_style = new MAGStyle();
	$doc_style->setBackground("login.jpg");
	$doc_style->setStyle("title-background", "top_bg.png duplicate adjust-vertical");
	$doc_style->setStyle("title-text-style", "icon=icon_read_doc.png font-weight=bold");
	$doc_style->setStyle("title-height", "56");
	$doc->setStyle($doc_style);

	$top_link = new MAGLinkURL();
	$top_link->setHandler("TOPMENU");
	$frame_top = new MAGFrame($top_link, "_top");

	$frame_top_style = new MAGStyle();
	$frame_top_style->setHeight('40');

	$frame_top->setStyle($frame_top_style);

	$doc->add($frame_top);

	$menu_link = new MAGLinkURL();
	$menu_link->setHandler("MAINSCREEN");
	$frame_menu = new MAGFrame($menu_link, "_menu");

	$menu_frame_style = new MAGStyle();
	$menu_frame_style->setHeight('*');
	$menu_frame_style->setWidth('30%');

	$frame_menu->setStyle($menu_frame_style);

	$doc->add($frame_menu);

	$content_link = new MAGLinkURL("services.php");
	$content_link->setHandler("SCREEN1");
	$frame_content = new MAGFrame($content_link, "_content");

	$frame_content_style = new MAGStyle();
	$frame_content_style->setHeight('*');
	$frame_content_style->setWidth('*');

	$frame_content->setStyle($frame_content_style);

	$doc->add($frame_content);


	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("MAINSCREENPAD", "mainscreen_pad");

function mainscreen(&$req) {
	$doc = new MAGDocument("MAIN - ".$req->getUsername());

	$doc_style = new MAGStyle();
	$doc_style->setBackground("login.jpg");
	$doc_style->setStyle("title-background", "top_bg.png #FF0000 #00FF00 duplicate adjust-vertical");
	$doc_style->setStyle("title-icon", "icon_read_doc.png");
	$doc_style->setStyle("title-height", "56");
	$doc_style->setStyle("title-font-weight", "bold");
	$doc->setStyle($doc_style);

	#$panel = new MAGPanel("");
	#$panel_style = new MAGStyle();
	#$panel_style->setBorder(2, '#DDDDDD');
	#$panel_style->setBackground("bgimg.jpg adjust-horizontal white top");
	#$panel_style->setPadding(0);
	#$panel->setStyle($panel_style);

	$imgbtn_style = new MAGStyle();
	$imgbtn_style->setAlignCenter();
	$imgbtn_style->setIWidth(300);
	$imgbtn_style->setIHeight('100%');
	$imgbtn_style->setHeight(100);
	#$imgbtn_style->setIHeight('100%');
	#$imgbtn_style->setBorder(10, '#000000');
	$imgbtn_style->setStyle("padding", 20);
	$imgbtn_style->setStyle("text-style", "icon=icon_unread_doc.png icon-position=right text-color=#ff0000 line-limit=1 text-valign=center icon-valign=center");
	#$imgbtn_style->setStyle("text-style", "icon=icon_unread_doc.png icon-position=right text-color=#ff0000 text-align=right icon-align=right icon-padding-right=80 line-limit=1");
	$imgbtn_style->setStyle("focus-text-style", "icon=icon_unread_doc.png icon-position=left text-color=#ff0000 text-align=right icon-align=left line-limit=1 text-valign=center");
	$imgbtn_style->setStyle("visited-text-style", "icon=icon_read_doc.png line-limit=1 text-valign=center");
	$imgbtn_style->setStyle("focus-visited-text-style", "icon=icon_read_doc.png icon-position=top line-limit=1 text-valign=center");
	$imgbtn_style->setStyle("body-background", "button_normal_300.png");
	$imgbtn_style->setStyle("focus-body-background", "button_over_300.png");
	$imgbtn_style->setBackground("image=bitmapborder.png color=#000000 duplicate=bitmap-border border-top=14 border-left=13 border-bottom=16 border-right=17");
	$imgbtn_style->setStyle("hint-background", "image=bitmapborder.png color=#000000 duplicate=bitmap-border border-top=14 border-left=13 border-bottom=16 border-right=17");
	$imgbtn_style->setFocusBackground("alpha=0");
	$imgbtn_style->setStyle("hint-text-style", "font-scale=0.8 text-align=left padding-top=14 padding-bottom=16 padding-left=13 padding-right=17");
	$imgbtn_style->setAlignCenter();
	$doc->addClass("imgbtn_style", $imgbtn_style);

	$style = new MAGStyle();
	$style->setAlignCenter();
	$style->setBackground("start-color=#ff0000 end-color=#880000 corner=10");
	$style->setFocusBackground("start-color=#00ff00 end-color=#008800 corner=5");
	#$style->setIWidth('100%');
	#$style->setIHeight('100%');
	$style->setStyle("text-style", "align=center color=#ffe401");
	$style->setStyle("visited-text-style", "font-style=italic color=#00e401");
	$style->setStyle("focus-text-style", "align=center color=#83530e");
	$style->setBorder(5, null);
	$style->setHeight(60);

	session_start();
	if(isset($_SESSION['tval']) && !empty($_SESSION['tval'])) {
		$_SESSION['tval']++;
	}else {
		$_SESSION['tval']=1;
	}

	$link1 = new MAGLinkURL("services.php");
	$link1->setHandler("SCREEN1");
	$btn1 = new MAGLink("文档一 OOOKKK!!".$_SESSION['tval']."text", $link1, 0, '_content');
	$btn1->setNotify(TRUE);
	$btn1->setClass('imgbtn_style');
	$btn1->setHint("Document 1 shows some useful information.\nTooltip ....\nToootip ....\n12312312\n123123\n");
	$btn1->setStatus("Document 1 get focus!");
	$btn1->setSave(FALSE);
	$doc->add($btn1);

	$btn2_style = new MAGStyle();
	$btn2_style->setStyle("hint-text-style", "font-scale=2.0 text-align=right padding=20");
	$btn2_style->setStyle("hint-border-width", "0");
	$btn2_style->setStyle("hint-background-transparent", "true");
	$link2 = new MAGLinkURL('services.php');
	$link2->setHandler('SCREEN2');
	$btn2 = new MAGLink("文档二", $link2, DEFAULT_EXPIRE, '_content');
	$btn2->setNotify(TRUE);
	$btn2->setClass('imgbtn_style');
	$btn2->setStyle($btn2_style);
	$btn2->setHint("一个组件可以有_class和_style两个样式属性，_style属性优先级高于_class属性，可以定义一个通用的_class属性，然后再_style中个别定义个性的样式属性。");
	$doc->add($btn2);

	$link_quotation = new MAGLinkURL('services.php');
	$link_quotation->setHandler('QUOTATION');
	$btn3 = new MAGLink("报价单(推送无提示)", $link_quotation, DEFAULT_EXPIRE, '_content');
	$btn3->setNotify(FALSE);
	$btn3->setStyle($style);
	$doc->add($btn3);

	$link4 = new MAGLinkURL('services.php');
	$link4->setHandler('SCREEN_READONLY');
	$btn4 = new MAGLink("只读控件", $link4, DEFAULT_EXPIRE, '_content');
	$btn4->setStyle($style);
	$doc->add($btn4);

	$link5 = new MAGLinkURL('services.php');
	$link5->setHandler('MAGLIST_EXAMPLE');
	$btn4 = new MAGLink("MAGList例子", $link5, DEFAULT_EXPIRE, '_content');
	$btn4->setStyle($style);
	$doc->add($btn4);

	$link6 = new MAGLinkURL('services.php');
	$link6->setHandler('NINEGRID');
	$btn4 = new MAGLink("九宫格", $link6, DEFAULT_EXPIRE, LINK_TARGET_NEW);
	$btn4->setStyle($style);
	$doc->add($btn4);

	$params = array(
		"class" => "com.anheinno.libs.mag.controls.calendar.CalendarLinkedControl",
		"params" => array("http://192.168.0.201/MAGtest/services.php?_action=GETCALENDAR")
		);
	$btn5 = new MAGLink("日历周视图", json_encode($params), 0, LINK_TARGET_CUSTOMCONTROL);
	$btn5->setStyle($style);
	$doc->add($btn5);

	$link7 = new MAGLinkURL('services.php');
	$link7->setHandler('NEWAPPOINTMENT');
	$btn6 = new MAGLink("添加约会", $link7, 0, LINK_TARGET_NEW);
	$btn6->setStyle($style);
	$btn6->setHint("To create new appointment!\nThis is a brand new function, please try it. You will not disappoint!!!");
	$doc->add($btn6);

	#$doc->add($panel);

	#$doc->add(new MAGPanel("test panel test"));
	#$doc->add(new MAGPanel("test panel test 23232"));

	$menu = new MAGMenuItem("访问新浪", "http://www.sina.com.cn", 0, LINK_TARGET_BROWSER);
	$doc->add($menu);

	$menu = new MAGMenuItem("报价单", $link_quotation, DEFAULT_EXPIRE, LINK_TARGET_NEW);
	$doc->add($menu);

	$link_infogrid = new MAGLinkURL('services.php');
	$link_infogrid->setHandler('INFOGRIDDEMO');
	$menu = new MAGMenuItem("Info Grid Demo", $link_infogrid, DEFAULT_EXPIRE, LINK_TARGET_NEW);
	$doc->add($menu);


	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("MAINSCREEN", "mainscreen");

function topmenu(&$req) {
	$doc = new MAGDocument("");

	$doc_style = new MAGStyle();
	$doc_style->setBackground("color=blue");

	$doc->setStyle($doc_style);

	$text = new MAGText("Hello", "MAG");
	$doc->add($text);
	
	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("TOPMENU", "topmenu");

acceptRequest();

?>
