
#將原來的 smallint(6) 改為 TINYINT( 3 ) 減少不必要的浪費資料庫
ALTER TABLE `pre_forum_forum` CHANGE `displayorder` `displayorder` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `pre_common_member_profile_setting` CHANGE `displayorder` `displayorder` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `pre_home_blog_category` CHANGE `displayorder` `displayorder` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `pre_home_class` ADD ` displayorder` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `pre_forum_threadclass` CHANGE `displayorder` `displayorder` TINYINT( 1 ) NOT NULL DEFAULT '0';
