<?php

/**
 * DiscuzX Convert
 *
 * $Id: spacenews.php 15777 2010-08-26 04:00:58Z zhengqingpeng $
 */

$curprg = basename(__FILE__);

$table_source = $db_source->tablepre.'spacenews';
$table_target = $db_target->tablepre.'portal_article_content';

$limit = 1000;
$nextid = 0;

$start = getgpc('start');
if($start == 0) {
	$db_target->query("TRUNCATE $table_target");
}

$query = $db_source->query("SELECT * FROM $table_source WHERE nid>'$start' ORDER BY nid LIMIT $limit");
while ($news = $db_source->fetch_array($query)) {

	$nextid = $news['nid'];

	$setcontent = array();
	$setcontent['cid'] = $news['nid'];
	$setcontent['aid'] = $news['itemid'];
	$setcontent['content'] = $news['message'];
	$setcontent['pageorder'] = $news['pageorder'];
	$setcontent['dateline'] = $news['dateline'];

	$setcontent  = daddslashes($setcontent, 1);

	$data = implode_field_value($setcontent, ',', db_table_fields($db_target, $table_target));

	$db_target->query("INSERT INTO $table_target SET $data");

}

if($nextid) {
	showmessage("繼續轉換數據表 ".$table_source." nid> $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid");
}

?>