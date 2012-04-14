<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: install.php 27070 2012-01-04 05:55:20Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$field = C::t('#security#security_failedlog')->fetch_all_field();
$table = DB::table('security_failedlog');
$sql = '';
if (!$field['scheduletime']) {
	$sql .= "ALTER TABLE $table ADD `scheduletime` INT(10) NOT NULL DEFAULT '0';\n";
}

if (!$field['lastfailtime']) {
	$sql .= "ALTER TABLE $table ADD `lastfailtime` INT(10) NOT NULL DEFAULT '0';\n";
}

if (!$field['posttime']) {
	$sql .= "ALTER TABLE $table ADD `posttime` INT(10) unsigned NOT NULL DEFAULT '0';\n";
}

if (!$field['delreason']) {
	$sql .= "ALTER TABLE $table ADD `delreason` char(255) NOT NULL;\n";
}

if (!$field['extra1']) {
	$sql .= "ALTER TABLE $table ADD `extra1` INT(10) unsigned NOT NULL DEFAULT '0';\n";
}

if (!$field['extra2']) {
	$sql .= "ALTER TABLE $table ADD `extra2` char(255) NOT NULL;\n";
}
if ($sql) {
	runquery($sql);
}

$table = DB::table('common_plugin');
include DISCUZ_ROOT . 'source/language/lang_admincp_cloud.php';
$format = "UPDATE $table SET name = '%s' WHERE identifier = 'security'";
$name = $extend_lang['menu_cloud_security'];
$sql = sprintf($format, $name);

runquery($sql);

$finish = true;