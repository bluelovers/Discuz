<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: script_example.php 60 2011-07-20 13:04:22Z chuzhaowei@gmail.com $
*/
class script_example{
	
	var $name = '范例程序'; //扩展脚本名
	var $version = '1.0'; //扩展脚本版本号
	var $copyright = '<a href="www.dsu.cc">DSU Team</a>';
	var $introduction = '这只是一个供开发者参考的范例脚本，无实际意义，请勿安装于正式站点。'; //在这儿你可以填写对这个扩展的介绍
	
	
	
	/**
	 * 设置时显示的内容（直接在函数中输出即可）
	 * 此函数后于 admincp_show_simple 调用，一般用于显示需采用独立table的设置项
	 * @param <array> $setting 传入admincp_save方法中保存的信息
	 */
	function admincp_show($setting){
		return '';
	}
	
	/**
	 * 设置时显示的内容（直接在函数中输出即可）
	 * 此函数先于 admincp_show 调用，一般用于显示无需采用独立table的设置项
	 * @param <array> $setting 传入admincp_save方法中保存的信息
	 */
	function admincp_show_simple($setting){
		return '';
	}
	
	/**
	 * 在数据提交后对数据进行合法性检验
	 */
	function admincp_check(){
		return true;
	}
	
	/**
	 * @return <array>返回要保存的内容
	 */
	function admincp_save(){
		return array();
	}
	
	/**
	 * 前台勋章列表时显示的设置要求
	 * 建议采用如下设置格式：
	 * 		条件标题：粗体
	 * 		条件内容：如果满足的条件显示为绿色，如果不满足显示为红色
	 * @param <array> $setting 传入admincp_save方法中保存的信息
	 * @return <string>返回要显示的内容
	 */
	function memcp_show($setting){
		return '';
	}
	
	/**
	 * 检验用户是否满足领取要求
	 * @param <array> $setting 传入admincp_save方法中保存的信息
	 * @return <mixed>返回检验是否通过
	 *	返回TRUE代表检验成功
	 *	返回FALSE代表检验失败（默认，当返回值非TRUE时视为检验失败）
	 *	若要同时自定义安装后显示的信息，请返回一个数组，格式为array(安装是否成功, 提示信息)。 e.g. return array(false, '请先安装XX插件后再安装本扩展');
	 */
	function memcp_check($setting){
		return true;
	}
	
	/**
	 * 在用户领取成功（前台勋章中心）后，会自动调用此方法
	 * @param <array> $setting 传入admincp_save方法中保存的信息
	 */
	function memcp_get_succeed($setting){
		return;
	}
	
	/**
	 * 在扩展脚本安装时会自动调用此方法。
	 * @return <mixed>
	 *	返回FALSE代表安装失败
	 *	返回TRUE代表安装成功（默认，当返回值非布尔型或数组时，或无返回值时视为安装成功）
	 *	若要同时自定义安装后显示的信息，请返回一个数组，格式为array(安装是否成功, 提示信息)。 e.g. return array(false, '请先安装XX插件后再安装本扩展');
	 *	注意：考虑到后续扩展性，返回值请设定为TRUE/FALSE/NULL(如无返回值推荐不要实现此方法)/数组
	 */
	function install(){
		return array(false, '示例程序仅供开发者参考，请勿用于生产环境！');
	}
	
	/**
	 * 在扩展脚本升级时会自动调用此方法。
	 * @param <string> $nowVer 当前安装的版本号
	 * @return <mixed>
	 *	返回FALSE代表升级失败
	 *	返回TRUE代表升级成功（默认，当返回值非布尔型或数组时，或无返回值时视为升级成功）
	 *	若要同时自定义升级后显示的信息，请返回一个数组，格式为array(升级是否成功, 提示信息)。 e.g. return array(false, '扩展与配套插件版本不匹配，请先更新插件');
	 *	注意：考虑到后续扩展性，返回值请设定为TRUE/FALSE/NULL(如无返回值推荐不要实现此方法)/数组
	 */
	function upgrade($nowVer){
		return array(false, '示例程序仅供开发者参考，请勿用于生产环境！');
	}
	
	/**
	 * 在扩展脚本卸载时会自动调用此方法。
	 * @return <mixed>
	 *	返回FALSE代表卸载失败
	 *	返回TRUE代表卸载成功（默认，当返回值非布尔型或数组时，或无返回值时视为卸载成功）
	 *	若要同时自定义卸载后显示的信息，请返回一个数组，格式为array(卸载是否成功, 提示信息)。 e.g. return array(false, '系统模块禁止卸载');
	 *	注意：考虑到后续扩展性，返回值请设定为TRUE/FALSE/NULL(如无返回值推荐不要实现此方法)/数组
	 */
	function uninstall(){
		return array(false, '示例程序仅供开发者参考，请勿用于生产环境！');
	}
}
?>