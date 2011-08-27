<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

$curprg = basename(__FILE__);

$table_source = $db_source->tablepre . 'posts';
$table_target = $db_target->tablepre . 'forum_post';

$limit = 50;
$start = intval(getgpc('start'));
$nextid = 0;

$query = $db_target->query("SELECT *
	FROM
		$table_target
	WHERE
		pid>'$start'
		AND message = ''
	ORDER BY
		pid ASC
	LIMIT $limit
");
while($row = $db_target->fetch_array($query)) {
	$nextid = $row['pid'];

	$query = $db_source->query("SELECT *
		FROM
			$table_source
		WHERE
			pid = '$nextid'
		LIMIT 1
	");
	if ($row_old = $db_source->fetch_array($query)) {
		if ($row_old['pid'] != $row['pid']) continue;

		$row_old['message'] = str_replace("\r\n", "\n", $row_old['message']);

		$data = implode_field_value(daddslashes(array(
			'message' => $row_old['message'],
		), 1), ',', db_table_fields($db_target, $table_target));
		$db_target->query("UPDATE $table_target SET $data WHERE pid = '$nextid'");
	}
}

if($nextid) {
	showmessage("繼續轉換數據表 ".$table_target." pid > $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid");
}

?>