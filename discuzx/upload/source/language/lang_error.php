<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_error.php 15532 2010-08-24 10:08:02Z cnteacher $
 */

$lang = array
(
	'System Message' => '站點信息',

	'config_notfound' => '配置文件 "config_global.php" 未找到或者無法訪問。',
	'template_notfound' => '模版文件 "$tplfile" 未找到或者無法訪問。',
	'directory_notfound' => '目錄 "$dir" 未找到或者無法訪問。',
	'request_tainting' => '非法的提交請求。',
	'db_error' => '<b>$message</b>$errorno<br />$info$sql$backtrace<br /><a href="$helplink" target="_blank">點擊這裡尋求幫助</a><br /><br />',
	'db_error_message' => '<b>錯誤信息</b>: $dberror<br />',
	'db_error_sql' => '<b>SQL</b>: $sql<br />',
	'db_error_backtrace' => '<b>Backtrace</b>: $backtrace<br />',
	'db_error_no' => ' [$dberrno]',
	'db_notfound_config' => '配置文件 "config_global.php" 未找到或者無法訪問。',
	'db_notconnect' => '無法連接到數據庫服務器',
	'db_security_error' => '查詢語句安全威脅',
	'db_query_error' => '查詢語句錯誤',
	'db_config_db_not_found' => '數據庫配置錯誤，請仔細檢查 config_global.php 文件',
	'system_init_ok' => '網站系統初始化完成，請<a href="index.php">點擊這裡</a>進入',

	'file_upload_error_-101' => '上傳失敗！上傳文件不存在或不合法，請返回。',
	'file_upload_error_-102' => '上傳失敗！非圖片類型文件，請返回。',
	'file_upload_error_-103' => '上傳失敗！無法寫入文件或寫入失敗，請返回。',
	'file_upload_error_-104' => '上傳失敗！無法識別的圖像文件格式，請返回。',
);

?>