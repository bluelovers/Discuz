<?php

require dirname(__FILE__).'/data/config.inc.php';
require dirname(__FILE__).'/lib/db.class.php';

$db = new ucserver_db();
$db->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET, UC_DBCONNECT, UC_DBTABLEPRE);

$nomatch = true;

$applist = $db->fetch_all("SELECT appid, name FROM ".UC_DBTABLEPRE."applications");
$table_columns = loadtable('notelist');
foreach($applist as $app) {
	$appid = $app['appid'];
	if(empty($appid)) continue;
	if(!isset($table_columns['app'.$appid])) {
		$nomatch = false;
		if($db->query("ALTER TABLE ".UC_DBTABLEPRE."notelist ADD COLUMN app$appid tinyint NOT NULL")) {
			echo "補充notelist表字段成功: $appid <br />";
		} else {
			echo "補充notelist表字段失敗，請刷新重試<br />";
		}
		
	}
}

if($nomatch) {
	echo '沒有需要補充的字段<br />';
}

if(!unlink(__FILE__)) {
	echo '請立即登陸服務器刪除此文件<br />';
}

function loadtable($table, $force = 0) {
	global $db;
	static $tables = array();
	if(!isset($tables[$table]) || $force) {
		if($db->version() > '4.1') {
			$query = $db->query("SHOW FULL COLUMNS FROM ".UC_DBTABLEPRE."$table", 'SILENT');
		} else {
			$query = $db->query("SHOW COLUMNS FROM ".UC_DBTABLEPRE."$table", 'SILENT');
		}
		while($field = @$db->fetch_array($query)) {
			$tables[$table][$field['Field']] = $field;
		}
	}
	return $tables[$table];
}



