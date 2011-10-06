<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (!$_G['uid'] || $_G['adminid'] != 1) {
	showmessage('admin_nopermission', NULL);
}

if (!$thread) {
	showmessage('admin_nopermission', NULL);
}

$authoridnew = &$_G['gp_authoridnew'];

$authoridnew = intval($authoridnew);

$tid = $thread['tid'];

$topiclist = (array)$_G['gp_topiclist'];
if (!empty($topiclist)) {
	$topiclist = array_map('intval', $topiclist);
	$topiclist = array_unique($topiclist);

	sort($topiclist);
}

$modpostsnum = count($topiclist);

$_post = array();
if ($modpostsnum == 1) {
	$_post = get_post_by_pid($topiclist[0]);

	if ($_post['first']) {
		unset($topiclist);
		$modpostsnum = 0;
	}

	if ($_post['tid'] != $tid) {
		showmessage('admin_nopermission', NULL);
	}
}

if (!submitcheck('modsubmit')) {

	include_once libfile('class/sco_dx_plugin', 'source', 'extensions/');

	$_p = new _sco_dx_plugin();
	$_p->identifier = 'sco_cpanel';

	if (!empty($topiclist)) {
		discuz_core::$tpl['forum']['return_mods_title'][$_G['gp_action']] = strtr(lang('forum/template', 'admin_select_piece'), array('$modpostsnum' => $modpostsnum));
	}

	discuz_core::$tpl['forum']['topicadmin_action'][$_G['gp_action']] = $_p->_fetch_template($_p->_template('forum/topicadmin_action_author'), array(
		'topiclist' => $topiclist,
	));

	include template('forum/topicadmin_action');
} else {

	include_once libfile('function/home');

	$authornew = array();

	if (empty($authoridnew)) {
		showmessage('admin_author_authoridnew_invalid');
	} elseif ($authoridnew > 0) {
		$authornew = getspace($authoridnew);

		if (!$authornew['uid'] || $authornew['uid'] != $authoridnew) {
			showmessage('admin_author_authoridnew_invalid');
		}
	} else {
		$_groupid = 18;

		$authornew = DB::fetch_first("
			SELECT *
			FROM ".DB::table('common_member')."
			WHERE
				`groupid` = '{$_groupid}'
			ORDER BY
				RAND()
			LIMIT 1
		");

		if (!$authornew['uid']) {
			showmessage('admin_author_authoridnew_invalid');
		} else {
			$authoridnew = $authornew['uid'];
		}
	}

	$authornew = daddslashes($authornew);

	$posttable = getposttablebytid($tid);

	$firstpost = DB::fetch_first("SELECT * FROM ".DB::table($posttable)." WHERE tid = '$tid' AND first = '1' LIMIT 1");

	if ($topiclist) {
		$_ids = dimplode($topiclist);

		$query = DB::query("SELECT * FROM ".DB::table($posttable)." WHERE tid = '$tid' AND pid IN ($_ids) ORDER BY pid ASC");
	}

	DB::update($posttable, array(
		'author' => $authornew['username'],
		'authorid' => $authornew['uid'],
	), array(
		'tid' => $tid,
		'authorid' => $thread['authorid'],
	));

	foreach(array(
		'forum_thread',
		'forum_forumrecommend',
	) as $_t) {
		DB::update($_t, array(
			'author' => $authornew['username'],
			'authorid' => $authornew['uid'],
		), array(
			'tid' => $tid,
		));
	}

	foreach(array(
		'forum_attachment',
		getattachtablebytid($tid),
	) as $_t) {
		DB::update($_t, array(
			'uid' => $authornew['uid'],
		), "tid = '$tid'
			AND (
				uid = '$thread[authorid]'
				OR pid = '$firstpost[pid]'
			)
		");
	}

		DB::update('forum_thread_rewardlog', array(
			'authorid' => $authornew['uid'],
		), array(
			'tid' => $tid,
		));

	foreach(array(
		'forum_trade',
		'forum_tradelog',
	) as $_t) {
		DB::update($_t, array(
			'seller' => $authornew['username'],
			'sellerid' => $authornew['uid'],
		), array(
			'tid' => $tid,
		));
	}

	$resultarray = array();

	$resultarray['redirect'] = dreferer("forum.php?mod=viewthread&tid=$tid");

	showmessage((isset($resultarray['message']) ? $resultarray['message'] : 'admin_succeed'), $resultarray['redirect']);

	/*
	$_tables = array(
		'authorid' => array(
			'forum_thread_rewardlog',
			'forum_thread',
			'forum_forumrecommend',

			'forum_post',
		),

		'author' => array(
			'forum_thread',
			'forum_forumrecommend',

			'forum_post',
		),

		'uid' => array(
			'forum_attachment',
			'home_feed',
		),
		'username' => array(
			'home_feed',
		),

		'sellerid' => array(
			'forum_trade',
			'forum_tradelog',
		),
		'seller' => array(
			'forum_trade',
			'forum_tradelog',
		),
	);
	*/
}

?>