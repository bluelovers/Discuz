<?php

/*
	每日抢楼签到 for DX 1.5
	Powered by Donglin8.Com 2010.10
*/

if(!defined('IN_DISCUZ')) {
		exit('Access Denied');
}
$tid = intval($_G['gp_tid']);
if (!$_G['uid']) {
	showmessage('游客不能领取奖金，请返回。', NULL, 'HALTED');
}
loadcache('plugin');
$config=$_G['cache']['plugin']['donglin8_signin'];	
$thread = db::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid = '$tid'");
if (!$thread) {
	header('HTTP/1.0 404 Not Found');
	showmessage('thread_nonexistence');
}

if (!class_exists('threadplugin_donglin8_signin')) {
	require_once dirname(__FILE__).'/donglin8_signin.class.php';
}

$gsobj = new threadplugin_donglin8_signin();

$todayzero = strtotime('today');
$signin_begint = $config['donglin8_begin'] * 3600 + $todayzero;
$signin_endt = $config['donglin8_end'] * 3600 + $todayzero;

if ($thread['dateline'] < $todayzero) {
	showmessage('您只能在今日的签到帖中领取奖金，请返回。', NULL, 'HALTED');
}

$rpost = db::fetch_first("SELECT p.*, gs.bonused FROM ".DB::table('forum_post')." AS p
	LEFT JOIN ".DB::table('forum_donglin8_signin')." AS gs ON(gs.pid = p.pid)
	WHERE p.tid = '$tid' AND p.authorid = '$_G[uid]' ORDER BY p.dateline ASC LIMIT 1");
if (!$rpost) {
	showmessage('您还没有签到，不能领取奖金，请先返回，必须回复后签到才能领分。', NULL, 'HALTED');
}

if ($rpost['first'] == 1 || $rpost['authorid'] == $thread['authorid']) {
	showmessage('您是楼主，你的奖金在你抢到楼的时候系统就已经加上，请返回。', NULL, 'HALTED');
}

if ($rpost['bonused'] > 0) {
	showmessage('您今日已经领过奖金了，请返回。', NULL, 'HALTED');
}

if ($rpost['dateline'] < $signin_begint) {
	showmessage('未到领奖时间，必须于本日 '. $gsobj->config['donglin8_begin'].' 点后重新签到，请返回。');
} elseif ($rpost['dateline'] > $signin_endt) {
	showmessage('已超过领奖时限，明日请早，请返回。', NULL, 'HALTED');
}
        
$query = db::query("SELECT COUNT(p.pid) FROM ".DB::table('forum_post')." AS p
	LEFT JOIN ".DB::table('forum_donglin8_signin')." AS gs ON(gs.pid = p.pid)
	WHERE p.tid = '$tid' AND gs.bonused > 0 GROUP BY p.authorid");
$ecount = db::num_rows($query);

//签到奖励
$signin_bonus = $ecount < 10 ? $config['donglin8_bonus_top10'] : $config['donglin8_bonus'];
if (in_array($config['donglin8_extcreditx'], range(1,8))) {
	$ext3 = "extcredits".$config["donglin8_extcreditx"];
	DB::query("UPDATE ".DB::table('common_member_count')." SET ".$ext3. "= ".$ext3."+ ".$signin_bonus." WHERE uid='".$_G[uid]."'");
}
$timer = $_G['timestamp'] + 28757;
db::query("REPLACE INTO ".DB::table('forum_donglin8_signin')." (pid, bonused, bdateline) VALUES ('$rpost[pid]', '$signin_bonus', '".$timer."')", 'UNBUFFERED');
db::query("UPDATE ".DB::table('forum_post')." SET rate=rate+($signin_bonus), ratetimes=ratetimes+5 WHERE pid = '$rpost[pid]'", 'UNBUFFERED');
db::query("INSERT INTO ".DB::table('forum_ratelog')." (pid, uid, username, extcredits, dateline, score, reason)
	VALUES ('$rpost[pid]', '0', '系统奖励', '".$config['donglin8_extcreditx']."', '".$timer."', '$signin_bonus', '')", 'UNBUFFERED');
        
showmessage('恭喜您，领取奖金成功。', 'forum.php?mod=viewthread&tid='.$tid);

?>