<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: nav.inc.php 4337 2010-09-06 04:48:05Z fanshengshuai $
 */

if(!defined('IN_STORE')) {
	exit('Acess Denied');
}

$navid = empty($_REQUEST['navid'])?null:intval($_REQUEST['navid']);

if(in_array($_REQUEST['navid'],array('index','notice','album','good','consume','groupbuy'))){
	$navid=$_REQUEST['navid'];
}

$shopid = $_G['myshopid'];
if(intval($shopid) < 1){
	cpmsg(lang('menu_home_waitmod'));
	exit;
}
require_once B_ROOT.'./source/class/nav.class.php';
$_nav = new nav();
$nav_default = $_nav->get_shop_nav_default($_G['myshopid']);

if(!empty($_POST['valuesubmit'])){
	$_BCACHE->deltype('detail', 'nav', $_G['myshopid']);
	$checkresults = array();
	$arr_data = array();
	$arr_data['name'] = $_POST['subject'];
	$arr_data['target'] = $_POST['nav_target'];
	$arr_data['available'] = $_POST['available'];

	empty($_POST['strongsubject'])?$_POST['strongsubject']='':$_POST['strongsubject']=1;
	empty($_POST['underlinesubject'])?$_POST['underlinesubject']='':$_POST['underlinesubject']=1;
	empty($_POST['emsubject'])?$_POST['emsubject']='':$_POST['emsubject']=1;
	empty($_POST['fontcolorsubject'])?$_POST['fontcolorsubject']='#      ':$_POST['fontcolorsubject']='#'.$_POST['fontcolorsubject'];
	$arr_data['highlight'] = sprintf("%7s%1s%1s%1s",substr($_POST['fontcolorsubject'], -7),$_POST['emsubject'],$_POST['strongsubject'],$_POST['underlinesubject']);

	if($arr_data['highlight'] === '#        ') {
		$arr_data['highlight']  = '';
	}
	unset($_POST['strongsubject'], $_POST['underlinesubject'], $_POST['emsubject'], $_POST['fontcolorsubject']);
	
	if(!empty($_POST['url'])) {
		$arr_data['url'] = $_POST['url'];
	}

	$arr_data['name'] = cutstr($arr_data['name'], 5);

	if(empty($arr_data['name'])) {
		$checkresults[] = array('subject'=>$lang['nav_name_comment']);
	}

	if(empty($arr_data['url'])) {
		if(intval($navid) > 0) {
		$query = DB::fetch(DB::query('SELECT type FROM '.tname('nav').' where navid='.$navid));
			if($query['type'] != 'sys') {
				$checkresults[] = array('url'=>$lang['nav_url_comment']);
			}
		} elseif (empty($navid)) {
			$checkresults[] = array('url'=>$lang['nav_url_comment']);
		}
	}

	if(!empty($checkresults)) {
		cpmsg('ajax_update_failed', '', '', '', true, true, $checkresults);
	}

	if(!empty($_POST['displayorder'])) {
		$arr_data['displayorder'] = $_POST['displayorder'];
	}

	if(empty($_POST['displayorder'])) {
		$query = DB::fetch(DB::query('SELECT max(displayorder) as displayorder FROM '.tname('nav').' WHERE (type=\'sys\' or type=\'shop\') and shopid=\''.$shopid.'\''));
		$arr_data['displayorder'] = intval($query['displayorder'])+1;
	}

	if(intval($navid) < 1){
		$_GET['op']='add';
		$arr_data['flag']=$navid;
	}

	if($_GET['op']=='add') {

		$arr_data['shopid'] = $shopid;
		if(in_array($navid,array('index','notice','album','good','consume','groupbuy'))){
			$arr_data['type']='sys';
		}else{
			$arr_data['type']='shop';
		}

		$navid = inserttable('nav',$arr_data,1);
		if(empty($arr_data['flag'])) {
			updatetable('nav',array('flag'=>'nav_'.$navid),'navid='.$navid);
		}
	} elseif($_GET['op']=='edit') {
		updatetable('nav', $arr_data, 'navid='.$navid.' and shopid='.$shopid);
	}
	if($_GET['op'] == 'add') {
		cpmsg('message_success', '?action=nav');
	} else {
		cpmsg('update_success', '?action=nav');
	}
}

