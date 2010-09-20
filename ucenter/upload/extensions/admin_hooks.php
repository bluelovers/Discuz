<?php

/*
	$HeadURL:  $
	$Revision: $
	$Author: $
	$Date: $
	$Id:  $
*/

scAddHooks('Init_AdminBaseControlClass_After', 'eInit_AdminBaseControlClass_After');

function eInit_AdminBaseControlClass_After (&$control) {
	global $m, $a;

	switch ($m) {
		case 'setting':

//			$control->_setting_items = array_merge($control->_setting_items, array(
//				'',
//			));

			break;
	}
}

?>