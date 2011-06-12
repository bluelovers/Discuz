<?php
if(!defined('IN_DISCUZ')) {
	exit('Access denied');
}
$sql = <<<EOT
CREATE TABLE pre_plugin_auction (
  `tid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `username` char(15) NOT NULL,
  `aid` int(10) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `extid` tinyint(1) NOT NULL,
  `typeid` tinyint(1) unsigned NOT NULL,
  `name` char(255) NOT NULL,
  `number` smallint(6) NOT NULL,
  `hot` mediumint(8) NOT NULL default '0',
  `top_price` smallint(6) unsigned NOT NULL,
  `ext_price` smallint(6) default NULL,
  `real_price` mediumint(6) default NULL,
  `base_price` smallint(6) default NULL,
  `delta_price` smallint(6) default NULL,
  `starttimefrom` int(10) NOT NULL,
  `starttimeto` int(10) NOT NULL,
  `lastuser` char(15) NOT NULL default '-',
  `extra` text NOT NULL,
  PRIMARY KEY  (`tid`),
  KEY `starttimeto` (`starttimeto`),
  KEY `typeid` (`typeid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM;



CREATE TABLE `pre_plugin_auctionapply` (
  `applyid` int(10) NOT NULL auto_increment,
  `tid` int(10) unsigned NOT NULL,
  `username` char(15) NOT NULL,
  `uid` int(10) NOT NULL,
  `dateline` int(10) NOT NULL,
  `cur_price` mediumint(8) NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  `updated` tinyint(1) NOT NULL default '0',
  `mobile` char(20) NOT NULL,
  PRIMARY KEY  (`applyid`),
  UNIQUE KEY `tid_2` (`tid`,`uid`),
  KEY `uid` (`uid`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM;

EOT;

runquery($sql);
$finish = true;

?>