// 处理提交的删除操作
if($_GET['op']=='del' && !empty($navid)) {
	$_BCACHE->deltype('detail', 'nav', $_G['myshopid']);
	$navitem = DB::result_first('select type from '.tname('nav')." WHERE navid='$navid' AND shopid='$_G[myshopid]'");
	if($navitem!='sys'){
		DB::query('DELETE FROM '.tname('nav')." WHERE navid='".$navid."';");
	}else{
		cpmsg('The SYS nav cannot be deleted .');
	}
	cpmsg('message_success', '?action=nav');
}

// 更新排序
if(!empty($_POST['listsubmit'])) {
	$_nav->check_shop_nav($_G['myshopid']);
	$_BCACHE->deltype('detail', 'nav', $_G['myshopid']);
	$item = array();
	foreach($_POST['display'] as $key=>$value) {
		$query = '';
		$value = intval($value);
		if($value > 0) {
			$query = 'UPDATE '.tname('nav')." SET displayorder='$value' WHERE navid='$key' AND shopid='$_G[myshopid]'";
			DB::query($query);
		}
	}
	cpmsg('message_success', '?action=nav');
}

if($_GET['op']=='add' || $_GET['op']=='edit') {

	shownav('shop', 'nav_'.$_GET['op']);
	showsubmenu('nav_'.$_GET['op']);
	showformheader('nav&op='.$_GET['op']);
	showtableheader('');
	if($_GET['op']=='edit'){
		if($navid>0){
			$query = DB::query('select * from '.tname('nav').' where navid='.$navid);
			$navitem = DB::fetch($query);
		}else{
			$navitem = $nav_default[$navid];
		}
	}else{
		$navitem['available']=1;
	}

	showsetting('nav_name', 'subject', $navitem['name'], 'text', '', '', '', ' style=\''.pktitlestyle($navitem['highlight']).'\'', '<span style="color:red">*</span>');
	showstyletitle('nav');
	if($navitem['type']!='sys'){
		showsetting('nav_url', 'url', $navitem['url'], 'text');
	}
	showsetting('nav_target', 'nav_target', $navitem['target'], 'radio');
	showsetting('nav_available', 'available', $navitem['available'], 'radio');
	showsetting('nav_displayorder', 'displayorder', $navitem['displayorder'], 'number');
	showhiddenfields(array('navid' => $navid));
	showsubmit('valuesubmit');
	showtablefooter();
	showformfooter();
	bind_ajax_form();
} else {

	shownav('shop', 'nav_list');
	showsubmenu('nav_list');
	showformheader('nav');
	showtableheader('');
	showsubtitle(array('display_order', 'nav_name','nav_target','nav_available','nav_type','operation'));

	$sql = 'SELECT * FROM '.tname('nav')." WHERE (type='sys' or type='shop') and shopid='$shopid' order by displayorder";

	$query = DB::query($sql);
	$rowitems =array();
	while($value = DB::fetch($query)){
		$rowitems[$value['flag']] = $value;
	}

	// 合并用户和默认导航设置
	foreach($nav_default as $key => $value) {
		if(!isset($rowitems[$key])) {
			$rowitems[$key] = $value;
		}
		$rowitems[$key]['url'] = $value['url'];
	}

	foreach($rowitems as $k=>$value){
		$rowItem = array();
		$rowItem[] = '<input name="display['.$value['navid'].']" type="text" size="2" value="'.$value['displayorder'].'" />';
		$rowItem[] = empty($value['url'])?($value['name']):('<a href="'.$value['url'].'" target="_blank">'.$value['name'].' </a>');
		$rowItem[] = ($value['target']==1)?lang('nav_target_blank'):lang('nav_display_self');
		$rowItem[] = ($value['available']==1)?lang('nav_display_normal'):lang('nav_display_none');
		$rowItem[] = ($value['type']=='sys')?lang('nav_type_sys'):lang('nav_type_normal');
		$rowItem[] = '[<a href="?action=nav&op=edit&navid='.$value['navid'].'">'.lang('edit').'</a>]'.
			(($value['type']=='sys')?'':('[<a href="?action=nav&op=del&navid='.$value['navid'].'">'.lang('nav_del').'</a>]'));

		showtablerow('', array(), $rowItem);
	}
	showtablerow('', array(), array('','<a class="addtr" href="?action=nav&op=add">'.lang('nav_add').'</a>','','',''));
	showsubmit('listsubmit');
	showtablefooter();
	showformfooter();
	bind_ajax_form();
}
?>