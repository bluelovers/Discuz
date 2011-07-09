<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class adv_pdnovel {

	var $version = '1.0';
	var $name = 'pdnovel_name';
	var $description = 'pdnovel_desc';
	var $copyright = '<a href="http://www.dke8.com/forum-213-1.html" target="_blank">Poudu Inc.</a>';
	var $targets = array('pdnovel');
	var $imagesizes = array('960x60', '765x60', '190x60');
	var $categoryvalue = array();

	function getsetting() {
		global $_G;
		$settings = array(
			'position' => array(
				'title' => 'pdnovel_position',
				'type' => 'mradio',
				'value' => array(
					array(1, 'pdnovel_position_1'),
					array(2, 'pdnovel_position_2'),
					array(3, 'pdnovel_position_3'),
					array(4, 'pdnovel_position_4'),
					array(5, 'pdnovel_position_5'),
					array(6, 'pdnovel_position_6'),
					array(7, 'pdnovel_position_7'),
					array(8, 'pdnovel_position_8'),
				),
				'default' => 1,
			),
			'category' => array(
				'title' => 'pdnovel_category',
				'type' => 'mselect',
				'value' => array(),
			),
		);
		loadcache('pdnovelcategory');
		$this->getcategory(0);
		$settings['category']['value'] = $this->categoryvalue;
		return $settings;
	}

	function getcategory($upid) {
		global $_G;
		foreach($_G['cache']['pdnovelcategory'] as $category) {
			if($category['upid'] == $upid) {
				$this->categoryvalue[] = array($category['catid'], str_repeat('&nbsp;', $category['level'] * 4).$category['catname']);
				$this->getcategory($category['catid']);
			}
		}
	}

	function setsetting(&$advnew, &$parameters) {
		global $_G;
		if(is_array($advnew['targets'])) {
			$advnew['targets'] = implode("\t", $advnew['targets']);
		}
		if(is_array($parameters['extra']['category']) && in_array(0, $parameters['extra']['category'])) {
			$parameters['extra']['category'] = array();
		}
	}

	function evalcode() {
		return array(
			'check' => '
			$checked = $params[2] == $parameter[\'position\'] && (!$parameter[\'category\'] || $parameter[\'category\'] && in_array($_G[\'catid\'], $parameter[\'category\']));
			',
			'create' => '$adcode = $codes[$adids[array_rand($adids)]];',
		);
	}

}

?>