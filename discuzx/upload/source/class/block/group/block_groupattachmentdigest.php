<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_groupattachmentdigest.php 6994 2010-03-27 14:52:10Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_groupattachment', 'class/block/group');

class block_groupattachmentdigest extends block_groupattachment {
	function block_groupattachmentdigest() {
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
			'isimage' => array(
				'title' => 'groupattachment_isimage',
				'type' => 'mradio',
				'value' => array(
					array(0, 'groupattachment_isimage_0'),
					array(1, 'groupattachment_isimage_1'),
					array(2, 'groupattachment_isimage_2')
				),
				'default' => 0
			),
			'threadmethod' => array(
				'title' => 'groupattachment_threadmethod',
				'type' => 'radio',
				'default' => 0
			),
			'digest' => array(
				'title' => 'groupattachment_digest',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'groupattachment_digest_1'),
					array(2, 'groupattachment_digest_2'),
					array(3, 'groupattachment_digest_3'),
					array(0, 'groupattachment_digest_0')
				),
			),
			'gviewperm' => array(
				'title' => 'groupattachment_gviewperm',
				'type' => 'mradio',
				'value' => array(
					array('0', 'groupattachment_gviewperm_only_member'),
					array('1', 'groupattachment_gviewperm_all_member')
				),
				'default' => '1'
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
		return lang('blockclass', 'blockclass_groupattachment_script_groupattachmentdigest');
	}

	function cookeparameter($parameter) {
		$parameter['orderby'] = 'dateline';
		return $parameter;
	}
}