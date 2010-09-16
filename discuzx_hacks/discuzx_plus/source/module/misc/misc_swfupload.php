<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_swfupload.php 583 2010-09-06 03:02:48Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

ob_end_clean();
ob_start();
@header("Expires: -1");
@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
@header("Pragma: no-cache");
@header("Content-type: application/xml; charset=UTF-8");

if($_G['gp_operation'] == 'config' && $_G['uid']) {

	$swfhash = md5(substr(md5($_G['config']['security']['authkey']), 8).$_G['uid']);
	$xmllang = lang('swfupload');
	$imageexts = array('jpg','jpeg','gif','png');
	$attachextensions = '*.'.implode(',*.', $imageexts);
	$maxattachsize = 10240000;
	$depict = 'Image File ';
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><parameter><allowsExtend><extend depict=\"$depict\">{$attachextensions}</extend></allowsExtend><language>$xmllang</language><config><userid>$_G[uid]</userid><hash>$swfhash</hash><maxupload>{$maxattachsize}</maxupload></config></parameter>";

} elseif($_G['gp_operation'] == 'upload') {

/*
@$fp = fopen(DISCUZ_ROOT.'./data/log/misc.log.php', 'a');
@flock($fp, 2);
@fwrite($fp, "<?exit?>".var_export($this->pollid, TRUE)."\n\n");
@fclose($fp);
*/

	require_once libfile('class/pollupload');
	$_FILES['Filedata']['name'] = addslashes(diconv(urldecode($_FILES['Filedata']['name']), 'UTF-8'));
	$upload = new poll_upload();

}

?>