<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']){
	showmessage($lang['nologin']);
}

loadcache('novelcategory');
$ncc = $_G['cache']['novelcategory'];

$query = DB::query("SELECT m.*, n.catid, n.name, n.lastchapter, n.lastchapterid, n.authorid, n.author, n.full, n.lastupdate FROM ".DB::table('novel_mark')." m LEFT JOIN ".DB::table('novel_novel')." n ON n.novelid=m.novelid WHERE m.uid=$_G[uid] ORDER BY n.lastupdate DESC");
$novellist = array();
while ($novel = DB::fetch($query)){
	$novel['lastupdate'] = strftime("%Y-%m-%d %H:%M",$novel['lastupdate']);
	$novel['catname'] = $ncc[$novel['catid']]['catname'];
	$novel['upid'] = $ncc[$novel['catid']]['upid'];
	$novel['upname'] = $ncc[$novel['upid']]['catname'];
	$novel['full'] = $novel['full']==1?$lang['full']:$lang['nofull'];
	$novellist[] = $novel;
}

include template('diy:novel/novel_mark');
?>