<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_swfupload.php 17148 2010-09-25 03:56:14Z zhengqingpeng $
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
	$_G['group']['attachextensions'] = !$forumattachextensions ? $_G['group']['attachextensions'] : $forumattachextensions;
	if($_G['group']['attachextensions'] !== '') {
		$_G['group']['attachextensions'] = str_replace(' ', '', $_G['group']['attachextensions']);
		$exts = explode(',', $_G['group']['attachextensions']);
		if($_G['gp_type'] == 'image') {
			$exts = array_intersect($imageexts, $exts);
		}
		$_G['group']['attachextensions'] = '*.'.implode(',*.', $exts);
	} else {
		$_G['group']['attachextensions'] = $_G['gp_type'] == 'image' ? '*.'.implode(',*.', $imageexts) : '*.*';
	}
	$depict = $_G['gp_type'] == 'image' ? 'Image File ' : 'All Support Formats ';
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><parameter><allowsExtend><extend depict=\"$depict\">{$_G[group][attachextensions]}</extend></allowsExtend><language>$xmllang</language><config><userid>$_G[uid]</userid><hash>$swfhash</hash><maxupload>{$_G[group][maxattachsize]}</maxupload></config></parameter>";

} elseif($_G['gp_operation'] == 'upload') {

	require_once libfile('class/forumupload');
	if(empty($_G['gp_simple'])) {
		$_FILES['Filedata']['name'] = addslashes(diconv(urldecode($_FILES['Filedata']['name']), 'UTF-8'));
	}
	$upload = new forum_upload();

}

?>