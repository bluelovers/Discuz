<?php

/**
 *      [Æ·ÅÆ¿Õ¼ä] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: perm.inc.php 4238 2010-08-23 06:49:46Z yumiao $
 */

if(!defined('IN_ADMIN') && !defined('IN_STORE')) {
	exit('Access Denied');
}

$isfounder = isset($isfounder) ? $isfounder : isfounder();

$topmenu = $menu = array();

$topmenu = array (
	'index' => '',
	'global' => '',
	'catmanage' => '',
	'shop' => '',
	'infomanage' => '',
	'admintools' => ''
);
$menu['index'] = array(
		array('menu_home', 'index'),
		array('menu_shop', 'list&m=shop'),
		array('menu_good', 'list&m=good'),
		array('menu_notice', 'list&m=notice'),
		array('menu_album', 'list&m=album'),
		array('menu_consume', 'list&m=consume'),
		array('menu_groupbuy', 'list&m=groupbuy'),
		array('menu_home_waitmod', 'list&m=shop&grade=0&optpass=1&filtersubmit=GO')
);

$menu['global'] = array(
	array('menu_global_basic', 'global'),
	array('menu_attach', 'attach'),
	array('menu_ads', 'ads'),
	array('menu_commentmodel', 'commentmodel'),
	array('menu_nav', 'nav'),
	array('menu_attr', 'attr'),
	array('menu_global_censor', 'censor'),
	array('menu_tool_updatecache', 'tool&operation=updatecache'),
	array('menu_tool_cron', 'tool&operation=cron'),
	array('menu_uc_discuz', 'discuz'),
	array('menu_block', 'block'),
);

$menu['catmanage'] = array(
	array('menu_category_shop', 'category&type=shop'),
	array('menu_category_region', 'category&type=region'),
	array('menu_category_good', 'category&type=good'),
	array('menu_category_album', 'category&type=album'),
	array('menu_category_consume', 'category&type=consume'),
	array('menu_category_groupbuy', 'category&type=groupbuy'),
	array('menu_category_notice', 'category&type=notice'),
);

$menu['shop'] = array(
	array('menu_shop', 'list&m=shop'),
	array('menu_home_waitmod', 'list&m=shop&grade=0&optpass=1&filtersubmit=GO'),
	array('menu_list_addshop', 'add&m=shop'),
	array('menu_group', 'group'),
);

