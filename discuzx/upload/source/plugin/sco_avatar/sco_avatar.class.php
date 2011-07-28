<?php

//TODO:增加可出售用戶的創意頭像
//TODO:增加可評分頭像
include_once '_sco_dx_plugin.class.php';

class plugin_sco_avatar extends _sco_dx_plugin {
	function plugin_sco_avatar() {
		$this->_init($this->_get_identifier(__METHOD__));

		// 追加的基準語言包
		$this->_lang_push('home');

		// set instance = $this
		$this->_this(&$this);

		$this
			// 設定允許的圖檔類型
			->_setglobal('imgexts', array('jpg', 'jpeg', 'gif', 'png', 'bmp'))
			// 設定 avatar 基礎目錄
			->_setglobal('avatar_base_path', $this->attr['directory'].'image/avatar/')
		;
	}

	/**
	 * 儲存 avatar
	 *
	 * @example $member_uc = $plugin_self->_my_avatar_user_save($_G['uid'], $_G['siteurl'].$a_file);
	 */
	function _my_avatar_user_save($uid, $url) {
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

	/**
	 * 取得 avatar
	 *
	 * @example $member_uc2 = $plugin_self->_my_avatar_user_get($_G['uid']);
	 */
	function _my_avatar_user_get($uid, $return = 0) {
		if ($uid <= 0) return false;

		$member_uc = $this
			->_uc_init()
			->_uc_call('sc', 'get_user_fields', array(
				'uid' => $uid,
				'fields'=> array(
					'avatar',
				),
		));

		if ($return) return $member_uc[$uid]['avatar'];

		return $member_uc;
	}

	/**
	 * 取得 avatar 目錄作為類型
	 */
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

	/**
	 * 設定查看的 avatar 類別目錄
	 *
	 * @example $plugin_self->_my_avatar_view_path(getgpc('avatar_view_path'))
	 */
	function _my_avatar_view_path($avatar_view_path = 'default') {
		$avatar_types = $this->_getglobal('avatar_types');

		$avatar_view_path = in_array($avatar_view_path, $avatar_types) ? $avatar_view_path : 'default';

		return $this
			->_setglobal('avatar_view_path', $avatar_view_path)
			->_getglobal('avatar_view_path')
		;
	}

	/**
	 * 取得 view 類別目錄下的 avatar 圖檔
	 *
	 * @example $plugin_self->_my_avatar_pics(
	$plugin_self->_my_avatar_view_path(getgpc('avatar_view_path'))
);
	 */
	function _my_avatar_pics($view = null) {

		if (!$view) {
			// 如果沒有指定 view 則使用預設值
			$view = $this->_getglobal('avatar_view_path');
		}

		$imgexts = $this->_getglobal('imgexts');

		// 取得要瀏覽的檔案目錄
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
			// 最後一次使用的檔案清單
			->_setglobal('avatar_pics', $avatar_pics)
			// 儲存目前的類別目錄名稱
			->_setglobal('avatar_pics_view', $view)
			// 依照類別來儲存檔案清單
			->_setglobal('avatar_pics_all/'.$view, $avatar_pics)
			->_getglobal('avatar_pics')
		;
	}

	function _my_parse_url_domain($url) {
		$_p = parse_url($url);
		$_p_h = explode('.', $_p['host']);

		$_1 = array_pop($_p_h);
		$_0 = array_pop($_p_h);

		return (empty($_0) || empty($_1)) ? '' : $_0.'.'.$_1;
	}

	function _my_fix_avatar_url($url) {
		global $_G;

		//TODO:增加可於插件設置內來設定允許的網域
		//TODO:新增自動修正各個網站的網址處理(防呆 防笨 防貼錯網址)

		// 允許使用的網站
		$domain_allow = array(
			'imgur.com',

			'imageshack.us',
			'photobucket.com',

			'hinet.net',

			'hotimg.com',
			'jnafo.com',

			// picasa
			'googleusercontent.com',
		);

		$domain = $this->_my_parse_url_domain($url);

		if (
			0
			// 如果沒有 domain
			|| empty($domain)

			|| strpos($url, 'http://') !== 0
			|| strpos($url, 'http://', 1) !== false

			// 不允許包含 /./ /../ 之類的網址
			|| preg_match('/\/\.+\//', $url)

			// 不允許使用跟網站的網域相同的網址
			|| $this->_my_parse_url_domain($_G['siteurl']) == $domain
			// 檢查是否在允許使用的網站
			|| !in_array($domain, $domain_allow)
		) {
			$url = '';
		}

		return $url;
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
		global $_G;

		$_G['mnid'] = 'mn_common';
		$actives = array('avatar' =>' class="a"');

		$_v = $this->_parse_method(__METHOD__);

		// 修正 setting 的內容
		$this->_fix_plugin_setting();

		$this->_setglobal('mod', $_v[2]);
		$this->_setglobal('ac', $_v[3]);

		// 檢查使用者是否登入
		if(empty($_G['uid'])) {
			extract($this->attr['global']);

			if($_SERVER['REQUEST_METHOD'] == 'GET') {
				dsetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
			} else {
				dsetcookie('_refer', rawurlencode('home.php?mod=spacecp&ac='.$ac));
			}
			showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
		}

		// 判定是否允許使用網路頭像
		$this->_setglobal('user_avatar_allowurl', (
			$_G['adminid'] == 1
			|| (
				$this->attr['setting']['allowurl']
				&& !in_array($_G['groupid'], (array)$this->attr['setting']['allowurl_not_usergroup'])
			)
		) ? 1 : 0);

		$this->_my_avatar_types_list();

		$avatar_pics = $this->_my_avatar_pics(
			$this->_my_avatar_view_path(getgpc('avatar_view_path'))
		);

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (submitcheck('reset_'.$this->identifier)) {
				$this->_uc_init();

				$member_uc = $this->_my_avatar_user_save($_G['uid'], '');

				if ($member_uc == 1) {
					// 一併刪除上傳的頭像
					uc_user_deleteavatar($_G['uid']);

					showmessage('成功重設頭像到預設', $this->_make_url(null, $_G['basescript']));
				} else {
					showmessage('發生錯誤! 請稍後再嘗試提交', null, null, array(
						'return' => 1,
					));
				}
			} elseif (submitcheck('submit_'.$this->identifier)) {
				//TODO:增加可設定站外頭像

				$a_file = getgpc('a_file');

				if (empty($a_file) || empty($avatar_pics[$a_file])) {
					unset($a_file);
				} else {
					$a_file = $avatar_pics[$a_file];
				}

				// 設定網路頭像
				if (empty($a_file) && $this->_getglobal('user_avatar_allowurl')) {
					unset($a_file);

					$a_file_url = trim(getgpc('a_file_url'));

					if (empty($a_file_url)
						|| strpos($a_file_url, 'http://') !== 0
					) {
						unset($a_file_url);
					} elseif ($a_file_url = $this->_my_fix_avatar_url($a_file_url)) {
						// 成功修正的 $a_file_url
					} else {
						unset($a_file_url);
					}
				}

				if (!empty($a_file)) {
					$this->_uc_init();

					// 先進行一次刪除頭像
					uc_user_deleteavatar($_G['uid']);

					$member_uc = $this->_my_avatar_user_save($_G['uid'], $_G['siteurl'].$a_file);

					showmessage('do_success', $this->_make_url(null, $_G['basescript']));

				} elseif (!empty($a_file_url)) {
					$this->_uc_init();
					uc_user_deleteavatar($_G['uid']);

					$member_uc = $this->_my_avatar_user_save($_G['uid'], $a_file_url);

					showmessage('do_success', $this->_make_url(null, $_G['basescript']));

				} else {
					showmessage('沒有選擇頭像或者錯誤的頭像請求', null, null, array(
						'return' => 1,
					));
				}

			}
		} elseif ($_G['adminid'] == 1) {
			//TODO:增加可上傳的用戶組
			// 如果是管理員額外允許使用原始的上傳頭像
			$this->_uc_init();
			$uc_avatarflash = uc_avatar($_G['uid'], 'virtual', 0);
		}

		// 取出值給模板使用
		extract($this->attr['global']);
		$plugin_self = &$this;

		include $this->_template('spacecp_avatar');

		/*
		var_dump(array(
			$this
		));
		*/

		exit();
	}
}

?>