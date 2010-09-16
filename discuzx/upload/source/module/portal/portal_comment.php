<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_comment.php 14960 2010-08-17 08:43:45Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$aid = empty($_GET['aid'])?0:intval($_GET['aid']);
if(empty($aid)) {
	showmessage("comment_no_article_id");
}
$article = DB::fetch_first("SELECT a.*, ac.*
	FROM ".DB::table('portal_article_title')." a
	LEFT JOIN ".DB::table('portal_article_count')." ac
	ON ac.aid=a.aid
	WHERE a.aid='$aid'");
if(empty($article)) {
	showmessage("comment_article_no_exist");
} elseif(empty($article['allowcomment'])) {
	showmessage('article_comment_is_forbidden');
}

$perpage = 25;
$page = intval($_GET['page']);
if($page<1) $page = 1;
$start = ($page-1)*$perpage;

$commentlist = array();
$multi = '';

if($article['commentnum']) {
	$sqladd = '';
	$pricount = 0;
	$query = DB::query("SELECT * FROM ".DB::table('portal_comment')." WHERE aid='$aid' $sqladd ORDER BY cid DESC LIMIT $start,$perpage");
	while ($value = DB::fetch($query)) {
		if($value['status'] == 0 || $value['uid'] == $_G['uid'] || $_G['adminid'] == 1) {
			$value['allowop'] = 1;
			$commentlist[] = $value;
		} else {
			$pricount ++;
		}
	}
}

$multi = multi($article['commentnum'], $perpage, $page, "portal.php?mod=comment&aid=$aid");
$seccodecheck = $_G['group']['seccode'] ? $_G['setting']['seccodestatus'] & 4 : 0;
$secqaacheck = $_G['group']['seccode'] ? $_G['setting']['secqaa']['status'] & 2 : 0;
include_once template("diy:portal/comment");

?>