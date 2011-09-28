<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_sco_plugin_style extends plugin_sco_plugin_inc {

	function on_op_default() {
		$this->_my_get_stylelist();

		$this->_setglobal('plugin_self', &$this);

		global $_G;
  		extract($this->attr['global']);

		include $this->_template('plugin_index');
	}

	function _my_get_stylelist() {
		global $_G;

		$sarray = array();
		$styleid_default = $_G['setting']['styleid'];

		$query = DB::query("SELECT
				s.styleid, s.available, s.name, t.name AS tplname, t.directory, t.copyright
			FROM ".DB::table('common_style')." s
			LEFT JOIN ".DB::table('common_template')." t ON t.templateid=s.templateid
			WHERE
				s.available = '1'
			ORDER BY
				s.available desc
				, s.styleid = '$styleid_default' DESC
				, s.name ASC
				, s.styleid
		");

		while($style = DB::fetch($query)) {

			$preview = file_exists($style['directory'].'/preview.jpg') ? $style['directory'].'/preview.jpg' : './static/image/admincp/stylepreview.gif';
			$previewlarge = file_exists($style['directory'].'/preview_large.jpg') ? $style['directory'].'/preview_large.jpg' : '';

			$style['preview'] = $preview;
			$style['previewlarge'] = $previewlarge;

			$sarray[$style['styleid']] = $style;
		}

		$this->_setglobal('style_defaultid', $styleid_default);
		$this->_setglobal('style_lists', $sarray);
	}

}

?>