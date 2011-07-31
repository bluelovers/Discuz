<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: script_amupper.php 66 2011-07-21 13:55:12Z chuzhaowei@gmail.com $
*/
class script_amupper{
	
	var $name = '打卡机扩展模块'; //扩展脚本名
	var $version = '1.0'; //扩展脚本版本号
	var $copyright = '<a href="www.dsu.cc">阿牧&nbsp;@&nbsp;DSU</a>';
	var $introduction = '添加领取勋章时所需连续或累计打卡签到次数的要求'; //在这儿你可以填写对这个扩展的介绍
	
	
	
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
		global $_G, $lang;
		$var = array();
		$var['value'] = $setting['ppercon'];
		$var['type'] = '<input name="ppercon" value="'.$var['value'].'" type="number" class="txt">';
		
		showsetting('打卡机-连续签到次数', '', '', $var['type'], '', '', '允许连续签到到达设置的用户领取勋章。');

		$var2 = array();
		$var2['value'] = $setting['pperaddup'];
		$var2['type'] = '<input name="pperaddup" value="'.$var2['value'].'" type="number" class="txt">';
		
		showsetting('打卡机-累计签到次数', '', '', $var2['type'], '', '', '允许累计签到到达设置的用户领取勋章。');
	}
	
	/**
	 * 在数据提交后对数据进行合法性检验
	 */
	function admincp_check(){
		global $_G, $medalid;
		$ppercon = is_numeric($_G['gp_ppercon']);
		if($ppercon || empty($ppercon)){}else{cpmsg('打卡机-连续签到次数设置错误！', 'action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_manage&pdo=edit&medalid='.$medalid ,'error');}
		
		$pperaddup = is_numeric($_G['gp_pperaddup']);
		if($pperaddup || empty($pperaddup)){}else{cpmsg('打卡机-累计签到次数设置错误！', 'action=plugins&operation=config&identifier=dsu_medalCenter&pmod=admin_manage&pdo=edit&medalid='.$medalid ,'error');}			
	}
	
	/**
	 * @return <array>返回要保存的内容
	 */
	function admincp_save(){
		global $_G;
		return array('ppercon' => $_G['gp_ppercon'],'pperaddup' => $_G['gp_pperaddup']);
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
		global $_G;
		$return = '';
		if($setting['ppercon'] && $setting['pperaddup']){
			$_check = $this->_memcp_check($setting);
			$return .= ($_check == 1 || $_check == 3  ? '' : '<font color="red">');
			$return .= '<strong>连续打卡签到大于等于：</strong>';
			$return .= ($_check == 1 || $_check == 3  ? '' : '</font>');
			$return .= $setting['ppercon'].'次';

			$return .= ($_check == 2 || $_check == 3  ? '' : '<font color="red">');
			$return .= '<BR><strong>累计打卡签到大于等于：</strong>';
			$return .= ($_check == 2 || $_check == 3  ? '' : '</font>');
			$return .= $setting['pperaddup'].'次';
		}
		return $return;
	}
	
	/**
	 * 检验用户是否满足领取要求
	 * @param <array> $setting 传入admincp_save方法中保存的信息
	 * @return <bool>返回检验是否通过
	 */
	function memcp_check($setting){
		global $_G;
		$_check = $this->_memcp_check($setting);
		$return = ($_check == 3 ? TRUE : FALSE);
		return $return;
	}

	function _memcp_check($setting){
		global $_G;
		$return = 0;
		$cdb_pper['uid'] = intval($_G['uid']);
		$query = DB::fetch_first("SELECT * FROM ".DB::table("plugin_dsuampper")." WHERE uid = '{$cdb_pper['uid']}'");
		if(empty($setting['ppercon']) || $query['continuous'] >= $setting['ppercon']){$return = $return + 1;}
		if(empty($setting['pperaddup']) || $query['addup'] >= $setting['pperaddup']){$return = $return + 2;}
		return $return;
	}

	function memcp_get_succeed($setting){
		return;
	}
	
	/**
	 * 在扩展脚本安装时会自动调用此方法。
	 */
	function install(){
		if(!dsuMedal_pluginExists('dsu_amupper'))
			return array(FALSE, '请安装打卡机后再安装此模块！');
	}
	
	/**
	 * 在扩展脚本升级时会自动调用此方法。
	 */
	function upgrade(){}
	
	/**
	 * 在扩展脚本卸载时会自动调用此方法。
	 */
	function uninstall(){}
}
?>