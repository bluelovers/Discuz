<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: install.php 29353 2012-04-06 03:00:07Z liudongdong $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

CREATE TABLE IF NOT EXISTS pre_common_member_connect (
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `conuin` char(40) NOT NULL default '',
  `conuinsecret` char(16) NOT NULL default '',
  `conopenid` char(32) NOT NULL default '',
  `conisfeed` tinyint(1) unsigned NOT NULL default '0',
  `conispublishfeed` tinyint(1) unsigned NOT NULL default '0',
  `conispublisht` tinyint(1) unsigned NOT NULL default '0',
  `conisregister` tinyint(1) unsigned NOT NULL default '0',
  `conisqzoneavatar` tinyint(1) unsigned NOT NULL default '0',
  `conisqqshow` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `conuin` (`conuin`),
  KEY `conopenid` (`conopenid`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS pre_connect_feedlog (
  flid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  tid mediumint(8) unsigned NOT NULL DEFAULT '0',
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  publishtimes mediumint(8) unsigned NOT NULL DEFAULT '0',
  lastpublished int(10) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (flid),
  UNIQUE KEY tid (tid)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS pre_connect_memberbindlog (
  mblid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  uin char(40) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (mblid),
  KEY uid (uid),
  KEY uin (uin),
  KEY dateline (dateline)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS pre_connect_tthreadlog (
  twid char(16) NOT NULL,
  tid mediumint(8) unsigned NOT NULL DEFAULT '0',
  conopenid char(32) NOT NULL,
  pagetime int(10) unsigned DEFAULT '0',
  lasttwid char(16) DEFAULT NULL,
  nexttime int(10) unsigned DEFAULT '0',
  updatetime int(10) unsigned DEFAULT '0',
  dateline int(10) unsigned DEFAULT '0',
  PRIMARY KEY (twid),
  KEY nexttime (tid,nexttime),
  KEY updatetime (tid,updatetime)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS pre_common_uin_black (
  uin char(40) NOT NULL,
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uin),
  UNIQUE KEY uid (uid)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS pre_common_connect_guest (
  `conopenid` char(32) NOT NULL default '',
  `conuin` char(40) NOT NULL default '',
  `conuinsecret` char(16) NOT NULL default '',
  `conqqnick` char(100) NOT NULL default '',
  PRIMARY KEY (conopenid)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `pre_connect_disktask` (
  `taskid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '任务ID',
  `aid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `openid` char(32) NOT NULL DEFAULT '' COMMENT 'openId',
  `filename` varchar(255) NOT NULL DEFAULT '' COMMENT '附件名称',
  `verifycode` char(32) NOT NULL DEFAULT '' COMMENT '下载验证码',
  `status` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '下载状态',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加任务的时间',
  `downloadtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下载完成时间',
  `extra` text COMMENT '保留字段',
  PRIMARY KEY (`taskid`),
  KEY `openid` (`openid`),
  KEY `status` (`status`)
) TYPE=MyISAM COMMENT='网盘下载任务表';


REPLACE INTO pre_common_setting VALUES ('regconnect', '1');
REPLACE INTO pre_common_setting VALUES ('connect', 'a:19:{s:5:"allow";s:1:"1";s:4:"feed";a:2:{s:5:"allow";s:1:"1";s:5:"group";s:1:"0";}s:1:"t";a:4:{s:5:"allow";s:1:"1";s:5:"group";s:1:"0";s:5:"reply";i:1;s:16:"reply_showauthor";i:1;}s:10:"like_allow";s:1:"1";s:7:"like_qq";s:0:"";s:10:"turl_allow";s:1:"1";s:7:"turl_qq";s:0:"";s:8:"like_url";s:0:"";s:17:"register_birthday";s:1:"0";s:15:"register_gender";s:1:"0";s:17:"register_uinlimit";s:0:"";s:21:"register_rewardcredit";s:1:"1";s:18:"register_addcredit";s:0:"";s:16:"register_groupid";s:1:"0";s:18:"register_regverify";s:1:"1";s:15:"register_invite";s:1:"1";s:10:"newbiespan";s:0:"";s:9:"turl_code";s:0:"";s:13:"mblog_app_key";s:3:"abc";}');

EOF;

runquery($sql);

$setting = C::t('common_setting')->fetch_all(array('connect'));
$setting['connect'] = (array)dunserialize($setting['connect']);
$connect = $setting['connect'];

$needCreateGroup = false;
if ($connect['guest_groupid']) {
	$group = C::t('common_usergroup')->fetch($connect['guest_groupid']);
	if (!$group) {
		$needCreateGroup = true;
	}
} else {
	$needCreateGroup = true;
}

include DISCUZ_ROOT . 'source/language/lang_admincp_cloud.php';
$name = $extend_lang['connect_guest_group_name'];
if ($needCreateGroup) {
	$userGroupData = array(
		'type' => 'special',
		'grouptitle' => $name,
		'allowvisit' => 1,
		'color' => '',
		'stars' => '',
	);
	$newGroupId = C::t('common_usergroup')->insert($userGroupData, true);

	$dataField = array(
		'groupid' => $newGroupId,
		'allowsearch' => 2,
		'readaccess' => 1,
		'allowgetattach' => 1,
		'allowgetimage' => 1,
	);
	C::t('common_usergroup_field')->insert($dataField);

	$newConnect = array_merge($connect, array('guest_groupid' => $newGroupId));
	C::t('common_setting')->update_batch(array('connect' => serialize($newConnect)));
	updatecache('usergroups');
}
updatecache('setting');
$finish = true;