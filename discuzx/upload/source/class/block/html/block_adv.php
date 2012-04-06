<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_adv.php 7212 2010-03-30 13:05:47Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('commonblock_html', 'class/block/html');

class block_adv extends commonblock_html {

	function block_adv() {}

	function name() {
		return lang('blockclass', 'blockclass_html_script_adv');
	}

	function getsetting() {
		global $_G;
		$settings = array(
			'adv' => array(
				'title' => 'adv_adv',
				'type' => 'mradio',
				'value' => array(),
			),
			'title' => array(
				'title' => 'adv_title',
				'type' => 'text',
			)
		);
		$query = DB::query('SELECT * FROM '.DB::table('common_advertisement_custom'));
		while($value=DB::fetch($query)) {
			$settings['adv']['value'][] = array($value['name'], $value['name']);
		}
		return $settings;
	}

	function getdata($style, $parameter) {
		$advid = 0;
		if(!empty($parameter['title'])) {
			$parameter['title'] = addslashes($parameter['title']);
			$adv = DB::fetch_first('SELECT * FROM '.DB::table('common_advertisement_custom')." WHERE name='$parameter[title]'");
			if(empty($adv)) {
				$advid = DB::insert('common_advertisement_custom', array('name'=>$parameter['title']), 1);
			} else {
				$advid = $adv['id'];
			}
		} elseif(!empty($parameter['adv'])) {
		   $parameter['adv'] = addslashes($parameter['adv']);
		   $adv = DB::fetch_first('SELECT * FROM '.DB::table('common_advertisement_custom')." WHERE name='$parameter[adv]'");
		   $advid = intval($adv['id']);
		} else {
			$return = 'Empty Ads';
		}
		if($advid) {
			$flag = false;
			if(getglobal('inajax')) {
				$flag = true;
				setglobal('inajax', 0);
			}
			$return = adshow('custom_'.$advid);
			if($flag) setglobal('inajax', 1);
		}
		return array('html' => $return, 'data' => null);
	}
}

?>