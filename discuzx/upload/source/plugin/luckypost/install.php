<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$plugintable = DB::table('common_plugin_luckypost');
$pluginlogtable = DB::table('common_plugin_luckypostlog');

$sql = <<<EOF

DROP TABLE IF EXISTS {$plugintable};
CREATE TABLE {$plugintable} (
  `lid` int(10) unsigned NOT NULL auto_increment,
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `tid` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `extcredit` tinyint(1) NOT NULL default '0',
  `credits` int(10) NOT NULL,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `eventid` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`lid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS {$pluginlogtable};
CREATE TABLE {$pluginlogtable} (
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `goodtimes` int(10) unsigned NOT NULL,
  `badtimes` int(10) unsigned NOT NULL,
  KEY `uid` (`uid`)
) TYPE=MyISAM;

EOF;

runquery($sql);
if(!is_file(DISCUZ_ROOT.'./data/attachment/common/bgpic.gif')) {
	rename('./source/plugin/luckypost/template/bgpic.gif', DISCUZ_ROOT.'./data/attachment/common/bgpic.gif');
}
$finish = TRUE;

?>