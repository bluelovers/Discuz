<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_swfupload.php 18303 2010-11-18 09:56:38Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($_G['gp_operation'] == 'config') {

	$swfhash = md5(substr(md5($_G['config']['security']['authkey']), 8).$_G['uid']);
	$xmllang = lang('forum/swfupload');
	$imageexts = array('jpg','jpeg','gif','png','bmp');
	$forumattachextensions = '';
	if(!empty($_G['gp_fid'])) {
		$forum = DB::fetch_first("SELECT ff.attachextensions, f.status, f.level FROM ".DB::table('forum_forumfield')." ff LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=ff.fid WHERE ff.fid='$_G[gp_fid]'");
		if($forum['status'] == 3 && $forum['level'] && $postpolicy = DB::result_first("SELECT postpolicy FROM ".DB::table('forum_grouplevel')." WHERE levelid='$forum[level]'")) {
			$postpolicy = unserialize($postpolicy);
			$forumattachextensions = $postpolicy['attachextensions'];
		} else {
			$forumattachextensions = $forum['attachextensions'];
		}
	}
	$notallow = array();
	$extendtype = '';
	$query = DB::query("SELECT extension, maxsize FROM ".DB::table('forum_attachtype'));
	while($type = DB::fetch($query)) {
		if($type['maxsize'] == 0) {
			$notallow[] = $type['extension'];
		} else {
			$extendtype .= "<extendType extension=\"".strtolower($type['extension'])."\">$type[maxsize]</extendType>";
		}
	}
	$_G['group']['attachextensions'] = !$forumattachextensions ? $_G['group']['attachextensions'] : $forumattachextensions;
	if($_G['group']['attachextensions'] !== '') {
		$_G['group']['attachextensions'] = str_replace(' ', '', $_G['group']['attachextensions']);
		$exts = explode(',', $_G['group']['attachextensions']);
		if($_G['gp_type'] == 'image') {
			$exts = array_intersect($imageexts, $exts);
		}
		foreach($exts as $key => $value) {
			if(in_array($value, $notallow)) {
				unset($exts[$key]);
			}
		}
		$_G['group']['attachextensions'] = !empty($exts) ? '*.'.implode(',*.', $exts) : '';
	} else {
		foreach($imageexts as $key => $value) {
			if(in_array($value, $notallow)) {
				unset($imageexts[$key]);
			}
		}
		$_G['group']['attachextensions'] = $_G['gp_type'] == 'image' ? (!empty($imageexts) ? '*.'.implode(',*.', $imageexts) : '') : '*.*';
	}
	$depict = $_G['gp_type'] == 'image' ? 'Image File ' : 'All Support Formats ';
	$max = 0;
	if(!empty($_G['group']['maxattachsize'])) {
		$max = intval($_G['group']['maxattachsize']);
	} else {
		$max = @ini_get(upload_max_filesize);
		$unit = strtolower(substr($max, -1, 1));
		if($unit == 'k') {
			$max = intval($max)*1024;
		} elseif($unit == 'm') {
			$max = intval($max)*1024*1024;
		} elseif($unit == 'g') {
			$max = intval($max)*1024*1024*1024;
		}
	}

	@header("Expires: -1");
	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
	@header("Content-type: application/xml; charset=utf-8");
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><parameter><allowsExtend>".(!empty($_G['group']['attachextensions']) ? "<extend depict=\"$depict\">{$_G[group][attachextensions]}</extend>" : '')."</allowsExtend><language>$xmllang</language><config><userid>$_G[uid]</userid><hash>$swfhash</hash><maxupload>{$max}</maxupload>".(!empty($extendtype) ? "<limitType>$extendtype</limitType>" : "")."</config></parameter>";

} elseif($_G['gp_operation'] == 'upload') {

	require_once libfile('class/forumupload');
	if(empty($_G['gp_simple'])) {
		$_FILES['Filedata']['name'] = addslashes(diconv(urldecode($_FILES['Filedata']['name']), 'UTF-8'));
		$_FILES['Filedata']['type'] = $_G['gp_filetype'];
	}
	$upload = new forum_upload();

}

?>