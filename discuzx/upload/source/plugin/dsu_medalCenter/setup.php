<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: setup.php 29 2011-01-15 13:35:57Z chuzhaowei@gmail.com $
*/
!defined('IN_DISCUZ') && exit('Access Denied');

$opt = strtolower(substr($operation,6));
if(!in_array($opt, array('install', 'uninstall', 'upgrade'))) cpmsg('BAD INPUT');

//load common lib
require_once DISCUZ_ROOT.'./source/plugin/dsu_medalCenter/include/install/FSO.class.php';
require_once DISCUZ_ROOT.'./source/plugin/dsu_medalCenter/include/function_common.php';

$stepMax = 3;
if(empty($_G['gp_step'])){
	require DISCUZ_ROOT.'./source/plugin/dsu_medalCenter/include/install/stat.inc.php';
	$step = 1;
}else{
	$step = max(intval($_G['gp_step']), 1);
}
$stepArr = array(
	1 => array('插件文件处理', $step == 1),
	2 => array('数据库升级', $step == 2),
	3 => array('数据处理', $step == 3),
);

if(1 <= $step && $step <= $stepMax ){
	showsubmenusteps('【DSU】勋章中心安装程序', $stepArr);
}

$nextstep = max(intval($_G['gp_nextstep']), $step);
if($nextstep == $step){
	$nextstep = $nextstep + 1;
	cpmsg('操作执行中，请稍后……',"action=plugins&operation=$operation&dir=dsu_medalCenter&step=$step&nextstep=$nextstep", 'loading');
}else{
	$nextstep = $step + 1;
}

if(1 <= $step && $step <= $stepMax ){
	require DISCUZ_ROOT.'./source/plugin/dsu_medalCenter/include/install/'.$opt.'.php';
	cpmsg('操作完成！进入下一步操作。',"action=plugins&operation=$operation&dir=dsu_medalCenter&step=$nextstep", 'succeed');
}else{
	$finish = TRUE;
}