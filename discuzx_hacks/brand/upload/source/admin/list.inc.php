<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: list.inc.php 4411 2010-09-13 09:01:01Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

$wheresql = $joinsql = $countsql = $query = $mlist = $opcheckstr = $catstr = $gradestr = $url = '';
$where = array();
$_GET['grade'] = isset($_GET['grade'])?intval($_GET['grade']):-1;
$_GET['subject'] = trim(strip_tags($_GET['subject']));

// 导航和子菜单处理
switch($mname) {
	case 'good':
		shownav('infomanage', $mname.'_list');
		showsubmenu('menu_good');
		break;
	case 'groupbuy':
		shownav('infomanage', $mname.'_list');
		showsubmenu('menu_groupbuy');
		break;
	case 'album':
		shownav('infomanage', $mname.'_list');
		showsubmenu('menu_album', array(
					array('menu_album', 'list&m=album', '1'),
					array('menu_photo', 'list&m=photo', '0')
					));
		break;
	case 'consume':
		shownav('infomanage', $mname.'_list');
		showsubmenu('menu_consume');
		break;
	case 'notice':
		shownav('infomanage', $mname.'_list');
		showsubmenu('menu_notice');
		break;
	case 'photo':
		shownav('infomanage', $mname.'_list');
		showsubmenu('menu_photo', array(
					array('menu_album', 'list&m=album', '0'),
					array('menu_photo', 'list&m=photo', '1')
					));
		break;
	case 'shop':
		shownav('shop', $mname.'_list');
		showsubmenu('shop_list');
		break;
}
showtips($mname.'_list_tips');

// 搜索条件拼合
if($mname == 'shop') {
	$joinsql = ' c.title FROM '.tname('shopitems').' s left join '.tname('shopgroup').' c on s.groupid = c.id';
	$countsql = substr($joinsql, 8);
} elseif($mname == 'album') {
	// 店长自定义相册
	$joinsql = ' c.subject AS title FROM '.tname($mname.'items').' s INNER JOIN '.tname('shopitems').' c ON s.shopid=c.itemid';
	$countsql = substr($joinsql, 19);
	// 区分用户相册还是论坛导入相册
	$where[] = $_GET['type']=='import'?"s.frombbs='1'":"s.frombbs='0'";
} elseif($mname == 'photo') {
	// 相册中的图片
	$joinsql = ' c.subject AS title FROM '.tname($mname.'items').' s LEFT JOIN '.tname('albumitems').' c ON s.albumid=c.itemid';
	$countsql = substr($joinsql, 19);
}  else {
	$joinsql = ' c.subject AS title FROM '.tname($mname.'items').' s INNER JOIN '.tname('shopitems').' c ON s.shopid=c.itemid';
	$countsql = substr($joinsql, 19);
}

