<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_error.php 646 2010-09-13 03:37:40Z yexinhao $
 */

$lang = array
(
	'System Message' => '站点信息',

	'config_notfound' => '配置文件 "config_global.php" 未找到或者无法访问。',
	'template_notfound' => '模版文件 "$tplfile" 未找到或者无法访问。',
	'directory_notfound' => '目录 "$dir" 未找到或者无法访问。',
	'request_tainting' => '非法的提交请求。',
	'db_error' => '<b>$message</b>$errorno<br />$info$sql<br /><a href="$helplink" target="_blank">点击这里寻求帮助</a><br /><br />',
	'db_error_message' => '<b>错误信息</b>: $dberror<br />',
	'db_error_sql' => '<b>SQL</b>: $sql<br />',
	'db_error_backtrace' => '<b>Backtrace</b>: $backtrace<br />',
	'db_error_no' => ' [$dberrno]',
	'db_notfound_config' => '配置文件 "config_global.php" 未找到或者无法访问。',
	'db_notconnect' => '无法连接到数据库服务器',
	'db_query_error' => '查询语句错误',
	'db_config_db_not_found' => '数据库配置错误，请仔细检查 config_global.php 文件',
	'system_init_ok' => '网站系统初始化完成，请<a href="index.php">点击这里</a>进入',

	'file_upload_error_-101' => '上传失败！上传文件不存在或不合法，请返回。',
	'file_upload_error_-102' => '上传失败！非图片类型文件，请返回。',
	'file_upload_error_-103' => '上传失败！无法写入文件或写入失败，请返回。',
	'file_upload_error_-104' => '上传失败！无法识别的图像文件格式，请返回。',
);

?>