
ALTER TABLE `pre_forum_moderator` ADD `dateline` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '任命日期';
ALTER TABLE `pre_forum_moderator` ADD `by_uid` MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '由誰任命';
