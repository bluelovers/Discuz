<?php

/**
 * @author bluelovers
 * @copyright 2011
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

/*
SELECT *
FROM `pre_forum_thread`
WHERE `dateline` =0
ORDER BY RAND( )
LIMIT 1
*/
$query = DB::query("SELECT *
	FROM ".DB::table('forum_thread')."
	WHERE
		dateline='0'
	ORDER BY
		RAND()
	LIMIT 1
");
while($thread = DB::fetch($query)) {
	$lastpost_min = 0;
	$lastpost_max = max(0, $thread['dateline'], $thread['lastpost']);

	$query_post = DB::query("SELECT *
		FROM ".DB::table('forum_post')."
		WHERE
			tid = '{$thread[tid]}'
		ORDER BY
			first DESC
			, dateline ASC
			, pid ASC
	");
	while($post = DB::fetch($query_post)) {
		if (
			($lastpost_min == 0 && $post['dateline'] > 0)
			|| ($post['dateline'] < $lastpost_min)
		) {
			$lastpost_min = $post['dateline'];
		}
		if ($post['dateline'] > $lastpost_max) $lastpost_max = $post['dateline'];

		$postlist[$post['pid']] = $post;
	}


}

?>