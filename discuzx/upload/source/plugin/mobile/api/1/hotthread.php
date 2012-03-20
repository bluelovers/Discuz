<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: hotthread.php 28885 2012-03-16 08:00:41Z monkey $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

include_once 'misc.php';

class mobile_api {

	function common() {
		global $_G;
		$perpage = !empty($_GET['perpage']) ? $_GET['perpage'] : 50;
		list($data, $lastupdate) = loadcache('mobile_hotthread');
		if(!$data || TIMESTAMP - $lastupdate > 43200) {
			$query = DB::query("SELECT t.tid, t.fid, t.author, t.authorid, t.dateline AS dbdateline, t.replies, t.lastpost AS dblastpost, t.lastposter, t.subject, t.attachment, t.views
				FROM ".DB::table('forum_thread')." t INNER JOIN ".DB::table('forum_forum')." f ON f.fid=t.fid AND f.status='1' WHERE t.dateline>".(TIMESTAMP - 604800)." AND t.displayorder>='0' ORDER BY t.views DESC LIMIT 0, $perpage");
			while($thread = DB::fetch($query)) {
				$thread['dateline'] = dgmdate($thread['dbdateline']);
				$thread['lastpost'] = dgmdate($thread['dblastpost']);
				$data[] = $thread;
			}
			save_syscache('mobile_hotthread', array($data, TIMESTAMP));
		}
		$variable = array(
			'data' => mobile_core::getvalues($data, array('/^.+?$/'),
				array('tid', 'fid', 'author', 'authorid', 'dbdateline', 'dateline', 'replies', 'dblastpost', 'lastpost', 'lastposter', 'subject', 'attachment', 'views')),
			'perpage' => $perpage,
		);
		mobile_core::result(mobile_core::variable($variable));
	}

	function output() {
	}

}

?>