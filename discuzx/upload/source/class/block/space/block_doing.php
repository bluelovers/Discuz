<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_doing.php 10887 2010-05-18 02:11:40Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class block_doing {
	var $setting = array();
	function block_doing() {
		$this->setting = array(
			'uids'	=> array(
				'title' => 'doinglist_uids',
				'type' => 'text',
				'value' => ''
			),
			'titlelength' => array(
				'title' => 'doinglist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'orderby' => array(
				'title' => 'doinglist_orderby',
				'type' => 'mradio',
				'value' => array(
					array('dateline', 'doinglist_orderby_dateline'),
					array('replynum', 'doinglist_orderby_replynum')
				),
				'default' => 'dateline'
			),
			'startrow' => array(
				'title' => 'doinglist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_doing_script_doing');
	}

	function blockclass() {
		return array('doing', lang('blockclass', 'blockclass_space_doing'));
	}

	function fields() {
		return array(
				'url' => array('name' => lang('blockclass', 'blockclass_doing_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
				'title' => array('name' => lang('blockclass', 'blockclass_doing_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
				'uid' => array('name' => lang('blockclass', 'blockclass_doing_field_uid'), 'formtype' => 'text', 'datatype' => 'pic'),
				'username' => array('name' => lang('blockclass', 'blockclass_doing_field_username'), 'formtype' => 'text', 'datatype' => 'string'),
				'avatar' => array('name' => lang('blockclass', 'blockclass_doing_field_avatar'), 'formtype' => 'text', 'datatype' => 'string'),
				'avatar_middle' => array('name' => lang('blockclass', 'blockclass_doing_field_avatar_middle'), 'formtype' => 'text', 'datatype' => 'string'),
				'avatar_big' => array('name' => lang('blockclass', 'blockclass_doing_field_avatar_big'), 'formtype' => 'text', 'datatype' => 'string'),
				'dateline' => array('name' => lang('blockclass', 'blockclass_doing_field_dateline'), 'formtype' => 'date', 'datatype' => 'date'),
				'replynum' => array('name' => lang('blockclass', 'blockclass_doing_field_replynum'), 'formtype' => 'text', 'datatype' => 'int'),
			);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		return $settings;
	}

	function cookparameter($parameter) {
		return $parameter;
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);
		$uids		= isset($parameter['uids']) && !in_array(0, (array)$parameter['uids']) ? $parameter['uids'] : '';
		$startrow	= isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= isset($parameter['items']) ? intval($parameter['items']) : 10;
		$titlelength = intval($parameter['titlelength']);
		$orderby	= isset($parameter['orderby']) && in_array($parameter['orderby'],array('dateline', 'replynum')) ? $parameter['orderby'] : 'dateline';

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		$datalist = $list = array();
		$wheres = array();
		if($uids) {
			$wheres[] = 'uid IN ('.dimplode($uids).')';
		}
		if($bannedids) {
			$wheres[] = 'doid NOT IN ('.dimplode($bannedids).')';
		}
		$wheres[] = " status = '0'";
		$wheresql = $wheres ? implode(' AND ', $wheres) : '1';
		$query = DB::query("SELECT * FROM ".DB::table('home_doing')." WHERE $wheresql ORDER BY $orderby DESC LIMIT $startrow,$items");
		while($data = DB::fetch($query)) {
			$datalist = array(
				'id' => $data['doid'],
				'idtype' => 'doid',
				'title' => cutstr(strip_tags($data['message']), $titlelength, ''),
				'url' => 'home.php?mod=space&uid='.$data['uid'].'&do=doing&doid='.$data['doid'],
				'pic' => '',
				'summary' => '',
				'fields' => array(
					'fulltitle' => strip_tags($data['message']),
					'uid' => $data['uid'],
					'username' => $data['username'],
					'avatar' => avatar($data['uid'], 'small', true, false, false, $_G['setting']['ucenterurl']),
					'avatar_middle' => avatar($data['uid'], 'middle', true, false, false, $_G['setting']['ucenterurl']),
					'avatar_big' => avatar($data['uid'], 'big', true, false, false, $_G['setting']['ucenterurl']),
					'dateline'=>$data['dateline'],
					'replynum'=>$data['replynum'],
				)
			);
			if($titlelength) {
				$datalist['title'] = cutstr(strip_tags($data['message']), $titlelength);
			} else {
				$datalist['title'] = strip_tags($data['message'], '<img>');
			}
			$list[] = $datalist;
		}
		return array('html' => '', 'data' => $list);
	}
}

?>