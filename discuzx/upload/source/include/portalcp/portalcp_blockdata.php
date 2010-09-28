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

$op = in_array($_GET['op'], array('verify')) ? $_GET['op'] : 'verify';

if(!checkperm('allowdiy') && !checkperm('allowauthorizedblock')) {
	showmessage('portal_nopermission', dreferer());
}

loadcache('diytemplatename');
$blocks = $bids = $tpls = array();
$diytemplate = array();
if(checkperm('allowdiy')) {
	$tpls = array_keys($_G['cache']['diytemplatename']);
} else {
	$permissions = getallowdiytemplate($_G['uid']);
	foreach($permissions as $value) {
		if($value['allowmanage'] || ($value['allowrecommend'] && empty($value['needverify'])) || ($op=='recommend' && $value['allowrecommend'])) {
			$tpls[] = $value['targettplname'];
		}
	}
}
if($tpls) {
	$query = DB::query('SELECT bid FROM '.DB::table('common_template_block')." WHERE targettplname IN (".dimplode($tpls).")");
	while(($value=DB::fetch($query))) {
		$bids[] = intval($value['bid']);
	}
}
if(!$_G['group']['allowdiy']) {
	$query = DB::query('SELECT bid FROM '.DB::table('common_block_permission')." WHERE uid='$_G[uid]' AND allowmanage='1' OR (allowrecommend='1' AND needverify='0')");
	while(($value=DB::fetch($query))) {
		$bids[] = intval($value['bid']);
	}
}
$bids = array_unique($bids);

if(submitcheck('batchsubmit')) {

	$tourl = dreferer();
	if(!in_array($_POST['optype'], array('pass', 'delete'))) {
		showmessage('select_a_option', $tourl);
	}
	$ids = array();
	if($_POST['ids']) {
		$query = DB::query('SELECT dataid, bid FROM '.DB::table('common_block_item_data')." WHERE dataid IN (".dimplode($_POST['ids']).')');
		while(($value=DB::fetch($query))) {
			if(in_array($value['bid'], $bids)) {
				$ids[] = intval($value['dataid']);
			}
		}
	}
	if(empty($ids)) {
		showmessage('select_a_moderate_data', $tourl);
	}

	if($_POST['optype']=='pass') {
		DB::query('UPDATE '.DB::table('common_block_item_data')." SET isverified='1', verifiedtime='$_G[timestamp]' WHERE dataid IN (".dimplode($ids).")");
	} elseif($_POST['optype']=='delete') {
		DB::query('DELETE FROM '.DB::table('common_block_item_data')." WHERE dataid IN (".dimplode($ids).")");
	}
	showmessage('operation_done', $tourl);
}

$theurl = 'portal.php?mod=portalcp&ac=blockdata';
$perpage = 20;
$page = max(1,intval($_GET['page']));
$start = ($page-1)*$perpage;
if($start<0) $start = 0;

if($_GET['searchkey']) {
	$_GET['searchkey'] = trim($_GET['searchkey']);
	if (preg_match('/^[#]?(\d+)$/', $_GET['searchkey'],$match)) {
		$bid = intval($match[1]);
		$bids = in_array($bid, $bids) ? array($bid) : array();
	} elseif($bids) {
		$_GET['searchkey'] = stripsearchkey($_GET['searchkey']);
		$query = DB::query('SELECT bid FROM '.DB::table('common_block')." WHERE bid IN (".dimplode($bids).") AND name LIKE '%$_GET[searchkey]%'");
		$bids = array();
		while(($value=DB::fetch($query))) {
			$bids[] = intval($value['bid']);
		}
	}
}

$datalist = $ids = array();
$multi = '';
if($bids) {
	$count = DB::result_first('SELECT COUNT(*) FROM '.DB::table('common_block_item_data')." WHERE bid IN (".dimplode($bids).") AND isverified='0'");
	if($count) {
		$query = DB::query('SELECT * FROM '.DB::table('common_block_item_data')." WHERE bid IN (".dimplode($bids).") AND isverified='0' LIMIT $start, $perpage");
		while(($value=DB::fetch($query))) {
			$datalist[] = $value;
			$ids[] = $value['bid'];
		}
		$multi = multi($count, $perpage, $page, $theurl);
	}
}
if($ids) {
	include_once libfile('function/block');
	$ids = array_unique($ids);
	$query = DB::query('SELECT b.bid, b.name as blockname, tb.targettplname FROM '.DB::table('common_block')." b LEFT JOIN ".DB::table('common_template_block')." tb ON b.bid=tb.bid WHERE b.bid IN (".dimplode($ids).")");
	while(($value=DB::fetch($query))) {
		$diyurl = block_getdiyurl($value['targettplname']);
		$value['diyurl'] = $diyurl['url'];
		$value['tplname'] = isset($_G['cache']['diytemplatename'][$value['targettplname']]) ? $_G['cache']['diytemplatename'][$value['targettplname']] : $value['targettplname'];
		$blocks[$value['bid']] = $value;
	}
}

include_once template("portal/portalcp_blockdata");

?>