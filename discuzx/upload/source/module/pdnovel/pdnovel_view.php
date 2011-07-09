<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

loadcache('pdnovelcategory');
$ncc = $_G['cache']['pdnovelcategory'];

$novelid = $_G['gp_novelid'];

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
$novel['lastupdate'] = strftime('%Y-%m-%d %X',$novel['lastupdate']);
$novel['vip'] = $novel['vip'] == 1 ? $lang['vip'] : $lang['novip'];
$novel['full'] = $novel['full'] == 1 ? $lang['full'] : $lang['nofull'];
$novel['permission'] = $novel['permission'] == 1 ? $lang['permission'] : $lang['nopermission'];
$novel['first'] = $novel['first'] == 1 ? $lang['first'] : $lang['nofirst'];
$novel['intro'] = mysql2html($novel['intro']);
$novel['lastchaptercontent'] = mysql2html($novel['lastchaptercontent']);

$catid = $novel['upid'];
if($catid>0){
	if($ncc[$catid][children]){
		$i=0;
		foreach($ncc[$catid][children] as $ncccatids){
			$i++;
			$catids .= $i==count($ncc[$catid][children])?$ncccatids:$ncccatids.','; 
		}
	}else{
		$catids = $catid;
	}
	$catidadd .= " AND catid in ($catids)";
}

$percentage = $width = array();
for($i=1;$i<6;$i++){
	$percentage[$i] = round($novel['click'.$i]*100/$novel[click],1);
	$width[$i] = ceil($percentage[$i]*0.7)+1;
	$sum_score += $novel['click'.$i]*$i;
}
$novel_score = round($sum_score*2/$novel[click],1);
$current_rating = $novel_score*10;
$current_i = ceil($novel_score/2);
$current_text = array($lang['star_0'], $lang['star_2'], $lang['star_4'], $lang['star_6'], $lang['star_8'], $lang['star_10']);

$rateloglist = array();
$query = DB::query("SELECT * FROM ".DB::table('pdnovel_rate')." WHERE novelid=$novelid ORDER BY dateline DESC LIMIT 9");
$sum_credits = DB::result_first("SELECT sum(credits) FROM ".DB::table('pdnovel_rate')." WHERE novelid=$novelid;");
while($ratelog=DB::fetch($query)){
	$ratelog['dateline'] = strftime('%m-%d',$ratelog['dateline']);
	$rateloglist[] = $ratelog;
}

$commentlist = array();
$query = DB::query("SELECT * FROM ".DB::table('pdnovel_comment')." WHERE novelid=$novelid ORDER BY dateline DESC LIMIT 10");
while($comment=DB::fetch($query)){
	$comment['dateline'] = strftime('%Y-%m-%d %X',$comment['dateline']);
	$commentlist[] = $comment;
}

$findvote = DB::result_first("SELECT uid FROM ".DB::table('pdnovel_vote')." WHERE uid='$_G[uid]' AND novelid='$novelid'");
$findvote = $findvote?$findvote:0;

$navtitle = $novel['name'].' - '.$catname.' - '.$upname.' - '.$navtitle;
$metakeywords = $novel['name'].','.$novel['author'].','.$novel['keywords'];
$metadescription = substr($novel['intro'],0,200);

pdupdateclick($novelid, $novel['lastvisit']);
loadcache( "diytemplatename" );
include template('diy:pdnovel/view');
?>