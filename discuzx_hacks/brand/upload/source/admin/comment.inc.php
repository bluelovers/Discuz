<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: comment.inc.php 4324 2010-09-04 07:08:16Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

$wheresql = $joinsql = $query = $mlist = $opcheckstr = $catstr = $gradestr = '';

if($_POST['deletesubmit'] && $_POST['operation'] == "delete") {
	require_once(B_ROOT.'./source/adminfunc/tool.func.php');
	if(!empty($_POST['cid'])) {
		foreach($_POST['cid'] as $cid) {
			deletecomment($cid);
		}
		cpmsg('message_success', $_POST['buffurl']);
	} else {
		cpmsg('no_item', $_POST['buffurl']);
	}
}
shownav('infomanage','comment_list');
showsubmenu('menu_comment', array(
	array('menu_comment', 'comment', '1'),
	array('menu_remark', 'remark', '0')
));
showtips('comment_list_tips');

//搜索条件拼合
if(!empty($_GET['filtersubmit']) || !empty($_GET['filter']) || !empty($_GET['sc']) || !empty($_GET['itemid']) || !empty($_GET['author']) || !empty($_GET['message'])) {
	$wheresql .= !empty($_GET['type'])?' AND s.type=\''.$_GET['type'].'\'':'';
	$wheresql .= !empty($_GET['itemid'])?' AND s.itemid=\''.$_GET['itemid'].'\'':'';
	$wheresql .= !empty($_GET['author'])?' AND s.author LIKE \'%'.$_GET['author'].'%\'':'';
	$wheresql .= !empty($_GET['message'])?' AND s.message LIKE \'%'.$_GET['message'].'%\'':'';
	$wheresql .= ' AND s.subtype=\'0\'';
}
if(!ckfounder($_G['uid'])) {
    $query = DB::query("SELECT itemid FROM ".DB::table("shopitems")." WHERE catid IN (".$_SGLOBAL['adminsession']['cpgroupshopcats'].")");
    while($result = DB::fetch($query)) {
        $shopitems[] = $result['itemid'];
    }
    if(!empty($shopitems)) {
        $wheresql .= ' AND shopid IN ('.implode(",", $shopitems).')';
    } else {
        $wheresql .= ' AND shopid IN (0)';
    }
}
if(!empty($wheresql)) {
	$wheresql = ' WHERE'.substr($wheresql, 4);
}
//排序
if(!in_array($_GET['order'], array('dateline', 'lastpost', 'viewnum', 'replynum'))) {
	$_GET['order'] = 'dateline';
}
$_GET['order'] = $_GET['order'] == 'itemid'?'displayorder DESC, itemid':$_GET['order'];
if(!in_array($_GET['sc'], array('DESC', 'ASC'))) {
	$_GET['sc'] = 'DESC';
}

if(submitcheck('filtersubmit')) {

	//分页处理
	$tpp = 15;
	$_GET['page'] = $_GET['page']>0?intval($_GET['page']):1;
	$pstart = ($_GET['page']-1)*$tpp;
	$query = DB::query("SELECT count(s.itemid) AS count  FROM ".tname('spacecomments')." s ".$wheresql.";");
	$value = DB::fetch($query);
	foreach($_GET as $key=>$_value) {
		if(in_array($key, array('action', 'formhash', 'filtersubmit', 'type', 'author', 'itemid', 'order', 'sc', 'message'))) {
			$url .= '&'.$key.'='.$_value;
		}
	}
	$url = '?'.substr($url, 1);
	$multipage = multi($value['count'], $tpp, $_GET['page'], 'admin.php'.$url, $phpurl=1);
	//数据查询
	$query = DB::query('SELECT * FROM '.tname("spacecomments").' s '.$wheresql.' ORDER BY s.'.$_GET['order'].' '.$_GET['sc'].' LIMIT '.$pstart.', '.$tpp.';');
	while($value = DB::fetch($query)) {
		if(!empty($value['upcid'])) {
			$currentmessage = array();
			preg_match_all ("/\<div class=\"new\">(.+)?\<\/div\>/is", $value['message'], $currentmessage, PREG_SET_ORDER);
			if(!empty($currentmessage)) $value['message'] = $currentmessage[0][0];
			$value['message'] = preg_replace("/\<div class=\"quote\"\>\<blockquote.+?\<\/blockquote\>\<\/div\>/is", '',$value['message']);

		}
		$mlist .= showcommentrow($mname, $value);
	}
	showlistcomment($mlist, $multipage, 'comment');
	showcommentmod($mname, 'comment');
} else {
	show_searchfrom_comment('comment');
}

?>