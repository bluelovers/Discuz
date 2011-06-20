<?php

/*
* 
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
global $_G;
loadcache('plugin');
$team = $_G['cache']['plugin']['team'];
$open = $team['open'];
if ($open == '1') {
$money = "extcredits".$team['money'];
$moneyname = $_G['setting']['extcredits'][$team['money']]['title'];
$moneynum = $team['moneynum'];
$moneymax = $team['moneymax'];
$monthpostnum = $team['monthpostnum'];
$digestpostnum = $team['digestpostnum'];
$modactionnum = $team['modactionnum'];
$thismonthnum = $team['thismonthnum'];
$gjdata = $team['gjdata'];
$adminidgroup = $team['adminidgroup'];
$date=date("Y-m");

$query = DB::query("SELECT uid, username, adminid FROM ".DB::table('common_member')." WHERE adminid IN ($adminidgroup)");
while($member = DB::fetch($query)) {
$uid = $member['uid'];
$username = $member['username'];
$authorid = $uid;
$timeago=$_G['timestamp']-86400*30;
$monthpost = DB::result_first("SELECT count(*) FROM ".DB::table("forum_post")." WHERE `authorid` ='$uid' AND `dateline` >='$timeago'");
$starttime = gmdate("Y-m-1", TIMESTAMP-86400*30);
$modactions = DB::result_first("SELECT sum(count) AS actioncount FROM ".DB::table('forum_modwork')." WHERE uid ='$uid' AND dateline>='$starttime'");
$digestposts = DB::result_first("SELECT count(*) FROM ".DB::table("forum_thread")." WHERE `authorid` ='$uid' AND digest IN (1, 2, 3) AND `dateline` >='$timeago'");
$thismonth = DB::result_first("SELECT thismonth FROM ".DB::table("common_onlinetime")." WHERE uid ='$uid'");
$thismonth = round($thismonth / 60, 2);
$modactions += 0;
$alldata += 0;
if( $modactions >= $gjdata  ){
	$alldata=$money + ($monthpost * $monthpostnum) + ($modactions * $modactionnum) + ($digestposts * $digestpostnum) + ($thismonth * $thismonthnum);
	$alldata=min($alldata,$moneymax);
	updatemembercount($uid , array($money => $alldata));
}
notification_add($uid,0,'尊敬的 '.$_G['setting']['bbname'].' 管理成员 '.$username.'，这是您在 '.$date.' 份的管理报告。<br>您出勤了 '.$thismonth.' 小时，您发了 '.$monthpost.' 篇帖子，其中精华帖 '.$digestposts.' 篇，您管理操作了 '.$modactions.' 次。<br>根据您在 '.$date.' 份的表现 '.$_G['setting']['bbname'].' 团队给您发放奖励 '.$moneyname.' + '.$alldata.' 个。<br>( 计算公式: 基本工资 '.$moneyname.' '.$moneynum.' 个 + 发帖数 '.$monthpost.' x '.$monthpostnum.' + 管理数 '.$modactions.' x '.$modactionnum.' + 精华帖 '.$digestposts.' x '.$digestpostnum.' + 出勤 '.$thismonth.' x '.$thismonthnum.' )。<br>感谢您勤劳的管理，管理报告每月统计1次，奖励每月发放1次，请您继续努力为 '.$_G['setting']['bbname'].' 的发展作出更多贡献！<br>注：每月必须超出'.$gjdata.'次以上管理才有奖励，最高奖励上限 '.$moneyname.' '.$moneymax.' 个。<br><br>'.$_G['setting']['bbname'].'管理组。',0,1);
}
}
?>