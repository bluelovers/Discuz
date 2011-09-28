<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_sco_plugin_plugin extends plugin_sco_plugin_inc {

	function on_op_default() {
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
		include $this->_template('plugin_index');
	}

}

?>