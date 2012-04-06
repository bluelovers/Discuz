<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_money.php 7830 2010-04-14 02:22:32Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_updateline {

	var $version = '1.0';
	var $name = 'updateline_name';
	var $description = 'updateline_desc';
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

		$id = intval($_G['gp_id']);
		$idtype = $_G['gp_idtype'];

		magic_check_idtype($id, $idtype);

		$tablename = gettablebyidtype($idtype);
		DB::query("UPDATE ".DB::table($tablename)." SET dateline = '$_G[timestamp]' WHERE $idtype = '$id' AND uid = '$_G[uid]'");

		DB::query("UPDATE ".DB::table('home_feed')." SET dateline = '$_G[timestamp]' WHERE id = '$id' AND idtype = '$idtype' AND uid = '$_G[uid]'");

		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', '0', $idtype, $id);
		showmessage('magics_use_success', '', array('magicname'=>$_G['setting']['magics']['updateline']), array('showdialog' => 1));
	}

	function show() {
		global $_G;
		$id = intval($_GET['id']);
		$idtype = $_GET['idtype'];

		magic_check_idtype($id, $idtype);
		magicshowtips(lang('magic/updateline', 'updateline_info'));
		echo '<p><input type="hidden" name="id" value="'.$id.'" /><input type="hidden" name="idtype" value="'.$idtype.'" /></p>';
	}

}

?>