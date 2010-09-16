<?php

/*
	[UCenter] (C)2001-2009 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: feed.php 845 2008-12-08 05:36:51Z zhaoxiongfei $
*/

!defined('IN_UC') && exit('Access Denied');

class feedmodel {

	var $db;
	var $base;
	var $apps;
	var $operations = array();

	function __construct(&$base) {
		$this->feedmodel($base);
	}

	function feedmodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function get_total_num() {
		$data = $this->db->result_first("SELECT COUNT(*) FROM ".UC_DBTABLEPRE."feeds");
		return $data;
	}

	function get_list($page, $ppp, $totalnum) {		
		$start = $this->base->page_get_start($page, $ppp, $totalnum);
		$data = $this->db->fetch_all("SELECT * FROM ".UC_DBTABLEPRE."feeds LIMIT $start, $ppp");

		foreach((array)$data as $k=> $v) {
			$searchs = $replaces = array();
			$title_data = $_ENV['misc']->string2array($v['title_data']);
			foreach(array_keys($title_data) as $key) {
				$searchs[] = '{'.$key.'}';
				$replaces[] = $title_data[$key];
			}
			$searchs[] = '{actor}';
			$replaces[] = $v['username'];
			$searchs[] = '{app}';
			$replaces[] = $this->base->apps[$v['appid']]['name'];
			$data[$k]['title_template'] = str_replace($searchs, $replaces, $data[$k]['title_template']);
			$data[$k]['dateline'] = $v['dateline'] ? $this->base->date($data[$k]['dateline']) : '';
		}
		return $data;
	}
}
?>