<?php

/*
	Scorpio (C)2000-2010 Bluelovers Net.
	This is NOT a freeware, use is subject to license terms

	$HeadURL: svn://localhost/trunk/discuz_x/upload/extensions/hooks/hooks_cache.php $
	$Revision: 109 $
	$Author: bluelovers$
	$Date: 2010-08-02 06:22:26 +0800 (Mon, 02 Aug 2010) $
	$Id: hooks_cache.php 109 2010-08-01 22:22:26Z user $
*/

Scorpio_Hook::add('Dz_module_group_index:Before_template', '_eDz_module_group_index_Before_template');

function _eDz_module_group_index_Before_template($conf) {
	extract($conf, EXTR_REFS);
	global $_G;

	if(empty($curtype)) {
		$recommend_num = 8;
		$group_recommend = unserialize($_G['setting']['group_recommend']);

		if (count($group_recommend) < $recommend_num) {
			$query = DB::query("SELECT f.fid, f.name, ff.description, ff.icon FROM ".DB::table('forum_forum')." f LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid) WHERE f.status='3' AND f.type='sub'
			ORDER BY f.commoncredits desc, ff.membernum desc LIMIT $recommend_num");
			while($row = DB::fetch($query)) {
				$row['icon'] = get_groupimg($row['icon'], 'icon');
				if(count($group_recommend) == $recommend_num) {
					break;
				} elseif(empty($group_recommend[$row[fid]])) {
					$group_recommend[$row[fid]] = $row;
				}
			}

			$_G['setting']['group_recommend'] = serialize($group_recommend);
		}
	}
}

?>