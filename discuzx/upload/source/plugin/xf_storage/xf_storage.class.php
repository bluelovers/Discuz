<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: xf_storage.class.php 29021 2012-03-22 09:35:55Z songlixin $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_xf_storage {

	var $value = array();
    var $status = '';

    function plugin_xf_storage() {
		global $_G;
        include_once libfile('function/cloud');
        $this->status = getcloudappstatus('storage', 0);
    }

	function common(){
        global $_G;

        if (!$this->status) {
            return false;
        }
		include_once libfile('function/ftn');
		if($this->_check_browse() > 0){
			if(CURMODULE == 'post' && CURSCRIPT == 'forum' && $_G['uid']){
				$_G['config']['output']['iecompatible'] = '7';
			}
		}
	}

    function _check_browse(){
        return true;
	}

	function global_footer(){
        if (!$this->status) {
           return false;
        }
        include template('xf_storage:css');
        return $return;
	}

}

class plugin_xf_storage_forum extends plugin_xf_storage {

	function post_attach_btn_extra() {
        if (!$this->status) {
            return false;
        }
        global $_G;
		include template('xf_storage:editor');
		return $return;
	}

	function post_attach_tab_extra() {
        if (!$this->status) {
            return false;
        }
        global $_G;
		$editorid = 'e';
		$check = $this->_check_browse();
		include template('xf_storage:ftnupload');
		return $return;
	}
}

?>