<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: install.php 41 2011-01-31 16:03:19Z chuzhaowei@gmail.com $
*/
//需要复制的文件/文件夹(源地址留空将在目标位置建立一个空文件夹)
$fileList = array(
	array('', 'data/plugin/dsu_medalCenter'),
	//array('source/plugin/dsu_medalCenter/include/install/files/cache_dsuMedalCenter.php', 'source/function/cache/cache_dsuMedalCenter.php'),
);
//数据库升级语句
$_sql = <<<EOT
DROP TABLE IF EXISTS `pre_dsu_medaltype`;
CREATE TABLE `pre_dsu_medaltype` (
  `typeid` smallint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `displayorder` smallint(3) unsigned zerofill NOT NULL,
  PRIMARY KEY (`typeid`)
) ENGINE=MyISAM;
DROP TABLE IF EXISTS `pre_dsu_medalfield`;
CREATE TABLE `pre_dsu_medalfield` (
  `medalid` smallint(6) unsigned NOT NULL,
  `typeid` smallint(3) unsigned NOT NULL,
  `gettype` smallint(1) unsigned NOT NULL DEFAULT '1',
  `script` text,
  `setting` text,
  PRIMARY KEY (`medalid`),
  KEY `typeid` (`typeid`)
) ENGINE=MyISAM;
EOT;

if($step == 1){
	foreach($fileList as $filename){
		if($filename[0] == ''){
			@dmkdir(DISCUZ_ROOT.'./'.$filename[1]);
		}else{
			@copy(DISCUZ_ROOT.'./'.$filename[0], DISCUZ_ROOT.'./'.$filename[1]);
		}
	}
	cpmsg('文件处理完成！进入下一步操作。','action=plugins&operation=plugininstall&dir=dsu_medalCenter&step='.$nextstep, 'succeed');
}elseif($step == 2){
	runquery($_sql);
	cpmsg('数据库升级完成！进入下一步操作。','action=plugins&operation=plugininstall&dir=dsu_medalCenter&step='.$nextstep, 'succeed');
}elseif($step == 3){
	require_once DISCUZ_ROOT.'./source/plugin/dsu_medalCenter/include/function_common.php';
	$modlist = array('script_market' => '1.0', 'script_usergroup' => '1.0');
	dsuMedal_saveOption('modlist', $modlist);
	cpmsg('载入默认设置完成！进入下一步操作。','action=plugins&operation=plugininstall&dir=dsu_medalCenter&step='.$nextstep, 'succeed');
}