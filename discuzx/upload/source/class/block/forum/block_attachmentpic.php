<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_attachmentpic.php 6994 2010-03-27 14:52:10Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_attachment', 'class/block/forum');

class block_attachmentpic extends block_attachment {

	function block_attachmentpic() {
		$this->settings = array(
			'fids'	=> array(
				'title' => 'attachmentlist_fids',
				'type' => 'mselect',
				'value' => array()
			),
			'special' => array(
				'title' => 'attachmentlist_special',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'attachmentlist_special_1'),
					array(2, 'attachmentlist_special_2'),
					array(3, 'attachmentlist_special_3'),
					array(4, 'attachmentlist_special_4'),
					array(5, 'attachmentlist_special_5'),
					array(0, 'attachmentlist_special_0'),
				)
			),
			'rewardstatus' => array(
				'title' => 'attachmentlist_special_reward',
				'type' => 'mradio',
				'value' => array(
					array(0, 'attachmentlist_special_reward_0'),
					array(1, 'attachmentlist_special_reward_1'),
					array(2, 'attachmentlist_special_reward_2')
				),
				'default' => 0,
			),
			'threadmethod' => array(
				'title' => 'attachmentlist_threadmethod',
				'type' => 'radio',
				'default' => 0
			),
			'titlelength' => array(
				'title' => 'attachmentlist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'attachmentlist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'attachmentlist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_attachment_script_attachmentpic');
	}

	function cookparameter($parameter) {
		$parameter['isimage'] = '1';
		return $parameter;
	}
}