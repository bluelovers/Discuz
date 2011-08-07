<?php

/**
 * DiscuzX Convert
 *
 * $Id: home_share.php 15720 2010-08-25 23:56:08Z monkey $
 */

$curprg = basename(__FILE__);

$table_source = $db_source->tablepre.'share';
$table_target = $db_target->tablepre.'home_share';

$limit = $setting['limit']['share'] ? $setting['limit']['share'] : 1000;
$nextid = 0;

$start = getgpc('start');
if($start == 0) {
	$db_target->query("TRUNCATE $table_target");
}

// bluelovers
$home = load_process('home');
$domain = $home['domain'];

foreach ($domain as $_k => $_v) {
	$_v = preg_split('/ *(\r\n|\n) */', $_v);

	foreach ($_v as $__k => $__v) {
		$__v = trim($__v, '\\/');

		$_b[$__k] = $__v;
	}

	$domain[$_k] = $_v;
}

$fix_array = $replace = array();
$replace['home'] = array(
	'space.php?do=doing&doid=' => 'home.php?mod=space&do=doing&doid=',
	'space.php?uid=' => 'home.php?mod=space&uid=',
);
$replace['forum'] = array(
	'viewthread.php?tid=' => 'forum.php?mod=viewthread&tid=',
);

$fix_array[0] = array(
	'link', 'data',
);
$fix_array[1] = array(
	'data',
);
// bluelovers

$query = $db_source->query("SELECT sid, type, uid, username, dateline, title_template, body_template, body_data, body_general, image,
							image_link, hot, hotuser FROM $table_source WHERE sid>'$start' ORDER BY sid LIMIT $limit");
while ($share = $db_source->fetch_array($query)) {

	$nextid = intval($share['sid']);

	// bluelovers
	if ($tmp = unserialize($share['body_data'])) {
		foreach ($fix_array[0] as $_k) {
			if (isset($tmp[$_k])) {
				$tmp[$_k] = _fix_link($tmp[$_k], $_k);
			}
		}

		$share['data_index'] = $tmp['data'];

		$share['body_data'] = serialize((array)$tmp);
	}
	// bluelovers

	$share  = daddslashes($share, 1);

	$data = implode_field_value($share, ',', db_table_fields($db_target, $table_target));

	$db_target->query("INSERT INTO $table_target SET $data");
}

if($nextid) {
	showmessage("繼續轉換數據表 ".$table_source." sid> $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid");
}

// bluelovers
function _fix_link($value, $key) {
	global $domain;
	global $replace;
	global $fix_array;

	foreach (array('home', 'forum') as $_k) {
		if ($domain[$_k]) {
			foreach ($domain[$_k] as $_row) {
				$value = str_replace('<a href="http://'.$_row.'/', '<a href="', $value);
				$value = str_replace('<a href="http://www.'.$_row.'/', '<a href="', $value);
			}
		}

		if ($replace[$_k]) {
			foreach ($replace[$_k] as $_s_ => $_r_) {
				$value = str_replace('<a href="'.$_s_, '<a href="'.$_r_, $value);

				if (in_array($key, $fix_array[1])) {
					$value = str_replace($_s_, $_r_, $value);
				}
			}
		}
	}

	return $value;
}
// bluelovers

?>