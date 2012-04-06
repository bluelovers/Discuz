<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_smilies.php 16693 2010-09-13 04:31:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_smilies() {
	$data = array();
	$query = DB::query("SELECT s.id, s.code, s.url, t.typeid FROM ".DB::table('common_smiley')." s
		LEFT JOIN ".DB::table('forum_imagetype')." t ON t.typeid=s.typeid WHERE s.type='smiley' AND s.code<>'' AND t.available='1' ORDER BY LENGTH(s.code) DESC");

	$data = array('searcharray' => array(), 'replacearray' => array(), 'typearray' => array());
	while($smiley = DB::fetch($query)) {
		$data['searcharray'][$smiley['id']] = '/'.preg_quote(dhtmlspecialchars($smiley['code']), '/').'/';
		$data['replacearray'][$smiley['id']] = $smiley['url'];
		$data['typearray'][$smiley['id']] = $smiley['typeid'];
	}

	save_syscache('smilies', $data);
}

?>