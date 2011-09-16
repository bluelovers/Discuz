<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once dirname(__FILE__).'/./class_sco_dx_plugin_admincp.php';

class plugin_sco_cpanel extends _sco_dx_plugin_admincp {

	function cpheader() {
		/*
		global $lang;
		*/

		parent::cpheader();

		$url = "plugins&operation=config&do=".$this->attr['profile']['pluginid']."&identifier=".$this->identifier."&pmod=".$this->attr['global']['module']['name']."&";

		$op_list = array(
			'threadsorts' => 'threadsorts',

			'setting_subject' => 'setting_subject',
		);

		echo '<div class="extcredits" style="margin: 0px;"><ul class="rowform">';
		foreach ($op_list as $key => $name) {
			echo '<li><a href="'.ADMINSCRIPT."?action=".$url.'&cpmod='.$key.'"><span>'.$name.'</span></a></li>';
		}
		echo '</ul></div>';

		return $this;
	}

}

?>