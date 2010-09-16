<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_perm.php 13923 2010-08-03 06:31:22Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

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
	array('menu_moderate_blogs', 'moderate_blogs'),
	array('menu_moderate_pictures', 'moderate_pictures'),
	array('menu_moderate_doings', 'moderate_doings'),
	array('menu_moderate_shares', 'moderate_shares'),
	array('menu_moderate_comments', 'moderate_comments'),
	array('menu_moderate_articles', 'moderate_articles'),
	array('menu_moderate_articlecomments', 'moderate_articlecomments'),
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

?>