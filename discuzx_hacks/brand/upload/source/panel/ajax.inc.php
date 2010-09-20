<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: ajax.inc.php 4446 2010-09-14 11:35:06Z xuhui $
 */

if(!defined('IN_STORE')) {
	exit('Acess Denied');
}

$opt = $_GET['opt'];
if ($opt == "edit_album_subject") {
	$album_id = intval($_GET['album_id']);
	$subject = biconv(trim($_GET['subject']), 'UTF-8', $_G['charset']);
	
	$subject_old = DB::result_first("select subject from ".tname('albumitems'). " where uid=".$_G['uid']." and itemid=".$album_id);

	if ($subject_old != $subject) {
		DB::query('update '.tname('albumitems').' set subject=\''.$subject.'\' where uid='.$_G['uid'].' and itemid='.$album_id);
		if(!empty($album_id)) {
			require_once(B_ROOT.'./api/bbs_syncpost.php');
			syncalbum($album_id);
		}
		die('OK');
	} else {
		die('NO-MODIFY');
	}
	exit;
} elseif ($opt == "edit_photo_subject") {
	$photo_id = intval($_GET['photo_id']);
	$subject = biconv(trim($_GET['subject']), 'UTF-8', $_G['charset']);
	
	$subject_old = DB::result_first("select subject from ".tname('photoitems'). " where itemid=".$photo_id);

	if ($subject_old != $subject) {
		DB::query('update '.tname('photoitems').' set subject=\''.$subject.'\' where itemid='.$photo_id);
		die('OK');
	} else {
		die('NO-MODIFY');
	}
	exit;
}

$value = array();
$catid = intval($_GET['catid']);
$groupid = intval($_GET['groupid']);
$shopid = intval($_G['myshopid']);
$catType = $type = trim($_GET['type']);
$relatedtype = trim($_GET['relatedtype']);

if($_GET['opt']=='getCat' && $_GET['catid'] > 0) {

	// 取出店舖組下可用的分類
	$mycats = mymodelcategory($catType);
	if($mycats){
		foreach($mycats as $cat) {
			echo "<option value=\"$cat[catid]\">$cat[name]</option>\n";
		}
	} else {
		echo '<option value="0">'.lang('please_select')."</option>\n";
	}

} elseif($opt == 'getallCat' && $_GET['groupid'] > 0) {

	// 取出店舖組下可用的分類
	$mycats = mymodelcategory($catType);
	if($mycats){
		echo '<option value="0" selected="selected">'.lang('please_select')."</option>\n";
		foreach($mycats as $catinfo) {
			echo '<option value="'.$catinfo['catid'].'">'.$catinfo['pre'].$catinfo['name'].'</option>';
		}
	} else {
		echo '<option value="0">'.lang('please_select')."</option>\n";
	}
} elseif($_GET['opt'] == 'search') {

	//關聯信息中搜索該商舖下的關聯對像
	$itemid = !empty($_GET['itemid']) ? intval($_GET['itemid']) : '';
	$keyword = trim($_GET['keyword']);
	$keyword = biconv($keyword, 'UTF-8', $_G['charset']);
	$wheresql = ' WHERE shopid=\''.$shopid.'\'';
	$wheresql .= $catid != 0 ? ' AND catid=\''.$catid.'\'' : '';
	$wheresql .= !empty($keyword) ? ' AND subject LIKE \'%'.$keyword.'%\'' : '';
	$wheresql .= ' AND grade>\'2\'';
	$query = DB::query('SELECT itemid, subject FROM '.tname($relatedtype.'items').$wheresql.';');
	$i = 0;
	while($resultlist = DB::fetch($query)) {
		if(!empty($itemid)) {
			$relatedid = DB::result_first("SELECT itemid FROM ".tname("relatedinfo")." WHERE itemid='$itemid' AND relatedid='$resultlist[itemid]' AND relatedtype='$relatedtype'");
			if(!$relatedid && $itemid != $resultlist['itemid']) {
				$i++;
				echo "<option value=\"$relatedtype@$resultlist[itemid]\" ".($i == 1 ? 'selected="selected"' : '').">$resultlist[subject]</option>\n";
			}
		} else {
			$i++;
			echo "<option value=\"$relatedtype@$resultlist[itemid]\" ".($i == 1 ? 'selected="selected"' : '').">$resultlist[subject]</option>\n";
		}
	}
	if($i == 0) {
		echo '<script type="text/javascript" charset="'.$_G['charset'].'">alert(\''.lang('noresult_content').'\')</script>';
	}

} elseif($_GET['opt'] == 'previewconsume') {

	$id = intval($_GET['id']);
	$shopid = intval($_GET['shopid']);
	$coupon_title = trim($_GET['coupon_title']);
	$brief = trim($_GET['brief']);
	$exception = trim($_GET['exception']);
	$shopinfo = DB::fetch(DB::query("SELECT subject, address, tel FROM ".tname('shopitems')." WHERE itemid='$shopid'"));
	$coupon_title = biconv($coupon_title, 'UTF-8', $_G['charset']);
	$brief = biconv($brief, 'UTF-8', $_G['charset']);
	$exception = biconv($exception, 'UTF-8', $_G['charset']);
	$createimgarr = array(
		'preview' => 1,
		'id' => $id,
		'coupon_title' => $coupon_title,
		'dealer_name' => $shopinfo['subject'],
		'begin_date' => $_GET['begin_date'],
		'end_date' => $_GET['end_date'],
		'brief' => $brief,
		'exception' => $exception,
		'address' => $shopinfo['address'],
		'hotline' => $shopinfo['tel']
	);
	require_once(B_ROOT.'./source/adminfunc/tool.func.php');
	if($consumeimgpath = image_text($createimgarr)) {
		echo '<img width="750px" height="466px" src="'.getattachurl($consumeimgpath).'?time='.$_G['timestamp'].'">';
	}

} else {
	//其他情況
	echo "<option value=\"0\" selected=\"selected\">".lang('please_select')."</option>\n";
}

?>