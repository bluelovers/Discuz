
# session 額外紀錄
ALTER TABLE `pre_common_session` ADD `gender` TINYINT( 1 ) NOT NULL DEFAULT '0' COMMENT '性別';
ALTER TABLE `pre_common_session` ADD `session_lang` CHAR( 15 ) NOT NULL COMMENT '語言';
