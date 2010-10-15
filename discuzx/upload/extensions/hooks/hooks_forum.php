<?php

/*
	Scorpio (C)2000-2010 Bluelovers Net.

	$HeadURL: $
	$Revision: $
	$Author: bluelovers$
	$Date: $
	$Id: $
*/

Scorpio_Hook::add('Dz_module_forum_viewthread:Before_thread_init', '_eDz_module_forum_viewthread_Before_thread_init');

function _eDz_module_forum_viewthread_Before_thread_init($conf) {
	extract($conf, EXTR_REFS);


}

Scorpio_Hook::add('Dz_module_forum_viewthread:Before_check_nonexistence', '_eDz_module_forum_viewthread_Before_check_nonexistence');
Scorpio_Hook::add('Dz_module_forum_redirect:Before_check_nonexistence', '_eDz_module_forum_viewthread_Before_check_nonexistence');

function _eDz_module_forum_viewthread_Before_check_nonexistence($conf) {
	extract($conf, EXTR_REFS);
	global $_G;

	if ($goto == 'findpost') {
		if ($post['sortid'] == 109) $_G['ppp'] = 1;
	} else {
		if ($thread['sortid'] == 109) $_G['ppp'] = 1;
	}
}

Scorpio_Hook::add('Tpl_Func_hooktags:Before', '_eTpl_Func_hooktags_Before_module_forum');

function _eTpl_Func_hooktags_Before_module_forum(&$hook_data, $hookid, $key) {
	global $_G;

	if ($hookid == 'global_header_javascript2') {
		if ($_G['ppp'] <= 5) {
			$hook_data .= '<style>.pg_viewthread div.pcb .t_fsz .t_f { max-height: none; }</style>';
		}
	}
}

?>