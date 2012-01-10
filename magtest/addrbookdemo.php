<?php

define("MAG_PUSHENGINE_URI", "");
define("MAG_LOG_DIR", dirname(__FILE__)."/log");
include_once("../magadalib/php/maglibada.inc");
include_once("../magserver/include/hz2py.inc");

define("DATA_ID", "id");
define("DATA_NAME", "name");
define("DATA_GENDER", "gender");
define("DATA_ORG_ID", "orgid");
define("DATA_ORG_NAME", "orgname");
define("DATA_PHONE", "phone");
define("DATA_MOBILE", "mobile");
define("DATA_EMAIL", "email");
define("DATA_OTHER", "");

define("TYPE_STRING", "string");
define("TYPE_DOUBLE", "double");

function getConfig(&$req) {
	if(preg_match('/192\.168\.0\./', $_SERVER['HTTP_HOST']) > 0) {
		$dbsync_uri = "http://192.168.0.201/MAGLIBv0.3/magserver/pushserv/dbengine.php";
	}else {
		$dbsync_uri = "http://119.161.134.220:20180/MAGLIBv0.3/magserver/pushserv/dbengine.php";
	}
	$conf = new DBSyncConfig($req, $dbsync_uri);

	$conf->addTable('mag_txl_org');
	$conf->addTable('mag_txl_user');

	$conf->addParam('_title', "MAG企业地址簿iPhone版");
	$conf->addParam('_template', array(
		# label, field, type, header, index, orderby, datatype
		array("",           "badge", DATA_ID, TRUE, FALSE, FALSE, TYPE_STRING),
		array("姓名/Name",  "name",  DATA_NAME, TRUE, FALSE, FALSE, TYPE_STRING),
		array("性别/Gender", "gender", DATA_GENDER, TRUE, FALSE, FALSE, TYPE_STRING),
		array("职级/Level", "levelname", DATA_OTHER, FALSE, FALSE, FALSE, TYPE_STRING),
		array("办公室/Office", "phonenumber", DATA_PHONE, FALSE, FALSE, FALSE, TYPE_STRING),
		array("手机/Mobile", "mobile", DATA_MOBILE, FALSE, FALSE, FALSE, TYPE_STRING),
		array("黑莓/BB", "bb", DATA_MOBILE, FALSE, FALSE, FALSE, TYPE_STRING),
		array("电子邮件/Email", "email", DATA_EMAIL, FALSE, FALSE, FALSE, TYPE_STRING),
		array("", "levelorder", DATA_OTHER, FALSE, FALSE, TRUE, TYPE_DOUBLE),
		array("", "displayorder", DATA_OTHER, FALSE, FALSE, TRUE, TYPE_DOUBLE),
		array("", "namePY", DATA_OTHER, FALSE, TRUE, TRUE, TYPE_STRING),
		array("", "nameInitials", DATA_OTHER, FALSE, TRUE, FALSE, TYPE_STRING),
		array("", "jgdm", DATA_ORG_ID, FALSE, FALSE, FALSE, TYPE_STRING),
		array("部门/Dept", "jgmc", DATA_ORG_NAME, TRUE, FALSE, FALSE, TYPE_STRING),
	));

	$req->response($conf->toJSON());
	return TRUE;
}
registerHandler("CONFIG", "getConfig");

function zeropadding($num, $width) {
	$str = "".$num;
	while(strlen($str) < $width) {
		$str = "0".$str;
	}
	return $str;
}

function getName() {
	$surname = array("李", "王", "张", "刘", "陈", "杨", "赵", "黄", "周", "吴", "徐", "孙", "胡", "朱", "高", "林", "何", "郭", "马", "罗", "梁", "宋", "郑", "谢", "韩", "唐", "冯", "于", "董", "萧", "余", "潘", "杜", "戴", "夏", "钟", "汪", "田", "任", "姜");
	$name = array("刚", "阳", "大", "天", "江", "海", "山", "石", "铁", "坚", "强", "辉", "亮", "雄", "伟", "强辉", "汉夫", "长江", "希亮", "四光", "进喜", "汉祥", "运高", "应吉", "顺达", "宝瑞", "开泰", "寿康");
	$fname = array("美惠", "淑芳", "素贞", "懿妃", "清照", "静文", "雅茹", "宝钗", "瑶环", "凤钏", "小翠", "金兰", "香玉", "瑛", "桂珍", "珠", "珊", "绣", "琳");
	if(rand()/getrandmax() < 0.3) {
		return array($surname[rand(0, count($surname)-1)].$fname[rand(0, count($fname)-1)], 'F');
	}else {
		return array($surname[rand(0, count($surname)-1)].$name[rand(0, count($name)-1)], 'M');
	}
}

