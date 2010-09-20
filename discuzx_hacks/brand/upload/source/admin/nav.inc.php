<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: nav.inc.php 4337 2010-09-06 04:48:05Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

$navid = empty($_REQUEST['navid'])?null:intval($_REQUEST['navid']);


if(!empty($_POST['valuesubmit'])){
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
		$query = DB::fetch(DB::query('SELECT max(displayorder) as displayorder FROM '.tname('nav').' WHERE (type=\'sys\' or type=\'site\') and shopid=0'));
		$arr_data['displayorder'] = intval($query['displayorder'])+1;
	}

	if($_GET['op']=='add') {
		$arr_data['shopid'] = 0;
		$arr_data['type']='site';

		$navid = inserttable('nav',$arr_data,1);
		if(empty($arr_data['flag'])) {
			updatetable('nav',array('flag'=>'nav_'.$navid),'navid='.$navid);
		}
	} elseif($_GET['op']=='edit') {
		updatetable('nav', $arr_data, 'navid='.$navid);
	}
	updatesettingcache();
	if($_GET['op'] == 'add') {
		cpmsg('message_success', '?action=nav');
	} else {
		cpmsg('update_success', '?action=nav');
	}
}elseif(!empty($_POST['listsubmit'])) {
	$item = array();
	foreach($_POST['display'] as $key=>$value) {
		$query = '';
		$key = intval($key);
		$value = intval($value);
		if($key>0 && $value>0) {
			$query = 'UPDATE '.tname('nav').' SET displayorder=\''.$value.'\' WHERE navid=\''.intval($key).'\'; ';
			DB::query($query);
		}
	}
	updatesettingcache();
	cpmsg('message_success', '?action=nav');
}

