<?php

/* 背景商店插件参赛版 For Discuz X2.0
   $Id:shop.inc.php 2011-07-19 Rufey_Lau
 */
 
if(!defined('IN_DISCUZ')){
	exit('Access Denied!');
}

/* 系统初始化 */
define('IN_BKSHOP', true);
$path = 'source/plugin/bkshop_dzx';
$self = 'plugin.php?id=bkshop_dzx:shop';
$set = $_G['cache']['plugin']['bkshop_dzx'];
$admins = explode(",", $set['ADMINS']);
include DISCUZ_ROOT.$path.'/shop.func.php';

/* 基本判断 */
in_array($_G['uid'], $admins) ? $isadmin = true : $isadmin = false;
!$_G['uid'] ? exit(showmessage('not_loggedin', NULL, array(), array('login' => 1))) : '';
!in_array($_G['groupid'], unserialize($set['GROUPS'])) ? exit(showmessage('对不起，您当前的用户组不在被允许的范围内！','index.php')) : '';

/* 加载模块 */
list($shop, $shoppage) = datalist('bkshop', '', 12, TRUE, 'id', $self); //商店模块
list($bkadmindata, $bkadminpage) = datalist('bkshop', '', 15, TRUE, 'id', $self.'&amp;mod=admin'); //背景管理模块
list($mine, $minepage) = datalist('bkshop_buy', "WHERE `uid` = '$_G[uid]'", 8, TRUE, 'id', $self.'&amp;mod=mine');

/* 数据读取 */
if($_G['gp_type']=='edit' && $_G['gp_eid']!==''){ 
	$edit = DB::fetch_first("SELECT * FROM ".DB::table('bkshop')." WHERE `id` = '".intval($_G['gp_eid'])."'"); 
}

/* 数据处理 */

$uconfig = DB::fetch_first("SELECT * FROM ".DB::table('bkshop_users')." WHERE `uid` = '".$_G['uid']."'");
if(!$uconfig){
	$newdata = array(
		'uid' => $_G['uid'],
		'switch' => 1,
		'repeat' => 0,
		'level' => 3,
		'vertical' => 1,
		'used' => 0
	);
	DB::insert('bkshop_users', $newdata);
}

