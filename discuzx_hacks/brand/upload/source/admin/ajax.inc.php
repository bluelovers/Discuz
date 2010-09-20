<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: ajax.inc.php 4446 2010-09-14 11:35:06Z xuhui $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

$opt = $_GET['opt'];
if ($opt == "edit_album_subject") {
	$album_id = intval($_GET['album_id']);
	$subject = biconv(trim($_GET['subject']), 'UTF-8', $_G['charset']);
	
	$subject_old = DB::result_first("select subject from ".tname('albumitems'). " where itemid=".$album_id);

	if ($subject_old != $subject) {
		DB::query('update '.tname('albumitems').' set subject=\''.$subject.'\' where itemid='.$album_id);
		if(!empty($album_id)) {
			require_once(B_ROOT.'./api/bbs_syncpost.php');
			syncalbum($album_id);
		}
		$_BCACHE->deltype('all', 'album', 0 , $album_id);
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
		$_BCACHE->deltype('all', 'photo', 0 , $photo_id);
		die('OK');
	} else {
		die('NO-MODIFY');
	}
	exit;
}

$value = array();
$catid = intval($_GET['catid']);
$groupid = intval($_GET['groupid']);
$shopid = intval($_GET['shopid']);
$catType = $type = trim($_GET['type']);
$relatedtype = trim($_GET['relatedtype']);

if ($opt == 'getshop' && $_GET['catid']>0) {
	$catid = intval($_GET['catid']);
	$sql = 'SELECT itemid, subject FROM '.tname('shopitems')." WHERE groupid = '$catid' LIMIT 0,50";
	$query = DB::query($sql);
	echo "<option value=\"0\">".lang('please_select')."</option>\n";
	while($res = DB::fetch($query)) {
		echo "<option value=\"$res[itemid]\">$res[subject]</option>\n";
	}

} elseif ($opt == 'getCat' && $_GET['catid']>0){

	// 取出店舖組下可用的分類
	$catid = intval($_GET['catid']);
	$sql = 'select * from '.tname('shopgroup').' where type=\'shop\' and id='.$catid;
	$aviable_field = DB::fetch(DB::query($sql));
	if(empty($aviable_field[$catType.'_field'])){
		echo '<option value="0">'.lang('please_select')."</option>\n";
	}else{
		echo '<option value="0" selected="selected">'.lang('please_select')."</option>\n";
		$catidinsql = $aviable_field[$catType.'_field'] == 'all' ? '' : 'catid in ('.$aviable_field[$catType.'_field'].') AND';
		$sql = 'SELECT catid, name  FROM '.tname('categories').' WHERE '.$catidinsql.' type=\''.$catType.'\' LIMIT 0,50';
		$_query = DB::query($sql);
		while($res = DB::fetch($_query)) {
			echo "<option value=\"$res[catid]\">$res[name]</option>\n";
		}
	}
} elseif($opt == 'getallCat' && $_GET['groupid'] > 0){

	// 取出店舖組下可用的分類
	$sql = 'select * from '.tname('shopgroup').' where type=\'shop\' and id='.$groupid;
	$aviable_field = DB::fetch(DB::query($sql));
	if(empty($aviable_field[$catType.'_field'])){
		echo '<option value="0">'.lang('please_select')."</option>\n";
	} else {
		$catidinsql = $aviable_field[$catType.'_field'] == 'all' ? '' : 'catid in ('.$aviable_field[$catType.'_field'].') AND';
		$sql = 'SELECT *  FROM '.tname('categories').' WHERE '.$catidinsql.' type=\''.$catType.'\' LIMIT 0,50';
		$_query = DB::query($sql);
		while($res = DB::fetch($_query)) {
			$cats[$res['catid']] = $res;
		}
		include_once(B_ROOT.'./source/class/tree.class.php');
		$tree = new Tree($type);
		$miniupid = '';
		$space = '|----';
		foreach($cats as $catid=>$category) {
			if($miniupid === '') $miniupid = $category['upid'];
			$tree->setNode($category['catid'], $category['upid'], $category);
		}
		if(count($cats) > 0) {
			$categoryarr = $tree->getChilds($miniupid);
			foreach ($categoryarr as $key => $catid) {
				$cat = $tree->getValue($catid);
				$cat['pre'] = $tree->getLayer($catid, $space);
				$cat['havechild'] = $tree->getChild($catid) ? 1: 0;
				$listarr[$cat['catid']] = $cat;
			}
		}
		echo '<option value="0" selected="selected">'.lang('please_select')."</option>\n";
		foreach($listarr as $catinfo) {
			echo '<option value="'.$catinfo['catid'].'">'.$catinfo['pre'].$catinfo['name'].'</option>';
		}
	}
} elseif($opt == 'search') {

	//關聯信息中搜索該商舖下的關聯對像
	$shopid = intval($_GET['shopid']);
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
		echo '<script relatedtype="text/javascript" charset="'.$_G['charset'].'">alert(\''.lang('noresult_content').'\')</script>';
	}
} elseif ($opt == 'previewconsume') {

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

} elseif ($opt == 'getThread' && $_GET['tid']>0) {
	//管理員專用
	require_once(B_ROOT.'./api/bbs_pic.php');
	showxmlheader('GBK');
	echo '<threadinfo>';
	echo showarraytoxml(list_threads(intval($_GET['tid'])),'GBK');
	echo '</threadinfo>';

} else {
	//其他情況
	echo "<option value=\"0\" selected=\"selected\">".lang('please_select')."</option>\n";
}

?>