// 處理提交的刪除操作
if($_GET['op']=='del' && !empty($navid)) {

	$navitem = DB::result_first('select type from '.tname('nav').' where navid='.$navid);
	if($navitem!='sys'){
		DB::query('DELETE FROM '.tname('nav')." WHERE navid='".$navid."';");
		updatesettingcache();
	}else{
		cpmsg('The SYS nav cannot be deleted .');
	}

	header('Location: ?action=nav');
}elseif($_GET['op'] == 'batachdel'){
	$delids = $_POST['delids'];
	foreach($delids as $id){
		$navitem = DB::result_first('select type from '.tname('nav').' where navid='.$id);
		if($navitem!='sys'){
			DB::query('DELETE FROM '.tname('nav')." WHERE navid='".$id."';");
			updatesettingcache();
		}else{
			cpmsg('The SYS nav cannot be deleted .');
		}
	}
	cpmsg('message_success', '?action=nav');
	exit;
}elseif($_GET['op'] == "searchsubmit"){

	shownav('global', 'nav_search');
	showsubmenu('nav_list', array(
		array('nav_list', 'nav', '0'),
		array('nav_add', 'nav&op=add', '0'),
		array('nav_search', 'nav&op=search', '1')
	));
	$sql_where = "";

	if(!empty($_POST['shop_item'])){
		$sql_where .= ' and shop.itemid='. $_POST['shop_item'].'';
	}else{
		if(!empty($_POST['shop_subject'])){
			$sql_where .= ' and shop.subject like \'%'. trim($_POST['shop_subject']).'%\'';
		}
		if(!empty($_POST['groupid'])){
			$sql_where .= ' and shop.groupid='. $_POST['groupid'];
		}
	}
	$sql = 'SELECT count(*) AS count FROM '.tname('nav').' as nav left join '.tname('shopitems')." as shop on nav.shopid=shop.itemid where nav.type='shop' $sql_where limit 100;";

	$tpp = 50;
	$page = $_GET['page'] > 0 ? intval($_GET['page']) : 1;
	$mlstart = ($page - 1) * $tpp;
	$query = DB::query($sql);
	$value = DB::fetch($query);
	foreach($_REQUEST as $key=>$_value) {
		if(in_array($key, array('action', 'op', 'shop_item', 'shop_subject', 'groupid'))) {
			$url .= '&'.$key.'='.rawurlencode($_value);
		}
	}
	$url = '?'.substr($url, 1);
	$multipage = multi($value['count'], $tpp, $page, 'admin.php'.$url, $phpurl=1);

	showformheader('nav&op=batachdel');
	showtableheader('');
	showsubtitle(array('delete','shop_subject','nav_name','nav_url','operation'));
	$sql = "SELECT shop.itemid as shopid,shop.subject as shopname,nav.navid as navid,nav.name as navname,nav.url as navurl FROM ".tname('nav')." as nav left join ".tname('shopitems')." as shop on nav.shopid=shop.itemid where nav.type='shop' $sql_where limit ".$mlstart.", ".$tpp.";";
	$query = DB::query($sql);
	while($value = DB::fetch($query)){
		$rowItem = array();
		$rowItem[] = '<input type="checkbox" checked="" value="'.$value['navid'].'" name="delids[]" class="checkbox">';
		$rowItem[] = '<a href="store.php?id='.$value['shopid'].'" target="_blank">'.$value['shopname'].' </a>';
		$rowItem[] = '<a href="'.$value['navurl'].'" target="_blank">'.$value['navname'].' </a>';
		$rowItem[] = '<a href="'.$value['navurl'].'" target="_blank">'.$value['navurl'].' </a>';
		$rowItem[] = '[<a href="?action=nav&op=del&navid='.$value['navid'].'">'.lang('nav_del').'</a>]';

		showtablerow('', array(), $rowItem);
	}

	showsubmit('delsubmit');
	showtablefooter();
	showformfooter();
	bind_ajax_form();

	echo $multipage;
}elseif($_GET['op'] == 'search') {
	shownav('global', 'nav_'.$_GET['op']);
	showsubmenu('nav_list', array(
		array('nav_list', 'nav', '0'),
		array('nav_add', 'nav&op=add', '0'),
		array('nav_search', 'nav&op=search', '1')
	));

	$query = DB::query("SELECT * FROM ".tname("shopgroup")." ORDER BY id ASC;");
	while($result = DB::fetch($query)) {
		$catstr .= '<option value="'.$result['id'].'">'.$result['title'].'</option>';
	}
	showtips('nav_search_tips');
	showformheader('nav&op=searchsubmit');
	showtableheader('nav_search_title', 'notop');

	$search_items[]=lang($mname.'_subject').'::<input style="width:250px;" type="text" name="shop_subject" value="'.$_POST['shop_subject'].'" size="10" />';
	$search_items[]=lang($mname.'_itemid').'::<input style="width:250px;" type="text" name="shop_itemid" value="'.$_POST['shop_itemid'].'" size="3" />';
	$search_items[] = lang($mname.'_catid').'::<select style="width:250px;" name="groupid" id="shop_incat"><option value="0">'.lang('please_select').'</option>'.$catstr.'</select>';
	foreach($search_items as $k=>$v){
		$tmp=explode('::',$v);
		showsetting($tmp[0], '', '',$tmp[1]);
	}
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
}elseif($_GET['op'] == 'add' || $_GET['op'] == 'edit') {
	shownav('global', 'nav_'.$_GET['op']);
	showsubmenu('nav_list', array(
		array('nav_list', 'nav', '0'),
		array('nav_'.(($_GET['op']=='add')?'add':'edit'), 'nav&op=add', '1'),
		array('nav_search', 'nav&op=search', '0')
	));

	showformheader('nav&op='.$_GET['op']);
	showtableheader('');
	if($_GET['op']=='edit'){
		if($navid>0){
			$query = DB::query('select * from '.tname('nav').' where navid='.$navid);
			$navitem = DB::fetch($query);
		}else{
			cpmsg('ID ERROR .');
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
	showhiddenfields(array('navid' => $_GET['navid']));
	showsubmit('valuesubmit');
	showtablefooter();
	showformfooter();
	bind_ajax_form();
} else {
	//沒有提交數據的列表頁
	shownav('global', 'nav_list');
	showsubmenu('nav_list', array(
		array('nav_list', 'nav', '1'),
		array('nav_add', 'nav&op=add', '0'),
		array('nav_search', 'nav&op=search', '0')
	));
	showformheader('nav');
	showtableheader('');
	showsubtitle(array('display_order', 'nav_name','nav_target','nav_available','nav_type','operation'));
	$query = DB::query('SELECT * FROM '.tname('nav').' WHERE (type=\'sys\' or type=\'site\') and shopid=0 order by displayorder');
	while($value = DB::fetch($query)){
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