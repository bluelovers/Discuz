<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = in_array($_G['gp_op'], array('show', 'add')) ? $_G['gp_op'] : '';

$tid = $_G['gp_tid'] - 0;
$idtype = 'tid';
$sql = "SELECT * FROM ".DB::table('common_plugin_threadclick')." WHERE tid='$tid' LIMIT 1";
$query = DB::query($sql);
if(!$item = DB::fetch($query)) {
	require_once libfile('function/forum');
	$thread = get_thread_by_tid($tid, 'authorid');
	DB::query("INSERT INTO ".DB::table('common_plugin_threadclick')." SET tid='$tid', uid='$thread[authorid]'");
}

$hash = formhash($_G['uid']."\t".$tid);
if($op == 'add') {
	if(empty($_G['uid'])) {
		showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
	}

	if(!checkperm('allowclick') || $_GET['hash'] != $hash) {
		showmessage('no_privilege_click');
	}

	if($item['uid'] == $_G['uid']) {
		showmessage('click_no_self');
	}

	$clickid = intval($_G['gp_clickid']);
	$query = DB::query("SELECT * FROM ".DB::table('home_clickuser')." WHERE uid='$_G[uid]' AND id='$tid' AND idtype='tid'");
	if($value = DB::fetch($query)) {
		showmessage('click_have');
	}

	$setarr = array(
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'id' => $tid,
		'idtype' => 'tid',
		'clickid' => $clickid,
		'dateline' => $_G['timestamp']
	);
	DB::insert('home_clickuser', $setarr);
	DB::query("UPDATE ".DB::table('common_plugin_threadclick')." SET click{$clickid}=click{$clickid}+1 WHERE tid='$tid' LIMIT 1");

	require_once libfile('function/forum');
	$thread = get_thread_by_tid($tid, 'subject');
	$q_note_values = array(
		'url' => "forum.php?mod=viewthread&tid=$tid",
		'subject' => $thread['subject'],
		'from_id' => $item['tid'],
		'from_idtype' => 'tid'
	);

	notification_add($item['uid'], 'clickthread', 'threadclick:note', $q_note_values);
	showmessage('click_success', '', array('idtype' => 'tid', 'tid' => $tid, 'clickid' => $clickid), array('msgtype' => 3, 'showmsg' => true, 'closetime' => true));
} elseif($op == 'show') {
	$maxclicknum = 0;
	loadcache('click');
	$clicks = empty($_G['cache']['click']['tid']) ? array() : $_G['cache']['click']['tid'];

	foreach($clicks as $key => $value) {
		$value['clicknum'] = $item["click{$key}"];
		$value['classid'] = mt_rand(1, 4);
		if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];
		$clicks[$key] = $value;
	}

	$perpage = 22;
	$page = intval($_GET['page']);
	$start = ($page-1)*$perpage;
	if($start < 0) $start = 0;

	$count = getcount('home_clickuser', array('id'=>$tid, 'idtype'=>'tid'));
	$clickuserlist = array();
	$click_multi = '';

	if($count) {
		$query = DB::query("SELECT * FROM ".DB::table('home_clickuser')."
			WHERE id='$tid' AND idtype='tid'
			ORDER BY dateline DESC
			LIMIT $start,$perpage");
		while($value = DB::fetch($query)) {
			$value['clickname'] = $clicks[$value['clickid']]['name'];
			$clickuserlist[] = $value;
		}

		$click_multi = multi($count, $perpage, $page, "plugin.php?id=threadclick&op=show&clickid=$clickid&idtype=tid&tid=$tid");
	}

	include_once template('threadclick:click');

}
?>