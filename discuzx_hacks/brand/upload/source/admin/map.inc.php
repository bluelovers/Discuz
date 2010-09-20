<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: map.inc.php 4360 2010-09-07 08:03:59Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

if(!empty($_POST['valuesubmit'])) {
	//print_r($_POST);exit();
	$query = DB::query('UPDATE '.tname('shopmessage')." SET mapapimark = '$_POST[inputmap]' WHERE itemid = '$_POST[itemid]'");
	$_BCACHE->deltype('detail', 'shop', $_POST['itemid']);
	cpmsg('update_success', 'admin.php?action=map&itemid='.$_POST['itemid']);
} else {
	$shopmenu = array(
		array('shop_edit', 'edit&m=shop&itemid='.$_GET['itemid']),
		array('menu_shop_theme', 'theme&m=shop&itemid='.$_GET['itemid']),
	);
	if($_G['setting']['enablemap'] == 1) {
		array_push($shopmenu, array('menu_shop_map', 'map&m=shop&itemid='.$_GET['itemid'], 1));
	}
	showsubmenu('shop_edit', $shopmenu);
	$wheresql = ' i.itemid='.$_GET['itemid'];

	//取得信息
	$query = DB::query('SELECT i.itemid,m.nid,m.mapapimark,i.address FROM '.tname('shopitems').' i INNER JOIN '.tname('shopmessage').' m ON i.itemid=m.itemid WHERE '.$wheresql.' ORDER BY i.itemid DESC LIMIT 1');
	$editvalue = DB::fetch($query);
	if(empty($editvalue)) {
		cpmsg('no_item', 'admin.php?action=list&m='.$mname);
	}
	//显示导航以及表头
	shownav($mname, $mname.'_'.$_GET['action']);
	showsubmenu($mname.'_'.$_GET['action']);
	showformheader('map');
	showtableheader();

	showmapsetting('shop', $_G['setting']['mapapikey'], $editvalue['mapapimark']); //显示地图设置
	showhiddenfields(array('itemid' => $editvalue['itemid']));
	showhiddenfields(array('nid' => $editvalue['nid']));
	showhiddenfields(array('valuesubmit' => 'yes'));
	echo '<tr><td colspan="15"><div class="fixsel"><input type="submit" value="'.lang('mapsubmit').'" name="settingsubmit" id="submit_settingsubmit" class="btn"> <input type="button" class="btn"  name="" value="'.lang('reset').'" id="resm" /></div></td></tr>';
	showtablefooter();
	showformfooter();
}
?>