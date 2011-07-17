<?php

/*
Plugin FOR Discuz! X1.5
Copyright (c) 2009-2012 WWW.NWDS.CN | NDS西域数码工作室
$Id: nds_votekick.class.php V1.6 20110401 SINGCEE $
*/

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_nds_votekick {
	function plugin_nds_votekick() {
		global $_G;
		$this->lvotes = $_G['cache']['plugin']['nds_votekick']['lvotes'];
		// 帖子可投票时间范围（天数）
		$this->vtexpired = $_G['cache']['plugin']['nds_votekick']['vtexpired'];
		$this->voteforums = unserialize($_G['cache']['plugin']['nds_votekick']['voteforums']);
		//$this->votegroups = unserialize($_G['cache']['plugin']['nds_votekick']['votegroups']);
	}
}
class plugin_nds_votekick_forum extends plugin_nds_votekick {
	function viewthread_useraction_output() {
		global $_G;
		if (!$_G['uid']) return;
		if (in_array($_G['fid'], $this->voteforums)) return;
		// 判斷帖子可投票时间范围（天数）
		if ($this->vtexpired > 0 && ($_G['timestamp'] - $_G['forum_thread']['dateline']) >
			$this->vtexpired * 24 * 60 * 60) return;
		$votemax = $this->lvotes;
		$vks = DB::fetch_first("SELECT votes,uids FROM " . DB::table('nds_votekick') .
			" WHERE tid = '$_G[tid]' ");
		if ($vks['votes']) {
			$votes = $vks['votes'];
		} else {
			$votes = 0;
			//$vkusers = lang('plugin:nds_votekick', 'novtuserlist');
		}
		$votemargin = $this->lvotes - $votes;
		$ndsvtreturn = '';
		include template('nds_votekick:votekick');
		return $ndsvtreturn;

	}
}

?>