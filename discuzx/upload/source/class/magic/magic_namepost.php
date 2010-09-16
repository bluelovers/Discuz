<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_namepost.php 13898 2010-08-03 02:22:52Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_namepost {

	var $version = '1.0';
	var $name = 'namepost_name';
	var $description = 'namepost_desc';
	var $price = '10';
	var $weight = '10';
	var $targetgroupperm = true;
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
		global $_G;
		$settings = array(
			'fids' => array(
				'title' => 'namepost_forum',
				'type' => 'mselect',
				'value' => array(),
			),
		);
		loadcache('forums');
		$settings['fids']['value'][] = array(0, '&nbsp;');
		if(empty($_G['cache']['forums'])) $_G['cache']['forums'] = array();
		foreach($_G['cache']['forums'] as $fid => $forum) {
			$settings['fids']['value'][] = array($fid, ($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')).$forum['name']);
		}
		$magic['fids'] = explode("\t", $magic['forum']);

		return $settings;
	}

	function setsetting(&$magicnew, &$parameters) {
		global $_G;
		$magicnew['forum'] = is_array($parameters['fids']) && !empty($parameters['fids']) ? implode("\t",$parameters['fids']) : '';
	}

	function usesubmit() {
		global $_G;
		if(empty($_G['gp_pid'])) {
			showmessage(lang('magic/namepost', 'namepost_info_nonexistence'));
		}
		$_G['tid'] = $_G['gp_ptid'];

		$post = getpostinfo($_G['gp_pid'], 'pid', array('p.first', 'p.tid', 'p.fid', 'p.authorid', 'p.dateline', 'p.anonymous'));
		$this->_check($post);

		$query = DB::query("SELECT username FROM ".DB::table('common_member')." WHERE uid='$post[authorid]'");
		$author = daddslashes(DB::result($query, 0), 1);

		$thread = getpostinfo($post['tid'], 'tid', array('tid', 'subject', 'author', 'replies', 'lastposter'));
		$posttable = getposttablebytid($post['tid']);
		if($post['first']) {
			$lastposter = $thread['replies'] > 0 ? $thread['lastposter'] : $author;
			DB::query("UPDATE ".DB::table($posttable)." SET anonymous='0' WHERE tid='$post[tid]' AND first='1'");
		} else {
			$lastposter = $author;
			$author = $thread['author'];
			DB::query("UPDATE ".DB::table($posttable)." SET anonymous='0' WHERE pid='$_G[gp_pid]'");
		}

		$forum['lastpost'] = explode("\t", DB::result_first("SELECT lastpost FROM ".DB::table('forum_forum')." WHERE fid='$post[fid]'"));

		if($thread['subject'] == $forum['lastpost'][1] && ($forum['lastpost'][3] == '' && $post['anonymous'])) {
			$lastpost = "$thread[tid]\t$thread[subject]\t$_G[timestamp]\t$lastposter";
			DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost' WHERE fid='$post[fid]'", 'UNBUFFERED');
		}

		DB::query("UPDATE ".DB::table('forum_thread')." SET author='$author', lastposter='$lastposter' WHERE tid='$post[tid]'");

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, 'tid', $_G['gp_tid']);

		if($post['authorid'] != $_G['uid']) {
			notification_add($post['authorid'], 'magic', lang('magic/namepost', 'namepost_notification'), array('pid' => $_G['gp_pid'], 'tid' => $_G['gp_tid'], 'subject' => $thread['subject'], 'magicname' => $this->magic['name']));
		}

		showmessage(lang('magic/namepost', 'namepost_succeed'), dreferer(), array(), array('showdialog' => 1, 'locationtime' => true));
	}

	function show() {
		global $_G;
		$pid = !empty($_G['gp_id']) ? htmlspecialchars($_G['gp_id']) : '';
		list($pid, $_G['tid']) = explode(':', $pid);
		if($tid) {
			$post = getpostinfo($_G['gp_id'], 'pid', array('p.fid', 'p.authorid'));
			$this->_check($post);
		}
		magicshowtype('top');
		magicshowsetting(lang('magic/namepost', 'namepost_info'), 'pid', $pid, 'text');
		magicshowsetting('', 'ptid', $_G['tid'], 'hidden');
		magicshowtype('bottom');
	}

	function buy() {
		global $_G;
		if(!empty($_G['gp_id'])) {
			list($_G['gp_id'], $_G['tid']) = explode(':', $_G['gp_id']);
			$post = getpostinfo($_G['gp_id'], 'pid', array('p.fid', 'p.authorid'));
			$this->_check($post);
		}
	}

	function _check($post) {
		global $_G;
		if(!checkmagicperm($this->parameters['forum'], $post['fid'])) {
			showmessage(lang('magic/namepost', 'namepost_info_noperm'));
		}
		$member = getuserbyuid($post['authorid']);
		if(!checkmagicperm($this->parameters['targetgroups'], $member['groupid'])) {
			showmessage(lang('magic/namepost', 'namepost_info_user_noperm'));
		}
	}

}

?>