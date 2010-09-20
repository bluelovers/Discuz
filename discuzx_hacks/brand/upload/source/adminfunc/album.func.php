<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: album.func.php 4379 2010-09-09 03:00:50Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Acess Denied');
}

/**
 * 創建相冊
 * @param
 * @return 相冊id
 */
function createalbum($shopid=0, $catid=0, $uid=0, $username='', $subject='', $description='') {
	global $_G, $_SGLOBAL;
	$arr_data = array();
	//id
	foreach(array('shopid', 'catid', 'uid') as $value) {
		$$value = intval($$value);
	}
	//字符串
	foreach(array('subject', 'description') as $value) {
		$$value = trim(strip_tags($$value));
	}
	//判斷必填，設置插入數據庫的數據
	foreach(array('shopid', 'catid', 'uid', 'username', 'subject') as $value) {
		if(empty($$value)) {
			cpmsg($value.'_not_selected', '', '', '', true, true);
		}
		$arr_data[$value] = $$value;
	}
	$arr_data['description'] = $description;
	if($_SGLOBAL['panelinfo']['group']['verifyalbum'] && !pkperm('isadmin')) {
		$arr_data['grade'] = 0;
	} else {
		$arr_data['grade'] = 3;
	}
	$arr_data['dateline'] = $_G['timestamp'];
	$albumid = inserttable('albumitems', $arr_data, 1);
	if(!$albumid) {
		cpmsg('album_creat_error', '', '', '', true, true);
	}
	//相冊屬性
	if(!empty($_POST['attr_ids'])) {
		require_once B_ROOT."./batch.attribute.php";
		setattributesettings($catid, $albumid, $_POST['attr_ids']);
	}
	return $albumid;
}

/**
 * 顯示相冊篩選器
 * @param
 * @return 無
 */
function showalbumattr() {
	global $_G, $_SC;
	echo '<tr><td colspan="2" style="border-top:none;"><div id="album_attr"></div></td></tr>';

	echo '
	<script type="text/javascript" charset="'.$_G['charset'].'">
		$(function() { getAlbumAttrList();}); //頁面加載完成加載相冊篩選器
		$("#album_catid").change(function() { getAlbumAttrList();});
		function getAlbumAttrList() {
			$("#album_attr").load("batch.attribute.php?ajax=1&type=album&typeid="+$("#album_catid").val());
		}
	</script>';
}

?>