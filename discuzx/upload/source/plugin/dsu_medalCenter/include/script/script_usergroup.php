<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: script_usergroup.php 60 2011-07-20 13:04:22Z chuzhaowei@gmail.com $
*/
class script_usergroup {
	
	var $name = '用户组限制模块';
	var $version = '1.1';
	var $copyright = '<a href="www.jhdxr.com">江湖大虾仁@DSU</a>';
	
	function admincp_show_simple($setting){
		global $_G, $lang, $medal;
		$medal['permission'] = is_array($medal['permission']) ? $medal['permission'] : unserialize($medal['permission']);
		$medal['usergroups'] = (array)$medal['permission']['usergroups'];
		$var = array();
		$var['value'] = $medal['usergroups'] ? $medal['usergroups'] : $setting['usergroup'];
		$query = DB::query("SELECT type, groupid, grouptitle, radminid FROM ".DB::table('common_usergroup')." ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower, groupid");
		$groupselect = array();
		while($group = DB::fetch($query)) {
			$group['type'] = $group['type'] == 'special' && $group['radminid'] ? 'specialadmin' : $group['type'];
			$groupselect[$group['type']] .= '<option value="'.$group['groupid'].'"'.(@in_array($group['groupid'], $var['value']) ? ' selected' : '').'>'.$group['grouptitle'].'</option>';
		}
		$var['type'] = '<select name="usergroup[]" size="10" multiple="multiple"><option value=""'.(@in_array('', $var['value']) ? ' selected' : '').'>'.cplang('plugins_empty').'</option>';
		$var['type'] .= '<optgroup label="'.$lang['usergroups_member'].'">'.$groupselect['member'].'</optgroup>'.
			($groupselect['special'] ? '<optgroup label="'.$lang['usergroups_special'].'">'.$groupselect['special'].'</optgroup>' : '').
			($groupselect['specialadmin'] ? '<optgroup label="'.$lang['usergroups_specialadmin'].'">'.$groupselect['specialadmin'].'</optgroup>' : '').
			'<optgroup label="'.$lang['usergroups_system'].'">'.$groupselect['system'].'</optgroup></select>';
		
		showsetting('用户组', '', '', $var['type'], '', '', '允许领取勋章的用户组，留空代表不限制');
	}
	
	function admincp_check(){
		global $_G, $formulapermary;
		$usergroup = &$_G['gp_usergroup'];
		if(in_array('', $usergroup)) $usergroup = array();
		
		//$formulapermary['usergroupallow'] = empty($usergroup);
		$formulapermary['usergroups'] = (array)$_G['gp_usergroup'];
	}
	
	function admincp_save(){
		global $_G;
		return array('usergroup' => $_G['gp_usergroup']);
	}
	
	function memcp_show($setting){
		global $_G;
		$return = '';
		if( !empty($setting['usergroup'][0])){
			$_check = $this->_memcp_check($setting);
			$return .= ($_check ? '' : '<font color="red">');
			$return .= '<strong>用户组为下列用户组之一：</strong><br />';
			$return .= ($_check ? '' : '</font>');
			loadcache('usergroups');
			$common = '';
			foreach($setting['usergroup'] as $gid){
				$return .= $common;
				$return .= $_G['cache']['usergroups'][$gid]['grouptitle'];
				$common = ',';
			}
		}
		return $return;
	}
	
	function memcp_check($setting){
		return $this->_memcp_check($setting);
	}
	
	function _memcp_check($setting){
		global $_G;
		return empty($setting['usergroup']) || in_array($_G['groupid'], $setting['usergroup']);
	}
}
?>