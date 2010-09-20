<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_ajax.php 7091 2010-03-29 02:47:30Z redstone $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

require_once libfile('function/post');

if($_G['gp_action'] == 'protectsort') {
	if($_G['gp_sortvalue']) {
		makevaluepic($_G['gp_sortvalue']);
	} else {
		$tid = intval($_G['gp_tid']);
		$optionid = $_G['gp_optionid'];
		include template('category/header_ajax');
		echo DB::result_first('SELECT value FROM '.DB::table('category_sortoptionvar')." WHERE tid='$tid' AND optionid='$optionid'");
		include template('category/footer_ajax');
	}
} elseif($_G['gp_action'] == 'thumb') {
	if(empty($_G['gp_aid']) || empty($_G['gp_size']) || empty($_G['gp_key'])) {
		header('location: '.$_G['siteurl'].'static/image/common/nophotosmall.gif');
		exit;
	}

	$nocache = !empty($_G['gp_nocache']) ? 1 : 0;
	$aid = intval($_G['gp_aid']);
	$type = !empty($_G['gp_type']) ? $_G['gp_type'] : 'fixwr';
	list($w, $h) = explode('x', $_G['gp_size']);
	$w = intval($w);
	$h = intval($h);
	$thumbfile = 'image/'.$aid.'_'.$w.'_'.$h.'_house.jpg';
	$identifier = $_G['gp_identifier'] && in_array($_G['gp_identifier'], array('house')) ? $identifier : 'house';
	if(!$nocache) {
		if(file_exists(DISCUZ_ROOT.'./data/attachment/'.$thumbfile)) {
			header('location: '.$_G['siteurl'].'data/attachment/'.$thumbfile);
			exit;
		}
	}

	define('NOROBOT', TRUE);

	list($daid, $dw, $dh) = explode("\t", authcode($_G['gp_key'], 'DECODE', $_G['config']['security']['authkey']));

	if($daid != $aid || $dw != $w || $dh != $h) {
		dheader('location: '.$_G['siteurl'].'static/image/common/nophotosmall.gif');
	}

	if($attach = DB::fetch(DB::query("SELECT url FROM ".DB::table('category_'.$identifier.'_pic')." WHERE aid='$aid'"))) {
		dheader('Expires: '.gmdate('D, d M Y H:i:s', TIMESTAMP + 3600).' GMT');
		$filename = $_G['setting']['attachdir'].'/category/'.$attach['url'];

		require_once libfile('class/image');
		$img = new image;
		if($img->Thumb($filename, $thumbfile, $w, $h, $type)) {
			if($nocache) {
				@readfile(DISCUZ_ROOT.'./data/attachment/'.$thumbfile);
				@unlink(DISCUZ_ROOT.'./data/attachment/'.$thumbfile);
			} else {
				dheader('location: '.$_G['siteurl'].'data/attachment/'.$thumbfile);
			}
		} else {
			@readfile($filename);
		}
	}
} elseif($_G['gp_action'] == 'buyoption') {

	if(empty($_G['uid'])) {
		showmessage(lang('category/template', 'house_please_login'));
	}

	$tid = intval($_G['gp_tid']);
	$optionid = intval($_G['gp_optionid']);
	$buy = unserialize(DB::result_first('SELECT protect FROM '.DB::table('category_sortoption')." WHERE optionid='$optionid'"));
	$exist = DB::result_first('SELECT tid FROM '.DB::table('category_payoption')." WHERE uid='$_G[uid]' AND tid='$tid' AND optionid='$optionid'");
	if(getuserprofile('extcredits'.$_G['setting']['creditstransextra'][$buy['credits']['title']]) < $buy['credits']['price']) {
		showmessage(lang('category/template', 'house_no_integral'));
	} else {
		if(empty($exist)) {
			updatemembercount($_G['uid'], array($_G['setting']['creditstransextra'][$buy['credits']['title']] => -$buy['credits']['price']));
			DB::query("INSERT INTO ".DB::table('category_payoption')." (tid, uid, optionid, dateline) VALUES ('$tid', '$_G[uid]', '$optionid', '$_G[timestamp]')");
		}
		$optionvalue = DB::result_first('SELECT value FROM '.DB::table('category_sortoptionvar')." WHERE tid='$tid' AND optionid='$optionid'");
		showmessage($optionvalue);
	}
}

function makevaluepic($value) {
	Header("Content-type:image/png");
	$im = imagecreate(130, 25);
	$text_color = imagecolorallocate($im, 23, 14, 91);
	imagestring($im, 4, 0, 4, $value, $text_color);
	imagepng($im);
	imagedestroy($im);
}

?>