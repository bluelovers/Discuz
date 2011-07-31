<?php
/*
	dsu_medalCenter (C)2010 Discuz Student Union
	This is NOT a freeware, use is subject to license terms

	$Id: script_market.php 64 2011-07-20 15:56:17Z chuzhaowei@gmail.com $
*/
class script_market{

	var $name = '积分购买模块';
	var $version = '1.1';
	var $copyright = '<a href="www.dsu.cc">DSU Team</a>';
	var $introduction = '';
	
	var $setting = array();
	
	function admincp_show($setting){
		global $_G;
		$creditset = $setting['MarketCredit'];
		showtableheader('积分价格', 'notop', 'id="creditbody"');
		include template('dsu_medalCenter:admin_extcredit');
		showtablefooter();
	}
	
	function admincp_check(){
		global $_G;
		$creditArr = array();
		if($_G['gp_typenew'] == 5){
			foreach($_G['gp_newcredit'] as $creditid => $value){
				$creditArr[$creditid] = intval($value);
			}
		}
		$_G['gp_newcredit'] = $creditArr;
	}
	
	function admincp_save(){
		global $_G;
		return array('MarketCredit' => $_G['gp_newcredit']);
	}
	
	function memcp_check($setting){
		if($setting['MarketCredit']){
			$return = self::_checkCredit($setting['MarketCredit']);
			if(!$return)
				return '对不起，由于您积分不足，购买失败！请返回。';
		}
		return true;
	}
	
	function memcp_get_succeed($setting){
		global $_G;
		if($setting['MarketCredit']){
			$creditArr = $setting['MarketCredit'];
			foreach($creditArr as $id => &$value){
				$value *= -1;
			}
			updatemembercount($_G['uid'], $creditArr);
		}
	}
	
	function memcp_show($setting){
		global $_G;
		$this->setting = $setting;
		$return = '';
		if($setting['MarketCredit']){
			$return .= '<strong>勋章价格：</strong><br />';
			$common = '';
			foreach($setting['MarketCredit'] as $creditid => $value){
				$return .= $common;
				$return .= $value > 0 ? $_G['setting'][extcredits][$creditid]['title'].':'.$value : '';
				$common = '<br />';
			}
		}
		return $return;
	}
	
	function _checkCredit($creditid, $value=0){
		global $_G;
		if(is_array($creditid)){
			foreach($creditid as $id=>$value){
				if(!self::_checkCredit($id, $value)) return false;
			}
			return true;
		}else{
			unset($_G['member']['extcredits'.$creditid]);
			getuserprofile('extcredits'.$creditid);
			return $value ==0 || $_G['member']['extcredits'.$creditid] >= $value;
		}
	}
	
	function uninstall(){
		return array(false, '系统模块，禁止卸载！');
	}
}
?>