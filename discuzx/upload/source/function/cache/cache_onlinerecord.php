<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

/**
 * 用來解決升級轉換後無法保存舊有的 onlinerecord 紀錄問題
 *
 * 最高記錄是 $onlineinfo[0] 於 $onlineinfo[1]
 */
function build_cache_onlinerecord() {
	$onlinenum = DB::result_first("SELECT count(*) FROM ".DB::table('common_session'));

	$onlinerecord = DB::fetch_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='onlinerecord'");
	$onlineinfo = explode("\t", $onlinerecord);

	if($onlinenum > $onlineinfo[0]) {
		$onlinerecord = "$onlinenum\t".TIMESTAMP;
		DB::query("UPDATE ".DB::table('common_setting')." SET svalue='$onlinerecord' WHERE skey='onlinerecord'");
		save_syscache('onlinerecord', $onlinerecord);
		$onlineinfo = array($onlinenum, TIMESTAMP);
	}

	$_G['cache']['onlinerecord'] = $onlinerecord;

	discuz_core::$_cache_data['onlinerecord']['onlinenum'] = $onlinenum;
}

?>