if($_G['gp_handle']=="buying"){
	$data = array(
		'uid' => $_G['uid'],
		'date' => TIMESTAMP,
		'days' => intval($_G['gp_days']),
		'bid' => intval($_G['gp_bid'])
	);
	$buy = DB::fetch_first("SELECT * FROM ".DB::table('bkshop')." WHERE `id` = '$data[bid]'");
	$credit = 'extcredits'.$buy['credit'];
	$credits = DB::fetch_first("SELECT * FROM ".DB::table('common_member_count')." WHERE `uid` = '$_G[uid]'");
	$total = $data['days'] * $buy['price'];
	if(($credits[$credit] - $total) < 0){
		showmessage('对不起，您的积分不足！', $self);	
	}else{
		$bought = DB::fetch_first("SELECT * FROM ".DB::table('bkshop_buy')." WHERE `uid` = '$_G[uid]' and `bid` = '".$data['bid']."'");
		updatemembercount($_G['uid'], array($credit => -$total));
		if(!$bought){
			DB::insert('bkshop_buy', $data);
		}else{
			$data['date'] = $bought['date'];
			$data['days'] = $bought['days'] + $data['days'];
			DB::update('bkshop_buy', $data, "`id`='$bought[id]' and `uid` = '$_G[uid]'");
		}
	}
	showmessage('操作成功!', $self.'&amp;mod=mine');
}elseif($_G['gp_handle']=="new" && $isadmin){
	$data = array(
		'name' => htmlspecialchars($_G['gp_name']),
		'price' => intval($_G['gp_price']),
		'credit' => $_G['gp_credit']
	);
	if($data['name']==''){
		showmessage('对不起，您还没有填写背景名称！','javascript:history.go(-1)');
	}else{
		$resource = $_G['gp_background']=='' ? $_FILES['background'] : $_G['gp_background'];
		if(is_array($resource)){
			if($resource['name']==''){
				showmessage('对不起，您没有上传背景图片！', 'javascript:history.go(-1)');
				exit;
			}
			$ext = explode(".", $resource['name']);
			$exk = count($ext) - 1;
			$ext = $ext[$exk];
			$exts = explode(",", $set['EXTS']);
			if(!in_array($ext, $exts)){
				showmessage('对不起，您所上传的背景图片格式不被支持！',	'javascript:history.go(-1)');
			}else{
				!file_exists($path.'/background') ? mkdir($path.'/background', 0777) : '';
				$newname = md5('Ymdhis'.rand(1,999));
				$copying = $path.'/background/'.$newname.'.'.$ext;
				copy($resource['tmp_name'], $copying);
				$data['background'] = $copying;
			}
		}elseif(is_string($resource)){
			$data['background'] = $resource;
		}else{
			showmessage('对不起，您没有上传背景图片！', 'javascript:history.go(-1)');
			exit;
		}
		$query = DB::insert('bkshop', $data);
		$query > 0 ? showmessage('操作成功！',$self.'&amp;mod=admin&amp;type=new') : showmessage('系统繁忙，请稍后再试！','javascript:history.go(-1)');
	}
}elseif($_G['gp_handle']=="delete" && $isadmin){
	//背景删除模块
	foreach($_G['gp_delete'] as $val){
		DB::query("DELETE FROM ".DB::table('bkshop')." WHERE `id` = '$val'");
	}
	showmessage('操作成功！',$self.'&amp;mod=admin');
}elseif($_G['gp_handle']=="edit" && $isadmin){
	//背景编辑模块
	$editdata = DB::fetch_first("SELECT * FROM ".DB::table('bkshop')." WHERE `id` = '".intval($_G['gp_eid'])."'");
	$data = array(
		'name' => htmlspecialchars($_G['gp_name']),
		'price' => intval($_G['gp_price']),
		'credit' => $_G['gp_credit']
	);
	if($data['name']==''){
		showmessage('对不起，背景名称不能为空！','javascript:history.go(-1)');
	}else{
		$resource = $_G['gp_background']=='' ? $_FILES['background'] : $_G['gp_background'];
		if(is_array($resource)){
			if($resource['name']==''){
				showmessage('对不起，背景图片不能为空！', 'javascript:history.go(-1)');
				exit;
			}
			$ext = explode(".", $resource['name']);
			$exk = count($ext) - 1;
			$ext = $ext[$exk];
			$exts = explode(",", $set['EXTS']);
			if(!in_array($ext, $exts)){
				showmessage('对不起，您所上传的背景图片格式不被支持！',	'javascript:history.go(-1)');
			}else{
				!file_exists($path.'/background') ? mkdir($path.'/background', 0777) : '';
				copy($resource['tmp_name'], $editdata['background']);
				$data['background'] = $editdata['background'];
			}
		}elseif(is_string($resource)){
			$data['background'] = $resource;
		}else{
			showmessage('对不起，背景图片不能为空！', 'javascript:history.go(-1)');
			exit;
		}
		$query = DB::update('bkshop', $data, "`id`='".$editdata['id']."'");
		$query > 0 ? showmessage('操作成功！',$self.'&amp;mod=admin') : showmessage('系统繁忙，请稍后再试！', 'javascript:history.go(-1)');
	}
}elseif($_G['gp_handle']=="config"){
	$result = DB::result_first("SELECT * FROM ".DB::table('bkshop_users')." WHERE `uid` = '".$_G['uid']."'");
	$data = array(
		'uid' => $_G['uid'],
		'switch' => intval($_G['gp_switch']),
		'repeat' => intval($_G['gp_repeat']),
		'level' => intval($_G['gp_level']),
		'vertical' => intval($_G['gp_vertical']),
		'used' => intval($_G['gp_used'])
	);
	$query = DB::update('bkshop_users', $data, "uid='$_G[uid]'");
	$query > 0 ? showmessage('操作成功！',$self.'&amp;mod=mine') : showmessage('系统繁忙，请稍后再试！', 'javascript:history.go(-1)');
}

/* 加载模版 */
if($_G['gp_mod']=="buy"){
	$info = DB::fetch_first("SELECT * FROM ".DB::table('bkshop')." WHERE `id` = '".intval($_G['gp_bid'])."'");
	include_once template('bkshop_dzx:buy');
}else{
	include_once template('bkshop_dzx:memcp');
}

?>