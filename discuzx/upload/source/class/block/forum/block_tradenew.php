<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_tradenew.php 7979 2010-04-15 06:52:32Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_trade', 'class/block/forum');

class block_tradenew extends block_trade {
	function block_tradenew() {
		$this->setting = array(
			'fids'	=> array(
				'title' => 'tradelist_fids',
				'type' => 'mselect',
				'value' => array()
			),
			'viewmod' => array(
				'title' => 'threadlist_viewmod',
				'type' => 'radio'
			),
			'titlelength' => array(
				'title' => 'tradelist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'tradelist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'tradelist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_trade_script_tradenew');
	}

	function cookparameter($parameter) {
		$parameter['orderby'] = 'dateline';
		return $parameter;
	}
}

?>