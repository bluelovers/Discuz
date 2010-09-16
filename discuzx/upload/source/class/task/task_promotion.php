<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: task_promotion.php 6736 2010-03-25 07:30:28Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class task_promotion {

	var $version = '1.0';
	var $name = 'promotion_name';
	var $description = 'promotion_desc';
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $icon = '';
	var $period = '';
	var $periodtype = 0;
	var $conditions = array(
		'num' => array(
			'title' => 'promotion_complete_var_iplimit',
			'type' => 'text',
			'value' => '',
			'default' => 100,
			'sort' => 'complete',
		),
	);

	function preprocess($task) {
		global $_G;

		$promotions = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_promotion')." WHERE uid='$_G[uid]'");
		DB::query("REPLACE INTO ".DB::table('forum_spacecache')." (uid, variable, value, expiration) VALUES ('$_G[uid]', 'promotion$task[taskid]', '$promotions', '$_G[timestamp]')");
	}

	function csc($task = array()) {
		global $_G;

		$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_promotion')." WHERE uid='$_G[uid]'") - DB::result_first("SELECT value FROM ".DB::table('forum_spacecache')." WHERE uid='$_G[uid]' AND variable='promotion$task[taskid]'");
		$numlimit = DB::result_first("SELECT value FROM ".DB::table('common_taskvar')." WHERE taskid='$task[taskid]' AND variable='num'");
		if($num && $num >= $numlimit) {
			return TRUE;
		} else {
			return array('csc' => $num > 0 && $numlimit ? sprintf("%01.2f", $num / $numlimit * 100) : 0, 'remaintime' => 0);
		}
	}

	function sufprocess($task) {
		global $_G;

		DB::query("DELETE FROM ".DB::table('forum_spacecache')." WHERE uid='$_G[uid]' AND variable='promotion$task[taskid]'");
	}

}

?>