<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_heats.php 16696 2010-09-13 05:02:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_heats() {
	global $_G;

	$data = array();
	if($_G['setting']['indexhot']['status']) {

		require_once libfile('function/post');
		$_G['setting']['indexhot'] = array(
			'status' => 1,
			'limit' => intval($_G['setting']['indexhot']['limit'] ? $_G['setting']['indexhot']['limit'] : 10),
			'days' => intval($_G['setting']['indexhot']['days'] ? $_G['setting']['indexhot']['days'] : 7),
			'expiration' => intval($_G['setting']['indexhot']['expiration'] ? $_G['setting']['indexhot']['expiration'] : 900),
			'messagecut' => intval($_G['setting']['indexhot']['messagecut'] ? $_G['setting']['indexhot']['messagecut'] : 200)
		);

		$heatdateline = TIMESTAMP - 86400 * $_G['setting']['indexhot']['days'];

		$query = DB::query("SELECT t.tid,t.posttableid,t.views,t.dateline,t.replies,t.author,t.authorid,t.subject,t.price
			FROM ".DB::table('forum_thread')." t
			WHERE t.dateline>'$heatdateline' AND t.heats>'0' AND t.displayorder>='0' ORDER BY t.heats DESC LIMIT ".($_G['setting']['indexhot']['limit'] * 2));

		$messageitems = 2;
		while($heat = DB::fetch($query)) {
			$posttable = $heat['posttableid'] ? "forum_post_{$heat['posttableid']}" : 'forum_post';
			$post = DB::fetch_first("SELECT p.pid, p.message FROM ".DB::table($posttable)." p WHERE p.tid='{$heat['tid']}' AND p.first='1'");
			$heat = array_merge($heat, (array)$post);
			if($_G['setting']['indexhot']['limit'] == 0) {
				break;
			}
			if($messageitems > 0) {
				$heat['message'] = !$heat['price'] ? messagecutstr($heat['message'], $_G['setting']['indexhot']['messagecut']) : '';
				$data['message'][$heat['tid']] = $heat;
			} else {
				unset($heat['message']);
				$data['subject'][$heat['tid']] = $heat;
			}
			$messageitems--;
			$_G['setting']['indexhot']['limit']--;
		}
		$data['expiration'] = TIMESTAMP + $_G['setting']['indexhot']['expiration'];
	}

	save_syscache('heats', $data);
}

?>