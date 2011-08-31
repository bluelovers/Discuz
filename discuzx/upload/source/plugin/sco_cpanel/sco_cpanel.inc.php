<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

//error_reporting(E_ALL ^E_NOTICE ^E_STRICT);

include_once dirname(__FILE__).'/./sco_cpanel.class.php';

class plugin_sco_cpanel_threadsorts extends plugin_sco_cpanel {

	function plugin_sco_cpanel_threadsorts() {
		global $plugin, $module;
		$this->_init($plugin['identifier']);

		$this->_this(&$this);

		$this->_fix_plugin_setting();
		$this->attr['profile'] = $this->attr['db']['common_plugin'];
	}

	function set($attr) {
		$this->attr['global'] = $attr;
		return $this;
	}

	function run() {
		$operation = $this->attr['global']['op'];

		$operation = $operation ? $operation : 'default';

		$method = 'on_op_'.$operation;

		cpheader();

		$this->$method();

		if ($this->_getglobal('debug')) {
			var_dump($this);
		}

		cpfooter();
	}

	function on_op_default() {
		global $lang;

		$threadtypes = '';

		$query = DB::query("SELECT * FROM ".DB::table('forum_typeoption')." WHERE classid='0' ORDER BY displayorder");
		while($option = DB::fetch($query)) {
			$threadtypes .= showtablerow('', array('class="td25"', 'class="td28"', '', 'class="td25"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$option[optionid]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$option[optionid]]\" value=\"$option[displayorder]\">",
				"<input type=\"text\" class=\"txt\" size=\"15\" name=\"namenew[$option[optionid]]\" value=\"".dhtmlspecialchars($option['title'])."\">",
				"<a href=\"".ADMINSCRIPT."?action=threadtypes&operation=typeoption&classid=$option[optionid]\" class=\"act nowrap\">$lang[detail]</a>"
			), TRUE);
		}

		showformheader("plugins&operation=config&do=".$this->attr['profile']['pluginid']."&identifier=".$this->identifier."&pmod=".$this->attr['global']['module']['name']."&");
		showtableheader('threadtype_infotypes');
		showsubtitle(array('', 'display_order', 'name', ''));
		echo $threadtypes;
		showsubmit('typesubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();
	}
}

$_cpanel = new plugin_sco_cpanel_threadsorts();

$_cpanel
	->set(array(
		'op' => $_G['gp_op'],
		'debug' => 1,
		'module' => &$module,
	))
	->run()
;

?>