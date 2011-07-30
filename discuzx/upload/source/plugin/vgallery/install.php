<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$sql = <<<EOF
CREATE TABLE IF NOT EXISTS `hsk_vgallerys` (
  `id` int(12) unsigned NOT NULL auto_increment,
  `sid` mediumint(6) NOT NULL,
  `tid` mediumint(16) NOT NULL default '0',
  `pid` int(18) NOT NULL default '0',
  `vprice` mediumint(12) NOT NULL default '0',
  `album` mediumint(1) NOT NULL default '0',
  `sup` mediumint(12) NOT NULL default '0',
  `vsum` mediumint(10) NOT NULL default '0',
  `vsubject` varchar(250) NOT NULL,
  `vurl` varchar(250) NOT NULL,
  `purl` varchar(250) NOT NULL,
  `uid` int(15) NOT NULL,
  `dateline` int(16) NOT NULL,
  `timelong` int(12) NOT NULL,
  `views` int(12) NOT NULL,
  `polls` int(2) NOT NULL,
  `valuate` int(4) NOT NULL,
  `audit` int(1) NOT NULL,
  `tag` varchar(20) NOT NULL,
  `replyuid` varchar(16) NOT NULL,
  `updateline` int(18) NOT NULL,
  `vinfo` mediumtext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `sid` (`sid`,`uid`,`dateline`,`views`,`polls`,`valuate`,`audit`),
  KEY `updateline` (`updateline`),
  KEY `replyuid` (`replyuid`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `hsk_vgallery_evaluate` (
  `id` int(16) unsigned NOT NULL auto_increment,
  `vid` int(12) NOT NULL,
  `uid` int(16) NOT NULL,
  `dateline` int(16) NOT NULL,
  `audit` mediumint(1) NOT NULL,
  `post` mediumtext NOT NULL,
  `mypolls` mediumint(2) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `id` (`vid`,`uid`,`audit`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `hsk_vgallery_favorites` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `vid` mediumint(12) NOT NULL,
  `uid` mediumint(12) NOT NULL,
  `dateline` varchar(16) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `hsk_vgallery_report` (
  `id` mediumint(12) unsigned NOT NULL auto_increment,
  `vid` mediumint(12) NOT NULL,
  `uid` mediumint(15) NOT NULL,
  `dateline` varchar(16) NOT NULL,
  `message` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `vid` (`vid`,`uid`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `hsk_vgallery_top5` (
  `id` mediumint(12) unsigned NOT NULL auto_increment,
  `vid` mediumint(12) NOT NULL,
  `uid` varchar(15) NOT NULL,
  `dateline` varchar(16) NOT NULL,
  `dps` mediumint(12) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `vid` (`vid`,`dps`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `hsk_vgallery_sort` (
  `sid` mediumint(12) unsigned NOT NULL auto_increment,
  `sup` mediumint(12) NOT NULL default '0',
  `sort` varchar(12) NOT NULL,
  `dps` mediumint(12) NOT NULL default '0',
  `indexcap` mediumint(1) NOT NULL,
  PRIMARY KEY  (`sid`),
  KEY `dps` (`dps`)
) ENGINE=MyISAM;



INSERT INTO `hsk_vgallery_sort` (`sid`, `sup`, `sort`, `dps`, `indexcap`) VALUES
(1, 0, '電影', 1, 0),
(2, 0, '電視劇', 2, 0),
(3, 0, '動漫', 3, 0),
(4, 0, '綜藝', 4, 0),
(5, 0, '體育', 5, 0),
(6, 0, '音樂', 6, 0),
(7, 0, '娛樂', 7, 0),
(8, 0, '其它', 8, 0),
(10, 1, '愛情', 1, 0),
(11, 1, '喜劇', 2, 0),
(12, 1, '動作', 3, 0),
(13, 1, '災難', 4, 0),
(14, 1, '恐怖', 5, 0),
(15, 1, '驚悚', 6, 0),
(16, 1, '科幻', 7, 0),
(17, 1, '武俠', 8, 0),
(18, 1, '槍戰', 9, 0),
(19, 1, '神話', 10, 0),
(20, 1, '藝術片', 11, 0),
(21, 1, '其它', 12, 0),
(22, 2, '言情', 0, 0),
(23, 2, '曆史', 0, 0),
(24, 2, '戰爭', 0, 0),
(25, 2, '諜戰', 0, 0),
(26, 2, '武俠', 0, 0),
(27, 2, '古裝', 0, 0),
(28, 2, '神話', 0, 0),
(29, 2, '都市', 0, 0),
(30, 2, '偶像', 0, 0),
(31, 2, '刑偵', 0, 0),
(32, 2, '懸疑', 0, 0),
(33, 2, '情景', 0, 0),
(34, 2, '倫理', 0, 0),
(35, 2, '喜劇', 0, 3),
(36, 2, '商戰', 0, 0),
(37, 2, '科幻', 0, 0),
(38, 2, '穿越', 0, 0),
(39, 2, '其它', 0, 0),
(40, 3, '動作', 0, 0),
(41, 3, '親子', 0, 0),
(42, 3, '熱血', 0, 0),
(43, 3, '冒險', 0, 0),
(44, 3, '未來', 0, 0),
(45, 3, '體育', 0, 0),
(46, 3, '搞笑', 0, 0),
(47, 3, '校園', 0, 0),
(48, 3, '魔幻', 0, 0),
(49, 3, '勵志', 0, 0),
(50, 3, '懸疑', 0, 0),
(51, 3, '寵物', 0, 0),
(52, 3, '益智', 0, 0),
(53, 3, '童話', 0, 0),
(54, 3, '神話', 0, 0),
(55, 3, '其它', 0, 0),
(56, 4, '訪談', 0, 0),
(57, 4, '相親', 0, 0),
(58, 4, '選秀', 0, 0),
(59, 4, '雜談', 0, 0),
(60, 4, '發布會', 0, 0),
(61, 4, '小品', 0, 0),
(62, 4, '相聲', 0, 0),
(63, 4, '單口', 0, 0),
(64, 4, '評書', 0, 0),
(65, 4, '其它', 0, 0),
(66, 5, '足球', 0, 0),
(67, 5, '籃球', 0, 0),
(68, 5, '綜合', 0, 0),
(69, 5, '體壇聚集', 0, 0),
(70, 6, '流行', 0, 0),
(71, 6, '搖滾', 0, 0),
(72, 6, '傷感', 0, 0),
(73, 6, '新歌', 0, 0),
(74, 6, '情歌', 0, 0),
(75, 6, '經典老歌', 0, 0),
(76, 6, '歐美', 0, 0),
(77, 6, '日韓', 0, 0),
(78, 6, '其它', 0, 0),
(79, 7, '搞笑', 0, 1),
(80, 7, 'MV', 0, 4),
(81, 7, '明星', 0, 0),
(82, 7, '時尚', 0, 0),
(83, 7, '八卦', 0, 0),
(84, 7, '活動', 0, 0),
(85, 8, '財經', 0, 0),
(86, 8, '記錄片', 0, 0),
(87, 8, '汽車', 0, 2);
EOF;


runquery($sql);

DB::query("INSERT INTO ".DB::table('forum_bbcode')." (available, tag, icon, replacement, example, explanation, params, prompt, nest, displayorder, perm) VALUES
		(2, 'qvod', '../../../source/plugin/vgallery/images/qvod.gif', '<script type=\"text/javascript\">var swf_width=\"{1}\";var swf_height=\"{2}\";var swf_url=\"{3}\";</script>\r\n<script src=\"/source/plugin/vgallery/include/flash_qvod.js\" type=\"text/javascript\"></script>', 'qvod=640,480]QVOD視頻地址[/gqplay]', '支持QVOD視頻地址在線播放', 3, 'QVOD視頻的寬和高	QVOD視頻的地址', 1, 24, '9	10	11	12	13	14	15	16	17	18	19	1	2	3	4	5	6	7	8')");
DB::query("INSERT INTO ".DB::table('forum_bbcode')." (available, tag, icon, replacement, example, explanation, params, prompt, nest, displayorder, perm) VALUES
		(2, 'gvod', '../../../source/plugin/vgallery/images/gvod.gif', '<script src=\"./source/plugin/vgallery/include/flash_gvod.js\" type=\"text/javascript\" type=\"text/javascript\" charset=\"gbk\"></script>\r\n<script>var player = gvod_player() ;player.width=\"{1}\";player.height=\"{2}\";\r\nplayer.play(\"{3}\");\r\n</script>', '[gvod=640,480]GVOD視頻地址[/gvod]', '支持GVOD視頻在線播放', 3, '', 1, 25, '9	10	11	12	13	14	15	16	17	18	19	1	2	3	4	5	6	7	8')");

$finish = TRUE;
?>