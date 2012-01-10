<?php
include "config_test.php";
include "../MAGLIB/magadalib/php/maglibada.inc";

function auth_func($user, $passwd) {
	if($user == 'admin' && $passwd == '123') {
		return SERVICE_SCRIPT."?_action=TEST";
	}else {
		return FALSE;
	}
}
registerAuthenticator("auth_func");

function magkeywords(&$req)
{
	$doc = new MAGDocument("magkeyword");
	$Panel = new MAGPanel("");

$keyvalue=array();
$keyvalue[]=array("_text"=>"北京0", "_value"=>"BJ0");
//$keyvalue[]=array("_text"=>"北京1", "_value"=>"BJ1");

$valuesx = "valuesBJ";
$text = "testBJ";
$bj = "BJ";
$beijing = "BeiJing";
$options=array();
for($i=0;$i<8;$i++)
{
	$xtext=$text.$i;
	$xvaluesx = $valuesx.$i;
	$xbj = $bj.$i;
	$xbeijing = $beijing.$i;
	
	$options[]=array("_text"=> $xtext,"_value"=> $valuesx,"_keywords"=>array($xtext,$xbj,$xbeijing));
}

	//$grid = new MAGInfoGrid("待审批表单", $fields, $data,5,"infogridxxx");
	//$grid = new MAGKeywordFilterSelect("MAGKeyword", "TEST_title", true, $options, $keyvalue, "URL", "TEST_id");
	$grid = new MAGKeywordFilterSelect("MAGKeyword", false, $options, $keyvalue, "URL", "TEST_id");
	$Panel->add($grid);

	$doc->add($Panel);
	$req->response($doc->toJSON());
	return true;
}
registerHandler("MAGKeywordFilterSelect", "magkeywords");

acceptRequest();

?>