<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_repent.php 13898 2010-08-03 02:22:52Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_repent {

	var $version = '1.0';
	var $name = 'repent_name';
	var $description = 'repent_desc';
	var $price = '10';
	var $weight = '10';
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
		global $_G;
		$settings = array(
			'fids' => array(
				'title' => 'repent_forum',
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
			showmessage(lang('magic/repent', 'repent_info_nonexistence'));
		}
		$_G['tid'] = $_G['gp_ptid'];

		$post = getpostinfo($_G['gp_pid'], 'pid', array('p.first', 'p.tid', 'p.fid', 'p.authorid'));
		$this->_check($post);

		require_once libfile('function/post');
		require_once libfile('function/delete');
		if($post['first']) {
			deletethread("tid='$post[tid]'");
			updateforumcount($post['fid']);
		} else {
			deletepost("pid='$_G[gp_pid]'");
			updatethreadcount($post['tid']);
		}

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, 'tid', $_G['tid']);

		showmessage(lang('magic/repent', 'repent_succeed'), $post['first'] ? 'forum.php?mod=forumdisplay&fid='.$post['fid'] : dreferer(), array(), array('showdialog' => 1, 'locationtime' => true));
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
		magicshowsetting(lang('magic/repent', 'repent_info'), 'pid', $pid, 'text');
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
			showmessage(lang('magic/repent', 'repent_info_noperm'));
		}
		if($post['authorid'] != $_G['uid']) {
			showmessage(lang('magic/repent', 'repent_info_user_noperm'));
		}
	}

}

?>