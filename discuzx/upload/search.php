<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search.php 17079 2010-09-20 09:00:42Z monkey $
 */

define('APPTYPEID', 0);
define('CURSCRIPT', 'search');

require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$modarray = array('portal', 'forum', 'blog', 'album', 'group', 'my', 'user', 'curforum');

$mod = !in_array($discuz->var['mod'], $modarray) ? 'forum' : $discuz->var['mod'];

define('CURMODULE', $mod);


$modcachelist = array('register' => array('modreasons', 'stamptypeid', 'fields_required', 'fields_optional'));

$cachelist = $slist = array();
if(isset($modcachelist[CURMODULE])) {
	$cachelist = $modcachelist[CURMODULE];
}

$discuz->cachelist = $cachelist;
$discuz->init();


runhooks();

require_once libfile('function/discuzcode');


$navtitle = lang('core', 'title_search');

if($discuz->var['mod'] == 'curforum') {
	$mod = 'forum';
	$_G['gp_srchfid'][] = $_G['gp_srhfid'];
}

require DISCUZ_ROOT.'./source/module/search/search_'.$mod.'.php';


?>