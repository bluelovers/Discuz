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

	function _my_allow_use() {
		global $_G;

		$ret = false;

		if (
			$_G['uid']
		) {
			$ret = true;
		}

		return $ret;
	}

}

class plugin_sco_style_home extends plugin_sco_style {

	function space__output() {
		if (!$this->_my_check_in_space_style()) {
			return;
		}

		$this->_dx_hook_value_add('global_header_javascript_before_body', $this->_my_global_header_javascript_before_body());
		$this->_dx_hook_value_add('global_header_javascript_before_body', $this->_my_home_space_diy_controlpanel_controlcontent());
	}

	function _my_home_space_diy_controlpanel_controlcontent() {
		discuz_core::$tpl['home_space_diy_controlpanel_controlnav_diy'] = true;

		return $this->_fetch_template($this->_template('home_space_diy_controlpanel_controlcontent'));
	}

	function _my_global_header_javascript_before_body() {
		global $_G, $space;

		$uid = $space['uid'];

		$theme = $this->_my_theme_get_by_uid($uid);

		return '<style id="diy_style_plugin">'.($theme['theme_disable'] ? '' : $theme['theme_css']).'</style>';
	}

	function _my_theme_get_by_uid($uid, $limit = 1) {

		$uid = intval($uid);

		$ret = array();

		if (empty($uid)) return $ret;

		if ($limit < 0) {
			$limitsql = '';
		} else {
			$limit = max(1, intval($limit));

			$limitsql = "LIMIT {$limit}";
		}

		$query = DB::query("SELECT td.*, tu.uid, tu.theme_disable
			FROM ".DB::table('home_theme_diy')." td
			LEFT JOIN ".DB::table('home_theme_user')." tu On (tu.uid = '{$uid}' AND tu.theme_id = td.theme_id)
			WHERE td.theme_authorid = '{$uid}' $limitsql");
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

			&& $this->_my_allow_use()
		) {
			/**
			 * @todo 在此 hack 掉 窩窩 DIY 的裝扮
			 * @link home.php?mod=spacecp&ac=index&op=diy&inajax=1&ajaxtarget=
			 */

			include_once libfile('function/space');
			include_once libfile('function/portalcp');
			include_once libfile('function/space');

			$themes = gettheme('space');

			global $space;

			$uid = $_G['uid'];
			$space = getspace($uid);
			space_merge($space, 'field_home');

			$userdiy = getuserdiydata($space);

			if (submitcheck('themesubmit')) {
				$my_theme = $this->_my_theme_get_by_uid($uid);

				$_G['gp_theme_css'] = $this->_my_spacecss($_G['gp_theme_css']);

				$my_theme['theme_name'] = $this->_my_getcssname($_G['gp_theme_css']);

				if ($my_theme['theme_id']) {

					DB::update('home_theme_diy', array(
						'theme_css' => $_G['gp_theme_css'],

						'theme_name' => $my_theme['theme_name'],
					), array(
						'theme_id' => $my_theme['theme_id'],
					));

				} else {

					$my_theme['theme_id'] = DB::insert('home_theme_diy', array(
						'theme_css' => $_G['gp_theme_css'],
						'theme_authorid' => $uid,

						'theme_name' => $my_theme['theme_name'],
					), 1);

					$my_theme['theme_css'] = $_G['gp_theme_css'];

				}

				DB::insert('home_theme_user', array(
					'uid' => $uid,
					'theme_id' => $my_theme['theme_id'],
					'theme_disable' => $_G['gp_theme_disable'] ? 1 : 0,
				), 0, 1);

				$my_theme['theme_css'] = $_G['gp_theme_css'];

				dheader('location: home.php?mod=space');

				exit();
			}

			$this->_dx_hook_value_add('global_header_javascript_before_body', $this->_my_global_header_javascript_before_body());

			$my_theme = $this->_my_theme_get_by_uid($uid);

			$widths = getlayout($userdiy['currentlayout']);
			$leftlist = $this->_my_formatdata($userdiy, 'left', $space);
			$centerlist = $this->_my_formatdata($userdiy, 'center', $space);
			$rightlist = $this->_my_formatdata($userdiy, 'right', $space);

			include $this->_template('home_space_diy_from');
			dexit();
		}
	}

	function _my_getcssname($css) {
		if ($css) {
			preg_match("/\[name\](.+?)\[\/name\]/i", trim($css), $mathes);
			if(!empty($mathes[1])) $name = dhtmlspecialchars($mathes[1]);
		}

		if (empty($name)) {
			$name = 'No name';
		}

		return $name;
	}

	function _my_formatdata($data, $position, $space) {
		$list = array();
		foreach ((array)$data['block']['frame`frame1']['column`frame1_'.$position] as $blockname => $blockdata) {
			if (strpos($blockname, 'block`') === false || empty($blockdata) || !isset($blockdata['attr']['name'])) continue;
			$name = $blockdata['attr']['name'];
			if(check_ban_block($name, $space)) {
				$list[$name] = getblockhtml($name, $data['parameters'][$name]);
			}
		}
		return $list;
	}

	function _my_spacecss($css) {



		return $css;
	}

}

?>