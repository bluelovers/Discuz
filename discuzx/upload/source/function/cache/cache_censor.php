<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_censor.php 21343 2011-03-23 08:26:08Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_censor() {
	$query = DB::query("SELECT find, replacement, extra FROM ".DB::table('common_word'));

	$banned = $mod = array();
	$bannednum = $modnum = 0;
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
					$bannednum ++;
					if($bannednum == 1000) {
						$data['banned'][] = '/('.implode('|', $banned).')/i';
						$banned = array();
						$bannednum = 0;
					}
					break;
				case '{MOD}':
					$mod[] = $censor['find'];
					$modnum ++;
					if($modnum == 1000) {
						$data['mod'][] = '/('.implode('|', $mod).')/i';
						$mod = array();
						$modnum = 0;
					}
					break;
				default:
					$data['filter']['find'][] = '/'.$censor['find'].'/i';
					$data['filter']['replace'][] = $censor['replacement'];
					break;
			}
		}
	}

	if($banned) {
		$data['banned'][] = '/('.implode('|', $banned).')/i';
	}
	if($mod) {
		$data['mod'][] = '/('.implode('|', $mod).')/i';
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