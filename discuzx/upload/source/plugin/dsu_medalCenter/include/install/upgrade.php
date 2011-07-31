<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: upgrade.php 29 2011-01-15 13:35:57Z chuzhaowei@gmail.com $
*/

$fileList = array(
);

$_sql = <<<EOT
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

	cpmsg($setpArr[$step][0].'完成！进入下一步操作。','action=plugins&operation=plugininstall&dir=dsu_medalCenter&step='.$nextstep, 'succeed');
}