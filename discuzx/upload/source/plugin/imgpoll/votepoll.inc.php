<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
	require_once libfile('function/post');
	
	if(!$_G['group']['allowvote']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	} elseif(!empty($thread['closed'])) {
		showmessage('thread_poll_closed', NULL, array(), array('login' => 1));
	} elseif(empty($_G['gp_pollanswers'])) {
		showmessage('thread_poll_invalid', NULL, array(), array('login' => 1));
	}
	
	$pollarray = DB::fetch_first("SELECT overt, maxchoices, expiration FROM ".DB::table('forum_imgpoll')." WHERE tid='$_G[gp_tid]'");
	$overt = $pollarray['overt'];
	
	if(!$pollarray) {
		showmessage('undefined_action', NULL);
	} elseif($pollarray['expiration'] && $pollarray['expiration'] < TIMESTAMP) {
		showmessage('poll_overdue', NULL, array(), array('login' => 1));
	} elseif($pollarray['maxchoices'] && $pollarray['maxchoices'] < count($_G['gp_pollanswers'])) {
		showmessage('poll_choose_most', NULL, array('maxchoices' => $pollarray['maxchoices']), array('login' => 1));
	}
	
	$voterids = $_G['uid'] ? $_G['uid'] : $_G['clientip'];

	$polloptionid = array();
	$query = DB::query("SELECT polloptionid, voterids FROM ".DB::table('forum_imgpolloption')." WHERE tid='$_G[gp_tid]'");
	while($pollarray = DB::fetch($query)) {
		if(strexists("\t".$pollarray['voterids']."\t", "\t".$voterids."\t")) {
			showmessage('thread_poll_voted', NULL, array(), array('login' => 1));
		}
		$polloptionid[] = $pollarray['polloptionid'];
	}
	
	$polloptionids = '';
	foreach($_G['gp_pollanswers'] as $key => $id) {
		if(!in_array($id, $polloptionid)) {
			showmessage('undefined_action', NULL);
		}
		unset($polloptionid[$key]);
		$polloptionids[] = $id;
	}

	$pollanswers = implode('\',\'', $polloptionids);

	DB::query("UPDATE ".DB::table('forum_imgpolloption')." SET votes=votes+1, voterids=CONCAT(voterids,'$voterids\t') WHERE polloptionid IN ('$pollanswers')", 'UNBUFFERED');
	DB::query("UPDATE ".DB::table('forum_thread')." SET lastpost='$_G[timestamp]' WHERE tid='$_G[gp_tid]'", 'UNBUFFERED');
	DB::query("UPDATE ".DB::table('forum_imgpoll')." SET voters=voters+1 WHERE tid='$_G[gp_tid]'", 'UNBUFFERED');
	

	DB::insert('forum_pollvoter', array(
		'tid' => $_G['gp_tid'],
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'options' => implode("\t", $_G['gp_pollanswers']),
		'dateline' => $_G['timestamp'],
		));

	updatecreditbyaction('joinpoll');

	$space = array();
	space_merge($space, 'field_home');

	if($overt && !empty($space['privacy']['feed']['newreply'])) {
		$feed['icon'] = 'poll';
		$feed['title_template'] = 'feed_thread_votepoll_title';
		$feed['title_data'] = array(
			'subject' => "<a href=\"forum.php?mod=viewthread&tid=$_G[gp_tid]\">$thread[subject]</a>",
			'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[author]</a>",
			'hash_data' => "tid{$_G[gp_tid]}"
		);
		$feed['id'] = $_G['gp_tid'];
		$feed['idtype'] = 'tid';
		postfeed($feed);
	}
	$posttable = getposttablebytid($_G['gp_tid']);
	$pid = DB::result_first("SELECT pid FROM ".DB::table($posttable)." WHERE tid='$_G[gp_tid]' AND first='1'");
	
	if(!empty($_G['inajax'])) {
		dheader("location: forum.php?mod=viewthread&tid=$_G[gp_tid]&viewpid=$pid&inajax=1");
	} else {
		showmessage('thread_poll_succeed', "forum.php?mod=viewthread&tid=$_G[gp_tid]".($_G['gp_from'] ? '&from='.$_G['gp_from'] : ''));
	}
?>