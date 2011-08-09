<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_grouptype.php 16698 2010-09-13 05:22:15Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

/**
 * 群組分類
 *
 * $data['grouptype'] = array (
  'first' =>
  array (
    208 =>
    array (
      'fid' => '208',
      'fup' => '0',
      'name' => '空間群組',
      'forumcolumns' => '0',
      'membernum' => '0',
      'groupnum' => 17,
      'secondlist' =>
      array (
        0 => '209',
        1 => '217',
        2 => '218',
        3 => '222',
        4 => '225',
        5 => '228',
        6 => '230',
      ),
    ),
  ),
  'second' =>
  array (
    209 =>
    array (
      'fid' => '209',
      'fup' => '208',
      'name' => '自由聯盟',
      'forumcolumns' => '0',
      'membernum' => '0',
      'groupnum' => '7',
    ),
    217 =>
    array (
      'fid' => '217',
      'fup' => '208',
      'name' => '地區聯盟',
      'forumcolumns' => '0',
      'membernum' => '0',
      'groupnum' => '0',
    ),
    218 =>
    array (
      'fid' => '218',
      'fup' => '208',
      'name' => '興趣聯盟',
      'forumcolumns' => '0',
      'membernum' => '0',
      'groupnum' => '3',
    ),
    222 =>
    array (
      'fid' => '222',
      'fup' => '208',
      'name' => '站內大雜燴',
      'forumcolumns' => '0',
      'membernum' => '0',
      'groupnum' => '2',
    ),
    225 =>
    array (
      'fid' => '225',
      'fup' => '208',
      'name' => '動漫聯盟',
      'forumcolumns' => '0',
      'membernum' => '0',
      'groupnum' => '2',
    ),
    228 =>
    array (
      'fid' => '228',
      'fup' => '208',
      'name' => '聯盟特區',
      'forumcolumns' => '0',
      'membernum' => '0',
      'groupnum' => '1',
    ),
    230 =>
    array (
      'fid' => '230',
      'fup' => '208',
      'name' => '遊戲聯盟',
      'forumcolumns' => '0',
      'membernum' => '0',
      'groupnum' => '2',
    ),
  ),
);
 */
function build_cache_grouptype() {
	$data = array();
	$query = DB::query("SELECT f.fid, f.fup, f.name, f.forumcolumns, ff.membernum, ff.groupnum, f.type FROM ".DB::table('forum_forum')." f
		LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid WHERE f.type IN('group', 'forum', 'sub') AND f.status='3' ORDER BY f.type, f.displayorder");

	$data['second'] = $data['first'] = array();
	while($group = DB::fetch($query)) {

		if ($group['type'] == 'sub') {
			$data['sub'][$group['fup']][] = $group['fid'];
			continue;
		} else {
			unset($group['type']);
		}

		if($group['fup']) {
			// 第二層
			$data['second'][$group['fid']] = $group;
		} else {
			// 最頂層
			$data['first'][$group['fid']] = $group;
		}
	}
	foreach($data['second'] as $fid => $secondgroup) {
		$data['first'][$secondgroup['fup']]['groupnum'] += $secondgroup['groupnum'];
		$data['first'][$secondgroup['fup']]['secondlist'][] = $secondgroup['fid'];

		// bluelovers
		// 儲存子群組列表
		$data['second'][$fid]['subs'] = (array)$data['sub'][$fid];
		// bluelovers
	}

	// bluelovers
	unset($data['sub']);
	// bluelovers

	save_syscache('grouptype', $data);
}

?>