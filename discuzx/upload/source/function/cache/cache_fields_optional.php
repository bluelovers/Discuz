<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_fields_optional.php 16693 2010-09-13 04:31:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_fields_optional() {
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_member_profile_setting')." WHERE available='1' AND required='0' ORDER BY displayorder");

	while($field = DB::fetch($query)) {
		$choices = array();
		if($field['selective']) {
			foreach(explode("\n", $field['choices']) as $item) {
				list($index, $choice) = explode('=', $item);
				$choices[trim($index)] = trim($choice);
			}
			$field['choices'] = $choices;
		} else {
			unset($field['choices']);
		}
		$data['field_'.$field['fieldid']] = $field;
	}

	save_syscache('fields_optional', $data);
}

?>