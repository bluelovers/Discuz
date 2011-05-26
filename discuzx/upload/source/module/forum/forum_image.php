<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_image.php 20946 2011-03-09 03:34:33Z monkey $
 */

if(!defined('IN_DISCUZ') || empty($_G['gp_aid']) || empty($_G['gp_size']) || empty($_G['gp_key'])) {
	header('location: '.$_G['siteurl'].'static/image/common/none.gif');
	exit;
}

$nocache = !empty($_G['gp_nocache']) ? 1 : 0;
$daid = intval($_G['gp_aid']);
$type = !empty($_G['gp_type']) ? $_G['gp_type'] : 'fixwr';
list($w, $h) = explode('x', $_G['gp_size']);
$dw = intval($w);
$dh = intval($h);
$thumbfile = 'image/'.$daid.'_'.$dw.'_'.$dh.'.jpg';
$parse = parse_url($_G['setting']['attachurl']);
$attachurl = !isset($parse['host']) ? $_G['siteurl'].$_G['setting']['attachurl'] : $_G['setting']['attachurl'];
if(!$nocache) {
	if(file_exists($_G['setting']['attachdir'].$thumbfile)) {
		dheader('location: '.$attachurl.$thumbfile);
	}
}

define('NOROBOT', TRUE);

$id = !empty($_G['gp_atid']) ? $_G['gp_atid'] : $daid;
if(md5($id.'|'.$dw.'|'.$dh) != $_G['gp_key']) {
	dheader('location: '.$_G['siteurl'].'static/image/common/none.gif');
}

if($attach = DB::fetch(DB::query("SELECT * FROM ".DB::table(getattachtablebyaid($daid))." WHERE aid='$daid' AND isimage IN ('1', '-1')"))) {
	if(!$dw && !$dh && $attach['tid'] != $daid) {
	       dheader('location: '.$_G['siteurl'].'static/image/common/none.gif');
	}
        dheader('Expires: '.gmdate('D, d M Y H:i:s', TIMESTAMP + 3600).' GMT');
	if($attach['remote']) {
		$filename = $_G['setting']['ftp']['attachurl'].'forum/'.$attach['attachment'];
	} else {
		$filename = $_G['setting']['attachdir'].'forum/'.$attach['attachment'];
	}
	require_once libfile('class/image');
	$img = new image;
	if($img->Thumb($filename, $thumbfile, $w, $h, $type)) {
		if($nocache) {
			@readfile($_G['setting']['attachdir'].$thumbfile);
			@unlink($_G['setting']['attachdir'].$thumbfile);
		} else {
			dheader('location: '.$attachurl.$thumbfile);
		}
	} else {
		@readfile($filename);
	}
}

?>