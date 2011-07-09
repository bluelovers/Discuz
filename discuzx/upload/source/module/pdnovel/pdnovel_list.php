<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

loadcache('pdnovelcategory');
$ncc = $_G['cache']['pdnovelcategory'];

$catid = $_G['gp_catid'] ? $_G['gp_catid'] : 0;
$full = $_G['gp_full'] ? $_G['gp_full'] : 0;
$words = $_G['gp_words'] ? $_G['gp_words'] : 0;
$update = $_G['gp_update'] ? $_G['gp_update'] : 0;
$letter = $_G['gp_letter'] ? $_G['gp_letter'] : 0;
$orderby = $_G['gp_orderby'] ? $_G['gp_orderby'] : 0;
$page = $_G['gp_page'] ? $_G['gp_page'] : 1;

$sql = $orderbyadd = "";

if($catid>0){
	$category = DB::fetch_first("SELECT * FROM ".DB::table('pdnovel_category')." WHERE catid=$catid");
	if($category){
		$navtitle = $category['catname'].' - '.($category['upid']>0?$ncc[$category['upid']]['catname'].' - ':'').$navtitle;
		$metakeywords = $category['keyword'];
		$metadescription = $category['description'];
	}else{
		showmessage($lang['novel_error']);
	}
	if($ncc[$catid][children]){
		$i=0;
		foreach($ncc[$catid][children] as $ncccatids){
			$i++;
			$catids .= $i==count($ncc[$catid][children])?$ncccatids:$ncccatids.','; 
		}
	}else{
		$catids = $catid;
	}
	$sql .= " AND catid in ($catids)";
}

$perpage = 50;
$limit_start = $perpage * ($page - 1);

$fullarr[0] = "";
$fullarr[1] = " AND full=1";
$fullarr[2] = " AND full=0";

$wordsarr[0] = "";
$wordsarr[1] = " AND words<300000";
$wordsarr[2] = " AND words>299999 AND words<500000";
$wordsarr[3] = " AND words>499999 AND words<1000000";
$wordsarr[4] = " AND words>999999 AND words<2000000";
$wordsarr[5] = " AND words>1999999";

$updatearr[0] = "";
$updatearr[1] = " AND lastupdate>".(time()-86400);
$updatearr[2] = " AND lastupdate>".(time()-259200);
$updatearr[3] = " AND lastupdate>".(time()-432000);
$updatearr[4] = " AND lastupdate>".(time()-604800);
$updatearr[5] = " AND lastupdate>".(time()-1296000);
$updatearr[6] = " AND lastupdate>".(time()-2592000);

$letterarr[0] = "";
$letterarr[1] = " AND initial=1";
$letterarr['a'] = " AND initial='a'";
$letterarr['b'] = " AND initial='b'";
$letterarr['c'] = " AND initial='c'";
$letterarr['d'] = " AND initial='d'";
$letterarr['e'] = " AND initial='e'";
$letterarr['f'] = " AND initial='f'";
$letterarr['g'] = " AND initial='g'";
$letterarr['h'] = " AND initial='h'";
$letterarr['i'] = " AND initial='i'";
$letterarr['j'] = " AND initial='j'";
$letterarr['k'] = " AND initial='k'";
$letterarr['l'] = " AND initial='l'";
$letterarr['m'] = " AND initial='m'";
$letterarr['n'] = " AND initial='n'";
$letterarr['o'] = " AND initial='o'";
$letterarr['p'] = " AND initial='p'";
$letterarr['q'] = " AND initial='q'";
$letterarr['r'] = " AND initial='r'";
$letterarr['s'] = " AND initial='s'";
$letterarr['t'] = " AND initial='t'";
$letterarr['u'] = " AND initial='u'";
$letterarr['v'] = " AND initial='v'";
$letterarr['w'] = " AND initial='w'";
$letterarr['x'] = " AND initial='x'";
$letterarr['y'] = " AND initial='y'";
$letterarr['z'] = " AND initial='z'";

$orderbyarr[0] = "lastupdate DESC";
$orderbyarr[1] = "dayvisit DESC";
$orderbyarr[2] = "weekvisit DESC";
$orderbyarr[3] = "monthvisit DESC";
$orderbyarr[4] = "allvisit DESC";
$orderbyarr[5] = "dayvote DESC";
$orderbyarr[6] = "weekvote DESC";
$orderbyarr[7] = "monthvote DESC";
$orderbyarr[8] = "allvote DESC";
$orderbyarr[9] = "words DESC";
$orderbyarr[10] = "allmark DESC";
$orderbyarr[11] = "click DESC";
$ordrbyadd = $orderbyarr[$orderby] ? $orderbyarr[$orderby] : $orderbyarr[0];

