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
$sql = '';
if (!$field['scheduletime']) {
	$sql .= "ALTER TABLE `pre_security_failedlog` ADD `scheduletime` INT(10) NOT NULL DEFAULT '0';\n";
}

if (!$field['lastfailtime']) {
	$sql .= "ALTER TABLE `pre_security_failedlog` ADD `lastfailtime` INT(10) NOT NULL DEFAULT '0';\n";
}

if (!$field['posttime']) {
	$sql .= "ALTER TABLE `pre_security_failedlog` ADD `posttime` INT(10) unsigned NOT NULL DEFAULT '0';\n";
}

if (!$field['delreason']) {
	$sql .= "ALTER TABLE `pre_security_failedlog` ADD `delreason` char(255) NOT NULL;\n";
}

if (!$field['extra1']) {
	$sql .= "ALTER TABLE `pre_security_failedlog` ADD `extra1` INT(10) unsigned NOT NULL DEFAULT '0';\n";
}

if (!$field['extra2']) {
	$sql .= "ALTER TABLE `pre_security_failedlog` ADD `extra2` char(255) NOT NULL;\n";
}
if ($sql) {
	runquery($sql);
}

include DISCUZ_ROOT . 'source/language/lang_admincp_cloud.php';
$format = "UPDATE `pre_common_plugin` SET name = '%s' WHERE identifier = 'security'";
$name = $extend_lang['menu_cloud_security'];
$sql = sprintf($format, $name);

runquery($sql);

$finish = true;