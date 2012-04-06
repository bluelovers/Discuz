<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_custominfo.php 19640 2011-01-12 08:24:22Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_custominfo() {
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_setting')." WHERE skey IN ('extcredits', 'customauthorinfo', 'postno', 'postnocustom')");

	while($setting = DB::fetch($query)) {
		$data[$setting['skey']] = $setting['svalue'];
	}

	$data['customauthorinfo'] = unserialize($data['customauthorinfo']);
	$data['customauthorinfo'] = $data['customauthorinfo'][0];
	$data['fieldsadd'] = '';
	$data['extcredits'] = unserialize($data['extcredits']);
	$order = array();
	if($data['customauthorinfo']) {
		foreach($data['customauthorinfo'] as $k => $v) {
			if($v['left']) {
				$order['left'][$k] = $v['order'];
			}
			if($v['menu']) {
				$order['menu'][$k] = $v['order'];
			}
		}
		if(!empty($order['left'])) {
			asort($order['left']);
		}
		if(!empty($order['menu'])) {
			asort($order['menu']);
		}
	}
	$data['setting'] = $order;

	$profile = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_member_profile_setting')." WHERE available='1' AND showinthread='1' ORDER BY displayorder");
	while($field = DB::fetch($query)) {
		$data['fieldsadd'] .= ', mp.'.$field['fieldid'].' AS '.$field['fieldid'];
		$profile['field_'.$field['fieldid']] = array($field['title'], $field['fieldid']);
	}
	$data['profile'] = $profile;

	$postnocustomnew[0] = $data['postno'] != '' ? (preg_match("/^[\x01-\x7f]+$/", $data['postno']) ? '<sup>'.$data['postno'].'</sup>' : $data['postno']) : '<sup>#</sup>';
	$data['postnocustom'] = unserialize($data['postnocustom']);
	if(is_array($data['postnocustom'])) {
		foreach($data['postnocustom'] as $key => $value) {
			$value = trim($value);
			$postnocustomnew[$key + 1] = preg_match("/^[\x01-\x7f]+$/", $value) ? '<sup>'.$value.'</sup>' : $value;
		}
	}
	unset($data['customauthorinfo'], $data['postno'], $data['postnocustom'], $data['extcredits']);
	$data['postno'] = $postnocustomnew;

	save_syscache('custominfo', $data);
}

?>