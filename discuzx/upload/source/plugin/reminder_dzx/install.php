<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$plugintable = DB::table('common_plugin_reminder');

$sql = <<<EOF

DROP TABLE IF EXISTS {$plugintable};
CREATE TABLE {$plugintable} (
 `uid` mediumint(8) unsigned NOT NULL,
 `remind` tinyint(1) NOT NULL default '0',
 `readtype` text NOT NULL,
 PRIMARY KEY  (`uid`)
) TYPE=MyISAM;

EOF;

runquery($sql);
$finish = TRUE;

?>


