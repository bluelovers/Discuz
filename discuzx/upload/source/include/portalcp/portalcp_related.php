<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_related.php 12343 2010-07-05 08:56:47Z shanzongjun $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = in_array($_GET['op'], array('manual','search','add','get')) ? $_GET['op'] : '';
$aid = intval($_G['gp_aid']);
$catid = intval($_G['gp_catid']);
if($aid) {
	check_articleperm($catid, $aid);
} else {
	check_articleperm($catid);
}

$wherearr = $articlelist = 	$relatedarr = array();

if($op == 'manual') {
	$manualid = intval($_GET['manualid']);
	$ra = array();
	if($manualid) {
		$query = DB::query("SELECT * FROM ".DB::table('portal_article_title')." WHERE aid='$manualid'");
		$ra = DB::fetch($query);
	}
} elseif($op == 'get') {
	$id = trim($_GET['id']);
	$getidarr = explode(',', $id);
	$getidarr = array_map('intval', $getidarr);
	$getidarr = array_unique($getidarr);
	$getidarr = array_filter($getidarr);
	if($getidarr) {
		$query = DB::query("SELECT * FROM ".DB::table('portal_article_title')." WHERE aid IN (".dimplode($getidarr).")");
		$list = array();
		while(($value=DB::fetch($query))) {
			$list[$value['aid']] = $value;
		}
		foreach($getidarr as $getid) {
			if($list[$getid]) {
				$articlelist[] = $list[$getid];
			}
		}
	}
} elseif($op == 'search') {

	$catids = array();
	$searchkey = stripsearchkey($_GET['searchkey']);
	$searchcate = intval($_GET['searchcate']);
	$catids = category_get_childids('portal', $searchcate);
	$catids[] = $searchcate;
	if($searchkey) {
		$wherearr[] = "title LIKE '%$searchkey%'";
	}
	$searchkey = dhtmlspecialchars($searchkey);
	if($searchcate) {
		$wherearr[] = "catid IN  (".dimplode($catids).")";
	}
	$wheresql = implode(' AND ', $wherearr);
	if($wheresql) {
		$wheresql = " WHERE ".$wheresql;
	}
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('portal_article_title')."$wheresql LIMIT 50"), 0);
	if($count) {
		$query = DB::query("SELECT * FROM ".DB::table('portal_article_title')."$wheresql ORDER BY dateline DESC LIMIT 50");
		while($value = DB::fetch($query)) {
			$articlelist[] = $value;
		}
	}

} elseif($op == 'add') {
	$relatedid = trim($_GET['relatedid']);
	$relatedarr = explode(',', $relatedid);
	$relatedarr = array_map('intval', $relatedarr);
	$relatedarr = array_unique($relatedarr);
	$relatedarr = array_filter($relatedarr);
	if($relatedarr) {
		$query = DB::query("SELECT * FROM ".DB::table('portal_article_title')." WHERE aid IN (".dimplode($relatedarr).")");
		$list = array();
		while(($value=DB::fetch($query))) {
			$list[$value['aid']] = $value;
		}
		foreach($relatedarr as $relateid) {
			if($list[$relateid]) {
				$articlelist[] = $list[$relateid];
			}
		}
	}
} else {
	$count = 0;
	$query = DB::query("SELECT * FROM ".DB::table('portal_article_title')." ORDER BY dateline DESC LIMIT 50");
	while($value = DB::fetch($query)) {
		$articlelist[] = $value;
		$count++;
	}
}
$category = category_showselect('portal', 'searchcate', false, $_G[gp_searchcate]);
include_once template("portal/portalcp_related_article");
?>