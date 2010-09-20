<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: managelog.inc.php 4324 2010-09-04 07:08:16Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

require_once(B_ROOT.'./source/adminfunc/tool.func.php');

shownav('oplog', 'managelog_list');
showsubmenu('managelog_list');
showtips('managelog_list_tips');

if(submitcheck('filtersubmit')) {

	showformheader('managelog');
	showtableheader('');
	showsubtitle(array('managelogusername', 'managelogobject', 'managelogedshopname', 'managelogop', 'managelogreason', 'mldateliane'));

	$wheresql = '';
	$wheresql .= !empty($_REQUEST['managelogtype']) ? ' AND type=\''.trim($_REQUEST['managelogtype']).'\'' : '';
	$wheresql .= !empty($_REQUEST['shopid']) ? ' AND shopid=\''.intval($_REQUEST['shopid']).'\'' : '';
	if(!empty($wheresql)) {
		$wheresql = ' WHERE'.substr($wheresql, 4);
	}

	$managelog = $managelogarr = array();
	$tpp = 15;
	$page = $_GET['page'] > 0 ? intval($_GET['page']) : 1;
	$mlstart = ($page - 1) * $tpp;
	$query = DB::query("SELECT count(mlogid) AS count  FROM ".tname('managelog').$wheresql.";");
	$value = DB::fetch($query);
	foreach($_GET as $key=>$_value) {
		if(in_array($key, array('action', 'formhash', 'filtersubmit', 'managelogtype', 'shopid'))) {
			$url .= '&'.$key.'='.$_value;
		}
	}
	$url = '?'.substr($url, 1);
	$multipage = multi($value['count'], $tpp, $page, 'admin.php'.$url, $phpurl=1);
	$query = DB::query("SELECT * FROM ".tname('managelog').$wheresql." ORDER BY dateline DESC LIMIT ".$mlstart.", ".$tpp.";");
	while($managelog = DB::fetch($query)) {
		$managelog['shopname'] = DB::result_first("SELECT subject FROM ".tname('shopitems')." WHERE itemid='$managelog[shopid]'");
		$managelog['itemname'] = DB::result_first("SELECT subject FROM ".tname($managelog['type'].'items')." WHERE itemid='$managelog[itemid]'");
		$managelog['typename'] = $lang['header_'.$managelog['type']];
		$managelog['shortreason'] = cutstr($managelog['reason'], 60, 1);
		$managelogarr[] = $managelog;
	}
	foreach($managelogarr as $value) {
		showtablerow('', array(), array($_G['username'], '<a href=\'store.php?id='.$value['shopid'].'&action='.$value['type'].'&xid='.$value['itemid'].'\' target=\'_blank\'>'.$value['itemname'].'</a>', '<a href=\'store.php?id='.$value['shopid'].'\' target=\'_blank\'>'.$value['shopname'].'</a>', $value['typename'].$_SGLOBAL['shopgrade'][$value['opcheck']], '<span title="'.$value['reason'].'">'.$value['shortreason'].'</span>', date('Y-m-d', $value['dateline'])));
	}
	showtablefooter();
	echo $multipage;
	showformfooter();

} else {
	show_searchform_managelog();
}
?>