$sql .= $fullarr[$full].$wordsarr[$words].$updatearr[$update].$letterarr[$letter];

$query = DB::query("SELECT * FROM ".DB::table('pdnovel_view')." WHERE display=0 $sql ORDER BY $ordrbyadd LIMIT $limit_start,$perpage");
$novellist = array();
while ($novel = DB::fetch($query)){
	$novel['lastupdate'] = strftime("%y-%m-%d %H:%M",$novel['lastupdate']);
	$novel['catname'] = $ncc[$novel['catid']]['catname'];
	$novel['upid'] = $ncc[$novel['catid']]['upid'];
	$novel['upname'] = $ncc[$novel['upid']]['catname'];
	$novel['lastchapter'] = mb_substr($novel['lastchapter'], 0, 15, 'gb2312');
	$novellist[] = $novel;
	
}

$novelcount = DB::result_first("SELECT count(*) FROM ".DB::table('pdnovel_view')." WHERE display=0 $sql");
$allnovel= DB::result_first("SELECT count(*) FROM ".DB::table('pdnovel_view').";");
$multi = multipage($novelcount, $perpage, $page, "novel.php?mod=list&catid=$catid&full=$full&words=$words&update=$update&letter=$letter&orderby=$orderby");

$nccup = $ncc[$catid]['upid'];
$query = DB::query("SELECT * FROM ".DB::table('pdnovel_category')." WHERE upid=0");
$selectul = '<li><a href="novel.php?mod=list&catid=0&full='.$full.'&words='.$words.'&update='.$update.'&letter='.$letter.'&orderby='.$orderby.'" '.($catid==0?'class="licurrent"':'class="hand1"').' title="'.$lang['list_all'].'">'.$lang['list_all'].'</a></li>';
while ($nccul = DB::fetch($query)){
	if($nccup==0){
		$selectul .= '<li><a href="novel.php?mod=list&catid='.$nccul['catid'].'&full='.$full.'&words='.$words.'&update='.$update.'&letter='.$letter.'&orderby='.$orderby.'" '.($catid==$nccul['catid']?'class="licurrent"':'class="hand1"').' title="'.$nccul['catname'].'">'.$nccul['catname'].'</a></li>';
	}else{
		$selectul .= '<li><a href="novel.php?mod=list&catid='.$nccul['catid'].'&full='.$full.'&words='.$words.'&update='.$update.'&letter='.$letter.'&orderby='.$orderby.'" '.($nccup==$nccul['catid']?'class="licurrent"':'class="hand1"').' title="'.$nccul['catname'].'">'.$nccul['catname'].'</a></li>';
	}
}
if($nccup==0){
	$selectyy = '<li><a href="novel.php?mod=list&catid='.$catid.'&full='.$full.'&words='.$words.'&update='.$update.'&letter='.$letter.'&orderby='.$orderby.'" class="selectfont" title="'.$lang['list_all'].'">'.$lang['list_all'].'</a></li>';
	foreach($ncc[$catid][children] as $nccyy){
		$selectyy .= '<li><a href="novel.php?mod=list&catid='.$nccyy.'&full='.$full.'&words='.$words.'&update='.$update.'&letter='.$letter.'&orderby='.$orderby.'" class="hand1" title="'.$ncc[$nccyy][caption].'">'.$ncc[$nccyy]['catname'].'</a></li>';
	}
}else{
	$selectyy = '<li><a href="novel.php?mod=list&catid='.$nccup.'&full='.$full.'&words='.$words.'&update='.$update.'&letter='.$letter.'&orderby='.$orderby.'" class="hand1" title="'.$lang['list_all'].'">'.$lang['list_all'].'</a></li>';
	foreach($ncc[$nccup][children] as $nccyy){
		$selectyy .= '<li><a href="novel.php?mod=list&catid='.$nccyy.'&full='.$full.'&words='.$words.'&update='.$update.'&letter='.$letter.'&orderby='.$orderby.'" '.($catid==$nccyy?'class="selectfont"':'class="hand1"').' title="'.$ncc[$nccyy][caption].'">'.$ncc[$nccyy]['catname'].'</a></li>';
	}
}
loadcache( "diytemplatename" );
include template('diy:pdnovel/list');

?>