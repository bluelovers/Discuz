<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

loadcache('pdnovelcategory');
$ncc = $_G['cache']['pdnovelcategory'];

$novelid = $_G['gp_novelid'];

if (!is_numeric($novelid)){
	showmessage($lang['pdnovelid_error']);
}

$novel = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_view')." WHERE novelid=$novelid AND display=0");

if(!$novel){
	showmessage($lang['pdnovel_error']);
}
$catname = $ncc[$novel['catid']]['catname'];
$novel['upid'] = $ncc[$novel['catid']]['upid'];
$upname = $ncc[$novel['upid']]['catname'];
$novel['lastupdate'] = strftime('%Y-%m-%d %X',$novel['lastupdate']);

$volumechapter = "";
$volumenum = 0;
$vquery = DB::query("SELECT * FROM ".DB::table('pdnovel_volume')." WHERE novelid=$novelid ORDER BY volumeid ASC");
while ($volume = DB::fetch($vquery)){
	$volumenum++;
	$volumechapter .= '<div class="contenttitle"><h2><span>'.$volumenum.'</span>《'.$novel[name].'》'.$volume[volumename].'</h2></div><div class="contentlist"><ul>';
	
	$cquery = DB::query("SELECT * FROM ".DB::table('pdnovel_chapter')." WHERE novelid=$novelid AND volumeid=$volume[volumeid] ORDER BY chapterid ASC");
	while ($chapter = DB::fetch($cquery)) {
		$chapter['lastupdate']=strftime ("%Y-%m-%d %X",$chapter['lastupdate']);
		$volumechapter .= '<li style="width:25%;"><a href="novel.php?mod=read&novelid='.$novelid.'&chapterid='.$chapter[chapterid].'" title="'.$chapter[chaptername].'  '.$pdlang[chapter_words].$chapter[chapterwords].'  '.$pdlang[chapter_updatetime].$chapter[lastupdate].'">'.$chapter[chaptername].'</a></li>';
	}
	
	$volumechapter .= '</ul></div>';
}

$navtitle = $novel['name'].' - '.$catname.' - '.$upname.' - '.$navtitle;
$metakeywords = $novel['name'].','.$novel['author'].','.$novel['keywords'];
$metadescription = substr($novel['intro'],0,200);

pdupdateclick($novelid, $novel['lastvisit']);
loadcache( "diytemplatename" );
include template('diy:pdnovel/chapter');
?>