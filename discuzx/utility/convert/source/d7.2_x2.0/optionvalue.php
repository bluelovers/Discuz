<?php

/**
 * DiscuzX Convert
 *
 * $Id: threadtype.php 18152 2010-11-15 09:52:23Z monkey $
 */

$curprg = basename(__FILE__);

$table_source = $db_source->tablepre.'optionvalue';
$table_target = $db_target->tablepre.'forum_optionvalue';

$limit = 250;
$nextid = 0;
$start = intval(getgpc('start'));
$_sorts = getgpc('_sorts');
$_sortid = getgpc('_sortid');

if(empty($_sorts)) {
	$_sorts = array();

	$query = $db_source->query("SHOW TABLES LIKE '$table_sourcee%'");
	while($row = $db_source->fetch_row($query)) {
		$tabledump = '';
		$sortid = 0;

		$sortid = str_replace($table_source, '', $row);
		if ($sortid && $sortid == intval($sortid)) {
			if ($tabledump = _sqldumptablestruct($table_source.$sortid, $db_source)) {
				$db_target->query("DROP TABLE IF EXISTS ".$table_target.$sortid);

				$tabledump = str_replace('CREATE TABLE '.$table_source.$sortid, 'CREATE TABLE '.$table_target.$sortid, $tabledump);
				$db_target->query($tabledump);
			}

			$_sorts[] = $sortid;
		}
	}

} else {
	$_sorts = explode(',', $_sorts);
}

$nextid = -1;

$_sortid = $_sorts[0];

$query = $db_source->query("SELECT * FROM $table_source$_sortid WHERE tid>'$start' ORDER BY tid LIMIT $limit");
while ($row = $db_source->fetch_array($query)) {

	$nextid = $row['tid'];

	$row  = daddslashes($row, 1);
	$data = implode_field_value($row, ',', db_table_fields($db_target, $table_target.$sortid));
	$db_target->query("INSERT INTO $table_target$sortid SET $data");
}

if($nextid < 0) {
	if (!$_sortid = array_shift($_sorts)) {
		$nextid = 0;
	}
}

if($nextid) {
	if ($nextid < 0) $nextid = 0;
	$_sorts = implode(',', $_sorts);

	$urladd = http_build_query(array(
		'_sorts' => $_sorts,
	));

	showmessage("繼續複製 optionvalue 數據表，sortid=$_sortid, tid > $nextid", "index.php?a=$action&source=$source&prg=$curprg&start=$nextid&".$urladd);
}

function _sqldumptablestruct($table, $db) {
	$dumpcharset = 'utf8';
	$tabledump  = '';

	$createtable = $db->query("SHOW CREATE TABLE $table", 'SILENT');

	if(!$db->error()) {
		$tabledump = "DROP TABLE IF EXISTS $table;\n";
	} else {
		return '';
	}

	$create = $db->fetch_row($createtable);

	if(strpos($table, '.') !== FALSE) {
		$tablename = substr($table, strpos($table, '.') + 1);
		$create[1] = str_replace("CREATE TABLE $tablename", 'CREATE TABLE '.$table, $create[1]);
	}
	$tabledump .= $create[1];

	if($dumpcharset && $db->version() > '4.1') {
		$tabledump = preg_replace("/(DEFAULT)*\s*CHARSET=.+/", "DEFAULT CHARSET=".$dumpcharset, $tabledump);
	}

//	$tablestatus = $db->fetch_first("SHOW TABLE STATUS LIKE '$table'");
//	$tabledump .= ($tablestatus['Auto_increment'] ? " AUTO_INCREMENT=$tablestatus[Auto_increment]" : '').";\n\n";

	return $tabledump;
}

?>