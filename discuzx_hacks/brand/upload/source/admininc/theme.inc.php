<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: theme.inc.php 4442 2010-09-14 09:43:34Z yumiao $
 */

if(!defined('IN_STORE') && !defined('IN_ADMIN')) {
	exit('Acess Denied');
}
if(!$wheresql) {
	cpmsg('no_wheresql');
}

if($_GET['op']=='usetheme') {
	//提交数据的处理
	$themeid = intval(substr($_GET['theme'], 1));
	$query = DB::query("UPDATE ".tname("shopitems")." SET themeid = '$themeid' WHERE $wheresql LIMIT 1");
	if(DB::affected_rows($query)) {
		cpmsg('update_success', $BASESCRIPT.'?action=theme&m=shop&itemid='.$_GET['itemid']);
	}

} else {

	//取得信息
	$query = DB::query('SELECT itemid, subject, themeid FROM '.tname('shopitems').' WHERE '.$wheresql.' ORDER BY itemid DESC LIMIT 1');
	$editvalue = DB::fetch($query);
	if(empty($editvalue)) {
		cpmsg('no_item', $BASESCRIPT.'?action=list&m='.$mname);
	}
	//显示导航以及表头
	$subjectnav = $BASESCRIPT == 'admin.php' ? $editvalue['subject'] : '';
	shownav($mname, $mname.'_'.$_GET['action'], $subjectnav);
	if(pkperm('isadmin')) {
		$shopmenu = array(
				array('shop_edit', 'edit&m=shop&itemid='.$_GET['itemid']),
				array('menu_shop_theme', 'theme&m=shop&itemid='.$_GET['itemid'], 1),
				array('menu_modifypasswd', 'modifypasswd&m=shop&itemid='.$_GET['itemid'], 0)
				);
		if($_G['setting']['enablemap'] == 1) {
			array_push($shopmenu, array('menu_shop_map', 'map&m=shop&itemid='.$_GET['itemid']));
		}
		showsubmenu('menu_shop_theme', $shopmenu);
	} else {
		showsubmenu($mname.'_'.$_GET['action']);
	}
	showtips('theme_tips');
	showformheader('theme');
	echo '<div id="theme_list">';
	showthistheme('default');
	$tpl = dir(B_ROOT.'./templates/store/');
	$tpl->handle;
	while($entry = $tpl->read()) {
		if(strpos($entry, 't')===0 && file_exists(B_ROOT.'./templates/store/'.$entry.'/preview.jpg')) {
			showthistheme($entry);
		}
	}
	$tpl->close();
	echo '</div><div style="clear:both;"></div>';
	showformfooter();
}

function showthistheme($entry) {
	global $_G, $editvalue;
	$usenow = lang('theme_now');
	echo '<div class="theme_div">
		<div calss="theme_imgdiv">
		<a target="_blank" href="store.php?id='.$editvalue['itemid'].'&op=preview&theme='.$entry.'"><img style="width: 110px; height: 120px;" alt="'.lang('theme_'.$entry.'_name').'" src="'.B_URL.'/templates/store/'.$entry.'/preview.jpg'.'"></a>
		</div>

		<div class="theme_desc">
		<a target="_blank" href="store.php?id='.$editvalue['itemid'].'&op=preview&theme='.$entry.'"><strong>'.lang('theme_'.$entry.'_name').'&nbsp;'.($editvalue['themeid']?($entry=='t'.$editvalue['themeid']?$usenow:''):($entry=='default'?$usenow:'')).'</strong></a>
		<br />';
	if(($editvalue['themeid'] && ($entry == 't'.$editvalue['themeid'])) || (!$editvalue['themeid'] && $entry == 'default')) {
		echo lang('theme_prev').' | '.lang('theme_use').'';
	} else {
		echo '<a target="_blank" href="store.php?id='.$editvalue['itemid'].'&op=preview&theme='.$entry.'">'.lang('theme_prev').'</a> | <a href="'.$BASESCRIPT.'?action=theme&itemid='.$editvalue['itemid'].'&op=usetheme&theme='.$entry.'">'.lang('theme_use').'</a>';
		
	}
		echo '</div>
		</div>';
}
?>