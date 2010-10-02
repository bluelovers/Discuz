<?php

/**
 * 關於漫遊應用統計轉換過來沒有數據的解決方案
 * http://www.discuz.net/thread-1909786-1-1.html
 */

$curprg = basename(__FILE__);

$table_source = $db_target->tablepre.'common_myapp';
$table_target = $db_target->tablepre.'common_myapp_count';

$limit = $setting['limit']['myapp'] ? $setting['limit']['myapp'] : 500;
$nextid = 0;

$start = getgpc('start');
if($start == 0) {
//	$db_target->query("TRUNCATE $table_target");
}

$query = $db_target->query("SELECT appid FROM $table_source WHERE appid>'$start' ORDER BY appid LIMIT $limit");
while($app = $db_target->fetch_array($query)) {
	$nextid = $app['appid'];

	$count = $db_target->result_first("SELECT count(*) FROM $table_target WHERE appid='$nextid'");
	if(!$count) {
		$db_target->query("INSERT INTO $table_target (appid) VALUES ($nextid)");
	}
}

if($nextid) {
	showmessage("繼續轉換數據表 ".$table_source." appid> $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid");
}

?>