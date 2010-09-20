<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_smileycodes.php 16698 2010-09-13 05:22:15Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_smileycodes() {
	$data = array();
	$query = DB::query("SELECT typeid, directory FROM ".DB::table('forum_imagetype')." WHERE type='smiley' AND available='1' ORDER BY displayorder");

	while($type = DB::fetch($query)) {
		$squery = DB::query("SELECT id, code, url FROM ".DB::table('common_smiley')." WHERE type='smiley' AND code<>'' AND typeid='$type[typeid]' ORDER BY displayorder");
		if(DB::num_rows($squery)) {
			while($smiley = DB::fetch($squery)) {
				if($size = @getimagesize('./static/image/smiley/'.$type['directory'].'/'.$smiley['url'])) {
					$data[$smiley['id']] = $smiley['code'];
				}
			}
		}
	}

	save_syscache('smileycodes', $data);
}

?>