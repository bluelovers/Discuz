<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denid');
}

$sql = "DROP TABLE IF EXISTS ".DB::table('common_plugin_threadclick').";
CREATE TABLE ".DB::table('common_plugin_threadclick')." (
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  click1 smallint(6) unsigned NOT NULL DEFAULT '0',
  click2 smallint(6) unsigned NOT NULL DEFAULT '0',
  click3 smallint(6) unsigned NOT NULL DEFAULT '0',
  click4 smallint(6) unsigned NOT NULL DEFAULT '0',
  click5 smallint(6) unsigned NOT NULL DEFAULT '0',
  click6 smallint(6) unsigned NOT NULL DEFAULT '0',
  click7 smallint(6) unsigned NOT NULL DEFAULT '0',
  click8 smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (tid),
  KEY uid (uid)
) TYPE=MyISAM;


INSERT INTO pre_home_click (`name`, `icon`, `idtype`, `available`, displayorder) VALUES ('{$installlang['passby']}','luguo.gif','tid','1','0');
INSERT INTO pre_home_click (`name`, `icon`, `idtype`, `available`, displayorder) VALUES ('{$installlang['shock']}','leiren.gif','tid','1','0');
INSERT INTO pre_home_click (`name`, `icon`, `idtype`, `available`, displayorder) VALUES ('{$installlang['shakehand']}','woshou.gif','tid','1','0');
INSERT INTO pre_home_click (`name`, `icon`, `idtype`, `available`, displayorder) VALUES ('{$installlang['flower']}','xianhua.gif','tid','1','0');
INSERT INTO pre_home_click (`name`, `icon`, `idtype`, `available`, displayorder) VALUES ('{$installlang['egg']}','jidan.gif','tid','1','0');
";
runquery($sql);

$finish = TRUE;

?>