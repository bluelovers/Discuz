<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_fields_required.php 16693 2010-09-13 04:31:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_fields_required() {
	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_member_profile_setting')." WHERE available='1' AND required='1' ORDER BY displayorder");

	while($field = DB::fetch($query)) {
		// bluelvoers
		/**
		 * 修正 $field['selective'] 不存在於 DX 1.5, DX 2.0
		 *
		 * 但是實際上 fields_optional, fields_register, fields_required
		 * 只使用到 $field['fieldid'] 欄位
		 * 其餘皆為使用 profilesetting 內儲存的資料
		 * 但仍有可能有不確定的 BUG 存在
		 *
		 * 另外 $field['choices'] 欄位與 profilesetting 內的儲存格式也不同
		 * 新版 DX 2.0 的 $field['choices'] 不支援 1=abc 這種格式
		 * 導致舊版升級後 會造成 舊的自訂欄位有關於 choices 類的資料全部無效
		 *
		 * profilesetting 內為 DX 1.5, DX 2.0 的格式
		 * fields_optional, fields_register, fields_required 內為 D 7.2 以前的格式
		 **/
		$field['selective'] = !empty($field['choices']);
		// bluelvoers
		if($field['selective']) {
			$choices = array();
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

	save_syscache('fields_required', $data);
}

?>