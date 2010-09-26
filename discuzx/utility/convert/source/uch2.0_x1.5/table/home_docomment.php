<?php

/**
 * DiscuzX Convert
 *
 * $Id: home_docomment.php 15720 2010-08-25 23:56:08Z monkey $
 */

$curprg = basename(__FILE__);

$table_source = $db_source->tablepre.'docomment';
$table_target = $db_target->tablepre.'home_docomment';

$limit = $setting['limit']['docomment'] ? $setting['limit']['docomment'] : 1000;
$nextid = 0;

$start = getgpc('start');
if($start == 0) {
	$db_target->query("TRUNCATE $table_target");
}

$query = $db_source->query("SELECT  * FROM $table_source WHERE id>'$start' ORDER BY id LIMIT $limit");
while ($docomment = $db_source->fetch_array($query)) {

	$nextid = $docomment['id'];

	// bluelovers
	$docomment['message'] = str_replace(arrar("\r\n", "\n\r"), "\n", $docomment['message']);
	$docomment['message'] = preg_replace('/image\/face\/(30|2[1-9])/', 'static/image/smiley/comcom_dx/$1', $docomment['message']);
	// bluelovers

	$docomment  = daddslashes($docomment, 1);

	$data = implode_field_value($docomment, ',', db_table_fields($db_target, $table_target));

	$db_target->query("INSERT INTO $table_target SET $data");
}

if($nextid) {
	showmessage("繼續轉換數據表 ".$table_source." id> $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid");
}

// bluelovers
$db_target->query("UPDATE $table_target SET message =  replace( message, 'image/face/', 'static/image/smiley/comcom/'  ) ");
// bluelovers

?>