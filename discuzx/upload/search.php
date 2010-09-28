<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search.php 17219 2010-09-27 00:30:26Z monkey $
 */

define('APPTYPEID', 0);
define('CURSCRIPT', 'search');

require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$modarray = array('my', 'user', 'curforum');

$modcachelist = array('register' => array('modreasons', 'stamptypeid', 'fields_required', 'fields_optional'));

$cachelist = $slist = array();
if(isset($modcachelist[CURMODULE])) {
	$cachelist = $modcachelist[CURMODULE];
}

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

define('CURMODULE', $mod);


runhooks();

require_once libfile('function/discuzcode');


$navtitle = lang('core', 'title_search');

if($mod == 'curforum') {
	$mod = 'forum';
	$_G['gp_srchfid'] = array($_G['gp_srhfid']);
	$_G['gp_srhfid'] = $_G['gp_srhfid'];
} elseif($mod == 'forum') {
	$_G['gp_srchfid'] = array();
	$_G['gp_srhfid'] = '';
}

require DISCUZ_ROOT.'./source/module/search/search_'.$mod.'.php';


?>