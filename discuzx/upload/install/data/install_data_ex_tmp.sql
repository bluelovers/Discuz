

ALTER TABLE pre_common_member_profile ADD `nickname` VARCHAR( 255 ) NOT NULL DEFAULT '';

INSERT INTO pre_common_member_profile_setting (
`fieldid` ,
`available` ,
`invisible` ,
`needverify` ,
`title` ,
`description` ,
`displayorder` ,
`required` ,
`unchangeable` ,
`showinthread` ,
`allowsearch` ,
`formtype` ,
`size` ,
`choices` ,
`validate`
)
VALUES (
'nickname', '1', '0', '0', '暱稱', '', '0', '0', '0', '0', '1', 'text', '0', '', ''
);

UPDATE `pre_common_member_profile_setting` SET `invisible` = '0' WHERE `fieldid` IN ('bloodtype', '	constellation', 'zodiac', 'nickname', 'nationality');
UPDATE `pre_common_member_profile_setting` SET `allowsearch` = '1' WHERE `fieldid` IN ('bloodtype', 'constellation', 'zodiac', 'nickname', 'nationality', 'affectivestatus', 'lookingfor', 'site', 'bio', 'interest', 'gender');

ALTER TABLE `pre_common_member_profile_setting` ADD `typename` VARCHAR( 20 ) NOT NULL DEFAULT '' COMMENT '資料類別';

#增加資料表存放懸賞主題最佳答案
DROP TABLE IF EXISTS pre_forum_thread_rewardlog;
CREATE TABLE IF NOT EXISTS pre_forum_thread_rewardlog (
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `authorid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `answererid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned DEFAULT '0',
  `netamount` int(10) unsigned NOT NULL DEFAULT '0',
  `answererpid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '最佳答案的PID',
  `answererdateline` int(10) NOT NULL DEFAULT '0' COMMENT '最佳答案時間',
  `setreward_uid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '選擇最佳答案的操作者',
  `setreward_author` varchar(15) NOT NULL DEFAULT '' COMMENT '選擇最佳答案的操作者',
  KEY `userid` (`authorid`,`answererid`)
) ENGINE=MyISAM;

ALTER TABLE `pre_forum_forumfield` ADD `article` TEXT NOT NULL ;

ALTER TABLE `pre_home_feed` ADD `lang_template` TINYTEXT NOT NULL DEFAULT '' COMMENT '語言模板';
ALTER TABLE `pre_home_share` ADD `lang_template` TINYTEXT NOT NULL DEFAULT '' COMMENT '語言模板';

#地區資料
ALTER TABLE `pre_common_district` ADD `displayorder` TINYINT( 1 ) NOT NULL DEFAULT '0';

#將原來的 smallint(6) 改為 TINYINT( 3 ) 減少不必要的浪費資料庫
ALTER TABLE `pre_forum_forum` CHANGE `displayorder` `displayorder` TINYINT( 1 ) NOT NULL DEFAULT '0';

#DROP TABLE IF EXISTS `pre_common_stat_month`;
#CREATE TABLE IF NOT EXISTS `pre_common_stat_month` (
#  `daytime` int(10) unsigned NOT NULL DEFAULT '0',
#  `login` int(10) unsigned NOT NULL DEFAULT '0',
#  `register` int(10) unsigned NOT NULL DEFAULT '0',
#  `invite` int(10) unsigned NOT NULL DEFAULT '0',
#  `appinvite` int(10) unsigned NOT NULL DEFAULT '0',
#  `doing` int(10) unsigned NOT NULL DEFAULT '0',
#  `blog` int(10) unsigned NOT NULL DEFAULT '0',
#  `pic` int(10) unsigned NOT NULL DEFAULT '0',
#  `poll` int(10) unsigned NOT NULL DEFAULT '0',
#  `activity` int(10) unsigned NOT NULL DEFAULT '0',
#  `share` int(10) unsigned NOT NULL DEFAULT '0',
#  `thread` int(10) unsigned NOT NULL DEFAULT '0',
#  `docomment` int(10) unsigned NOT NULL DEFAULT '0',
#  `blogcomment` int(10) unsigned NOT NULL DEFAULT '0',
#  `piccomment` int(10) unsigned NOT NULL DEFAULT '0',
#  `sharecomment` int(10) unsigned NOT NULL DEFAULT '0',
#  `reward` int(10) unsigned NOT NULL DEFAULT '0',
#  `debate` int(10) unsigned NOT NULL DEFAULT '0',
#  `trade` int(10) unsigned NOT NULL DEFAULT '0',
#  `group` int(10) unsigned NOT NULL DEFAULT '0',
#  `groupjoin` int(10) unsigned NOT NULL DEFAULT '0',
#  `groupthread` int(10) unsigned NOT NULL DEFAULT '0',
#  `grouppost` int(10) unsigned NOT NULL DEFAULT '0',
#  `post` int(10) unsigned NOT NULL DEFAULT '0',
#  `wall` int(10) unsigned NOT NULL DEFAULT '0',
#  `poke` int(10) unsigned NOT NULL DEFAULT '0',
#  `click` int(10) unsigned NOT NULL DEFAULT '0',
#  PRIMARY KEY (`daytime`)
#) ENGINE=MyISAM;


