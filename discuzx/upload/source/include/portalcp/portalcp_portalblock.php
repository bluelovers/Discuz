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
$pageblocks = $tpls = $bids = $blocks = $tplpermissions = $blockpermissions = array();
if(checkperm('allowdiy')) {
	$tpls = array_keys($_G['cache']['diytemplatename']);
} else {
	$tplpermissions = getallowdiytemplate($_G['uid']);
	foreach($tplpermissions as $value) {
		if($value['allowmanage'] || ($value['allowrecommend'] && empty($value['needverify'])) || ($op=='recommend' && $value['allowrecommend'])) {
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
	$query = DB::query('SELECT bid,allowmanage,allowrecommend,needverify FROM '.DB::table('common_block_permission')." WHERE uid='$_G[uid]' AND allowmanage='1' OR (allowrecommend='1' AND needverify='0')");
	while(($value=DB::fetch($query))) {
		$bid = intval($value['bid']);
		$blockpermissions[$bid] = $value;
		$bids[] = $bid;
		$pageblocks['others'][] = $bid;
	}
} else {
	$query = DB::query('SELECT bid FROM '.DB::table('common_block')." WHERE blocktype = '1'");
	while(($value=DB::fetch($query))) {
		$bid = intval($value['bid']);
		$bids[] = $bid;
		$pageblocks['others'][] = $bid;
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

	$sql = '';
	switch ($_GET['idtype']) {
		case 'tid' :
			$sql = " AND (blockclass='forum_thread' OR blockclass='forum_attachment')";
			break;
		case 'gtid' :
			$sql = " AND (blockclass='group_thread' OR blockclass='group_attachment')";
			break;
		case 'blogid' :
			$sql = " AND blockclass ='space_blog'";
			break;
		case 'picid' :
			$sql = " AND blockclass ='space_pic'";
			break;
		case 'aid' :
			$sql = " AND blockclass ='portal_article'";
			break;
	}

	if($bids) {
		$query = DB::query('SELECT bid, `name` FROM '.DB::table('common_block')." WHERE bid IN (".dimplode($bids).")$sql");
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
		$query = DB::query('SELECT b.bid, b.`name`, b.blockclass, b.script, b.notinherited, tb.targettplname FROM '.DB::table('common_block')." b LEFT JOIN ".DB::table('common_template_block')." tb ON b.bid=tb.bid WHERE $wheresql LIMIT 100");
		while(($value=DB::fetch($query))) {
			$value['name'] = empty($value['name']) ? '#'.$value['bid'] : $value['name'];
			$theclass = block_getclass($value['blockclass']);
			$value['blockclassname'] = $theclass['name'];
			$value['datasrc'] = $theclass['script'][$value['script']];
			$diyurl = block_getdiyurl($value['targettplname']);
			$value['diyurl'] = $diyurl['url'];
			$value['tplname'] = $diytemplate[$value['targettplname']];
			$value['isrecommendable'] = block_isrecommendable($value);
			$value['perm'] = formatblockpermissoin($value);
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
		$query = DB::query('SELECT b.bid, b.`name`, b.blockclass, b.notinherited, tb.targettplname FROM '.DB::table('common_block').' b LEFT JOIN '.DB::table('common_template_block').' tb ON tb.bid=b.bid WHERE b.bid IN ('.dimplode($bids).')');
		while(($value=DB::fetch($query))) {
			$value['isrecommendable'] = block_isrecommendable($value);
			$value['perm'] = formatblockpermissoin($value);
			$value['name'] = !empty($value['name']) ? $value['name'] : '<strong>#'.$value['bid'].'</strong>';
			$blocks[$value['bid']] = $value;
		}
	}
}

include_once template("portal/portalcp_portalblock");

function formatblockpermissoin($block) {
	global $tplpermissions, $blockpermissions;
	$perm = array('allowproperty' => 0, 'allowdata'=> 0);
	$bid = !empty($block) ? $block['bid'] : 0;
	if(!empty($bid)) {
		if(checkperm('allowdiy')) {
			$perm = array('allowproperty' => 1, 'allowdata'=> 1);
		} else {
			if(isset($blockpermissions[$bid])) {
				if($blockpermissions[$bid]['allowmanage']) {
					$perm = array('allowproperty' => 1, 'allowdata'=> 1);
				}
				if ($blockpermissions[$bid]['allowrecommend'] && !$blockpermissions[$bid]['needverify']) {
					$perm['allowdata'] = 1;
				}
			} elseif(!$block['notinherited'] && $block['targettplname'] && isset($tplpermissions[$block['targettplname']])) {
				if($tplpermissions[$block['targettplname']]['allowmanage']) {
					$perm = array('allowproperty' => 1, 'allowdata'=> 1);
				}
				if ($tplpermissions[$block['targettplname']]['allowrecommend'] && !$tplpermissions[$block['targettplname']]['needverify']) {
					$perm['allowdata'] = 1;
				}
			}
		}
	}
	return $perm;
}

?>