$__org_data = array(
			"name_0" => "总公司",
			"0" => array(
				"name_0" => "董事会办公室",
				"user_0" => 10,
				"name_1" => "执行委员会",
				"user_1" => 15,
				"name_2" => "总经理办公室",
				"user_2" => 8,
				"name_3" => "党务工作部（工会)",
				"user_3" => 9,
				"name_4" => "综合管理部",
				"user_4" => 84,
				"name_5" => "计划财务部",
				"user_5" => 23,
				"name_6" => "信息技术中心",
				"user_6" => 5,
				"6" => array(
					"name_0" => "业务支撑部",
					"user_0" => 42,
					"name_1" => "信息管理部",
					"user_1" => 24,
					"name_2" => "研发部",
					"user_2" => 23,
				),
				"name_7" => "法务部",
				"user_7" => 7,
				"name_8" => "市场部",
				"user_8" => 232,
				"name_9" => "客户管理部",
				"user_9" => 13,
				"9" => array(
					"name_0" => "客户管理部A组",
					"user_0" => 93,
					"name_1" => "客户管理部B组",
					"user_1" => 83,
					"name_2" => "客户管理部C组",
					"user_2" => 89,
				),
				"name_10" => "发展规划部",
				"user_10" => 19,
				"name_11" => "风险控制部",
				"user_11" => 16,
				"name_12" => "研究部",
				"user_12" => 21,
			),
			"name_1" => "华东（上海）分公司",
			"1" => array(
				"name_0" => "公司领导",
				"user_0" => 5,
				"name_1" => "综合管理部",
				"user_1" => 23,
				"name_2" => "财务部",
				"user_2" => 14,
				"name_3" => "外商服务部",
				"user_3" => 8,
				"name_4" => "信息服务部",
				"user_4" => 19,
				"name_5" => "市场部",
				"user_5" => 103,
				"name_6" => "客户管理部",
				"user_6" => 64,
			),
			"name_2" => "华南（广州）分公司",
			"2" => array(
				"name_0" => "总经理办公室",
				"user_0" => 12,
				"name_1" => "综合管理部",
				"user_1" => 43,
				"name_2" => "财务部",
				"user_2" => 13,
				"name_3" => "信息服务部",
				"user_3" => 36,
				"name_4" => "市场部",
				"user_4" => 223,
				"name_5" => "客户管理部",
				"user_5" => 112,
			),
			"name_3" => "西部（重庆）分公司",
			"3" => array(
				"name_0" => "总经理办公室",
				"user_0" => 7,
				"name_1" => "综合管理部",
				"user_1" => 12,
				"name_2" => "财务部",
				"user_2" => 8,
				"name_3" => "信息服务部",
				"user_3" => 13,
				"name_4" => "市场部",
				"user_4" => 63,
				"name_5" => "客户管理部",
				"user_5" => 8,
				"5" => array(
					"name_0" => "成都办事处",
					"user_0" => 23,
					"name_1" => "昆明办事处",
					"user_1" => 16,
					"name_2" => "兰州办事处",
					"user_2" => 5,
					"name_3" => "乌鲁木齐办事处",
					"user_3" => 4,
				),
			),
	);


function generateOrgData($adminid, &$org_data, &$org_array, &$usr_array) {
	$utel_num = 62674201;
	$umob_num = 13787202001;
	$ubb_num  = 13920205001;

	$i = 0;
	while(array_key_exists("name_".$i, $org_data)) {
		#echo $org_data["name_".$i]."/".$org_data["user_".$i]."\n";

		$org = array();
		$jgmc = $org_data["name_".$i];
		$org['jgmc'] = $jgmc;
		$org_count = count($org_array)+1;
		$org_tel = 82008300 + count($org_array);
		$org_id = zeropadding($org_count, 6);
		$org['jgdm'] = $org_id;
		$org['adminid'] = $adminid;
		$org['orginfo'] = $jgmc."\nTel: 010-".$org_tel."\nFax: 010-".($org_tel+1);
		$org_tel+=2;
		$org['displayorder'] = $org_count;
		$ucount = $org_data["user_".$i];
		if(is_null($ucount) || empty($ucount)) {
			$ucount = 0;
		}
		$org['usercount'] = $ucount;

		$org_array[] = $org;

		for($u = 0; $u < $ucount; $u ++) {
			$usr = array();
			$usr['badge'] = 'U'.zeropadding(count($usr_array)+1, 6);
			list($uname, $gender) = getName();
			$usr['name'] = $uname;;
			$usr['ename'] = hz2py($uname);
			$usr['gender'] = $gender;
			$usr['levelname'] = "";
			$usr['levelorder'] = 0;
			$usr['phonenumber'] = "010-".($utel_num + count($usr_array));
			$usr['mobile'] = "".($umob_num + count($usr_array));
			$usr['email'] = hz2py($uname)."@anhe-inno.com";
			$usr['namePY'] = hz2py($uname);
			$usr['nameInitials'] = hz2py_initial($uname);
			$usr['jgmc'] = $jgmc;
			$usr['jgdm'] = $org_id;
			$usr['bb'] = "".($ubb_num + count($usr_array));
			$usr['displayorder'] = $u*1.0;
			$usr_array[] = $usr;
		}

		if(!is_null($org_data["$i"])) {
			generateOrgData($org_id, $org_data["$i"], $org_array, $usr_array);
		}
		$i ++;
	}
}

function getOrgData(&$req) {
	global $__org_data;
	$org_array = array();
	$usr_array = array();
	generateOrgData("", $__org_data, $org_array, $usr_array);

	$req->response(json_encode($org_array));
	return TRUE;
}
registerHandler("SYNCORG", "getOrgData");

function getUserData(&$req) {
	global $__org_data;
	$org_array = array();
	$usr_array = array();
	generateOrgData("", $__org_data, $org_array, $usr_array);

	$req->response(json_encode($usr_array));
	return TRUE;
}
registerHandler("SYNCUSR", "getUserData");

acceptRequest();

?>
