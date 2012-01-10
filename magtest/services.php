<?php

define("ANHE_MEASURE_COMPRESSION_RATIO", 1);

include_once("../magversion.inc");
include_once("config.php");
include_once("../magadalib/php/maglibada.inc");
include_once("utils.inc.php");

function auth_func($user, $passwd, &$req) {
	if($user == 'admin' && $passwd == '123') {

		$url = new MAGLinkURL();
		$url->setHandler("QUOTATION");
		$url->setNotify(FALSE);
		$url->setExpireHours(72);
		registerPrefetchURL($url);

		$ret_url = new MAGLinkURL();
		$ret_url->setHandler("MAINSCREEN");
		$ret_url->setNotify(FALSE);
		$ret_url->setExpireHours(72);
		return $ret_url; #SERVICE_SCRIPT."?_action=MAINSCREEN";
	}else {
		return FALSE;
	}
}
registerAuthenticator("auth_func");


function mainscreen(&$req) {
	$doc = new MAGDocument("MAIN - ".$req->getUsername()."/".$req->getOS());

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

	$btn1 = new MAGLink("文档一 OOOKKK!!".$_SESSION['tval']."text", SERVICE_SCRIPT."?_action=SCREEN1", 0);
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
	$btn2 = new MAGLink("文档二", SERVICE_SCRIPT."?_action=SCREEN2", DEFAULT_EXPIRE);
	$btn2->setNotify(TRUE);
	$btn2->setClass('imgbtn_style');
	$btn2->setStyle($btn2_style);
	$btn2->setHint("一个组件可以有_class和_style两个样式属性，_style属性优先级高于_class属性，可以定义一个通用的_class属性，然后再_style中个别定义个性的样式属性。");
	$doc->add($btn2);

	$btn3 = new MAGLink("报价单(推送无提示)", SERVICE_SCRIPT."?_action=QUOTATION", DEFAULT_EXPIRE);
	$btn3->setNotify(FALSE);
	$btn3->setStyle($style);
	$doc->add($btn3);

	$btn4 = new MAGLink("只读控件", SERVICE_SCRIPT."?_action=SCREEN_READONLY", DEFAULT_EXPIRE);
	$btn4->setStyle($style);
	$doc->add($btn4);

	$btn4 = new MAGLink("MAGList例子", SERVICE_SCRIPT."?_action=MAGLIST_EXAMPLE", DEFAULT_EXPIRE);
	$btn4->setStyle($style);
	$doc->add($btn4);

	$btn4 = new MAGLink("九宫格", SERVICE_SCRIPT."?_action=NINEGRID", DEFAULT_EXPIRE, LINK_TARGET_NEW);
	$btn4->setStyle($style);
	$doc->add($btn4);

	$params = array(
		"class" => "com.anheinno.libs.mag.controls.calendar.CalendarLinkedControl",
		"params" => array("http://192.168.0.201/MAGtest/services.php?_action=GETCALENDAR")
		);
	$btn5 = new MAGLink("日历周视图", json_encode($params), 0, LINK_TARGET_CUSTOMCONTROL);
	$btn5->setStyle($style);
	$doc->add($btn5);

	$btn6 = new MAGLink("添加约会", SERVICE_SCRIPT."?_action=NEWAPPOINTMENT", 0, LINK_TARGET_NEW);
	$btn6->setStyle($style);
	$btn6->setHint("To create new appointment!\nThis is a brand new function, please try it. You will not disappoint!!!");
	$doc->add($btn6);

	#$doc->add($panel);

	#$doc->add(new MAGPanel("test panel test"));
	#$doc->add(new MAGPanel("test panel test 23232"));

	$menu = new MAGMenuItem("访问新浪", "http://www.sina.com.cn", 0, LINK_TARGET_BROWSER);
	$doc->add($menu);
	$menu = new MAGMenuItem("报价单", SERVICE_SCRIPT."?_action=QUOTATION", DEFAULT_EXPIRE, LINK_TARGET_NEW);
	$doc->add($menu);
	$menu = new MAGMenuItem("Info Grid Demo", SERVICE_SCRIPT."?_action=INFOGRIDDEMO", DEFAULT_EXPIRE, LINK_TARGET_NEW);
	$doc->add($menu);


	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("MAINSCREEN", "mainscreen");

function ninegrid(&$req) {
	$doc = new MAGDocument("九宫格实例");

	$doc_style = new MAGStyle();
	$doc_style->setStyle("status-color", "#ffff00");
	$doc_style->setStyle("title-background", "top_bg.png duplicate adjust-vertical");
	$doc_style->setStyle("title-icon", "icon_read_doc.png");
	$doc_style->setStyle("title-height", "56");
	$doc_style->setStyle("title-font-weight", "bold");
	$doc_style->setBackground("#000066");
	$doc->setStyle($doc_style);

	$panel = new MAGPanel("");
	$panel_style = new MAGStyle();
	$panel_style->setBackground("#000066");
	$panel_style->setPadding(0);
	$panel->setStyle($panel_style);

	$imgbtn_style = new MAGStyle();
	$imgbtn_style->setAlignCenter();
	$imgbtn_style->setWidth('25%');
	$imgbtn_style->setIWidth('100%');
	$imgbtn_style->setHeight(100);
	$imgbtn_style->setBorder(10, '#000066');
	$imgbtn_style->setIHeight('100%');
	$imgbtn_style->setStyle("text-alignment", "center");
	$imgbtn_style->setBackground("start-color=#000000 end-color=#000044 corner=10 icon=icon_unread_doc.png align=center valign=middle duplicate=none");
	$imgbtn_style->setFocusBackground("start-color=#000044 end-color=#000000 corner=10 icon=icon_read_doc.png align=center valign=middle duplicate=none");
	$imgbtn_style->setStyle("status-text-style", "color=#ff0000 text-align=center");
	$imgbtn_style->setStyle("status-background", "#ff0066 #000022");

	$doc->addClass("btn_style", $imgbtn_style);

	for($i = 0; $i < 7; $i ++) {
		$btn = new MAGLink("$i", SERVICE_SCRIPT."?_action=SCREEN1", DEFAULT_EXPIRE, LINK_TARGET_NEW);
		$btn->setNotify(TRUE);
		$btn->setClass("btn_style");
		$btn->setHint("Document $i shows some useful information");
		$btn->setStatus("Document $i get focus!");
		$panel->add($btn);
	}

	$doc->add($panel);

	$close_link = new MAGLink("Close window", "close();", 0, LINK_TARGET_SCRIPT);
	$cl_style = new MAGStyle();
	$cl_style->setAlignCenter();
	$cl_style->setStyle("text-style", "color=white");
	$cl_style->setStyle("focus-text-style", "color=red");
	$close_link->setStyle($cl_style);
	$doc->add($close_link);

	$open_link = new MAGLink("Open window", "open('".SERVICE_SCRIPT."?_action=SCREEN1"."');", 0, LINK_TARGET_SCRIPT);
	$open_link->setStyle($cl_style);
	$doc->add($open_link);

	$popup_link = new MAGLink("Popup window", "popup('".SERVICE_SCRIPT."?_action=SCREEN1"."');", 0, LINK_TARGET_SCRIPT);
	$popup_link->setStyle($cl_style);
	$doc->add($popup_link);

	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("NINEGRID", "ninegrid");


function quotation(&$req) {
	$doc = new MAGDocument("BlackBerry Quotation - ".$req->getUsername());

	$panel = new MAGPanel("");

	$panel_style = new MAGStyle();
	$panel_style->setBgcolor('#DDDDDD');
	$panel_style->setBorder(20, '#000000');
	$panel_style->setPadding(10);
	$panel->setStyle($panel_style);

	$style_header = new MAGStyle();
	$style_header->setAlignCenter();
	$style_header->setBackground("#333333 #666666");
	$style_header->setStyle('title-text-style', 'text-align=center color=white font-weight=bold');
	$style_header->setStyle('title-width', '100%');
	$style_header->setBorder(1, '#000000');

	$style_header->setWidth('31%');
	$head1 = new MAGText("Carrier", "");
	$head1->setStyle($style_header);
	$panel->add($head1);

	$style_header->setWidth('23%');
	$head2 = new MAGText("Model", "");
	$head2->setStyle($style_header);
	$panel->add($head2);

	$style_header->setWidth('23%');
	$head3 = new MAGText("Price", "");
	$head3->setStyle($style_header);
	$panel->add($head3);

	$style_header->setWidth('23%');
	$head4 = new MAGText("Retail", "");
	$head4->setStyle($style_header);
	$panel->add($head4);

	$val = array(
		array('Original', '9700', '5900', '5600'),
		array('Original', '9000', '4900', '4600'),
		array('Original', '8900', '4000', '3700'),
		array('Carrier', '9000', '4500', '4200'),
		array('Carrier', '8900', '3500', '3200'),
		array('Carrier', '9500', '3600', '3300'),
		array('Carrier', '8320', '2800', '2500'),
		array('Carrier', '8310', '2400', '2100'),
		array('Carrier', '8800', '2000', '1700'),
		array('Original', '9700', '5900', '5600'),
		array('Original', '9000', '4900', '4600'),
		array('Original', '8900', '4000', '3700'),
		array('Carrier', '9000', '4500', '4200'),
		array('Carrier', '8900', '3500', '3200'),
		array('Carrier', '9500', '3600', '3300'),
		array('Carrier', '8320', '2800', '2500'),
		array('Carrier', '8310', '2400', '2100'),
		array('Carrier', '8800', '2000', '1700'),
		array('Original', '9700', '5900', '5600'),
		array('Original', '9000', '4900', '4600'),
		array('Original', '8900', '4000', '3700'),
		array('Carrier', '9000', '4500', '4200'),
		array('Carrier', '8900', '3500', '3200'),
		array('Carrier', '9500', '3600', '3300'),
		array('Carrier', '8320', '2800', '2500'),
		array('Carrier', '8310', '2400', '2100'),
		array('Carrier', '8800', '2000', '1700'),
		array('Original', '9700', '5900', '5600'),
		array('Original', '9000', '4900', '4600'),
		array('Original', '8900', '4000', '3700'),
		array('Carrier', '9000', '4500', '4200'),
		array('Carrier', '8900', '3500', '3200'),
		array('Carrier', '9500', '3600', '3300'),
		array('Carrier', '8320', '2800', '2500'),
		array('Carrier', '8310', '2400', '2100'),
		array('Carrier', '8800', '2000', '1700'),
		array('Original', '9700', '5900', '5600'),
		array('Original', '9000', '4900', '4600'),
		array('Original', '8900', '4000', '3700'),
		array('Carrier', '9000', '4500', '4200'),
		array('Carrier', '8900', '3500', '3200'),
		array('Carrier', '9500', '3600', '3300'),
		array('Carrier', '8320', '2800', '2500'),
		array('Carrier', '8310', '2400', '2100'),
		array('Carrier', '8800', '2000', '1700'),
		array('Carrier', '8700', '1300', '1000')
	);

	$width = array(0.31, 0.23, 0.23, 0.23);

	$style = new MAGStyle();
	$style->setBorderBottom(1, "#000000");
	$style->setStyle("focus", "normal");

	for($i = 0; $i < count($val); $i ++) {
		for($j = 0; $j < count($val[$i]); $j ++) {
			if($j <= 1) {
				$text = new MAGText($val[$i][$j], "");
			}else {
				$text = new MAGText("", $val[$i][$j]);
			}
			$style->setWidth($width[$j]);
			$text->setStyle($style);
			$panel->add($text);
		}
	}

	$doc->add($panel);

	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("QUOTATION", "quotation");


function screen_readonly(&$req) {
	$doc = new MAGDocument("MAG Document 1 for {$req->getUsername()}");
	#$doc->collapse();

	$panel1 = new MAGPanel("只读控件面板");

	$text = new MAGText("MAGText", "这是Active Focus, 010-62774256");

	$style = new MAGStyle();
	$style->setBgcolor('#330000');
	$style->setStyle('focus', 'active');
	$style->setBorderBottom(1, '#00FF00');
	$text->setStyle($style);
	$panel1->add($text);

	$text2 = new MAGText("MAGText", "这是Active Focus, http://msmb.mobi");
	$text2->setStyle($style);
	$panel1->add($text2);

	$panel1->add(new MAGNote("MAGNote", "这是一个MAGNote"));

	$link = new MAGLink("下一个文档", SERVICE_SCRIPT."?_action=SCREEN2&_user={$req->getUsername()}", DEFAULT_EXPIRE, LINK_TARGET_NEW);
	$style_link = new MAGStyle();
	$style_link->setAlignCenter();
	$style_link->setHeight(60);
	$style_link->setVAlignMiddle();
	$style_link->setBorderBottom(5, '#000000');
	$style_link->setFocusBorderColorBottom('#FF0000');
	$style_link->setBackground('#ff0000');
	$link->setStyle($style_link);
	$panel1->add($link);
	$doc->add($panel1);

	$tabpanel = new MAGTabPanel(2);

	$panel2 = new MAGPanel("输入控件面板");

	$panel2_style = new MAGStyle();
	$panel2_style->setPadding(20);
	$panel2_style->setBorder(5, '#ff0000');
	$panel2_style->setBackground("start-color=#ffeeee end-color=#888888");
	$panel2->setStyle($panel2_style);

	$text = new MAGTextinput("MAGTextinput", "_textinput", "");
	$text->readOnly();
	$panel2->add($text); 
	$passwd = new MAGPassword("MAGPassword", "_password");
	$passwd->readOnly();
	$panel2->add($passwd);
	$date1 = new MAGDate("MAGDate/Date", "_date", 0, MAGDATE_UI_DATE);
	$date1->readOnly();
	$panel2->add($date1);
	$date2 = new MAGDate("MAGDate/Time", "_time", 0, MAGDATE_UI_TIME);
	$date2->readOnly();
	$panel2->add($date2);
	$date3 = new MAGDate("MAGDate/DateTime", "_datetime", 0, MAGDATE_UI_DATETIME);
	$date3->readOnly();
	$panel2->add($date3);

	$sel = new MAGSelect("MAGSelect", "_select", "", MAGSELECT_UI_AUTO);
	$sel->addOption("Option1", "Option1");
	$sel->addOption("Option2", "Option2");
	$sel->addOption("Option3", "Option3");
	$sel->addOption("Option4", "Option4");
	$sel->setStyle($style_link);
	$sel->readOnly();
	$panel2->add($sel);

	$msel = new MAGMultiselect("MAGMultiselect", "_mselect");
	$msel->addOption("Option1", "Option1", true);
	$msel->addOption("Option2", "Option2", false);
	$msel->addOption("Option3", "Option3", false);
	$msel->addOption("Option4", "Option4", true);
	$style_link->setBackground('#00ff00');
	$style_link->setFocusBackground('#0000ff');
	$msel->setStyle($style_link);
	$msel->readOnly();
	$panel2->add($msel);

	$radio = new MAGRadio("Radio 1", "radio_group", "1", "r1");
	$panel2->add($radio);
	$radio = new MAGRadio("Radio 2", "radio_group", "2", "r2");
	$panel2->add($radio);
	$radio = new MAGRadio("Radio 3", "radio_group", "3", "r3");
	$panel2->add($radio);

	$style = new MAGStyle();
	$style->setAlignRight();
	$style->setWidth(0.5);
	$submit = new MAGSubmit("Submit", "SUBMIT", SERVICE_SCRIPT, LINK_TARGET_NEW);
	$submit->readOnly();
	$submit->setStyle($style);
	$panel2->add($submit);
	$submit2 = new MAGSubmit("Submit2", "SUBMIT", SERVICE_SCRIPT, LINK_TARGET_NEW);
	$style->setAlignLeft();
	$submit2->setStyle($style);
	$panel2->add($submit2);

	$panel2->add(new MAGHiddenInput("_hidden1", "hiddenVal1"));
	$panel2->add(new MAGHiddenInput("_hidden2", "hiddenVal2"));
	$panel2->add(new MAGHiddenInput("_hidden3", "hiddenVal3"));

	$tabpanel->add($panel2);

	$panel3 = new MAGPanel('面板3');
	$panel3->add(new MAGText("MAGText", "这是面板3"));
	$tabpanel->add($panel3);

	$panel3 = new MAGPanel('面板4');
	$panel3->add(new MAGText("MAGText", "这是面板4"));
	$tabpanel->add($panel3);

	$panel3 = new MAGPanel('面板五');
	$panel3->add(new MAGText("MAGText", "这是面板5"));
	$tabpanel->add($panel3);

	$panel3 = new MAGPanel('面板6');
	$panel3->add(new MAGText("MAGText", "这是面板6"));
	$tabpanel->add($panel3);

	$panel3 = new MAGPanel('面板7');
	$panel3->add(new MAGText("MAGText", "这是面板7"));
	$tabpanel->add($panel3);

	$doc->add($tabpanel);

	$doc->add(new MAGGadget(MAG_GADGET_SIGNAL));
	$doc->add(new MAGGadget(MAG_GADGET_BATTERY));
	$doc->add(new MAGGadget(MAG_GADGET_TIME));

	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("SCREEN_READONLY", "screen_readonly");

function screen1(&$req) {
	$doc = new MAGDocument("MAG Document 1 for {$req->getUsername()}");

	$panel1 = new MAGPanel("Read-only control panel", MAGPANEL_EXPAND_COLLAPSE);

	$panel_style = new MAGStyle();
	$panel_style->setBackground('#999999');
	$panel_style->setFocusBackground('#EFEFEF');
	$panel1->setStyle($panel_style);

	$text = new MAGText("MAGText", "这是Active Focus, 010-62774256");

	$style = new MAGStyle();
	$style->setBgcolor('#330000');
	$style->setStyle('focus', 'active');
	$style->setBorderBottom(1, '#00FF00');
	$text->setStyle($style);
	$panel1->add($text);

	$text2 = new MAGText("MAGText", "这是Active Focus, http://msmb.mobi");
	$text2->setStyle($style);
	$panel1->add($text2);

	$text3 = new MAGText("", "居中对齐(focus=none)");
	$style = new MAGStyle();
	$style->setAlignCenter();
	$style->setStyle('focus', 'none');
	$style->setStyle('text-style', 'font-scale=0.8 color=purple');
	$text3->setStyle($style);
	$panel1->add($text3);

	$text3 = new MAGText("", "靠右对齐(focus=normal)");
	$style = new MAGStyle();
	$style->setAlignRight();
	$style->setStyle('focus', 'normal');
	$text3->setStyle($style);
	$panel1->add($text3);

	$text3 = new MAGText("", "focus=active则占据");
	$style = new MAGStyle();
	$style->setStyle('focus', 'active');
	$text3->setStyle($style);
	$panel1->add($text3);

	$panel1->add(new MAGNote("MAGNote", "这是一个MAGNote"));

	$link = new MAGLink("下一个文档", SERVICE_SCRIPT."?_action=SCREEN2&_user={$req->getUsername()}", DEFAULT_EXPIRE, LINK_TARGET_SELF);
	$style_link = new MAGStyle();
	$style_link->setAlignCenter();
	$style_link->setHeight(60);
	$style_link->setVAlignMiddle();
	$style_link->setBorderBottom(1, '#000000');
	$style_link->setBackground('#ff0000');
	$style_link->setFocusBackground('#00ff00');
	$link->setStyle($style_link);
	$panel1->add($link);
	$doc->add($panel1);

	$panel2 = new MAGPanel("Input control panel", MAGPANEL_EXPAND_EXPAND);

	$text_val = getLocalVar($req, "test_local_var", "Default text string");

	$text = new MAGTextinput("MAGTextinput", "_textinput", $text_val);
	$textinput_style = new MAGStyle();
	$textinput_style->setStyle("title-width", "100");
	$textinput_style->setStyle("content-padding", "10");
	$text->setStyle($textinput_style);
	$text->nonEmpty();
	$panel2->add($text); 

	$number = new MAGTextinput("MAGTextinput(Number)", "_numberinput", "", TEXTINPUT_FILTER_NUMERIC);
	$number->nonEmpty();
	$number->verifyMessage("请填写数字，且不能为空！");
	$panel2->add($number); 
	$panel2->add(new MAGPassword("MAGPassword", "_password"));
	$panel2->add(new MAGDate("MAGDate/Date", "_date", 0, MAGDATE_UI_DATE));
	$panel2->add(new MAGDate("MAGDate/Time", "_time", 0, MAGDATE_UI_TIME));
	$panel2->add(new MAGDate("MAGDate/DateTime", "_datetime", 0, MAGDATE_UI_DATETIME));

	$sel = new MAGSelect("MAGSelect", "_select", "", MAGSELECT_UI_AUTO);
	$sel->addOption("Option1", "Option1");
	$sel->addOption("Option2", "Option2");
	$sel->addOption("Option3", "Option3");
	$sel->addOption("Option4", "Option4");
	$sel->setStyle($style_link);
	$panel2->add($sel);

	$msel = new MAGMultiselect("MAGMultiselect", "_mselect");
	$msel->addOption("Option1", "Option1", true);
	$msel->addOption("Option2", "Option2", false);
	$msel->addOption("Option3", "Option3", false);
	$msel->addOption("Option4", "Option4", true);
	$style_link->setBgcolor('#00ff00');
	$msel->setStyle($style_link);
	$panel2->add($msel);

	$radio = new MAGRadio("Radio 1", "radio_group", "1", "r1");
	$panel2->add($radio);
	$radio = new MAGRadio("Radio 2", "radio_group", "2", "r2");
	$panel2->add($radio);
	$radio = new MAGRadio("Radio 3", "radio_group", "3", "r3");
	$panel2->add($radio);

	$style = new MAGStyle();
	$style->setAlignRight();
	$style->setWidth(0.5);
	$style->setIWidth("80%");
	$style->setStyle("text-style", "color=red text-align=center padding=10");
	$style->setStyle("focus-text-style", "color=yellow text-align=center padding=10");
	$submit = new MAGSubmit("Submit", "SUBMIT", SERVICE_SCRIPT, LINK_TARGET_NEW);
	$submit->setConfirm("您确定提交以上信息吗？");
	$submit->setStyle($style);
	$panel2->add($submit);
	$submit2 = new MAGSubmit("Submit2", "SUBMIT", SERVICE_SCRIPT, LINK_TARGET_NEW);
	$submit2->setRequire(array('_numberinput'));
	$style->setAlignLeft();
	$submit2->setStyle($style);
	$panel2->add($submit2);

	$panel2->add(new MAGHiddenInput("_hidden1", "hiddenVal1"));
	$panel2->add(new MAGHiddenInput("_hidden2", "hiddenVal2"));
	$panel2->add(new MAGHiddenInput("_hidden3", "hiddenVal3"));

	$init_value = array("_firstname" => "Jian", "_lastname" => "Qiu", "_notes"=>"OK");

	$combo = new MAGInputCombo("_combo", $init_value);
	
	$first_name = new MAGTextinput("名：", "_firstname", "");
	$combo->add($first_name);
	$last_name  = new MAGTextinput("姓：", "_lastname", "");
	$combo->add($last_name);
	$notes = new MAGTextinput("备注：", "_notes", "", TEXTINPUT_FILTER_NOTES);
	$combo->add($notes);

	$panel2->add($combo);

	$init_val = array(
		array("_firstname" => "Jian", "_lastname" => "Qiu", "_notes"=>"OK"),
		array("_firstname" => "Yahoo", "_lastname" => "Google", "_notes"=>"Test2")
	);

	$duplicator = new MAGInputDuplicator("多输入测试", "_duplicator", $combo, $init_val);
	$duplicator->setSortable(false);
	$duplicator->setMaxCount(4);
	$panel2->add($duplicator);

	$doc->add($panel2);

	$download1 = new MAGFileLink("Word下载测试", "", "http://192.168.0.201/download/aog.doc");
	$doc->add($download1);
	$download1 = new MAGFileLink("Excel下载测试", "", "http://192.168.0.201/download/citics_txl.xls");
	$doc->add($download1);
	$download1 = new MAGFileLink("PowerPoint下载测试", "", "http://192.168.0.201/download/anheintro.ppt");
	$doc->add($download1);
	$download1 = new MAGFileLink("Pdf下载测试", "", "http://192.168.0.201/download/exim.pdf");
	$doc->add($download1);
	$download1 = new MAGFileLink("Pdf下载测试(<256K)", "", "http://192.168.0.201/download/aw_overview.pdf");
	$doc->add($download1);

	$menu = new MAGMenuItem("报价单", SERVICE_SCRIPT."?_action=QUOTATION", DEFAULT_EXPIRE, LINK_TARGET_NEW);
	$doc->add($menu);

	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("SCREEN1", "screen1");

function level3one(&$req) {
	$doc = new MAGDocument("Level 3/1 document");
	$text = new MAGText("title", "level 3 one page");
	$doc->add($text);

	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("LEVEL3ONE", "level3one");

function level3two(&$req) {
	$doc = new MAGDocument("Level 3/2 document");
	$text = new MAGText("title", "level 3 two page");
	$doc->add($text);

	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("LEVEL3TWO", "level3two");

function level3three(&$req) {
	$doc = new MAGDocument("Level 3/3 document");
	$text = new MAGText("title", "level 3 three page");
	$doc->add($text);

	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("LEVEL3THREE", "level3three");

function submitresult(&$req) {
	$doc = new MAGDocument("Submit result");

	setLocalVar($req, "test_local_var", $req->_textinput);

	$doc->add(new MAGText("_textinput", $req->_textinput));
	$doc->add(new MAGText("_numberinput", $req->_numberinput));
	$doc->add(new MAGText("_password", $req->_password));
	$doc->add(new MAGText("_date", $req->_date));
	$doc->add(new MAGText("_time", $req->_time));
	$doc->add(new MAGText("_datetime", $req->_datetime));
	$doc->add(new MAGText("_select", $req->_select));
	$doc->add(new MAGText("_mselect", $req->_mselect));
	$doc->add(new MAGText("_hidden1", $req->_hidden1));
	$doc->add(new MAGText("_hidden2", $req->_hidden2));
	$doc->add(new MAGText("_hidden3", $req->_hidden3));
	$doc->add(new MAGText("_combo", $req->_combo));
	$doc->add(new MAGText("_duplicator", $req->_duplicator));
	if(isset($req->radio_group)) {
		$doc->add(new MAGText("radio_group", $req->radio_group));
	}

	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("SUBMIT", "submitresult");

function screen2(&$req) {
	$doc = new MAGDocument("MAG Document 2 for {$req->getUsername()}");

	$panel1 = new MAGPanel("Read only component");
	$panel1->add(new MAGText("MAGText", "这是一个MAGText"));
	$panel1->add(new MAGNote("MAGNote", "这是一个MAGNote"));
	$panel1->add(new MAGLink("前一个文档", SERVICE_SCRIPT."?_action=SCREEN1&_user={$req->getUsername()}", DEFAULT_EXPIRE, LINK_TARGET_SELF));
	
	$imgstyle = new MAGStyle();
	$imgstyle->setIWidth(0.6);
	$imgstyle->setAlignCenter();

	$img1 = new MAGImage("图片1", "http://curl.haxx.se/ds-curlicon.png");
	$img1->setStyle($imgstyle);
	$panel1->add($img1);

	$img2 = new MAGImage("图片2", "http://static.howstuffworks.com/gif/nuclear-test-7.jpg");
	$img2->setStyle($imgstyle);
	$panel1->add($img2);

	$img3 = new MAGImage("图片3", "http://www.textually.org/tv/archives/images/set3/test-pattern-clock_4767.jpg");
	$img3->setStyle($imgstyle);
	$panel1->add($img3);

	$img4 = new MAGImage("图片4", "http://www.shybm.com/upload/news/n2008022711430910.jpg");
	$img4->setStyle($imgstyle);
	$panel1->add($img4);

	$scanner = new MAGBarcodeScanner("条码扫描", "_scanner");
	$panel1->add($scanner);

	$doc->add($panel1);

	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("SCREEN2", "screen2");

function calendarData(&$req) {
	$user = array("qj" => '#FFBBBB', "liuzn"=>'#BBBBFF', 'srh1'=>'#BBFFBB');
	$arr = array();
	foreach($user as $uname=>$color) {
		$cmd = "/usr/local/phpowa/phpowacalendar.php https 192.168.0.50 {$uname} {$uname} 123@asd json";
		$handler = popen($cmd, "r");
		if($handler) {
			$json = '';
			while (!feof($handler)) {
				$json .= fread($handler, 8192);
			}
			pclose($handler);
			$arr[] = array("_username"=>$uname, "_usercolor"=>$color, "_userdata"=>json_decode($json));
		}
	}
	$output = array("_data" => $arr);
	$req->response(json_encode($output));

	return TRUE;
}
registerHandler("GETCALENDAR", 'calendarData');

function newappointment(&$req) {
	$doc = new MAGDocument("创建新的约会({$req->getUsername()})");
	$doc_style = new MAGStyle();
	$doc_style->setBackground("white");
	$doc->setStyle($doc_style);

	$panel = new MAGPanel("");

	$subj = new MAGTextinput("主题：", "_subject", "");
	$subj->nonEmpty();
	$panel->add($subj); 

	$loc = new MAGTextinput("地点：", "_location", "");
	$loc->nonEmpty();
	$panel->add($loc);

	/*$allday = new MAGMultiselect("", "_allday");
	$allday->addOption("全天事件", "on", false);
	$panel->add($allday);*/
	
	$tm = ((time()/1800)+1)*1800*1000;
	$dtstart = new MAGDate("开始时间：", "_dtstart", $tm);
	$panel->add($dtstart);

	$dura = new MAGSelect("持续时间：", "_duration", 1800);
	for($i = 0; $i < 8; $i ++) {
		if($i > 0) {
			$dura->addOption($i."小时", $i*3600);
			$dur_str = $i."小时30分钟";
		}else {
			$dur_str = "30分钟";
		}
		$dura->addOption($dur_str, $i*3600+1800);
	}
	for($i = 1; $i <= 7; $i ++) {
		$dura->addOption($i."天", $i*24*3600);
	}
	$panel->add($dura);

	/*$tz = new MAGSelect("时区：", "_timezone", 'CN_CST');
	$tz->addOptions("中国标准时区(UTC+08)", "CN_CST");
	$tz->addOptions("PST(UTC-08)", "US_PST");
	$tz->addOptions("MST(UTC-07)", "US_MST");
	$tz->addOptions("CST(UTC-06)", "US_CST");
	$tz->addOptions("EST(UTC-05)", "US_EST");
	$tz->addOptions("全球标准时间(UTC-00)", "GMT");
	$panel->add($tz);*/

	$status = new MAGSelect("状态：", "_busystatus", 'BUSY');
	$status->addOption("忙", "BUSY");
	$status->addOption("闲", "FREE");
	$status->addOption("未定", "TENTATIVE");
	$status->addOption("外出", "OOF");
	$panel->add($status);

	$alarm = new MAGSelect("提前提醒时间：", "_reminder", 900);
	$alarm->addOption("5分钟", 300);
	$alarm->addOption("15分钟", 900);
	$alarm->addOption("半小时", 1800);
	for($h = 1; $h < 12; $h ++) {
		$alarm->addOption($h."小时", $h*3600);
	}
	$panel->add($alarm);

	$params = array(
		"http://192.168.0.205:10086/hostingapp/bb/contact_services.php?_action=GROUPDIR",
		"http://192.168.0.205:10086/hostingapp/bb/contact_services.php?_action=USERLIST",
		"http://192.168.0.205:10086/hostingapp/bb/contact_services.php?_action=SEARCH",
		1
	);
	$attendees = new MAGCustominput("参会者：", "com.anheinno.libs.mag.controls.contacts.ContactInput", '', $params, "_attendees");
	$panel->add($attendees);

	$note = new MAGTextinput("备注：", "_note", "", TEXTINPUT_FILTER_NOTES);
	$panel->add($note);

	$submit = new MAGSubmit("保存1", "SAVENEWAPPOINTMENT", SERVICE_SCRIPT);
	$submit_style = new MAGStyle();
	$submit_style->setAlignCenter();
	$submit_style->setWidth("1/3");
	$submit_style->setIWidth("90%");
	$submit->setStyle($submit_style);
	$panel->add($submit);

	$submit = new MAGSubmit("保存2", "SAVENEWAPPOINTMENT", SERVICE_SCRIPT);
	$submit->setStyle($submit_style);
	$panel->add($submit);

	$submit = new MAGSubmit("保存2", "SAVENEWAPPOINTMENT", SERVICE_SCRIPT);
	$submit->setStyle($submit_style);
	$panel->add($submit);

	$doc->add($panel);

	$req->response($doc->toJSON());
	
	return TRUE;
}
registerHandler("NEWAPPOINTMENT", "newappointment");

function saveNewAppointment(&$req) {

	//require_once("include/iCal/ical.inc.php");

	$dtstart = (int)($req->_dtstart/1000);   #2010-08-12T18:00:00.000Z
	$dtend   = $dtstart + $req->_duration;   #2010-08-12T18:00:00.000Z


	if($dtstart <= 0) {
		$req->response("ERROR: 错误的请求！");
		return FALSE;
	}

	if($req->_duration >= 3600*24) {
		$isallday = '1';
		$dtstart = ((int)($dtstart/3600/24))*3600*24;
		$dtend   = $dtstart + $req->_duration;
	} else {
		$isallday = '0';
	}

	magLog($req->_attendees_value);
	#  [{\"_orgname\":\"安和创新/北京/人力与财务\",\"_account\":\"weixy\",\"_name\":\"魏新颖\"}]
	$attendees = json_decode(stripslashes($req->_attendees_value));
	magLog(var_export($attendees, TRUE));

	$to_str = '';
	for($i = 0; $i < count($attendees); $i ++) {
		magLog($to_str);
		if(strlen($to_str) > 0) {
			$to_str .= ", ";
		}
		$name = $attendees[$i]->_account;
		/*if($name == 'qiujian') {
			$to_str .= "qj@bes50.blackberryhome.cn";
		}elseif($name == 'shenrh') {
			$to_str .= "srh1@bes50.blackberryhome.cn";
		}elseif($name == 'liuzn') {
			$to_str .= "liuzn@bes50.blackberryhome.cn";
		}else {*/
			$to_str .= "{$name}@blackberryhome.com.cn";
		//}
	}
	magLog($to_str);

	$dtstart_str = gmdate("Y-m-d\TH:i:s.000\Z", $dtstart);
	$dtend_str   = gmdate("Y-m-d\TH:i:s.000\Z", $dtend);

	$doc = new MAGDocument("保存成功！");

	$txt = new MAGText("_to", $to_str);
	$doc->add($txt);
	$txt = new MAGText("_subject", $req->_subject);
	$doc->add($txt);
	$txt = new MAGText("_location", $req->_location);
	$doc->add($txt);
	$txt = new MAGText("_allday", $isallday);
	$doc->add($txt);
	$txt = new MAGText("_dtstart", $dtstart_str);
	$doc->add($txt);
	$txt = new MAGText("_dtend",   $dtend_str);
	$doc->add($txt);
	$txt = new MAGText("_busystatus", $req->_busystatus);
	$doc->add($txt);
	$txt = new MAGText("_reminder", $req->_reminder);
	$doc->add($txt);
	$txt = new MAGText("_note", $req->_note);
	$doc->add($txt);

	#$attendee = array(
		#array('name'=>'柳仲宁', 'email'=>'liuzn@blackberryhome.com.cn'),
	#	array('name'=>'邱剑', 'email'=>'qiujian@blackberryhome.com.cn'),
		#array('name'=>'沈瑞恒', 'email'=>'shenrh@blackberryhome.com.cn'),
	#);

	/*
	$evt_info = array(
		"_subject" => $req->_subject,
		"_location" => $req->_location,
		"_allday" => $isallday,
		"_dtstart" => $dtstart_str,
		"_dtend" => $dtend_str,
		"_busystatus" => $req->_busystatus,
		"_reminder" => $req->_reminder,
		"_note" => $req->_note,
		"_to" => $to_str
	);

	magLog(json_encode($evt_info));
	*/

	//$ical = iCalRequest('邱剑', 'qj@bes50.blackberryhome.cn', $attendee, $arr);
	//
	//magLog($ical);

	/*$mail  = "To: ".mailAddrEncode($attendee).CRLF;
	$mail .= "Subject: ".mail_utf8_encode($arr['_subject']).CRLF;
	$mail .= "Mime-Version: 1.0".CRLF;
	$mail .= "X-mailer: Foxmail 6, 15, 201, 22 [cn]".CRLF;
	$mail .= "Content-class: urn:content-classes:calendarmessage".CRLF;
	$mail .= "Content-Type: multipart/alternative;".CRLF;
	#$mail .= "Content-Type: multipart/mixed;".CRLF;
	$boundary = getBoundary();
	$mail .= "\tboundary=\"".$boundary."\"".CRLF;
	$mail .= CRLF;
	$mail .= CRLF;
	$mail .= "This is a multi-part message in MIME format.".CRLF;
	$mail .= CRLF;
	$mail .= "--".$boundary.CRLF;
	$mail .= "Content-Type: text/plain;".CRLF;
	$mail .= "\tcharset=\"UTF-8\"".CRLF;
	$mail .= "Content-Transfer-Encoding: base64".CRLF;
	$mail .= CRLF;
	$mail .= base64mailencoding($arr['_note']);
	$mail .= CRLF;
	$mail .= "--".$boundary.CRLF;
	$mail .= "Content-class: urn:content-classes:calendarmessage".CRLF;
	$mail .= "Content-Type: text/calendar;".CRLF;
	$mail .= "\tcharset=\"UTF-8\";".CRLF;
	$mail .= "\tmethod=REQUEST;".CRLF;
	$mail .= "\tname=\"meeting.ics\"".CRLF;
	$mail .= "Content-Transfer-Encoding: 8bit".CRLF;
	$mail .= CRLF;
	$mail .= $ical;
	$mail .= CRLF;
	$mail .= "--".$boundary."--";

	magLog($mail);
	*/

	/*$mail_tmpname = tempnam(sys_get_temp_dir(), "SMTPML");
	file_put_contents($mail_tmpname, json_encode($evt_info));

	$proto = "https";
	$server_name = "192.168.0.50";
	$user = "srh1";
	$passwd = "123@asd";

	$cmd = "/usr/local/phpowa/phpowacalendarcreate.php {$proto} {$server_name} {$user} {$user} {$passwd} {$mail_tmpname}";
	$lastline = system($cmd, $retval);
	if($retval != 0) {
		magLog("OWA: ERRCODE: {$retval}/{$lastline}");
	}

	unlink($mail_tmpname);
	*/

	$doc->add(new MAGScript("invalidate('services.php?_action=MAINSCREEN'); alert('保存成功！');close();"));

	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("SAVENEWAPPOINTMENT", "saveNewAppointment");

function infoGrid(&$req) {
	$doc = new MAGDocument("申请表单({$req->getUsername()})");

	$fields = array(
		array("_title"=>"单号",   "_type"=>MAGINFOGRID_DATA_TYPE_STRING),
		array("_title"=>"处理人", "_type"=>MAGINFOGRID_DATA_TYPE_STRING),
		array("_title"=>"日期",   "_type"=>MAGINFOGRID_DATA_TYPE_STRING),
		array("_title"=>"价格",   "_type"=>MAGINFOGRID_DATA_TYPE_NUMBER),
		array("_title"=>"描述",   "_type"=>MAGINFOGRID_DATA_TYPE_STRING),
	);

	$data = array(
		array("_summary"=>"李四申请购买打印机", "_summary1"=>"2,300", "_summary2"=>"SQ001", "_data"=>array("SQ001", "李四", "2010-10-08 17:00:00", "2300", "购买打印机"), "_link"=>SERVICE_SCRIPT."?_action=QUOTATION", "_target"=>LINK_TARGET_NEW, "_expire"=>DEFAULT_EXPIRE, "_hint"=>"李四申请购买打印机\n2010-10-08 17:00:00\n2300元", "_id"=>"SQ001"),
		array("_summary"=>"张五申请购买电脑", "_summary1"=>"4,200", "_summary2"=>"SQ007", "_data"=>array("SQ007", "张三", "2010-10-01 16:00:00", "4200", "购买电脑"), "_link"=>SERVICE_SCRIPT."?_action=QUOTATION", "_target"=>LINK_TARGET_NEW, "_expire"=>DEFAULT_EXPIRE, "_hint"=>"张三申请购买电脑\n2010-10-01 16:00:00\n4200元", "_id"=>"SQ005"),
		array("_summary"=>"张三申请购买电脑",  "_summary1"=>"4,200", "_summary2"=>"SQ006", "_data"=>array("SQ006", "张三", "2010-10-01 16:00:00", "4200", "购买电脑"), "_link"=>SERVICE_SCRIPT."?_action=QUOTATION", "_target"=>LINK_TARGET_NEW, "_expire"=>DEFAULT_EXPIRE, "_hint"=>"张三申请购买电脑\n2010-10-01 16:00:00\n4200元", "_id"=>"SQ005"),
		array("_summary"=>"张三想申请购买电脑",  "_summary1"=>"4,200", "_summary2"=>"SQ005", "_data"=>array("SQ005", "张三", "2010-10-01 16:00:00", "4200", "购买电脑"), "_link"=>SERVICE_SCRIPT."?_action=QUOTATION", "_target"=>LINK_TARGET_NEW, "_expire"=>DEFAULT_EXPIRE, "_hint"=>"张三申请购买电脑\n2010-10-01 16:00:00\n4200元", "_id"=>"SQ005"),
		array("_summary"=>"王五申请购买桌子",  "_summary1"=>"100", "_summary2"=>"SQ002", "_data"=>array("SQ002", "王五", "2010-10-06 12:00:00", "100", "购买桌子"), "_link"=>SERVICE_SCRIPT."?_action=QUOTATION", "_target"=>LINK_TARGET_NEW, "_expire"=>DEFAULT_EXPIRE, "_hint"=>"王五申请购买桌子\n2010-10-06 12:00:00\n100元", "_id"=>"SQ002"),
		array("_summary"=>"李1申请购买椅子, 这是长字符创测试，测试是否会换行活着省略",  "_summary1"=>"23", "_summary2"=>"SQ010", "_data"=>array("SQ010", "李先生", "2010-09-08 17:00:00", "23", "购买椅子"), "_link"=>SERVICE_SCRIPT."?_action=QUOTATION", "_target"=>LINK_TARGET_NEW, "_expire"=>DEFAULT_EXPIRE, "_hint"=>"李先生申请购买椅子\n2010-09-08 17:00:00\n23元", "_id"=>"SQ009"),
		array("_summary"=>"李2申请购买椅子, 这是长字符创测试，测试是否会换行活着省略",  "_summary1"=>"23", "_summary2"=>"SQ011", "_data"=>array("SQ011", "李先生", "2010-09-08 17:00:00", "23", "购买椅子"), "_link"=>SERVICE_SCRIPT."?_action=QUOTATION", "_target"=>LINK_TARGET_NEW, "_expire"=>DEFAULT_EXPIRE, "_hint"=>"李先生申请购买椅子\n2010-09-08 17:00:00\n23元", "_id"=>"SQ009"),
		array("_summary"=>"李3申请购买椅子, 这是长字符创测试，测试是否会换行活着省略",  "_summary1"=>"23", "_summary2"=>"SQ012", "_data"=>array("SQ012", "李先生", "2010-09-08 17:00:00", "23", "购买椅子"), "_link"=>SERVICE_SCRIPT."?_action=QUOTATION", "_target"=>LINK_TARGET_NEW, "_expire"=>DEFAULT_EXPIRE, "_hint"=>"李先生申请购买椅子\n2010-09-08 17:00:00\n23元", "_id"=>"SQ009"),
		array("_summary"=>"李4申请购买椅子, 这是长字符创测试，测试是否会换行活着省略",  "_summary1"=>"23", "_summary2"=>"SQ013", "_data"=>array("SQ013", "李先生", "2010-09-08 17:00:00", "23", "购买椅子"), "_link"=>SERVICE_SCRIPT."?_action=QUOTATION", "_target"=>LINK_TARGET_NEW, "_expire"=>DEFAULT_EXPIRE, "_hint"=>"李先生申请购买椅子\n2010-09-08 17:00:00\n23元", "_id"=>"SQ009"),
		array("_summary"=>"李5申请购买椅子, 这是长字符创测试，测试是否会换行活着省略", "_summary1"=>"23", "_summary2"=>"SQ014", "_data"=>array("SQ014", "李先生", "2010-09-08 17:00:00", "23", "购买椅子"), "_link"=>SERVICE_SCRIPT."?_action=QUOTATION", "_target"=>LINK_TARGET_NEW, "_expire"=>DEFAULT_EXPIRE, "_hint"=>"李先生申请购买椅子\n2010-09-08 17:00:00\n23元", "_id"=>"SQ009"),
	);

	$grid = new MAGInfoGrid("待审批表单", $fields, $data, 4, "infogrid");
	$grid->setStatus('按"#Q"键显示详细信息。');
	$doc->add($grid);

	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("INFOGRIDDEMO", "infoGrid");


function maglist_example(&$req) {
	$doc = new MAGDocument("MAGList Example");

	$options = array(
		new MAGTieredSelectOption("选项一", "opt1",
			new MAGTieredSelectSuboption(array(
				new MAGTieredSelectOption("选项1.1", "opt1.1"),
				new MAGTieredSelectOption("选项1.2", "opt1.2"),
			), "选项1sub")),
		new MAGTieredSelectOption("选项二", "opt2",
			new MAGTieredSelectSuboption(array(
				new MAGTieredSelectOption("选项2.1", "opt2.1"),
				new MAGTieredSelectOption("选项2.2", "opt2.2"),
				new MAGTieredSelectOption("选项2.3", "opt2.3",
					new MAGTieredSelectSuboption(array(
						new MAGTieredSelectOption("选项2.3.1", "opt2.3.1"),
						new MAGTieredSelectOption("选项2.3.2", "opt2.3.2", 
							new MAGTieredSelectSuboption(array(
								new MAGTieredSelectOption("选项2.3.2.1", "opt2.3.2.1"),
								new MAGTieredSelectOption("选项2.3.2.2", "opt2.3.2.2"),
								new MAGTieredSelectOption("选项2.3.2.3", "opt2.3.2.3"),
								new MAGTieredSelectOption("选项2.3.2.4", "opt2.3.2.4"),
								new MAGTieredSelectOption("选项2.3.2.5", "opt2.3.2.5"),
							), "选项2sub3sub2sub")),
						new MAGTieredSelectOption("选项2.3.3", "opt2.3.3"),
						new MAGTieredSelectOption("选项2.3.4", "opt2.3.4"),
						new MAGTieredSelectOption("选项2.3.5", "opt2.3.5"),
					), "选项2sub3sub")),
				new MAGTieredSelectOption("选项2.4", "opt2.4"),
				new MAGTieredSelectOption("选项2.5", "opt2.5"),
			), "选项2sub")),
	);


	$combo_style = new MAGStyle();
	$combo_style->setPadding(10);
	$combo_style->setBorderBottom(1, '#999999');
	$combo_style->setBackground('color=white');
	$combo_style->setFocusBackground('color=#000099');
	$doc->addClass('combo_style', $combo_style);

	$link_style = new MAGStyle();
	$link_style->setWidth("70%");
	$link_style->setAlignLeft();
	$link_style->setStyle("text-style", "color=black");
	$link_style->setStyle("focus-text-style", "color=blue text-decoration=underline");
	$doc->addClass('link_style', $link_style);

	$text_style = new MAGStyle();
	$text_style->setWidth("30%");
	$text_style->setStyle('focus', 'none');
	$text_style->setStyle('title-text-style', 'font-scale=0.7 color=#999999');
	$text_style->setStyle('text-style', 'font-scale=0.8 font-weight=bold color=green');
	$text_style->setAlignRight();
	$doc->addClass('text_style', $text_style);

	$list = new MAGList("MAGList例子");

	$list_style = new MAGStyle();
	$list_style->setStyle("footer-text-style", "color=black font-scale=0.7");
	$list_style->setStyle("header-background", "color=#ff0000");
	$list_style->setStyle("footer-background", "color=#00ff00");
	$list->setStyle($list_style);

	$data = array(
		array("水木社区", "http://www.newsmth.net", 1000, "水木", "2008-05-14"),
		array("京东商城", "http://www.360buy.com", 5000, "京东", "2009-04-14"),
		array("北京缓解拥堵网", "http://www.bjhjyd.gov.cn", 10, "北京市政府", "2010-12-14"),
		array("百度贴吧", "http://tieba.baidu.com", 8000, "白度", "2009-05-14"),
		array("新浪新闻", "http://news.sina.com.cn", 2000, "新浪", "2010-06-23"),
		array("新浪汽车", "http://audo.sina.com.cn", 1200, "新浪", "2011-06-21"),
		array("新浪房产", "http://house.sina.com.cn", 1800, "新浪", "2011-06-23"),
		array("新浪科技", "http://tech.sina.com.cn", 1400, "新浪", "2011-05-23"),
		array("新浪财经", "http://finance.sina.com.cn", 1300, "新浪", "2011-04-23"),
		array("新浪读书", "http://book.sina.com.cn", 800, "新浪", "2010-04-23"),
		array("百度新闻", "http://news.baidu.com", 6000, "白度", "2011-07-08"),
		array("百度知道", "http://zhidao.baidu.com", 4500, "白度", "2011-01-11"),
		array("百度MP3", "http://mp3.baidu.com", 7700, "白度", "2011-03-28"),
		array("百度图片", "http://image.baidu.com", 9800, "白度", "2011-02-09"),
		array("百度视频", "http://video.baidu.com", 2300, "白度", "2011-02-11"),
		array("百度地图", "http://map.baidu.com", 2200, "百度", "2011-03-31"),
	);

	foreach($data as $d) {
		$combo = new MAGCombo();
		$combo->setHint($d[0]);

		$combo->setClass('combo_style');
		$link = new MAGLink($d[0], $d[1], 0, LINK_TARGET_BROWSER, "link");
		$link->setClass('link_style');
		$combo->add($link);

		$text = new MAGText("访问量：", $d[2], "text");
		$text->setClass('text_style');
		$combo->add($text);

		$note = new MAGNote($d[3], $d[4], "note");
		$combo->add($note);
		$list->add($combo);
	}

	$order_fields = new MAGListOrderFields();
	$order_fields->add("链接文字", "link._title", ORDER_FIELD_TYPE_TEXT);
	$order_fields->add("访问量", "text._text", ORDER_FIELD_TYPE_NUMERIC);
	$order_fields->add("类别", "note._title", ORDER_FIELD_TYPE_TEXT);
	$order_fields->add("日期", "note._note", ORDER_FIELD_TYPE_TEXT);

	$list->setOrderFields($order_fields, TRUE);

	$items_per_page = 3;
	$list->setItemsPerPage($items_per_page);

	$pages = count($data)/$items_per_page;
	if($pages != (int)($pages)) {
		$pages = (int)($pages)+1;
	}
	$list->setFooter('共'.$pages.'页');

	$doc->add($list);

	$panel = new MAGPanel("MAGInputList示例");

	$input_list = new MAGInputList("网站列表", "_input_list");
	$input_list->setStyle($list_style);

	$i = 1;
	foreach($data as $d) {
		$combo = new MAGCombo("item_{$i}");
		$combo->setHint($d[0]);
		$i++;
		$combo->setClass('combo_style');
		$link = new MAGLink($d[0], $d[1], 0, LINK_TARGET_BROWSER, "link");
		$link->setClass('link_style');
		$combo->add($link);

		$text = new MAGText("访问量：", $d[2], "text");
		$text->setClass('text_style');
		$combo->add($text);

		$note = new MAGNote($d[3], $d[4], "note");
		$combo->add($note);
		$input_list->add($combo);
	}

	$input_list->setOrderFields($order_fields, TRUE);

	$input_list->setItemsPerPage($items_per_page);

	$input_list->setFooter('共'.$pages.'页');

	$panel->add($input_list);

	$submit = new MAGSubmit("提交", "SUBMIT_INPUTLIST", SERVICE_SCRIPT, LINK_TARGET_NEW);
	$submit_style = new MAGStyle();
	$submit_style->setAlignCenter();
	$submit->setStyle($submit_style);
	$panel->add($submit);

	$tieredsel_val = array("opt2", "opt2.3", "opt2.3.4");

	$tieredsel = new MAGTieredSelect("层次选项：", $options, json_encode($tieredsel_val), "_tiered_select");
	$panel->add($tieredsel);

	$doc->add($panel);

	$link3 = new MAGLink("Level 3 link 1", SERVICE_SCRIPT."?_action=LEVEL3ONE", DEFAULT_EXPIRE);
	$link_style = new MAGStyle();
	$link_style->setStyle("text-style", "color=black");
	$link_style->setStyle("focus-text-style", "color=red text-decoration=underline");
	$link3->setStyle($link_style);
	$doc->add($link3);

	$link3 = new MAGLink("Level 3 link 2", SERVICE_SCRIPT."?_action=LEVEL3TWO", DEFAULT_EXPIRE);
	$link_style = new MAGStyle();
	$link_style->setStyle("text-style", "color=black");
	$link_style->setStyle("focus-text-style", "color=red text-decoration=underline");
	$link3->setStyle($link_style);
	$doc->add($link3);

	$link3 = new MAGLink("Level 3 link 3", SERVICE_SCRIPT."?_action=LEVEL3THREE", DEFAULT_EXPIRE);
	$link_style = new MAGStyle();
	$link_style->setStyle("text-style", "color=black");
	$link_style->setStyle("focus-text-style", "color=red text-decoration=underline");
	$link3->setStyle($link_style);
	$doc->add($link3);

	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("MAGLIST_EXAMPLE", "maglist_example");

function submit_inputlist(&$req) {
	$doc = new MAGDocument("");

	$text = new MAGText("", urldecode($req->_input_list));
	$doc->add($text);

	$text2 = new MAGText("", urldecode($req->_tiered_select));
	$doc->add($text2);

	$req->response($doc->toJSON());

	return TRUE;
}
registerHandler("SUBMIT_INPUTLIST", "submit_inputlist");

acceptRequest();

?>
