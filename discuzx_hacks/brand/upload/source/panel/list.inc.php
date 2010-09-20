<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: list.inc.php 4476 2010-09-15 04:51:33Z fanshengshuai $
 */

if(!defined('IN_STORE')) {
	exit('Acess Denied');
}
if(empty($_SGLOBAL['panelinfo']['enable'.$mname])) {
	cpmsg('no_perm');
}
//店長沒有店舖列表
if($mname=='shop') {
	header('Location:panel.php?action=edit&m=shop');
}
$cats = getmodelcategory($mname);
$wheresql = $joinsql = $query = $mlist = '';
$where = array();
$_GET['grade'] = isset($_GET['grade'])?intval($_GET['grade']):-1;
$_GET['subject'] = trim(strip_tags($_GET['subject']));

shownav('infomanage', $mname.'_list');
switch($mname) {
	case 'good':
		showsubmenu('menu_good', array(
			array('menu_good', 'list&m=good', '1'),
			array('menu_list_addgood', 'add&m=good', '0')
		));
		break;
	case 'groupbuy':
		showsubmenu('menu_groupbuy', array(
			array('menu_groupbuy', 'list&m=groupbuy', '1'),
			array('menu_list_addgroupbuy', 'add&m=groupbuy', '0')
		));
		break;
	case 'album':
		showsubmenu('menu_album', array(
			array('menu_album', 'list&m=album', '1'),
			array('menu_album_add', 'add&m=album', '0'),
		));
		break;
	case 'consume':
		showsubmenu('menu_consume', array(
			array('menu_consume', 'list&m=consume', '1'),
			array('menu_list_addconsume', 'add&m=consume', '0')
		));
		break;
	case 'notice':
		showsubmenu('menu_notice', array(
			array('menu_notice', 'list&m=notice', '1'),
			array('menu_list_addnotice', 'add&m=notice', '0')
		));
		break;
	case 'photo':
		showsubmenu('menu_photo', array(
			array('menu_album', 'list&m=album', '0'),
			array('menu_album_add', 'add&m=album', '0'),
		));
		break;
}
showtips($mname.'_list_tips');

if($mname == 'album' && $_GET['type'] == 'default') {
	//默認相冊不在表中，直接顯示
	require_once(B_ROOT.'./source/adminfunc/list_photo.func.php');
	$album_default['subject'] = lang('album_default');
	$album_default['shopid'] = $_G['myshopid'];
	$album_default['albumid'] = 0;
	$album_default['subjectimage'] = '';
	$mlist .=  '<table class="tb tb2 tdhover"><tr>';
	$mlist .= '<td>'.showlistrowalbum($album_default).'</td>';
	$mlist .=  '</table>';
	showlistsearch($mname);
	$multipage = '';
	showlistphoto($mname, $mlist, $multipage);
} else {

	//搜索條件拼合
	if($mname == 'shop') {
		$joinsql = ' c.title FROM '.tname('shopitems').' s INNER JOIN '.tname('shopgroup').' c ON s.groupid=c.id';
		$countsql = substr($joinsql, 8);
	} elseif($mname == 'album') {
		//店長自定義相冊
		$joinsql = ' c.subject AS title FROM '.tname($mname.'items').' s INNER JOIN '.tname('shopitems').' c ON s.shopid=c.itemid';
		$countsql = substr($joinsql, 19);
		//區分用戶相冊還是論壇導入相冊
		$where[] = $_GET['type']=='import'?"s.frombbs='1'":"s.frombbs='0'";
	} elseif($mname == 'photo') {
		//相冊中的圖片
		$joinsql = ' c.subject AS title FROM '.tname($mname.'items').' s LEFT JOIN '.tname('albumitems').' c ON s.albumid=c.itemid';
		$countsql = substr($joinsql, 19);
	}  else {
		$joinsql = ' c.subject AS title FROM '.tname($mname.'items').' s INNER JOIN '.tname('shopitems').' c ON s.shopid=c.itemid';
		$countsql = substr($joinsql, 19);
	}
	$where[] = "s.shopid='{$_G['myshopid']}'";
	if(!empty($_GET['filtersubmit'])) {
		!empty($_GET['itemid']) && $where[] = "s.itemid='{$_GET['itemid']}'";
		if($mname != 'album' && $_GET['grade'] != '-1') {
			$where[] = $_GET['grade'] == 5 ? 's.updateverify=1' : "s.grade='{$_GET['grade']}'";
		}
		$mname=='photo' && $where[] = "s.albumid='{$_GET['albumid']}'";
		!empty($_GET['subject']) && $where[] = "s.subject LIKE '%{$_GET['subject']}%'";
	}

	if(count($where)>0) {
		$wheresql = 'WHERE '.implode(' AND ', $where);
	}
	//排序
	if(!in_array($_GET['order'], array('itemid', 'lastpost', 'viewnum', 'replynum'))) {
		$_GET['order'] = 'itemid';
	}
	$_GET['order'] = $_GET['order'] == 'itemid'?'displayorder ASC, s.itemid':$_GET['order'];
	if(!in_array($_GET['sc'], array('DESC', 'ASC'))) {
		$_GET['sc'] = 'DESC';
	}
	//分頁處理
	$tpp = 15;
	$pstart = ($_GET['page']-1)*$tpp;
	$query = DB::query("SELECT count(*) AS count $countsql $wheresql");
	$value = DB::fetch($query);
	foreach($_GET as $key=>$_value) { if($key!='page') $url .= '&'.$key.'='.$_value;}
	$url = '?'.substr($url, 1);
	$multipage = multi($value['count'], $tpp, $_GET['page'], 'panel.php'.$url, $phpurl=1);
	//數據查詢
	$query = DB::query("SELECT s.*, $joinsql $wheresql ORDER BY s.$_GET[order] $_GET[sc] LIMIT $pstart, $tpp;");

	if($mname == 'album' || $mname == 'photo') {
		require_once(B_ROOT.'./source/adminfunc/list_photo.func.php');
		$step=1;
		$mlist .= "<div id=\"pList_0\" act=\"pList\" style=\"margin-top:10px;\"><ul class=\"impressList clear\"> ";
		$funcname = showlistrow.$mname;
		while($value = DB::fetch($query)) {
			$mlist .= ''.$funcname($value).'';
			$step++;
		}
		$mlist .= "</ul></div>";
		echo "
		<style>
		.impressList {color:#999;}
		.impressList li {float: left; overflow: hidden; height:270px; width: 19.5%; overflow:hidden; list-style-type: none;}
		/*.impressList li div.h {height: 260px; width: 140px;}*/
		.impressList li div.b {
		background: url(static/image/impressnew.gif) no-repeat 0px 0px;
		height: 129px;
		padding: 6px 6px 9px;
		width: 128px;
		overflow:hidden;
		}
		</style>";

		showlistsearch($mname);
		showlistphoto($mname, $mlist, $multipage);
		showlistmod($mname);
	} else {
		while($value = DB::fetch($query)) {
			$mlist .= showlistrow($mname, $value);
		}
		showlistsearch($mname);
		showlistnormal($mname, $mlist, $multipage);
		showlistmod($mname);
	}
}

?>