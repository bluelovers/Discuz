<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: admin_extend.inc.php 60 2011-07-20 13:04:22Z chuzhaowei@gmail.com $
*/
(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) && exit('Access Denied');

require_once DISCUZ_ROOT.'./source/plugin/dsu_medalCenter/include/function_common.php';

$modlist = dsuMedal_getOption('modlist');
$sysmod = array('script_market');

if(in_array($_G['gp_pdo'], array('install', 'upgrade', 'uninstall'))){ //脚本操作
	$classname = $_G['gp_classname'];
	if(!preg_match("/^[a-zA-Z0-9_]+$/", $classname) || !file_exists(DISCUZ_ROOT.'./source/plugin/dsu_medalCenter/include/script/'.$classname.'.php')){
		cpmsg("BAD INPUT", '', 'error');
	//}else if($_G['gp_pdo'] == 'uninstall' && in_array($classname, $sysmod)){
	//	cpmsg('系统模块，禁止操作！', '', 'error');
	}else{
		@include DISCUZ_ROOT.'./source/plugin/dsu_medalCenter/include/script/'.$classname.'.php';
		if(class_exists($classname)){
			$newclass = new $classname;
		}else{
			cpmsg('扩展文件已经损坏！', '', 'error');
		}
	}
	$return = TRUE;
	switch($_G['gp_pdo']){
		case 'install':
			if(method_exists($newclass, 'install')) $return = $newclass->install();
			$modlist[$classname] = $newclass->version;
			$msg = '指定扩展安装成功！';
			break;
		case 'uninstall':
			unset($modlist[$classname]);
			if(method_exists($newclass, 'uninstall')) $return = $newclass->uninstall();
			$msg = '指定扩展卸载成功！';
			break;
		case 'upgrade':
			if(method_exists($newclass, 'upgrade')) $return = $newclass->upgrade($modlist[$classname]);
			$modlist[$classname] = $newclass->version;
			$msg = '指定扩展升级成功！';
			break;
	}
	if(is_array($return)) list($return, $msg2) = $return;
	$msg = $msg2 ? $msg2 : ($return === FALSE ? '操作失败！' : $msg);
	if($return === FALSE) {
		cpmsg($msg, 'action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_extend', 'error');
	}else{
		dsuMedal_saveOption('modlist', $modlist);
		cpmsg($msg, 'action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_extend', 'succeed');
	}
}else{
	showtips('<li>安装新的扩展，需将扩展脚本程序上传到 source/plugin/dsu_medalCenter/include/script/ 目录，然后即可在以下列表中安装并使用了</li><li>积分购买模块为勋章中心运行必要模块，无法移除</li>');
	showtableheader('');
	showsubtitle(array('名称', '版本号', '版权信息', ''));
	$dir = dir(DISCUZ_ROOT.'./source/plugin/dsu_medalCenter/include/script/');
	while (false !== ($entry = $dir->read())) {
		if(substr($entry, 0, 7) != 'script_' || substr($entry, -4) != '.php') continue;
		include DISCUZ_ROOT.'./source/plugin/dsu_medalCenter/include/script/'.$entry;
		$classname = substr($entry, 0, -4);
		if(class_exists($classname)){
			$newclass = new $classname;
			if(empty($newclass->name)) continue;
			$adminaction = $namemsg = $versionmsg = '';
			$namemsg = $newclass->name;
			$versionmsg = $newclass->version;
			$introduction = empty($newclass->introduction) ? $newclass->name : $newclass->introduction;
			if(isset($modlist[$classname])){ //检查是否已经安装
				$namemsg = "<strong>$newclass->name</strong>";
				$adminaction .= "<a href=\"".ADMINSCRIPT."?action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_extend&pdo=uninstall&classname=$classname\" class=\"act\">卸载</a>";
				if($modlist[$classname] < $newclass->version){ //是否需要升级
					$adminaction .= "<a href=\"".ADMINSCRIPT."?action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_extend&pdo=upgrade&classname=$classname\" class=\"act\">升级</a>" ;
					$versionmsg .= '(当前安装版本：'.$modlist[$classname].')';
				}
			}else{
				$adminaction .= "<a href=\"".ADMINSCRIPT."?action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_extend&pdo=install&classname=$classname\" class=\"act\">安装</a>";
			}
			$namemsg = '<span title="'.$introduction.'">'.$namemsg.'</span>';
			showtablerow('', array('class="td25"', 'class="td25"', 'class="td25"', 'class="td25"'), array(
					$namemsg,
					$versionmsg,
					$newclass->copyright,
					$adminaction
				));
			
		}
	}
	$dir->close();
	showtablefooter();
}
?>