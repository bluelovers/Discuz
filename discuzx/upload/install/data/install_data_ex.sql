
ALTER TABLE `pre_home_feed` ADD `lang_template` TINYTEXT NOT NULL DEFAULT '' COMMENT '語言模板';
ALTER TABLE `pre_home_share` ADD `lang_template` TINYTEXT NOT NULL DEFAULT '' COMMENT '語言模板';

#修改為可推薦多個板塊
ALTER TABLE `pre_forum_forum` CHANGE `recommend` `recommend` TEXT NOT NULL DEFAULT '' COMMENT '推薦的板塊';
