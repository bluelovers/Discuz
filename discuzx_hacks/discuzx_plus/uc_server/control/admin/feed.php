<?php

/*
	[UCenter] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: feed.php 753 2008-11-14 06:48:25Z cnteacher $
*/

!defined('IN_UC') && exit('Access Denied');

class control extends adminbase {

	var $apps = array();
	var $operations = array();

	function __construct() {
		$this->control();
	}

	function control() {
		parent::__construct();
		if(!$this->user['isfounder'] && !$this->user['allowadminnote']) {
			$this->message('no_permission_for_this_module');
		}
		$this->load('feed');
		$this->load('misc');
		$this->apps = $this->cache['apps'];		
		$this->check_priv();
	}

	function onls() {
		$page = getgpc('page');
		$delete = getgpc('delete', 'P');
		$num = $_ENV['feed']->get_total_num();
		$feedlist = $_ENV['feed']->get_list($page, UC_PPP, $num);
		$multipage = $this->page($num, UC_PPP, $page, 'admin.php?m=feed&a=ls');

		$this->view->assign('feedlist', $feedlist);
		$this->view->assign('multipage', $multipage);

		$this->view->display('admin_feed');
	}

}

?>