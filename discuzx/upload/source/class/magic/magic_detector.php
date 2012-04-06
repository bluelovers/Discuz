<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: magic_money.php 7830 2010-04-14 02:22:32Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class magic_detector {

	var $version = '1.0';
	var $name = 'detector_name';
	var $description = 'detector_desc';
	var $price = '20';
	var $weight = '20';
	var $useevent = 0;
	var $targetgroupperm = false;
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $magic = array();
	var $parameters = array();

	function getsetting(&$magic) {
		$settings = array(
			'num' => array(
				'title' => 'detector_num',
				'type' => 'select',
				'value' => array(
					array('5', '5'),
					array('10', '10'),
					array('20', '20'),
				),
				'default' => '10'
			),
		);
		return $settings;
	}

	function setsetting(&$magicnew, &$parameters) {
		$magicnew['num'] = in_array($parameters['num'], array(5,10,20,50)) ? intval($parameters['num']) : '10';
	}

	function usesubmit() {
		global $_G;

		$list = $uids = array();
		$num = !empty($this->parameters['num']) ? intval($this->parameters['num']) : 10;
		$limit = $num + 20;
		loadcache('magics');
		$mid = !empty($_G['magics']['gift']) ? intval($_G['magics']['gift']['magicid']) : 0;
		if($mid) {
			$query = DB::query('SELECT * FROM '.DB::table('common_magiclog')." WHERE magicid = '$mid' AND action='2' AND uid != '$_G[uid]' ORDER BY dateline DESC LIMIT 0,$limit");
			while($value=DB::fetch($query)) {
				$uids[] = intval($value['uid']);
			}
		}
		if($uids) {
			$counter = 0;
			$query = DB::query('SELECT m.username, mfh.uid, mfh.magicgift FROM '.DB::table('common_member')." m LEFT JOIN ".DB::table('common_member_field_home')." mfh USING(uid) WHERE m.uid IN (".dimplode($uids).")");
			while($value=DB::fetch($query)) {
				$info = !empty($value['magicgift']) ? unserialize($value['magicgift']) : array();
				if(!empty($info['left']) && (empty($info['receiver']) || !in_array($_G['uid'], $info['receiver']))) {
					$value['avatar'] = addcslashes(avatar($value['uid'], 'small'), "'");
					$list[$value['uid']] = $value;
					$counter++;
					if($counter>=$num) {
						break;
					}
				}
			}
		}
		usemagic($this->magic['magicid'], $this->magic['num']);
		updatemagiclog($this->magic['magicid'], '2', '1', '0', '0', 'uid', $_G['uid']);

		$op = 'show';
		include template('home/magic_detector');
	}

	function show() {
		global $_G;
		$num = !empty($this->parameters['num']) ? intval($this->parameters['num']) : 10;
		magicshowtips(lang('magic/detector', 'detector_info', array('num'=>$num)));
	}
}

?>