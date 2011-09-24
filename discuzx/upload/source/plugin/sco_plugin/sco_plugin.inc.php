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

		/*
		ob_start();
		echo $this->_fetch_template($this->_template('plugin_index'), $this->attr['global']);
		*/

		extract($this->attr['global']);
		$plugin_self = &$this;
		include $this->_template('plugin_index');
	}

}

$_o = new plugin_sco_plugin_plugin();

$_o->plugin_message();

?>