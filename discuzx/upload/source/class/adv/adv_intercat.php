<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: adv_intercat.php 16548 2010-09-08 09:00:51Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class adv_intercat {

	var $version = '1.0';
	var $name = 'intercat_name';
	var $description = 'intercat_desc';
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $targets = array('forum');
	var $imagesizes = array('468x60', '658x60', '728x90', '760x90', '950x90');

	function getsetting() {
		global $_G;
		$settings = array(
			'fids' => array(
				'title' => 'intercat_fids',
				'type' => 'mselect',
				'value' => array(),
			),
			'position' => array(
				'title' => 'intercat_position',
				'type' => 'mradio',
				'value' => array(),
				'default' => 0,
			),
		);
		loadcache('forums');
		$settings['fids']['value'][] = array(0, '&nbsp;');
		$settings['position']['value'][] = array(0, 'intercat_position_random');
		if(empty($_G['cache']['forums'])) $_G['cache']['forums'] = array();
		foreach($_G['cache']['forums'] as $fid => $forum) {
			if($forum['type'] == 'group') {
				$settings['fids']['value'][] = array($fid, $forum['name']);
				$settings['position']['value'][] = array($fid, $forum['name']);
			}
		}

		return $settings;
	}

	function setsetting(&$advnew, &$parameters) {
		global $_G;
		if(is_array($advnew['targets'])) {
			$advnew['targets'] = implode("\t", $advnew['targets']);
		}
		if(is_array($parameters['extra']['fids']) && in_array(0, $parameters['extra']['fids'])) {
			$parameters['extra']['fids'] = array();
		}
	}

	function evalcode() {
		return array(
			'check' => '
			if(!($parameter[\'position\'] && $params[2] == $parameter[\'position\'] || $parameter[\'fids\'] && in_array($_G[\'gp_gid\'], $parameter[\'fids\']))) {
				$checked = false;
			}',
			'create' => '$adcode = $codes[$adids[array_rand($adids)]];',
		);
	}

}

?>