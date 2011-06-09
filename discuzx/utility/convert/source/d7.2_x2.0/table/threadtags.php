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

$table_source = $db_source->tablepre.'threadtags';
$table_target = $db_target->tablepre.'common_tag_thread';

$table_target_tag = $db_target->tablepre.'common_tag';

$limit = 1000;
$nextid = 0;

$start = intval(getgpc('start'));
if($start == 0) {
	$db_target->query("TRUNCATE $table_target");
}

$tagcache = array();

$query = $db_source->query("SELECT  * FROM $table_source WHERE 1 ORDER BY tagname LIMIT $start, $limit");
while ($row = $db_source->fetch_array($query)) {

	$nextid = $row['tagid'];

	$tagname = $row['tagname'];

	if (!$tagcache[$tagname]) {
		$_query_ = $_tag_ = null;

		$_query_ = $db_target->query("SELECT * FROM ".$table_target_tag." WHERE tagname='".daddslashes($tagname,1)."' LIMIT 1");
		if ($_tag_ = $db_target->fetch_array($_query_)) {
			$row['tagid'] = $_tag_['tagid'];
			$row['tagname'] = $_tag_['tagname'];

			$tagcache[$tagname] = $_tag_;
		} else {
			continue;
		}
	} else {
		$row['tagid'] = $tagcache[$tagname]['tagid'];
		$row['tagname'] = $tagcache[$tagname]['tagname'];
	}

	$row['keyid'] = $row['tid'];

	$newrow  = daddslashes($row, 1);

	$data = implode_field_value($newrow, ',', db_table_fields($db_target, $table_target));

	$db_target->query("INSERT INTO $table_target SET $data");
}

if($nextid) {
	showmessage("繼續轉換數據表 ".$table_source." tagid > $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid");
}

?>