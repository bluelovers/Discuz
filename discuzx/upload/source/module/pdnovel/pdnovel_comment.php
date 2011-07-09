<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

loadcache('pdnovelcategory');
$ncc = $_G['cache']['pdnovelcategory'];

$novelid = $_G['gp_novelid'];
$cid = $_G['gp_cid'];
$page = $_G['gp_page']?$_G['gp_page']:1;
$perpage = 10;

if (!is_numeric($novelid)){
	showmessage($lang['novelid_error']);
}

$novel = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid AND display=0 LIMIT 1");

if(!$novel ){
	showmessage($lang['novel_error']);
}
$catname = $ncc[$novel['catid']]['catname'];
$novel['upid'] = $ncc[$novel['catid']]['upid'];
$upname = $ncc[$novel['upid']]['catname'];
$novel['full'] = $novel['full']==1?$lang['full']:$lang['nofull'];

if($cid) {
	$prenum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('pdnovel_comment')." WHERE novelid=$novelid AND cid>=$cid");
	$page = @ceil($prenum/$perpage);
}

$limit_start = $perpage * ($page - 1);

$commentlist = array();
$query = DB::query("SELECT * FROM ".DB::table('pdnovel_comment')." WHERE novelid=$novelid ORDER BY dateline DESC LIMIT $limit_start,$perpage");
while($comment=DB::fetch($query)){
	$comment['dateline'] = strftime('%Y-%m-%d %X',$comment['dateline']);
	$commentlist[] = $comment;
}
$mpurl = "novel.php?mod=comment&novelid=$novelid";
$multi = multipage($novel['comments'], $perpage, $page, $mpurl);
$navtitle = $lang['comment'].' - '.$novel['name'].' - '.$catname.' - '.$upname.' - '.$navtitle;
$metakeywords = $novel['name'].','.$novel['author'].','.$novel['keywords'];

pdupdateclick($novelid, $novel['lastvisit']);
include template('diy:pdnovel/comment');

?>