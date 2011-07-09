<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

/*if(!checkperm('allowreadnovel')) {
	showmessage($lang['read_no_per'], NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}*/

loadcache('pdnovelcategory');
$ncc = $_G['cache']['pdnovelcategory'];

$novelid = $_G['gp_novelid'];
$chapterid = $_G['gp_chapterid'] ? $_G['gp_chapterid'] : 0;

if (!is_numeric($novelid)||!is_numeric($chapterid)){
	showmessage($lang['novelid_error']);
}

$novel = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid AND display=0 LIMIT 1");

if(!$novel ){
	showmessage($lang['novel_error']);
}
$catname = $ncc[$novel['catid']]['catname'];
$novel['upid'] = $ncc[$novel['catid']]['upid'];
$upname = $ncc[$novel['upid']]['catname'];
$novel['lastupdate'] = strftime('%Y-%m-%d %X',$novel['lastupdate']);

if($chapterid){
	$read = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_chapter')." where chapterid=$chapterid LIMIT 1");
}else{
	$read = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_chapter')." where novelid=$novelid LIMIT 1");
}

if(!$read){
	showmessage($lang['novel_error']);
}

$chapterid = $read['chapterid'];
$read['lastupdate'] = strftime('%Y-%m-%d %X',$read['lastupdate']);

$pre = DB::result_first("SELECT chapterid FROM ".DB::table('pdnovel_chapter')." where chapterid<$chapterid AND novelid=$novelid ORDER BY chapterid DESC LIMIT 1");
$next = DB::result_first("SELECT chapterid FROM ".DB::table('pdnovel_chapter')." where chapterid>$chapterid AND novelid=$novelid ORDER BY chapterid ASC LIMIT 1");

$prepage = $pre?"novel.php?mod=read&novelid=$novel[novelid]&chapterid=$pre":"novel.php?mod=chapter&novelid=$novel[novelid]";
$nextpage = $next?"novel.php?mod=read&novelid=$novel[novelid]&chapterid=$next":"novel.php?mod=chapter&novelid=$novel[novelid]";
$navtitle = $read['chaptername'].' - '.$novel['name'].' - '.$catname.' - '.$upname.' - '.$navtitle;
$metakeywords = $read['chaptername'].','.$novel['name'].','.$novel['author'].','.$novel['keywords'];

pdupdateclick($novelid, $novel['lastvisit']);
loadcache( "diytemplatename" );
include template('diy:pdnovel/read');
?>