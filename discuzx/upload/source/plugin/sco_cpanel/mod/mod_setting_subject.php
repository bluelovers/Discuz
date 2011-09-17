<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_sco_cpanel_setting_subject extends plugin_sco_cpanel {

	function _my_strlen($str) {
		if(strtolower(CHARSET) == 'utf-8') return self::_my_utf8_strlen($str);

		return dstrlen($str);
	}

	function _my_utf8_strlen($str) {
		return preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $str, $dummy);
	}

	/**
	 * 預設行為
	 */
	function on_op_default() {
		$_tables_map = array(
			'polloption' => array(
				'varchar' => array(
					'forum_polloption',
				),
			),

			'subject' => array(
				'char' => array(
					'forum_forumrecommend',
					'forum_rsscache',
					'forum_thread',
					'home_blog',
					'portal_rsscache',
				),
				'varchar' => array(
					'forum_post',
				),
			),
		);

		$_table_field = array();

		foreach ($_tables_map as $_k => $_v) {
			foreach ($_v as $_c => $_ts) {
				$_tableinfo = $this->_db()->loadtable($_ts);

				foreach ($_ts as $_t) {
					$_table_field[$_t][$_k] = $_tableinfo[$_t][$_k];
				}
			}
		}

		var_dump($_table_field);

		return $this;
	}
}

?>