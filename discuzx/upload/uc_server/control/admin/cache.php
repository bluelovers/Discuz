<?php

/*
	[UCenter] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: cache.php 753 2008-11-14 06:48:25Z cnteacher $
*/

!defined('IN_UC') && exit('Access Denied');

class control extends adminbase {

	function __construct() {
		$this->control();
	}

	function control() {
		parent::__construct();
		$this->check_priv();
		if(!$this->user['isfounder'] && !$this->user['allowadmincache']) {
			$this->message('no_permission_for_this_module');
		}
		$this->load('cache');
	}

	function onupdate() {
		$updated = false;
		if($this->submitcheck('submit')) {
			$type = getgpc('type', 'P');
			if(!is_array($type) || in_array('data', $type)) {
				$_ENV['cache']->updatedata();
			}
			if(!is_array($type) || in_array('tpl', $type)) {
				$_ENV['cache']->updatetpl();
			}
			$updated = true;
		}
		$this->view->assign('updated', $updated);
		$this->view->display('admin_cache');
	}
}

?>