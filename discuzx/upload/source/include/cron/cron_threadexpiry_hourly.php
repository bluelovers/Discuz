<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_threadexpiry_hourly.php 16910 2010-09-16 16:01:23Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$actionarray = array();
$query = DB::query("SELECT * FROM ".DB::table('forum_threadmod')." WHERE expiration>'0' AND expiration<'$_G[timestamp]' AND status='1'");
while($expiry = DB::fetch($query)) {
	switch($expiry['action']) {
		case 'EST':	$actionarray['UES'][] = $expiry['tid']; break;
		case 'EHL':	$actionarray['UEH'][] = $expiry['tid'];	break;
		case 'ECL':	$actionarray['UEC'][] = $expiry['tid'];	break;
		case 'EOP':	$actionarray['UEO'][] = $expiry['tid'];	break;
		case 'EDI':	$actionarray['UED'][] = $expiry['tid'];	break;
		case 'TOK':	$actionarray['UES'][] = $expiry['tid']; break;
		case 'CCK':	$actionarray['UEH'][] = $expiry['tid'];	break;
		case 'CLK':	$actionarray['UEC'][] = $expiry['tid']; break;
		case 'SPA':	$actionarray['SPD'][] = $expiry['tid']; break;
	}
}

if($actionarray) {

	foreach($actionarray as $action => $tids) {

		$tids = implode(',', $tids);

		switch($action) {

			case 'UES':
				DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='0' WHERE tid IN ($tids)", 'UNBUFFERED');
				DB::query("UPDATE ".DB::table('forum_threadmod')." SET status='0' WHERE tid IN ($tids) AND action IN ('EST', 'TOK')", 'UNBUFFERED');

				require_once libfile('function/cache');
				updatecache('globalstick');
				break;

			case 'UEH':
				DB::query("UPDATE ".DB::table('forum_thread')." SET highlight='0' WHERE tid IN ($tids)", 'UNBUFFERED');
				DB::query("UPDATE ".DB::table('forum_threadmod')." SET status='0' WHERE tid IN ($tids) AND action IN ('EHL', 'CCK')", 'UNBUFFERED');
				break;

			case 'UEC':
			case 'UEO':
				$closed = $action == 'UEO' ? 1 : 0;
				DB::query("UPDATE ".DB::table('forum_thread')." SET closed='$closed' WHERE tid IN ($tids)", 'UNBUFFERED');
				DB::query("UPDATE ".DB::table('forum_threadmod')." SET status='0' WHERE tid IN ($tids) AND action IN ('EOP', 'ECL', 'CLK')", 'UNBUFFERED');
				break;

			case 'UED':
				DB::query("UPDATE ".DB::table('forum_threadmod')." SET status='0' WHERE tid IN ($tids) AND action='EDI'", 'UNBUFFERED');

				$digestarray = $authoridarry = array();
				$query = DB::query("SELECT authorid, digest FROM ".DB::table('forum_thread')." WHERE tid IN ($tids)");
				while($digest = DB::fetch($query)) {
					$authoridarry[] = $digest['authorid'];
					$digestarray[$digest['digest']][] = $digest['authorid'];
				}
				foreach($digestarray as $digest => $authorids) {
					batchupdatecredit('digest', $authorids, array("digestposts=digestposts+'-1'"), -$digest, $fid = 0);
				}
				DB::query("UPDATE ".DB::table('forum_thread')." SET digest='0' WHERE tid IN ($tids)", 'UNBUFFERED');
				break;

			case 'SPD':
				DB::query("UPDATE ".DB::table('forum_thread')." SET stamp='-1' WHERE tid IN ($tids)", 'UNBUFFERED');
				DB::query("UPDATE ".DB::table('forum_threadmod')." SET status='0' WHERE tid IN ($tids) AND action IN ('SPA')", 'UNBUFFERED');
				break;

		}
	}

	require_once libfile('function/post');

	foreach($actionarray as $action => $tids) {
		updatemodlog(implode(',', $tids), $action, 0, 1);
	}

}

?>