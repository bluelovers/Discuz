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

		include $this->_template('ajax_viewthead');

		dexit();
	}
}

?>