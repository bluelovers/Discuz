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

$authoridnew = &$_G['gp_authoridnew'];

$authoridnew = intval($authoridnew);

if (!submitcheck('modsubmit')) {

	include_once libfile('class/sco_dx_plugin', 'source', 'extensions/');

	$_p = new _sco_dx_plugin();
	$_p->identifier = 'sco_cpanel';

	discuz_core::$tpl['forum']['topicadmin_action'][$_G['gp_action']] = $_p->_fetch_template($_p->_template('forum/topicadmin_action_author'));

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
	}

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
}

?>