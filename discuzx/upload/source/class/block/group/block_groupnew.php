<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_grouphot.php 6757 2010-03-25 09:01:29Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_group', 'class/block/group');

class block_groupnew extends block_group {

	function block_groupnew() {
		$this->setting = array(
			'gtids' => array(
				'title' => 'grouplist_gtids',
				'type' => 'mselect',
				'value' => array(
				),
			),
			'titlelength' => array(
				'title' => 'grouplist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength'	=> array(
				'title' => 'grouplist_summarylength',
				'type' => 'text',
				'default' => 80
			)
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_group_script_groupnew');
	}

	function cookparameter($parameter) {
		$parameter['orderby'] = 'dateline';
		return $parameter;
	}

}

?>