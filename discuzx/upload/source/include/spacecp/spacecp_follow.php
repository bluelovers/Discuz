<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_follow.php 27308 2012-01-13 08:33:43Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$ops = array('add', 'del', 'bkname', 'checkfeed', 'relay', 'getfeed', 'delete');
$op = in_array($_GET['op'], $ops) ? $_GET['op'] : '';

if($op == 'add') {
	$_GET['handlekey'] = $_GET['handlekey'] ? $_GET['handlekey'] : 'followmod';
	$followuid = intval($_GET['fuid']);
	if($_G['uid'] == $followuid) {
		showmessage('不能关注自己');
	}
	$special = intval($_GET['special']) ? intval($_GET['special']) : 0;
	$followuser = getuserbyuid($followuid);
	$mutual = 0;
	$followed = C::t('home_follow')->fetch_by_uid_followuid($followuid, $_G['uid']);
	if(!empty($followed)) {
		if($followed['status'] == '-1') {
			showmessage('对方不允许您关注TA');
		}
		$mutual = 1;
		C::t('home_follow')->update_by_uid_followuid($followuid, $_G['uid'], array('mutual'=>1));
	}
	$followed = C::t('home_follow')->fetch_by_uid_followuid($_G['uid'], $followuid);
	if(empty($followed)) {
		$followdata = array(
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'followuid' => $followuid,
			'fusername' => $followuser['username'],
			'status' => 0,
			'mutual' => $mutual,
			'dateline' => TIMESTAMP
		);
		C::t('home_follow')->insert($followdata, false, true);
		C::t('common_member_count')->increase($_G['uid'], array('following' => 1));
		C::t('common_member_count')->increase($followuid, array('follower' => 1, 'newfollower' => 1));
		notification_add($followuid, 'follower', 'member_follow_add', array('count' => $count, 'from_id'=>$_G['uid'], 'from_idtype' => 'following'), 1);
	} elseif($special) {
		$status = $special == 1 ? 1 : 0;
		C::t('home_follow')->update_by_uid_followuid($_G['uid'], $followuid, array('status'=>$status));
		$special = $special == 1 ? 2 : 1;
	}
	$type = !$special ? 'add' : 'special';
	showmessage('follow_add_succeed', dreferer(), array('fuid' => $followuid, 'type' => $type, 'special' => $special, 'from' => !empty($_GET['from']) ? $_GET['from'] : 'list'), array('closetime' => '2', 'showmsg' => '1'));
} elseif($op == 'del') {
	$_GET['handlekey'] = $_GET['handlekey'] ? $_GET['handlekey'] : 'followmod';
	$delfollowuid = intval($_GET['fuid']);
	$affectedrows = C::t('home_follow')->delete_by_uid_followuid($_G['uid'], $delfollowuid);
	if($affectedrows) {
		C::t('home_follow')->update_by_uid_followuid($delfollowuid, $_G['uid'], array('mutual'=>0));
		C::t('common_member_count')->increase($_G['uid'], array('following' => -1));
		C::t('common_member_count')->increase($delfollowuid, array('follower' => -1, 'newfollower' => -1));
	}
	showmessage('follow_cancel_succeed', dreferer(), array('fuid' => $delfollowuid, 'type' => 'del', 'from' => !empty($_GET['from']) ? $_GET['from'] : 'list'), array('closetime' => '2', 'showmsg' => '1'));
} elseif($op == 'bkname') {
	$followuid = intval($_GET['fuid']);
	$followuser = C::t('home_follow')->fetch_by_uid_followuid($_G['uid'], $followuid);
	if(empty($followuser)) {
		showmessage('follow_not_assignation_user');
	}
	if(submitcheck('editbkname')) {
		$bkname = cutstr(strip_tags($_GET['bkname']), 30, '');
		C::t('home_follow')->update_by_uid_followuid($_G['uid'], $followuid, array('bkname'=>$bkname));
		showmessage('follow_remark_succeed', dreferer(), array('bkname' => $bkname, 'btnstr' => empty($bkname) ? '添加备注' : '修改备注'), array('showdialog'=>true, 'closetime' => true));
	}
} elseif($op == 'relay') {

	$tid = intval($_GET['tid']);
	$preview = $post = array();
	$preview = C::t('forum_threadpreview')->fetch($tid);
	if(empty($preview)) {
		$post = C::t('forum_post')->fetch_threadpost_by_tid_invisible($tid);
		if($post['anonymous']) {
			showmessage('匿名帖子不允许被转播');
		}
	}
	if(empty($post) && empty($preview)) {
		showmessage('转播的内容不存在');
	}
	if(!submitcheck('relaysubmit')) {
		$user = getuserbyuid($feed['uid']);
	} else {

		if(strlen($_GET['note'])>140) {
			showmessage('您输入的转播理由超过140个字');
		}

		$followfeed = array(
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'tid' => $tid,
			'note' => dhtmlspecialchars($_GET['note']),
			'dateline' => TIMESTAMP
		);
		C::t('home_follow_feed')->insert($followfeed);
		C::t('common_member_count')->increase($_G['uid'], array('feeds'=>1));
		if(empty($preview)) {
			require_once libfile('function/discuzcode');
			require_once libfile('function/followcode');
			$feedcontent = array(
					'tid' => $tid,
					'content' => followcode($post['message'], $post['tid'], $post['pid'], 1000),
			);
			C::t('forum_threadpreview')->insert($feedcontent);
			C::t('forum_thread')->update_status_by_tid($tid, '512');
		} else {
			C::t('forum_threadpreview')->update_relay_by_tid($tid, 1);
		}
		$param = array();
		if($_GET['addnewreply']) {

			$_G['setting']['seccodestatus'] = 0;
			$_G['setting']['secqaa']['status'] = 0;

			$_POST['replysubmit'] = true;
			$_GET['tid'] = $msg['id'];
			$_GET['action'] = 'reply';
			$_GET['message'] = $_GET['note'];
			include_once libfile('function/forum');
			require_once libfile('function/post');
			loadforum();

			$inspacecpshare = 1;
			include_once libfile('forum/post', 'module');
		}

		showmessage('relay_feed_success', dreferer(), $param, array('showdialog'=>true, 'closetime' => true));
	}
} elseif($op == 'checkfeed') {
	require_once libfile("function/member");
	checkfollowfeed();
	exit;
} elseif($op == 'getfeed') {
	$archiver = $_GET['archiver'] ? true : false;
	$uid = intval($_GET['uid']);
	$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
	if($page<1) $page=1;
	$perpage = 20;
	$start = ($page-1)*$perpage;
	if($uid) {
		$list = getfollowfeed($uid, 'self', $archiver, $start, $perpage);
	} else {
		$type = in_array($_GET['viewtype'], array('special', 'follow', 'other')) ? $_GET['viewtype'] : 'follow';
		$list = getfollowfeed($type == 'other' ? 0 : $_G['uid'], $type, $archiver, $start, $perpage);
	}
	if(empty($list['feed'])) {
		$list = false;
	}
	if(!isset($_G['cache']['forums'])) {
		loadcache('forums');
	}
} elseif($op == 'delete') {
	$archiver = false;
	$feed = C::t('home_follow_feed')->fetch_by_feedid($_GET['feedid']);
	if(empty($feed)) {
		$feed = C::t('home_follow_feed')->fetch_by_feedid($_GET['feedid'], true);
		$archiver = true;
	}
	if(empty($feed)) {
		showmessage('指定的广播记录不存在', '', array(), array('return' => true));
	} elseif($feed['uid'] != $_G['uid'] && $_G['adminid'] != 1) {
		showmessage('您没有权限进行此操作', '', array(), array('return' => true));
	}

	if(submitcheck('deletesubmit')) {
		if(C::t('home_follow_feed')->delete_by_feedid($_GET['feedid'], $archiver)) {
			C::t('common_member_count')->increase($feed['uid'], array('feeds'=>-1));
			showmessage('do_success', dreferer(), array('feedid' => $_GET['feedid']), array('showdialog'=>1, 'showmsg' => true, 'closetime' => true));
		} else {
			showmessage('failed_to_delete_operation');
		}
	}
}
include template('home/spacecp_follow');
?>