$menu['infomanage'] = array(
	array('menu_good', 'list&m=good'),
	array('menu_album', 'list&m=album'),
	array('menu_consume', 'list&m=consume'),
	array('menu_notice', 'list&m=notice'),
	array('menu_comment', 'comment'),
	array('menu_report', 'report'),
	array('menu_groupbuy', 'list&m=groupbuy'),
	array('menu_brandlinks', 'brandlinks'),
);
$menu['admintools'] = array(
        array('menu_logs', 'logs&operation=admin'),
        array('menu_db', 'db&operation=export'),
);
if($isfounder) {
    $menu['admintools'][] = array('menu_perm', 'perm');
}
/*
$menu['forum'] = array(
	array('menu_forums', 'forums'),
	array('menu_forums_merge', 'forums_merge'),
	array('menu_forums_infotypes', 'threadtypes'),
);

$menu['group'] = array(
	array('menu_group_setting', 'group_setting'),
	array('menu_group_type', 'group_type'),
	array('menu_group_manage', 'group_manage'),
	array('menu_group_userperm', 'group_userperm'),
	array('menu_group_level', 'group_level'),
);

$menu['extended'] = array(
	array('menu_adv_custom', 'adv'),
	array('menu_tasks', 'tasks'),
	array('menu_magics', 'magics'),
	array('menu_medals', 'medals'),
	array('menu_misc_help', 'faq'),
	array('menu_ec', 'setting_ec'),
	array('menu_misc_link', 'misc_link'),
	array('memu_focus_topic', 'misc_focus'),
);
*/
/*
if(file_exists($menudir = DISCUZ_ROOT.'./source/admincp/menu')) {
	$adminextend = $adminextendnew = array();
	if(file_exists($adminextendfile = DISCUZ_ROOT.'./data/cache/cache_adminextend.php')) {
		@include $adminextendfile;
	}
	$menudirhandle = dir($menudir);
	while($entry = $menudirhandle->read()) {
		if(!in_array($entry, array('.', '..')) && preg_match("/^menu\_([\w\.]+)$/", $entry, $entryr) && substr($entry, -4) == '.php' && strlen($entry) < 30 && is_file($menudir.'/'.$entry)) {
			@include_once $menudir.'/'.$entry;
			$adminextendnew[] = $entryr[1];
		}
	}
	if($adminextend != $adminextendnew) {
		@unlink($adminextendfile);
		if($adminextendnew) {
			require_once libfile('function/cache');
			writetocache('adminextend', '', getcachevars(array('adminextend' => $adminextendnew)));
		}
		unset($_G['lang']['admincp']);
	}
}
*/
/*
if($isfounder) {
	$menu['plugin'] = array(
		array('menu_addons', 'addons'),
		array('menu_plugins', 'plugins'),
	);
}
*/
/*
@include_once DISCUZ_ROOT.'./data/cache/cache_adminmenu.php';
if(is_array($adminmenu)) {
	foreach($adminmenu as $row) {
		$menu['plugin'][] = array($row['name'], $row['action']);
	}
}
if(!$menu['plugin']) {
	unset($topmenu['plugin']);
}

$menu['tools'] = array(
	array('menu_tools_updatecaches', 'tools_updatecache'),
	array('menu_misc_announce', 'announce'),
	array('menu_tools_updatecounters', 'counter'),
	array('menu_logs', 'logs'),
	array('menu_misc_cron', 'misc_cron'),
	$isfounder ? array('menu_tools_fileperms', 'tools_fileperms') : null,
	$isfounder ? array('menu_tools_filecheck', 'checktools_filecheck') : null,
);

if($isfounder) {
	$topmenu['founder'] = '';

	$menu['founder'] = array(
		array('menu_founder_perm', 'founder_perm'),
		array('menu_setting_mail', 'setting_mail'),
		array('menu_setting_uc', 'setting_uc'),
		array('menu_setting_manyou', 'setting_manyou'),
		array('menu_db', 'db_export'),
		array('menu_postsplit', 'postsplit_manage'),
		array('menu_threadsplit', 'threadsplit_manage'),
	);

	$menu['uc'] = array();
}
*/
if(!isfounder() && !isset($_SGLOBAL['adminsession']['perms']['all'])) {
	$menunew = $menu;
	foreach($menu as $topkey => $datas) {
		/*
		if($topkey == 'index') {
			continue;
		}
		*/
		$itemexists = 0;
		foreach($datas as $key => $data) {
		    if($data[1] == 'index') {
		        $itemexists = 1;
			    continue;
		    }
			if(array_key_exists($data[1], $_SGLOBAL['adminsession']['perms'])) {
				$itemexists = 1;
			} else {
			    //echo $data[1];
			    //echo $_SGLOBAL['adminsession']['perms'][$data[1]];
			    //print_r($_SGLOBAL['adminsession']['perms']);
				unset($menunew[$topkey][$key]);
			}
		}
		if(!$itemexists) {
			unset($topmenu[$topkey]);
			unset($menunew[$topkey]);
		}
	}
	$menu = $menunew;
}
/*
array_splice($menu['global'], 3, 0, array(
	array('setting_cachethread', 'setting_cachethread'),
	array('setting_serveropti', 'setting_serveropti'),
));

array_splice($menu['global'], 9, 0, array(
	array('founder_perm_credits', 'credits'),
));

array_splice($menu['style'], 8, 0, array(
	array('setting_editor_code', 'misc_bbcode'),
));

$menu['topic'][0] = array('founder_perm_moderate_threads', 'moderate_threads');
array_splice($menu['topic'], 1, 0, array(
	array('founder_perm_moderate_replies', 'moderate_replies'),
));

array_splice($menu['user'], 1, 0, array(
	array('founder_perm_members_group', 'members_group'),
	array('founder_perm_members_access', 'members_access'),
	array('founder_perm_members_credit', 'members_credit'),
	array('founder_perm_members_medal', 'members_medal'),
	array('founder_perm_members_repeat', 'members_repeat'),
	array('founder_perm_members_clean', 'members_clean'),
	array('founder_perm_members_edit', 'members_edit'),
));

array_splice($menu['group'], 1, 0, array(
	array('founder_perm_group_editgroup', 'group_editgroup'),
	array('founder_perm_group_deletegroup', 'group_deletegroup'),
));

array_splice($menu['extended'], 6, 0, array(
	array('founder_perm_ec_alipay', 'ec_alipay'),
	array('founder_perm_ec_tenpay', 'ec_tenpay'),
	array('founder_perm_ec_credit', 'ec_credit'),
	array('founder_perm_ec_orders', 'ec_orders'),
	array('founder_perm_tradelog', 'tradelog'),
));
*/
function isfounder() {
    global $_G, $_SGLOBAL;
    return ckfounder($_G['uid']);
}
