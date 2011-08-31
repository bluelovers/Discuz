<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

//error_reporting(E_ALL ^E_NOTICE ^E_STRICT);

include_once 'sco_cpanel.class.php';

class plugin_sco_cpanel_threadsorts extends plugin_sco_cpanel {
	var $attr = array();

	function plugin_sco_cpanel_threadsorts() {
	}

	function set($attr) {
		$this->attr = $attr;
		return $this;
	}

	function run() {
		$operation = $this->attr['op'];

		$operation = $operation ? $operation : 'default';

		$method = 'on_op_'.$operation;

		cpheader();

		$this->$method();

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

		showformheader("plugins&operation=config&do=11&identifier=sco_cpanel&pmod=sco_cpanel&");
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
	))
	->run()
;

?>