// 查询条件处理
if(!empty($_GET['filtersubmit'])) {
	!empty($_GET['itemid']) && $where[] = "s.itemid='{$_GET['itemid']}'";
	!empty($_GET['uid']) && $where[] = "s.uid='{$_GET['uid']}'";
	if(!empty($_GET['groupid']) && $mname=='shop') {
		$where[] = "s.groupid='{$_GET['groupid']}'";
	}

	// 组
	if($mname!='shop' && !empty($_GET['groupid']) && $_GET['shopid'] == 0) {
		$where[] = "c.groupid='{$_GET['groupid']}'";
	}

	// 某个店铺下
	if($mname!='shop' && !empty($_GET['shopid'])) {
		$where[] = "s.shopid='{$_GET['shopid']}'";
	}

	// 推荐商铺
	if($mname=='shop' && !empty($_GET['recommend']) && in_array($_GET['recommend'], array('yes', 'no'))) {
		switch($_GET['recommend']) {
			case 'yes':
				$where[] = 's.recommend=1';
				$url_recommend = "&recommend=yes";
				break;
			case 'no':
				$where[] = 's.recommend=0';
				$url_recommend = "&recommend=no";
				break;
			default :
				break;
		}
	}
	if(!empty($_GET['catid']) && $_GET['catid'] != '-1') {
		$where[] = "s.catid='$_GET[catid]'";
	}
	if($mname=='photo' && !empty($_GET['albumid'])) {
		$where[] = "s.albumid='$_GET[albumid]'";
	}
	if($_GET['grade'] != '-1') {
		$where[] = $_GET['grade'] == 5 ? 's.updateverify=1' : "s.grade='{$_GET['grade']}'";
	}
	!empty($_GET['subject']) && $where[] = "s.subject LIKE '%{$_GET['subject']}%'";
	!empty($_GET['username']) && $where[] = "s.username LIKE '%{$_GET['username']}%'";
	$_GET['updatepass'] == 1 && $where[] =  "s.updateverify='1'";
	// 特殊情况取消以上所有where条件
	if($mname == 'album' && $_GET['type']=='default') {
		// 选默认相册
		$joinsql = " '".lang('album_default')."' AS name FROM ".tname('shopitems').' s';
		$countsql = substr($joinsql, 19);
		$where = array();
		!empty($_GET['shopid']) && $where[] = "s.itemid='$_GET[shopid]'";
	}
	if(!ckfounder($_G['uid'])) {
		if($mname == 'shop') {
			$where[] = 's.catid IN ('.$_SGLOBAL['adminsession']['cpgroupshopcats'].')';
		} else {
			$query = DB::query("SELECT itemid FROM ".DB::table("shopitems")." WHERE catid IN (".$_SGLOBAL['adminsession']['cpgroupshopcats'].")");
			while($result = DB::fetch($query)) {
				$shopitems[] = $result['itemid'];
			}
			if(!empty($shopitems)) {
				$where[] = 's.shopid IN ('.implode(",", $shopitems).')';
			} else {
				$where[] = 's.shopid IN (0)';
			}
		}
	}
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
$cats = getmodelcategory($mname);

//搜索页还是列表页
if(empty($_REQUEST['filtersubmit'])) {
	show_searchfrom_webmaster($mname);
}else{

	//分页处理
	$tpp = 15;
	$pstart = ($_GET['page']-1)*$tpp;
	$query = DB::query("SELECT count(*) AS count $countsql $wheresql");
	$value = DB::fetch($query);
	foreach($_GET as $key=>$_value) { if($key!='page') $url .= '&'.$key.'='.$_value;}
	!empty($url_recommend)?$url.$url_recommend:'';
	$url = '?'.substr($url, 1);
	$multipage = multi($value['count'], $tpp, $_GET['page'], 'admin.php'.$url, $phpurl=1);
	// 数据查询
	$query = DB::query("SELECT s.*, $joinsql $wheresql ORDER BY s.$_GET[order] $_GET[sc] LIMIT $pstart, $tpp;");

	if($mname=='album' || $mname=='photo') {
		// 相册和相册中的图片的显示
		require_once(B_ROOT.'./source/adminfunc/list_photo.func.php');
		$step=0;
		$mlist .= "<div id=\"pList_0\" act=\"pList\" style=\"margin-top:10px;\"><ul class=\"impressList clear\"> ";

		while($value = DB::fetch($query)) {
			$step++;

			// 选默认相册时有特殊情况
			if($mname=='album' && $_GET['type']=='default') {
				$value['shopid'] = $value['itemid'];
				$value['itemid'] = 0;
				list($value['name'], $value['subject']) = array( $value['subject'], $value['name']);//变量交换下
			}

			if ($mname == "album") {
				$mlist .= ''.showlistrowalbum($value).'';
			} else {
				$mlist .= ''.showlistrowphoto($value).'';
			}

		}
		$mlist .= "</ul></div>";

		showlistphoto($mname, $mlist, $multipage);

		// 选默认相册时不出现批量操作
		if(!($mname=='album' && $_GET['type']=='default')) {
			showlistmod($mname);
		}
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
	} else {
		// 普通列表显示
		while($value = DB::fetch($query)) {
			$mlist .= showlistrow($mname, $value);
		}
		showlistnormal($mname, $mlist, $multipage);
		if($mname == 'shop' &&($_GET['optpass'] == 1 || $_GET['updatepass'] == 1)) {

		} else {
			showlistmod($mname);
		}
	}

}

?>