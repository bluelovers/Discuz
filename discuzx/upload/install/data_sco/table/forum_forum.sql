
# 使 pre_forum_forum.recommend 轉為 TEXT 來儲存 fid 字串陣列
ALTER TABLE `pre_forum_forum` CHANGE `recommend` `recommend` TEXT NOT NULL COMMENT '推薦的板塊';


ALTER TABLE `pre_forum_forumfield` CHANGE `dateline` `dateline` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '建立時間';
