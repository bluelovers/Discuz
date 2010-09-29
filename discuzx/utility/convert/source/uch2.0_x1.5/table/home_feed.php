<?php

/**
 * DiscuzX Convert
 *
 * $Id: home_feed.php 15720 2010-08-25 23:56:08Z monkey $
 */

$curprg = basename(__FILE__);

$table_source = $db_source->tablepre.'feed';
$table_target = $db_target->tablepre.'home_feed';

$limit = $setting['limit']['feed'] ? $setting['limit']['feed'] : 1000;
$nextid = 0;

$start = getgpc('start');
if($start == 0) {
	$db_target->query("TRUNCATE $table_target");
}

$query = $db_source->query("SELECT  * FROM $table_source WHERE feedid>'$start' ORDER BY feedid LIMIT $limit");
while ($feed = $db_source->fetch_array($query)) {

	$nextid = $feed['feedid'];

	// bluelovers
	if ($tmp = unserialize($feed['title_data'])) {
		if ($tmp['message']) {
			$tmp['message'] = preg_replace('/image\/face\/(30|2[1-9])/', 'static/image/smiley/comcom_dx/$1', $tmp['message']);
			$tmp['message'] = preg_replace('/image\/face\/(\d+)/', 'static/image/smiley/comcom/$1', $tmp['message']);

			$feed['title_data'] = serialize($tmp);
		}
	}
	// bluelovers

	$feed  = daddslashes($feed, 1);

	$data = implode_field_value($feed, ',', db_table_fields($db_target, $table_target));

	$db_target->query("INSERT INTO $table_target SET $data");
}

if($nextid) {
	showmessage("繼續轉換數據表 ".$table_source." feedid> $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid");
}

?>