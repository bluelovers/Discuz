<?php
/*
 *	Author: IAN - zhouxingming
 *	Last modified: 2011-09-14 19:36
 *	Filename: aaa.inc.php
 *	Description: 
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$_G['gp_tid'] = intval($_G['gp_tid']);

if($_G['gp_tid']) {
	list($width, $height) = explode('x', $_G['gp_size']);
	$width = intval($width);
	$height = intval($height);
	$width = $width ? $width : 140;
	$height = $height ? $height : 140;

	if($_G['gp_code'] == md5($_G['gp_tid'].'|'.$width.'|'.$height.$_G['config']['security']['authkey'])) {
		$aid = DB::result_first("SELECT aid FROM ".DB::table('plugin_auction')." WHERE tid='{$_G[gp_tid]}'");
		if($aid) {

			$url = getforumimg($aid, 0, $width, $height);
			$url = $_G['siteurl'].$url;
			dheader("Location:$url");
		}
	}
}
?>
