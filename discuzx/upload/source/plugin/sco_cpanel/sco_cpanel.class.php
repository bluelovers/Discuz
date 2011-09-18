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

		list($tids, $membercount, $credit, $ponly) = $_args['param'];

		if ($ids = dimplode($tids)) {
			$query = DB::query("SELECT distinct fid FROM ".DB::table('forum_thread')."
				WHERE
					tid IN ($ids)
			");
			while($_row = DB::fetch($query)) {
				$fid = $_row['fid'];
				$_forum = DB::fetch_first("SELECT * FROM ".DB::table('forum_forum')." WHERE fid = '{$fid}'");

				$_forum['lastpost'] = explode("\t", $_forum['lastpost']);
				if (in_array($_forum['lastpost']['tid'], $tids)) {

					$sqladd = !in_array($_forum['type'], array('sub', 'group')) ? " OR fup = '$fid'" : '';

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

?>