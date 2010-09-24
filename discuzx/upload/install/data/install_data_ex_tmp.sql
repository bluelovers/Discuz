



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

#修改為可依照模塊變換風格
ALTER TABLE `pre_common_nav` ADD `styleid` SMALLINT( 6 ) UNSIGNED NOT NULL DEFAULT '0';
