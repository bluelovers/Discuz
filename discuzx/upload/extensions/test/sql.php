<?php

/**
 *
 */

require './../../source/class/class_core.php';

$discuz = & discuz_core::instance();
$discuz ->init();

$query = DB::query('SELECT * FROM '.DB::table('common_block_style')." ORDER BY styleid");
while($value = DB::fetch($query)) {
	$value['template'] = !empty($value['template']) ? (array)(unserialize($value['template'])) : array();
	$value['fields'] = !empty($value['fields']) ? (array)(unserialize($value['fields'])) : array();

	foreach (array('template', 'fields') as $_index) {
		foreach($value[$_index] as $_k => $_v) {
			$value[$_index][$_k] = str_replace("\r\n", "\n", $_v);
		}

		$value[$_index] = !empty($value[$_index]) ? serialize($value[$_index]) : '';
	}

	$value = daddslashes($value);

	DB::update('common_block_style', $value, array('styleid' => $value['styleid']));
}

?>