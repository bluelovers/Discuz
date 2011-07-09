<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

loadcache('pdnovelcategory');
$ncc = $_G['cache']['pdnovelcategory'];
$coverpath = 'data/attachment/pdnovel/cover/';
$page = $_G['gp_page'] ? $_G['gp_page'] : 1;
$perpage = 10;
$limit_start = $perpage * ($page - 1);

$ac = $_G['gp_ac'];

$srchtxt = $_G['gp_srchtxt'];
$keyword = str_replace(' ','',$srchtxt);

if(!$keyword){
	showmessage('对不起，关键字不能为空');
}

if($ac == 'author'){
	$query = DB::query("SELECT * FROM ".DB::table('pdnovel_view')." WHERE display=0 AND author like '%$keyword%' ORDER BY lastupdate DESC LIMIT $limit_start,$perpage");
	$num = DB::result_first("SELECT count(novelid) FROM ".DB::table('pdnovel_view')." WHERE display=0 AND author like '%$keyword%'");
}elseif($ac == 'name'){
	$query = DB::query("SELECT * FROM ".DB::table('pdnovel_view')." WHERE display=0 AND name like '%$keyword%' ORDER BY lastupdate DESC LIMIT $limit_start,$perpage");
	$num = DB::result_first("SELECT count(novelid) FROM ".DB::table('pdnovel_view')." WHERE display=0 AND name like '%$keyword%'");
}elseif($ac == 'authorid'){
	$query = DB::query("SELECT * FROM ".DB::table('pdnovel_view')." WHERE display=0 AND authorid='$keyword' ORDER BY lastupdate DESC LIMIT $limit_start,$perpage");
	$num = DB::result_first("SELECT count(novelid) FROM ".DB::table('pdnovel_view')." WHERE display=0 AND authorid='$keyword'");
}

$novellist = array();
while($novel = DB::fetch($query)){
	$novel['full'] = $novel['full']==1?$lang['full']:$lang['nofull'];
	$novel['catname'] = $ncc[$novel['catid']]['catname'];
	$novel['lastupdate'] = strftime('%Y-%m-%d %X',$novel['lastupdate']);
	$novellist[] = $novel;
}
$mpurl = "novel.php?mod=search&ac=$ac&srchtxt=$keyword&searchsubmit=yes";
$multi =  multi($num, $perpage, $page, $mpurl);

include_once template('pdnovel/search');
?>