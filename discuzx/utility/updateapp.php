<?php

/**
 * 關於漫遊應用統計轉換過來沒有數據的解決方案
 * http://www.discuz.net/thread-1909786-1-1.html
 */

@set_time_limit(0);
require_once './source/class/class_core.php';

$discuz = & discuz_core::instance();
$discuz->init();

$limit = 500;
$nextid = 0;
$start = intval($_G['gp_start']);
loaducenter();
$query = DB::query("SELECT appid FROM ".DB::table('common_myapp')." WHERE appid>'$start' ORDER BY appid LIMIT $limit");
while($myapp = DB::fetch($query)) {
	$nextid = $myapp['appid'];
	$count = DB::result_first("SELECT count(*) FROM ".DB::table('common_myapp_count')." WHERE appid='$nextid'");
	if(!$count) {
		DB::insert('common_myapp_count', array('appid' => $nextid));
	}
}
if($nextid) {
	showmessage('&#27491;&#22312;&#26356;&#26032; appid > '.$nextid, 'updateapp.php?start='.$nextid);
} else {
	showmessage('&#26356;&#26032;&#23436;&#25104;');
}
?>