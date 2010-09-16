<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_anonymouspost.php 13898 2010-08-03 02:22:52Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_anonymouspost {

	var $version = '1.0';
	var $name = 'anonymouspost_name';
	var $description = 'anonymouspost_desc';
	var $price = '10';
	var $weight = '10';
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
		global $_G;
		$settings = array(
			'fids' => array(
				'title' => 'anonymouspost_forum',
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
			showmessage(lang('magic/anonymouspost', 'anonymouspost_info_nonexistence'));
		}
		$_G['tid'] = $_G['gp_ptid'];

		$post = getpostinfo($_G['gp_pid'], 'pid', array('p.first', 'p.tid', 'p.fid', 'p.authorid', 'p.dateline', 'p.anonymous'));
		$this->_check($post);

		if($post['authorid'] != $_G['uid']) {
			showmessage('magics_operation_nopermission');
		}

		$thread = getpostinfo($post['tid'], 'tid', array('tid', 'subject', 'author', 'replies', 'lastposter'));
		$posttable = getposttablebytid($post['tid']);
		if($post['first']) {
			$author = '';
			$lastposter = $thread['replies'] > 0 ? $thread['lastposter'] : '';
			DB::query("UPDATE ".DB::table($posttable)." SET anonymous='1' WHERE tid='$post[tid]' AND first='1'");
		} else {
			$author = $thread['author'];
			$lastposter = '';
			DB::query("UPDATE ".DB::table($posttable)." SET anonymous='1' WHERE pid='$_G[gp_pid]'");
		}

		$forum['lastpost'] = explode("\t", DB::result_first("SELECT lastpost FROM ".DB::table('forum_forum')." WHERE fid='$post[fid]'"));

		if($post['dateline'] == $forum['lastpost'][2] && ($post['author'] == $forum['lastpost'][3] || ($forum['lastpost'][3] == '' && $post['anonymous']))) {
			$lastpost = "$thread[tid]\t$thread[subject]\t$_G[timestamp]\t$lastposter";
			DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost' WHERE fid='$post[fid]'", 'UNBUFFERED');
		}

		DB::query("UPDATE ".DB::table('forum_thread')." SET author='$author', lastposter='$lastposter' WHERE tid='$post[tid]'");

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, 'tid', $_G['gp_tid']);

		showmessage(lang('magic/anonymouspost', 'anonymouspost_succeed'), dreferer(), array(), array('showdialog' => 1, 'locationtime' => true));
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
		magicshowsetting(lang('magic/anonymouspost', 'anonymouspost_info'), 'pid', $pid, 'text');
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
			showmessage(lang('magic/anonymouspost', 'anonymouspost_info_noperm'));
		}
		if($post['authorid'] != $_G['uid']) {
			showmessage(lang('magic/anonymouspost', 'anonymouspost_info_user_noperm'));
		}
	}

}

?>