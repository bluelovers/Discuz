<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_cleanup_monthly.php 26922 2011-12-27 10:00:36Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


DB::query("DELETE FROM ".DB::table('common_mytask')." WHERE status='-1' AND dateline<'$_G[timestamp]'-2592000", 'UNBUFFERED');

?>