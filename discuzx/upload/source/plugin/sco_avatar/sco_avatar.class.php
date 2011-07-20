<?php

include_once 'sco_dx_plugin.class.php';

class plugin_sco_avatar extends _sco_dx_plugin {
	function plugin_sco_avatar() {
		$this->_init($this->_get_identifier(__METHOD__));

		// 追加的基準語言包
		$this->_lang_push('home');
	}
}

class plugin_sco_avatar_home extends plugin_sco_avatar {
	/**
	 * 在瀏覽 修改頭像 時執行
	 *
	 * 此時尚未執行 require_once libfile('home/'.$mod, 'module');
	 *
	 * @see home.php
	 * @link home.php?mod=spacecp&ac=avatar
	 **/
	function spacecp_avatar() {
		/*
		echo '<pre>';

		echo $this->identifier."\n";
		print_r($this);
		*/

		global $_G;

		//------------------------------------------------------

		$_G['mnid'] = 'mn_common';

		$ac = getgpc('ac');
		$op = getgpc('op');

		if(empty($_G['uid'])) {
			if($_SERVER['REQUEST_METHOD'] == 'GET') {
				dsetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
			} else {
				dsetcookie('_refer', rawurlencode('home.php?mod=spacecp&ac='.$ac));
			}
			showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
		}

		$space = getspace($_G['uid']);
		if(empty($space)) {
			showmessage('space_does_not_exist');
		}
		space_merge($space, 'field_home');

		if(($space['status'] == -1 || in_array($space['groupid'], array(4, 5, 6))) && $ac != 'usergroup') {
			showmessage('space_has_been_locked');
		}

		$actives = array($ac => ' class="a"');

		$seccodecheck = $_G['group']['seccode'] ? $_G['setting']['seccodestatus'] & 4 : 0;
		$secqaacheck = $_G['group']['seccode'] ? $_G['setting']['secqaa']['status'] & 2 : 0;

		$navtitle = lang('core', 'title_setup');
		if(lang('core', 'title_memcp_'.$ac)) {
			$navtitle = lang('core', 'title_memcp_'.$ac);
		}

		//------------------------------------------------------

		$actives = array('avatar' =>' class="a"');

		$_loop_avatar = $this->_loop_glob($this->attr['directory'].'image/avatar/default', '*');

		include $this->_template('spacecp_avatar');
		exit();
	}
}

?>