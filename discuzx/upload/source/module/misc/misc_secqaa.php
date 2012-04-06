<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_secqaa.php 10395 2010-05-11 04:48:31Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/seccode');

if($_G['gp_action'] == 'update') {

	$refererhost = parse_url($_SERVER['HTTP_REFERER']);
	$refererhost['host'] .= !empty($refererhost['port']) ? (':'.$refererhost['port']) : '';

	if($refererhost['host'] != $_SERVER['HTTP_HOST']) {
		exit('Access Denied');
	}

	$message = '';
	if($_G['setting']['secqaa']) {
		$question = make_secqaa($_G['gp_idhash']);
	}
	include template('common/header_ajax');
	echo lang('core', 'secqaa_tips').$question;
	include template('common/footer_ajax');

} elseif($_G['gp_action'] == 'check') {

	include template('common/header_ajax');
	echo check_secqaa($_G['gp_secverify'], $_G['gp_idhash']) ? 'succeed' : 'invalid';
	include template('common/footer_ajax');

}

?>