DROP TABLE IF EXISTS `pre_common_tags`;
CREATE TABLE IF NOT EXISTS `pre_common_tags` (
  `tagid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tagname` char(30) NOT NULL,

  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `total` mediumint(8) unsigned NOT NULL,

  `fupid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上層tagid',

  `same_tagname` text NOT NULL COMMENT '同義的tag',
  `same_tagid` tinytext NOT NULL,

  `link_tagname` text NOT NULL COMMENT '關聯的tag',
  `link_tagid` tinytext NOT NULL,

  `tag_author` varchar(15) NOT NULL DEFAULT '' COMMENT '建立者',
  `tag_authorid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `tag_dateline` int(10) unsigned NOT NULL DEFAULT '0',

  `last_author` varchar(15) NOT NULL DEFAULT '' COMMENT '最後使用者',
  `last_authorid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `last_dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tagname`),
  UNIQUE KEY `tagid` (`tagid`),
  KEY `total` (`total`),
  KEY `closed` (`closed`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pre_common_tags_thread`;
CREATE TABLE IF NOT EXISTS `pre_common_tags_thread` (
  `keyid` int(10) unsigned NOT NULL,
  `tagid` int(10) unsigned NOT NULL,
  `tagname` varchar(30) NOT NULL,
  KEY `tagid` (`tagid`),
  KEY `keyid` (`keyid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pre_common_tags_data`;
CREATE TABLE IF NOT EXISTS `pre_common_tags_data` (
  `keyid` int(10) unsigned NOT NULL,
  `tagid` int(10) unsigned NOT NULL,
  `tagname` varchar(30) NOT NULL,

  `keytype` varchar(20) NOT NULL,

  KEY `tagid` (`tagid`),
  KEY `keyid` (`keyid`),
  KEY `keytype` (`keytype`)
) ENGINE=MyISAM;

ALTER TABLE `pre_forum_threadclass` COMMENT = '主題分類 typeid threadtypes';
ALTER TABLE `pre_forum_threadtype`  COMMENT = '分類信息 sortid threadsorts';

ALTER TABLE `pre_forum_threadclass` ADD `ishide` TINYINT( 1 ) NOT NULL DEFAULT '0' COMMENT '是否在顯示全部主題時預設為隱藏';
ALTER TABLE `pre_forum_threadclass` CHANGE `displayorder` `displayorder` TINYINT( 1 ) NOT NULL DEFAULT '0';

ALTER TABLE `pre_common_member_profile_setting` CHANGE `displayorder` `displayorder` TINYINT( 1 ) NOT NULL DEFAULT '0';

#修改為可推薦多個板塊
ALTER TABLE `pre_forum_forum` CHANGE `recommend` `recommend` TEXT NOT NULL DEFAULT '' COMMENT '推薦的板塊';

#修改為可依照模塊變換風格
ALTER TABLE `pre_common_nav` ADD `styleid` SMALLINT( 6 ) UNSIGNED NOT NULL DEFAULT '0';

UPDATE `pre_common_nav` SET `name` = '設施', `url` = 'plugin.php' WHERE `id` =6;
UPDATE `pre_common_nav` SET `name` = '社區', `title` = 'Home' WHERE `id` =4;
UPDATE `pre_common_nav` SET `title` = 'Portal' WHERE `id` =1;

ALTER TABLE `pre_forum_bbcode` ADD `icontype` TINYINT( 1 ) NOT NULL DEFAULT '0' COMMENT 'icon 類型';
ALTER TABLE `pre_forum_bbcode` ADD `tag_alias` TINYTEXT NOT NULL COMMENT '標籤別名';

ALTER TABLE `pre_forum_bbcode` CHANGE `icon` `icon` TEXT NOT NULL DEFAULT '';
ALTER TABLE `pre_forum_bbcode` CHANGE `example` `example` TEXT NOT NULL DEFAULT '';

ALTER TABLE `pre_forum_thread` ADD `specialextra` VARCHAR( 100 ) NOT NULL COMMENT '因插件產生的特殊主題';

CREATE TABLE `pre_forum_thread_specialextra` (
	`tid` MEDIUMINT( 8 )  UNSIGNED NOT NULL DEFAULT '0',
	`fid` SMALLINT( 6 ) UNSIGNED NOT NULL DEFAULT '0',
	`specialextra` VARCHAR( 100 ) NOT NULL ,
	PRIMARY KEY ( `tid` ) ,
	INDEX ( `specialextra` )
) ENGINE = MYISAM COMMENT = '因插件產生的特殊主題';

ALTER TABLE `pre_common_session` ADD `gender` TINYINT( 1 ) NOT NULL DEFAULT '0' COMMENT '性別';
ALTER TABLE `pre_common_session` ADD `session_lang` CHAR( 15 ) NOT NULL COMMENT '語言';

CREATE TABLE `pre_common_session_data` (
`sid` CHAR( 6 ) NOT NULL ,
`uid` MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT '0',
`session_dateline` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
`session_data` TEXT NOT NULL DEFAULT '',
PRIMARY KEY ( `sid` ) ,
INDEX ( `uid` )
) ENGINE = MYISAM ;

ALTER TABLE `pre_home_class` ADD `upid` MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `pre_home_blog_category` CHANGE `displayorder` `displayorder` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `pre_home_class` ADD ` displayorder` TINYINT( 1 ) NOT NULL DEFAULT '0';
