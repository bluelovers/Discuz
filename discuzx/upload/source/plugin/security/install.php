<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: install.php 26608 2011-12-16 06:47:48Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

CREATE TABLE IF NOT EXISTS `pre_security_evilpost` (
  `pid` int(10) unsigned NOT NULL COMMENT '帖子ID',
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '主題ID',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '帖子類型',
  `evilcount` int(10) NOT NULL DEFAULT '0' COMMENT '惡意次數',
  `eviltype` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '惡意類型',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '創建時間',
  `operateresult` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '操作結果：1 通過 2 刪除 3 忽略',
  `isreported` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已經上報',
  PRIMARY KEY (`pid`),
  KEY `type` (`tid`,`type`),
  KEY `operateresult` (`operateresult`,`createtime`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `pre_security_eviluser` (
  `uid` int(10) unsigned NOT NULL COMMENT '用戶ID',
  `evilcount` int(10) NOT NULL DEFAULT '0' COMMENT '惡意次數',
  `eviltype` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '惡意類型',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '創建時間',
  `operateresult` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '操作結果：1 恢復 2 刪除 3 忽略',
  `isreported` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已經上報',
  PRIMARY KEY (`uid`),
  KEY `operateresult` (`operateresult`,`createtime`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `pre_security_failedlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主鍵',
  `reporttype` char(20) NOT NULL COMMENT '上報類型',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'TID',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'PID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UID',
  `failcount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '計數',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '失敗時間',
  `posttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '發帖時間/上次發帖時間',
  `delreason` char(255) NOT NULL COMMENT '處理原因',
  `scheduletime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '計劃重試時間',
  `lastfailtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上次失敗時間',
  `extra1` int(10) unsigned NOT NULL COMMENT '整型的擴展字段',
  `extra2` char(255) NOT NULL DEFAULT '0' COMMENT '字符類型的擴展字段',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM

EOF;

runquery($sql);

$finish = true;

?>