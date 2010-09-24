<?php

/**
 *
 * $HeadURL$
 * $Revision$
 * $Author$
 * $Date$
 * $Id$
 *
 * @author bluelovers
 * @copyright 2010
 */

$curprg = basename(__FILE__);

$table_source = $db_source->tablepre.'tags';
$table_target = $db_target->tablepre.'common_tags';

$limit = 1000;
$nextid = 0;

$start = intval(getgpc('start'));
if($start == 0) {
	$db_target->query("TRUNCATE $table_target");
}

$query = $db_source->query("SELECT  * FROM $table_source WHERE 1 ORDER BY tagid LIMIT $start, $limit");
while ($row = $db_source->fetch_array($query)) {

	$nextid = $row['tagid'];

	$row['tagname'] = trim(str_replace(array('　', "\t", '  '),' ',$row['tagname']));

	if (
		empty($row['tagname'])
		|| !$row['tagname']
	) continue;

	$newrow  = daddslashes($row, 1);

	$data = implode_field_value($newrow, ',', db_table_fields($db_target, $table_target));

	$db_target->query("INSERT INTO $table_target SET $data");
}

if($nextid) {
	showmessage("繼續轉換數據表 ".$table_source." tagid > $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid");
}

?>