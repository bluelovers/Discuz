<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_admincp_tools.php 2473 2011-04-21 06:44:56Z songlixin $
 */
if(file_exists(DISCUZ_ROOT.'./data/plugindata/tools.lang.php')){
	include DISCUZ_ROOT.'./data/plugindata/tools.lang.php';
	$menulang = $installlang['tools'];
} else {
	loadcache('pluginlanguage_install');
	$menulang = $_G['cache']['pluginlanguage_install']['tools'];
}

$extend_lang = array
(
	'header_exttools' => 'TOOLS',
	'nav_exttools' => 'TOOLS',
	'menu_exttools_pw' => $menulang['menu_exttools_pw'],
	'menu_exttools_moudle' => $menulang['menu_exttools_moudle'],
	'menu_exttools_cleardb' => $menulang['menu_exttools_cleardb'],
	'menu_exttools_exportdata' => $menulang['menu_exttools_exportdata'],
	'menu_exttools_district' => $menulang['menu_exttools_district'],
	'menu_exttools_censor' => $menulang['menu_exttools_censor'],
	'menu_exttools_ucenter'=> $menulang['menu_exttools_ucenter'],
	'menu_exttools_safe' => $menulang['menu_exttools_safe'],
	'menu_exttools_motion' => $menulang['menu_exttools_motion'],
	'menu_exttools_att' => $menulang['menu_exttools_att'],
	'menu_exttools_convert' => $menulang['menu_exttools_convert'],
	'menu_exttools_house' => $menulang['menu_exttools_house'],
);


$GLOBALS['admincp_actions_normal'][] = 'exttools';

?>
