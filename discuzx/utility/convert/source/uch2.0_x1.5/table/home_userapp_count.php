<?php

/**
 * DiscuzX Convert
 *
 * $Id: $
 */

$curprg = basename(__FILE__);

$table_source = $db_target->tablepre.'home_userapp';
$table_target = $db_target->tablepre.'common_myapp_count';

$start = getgpc('start');
$start = empty($start) ? 0 : intval($start);
$limit = 100;
$nextid = $limit + $start;
$done = true;

$query = $db_source->query("SELECT t2. *
	FROM (
	SELECT appid
	FROM $table_source
	GROUP BY appid
	)t1
	LEFT JOIN $table_target AS t2 ON t2.appid = t1.appid
	WHERE 1
	LIMIT $start, $limit");
while ($value = $db_source->fetch_array($query)) {

	$done = false;
	$value['usetotal'] = 0;

	$query2 = $db_source->query("SELECT t2.gender, count(*) as num
	FROM (
	SELECT uid
	FROM $table_source
	where appid='$value[appid]'
	)t1
	LEFT JOIN {$db_target->tablepre}common_member_profile AS t2 ON t2.uid = t1.uid
	WHERE 1
	GROUP BY gender");
	while ($value2 = $db_source->fetch_array($query2)) {
		if ($value2['gender'] == 1) {
			$value['boyuse'] = $value2['num'];
		} elseif ($value2['gender'] == 2) {
			$value['girluse'] = $value2['num'];
		}

		$value['usetotal'] += $value2['num'];
	}

	$value  = daddslashes($value, 1);

	$data = implode_field_value($value, ',', db_table_fields($db_target, $table_target));

	$db_target->query("UPDATE $table_target SET $data WHERE `appid`='$value[appid]'");
}

if($done == false) {
	showmessage("繼續轉換數據表 ".$table_source." uid> $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid");
} else {
	$db_target->query("UPDATE $table_target SET usedate=".time()." WHERE usetotal > 0");
}

?>