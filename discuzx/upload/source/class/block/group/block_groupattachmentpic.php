<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_groupattachmentpic.php 6994 2010-03-27 14:52:10Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_groupattachment', 'class/block/group');

class block_groupattachmentpic extends block_groupattachment {
	function block_groupattachmentpic() {
		$this->settings = array(
			'gtids' => array(
				'title' => 'groupattachment_gtids',
				'type' => 'mselect',
				'value' => array(
				),
			),
			'special' => array(
				'title' => 'groupattachment_special',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'groupattachment_special_1'),
					array(2, 'groupattachment_special_2'),
					array(3, 'groupattachment_special_3'),
					array(4, 'groupattachment_special_4'),
					array(5, 'groupattachment_special_5'),
					array(0, 'groupattachment_special_0'),
				)
			),
			'rewardstatus' => array(
				'title' => 'groupattachment_special_reward',
				'type' => 'mradio',
				'value' => array(
					array(0, 'groupattachment_special_reward_0'),
					array(1, 'groupattachment_special_reward_1'),
					array(2, 'groupattachment_special_reward_2')
				),
				'default' => 0,
			),
			'threadmethod' => array(
				'title' => 'groupattachment_threadmethod',
				'type' => 'radio',
				'default' => 0
			),
			'titlelength' => array(
				'title' => 'groupattachment_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'groupattachment_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'groupattachment_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_groupattachment_script_groupattachmentpic');
	}

	function cookparameter($parameter) {
		$parameter['isimage'] = '1';
		return $parameter;
	}
}