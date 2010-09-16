<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum.php 16429 2010-09-06 09:51:21Z monkey $
 */

define('APPTYPEID', 2);
define('CURSCRIPT', 'forum');


require './source/class/class_core.php';
require './source/function/function_forum.php';

$discuz = & discuz_core::instance();

$modarray = array('ajax','announcement','attachment','forumdisplay',
	'group','image','index','medal','misc','modcp','notice','post','redirect',
	'relatekw','relatethread','rss','topicadmin','trade','viewthread'
);

$modcachelist = array(
	'index'		=> array('announcements', 'onlinelist', 'forumlinks', 'advs_index',
			'heats', 'historyposts', 'onlinerecord', 'blockclass', 'userstats'),
	'forumdisplay'	=> array('smilies', 'announcements_forum', 'globalstick', 'forums',
			'icons', 'onlinelist', 'forumstick', 'blockclass',
			'threadtable_info', 'threadtableids', 'stamps'),
	'viewthread'	=> array('smilies', 'smileytypes', 'forums', 'usergroups', 'ranks',
			'stamps', 'bbcodes', 'smilies',	'custominfo', 'groupicon', 'stamps',
			'threadtableids', 'threadtable_info', 'blockclass'),
	'post'		=> array('bbcodes_display', 'bbcodes', 'smileycodes', 'smilies', 'smileytypes',
			'icons', 'domainwhitelist'),
	'space'		=> array('fields_required', 'fields_optional', 'custominfo'),
	'group'		=> array('grouptype'),
);

$mod = !in_array($discuz->var['mod'], $modarray) ? 'index' : $discuz->var['mod'];

define('CURMODULE', $mod != 'redirect' ? $mod : 'viewthread');
$cachelist = array();
if(isset($modcachelist[CURMODULE])) {
	$cachelist = $modcachelist[CURMODULE];
}
if($discuz->var['mod'] == 'group') {
	$_G['basescript'] = 'group';
}

$discuz->cachelist = $cachelist;
$discuz->init();

loadforum();
set_rssauth();
runhooks();


$navtitle = $_G['setting']['seotitle']['forum'];

require DISCUZ_ROOT.'./source/module/forum/forum_'.$mod.'.php';

?>