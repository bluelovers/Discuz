<?php

/**
 * @author bluelovers
 **/

if (!discuz_core::$plugin_support['Scorpio_Event']) return false;

Scorpio_Hook::add('Dz_module_group_index:Before_template', '_eDz_module_group_index_Before_template');

function _eDz_module_group_index_Before_template($_EVENT, $conf) {
	extract($conf, EXTR_REFS);
	global $_G;

	if(empty($curtype)) {
		// 解決推薦群組不足的時候自動補充
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