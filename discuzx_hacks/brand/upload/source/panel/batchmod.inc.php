<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: batchmod.inc.php 4473 2010-09-15 04:04:13Z fanshengshuai $
 */

if(!defined('IN_STORE')) {
	exit('Acess Denied');
}

@set_time_limit(100);
//删除二次确认之前，写入跳转cookie
if(empty($_POST['confirmed'])) {
	ssetcookie('batch_referer', $_SERVER['HTTP_REFERER'], 300);
	$cookie_referer = $_G['cookie']['batch_referer'] = $_SERVER['HTTP_REFERER'];
} else {
	$cookie_referer = $_G['cookie']['batch_referer'];
}

$items = $opsql = $passuids = $query = '';
$_POST['opcheck'] = intval($_POST['opcheck']);
$_POST['opallowreply'] = intval($_POST['opallowreply']);
$_POST['opdiscount'] = intval($_POST['opdiscount']);
$_POST['opdelete'] = intval($_POST['opdelete']);

if(empty($_POST['item']) && !in_array($_REQUEST['operation'], array('display', 'setalbumimg'))) {
	cpmsg('please_select_items', '', '', '', true, true);
} else {
	foreach($_POST['item'] as $value) {
		$items .= '\''.intval($value).'\' , ';
	}
	$items = substr($items, 0, -2);
}
if($_REQUEST['operation'] == 'setalbumimg') {
	$mname = 'album';
}
$gradesql = '';
if($mname != 'album' && $mname != 'photo') {
	//$gradesql = ' AND grade>1';
}
$wheresql = " itemid IN ($items) AND shopid='$_G[myshopid]' $gradesql";

if($mname=='shop') {
	cpmsg('no_perm', $cookie_referer);
}

//普通用户只允许操作自己的
if($_G['myshopstatus'] == 'verified') {
	if($mname == 'shop') {
		$wheresql .= ' AND itemid=\''.$_G['myshopid'].'\'';
	} else {
		$wheresql .= ' AND shopid=\''.$_G['myshopid'].'\'';
	}
} else {
	cpmsg('no_perm', $cookie_referer);
}

require_once(B_ROOT.'./source/adminfunc/tool.func.php');

//删除列表、以及对应物件的缓存
batchmodpaneldeletecache($mname);

//操作方法
$opsql = 'UPDATE '.tname($mname.'items').' SET ';
switch($_REQUEST['operation']) {
	case 'display':
		changedisplayorder($_POST['display'], $mname, ' AND '.$wheresql);
		break;
	case 'check':
		/*
		if($_SGLOBAL['panelinfo']['group']['verify'.$mname]) {
		    $itemss = $items;
			$query = DB::query("SELECT grade,itemid FROM ".tname($mname."items")." WHERE itemid IN ($items) AND shopid='$_G[myshopid]'");
			while($res = DB::fetch($query)) {
				if($res['grade'] == 0 || $res['grade'] == 5) {
					$itemss = str_replace("'$res[itemid]'", "'0'", $itemss);
				}
			}
			$wheresql = str_replace("($items)", "($itemss)", $wheresql);
		}
		*/
		if($_POST['opcheck'] != 2 && $_POST['opcheck'] != 3) {
			//print_r($_POST);exit();
			cpmsg('Error', '', '', '', true, true);
		}
		$opsql .= ' grade=\''.$_POST['opcheck'].'\'';
		$wheresql .= ' AND grade>1';
		break;
	case 'allowreply':
		$opsql .= ' allowreply=\''.$_POST['opallowreply'].'\'';
		break;
	case 'delete':
		if(empty($_POST['confirmed'])) {
			$extra_input = '';
			foreach($_POST['item'] as $value) {
				$extra_input .= '<input type="hidden" name="item[]" value="'.$value.'" />';
			}
			$extra_input .= '<input type="hidden" name="operation" value="delete" /><input type="hidden" name="opdelete" value="'.$_POST['opdelete'].'" />';
			cpmsg('mod_delete_confirm', $BASESCRIPT.'?action=batchmod&m='.$mname, 'form', $extra_input);//二次确认
		} else {
			if(in_array($mname, array('good', 'notice', 'consume', 'groupbuy'))) {
				$subnum = DB::result_first("SELECT count(*) FROM ".tname($mname."items")." i INNER JOIN ".tname($mname.'message')." m ON i.itemid=m.itemid  WHERE i.itemid IN ($items) and i.shopid = '$_G[myshopid]'");
			} elseif($mname == 'album') {
				$subnum = DB::result_first("SELECT count(*) FROM ".tname($mname."items")." WHERE itemid IN ($items) and shopid = '$_G[myshopid]' and frombbs = 0");
			}

			if(in_array($mname, array('good', 'notice', 'consume', 'groupbuy', 'album')))
				itemnumreset($mname, $_G['myshopid'], $do = 'sub', $subnum);
			delmitems($wheresql, $mname);

			$opsql = $wheresql = '';
			cpmsg('message_success', $cookie_referer);
		}
		break;
	case 'update_album_info':
		update_album_info($_POST['subject'], $mname, $wheresql);
		break;
	case 'album_movecat':
		album_movecat_panel($wheresql);
		break;
	case 'setalbumimg':
		setalbumimg();
		break;
	default:
		cpmsg('no_operation', '', '', '', true, true);
}
if(!empty($opsql) && !empty($wheresql)) {
	DB::query($opsql.' WHERE '.$wheresql);
}

cpmsg('message_success', $cookie_referer);

?>