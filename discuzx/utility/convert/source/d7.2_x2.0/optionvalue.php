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

// 接收已經處理過 sortid 陣列
$_sorts = getgpc('_sorts');

// 初始化 sortid 陣列
if(empty($_sorts)) {
	$_sorts = array();
	// 搜尋相關的 TABLE
	$query = mysql_list_tables($db_source->config['dbname'], $db_source->curlink);
	$num_rows = mysql_num_rows($query);
	for ($i = 0; $i < $num_rows; $i++) {
		$row = mysql_tablename($query, $i);

		if (strpos($row, $db_source->config['tablepre'].'optionvalue') !== 0) continue;

		$tabledump = '';
		$sortid = 0;
		// 檢查是否為正確想要搜尋的 TABLE
		$sortid = str_replace($db_source->config['tablepre'].'optionvalue', '', $row);
		if (!empty($sortid) && $sortid == intval($sortid) && is_numeric($sortid)) {
			$sortid == intval($sortid);

			// 複製來源 TABLE 結構
			if ($tabledump = _sqldumptablestruct($db_source->config['tablepre'].'optionvalue'.$sortid, $db_source)) {
				// 刪除已存在的目標 TABLE
				$db_target->query("DROP TABLE IF EXISTS ".$table_target.$sortid);
				// 轉換 TABLE 表的建立語法 將來源 TABLE 名稱取代為目標 TABLE 名稱
				$tabledump = str_replace('CREATE TABLE `'.$db_source->config['tablepre'].'optionvalue'.$sortid.'`', 'CREATE TABLE `'.$db_target->config['tablepre'].'forum_optionvalue'.$sortid.'`', $tabledump);
				$db_target->query($tabledump);
			}
			$_sorts[] = $sortid;
		}
	}

	unset($sortid, $tabledump, $query );
} else {
	// 將接收的 $_sorts 轉換為 Array
	$_sorts = explode(',', $_sorts);
}

$nextid = -1;
// 取出陣列中第一個值作為目前要處理的 sortid
if ($_sortid = $_sorts[0]) {

	$query = $db_source->query("SELECT * FROM $table_source$_sortid WHERE tid>'$start' ORDER BY tid LIMIT $limit");
	while ($row = $db_source->fetch_array($query)) {
		$nextid = $row['tid'];

		$row  = daddslashes($row, 1);
		$data = implode_field_value($row, ',', db_table_fields($db_target, $table_target.$_sortid));
		$db_target->query("INSERT INTO $table_target$_sortid SET $data");
	}

}

// 如果沒有查詢任何資料
if($nextid < 0) {
	// 取出陣列的第一個值
	array_shift($_sorts);
	// 判斷是否已經變為空陣列，如果是則代表以全部處理完成
	if (count($_sorts) == 0) {
		$nextid = 0;
	}
}

if($nextid) {
	// 重設 $nextid
	if ($nextid < 0) $nextid = 0;
	// 將陣列轉為文字陣列
	$_sorts = implode(',', $_sorts);
	// 傳遞 URL 參數
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
//		$tabledump = "DROP TABLE IF EXISTS $table;\n";
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