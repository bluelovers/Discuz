<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_sql.php 6757 2010-03-25 09:01:29Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class ultrax_cache {

	function ultrax_cache($conf) {
		$this->conf = $conf;
	}

	function get_cache($key) {
		static $data = null;
		if(!isset($data[$key])) {
			$cache = DB::fetch_first("SELECT * FROM ".DB::table('common_cache')."WHERE cachekey='$key'");
			if(!$cache) {
				return false;
			}
			$data[$key] = unserialize($cache['cachevalue']);
			if($cache['life'] && ($cache['dateline'] < time() - $data[$key]['life'])) {
				return false;
			}
		}
		return $data[$key]['data'];
	}

	function set_cache($key, $value, $life) {
		$data = array(
			'cachekey' => $key,
			'cachevalue' => addslashes(serialize(array('data' => $value, 'life' => $life))),
			'dateline' => time(),
			);
		return DB::insert('common_cache', $data);
	}

	function del_cache($key) {
		return DB::delete('common_cache', array('cachekey' => $key));
	}
}