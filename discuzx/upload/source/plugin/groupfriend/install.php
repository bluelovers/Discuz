<?php

/**
 *      [Discuz! X1.5] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $author : CongYuShuai(Max.Cong) Date:2010-09-09$
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$sql = <<<EOF
CREATE TABLE IF NOT EXISTS pre_plugin_groupfriend (
  `groupid` mediumint(8) unsigned NOT NULL,
  `friendid` mediumint(8) unsigned NOT NULL,
  `dateline` int(10) unsigned NOT NULL,
  KEY `groupid` (`groupid`,`friendid`)
) ENGINE=MyISAM;
EOF;

runquery($sql);
$finish = TRUE;
?>