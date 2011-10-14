<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$sql = "CREATE TABLE IF NOT EXISTS `".$_G['config']['db']['1']['tablepre']."facebook_connect` (
  `uid` int(11) NOT NULL,
  `fbid` bigint(20) NOT NULL,
  `showfblink` int(1) DEFAULT 1 NOT NULL,
  `liketid` mediumtext NOT NULL,
  UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
runquery($sql);

if(!DB::query("SELECT showfblink FROM ".$_G['config']['db']['1']['tablepre']."facebook_connect", 'SILENT')) {
	DB::query("ALTER TABLE ".$_G['config']['db']['1']['tablepre']."facebook_connect ADD showfblink int(1) DEFAULT 1 NOT NULL", 'SILENT');
}
if(!DB::query("SELECT liketid FROM ".$_G['config']['db']['1']['tablepre']."facebook_connect", 'SILENT')) {
	DB::query("ALTER TABLE ".$_G['config']['db']['1']['tablepre']."facebook_connect ADD liketid mediumtext NOT NULL", 'SILENT');
}

$finish = true;

?>