
DROP TABLE IF EXISTS `pre_home_theme_diy`;
CREATE TABLE `pre_home_theme_diy` (
`theme_id` SMALLINT( 6 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
`theme_fup_id` SMALLINT( 6 ) UNSIGNED NOT NULL DEFAULT '0',
`theme_name` VARCHAR( 30 ) NOT NULL DEFAULT '',
`theme_css` TEXT NOT NULL ,
PRIMARY KEY ( `theme_id` ) ,
INDEX ( `theme_fup_id` )
) ENGINE = MYISAM COMMENT = '自定義風格';

ALTER TABLE `pre_home_theme_diy` ADD `theme_authorid` MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT '0',
ADD INDEX ( `theme_authorid` ) ;

ALTER TABLE `pre_home_theme_diy` ADD `theme_baseon` VARCHAR( 30 ) NOT NULL DEFAULT '' COMMENT '基於哪一個空間風格';
