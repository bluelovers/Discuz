<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_forumrecommend.php 20322 2011-02-21 09:00:53Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

/**
 * 各版塊的推薦群組
 * hack:
 *		單一群組可推薦多個版塊
 *		版塊也可進行推薦
 *
$data['forumrecommend'] = array (
  75 =>
  array (
    0 =>
    array (
      'fid' => '219',
      'name' => 'Demonic',
      'threads' => '10',
      'lastpost' => '',
      'icon' => 'data/attachment/group/http://img3.imageshack.us/img3/8034/55066298.jpg',
      'membernum' => '6',
      'description' => '這裡是喜愛全民熙－符文之子系列的人(?)所創立的聯盟XDD
不論是喜歡第一部的波里斯或是第二部的喬書亞等等人物
歡迎您加入跟我們一同討論(笑',
      'recommend' => '75',
    ),
    1 =>
    array (
      'fid' => '229',
      'name' => '断罪の楽園',
      'threads' => '12',
      'lastpost' => '',
      'icon' => 'data/attachment/group/http://img24.imageshack.us/img24/6299/c585225sample4s6768189.jpg',
      'membernum' => '19',
      'description' => '提供有心並且願意為 Bluelovers．風 付出貢獻的人
討論並且提供各種資源管道、工具、權限...等等各種讓 Bluelovers．風 成為具有獨特特色的網站的建議或方法

＊本區內皆為機密資料，，請勿外流',
      'recommend' => '75',
    ),
  ),
);
*/
function build_cache_forumrecommend() {
	// bluelovers
	require_once libfile('function/group');
	// bluelovers

	$data = array();
//	$query = DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE type<>'group' AND status<>3");
//
//	while($row = DB::fetch($query)) {
//		require_once libfile('function/group');
		$squery = DB::query("SELECT f.fid, f.name, f.threads, f.lastpost, ff.icon, ff.membernum, ff.description, f.recommend FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff ON ff.fid=f.fid WHERE f.recommend <> '' AND f.recommend <> '0'");
		while($group = DB::fetch($squery)) {

			// bluelovers
			// 防止類似 0,0 這種狀況
			if ($recommend = array_unique(explode(',', $group['recommend']))) {

				unset($group['recommend']);
			// bluelovers

			$group['icon'] = get_groupimg($group['icon'], 'icon');
			$lastpost = array(0, 0, '', '');
			$group['lastpost'] = is_string($group['lastpost']) ? explode("\t", $group['lastpost']) : $group['lastpost'];
			$group['lastpost'] =count($group['lastpost']) != 4 ? $lastpost : $group['lastpost'];
			list($lastpost['tid'], $lastpost['subject'], $lastpost['dateline'], $lastpost['author']) = $group['lastpost'];
			if($lastpost['tid']) {
				$lastpost['dateline'] = dgmdate($lastpost['dateline'], 'Y-m-d H:i:s');
				if($lastpost['author']) {
					$lastpost['encode_author'] = rawurlencode($lastpost['author']);
				}
				$group['lastpost'] = $lastpost;
			} else {
				$group['lastpost'] = '';
			}

				// bluelovers
				foreach($recommend as $fid) {
					$row = array();
					if ($fid = intval($fid)) {
						$row['fid'] = $fid;
				// bluelovers

			$data[$row['fid']][] = $group;

				// bluelovers
					}
				}
				// bluelovers

			// bluelovers
			}
			// bluelovers

		}
//	}

	save_syscache('forumrecommend', $data);
}

?>