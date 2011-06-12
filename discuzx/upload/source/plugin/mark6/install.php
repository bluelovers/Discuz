<?php

// this file is not created by author 1224
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
exit('ACCESS DENIED');
}
$tablepre = DB::table('plugin_');
$commonpre = DB::table('common_');
$charset = $_G['charset'];
$installSQL = <<<EOT

DROP TABLE IF EXISTS `{$tablepre}mark6`;
CREATE TABLE `{$tablepre}mark6` (
  `id` smallint(6) unsigned NOT NULL auto_increment,
  `username` varchar(15) NOT NULL default '',
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `gameid` smallint(6) unsigned NOT NULL default '0',
  `number` varchar(64) NOT NULL default '',
  `duzhu` mediumint(6) unsigned NOT NULL default '0',
  `correct` smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=$charset AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `{$tablepre}mark6cp`;
CREATE TABLE `{$tablepre}mark6cp` (
  `id` smallint(6) unsigned NOT NULL auto_increment,
  `shownumber` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=$charset AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `{$tablepre}mark6jackpot`;
CREATE TABLE `{$tablepre}mark6jackpot` (
  `id` smallint(6) unsigned NOT NULL auto_increment,
  `jackpot` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=$charset AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `{$tablepre}mark6list`;
CREATE TABLE `{$tablepre}mark6list` (
  `id` smallint(6) unsigned NOT NULL auto_increment,
  `duzhu` bigint(6) unsigned NOT NULL default '0',
  `uid` smallint(6) NOT NULL default '0',
  `username` varchar(15) NOT NULL default '',
  `win3` smallint(6) unsigned NOT NULL default '0',
  `win2` smallint(6) unsigned NOT NULL default '0',
  `win1` smallint(6) unsigned NOT NULL default '0',
  `totalwin` bigint(6) NOT NULL default '0',
  `winonly` bigint(6) NOT NULL default '0',
  `totalmoney` bigint(6) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=$charset AUTO_INCREMENT=1 ;

INSERT INTO `{$commonpre}cron` (`available`, `type`, `name`, `filename`, `lastrun`, `nextrun`, `weekday`, `day`, `hour`, `minute`) VALUES
(1, 'user', 'mark6', 'cron_mark6.php', 1283173977, 0, -1, -1, 0, '0');
EOT;

runquery($installSQL);

$finish = true;

?>