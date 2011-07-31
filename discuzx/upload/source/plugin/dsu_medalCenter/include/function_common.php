<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: function_common.php 66 2011-07-21 13:55:12Z chuzhaowei@gmail.com $
*/
!defined('IN_DISCUZ') && exit('Access Denied');

/**
 * 根据用户UID获取用户勋章信息
 * @param <int> $uid 用户UID
 * @param <bool> $expiration 返回结果中是否包含勋章的过期时间，默认为false
 * @return <array>用户的勋章信息（当$expiration为TRUE时为array('勋章1'=>'过期时间1')，否则为array(勋章1，勋章2...)）
 */
function getMedalByUid($uid = '', $expiration = false){
	global $_G;
	static $usermedalArr = array();
	$uid = empty($uid) ? $_G['uid'] : $uid;
	if(empty($usermedalArr[$uid])) {
		$usermedal = DB::result_first("SELECT medals FROM ".DB::table('common_member_field_forum')." WHERE uid='$_G[uid]'");
		$medalArr = $usermedal ? explode("\t", $usermedal) : array();
		$medalArr2 = array();
		foreach($medalArr as $medal){
			list($_medalid, $_expiration) = explode('|', $medal);
			$medalArr2[$_medalid] = intval($_expiration);
		}
		$usermedalArr[$uid] = $medalArr2;
	}
	return $expiration ? $usermedalArr[$uid] : array_keys($usermedalArr[$uid]);
}

/**
 * 用于保存一些设置项
 * @param <string> $name 要保存的设置项的名称
 * @param <mixed> $data 要保存的设置项的值
 * @param <string> $script 进行保存操作的来源，此项是为了防止在有相同保存项名称的时候造成的冲突，默认为空。
 */
function dsuMedal_saveOption($name, $data, $script = ''){
	$name = 'dsuMedal'.substr(md5($script.$name), 8, 16);
	save_syscache($name, $data);
}

/**
 * 获取保存的设置项
 * @param <string> $name 保存的设置项的名称
 * @param <string> $script 操作来源，此项是为了防止在有相同保存项名称的时候造成的冲突，默认为空。
 * @return <mixed> 保存的设置项的值
 */
function dsuMedal_getOption($name, $script = ''){
	$name = 'dsuMedal'.substr(md5($script.$name), 8, 16);
	$option = DB::fetch_first("SELECT /*!40001 SQL_CACHE */ * FROM ".DB::table('common_syscache')." WHERE cname='$name'");
	if(empty($option)){
		return NULL;
	}else{
		if($option['ctype']) {
			$option['data'] = unserialize($option['data']);
		}
		return $option['data'];
	}
}

/**
 * 获取开启的扩展脚本类
 * @return <array>开启的扩展脚本类，格式为array(类名=>对象)
 */
function getMedalExtendClass(){
	global $_G;
	static $classes = array();
	if(empty($classes)){
		$modlist = dsuMedal_getOption('modlist');

		$modlist = array_keys($modlist);
		foreach($modlist as $classname){
	   		include_once DISCUZ_ROOT.'./source/plugin/dsu_medalCenter/include/script/'.$classname.'.php';
	   		if(class_exists($classname)){
	   			$newclass = new $classname;
	   			$classes[$classname] = $newclass;
	   		}
		}
	}
	return $classes;
}

/**
 * 获取勋章中心插件设置项并进行解析
 * @return <array>解析后的勋章中心插件设置项
 */
function dsuMedal_phraseConfig(){
	global $_G;
	loadcache('plugin');
	$cvars = &$_G['cache']['plugin']['dsu_medalCenter'];
	$cvars['showMedalLimit'] = (array) unserialize($cvars['showMedalLimit']);
	return $cvars;
}

/**
 * 检查指定插件是否已经安装
 * @param <string> 插件的identifier
 * @return <bool>指定插件是否已经安装
 */
function dsuMedal_pluginExists($identifier){
	$identifier = addslashes($identifier);
	$plugin = DB::fetch_first("SELECT * FROM ".DB::table('common_plugin')." WHERE identifier = '$identifier'");
	return !empty($plugin);
}
?>