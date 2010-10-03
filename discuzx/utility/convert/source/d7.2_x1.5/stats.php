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

$table_source = $db_source->tablepre;
$table_target = $db_target->tablepre.'common_stat';

$limit = 1000;
$nextid = 0;

$start = intval(getgpc('start'));

$urladd = '';

$timestamp = time();
$timeoffset = 8;

if($start == 0) {
	$db_target->query("TRUNCATE $table_target");

//	$dateline_start = $db_source->result_first("SELECT dateline FROM {$table_source}posts WHERE dateline > 0");
	$dateline_start = $db_source->result_first("SELECT dateline FROM {$table_source}posts WHERE dateline > 0 and dateline > ".strtotime('1971-01-01'));
	$dateline_start = strtotime(gmdate('Y-m-d', $dateline_start + $timeoffset * 3600)) - 3600 * $timeoffset;

	$start = $dateline_start;
}

$dateline = $start;
$ss = '';

if ($start < $timestamp) {
	for($i=0; $i < 30; $i++) {
		$nextid = $dateline + 3600 * 24;
		$daytime = gmdate('Ymd', $dateline + $timeoffset * 3600);

		$row = array(
			'post' => $db_source->result_first("SELECT COUNT(*) FROM {$table_source}posts WHERE dateline BETWEEN $dateline AND $nextid"),

	//		'thread' => $db_source->result_first("SELECT COUNT(*) FROM {$table_source}threads WHERE dateline BETWEEN $dateline AND $nextid"),
	//
	//		'poll' => $db_source->result_first("SELECT COUNT(*), special FROM {$table_source}threads WHERE special = '1' and dateline BETWEEN $dateline AND $nextid"),
	//		'trade' => $db_source->result_first("SELECT COUNT(*) FROM {$table_source}threads WHERE special = '2' and dateline BETWEEN $dateline AND $nextid"),
	//		'reward' => $db_source->result_first("SELECT COUNT(*) FROM {$table_source}threads WHERE special = '3' and dateline BETWEEN $dateline AND $nextid"),
	//		'activity' => $db_source->result_first("SELECT COUNT(*) FROM {$table_source}threads WHERE special = '4' and dateline BETWEEN $dateline AND $nextid"),
	//		'debate' => $db_source->result_first("SELECT COUNT(*) FROM {$table_source}threads WHERE special = '5' and dateline BETWEEN $dateline AND $nextid"),

			'register' => $db_source->result_first("SELECT COUNT(*) FROM {$table_source}members WHERE regdate BETWEEN $dateline AND $nextid"),
			'login' => $db_source->result_first("SELECT COUNT(*) FROM {$table_source}members WHERE lastvisit BETWEEN $dateline AND $nextid") + $db_source->result_first("SELECT COUNT(*) FROM {$table_source}members WHERE lastactivity BETWEEN $dateline AND $nextid"),
		);

		$query = $db_source->query("SELECT COUNT(*) as count, special FROM {$table_source}threads WHERE dateline BETWEEN $dateline AND $nextid GROUP BY special");
		while ($_tmp = $db_source->fetch_array($query)) {
			$_k = 'thread';

			switch ($_tmp['special']) {
				case 1:
					$_k = 'poll';
					break;
				case 2:
					$_k = 'trade';
					break;
				case 3:
					$_k = 'reward';
					break;
				case 4:
					$_k = 'activity';
					break;
				case 5:
					$_k = 'debate';
					break;
			}

			$row[$_k] += $_tmp['count'];
		}

		$row = array_filter($row);

//		$ss .= '<br>'.$daytime;

		if (!$row) {
			$dateline = $nextid;
			continue;
		}

		$row['daytime'] = $daytime;

		$newrow  = daddslashes($row, 1);
		$data = implode_field_value($newrow, ',', db_table_fields($db_target, $table_target));

		$db_target->query("REPLACE INTO $table_target SET $data");

		$dateline = $nextid;

		if ($nextid > $timestamp) {
			break;
		}
	}
}

if($nextid) {
	showmessage("繼續轉換數據表 ".$table_target." dateline > $nextid => ".gmdate('Y-m-d', $nextid + $timeoffset * 3600).$ss, "index.php?a=$action&source=$source&prg=$curprg&start=$nextid".$urladd);
}

?>