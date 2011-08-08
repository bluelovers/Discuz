<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('class/sco_dx_plugin', 'source', 'extensions/');

class plugin_sco_ajax extends _sco_dx_plugin {
	function plugin_sco_ajax() {
		$this->_init($this->_get_identifier(__METHOD__));
	}
}

class plugin_sco_ajax_forum extends plugin_sco_ajax {
	function ajax_viewthread() {
		$this->_hook('Script_forum_ajax:After_action_else', array(
				&$this,
				'_hook_ajax_viewthread'
		));
	}

	function _hook_ajax_viewthread() {
		global $_G;

		$this->_my_ajax_viewthread();

		extract($this->attr['global']);
		$plugin_self = &$this;

		include $this->_template('ajax_viewthread');

		dexit();
	}

	function _my_check_forum() {
		global $_G;

		$extraparam = array(
			'login' => 0,

			// Ajax 只顯示信息文本
			'msgtype' => 3,
			'showdialog' => false,
		);

		// 群組權限

		if ($_G['forum']['status'] == 3) {
			include_once libfile('function/group');
			$status = groupperm($_G['forum'], $_G['uid']);
			if($status == 1) {
				// 'forum_group_status_off' => '該{_G/setting/navs/3/navname}已關閉',
				showmessage('forum_group_status_off');
			} elseif($status == 2) {
				// 'forum_group_noallowed' => '抱歉，您沒有權限訪問該{_G/setting/navs/3/navname}',
				showmessage('forum_group_noallowed');
			} elseif($status == 3) {
				// 'forum_group_moderated' => '請等待群主審核',
				showmessage('forum_group_moderated');
			}
		}

		// 版塊權限

		if(empty($_G['forum']['allowview'])) {

			if(!$_G['forum']['viewperm'] && !$_G['group']['readaccess']) {
				showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 0));
			} elseif($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm'])) {
				showmessagenoperm('viewperm', $_G['fid'], null, $extraparam);
			}

		} elseif($_G['forum']['allowview'] == -1) {
			showmessage('forum_access_view_disallow');
		}

		// 版塊權限

		if($_G['forum']['formulaperm']) {
			formulaperm($_G['forum']['formulaperm'], $extraparam);
		}

		// 版塊密碼

		if($_G['forum']['password'] && $_G['forum']['password'] != $_G['cookie']['fidpw'.$_G['fid']]) {
			// 'forum_passwd_incorrect' => '抱歉，您輸入的密碼不正確，不能訪問這個版塊',
			showmessage('forum_passwd_incorrect', NULL);
		}

		// 閱讀權限

		if($_G['forum_thread']['readperm'] && $_G['forum_thread']['readperm'] > $_G['group']['readaccess'] && !$_G['forum']['ismoderator'] && $_G['forum_thread']['authorid'] != $_G['uid']) {
			showmessage('thread_nopermission', NULL, array('readperm' => $_G['forum_thread']['readperm']), array('login' => 0));
		}

		// 付費主題

		$threadtable = $_G['forum_thread']['threadtable'];

		$_G['forum_threadpay'] = FALSE;
		if($_G['forum_thread']['price'] > 0 && $_G['forum_thread']['special'] == 0) {
			if($_G['setting']['maxchargespan'] && TIMESTAMP - $_G['forum_thread']['dateline'] >= $_G['setting']['maxchargespan'] * 3600) {
				DB::query("UPDATE ".DB::table($threadtable)." SET price='0' WHERE tid='$_G[tid]'");
				$_G['forum_thread']['price'] = 0;
			} else {
				$exemptvalue = $_G['forum']['ismoderator'] ? 128 : 16;
				if(!($_G['group']['exempt'] & $exemptvalue) && $_G['forum_thread']['authorid'] != $_G['uid']) {
					$query = DB::query("SELECT relatedid FROM ".DB::table('common_credit_log')." WHERE relatedid='$_G[tid]' AND uid='$_G[uid]' AND operation='BTC'");
					if(!DB::num_rows($query)) {
						include_once libfile('thread/pay', 'include');
						$_G['forum_threadpay'] = TRUE;
					}
				}
			}
		}

		if ($_G['forum_threadpay'] == TRUE) {
			showmessage('thread_pay_error', NULL);
		}
	}

	function _my_ajax_viewthread() {
		$this->_my_check_forum();
	}

	/**
	 * @param array $key
	 *
	 * $key = array(
	 * 	'template' => 'forumdisplay',
	 * 	'message' => null,
	 *	'values' => null,
	 * )
	 */
	function forumdisplay_thread_output($key) {
		global $_G;

		// 不顯示給訪客使用
		if (!$_G['uid']) return;

		$this->_hook(
			'Tpl_Func_hooktags:Before',
			array(
				&$this,
				'_hook_forumdisplay_thread_output'
		));
	}

	function _hook_forumdisplay_thread_output($_EVENT, $hook_ret, $hook_id, $hook_key) {
		if ($hook_id != 'forumdisplay_thread') return;

		global $_G;

		$hook_ret = $this->_fetch_template($this->_template('forumdisplay_thread'), array(
			'_G' => &$_G,
			'thread' => &$_G['forum_threadlist'][$hook_key],

			'hook_key' => $hook_key,
		)).$hook_ret;
	}
}

?>