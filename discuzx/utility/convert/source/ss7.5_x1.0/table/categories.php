<?php

/**
 * DiscuzX Convert
 *
 * $Id: categories.php 10469 2010-05-11 09:12:14Z monkey $
 *	English by Valery Votintsev at sources.ru
 */

$curprg = basename(__FILE__);

$table_source = $db_source->tablepre.'categories';
$table_target = $db_target->tablepre.'portal_category';

$table_source_items =  $db_source->tablepre.'spaceitems';

$limit = 1000;
$nextid = 0;

$start = getgpc('start');

if($start == 0) {
	$db_target->query("TRUNCATE $table_target");
}

$arr = $catids = $count_num = array();

$query = $db_source->query("SELECT * FROM $table_source WHERE catid>'$start' ORDER BY catid LIMIT $limit");
while ($value = $db_source->fetch_array($query)) {
	$arr[] = $value;
	$catids[] = $value['catid'];
}

$query = $db_source->query("SELECT catid, COUNT(*) AS num FROM $table_source_items WHERE catid IN (".dimplode($catids).") GROUP BY catid");
while ($value = $db_source->fetch_array($query)) {
	$count_num[$value['catid']] = $value['num'];
}

foreach ($arr as $rs) {
	$nextid = $rs['aid'];

	$setarr = array();
	$setarr['catid'] = $rs['catid'];
	$setarr['upid'] = $rs['upid'];
	$setarr['catname'] = $rs['name'];
	$setarr['displayorder'] = $rs['displayorder'];

	$setarr['articles'] = empty($count_num[$rs['catid']]) ? 0 : $count_num[$rs['catid']];

	$setarr  = daddslashes($setarr, 1);

	$data = implode_field_value($setarr, ',', db_table_fields($db_target, $table_target));

	$db_target->query("INSERT INTO $table_target SET $data");
}

if($nextid) {
	showmessage(lang('continue_convert_table').$table_source." catid> $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid");
}

?>