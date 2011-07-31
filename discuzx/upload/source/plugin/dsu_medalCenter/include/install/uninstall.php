<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: uninstall.php 61 2011-07-20 13:08:54Z chuzhaowei@gmail.com $
*/

$filename = array(
	'data/plugin/dsu_medalCenter',
	//'source/function/cache/cache_dsuMedalCenter.php',
);
$_sql = <<<EOT
DROP TABLE IF EXISTS `pre_dsu_medaltype`;
DROP TABLE IF EXISTS `pre_dsu_medalfield`;
EOT;

if($step == 1){
	foreach($fileList as $filename){
		@FSO::unlink($filename);
	}
	cpmsg($setpArr[$step][0].'完成！进入下一步操作。','action=plugins&operation=pluginuninstall&dir=dsu_medalCenter&step='.$nextstep, 'succeed');
}elseif($step == 2){
	runquery($_sql);
	cpmsg($setpArr[$step][0].'完成！进入下一步操作。','action=plugins&operation=pluginuninstall&dir=dsu_medalCenter&step='.$nextstep, 'succeed');
}