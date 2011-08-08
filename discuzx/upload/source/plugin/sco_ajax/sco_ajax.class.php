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
				'_my_ajax_viewthread'
		));
	}

	function _my_ajax_viewthread() {

		extract($this->attr['global']);
		$plugin_self = &$this;

		include $this->_template('ajax_viewthread');

		dexit();
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
		$this->_hook(
			'Tpl_Func_hooktags:Before',
			array(
				&$this,
				'_forumdisplay_thread_output'
		));
	}

	function _forumdisplay_thread_output($_EVENT, $hook_ret, $hook_id, $hook_key) {
		if ($hook_id != 'forumdisplay_thread') return;

		global $_G;

		$hook_ret = $this->_fetch_template($this->_template('forumdisplay_thread'), array(
			'thread' => &$_G['forum_threadlist'][$hook_key],
		)).$hook_ret;
	}
}

?>