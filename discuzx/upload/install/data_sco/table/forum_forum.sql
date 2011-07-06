
# 使 pre_forum_forum.recommend 轉為 TEXT 來儲存 fid 字串陣列
ALTER TABLE `pre_forum_forum` CHANGE `recommend` `recommend` TEXT NOT NULL COMMENT '推薦的板塊';

#將原來的 smallint(6) 改為 TINYINT( 3 ) 減少不必要的浪費資料庫
ALTER TABLE `pre_forum_forum` CHANGE `displayorder` `displayorder` TINYINT( 1 ) NOT NULL DEFAULT '0';
