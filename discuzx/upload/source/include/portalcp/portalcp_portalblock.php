<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_article.php 7701 2010-04-12 06:01:33Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = in_array($_GET['op'], array('recommend', 'search')) ? $_GET['op'] : 'getblocklist';

if(!checkperm('allowdiy') && !checkperm('allowauthorizedblock')) {
	showmessage('portal_nopermission', dreferer());
}

loadcache(array('diytemplatename'));
loadcache('diytemplatename');
$pageblocks = $tpls = $bids = $blocks = array();
if(checkperm('allowdiy')) {
	$tpls = array_keys($_G['cache']['diytemplatename']);
} else {
	$permissions = getallowdiytemplate($_G['uid']);
	foreach($permissions as $value) {
		if($value['allowmanage'] || ($op=='recommend' && $value['allowrecommend'])) {
			$tpls[] = $value['targettplname'];
		}
	}
}
if($tpls) {
	$query = DB::query('SELECT * FROM '.DB::table('common_template_block')." WHERE targettplname IN (".dimplode($tpls).")");
	while(($value=DB::fetch($query))) {
		if(!isset($pageblocks[$value['targettplname']])) {
			$pageblocks[$value['targettplname']] = array();
		}
		$pageblocks[$value['targettplname']][] = intval($value['bid']);
		$bids[] = intval($value['bid']);
	}
}
$pageblocks['others'] = array();
if(!$_G['group']['allowdiy']) {
	$query = DB::query('SELECT bid FROM '.DB::table('common_block_permission')." WHERE uid='$_G[uid]' AND allowmanage='1'");
	while(($value=DB::fetch($query))) {
		$bids[] = intval($value['bid']);
		$pageblocks['others'][] = intval($value['bid']);
	}
}
$bids = array_unique($bids);

$diytemplate = array();
foreach($pageblocks as $key=>$value) {
	if(isset($_G['cache']['diytemplatename'][$key])) {
		$diytemplate[$key] = $_G['cache']['diytemplatename'][$key];
	} elseif($key == 'others') {
		$diytemplate[$key] = lang('portalcp', 'other_page');
	} else {
		$diytemplate[$key] = $key;
	}
}

if($op == 'recommend') {

	$blockclass = '';
	switch ($_GET['idtype']) {
		case 'tid' :
			$blockclass = 'forum_thread';
			break;
		case 'gtid' :
			$blockclass = 'group_thread';
			break;
		case 'blogid' :
			$blockclass = 'space_blog';
			break;
		case 'picid' :
			$blockclass = 'space_pic';
			break;
		case 'aid' :
			$blockclass = 'portal_article';
			break;
	}

	if($bids) {
		$query = DB::query('SELECT bid, `name` FROM '.DB::table('common_block')." WHERE bid IN (".dimplode($bids).") AND blockclass='$blockclass'");
		while(($value=DB::fetch($query))) {
			$blocks[$value['bid']] = !empty($value['name']) ? $value['name'] : '#'.$value['bid'];
		}
	}

} elseif($op == 'search') {

	if(!empty($bids)) {
		$wherearr = array();
		$wherearr[] = "b.bid IN (".dimplode($bids).')';
		if($_GET['searchkey']) {
			$_GET['searchkey'] = trim($_GET['searchkey']);
			if (preg_match('/^[#]?(\d+)$/', $_GET['searchkey'],$match)) {
				$bid = intval($match[1]);
				$wherearr = array(" b.bid='$bid'");
			} else {
				$_GET['searchkey'] = stripsearchkey($_GET['searchkey']);
				$wherearr[] = " b.name LIKE '%$_GET[searchkey]%'";
			}
		}

		require_once libfile('function/block');
		$wheresql = implode(' AND ',$wherearr);
		$query = DB::query('SELECT b.bid, b.`name`, b.blockclass, b.script, tb.targettplname FROM '.DB::table('common_block')." b LEFT JOIN ".DB::table('common_template_block')." tb ON b.bid=tb.bid WHERE $wheresql LIMIT 100");
		while(($value=DB::fetch($query))) {
			$value['name'] = empty($value['name']) ? '#'.$value['bid'] : $value['name'];
			$theclass = block_getclass($value['blockclass']);
			$value['blockclassname'] = $theclass['name'];
			$value['datasrc'] = $theclass['script'][$value['script']];
			$diyurl = block_getdiyurl($value['targettplname']);
			$value['diyurl'] = $diyurl['url'];
			$value['tplname'] = $diytemplate[$value['targettplname']];
			$value['isrecommendable'] = block_isrecommendable($value);
			$blocks[$value['bid']] = $value;
		}
	}

} else {

	$theurl = 'portal.php?mod=portalcp&ac=portalblock';
	$perpage = 5;
	$page = max(1,intval($_GET['page']));
	$start = ($page-1)*$perpage;
	if($start<0) $start = 0;
	$end = $start + $perpage;
	$multi = multi(count($pageblocks), $perpage, $page, $theurl);
	$allpageblocks = $pageblocks;
	$bids = $pageblocks = $diyurls = array();
	$cursor = 0;
	require_once libfile('function/block');
	foreach($allpageblocks as $key=>$value) {
		if($cursor >= $start && $cursor < $end) {
			$pageblocks[$key] = $value;
			$bids = array_merge($bids, $value);
			$diyurl = block_getdiyurl($key);
			$diyurls[$key] = $diyurl['url'];
		} elseif($cursor >= $end) {
			break;
		}
		$cursor++;
	}

	if($bids) {
		$query = DB::query('SELECT bid, `name`, blockclass FROM '.DB::table('common_block')." WHERE bid IN (".dimplode($bids).")");
		while(($value=DB::fetch($query))) {
			$value['isrecommendable'] = block_isrecommendable($value);
			$value['name'] = !empty($value['name']) ? $value['name'] : '#'.$value['bid'];
			$blocks[$value['bid']] = $value;
		}
	}
}

include_once template("portal/portalcp_portalblock");

?>