<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_flicker.php 13898 2010-08-03 02:22:52Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_flicker {
	var $version = '1.0';
	var $name = 'flicker_name';
	var $description = 'flicker_desc';
	var $price = '20';
	var $weight = '20';
	var $useevent = 0;
	var $targetgroupperm = false;
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {}

	function setsetting(&$magicnew, &$parameters) {}

	function usesubmit() {
		global $_G;

		DB::update('home_comment', array('magicflicker' => 1), array('cid'=>intval($_G['gp_id']), 'authorid'=>$_G['uid']));
		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0');
		showmessage(lang('magic/flicker', 'flicker_succeed'), '', array(), array('showdialog' => 1, 'closetime' => true));
	}

	function show() {
		global $_G;
		magicshowtips(lang('magic/flicker', 'flicker_info'));
	}
}

?>