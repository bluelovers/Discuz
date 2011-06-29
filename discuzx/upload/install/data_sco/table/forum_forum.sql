
# 使 pre_forum_forum.recommend 轉為 TEXT 來儲存 fid 字串陣列
ALTER TABLE `pre_forum_forum` CHANGE `recommend` `recommend` TEXT NOT NULL ;
