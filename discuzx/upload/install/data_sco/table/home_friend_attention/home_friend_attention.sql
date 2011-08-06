
DROP TABLE IF EXISTS `pre_home_friend_attention`;
CREATE TABLE IF NOT EXISTS `pre_home_friend_attention` (
  `uid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `fuid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`,`fuid`),
  KEY `uid` (`uid`,`dateline`),
  KEY `fuid` (`fuid`)
) ENGINE=MyISAM COMMENT = '關注用戶';
