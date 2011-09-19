<?php

/**
 * DiscuzX Convert
 *
 * $Id: admincustom.php 15808 2010-08-27 02:34:26Z monkey $
 * English by Valery Votintsev at sources.ru
 */

$curprg = basename(__FILE__);

$table_source = $db_source->tablepre.'admincustom';
$table_target = $db_target->tablepre.'common_admincp_cmenu';

$limit = 2000;
$nextid = 0;

$start = getgpc('start');
if(empty($start)) {
	$start = 0;
	$db_target->query("TRUNCATE $table_target");
}

$query = $db_source->query("SELECT * FROM $table_source LIMIT $start, $limit");
while ($row = $db_source->fetch_array($query)) {

	$nextid = 1;

	$row  = daddslashes($row, 1);

	$data = implode_field_value($row, ',', db_table_fields($db_target, $table_target));

	$db_target->query("INSERT INTO $table_target SET $data");
}

if($nextid) {
	showmessage(lang('continue_convert_table').$table_source." $start ".lang('to')." ".($start+$limit)." ".lang('records')." ", "index.php?a=$action&source=$source&prg=$curprg&start=".($start+$limit));
}

?>