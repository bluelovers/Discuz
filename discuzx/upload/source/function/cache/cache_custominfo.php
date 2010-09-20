<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_custominfo.php 16714 2010-09-13 07:39:12Z monkey $
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
	$data['extcredits'] = unserialize($data['extcredits']);
	$order = array();
	if($data['customauthorinfo']) {
		foreach($data['customauthorinfo'] as $k => $v) {
			$order[$k] = $v['order'];
		}
		asort($order);
	}

	$authorinfoitems = array(
		'uid' => '$post[uid]',
		'posts' => '$post[posts]',
		'threads' => '$post[threads]',
		'doings' => '$post[doings]',
		'blogs' => '$post[blogs]',
		'albums' => '$post[albums]',
		'sharings' => '$post[sharings]',
		'friends' => '$post[friends]',
		'digest' => '$post[digestposts]',
		'credits' => '$post[credits]',
		'readperm' => '$post[readaccess]',
		'regtime' => '$post[regdate]',
		'lastdate' => '$post[lastdate]',
		'oltime' => '$post[oltime]'.lang('admincp', 'hourtime')
	);

	if(!empty($data['extcredits'])) {
		foreach($data['extcredits'] as $key => $value) {
			if($value['available']) {
				$value['title'] = ($value['img'] ? '<img style="vertical-align:middle" src="'.$value['img'].'" /> ' : '').$value['title'];
				$authorinfoitems['extcredits'.$key] = array($value['title'], '$post[extcredits'.$key.'] {$_G[setting][extcredits]['.$key.'][unit]}');
			}
		}
	}

	$data['fieldsadd'] = '';$data['profilefields'] = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_member_profile_setting')." WHERE available='1' AND showinthread='1' ORDER BY displayorder");
	while($field = DB::fetch($query)) {
		$data['fieldsadd'] .= ', mp.'.$field['fieldid'].' AS field_'.$field['fieldid'];
		$authorinfoitems['field_'.$field['fieldid']] = array($field['title'], '$post[field_'.$field['fieldid'].']');
	}

	$customauthorinfo = array();
	if(is_array($data['customauthorinfo'])) {
		foreach($data['customauthorinfo'] as $key => $value) {
			if(array_key_exists($key, $authorinfoitems)) {
				if(substr($key, 0, 10) == 'extcredits') {
					$v = addcslashes('<dt>'.$authorinfoitems[$key][0].'</dt><dd>'.$authorinfoitems[$key][1].'&nbsp;</dd>', '"');
				} elseif($key == 'field_gender') {
					$v = '".('.$authorinfoitems['field_gender'][1].' == 1 ? "'.addcslashes('<dt>'.$authorinfoitems['field_gender'][0].'</dt><dd>'.lang('admincp', 'setting_styles_viewthread_userinfo_gender_male').'&nbsp;</dd>', '"').'" : ('.$authorinfoitems['field_gender'][1].' == 2 ? "'.addcslashes('<dt>'.$authorinfoitems['field_gender'][0].'</dt><dd>'.lang('admincp', 'setting_styles_viewthread_userinfo_gender_female').'&nbsp;</dd>', '"').'" : ""))."';
				} elseif(substr($key, 0, 6) == 'field_') {
					$v = addcslashes('<dt>'.$authorinfoitems[$key][0].'</dt><dd>'.$authorinfoitems[$key][1].'&nbsp;</dd>', '"');
				} else {
					$v = addcslashes('<dt>'.lang('admincp', 'setting_styles_viewthread_userinfo_'.$key).'</dt><dd>'.$authorinfoitems[$key].'&nbsp;</dd>', '"');
				}
				if(isset($value['left'])) {
					$customauthorinfo[1][$key] = $v;
				}
				if(isset($value['menu'])) {
					$customauthorinfo[2][$key] = $v;
				}
			}
		}
	}

	$customauthorinfonew = array();
	foreach($order as $k => $v) {
		$customauthorinfonew[1][] = $customauthorinfo[1][$k];
		$customauthorinfonew[2][] = $customauthorinfo[2][$k];
	}

	$customauthorinfo[1] = @implode('', $customauthorinfonew[1]);
	$customauthorinfo[2] = @implode('', $customauthorinfonew[2]);
	$data['customauthorinfo'] = $customauthorinfo;

	$postnocustomnew[0] = $data['postno'] != '' ? (preg_match("/^[\x01-\x7f]+$/", $data['postno']) ? '<sup>'.$data['postno'].'</sup>' : $data['postno']) : '<sup>#</sup>';
	$data['postnocustom'] = unserialize($data['postnocustom']);
	if(is_array($data['postnocustom'])) {
		foreach($data['postnocustom'] as $key => $value) {
			$value = trim($value);
			$postnocustomnew[$key + 1] = preg_match("/^[\x01-\x7f]+$/", $value) ? '<sup>'.$value.'</sup>' : $value;
		}
	}
	unset($data['postno'], $data['postnocustom'], $data['extcredits']);
	$data['postno'] = $postnocustomnew;

	save_syscache('custominfo', $data);
}

?>