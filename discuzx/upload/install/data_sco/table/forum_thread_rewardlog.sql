
#增加資料表存放懸賞主題最佳答案
DROP TABLE IF EXISTS pre_forum_thread_rewardlog;
CREATE TABLE IF NOT EXISTS pre_forum_thread_rewardlog (
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `authorid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `answererid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned DEFAULT '0',
  `netamount` int(10) unsigned NOT NULL DEFAULT '0',
  `answererpid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '最佳答案的PID',
  `answererdateline` int(10) NOT NULL DEFAULT '0' COMMENT '最佳答案時間',
  `setreward_uid` mediumint(8) NOT NULL DEFAULT '0' COMMENT '選擇最佳答案的操作者',
  `setreward_author` varchar(15) NOT NULL DEFAULT '' COMMENT '選擇最佳答案的操作者',
  KEY `userid` (`authorid`,`answererid`)
) ENGINE=MyISAM;
