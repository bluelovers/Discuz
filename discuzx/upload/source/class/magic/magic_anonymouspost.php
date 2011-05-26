<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_anonymouspost.php 18824 2010-12-07 02:39:28Z liulanbo $
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
		$id = intval($_G['gp_id']);
		if(empty($id)) {
			showmessage(lang('magic/anonymouspost', 'anonymouspost_info_nonexistence'));
		}
		$idtype = !empty($_G['gp_idtype']) ? htmlspecialchars($_G['gp_idtype']) : '';
		if(!in_array($idtype, array('pid', 'cid'))) {
			showmessage(lang('magic/anonymouspost', 'anonymouspost_use_error'));
		}
		if($idtype == 'pid') {
			$_G['tid'] = $_G['gp_ptid'];
			$post = getpostinfo($id, 'pid', array('p.first', 'p.tid', 'p.fid', 'p.authorid', 'p.author', 'p.dateline', 'p.anonymous'));
			$this->_check($post);

			if($post['authorid'] != $_G['uid']) {
				showmessage('magics_operation_nopermission');
			}

			$thread = getpostinfo($post['tid'], 'tid', array('tid', 'subject', 'author', 'replies', 'lastposter'));
			$posttable = getposttablebytid($post['tid']);
			if($post['first']) {
				$author = '';
				$lastposter = $thread['replies'] > 0 ? $thread['lastposter'] : '';
			} else {
				$author = $thread['author'];
				$lastposter = '';
			}
			DB::query("UPDATE ".DB::table($posttable)." SET anonymous='1' WHERE pid='$id'");

			$forum['lastpost'] = explode("\t", DB::result_first("SELECT lastpost FROM ".DB::table('forum_forum')." WHERE fid='$post[fid]'"));
			if($post['dateline'] == $forum['lastpost'][2] && ($post['author'] == $forum['lastpost'][3] || ($forum['lastpost'][3] == '' && $post['anonymous']))) {
				$lastpost = "$thread[tid]\t$thread[subject]\t$_G[timestamp]\t$lastposter";
				DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost' WHERE fid='$post[fid]'", 'UNBUFFERED');
			}
			DB::query("UPDATE ".DB::table('forum_thread')." SET author='$author', lastposter='$lastposter' WHERE tid='$post[tid]'");
		} elseif($idtype == 'cid') {
			$value = DB::fetch_first('SELECT * FROM '.DB::table('home_comment')." WHERE cid = '$id' AND authorid = '$_G[uid]'");
			if(empty($value)) {
				showmessage('anonymouspost_use_error');
			} elseif($value['author'] == '') {
				showmessage('anonymouspost_once_limit');
			}
			DB::query("UPDATE ".DB::table('home_comment')." SET author='' WHERE cid='$id' AND authorid='$_G[uid]'");
		}

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', 0, $idtype, $id);

		showmessage(lang('magic/anonymouspost', 'anonymouspost_succeed'), dreferer(), array(), array('showdialog' => 1, 'locationtime' => true));
	}

	function show() {
		global $_G;
		$id = !empty($_G['gp_id']) ? htmlspecialchars($_G['gp_id']) : '';
		$idtype = !empty($_G['gp_idtype']) ? htmlspecialchars($_G['gp_idtype']) : '';
		if($idtype == 'pid') {
			list($id, $_G['tid']) = explode(':', $id);
			if($id && $_G['tid']) {
				$post = getpostinfo($id, 'pid', array('p.fid', 'p.authorid'));
				$this->_check($post);
			}
		}
		magicshowtype('top');
		magicshowtips(lang('magic/anonymouspost', 'anonymouspost_desc'));
		magicshowtips(lang('magic/anonymouspost', 'anonymouspost_num', array('magicnum' => $this->magic['num'])));
		magicshowsetting('', 'id', $id, 'hidden');
		magicshowsetting('', 'idtype', $idtype, 'hidden');
		if($idtype == 'pid') {
			magicshowsetting('', 'ptid', $_G['tid'], 'hidden');
		}
		magicshowtype('bottom');
	}

	function buy() {
		global $_G;
		$id = !empty($_G['gp_id']) ? htmlspecialchars($_G['gp_id']) : '';
		$idtype = !empty($_G['gp_idtype']) ? htmlspecialchars($_G['gp_idtype']) : '';
		if(!empty($id) && $idtype == 'pid') {
			list($id, $_G['tid']) = explode(':', $id);
			$post = getpostinfo(intval($id), 'pid', array('p.fid', 'p.authorid'));
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