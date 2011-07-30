<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


$sql = <<<EOF
ALTER TABLE `hsk_vgallerys` ADD `tid` MEDIUMINT( 15 ) NOT NULL DEFAULT '0' AFTER `sid` ;
ALTER TABLE `hsk_vgallerys` ADD `pid` INT( 18 ) NOT NULL DEFAULT '0' AFTER `tid` ;
ALTER TABLE `hsk_vgallerys` ADD `vprice` MEDIUMINT( 12 ) NOT NULL DEFAULT '0' AFTER `pid` ;
EOF;

runquery($sql);


DB::query("INSERT INTO ".DB::table('forum_bbcode')." (available, tag, icon, replacement, example, explanation, params, prompt, nest, displayorder, perm) VALUES
		(2, 'qvod', '../../../source/plugin/vgallery/images/qvod.gif', '<script type=\"text/javascript\">var swf_width=\"{1}\";var swf_height=\"{2}\";var swf_url=\"{3}\";</script>\r\n<script src=\"/source/plugin/vgallery/include/flash_qvod.js\" type=\"text/javascript\"></script>', 'qvod=640,480]QVOD視頻地址[/gqplay]', '支持QVOD視頻地址在線播放', 3, 'QVOD視頻的寬和高	QVOD視頻的地址', 1, 24, '9	10	11	12	13	14	15	16	17	18	19	1	2	3	4	5	6	7	8')");
DB::query("INSERT INTO ".DB::table('forum_bbcode')." (available, tag, icon, replacement, example, explanation, params, prompt, nest, displayorder, perm) VALUES
		(2, 'gvod', '../../../source/plugin/vgallery/images/gvod.gif', '<script src=\"./source/plugin/vgallery/include/flash_gvod.js\" type=\"text/javascript\" type=\"text/javascript\" charset=\"gbk\"></script>\r\n<script>var player = gvod_player() ;player.width=\"{1}\";player.height=\"{2}\";\r\nplayer.play(\"{3}\");\r\n</script>', '[gvod=640,480]GVOD視頻地址[/gvod]', '支持GVOD視頻在線播放', 3, '', 1, 25, '9	10	11	12	13	14	15	16	17	18	19	1	2	3	4	5	6	7	8')");

$finish = TRUE;