<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('class/sco_dx_plugin', 'source', 'extensions/');

class plugin_sco_cpanel extends _sco_dx_plugin {

	/**
	 * 解決刪除主題時父版塊的最後發表沒有更新
	 */
	function deletethread($_args = array()) {
/*
Array
(
    [param] => Array
        (
            [0] => Array
                (
                    [0] => 32989
                )

            [1] => 1
            [2] => 1
            [3] => 1
        )

    [step] => check
)
*/

		if ($_args['step'] != 'check') return;

		list($tids, $membercount, $credit, $ponly) = $_args['param'];

		if ($ids = dimplode($tids)) {
			$query = DB::query("SELECT distinct fid FROM ".DB::table('forum_thread')."
				WHERE
					tid IN ($ids)
			");

			global $_G;

			if (!isset($_G['cache']['forums'])) {
				loadcache('forums');
			}

			$_fids = array();

			while($_row = DB::fetch($query)) {
				$fid = $_row['fid'];

				$_fids[] = $fid;

				while($fup = $_G['cache']['forums'][$fid]['fup']) {
					$_fids[] = $fup;

					$fid = $fup;
				}
			}

			$_fids = array_unique($_fids);

			foreach ($_fids as $fid) {
				$_forum = DB::fetch_first("SELECT * FROM ".DB::table('forum_forum')." WHERE fid = '{$fid}'");

				$_forum['lastpost'] = explode("\t", $_forum['lastpost']);

				if (in_array($_forum['lastpost'][0], $tids)) {
					$sqladd = '';

					if (!empty($_G['cache']['forums'][$fid]['subs'])) {
						if ($ids = dimplode($_G['cache']['forums'][$fid]['subs'])) {
							$sqladd = "OR fid IN ($ids)";
						}
					}

					$thread = DB::fetch_first("SELECT tid, subject, author, lastpost, lastposter, closed FROM ".DB::table('forum_thread')."
						WHERE
							(
								fid='$fid'
								$sqladd
							)
							AND displayorder='0'
						ORDER BY
							lastpost DESC
							LIMIT 1
					");

					$thread['subject'] = addslashes($thread['subject']);
					$thread['lastposter'] = $thread['author'] ? addslashes($thread['lastposter']) : lang('forum/misc', 'anonymous');
					$tid = $thread['closed'] > 1 ? $thread['closed'] : $thread['tid'];

					DB::query("UPDATE ".DB::table('forum_forum')."
						SET
							lastpost='$tid\t$thread[subject]\t$thread[lastpost]\t$thread[lastposter]'
						WHERE
							fid='$fid'
					");

				}
			}
		}
	}

}

class plugin_sco_cpanel_forum extends plugin_sco_cpanel {



}

?>