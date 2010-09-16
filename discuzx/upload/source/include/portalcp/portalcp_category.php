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
require_once libfile('function/portalcp');

$catid = max(0,intval($_GET['catid']));
$_GET['type'] = isset($_GET['type']) && in_array($_GET['type'], array('unrecommend', 'recommended')) ? $_GET['type'] : 'all';
$typearr[$_GET['type']] = 'class="a"';

$permission = getallowcategory($_G['uid']);

if (!checkperm('allowmanagearticle') && empty($permission[$catid]['allowmanage'])) {
	showmessage('portal_nopermission');
}

$category = $_G['cache']['portalcategory'];
$cate = $category[$catid];

if(empty($cate)) {
	showmessage('article_category_empty');
}

$allowmanage = checkperm('allowmanagearticle') || !empty($permission[$catid]['allowmanage']) ? true : false;
$allowpublish = checkperm('allowmanagearticle') || !empty($permission[$catid]['allowpublish']) ? true : false;

$wherearr = array();
$catids = category_get_childids('portal', $catid);
$catids[] = $catid;
$wherearr[] = "catid IN (".dimplode($catids).")";
if($_GET['searchkey']) {
	$_GET['searchkey'] = stripsearchkey($_GET['searchkey']);
	$wherearr[] = "title LIKE '%$_GET[searchkey]%'";
}
if($_GET['type'] == 'recommended') {
	$wherearr[] = "bid != ''";
} elseif($_GET['type'] == 'unrecommend') {
	$wherearr[] = "bid = ''";
}
$wheresql = implode(' AND ', $wherearr);

$perpage = 15;
$page = max(1,intval($_GET['page']));
$start = ($page-1)*$perpage;
if($start<0) $start = 0;

$list = array();
$multi = '';
$article_tags = article_tagnames();
$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('portal_article_title')." WHERE $wheresql"), 0);
if($count) {
	$query = DB::query("SELECT * FROM ".DB::table('portal_article_title')." WHERE $wheresql ORDER BY dateline DESC LIMIT $start,$perpage");
	while ($value = DB::fetch($query)) {
		if($value['pic']) $value['pic'] = pic_get($value['pic'], 'portal', $value['thumb'], $value['remote']);
		$value['dateline'] = dgmdate($value['dateline']);
		$value['allowmanage'] = $allowmanage;
		$value['allowpublish'] = $allowpublish;
		$value['taghtml'] = '';
		$tags = article_parse_tags($value['tag']);
		foreach($tags as $k=>$v) {
			if($v) {
				$value['taghtml'] .= "[{$article_tags[$k]}] ";
			}
		}
		$list[] = $value;
	}

	$multi = multi($count, $perpage, $page, "portal.php?mod=portalcp&ac=category&catid=$catid");
}

include_once template("portal/portalcp_category");


?>