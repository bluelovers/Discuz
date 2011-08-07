
ALTER TABLE `pre_home_feed` ADD `lang_template` TEXT NOT NULL DEFAULT '' COMMENT '語言模板';
ALTER TABLE `pre_home_share` ADD `lang_template` TEXT NOT NULL DEFAULT '' COMMENT '語言模板';
ALTER TABLE `pre_home_notification` ADD `lang_template` TEXT NOT NULL DEFAULT '' COMMENT '語言模板';

# 論壇任務
ALTER TABLE `pre_common_taskvar` ADD `lang_template` TEXT NOT NULL DEFAULT '' COMMENT '語言模版';
