<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
//print_r($_G);
$appurl=$_G['siteurl']."admin.php?action=plugins&operation=config&do=2&identifier=active&pmod=admin";
loadcache('plugin');
@extract($_G['cache']['plugin']['active']);

$activeclass=parconfig($activeclass);

$p=$_G['gp_p'];
$p=$p?$p:'index';

if($p=='index'){
	$page=$_G['page'];
	$begin=($page-1)*$adminpagenum;
	$manylist=array();
	$rs=DB::query("SELECT * FROM ".DB::table('plugin_active')." ORDER BY id desc LIMIT $begin , $adminpagenum");
	while ($rw=DB::fetch($rs)){
		$manylist[]=$rw;
	}
	$allnum=DB::result_first("SELECT count(*) FROM ".DB::table('plugin_active'));
	$pagenav=multi($allnum,$adminpagenum,$page,$appurl."&p=$p");
}elseif ($p=='add'){
	if($_POST){
		$timestamp=$_G['timestamp'];
		$cid=intval($_G['gp_acid']);
		$title=dhtmlspecialchars($_G['gp_atitle']);
		$info=($_G['gp_ainfo']);
		$begin=strtotime($_G['gp_abegin']);
		$end=strtotime($_G['gp_aend']);
		$place=$_G['gp_aplace'];
		$url=$_G['gp_aurl'];
		if(empty($cid)) cpmsg('请返回选择分类！');
		if(empty($title)) cpmsg("请返回增加标题！");
		
		DB::query("INSERT INTO ".DB::table('plugin_active')." ( `id` , `cid` , `title` ,`url`, `info` , `begin` , `end` , `place` , `dateline` ) VALUES (NULL , '$cid', '$title','$url', '$info', '$begin', '$end', '$place', '$timestamp');");
		$id=DB::insert_id();
		if($_FILES['file']['error']==0){
			$typename='jpg';
			$target="source/plugin/active/images/{$id}.{$typename}";
			if(@copy($_FILES['file']['tmp_name'], $target) || (function_exists('move_uploaded_file') && @move_uploaded_file($_FILES['file']['tmp_name'], $target))) {
  				 @unlink($_FILES['file']['tmp_name']);
   			}
		}
		//die($appurl."&p=$p");
		cpmsg("新增成功！",$appurl);
	}
}
elseif ($p=='edit'){
	$id=intval($_G['gp_id']);
	$active=DB::fetch_first("SELECT * FROM ".DB::table('plugin_active')." WHERE `id` ='{$id}' LIMIT 0 , 1");
	if($_POST){
		$timestamp=$_G['timestamp'];
		$cid=intval($_G['gp_acid']);
		$title=dhtmlspecialchars($_G['gp_atitle']);
		$info=($_G['gp_ainfo']);
		$begin=strtotime($_G['gp_abegin']);
		$end=strtotime($_G['gp_aend']);
		$place=$_G['gp_aplace'];
		$url=$_G['gp_aurl'];
		if(empty($cid)) cpmsg('请返回选择分类！');
		if(empty($title)) cpmsg("请返回增加标题！");
		
		DB::query("REPLACE INTO ".DB::table('plugin_active')." ( `id` , `cid` , `title` ,`url`, `info` , `begin` , `end` , `place` , `dateline` ) VALUES ($id , '$cid', '$title','$url', '$info', '$begin', '$end', '$place', '$timestamp');");
		if($_FILES['file']['error']==0){
			$typename='jpg';
			$target="source/plugin/active/images/{$id}.{$typename}";
			if(@copy($_FILES['file']['tmp_name'], $target) || (function_exists('move_uploaded_file') && @move_uploaded_file($_FILES['file']['tmp_name'], $target))) {
  				 @unlink($_FILES['file']['tmp_name']);
   			}
		}
		//die($appurl."&p=$p");
		cpmsg("编辑成功！",$appurl);
	}
}
elseif ($p=='del'){
	$id=intval($_G['gp_id']);
	DB::query("DELETE FROM ".DB::table('plugin_active')." WHERE `id` ='$id' LIMIT 1 ;");
	cpmsg('删除成功！',$appurl);
}
else cpmsg('未定义操作！');

include(template("active:admin_$p"));
function parconfig($str){
$return=array();
$array=explode("\n",str_replace("\r","",$str));
foreach ($array as $v){
   $t=explode("=",$v);
   $t[0]=trim($t[0]);
   $return[$t[0]]=$t[1];
}
return $return;
} 
?>
