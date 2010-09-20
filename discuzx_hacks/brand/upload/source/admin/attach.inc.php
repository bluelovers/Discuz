<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: attach.inc.php 4360 2010-09-07 08:03:59Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

// 检索出所有model的图片大小
$query = DB::query('select mid,modelname,thumbsize from '.tname('models').'');
while ($res = DB::fetch($query)){
	$_models[] = $res ;
}

if(submitcheck('settingsubmit')){
	foreach($_POST['attach'] as $k => $v){
		if($k == 'filesize'){
			if(preg_match("/(\d+)\s*M/i",$v,$match)){
				$v = $match[1] * 1024 * 1024;
			}elseif(preg_match('/(\d+)\S*K/i',$v,$match)){
				$v = $match[1] * 1024;
			}elseif(preg_match('/(\d+)/i',$v,$match)){
				$v = $v * 1024;
			}else{
				$v = 0;
			}
			$_POST['attach']['filesize'] = $v ;
		}
	}
	$_POST['attachmenturls'] = explode("\r\n", trim($_POST['attachmenturls']));
	$attachmenturlarr = array();
	$attachmenturls = '';
	foreach($_POST['attachmenturls'] as $k=>$v) {
		$v=trim(strip_tags($v));
		if(preg_match("/^http:\/\/[A-Za-z0-9\.\/]+\w$/i", $v)) {
			$attachmenturlarr[]=$v;
		}
	}
	$attachmenturl = $attachmenturlarr[0];
	$attachmenturlcount = count($attachmenturlarr);
	$attachmenturls = implode("\r\n", $attachmenturlarr);

	DB::query('REPLACE INTO '.tname('settings').' (variable,value) values (\'attach\',\''.serialize($_POST['attach']).'\')');
	DB::query('REPLACE INTO '.tname('settings').' (variable,value) values (\'attachmentdir\',\''.$_POST['attachmentdir'].'\')');
	DB::query('REPLACE INTO '.tname('settings').' (variable,value) values (\'attachmenturl\',\''.$attachmenturl.'\')');
	DB::query('REPLACE INTO '.tname('settings').' (variable,value) values (\'attachmenturls\',\''.$attachmenturls.'\')');
	DB::query('REPLACE INTO '.tname('settings').' (variable,value) values (\'attachmenturlcount\',\''.$attachmenturlcount.'\')');
	updatesettingcache();

	foreach($_POST['thumb'] as $k => $v){
		$v['width'] = intval($v['width']);
		$v['height'] = intval($v['height']);
		if($v['width'] == 0 || $v['height'] == 0){
			cpmsg('thumbsize_error');
		}
		updatetable('models',array('thumbsize'=>($v['width'].','.$v['height'])),'modelname=\''.$k.'\'');
		updatemodel('modelname',$k);
	}

	cpmsg('update_success','?action=attach');
}

//添加或更改分类的编辑页面
shownav('global', 'nav_attach');
showsubmenu('nav_attach', array(
	array('settings_basic', 'global', '0'),
	array('nav_attach', 'attach', '1')
));
showtips('attach_tips');
showformheader('attach');
showtableheader();

showsetting('settings_attach_basic_dir', 'attachmentdir', $_G['setting']['attachmentdir'], 'text');
showsetting('settings_attach_basic_urls', 'attachmenturls', ($_G['setting']['attachmenturls']), 'textarea');
showsetting('settings_attach_basic_filesize', 'attach[filesize]', ($_G['setting']['attach']['filesize']/1024) . 'K', 'text');

foreach ($_models as $_model) {
	$_mid = $_mname['mid'];
	$_mname = $_model['modelname'];
	$_thumbsize = explode(',',$_model['thumbsize']);
	showsetting('thumb_size_'.$_mname,
		array("thumb[$_mname][width]", "thumb[$_mname][height]"),
		array(intval($_thumbsize[0]), intval($_thumbsize[1])), 'multiply'
	);
}

showsubmit('settingsubmit');
showtablefooter();
showformfooter();
bind_ajax_form();
?>