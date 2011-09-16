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

		$url = "plugins&operation=config&do=".$this->attr['profile']['pluginid']."&identifier=".$this->identifier."&pmod=".$this->attr['global']['module']['name']."&cpmod=".$this->attr['global']['mod']."&";

		$op_list = array(
			'list_fups' => $this->cplang('threadtype_infotypes'),
		);

		echo '<div class="extcredits" style="margin: 0px;"><ul class="rowform">';
		foreach ($op_list as $key => $name) {
			echo '<li'.($this->attr['global']['op'] == $key ? ' class="current" style="font-weight: bold;"' : '').'><a href="'.ADMINSCRIPT."?action=".$url.'&op='.$key.'"><span>'.$name.'</span></a></li>';
		}
		echo '</ul></div>';

		return $this;
	}

	function on_op_default() {
		/*
		$this->on_op_list_fups();
		*/

		return $this;
	}

}

?>