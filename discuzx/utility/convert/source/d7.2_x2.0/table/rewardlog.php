<?php

/**
 * DiscuzX Convert
 *
 * $Id: rewardlog.php 15815 2010-08-27 02:56:14Z monkey $
 */

$curprg = basename(__FILE__);

$table_source = $db_source->tablepre.'rewardlog';
$table_target = $db_target->tablepre.'common_credit_log';

// bluelovers
$table_target2 = $db_target->tablepre.'forum_thread_rewardlog';
// bluelovers

$limit = 1000;
$nextid = 0;

$start = intval(getgpc('start'));
if(empty($start) && !$process['truncate_credit_log']) {
	$start = 0;
	$process['truncate_credit_log'] = 1;
	save_process('main', $process);
	$db_target->query("TRUNCATE $table_target");
}

// bluelovers
if(!$start && !$process['truncate_forum_thread_rewardlog']) {
	$start = 0;
	$process['truncate_forum_thread_rewardlog'] = 1;
	save_process('main', $process);
	$db_target->query("TRUNCATE $table_target2");
}
// bluelovers

$query = $db_source->query("SELECT * FROM ".$db_source->tablepre."settings WHERE variable IN ('creditstax', 'creditstrans')");
while($setting = $db_source->fetch_array($query)) {
	if($setting['variable'] == 'creditstrans') {
		$creditstrans = explode(',', $setting['value']);
		$ext = $creditstrans[2] ? $creditstrans[2] : $creditstrans[0];
		if(!$ext) {
			$ext = 1;
		}
	}
	if($setting['variable'] == 'creditstax') {
		$creditstax = $setting['value'];
	}
}

$query = $db_source->query("SELECT  * FROM $table_source WHERE netamount>'0' ORDER BY dateline LIMIT $start, $limit");
while ($row = $db_source->fetch_array($query)) {

	$nextid = 1;

	if($row['answererid'] > 0) {
		// bluelovers
		$rownew = array();

		$rownew  = daddslashes($row, 1);
		$data = implode_field_value($rownew, ',', db_table_fields($db_target, $table_target2));
		$db_target->query("INSERT INTO $table_target2 SET $data");
		// bluelovers

		$rownew = array();
		$rownew['uid'] = $row['answererid'];
		$rownew['operation'] = 'RAC';
		$rownew['relatedid'] = $row['tid'];
		$rownew['dateline'] = $row['dateline'];
		$rownew['extcredits'.$ext] = $row['netamount'];
		$rownew  = daddslashes($rownew, 1);

		$data = implode_field_value($rownew, ',', db_table_fields($db_target, $table_target));

		$db_target->query("INSERT INTO $table_target SET $data");
	} else {
		$rownew = array();
		$rownew['uid'] = $row['authorid'];
		$rownew['operation'] = 'RTC';
		$rownew['relatedid'] = $row['tid'];
		$rownew['dateline'] = $row['dateline'];
		$rownew['extcredits'.$ext] = -ceil($row['netamount'] / (1 - $creditstax));
		$rownew  = daddslashes($rownew, 1);

		$data = implode_field_value($rownew, ',', db_table_fields($db_target, $table_target));

		$db_target->query("INSERT INTO $table_target SET $data");
	}

}

if($nextid) {
	$next = $start + $limit;
	showmessage("繼續轉換數據表 ".$table_source." $start 至 ".($start+$limit)." 行", "index.php?a=$action&source=$source&prg=$curprg&start=$next");
}

?>