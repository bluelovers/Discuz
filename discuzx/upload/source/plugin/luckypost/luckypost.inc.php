<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(!$_G['uid']) {
	showmessage('to_login', '', '', array('login' => 1));
}

$acarray = array('list', 'stat');
$action = !in_array($_G['gp_ac'], $acarray) ? 'list' : $_G['gp_ac'];
$list = $myfortune_arr = array();
$myfortune_arr = DB::fetch_first("SELECT * FROM ".DB::table('common_plugin_luckypostlog')." WHERE uid='{$_G['uid']}' LIMIT 1");
$gdtimes = $myfortune_arr['goodtimes'] ? $myfortune_arr['goodtimes'] : 0;
$bdtimes = $myfortune_arr['badtimes'] ? $myfortune_arr['badtimes'] : 0;
$myfortune = lang('plugin/luckypost', 'myfortune', array('$goodtimes' => $gdtimes, '$badtimes' => $bdtimes));
if($action == 'list') {
	$op = isset($_G['gp_op']) ? $_G['gp_op'] : '';
	$page_url = "plugin.php?id=luckypost";
	if($op == 'my') {
		$wheresql = " AND uid='{$_G['uid']}'";
		$page_url .= "&op=my";
		$action = 'my';
	}
	$pn = 10;
	$page = isset($_G['gp_page']) ? max(1, intval($_G['gp_page'])) : 1;
	$start_limit = ($page-1)*$pn;
	$query = DB::query("SELECT * FROM ".DB::table('common_plugin_luckypost').' WHERE pid > 0'.$wheresql." ORDER BY `lid` DESC LIMIT $start_limit, $pn");

	$events = $rewards = $punishs = array();

	$rewards = explode("\n", str_replace(array("\r\n", "\r"), "\n", $_G['cache']['plugin']['luckypost']['rewardevent']));
	foreach($rewards as $reward) {
		$events[1][] = explode('|', $reward);
	}
	$punishs = explode("\n", str_replace(array("\r\n", "\r"), "\n", $_G['cache']['plugin']['luckypost']['punishevent']));
	foreach($punishs as $punish) {
		$events[0][] = explode('|', $punish);
	}

	$totalnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_plugin_luckypost').' WHERE  pid > 0'.$wheresql);
	while($result = DB::fetch($query)) {
		$member = getuserbyuid($result['uid']);
		$event = $result['credits'] > 0 ? 1 : 0;
		$extcredits = $_G['setting']['extcredits'][$events[$event][$result['eventid']][0]]['img'].$_G['setting']['extcredits'][$events[$event][$result['eventid']][0]]['title'];
		$result['credits'] = abs($result['credits']).' '.$_G['setting']['extcredits'][$events[$event][$result['eventid']][0]]['unit'];
		$getmsg = str_replace(array('{username}', '{credit}', '{extcredits}'), array($member['username'], $result['credits'], $extcredits), $events[$event][$result['eventid']][2]);
		$result['event'] = $getmsg.lang('plugin/luckypost', 'creditlog', array('$extcredit' => $extcredits, '$credit' =>  $event ? $result['credits'] : '-'.$result['credits']));
		$result['username'] = $member['username'];
		$list[] = $result;
	}

	$multipage = multi($totalnum, $pn, $page, $page_url);
} elseif($action == 'stat') {
	$gllist = $bllist = $lucklist = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_plugin_luckypostlog')." ORDER BY goodtimes DESC LIMIT 10");
	while($result = DB::fetch($query)) {
		$result['times'] = $result['goodtimes'];
		$member = getuserbyuid($result['uid']);
		$result['username'] = $member['username'];
		$gllist[] = $result;
	}
	$query = DB::query("SELECT * FROM ".DB::table('common_plugin_luckypostlog')." ORDER BY badtimes DESC LIMIT 10");
	while($result = DB::fetch($query)) {
		$result['times'] = $result['badtimes'];
		$member = getuserbyuid($result['uid']);
		$result['username'] = $member['username'];
		$bllist[] = $result;
	}
	$lucklist[] = $gllist;
	$lucklist[] = $bllist;
}
$opactives = array($action =>' class="a"');
include template('luckypost:luckypost');
?>