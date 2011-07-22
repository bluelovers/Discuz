<?php

include_once '_sco_dx_plugin.class.php';

class plugin_sco_avatar extends _sco_dx_plugin {
	function plugin_sco_avatar() {
		$this->_init($this->_get_identifier(__METHOD__));

		// 追加的基準語言包
		$this->_lang_push('home');

		// set instance = $this
		$this->_this(&$this);

		$this
			->_setglobal('imgexts', array('jpg', 'jpeg', 'gif', 'png', 'bmp'))
			->_setglobal('avatar_base_path', $this->attr['directory'].'image/avatar/')
		;
	}

	function _my_avatar_save($uid, $url) {
		if ($uid <= 0) return false;

		return $member_uc = $this
			->_uc_init()
			->_uc_call('sc', 'set_user_fields', array(
				'uid' => $uid,
				'fields'=> array(
					'avatar' => $url,
				),
		));
	}

	function _my_avatar_types_list() {
		$avatar_base_path = $this->_getglobal('avatar_base_path');

		$avatar_types = array();

		$avatar_types['default'] = 'default';

		$d = dir(DISCUZ_ROOT.'./'.$avatar_base_path);
		while (false !== ($entry = $d->read())) {
			if ($entry == '.' || $entry == '..' || $entry == 'default') continue;

			$avatar_types[$entry] = $entry;
		}

		return $this
			->_setglobal('avatar_types', $avatar_types)
			->_getglobal('avatar_types')
		;
	}

	function _my_avatar_view_path($avatar_view_path = 'default') {
		$avatar_types = $this->_getglobal('avatar_types');

		$avatar_view_path = in_array($avatar_view_path, $avatar_types) ? $avatar_view_path : 'default';

		return $this
			->_setglobal('avatar_view_path', $avatar_view_path)
			->_getglobal('avatar_view_path')
		;
	}

	function _my_avatar_pics($view = null) {

		if (!$view) {
			$view = $this->_getglobal('avatar_view_path');
		}

		$imgexts = $this->_getglobal('imgexts');

		$path = $this->_getglobal('avatar_base_path')
			.$view;

		$avatar_pics = array();
		$d = dir($path);
		while (false !== ($entry = $d->read())) {
			if ($entry == '.' || $entry == '..'
				|| !in_array(fileext($entry), $imgexts)
			) continue;

			$avatar_pics[$entry] = $path.'/'.$entry;
		}

		return $this
			->_setglobal('avatar_pics', $avatar_pics)
			->_getglobal('avatar_pics')
		;
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