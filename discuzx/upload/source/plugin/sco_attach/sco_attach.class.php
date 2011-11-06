<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('class/sco_dx_plugin', 'source', 'extensions/');

class plugin_sco_attach extends _sco_dx_plugin {

	function __construct() {
		$this->identifier = $this->_get_identifier(__CLASS__);
	}

}

class plugin_sco_attach_forum extends plugin_sco_attach {

	function attachment_message() {
		$_v = $this->_parse_method(__METHOD__, 1);

		if (
			CURSCRIPT == $_v[1]
			&& CURMODULE == $_v[2]
		) {
			$this->_hook(
				'Func_dshowmessage:Before_custom', array(
					$this,
					'_my_hook_attachment_message'
			));
		}
	}

	function _my_hook_attachment_message($_EVENT, $_conf) {
		global $_G;

		if (!empty($_G['forum_attach_filename'])) {
			$_conf['_data_dshowmessage_']['navtitle'] =
				lang('template', 'e_attach')
				. ': '
				. $_G['forum_attach_filename']
				. ' - '
				. $_conf['navtitle']
			;

			$_conf['_data_dshowmessage_']['navigation'] =
				'<em>&raquo;</em> '
				. '<span>' . lang('template', 'e_attach'). '</span> '
				. '<em>&raquo;</em> '
				. '<span>' . $_G['forum_attach_filename']. '</span> '
			;
		}
	}

}

?>