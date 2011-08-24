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

	}
}

?>