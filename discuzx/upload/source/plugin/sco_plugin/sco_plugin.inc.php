<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

include_once libfile('class/sco_dx_plugin', 'source', 'extensions/');

class plugin_sco_plugin extends _sco_dx_plugin {

	public function __construct() {
		$this->_init($this->_get_identifier(__CLASS__));
		$this->_this(&$this);
	}

}

class plugin_sco_plugin_plugin extends plugin_sco_plugin {

	function plugin_message() {
		$query = DB::query("SELECT
				*
			FROM
				".DB::table('common_plugin')."
			WHERE
				available = '1'
			ORDER BY
				available DESC
				, identifier LIKE '%sco\_%' DESC
				, pluginid DESC
		");

		$plugin_lists = array();

		while($plugin = DB::fetch($query)) {
			$plugin_lists[] = $plugin;
		}

		global $_G;
		$this->_setglobal('plugin_lists', $plugin_lists);
		$this->_setglobal('navigation', '<em>&raquo;</em>'.'Scorpio! Plugin Center');

		/*
		ob_start();
		echo $this->_fetch_template($this->_template('plugin_index'), $this->attr['global']);
		*/

		extract($this->attr['global']);
		$plugin_self = &$this;
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

			$sarray[$row['styleid']] = $style;
		}

		$this->_setglobal('style_defaultid', $styleid_default);
		$this->_setglobal('style_lists', $sarray);
	}

}

$_o = new plugin_sco_plugin_plugin();

$_o->plugin_message();

?>