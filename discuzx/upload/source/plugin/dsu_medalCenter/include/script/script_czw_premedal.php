<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: script_czw_premedal.php 37 2011-01-24 09:24:53Z chuzhaowei@gmail.com $
*/
class script_czw_premedal{
	
	var $name = '领取限制模块 For 已有勋章'; //扩展脚本名
	var $version = '1.0'; //扩展脚本版本号
	var $copyright = '<a href="www.jhdxr.com">江湖大虾仁@DSU</a>';
	var $introduction = '可以限制领取某个勋章前必须拥有另一个勋章';
	
	private $setting = array();
	
	function admincp_show_simple($setting){
		global $medalid;
		$setting['preMedalid'] = intval($setting['preMedalid']);
		$varname = array('preMedalid', array());
		$varname[1][] = array(0, '无限制');
		$query = DB::query("SELECT medalid, name FROM ".DB::table('forum_medal')." ORDER BY displayorder");
		while($medal = DB::fetch($query)) {
			if($medalid != $medal['medalid']) $varname[1][] = array($medal['medalid'], $medal['name']);
		}
		showsetting('拥有勋章', $varname, $setting['preMedalid'], 'select', '', '', '在领取此勋章前需要拥有的勋章');
	}

	function admincp_check(){
	}
	
	function admincp_save(){
		global $_G;
		return array(
			'preMedalid' => $_G['gp_preMedalid'],
		);
	}
	
	function memcp_show($setting){
		$return = '';
		$this->setting = $setting;
		if($setting['preMedalid']){
			$medal = DB::fetch_first("SELECT name FROM ".DB::table('forum_medal')." WHERE medalid='$setting[preMedalid]'");
			$_check = $this->_memcpCheck();
			$return .= '<font color="'.($_check ? 'green' : 'red').'">';
			$return .= '<strong>拥有勋章：</strong><br />';
			$return .= $medal['name'];
			$return .= '</font>';
		}
		return $return;
	}
	
	function memcp_check($setting){
		$this->setting = $setting;
		return $this->_memcpCheck();
	}
	
	private function _memcpCheck(){
		$setting = $this->setting;
		if($setting['preMedalid'] && !in_array($setting['preMedalid'], getMedalByUid())) return false;
		return true;
	}

}
?>