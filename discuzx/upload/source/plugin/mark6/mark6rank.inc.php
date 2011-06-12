<?php

if(!defined('IN_DISCUZ') || !defined('IN_MARK6')) {
exit('ACCESS DENIED');
}

$username = $_G['username'];
loadcache('usergroups');
$query = DB::query("SELECT * FROM ".DB::table('plugin_mark6list')." ORDER BY win1 DESC, win2 DESC, win3 DESC, winonly DESC");
$userlist = array();
$num = 1;
while($user = DB::fetch($query)) {
        $user['num'] = $num;
        $userlist[] = $user;
        $num += 1;
}

include template('mark6:mark6rank');


?>