<?php
include "config_MAGTieredSelect.php";
include "../MAGLIB/magadalib/php/maglibada.inc";

function auth_func($user, $passwd) {
	if($user == 'admin' && $passwd == '123') {
		return SERVICE_SCRIPT."?_action=MAGTieredSelect";
	}else {
		return FALSE;
	}
}
registerAuthenticator("auth_func");

function MAGTieredSelect(&$req)
{
	$doc = new MAGDocument("MAGTieredSelect");
	$Panel = new MAGPanel("");

$submit1 = new MAGSubmit("确认", "", SERVICE_SCRIPT, LINK_TARGET_NEW);
$Panel->add($submit1);

$keyvalue=array();
//$keyvalue[]=array("_text"=>"北京0", "_value"=>"BJ0");
//$keyvalue[]=array("_text"=>"北京1", "_value"=>"BJ1");
$keyvalue=array("valuesBJ0","valuesBJ1");

$valuesx = "valuesBJ";
$text = "testBJ";
$bj = "BJ";
$beijing = "BeiJing";
$options=array();
$suboption= array();
for($i=0;$i<8;$i++)
{
	$xtext=$text.$i;
	$xvaluesx = $valuesx.$i;
	$xbj = $bj.$i;
	$xbeijing = $beijing.$i;
	$title=$beijing.$i;
	
	
	if($i==0)
	{
	$subsuboption[]=array("_text"=> "sub".$xtext,"_value"=> "sub".$xvaluesx);
	$suboption=array("_title"=> "sub".$title,"_options"=>$subsuboption);
	$options[]=array("_text"=> $xtext,"_value"=> $xvaluesx,"_suboption"=>$suboption);
	}
	else
	{
	//$suboption[]=array("_text"=> "sub".$xtext,"_value"=> "sub".$valuesx);
	$options[]=array("_text"=> $xtext,"_value"=> $xvaluesx);
	}
	
	//boption[]=array("_text"=> "sub".$xtext,"_value"=> "sub".$valuesx,"_suboption"=>array("sub".$title));
	//$options[]=array("_text"=> $xtext,"_value"=> $valuesx,"_suboption"=>$suboption);
}

	$grid = new MAGTieredSelect("MAGTieredSelect", $options, $keyvalue, "TEST_id");
	$Panel->add($grid);

	$doc->add($Panel);
	$req->response($doc->toJSON());
	return true;
}
registerHandler("MAGTieredSelect", "MAGTieredSelect");

acceptRequest();

?>