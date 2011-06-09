<?php

/**
 * DiscuzX Convert
 *
 * $Id: tags.php 20881 2011-03-07 07:09:14Z monkey $
 */

$curprg = basename(__FILE__);

$table_source = $db_source->tablepre.'tags';
$table_target = $db_target->tablepre.'common_tag';

$table_thread_source = $db_source->tablepre.'threadtags';
$table_thread_target = $db_target->tablepre.'common_tagitem';
$table_post_target = $db_target->tablepre.'forum_post';

$limit = 100;
$nextid = 0;

$start = intval(getgpc('start'));

$_tagid_exists = intval(getgpc('_tagid_exists'));

/*
// debug & test only
$_j = intval(getgpc('_j'));
$_i = intval(getgpc('_i'));
$_p = getgpc('_p');
$_p2 = getgpc('_p2');
*/

if($start == 0) {
	$db_target->query("TRUNCATE $table_target");
	$db_target->query("TRUNCATE $table_thread_target");

	// 使原本就含有 tagid 的資料庫可以相容
	$_tagid_exists = -1;
}

$query = $db_source->query("SELECT  * FROM $table_source ORDER BY tagname LIMIT $start, $limit");
while ($row = $db_source->fetch_array($query)) {

	if ($_tagid_exists < 0) {
		$_tagid_exists = (isset($row['tagid']) && ($row['tagid'] > 0)) ? 1 : 0;
	}

	$nextid = $start + $limit;

	if (!$_tagid_exists) {
		unset($row['tagid']);
	}

	$row['status'] = $row['closed'];
	unset($row['closed'], $row['total']);

//	$row['tagname'] = trim(str_replace(array('　', "\t", '  '),' ',$row['tagname']));
	$row['tagname'] = trim(preg_replace(array(
		'/[ \　\t\n][\r \　\t\n]+/u'
		, '/[\　\t\n]/u'
		, '/^[\n \　\t]*(\S.*)[ \　\t\n]*$/u'
		, '/^[\-\+\_ \　]*(\d+)[\-\+\_ \　]*$/'
	), array(
		' '
		, ' '
		, '$1'
		, '$1'
	), $_oldtagname = $row['tagname']));

	if ($_oldtagname != $row['tagname']) {
		/*
		$_p .= ','.($_oldtagname == $row['tagname'] ? $_oldtagname : $_oldtagname.' : '.$row['tagname']);
		$_j++;
		*/

		$row['tagname'] = $_oldtagname;
	}

	$row  = daddslashes($row, 1);

	if (preg_match('/^[ \　]*0*([0-9])[ \　]*$/u', $row['tagname'])) {
		unset($row['tagname']);
	}

	if (
		empty($row['tagname'])
		|| !$row['tagname']
		|| preg_match('/\(.*\)/', $row['tagname'])
	) {
		/*
		$_i++;
		$_p2 .= ','.($_oldtagname == $row['tagname'] ? $_oldtagname : $_oldtagname.' : '.$row['tagname']);
		*/
		continue;
	}

	$row  = daddslashes($row, 1);

	$data = implode_field_value($row, ',', db_table_fields($db_target, $table_target));

//	$db_target->query("INSERT INTO $table_target SET $data");
	$db_target->query("INSERT INTO $table_target SET $data", 'SILENT');
	if ($db_target->errno() == 1062) {
		$tagid = $db_target->result_first("SELECT tagid FROM $table_target WHERE tagname = '{$row[tagname]}'");
	} elseif (!$_tagid_exists) {
		$tagid = $db_target->insert_id();
	} else {
		$tagid = $row['tagid'];
	}

	$query_thread = $db_source->query("SELECT tid FROM $table_thread_source WHERE tagname='$row[tagname]'");
	while ($rowthread = $db_source->fetch_array($query_thread)) {
		$db_target->query("INSERT INTO $table_thread_target SET tagid='$tagid', tagname='$row[tagname]', itemid='$rowthread[tid]', idtype='tid'");
		$db_target->query("UPDATE $table_post_target SET tags=CONCAT(tags, '$tagid,$row[tagname]\t') WHERE tid='$rowthread[tid]' AND first='1'");
	}

}

if($nextid) {
	showmessage("繼續轉換數據表 ".$table_source." tag > $nextid ", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid"."&_tagid_exists={$_tagid_exists}");

/*
	$_p = daddslashes($_p);
	$_p2 = daddslashes($_p2);

	showmessage("$_p<hr>$_p2<hr>繼續轉換數據表 ".$table_source." tag > $nextid : $_j : $_i", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid"."&_j={$_j}&_i={$_i}&_tagid_exists={$_tagid_exists}&_p=".rawurlencode($_p)."&_p2=".rawurlencode($_p2)."");
*/
}

?>