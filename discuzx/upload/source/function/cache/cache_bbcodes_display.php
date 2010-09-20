<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_bbcodes_display.php 16693 2010-09-13 04:31:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_bbcodes_display() {
	$data = array();
	$query = DB::query("SELECT tag, icon, explanation, params, prompt, perm FROM ".DB::table('forum_bbcode')." WHERE available='2' AND icon!='' ORDER BY displayorder");

	$i = 0;
	while($bbcode = DB::fetch($query)) {
		$bbcode['perm'] = explode("\t", $bbcode['perm']);
		if(in_array('', $bbcode['perm']) || !$bbcode['perm']) {
			continue;
		}
		$i++;
		$tag = $bbcode['tag'];
		$bbcode['i'] = $i;
		$bbcode['explanation'] = dhtmlspecialchars(trim($bbcode['explanation']));
		$bbcode['prompt'] = addcslashes($bbcode['prompt'], '\\\'');
		unset($bbcode['tag']);
		foreach($bbcode['perm'] as $groupid) {
			$data[$groupid][$tag] = $bbcode;
		}
	}

	save_syscache('bbcodes_display', $data);
}

?>