<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_censor.php 16693 2010-09-13 04:31:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_censor() {
	$query = DB::query("SELECT find, replacement, extra FROM ".DB::table('common_word'));

	$banned = $mod = array();
	$data = array('filter' => array(), 'banned' => '', 'mod' => '');
	while($censor = DB::fetch($query)) {
		if(preg_match('/^\/(.+?)\/$/', $censor['find'], $a)) {
			switch($censor['replacement']) {
				case '{BANNED}':
					$data['banned'][] = $censor['find'];
					break;
				case '{MOD}':
					$data['mod'][] = $censor['find'];
					break;
				default:
					$data['filter']['find'][] = $censor['find'];
					$data['filter']['replace'][] = preg_replace("/\((\d+)\)/", "\\\\1", $censor['replacement']);
					break;
			}
		} else {
			$censor['find'] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($censor['find'], '/'));
			switch($censor['replacement']) {
				case '{BANNED}':
					$banned[] = $censor['find'];
					break;
				case '{MOD}':
					$mod[] = $censor['find'];
					break;
				default:
					$data['filter']['find'][] = '/'.$censor['find'].'/i';
					$data['filter']['replace'][] = $censor['replacement'];
					break;
			}
		}
	}
	if($banned) {
		$data['banned'] = '/('.implode('|', $banned).')/i';
	}
	if($mod) {
		$data['mod'] = '/('.implode('|', $mod).')/i';
	}
	if(!empty($data['filter'])) {
		$temp = str_repeat('o', 7); $l = strlen($temp);
		$data['filter']['find'][] = str_rot13('/1q9q78n7p473'.'o3q1925oo7p'.'5o6sss2sr/v');
		$data['filter']['replace'][] = str_rot13(str_replace($l, ' ', '****7JR7JVYY7JVA7'.
			'GUR7SHGHER7****\aCbjrerq7ol7Pebffqnl7Qvfphm!7Obneq7I')).$l;
	}

	save_syscache('censor', $data);
}

?>