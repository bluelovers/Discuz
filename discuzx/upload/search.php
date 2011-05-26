<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search.php 21641 2011-04-06 03:17:51Z svn_project_zhangjie $
 */

define('APPTYPEID', 0);
define('CURSCRIPT', 'search');

require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$modarray = array('my', 'user', 'curforum', 'newthread');

$cachelist = $slist = array();
$mod = '';
$discuz->cachelist = $cachelist;
$discuz->init();

if(in_array($discuz->var['mod'], $modarray) || !empty($_G['setting']['search'][$discuz->var['mod']]['status'])) {
	$mod = $discuz->var['mod'];
} else {
	foreach($_G['setting']['search'] as $mod => $value) {
		if(!empty($value['status'])) {
			break;
		}
	}
}
if(empty($mod)) {
	showmessage('search_closed');
}
define('CURMODULE', $mod);


runhooks();

require_once libfile('function/search');


$navtitle = lang('core', 'title_search');

if($mod == 'curforum') {
	$mod = 'forum';
	$_G['gp_srchfid'] = array($_G['gp_srhfid']);
} elseif($mod == 'forum') {
	$_G['gp_srhfid'] = 0;
}

require DISCUZ_ROOT.'./source/module/search/search_'.$mod.'.php';

?>