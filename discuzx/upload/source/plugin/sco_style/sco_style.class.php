<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

include_once libfile('class/sco_dx_plugin', 'source', 'extensions/');

class plugin_sco_style extends _sco_dx_plugin {

	public function __construct() {
		$this->_init($this->_get_identifier(__CLASS__));

		$this->_this(&$this);
	}

}

class plugin_sco_style_home extends plugin_sco_style {

	function space__output() {
		if (!$this->_my_check_in_space_style()) {
			return;
		}

		$this->_dx_hook_value_add('global_header_javascript_before_body', $this->_my_global_header_javascript_before_body());
		$this->_dx_hook_value_add('home_space_diy_controlpanel_controlcontent', $this->_my_home_space_diy_controlpanel_controlcontent());
	}

	function _my_home_space_diy_controlpanel_controlcontent() {
		discuz_core::$tpl['home']['space']['diy']['diy'] = true;

		return $this->_fetch_template('_my_home_space_diy_controlpanel_controlcontent');
	}

	function _my_global_header_javascript_before_body() {
		global $_G, $space;

		$uid = $space['uid'];

		$theme = $this->_my_theme_get_by_uid($uid);

		return '<style id="diy_style_plugin">'.$theme['theme_css'].'</style>';
	}

	function _my_theme_get_by_uid($uid, $limit = 1) {

		$uid = intval($uid);
		if ($limit < 0) {
			$limitsql = '';
		} else {
			$limit = max(1, intval($limit));

			$limitsql = "LIMIT {$limit}";
		}

		$ret = array();

		$query = DB::query("SELECT * FROM ".DB::table('home_theme_diy')." WHERE theme_authorid = '{$uid}' $limitsql");
		while ($theme = DB::fetch($query)) {
			$ret[$theme['theme_id']] = $theme;
		}

		if ($limit == 1) {
			return current($ret);
		} else {
			return $ret;
		}
	}

	function _my_check_in_space_style() {
		global $_G;

		$ret = false;

		if (
			(
				CURMODULE == 'space'
				&& $_G['setting']['homestatus']
			)
		) {
			$ret = true;
		}

		return $ret;
	}

	function spacecp_index_diy() {
		global $_G;

		$_v = $this->_parse_method(__METHOD__, 1);

		if (
			$_G['gp_ac'] == $_v[3]
			&& $_G['gp_op'] == $_v[4]
		) {
			/**
			 * @todo 在此 hack 掉 窩窩 DIY 的裝扮
			 * @link home.php?mod=spacecp&ac=index&op=diy&inajax=1&ajaxtarget=
			 */
			dexit($_v);
		}
	}

}

?>