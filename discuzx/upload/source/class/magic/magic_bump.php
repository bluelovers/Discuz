<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_bump.php 13898 2010-08-03 02:22:52Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_bump {

	var $version = '1.0';
	var $name = 'bump_name';
	var $description = 'bump_desc';
	var $price = '10';
	var $weight = '10';
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
		global $_G;
		$settings = array(
			'fids' => array(
				'title' => 'bump_forum',
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
		if(empty($_G['gp_tid'])) {
			showmessage(lang('magic/bump', 'bump_info_nonexistence'));
		}

		$thread = getpostinfo($_G['gp_tid'], 'tid', array('fid', 'authorid', 'subject'));
		$this->_check($thread['fid']);

		DB::query("UPDATE ".DB::table('forum_thread')." SET lastpost='".TIMESTAMP."', moderated='1' WHERE tid='$_G[gp_tid]'");

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, 'tid', $_G['gp_tid']);
		updatemagicthreadlog($_G['gp_tid'], $this->magic['magicid'], 'BMP');

		if($thread['authorid'] != $_G['uid']) {
			notification_add($thread['authorid'], 'magic', lang('magic/bump', 'bump_notification'), array('tid' => $_G['gp_tid'], 'subject' => $thread['subject'], 'magicname' => $this->magic['name']));
		}

		showmessage(lang('magic/bump', 'bump_succeed'), dreferer(), array(), array('showdialog' => 1, 'locationtime' => true));
	}

	function show() {
		global $_G;
		$tid = !empty($_G['gp_id']) ? htmlspecialchars($_G['gp_id']) : '';
		if($tid) {
			$thread = getpostinfo($_G['gp_id'], 'tid', array('fid'));
			$this->_check($thread['fid']);
		}
		magicshowtype('top');
		magicshowsetting(lang('magic/bump', 'bump_info'), 'tid', $tid, 'text');
		magicshowtype('bottom');
	}

	function buy() {
		global $_G;
		if(!empty($_G['gp_id'])) {
			$thread = getpostinfo($_G['gp_id'], 'tid', array('fid'));
			$this->_check($thread['fid']);
		}
	}

	function _check($fid) {
		if(!checkmagicperm($this->parameters['forum'], $fid)) {
			showmessage(lang('magic/bump', 'bump_info_noperm'));
		}
	}

}

?>