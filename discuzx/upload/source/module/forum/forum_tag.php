<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

global $_G;
$op = in_array($_GET['op'], array('search', 'manage', 'set')) ? $_GET['op'] : '';
$taglist = array();
$thread = & $_G['forum_thread'];
$posttable = $thread['posttable'];

if($op == 'search') {

	$wheresql = '';
	$searchkey = stripsearchkey($_G['gp_searchkey']);
	if($searchkey) {
		$wheresql = " AND tagname LIKE '%$searchkey%'";
	}
	$searchkey = dhtmlspecialchars($searchkey);
	$query = DB::query("SELECT tagname FROM ".DB::table('common_tag')." WHERE status='0' $wheresql LIMIT 50");
	while($value = DB::fetch($query)) {
		$taglist[] = $value;
	}

} elseif($op == 'manage') {
	if($_G['tid']) {
		$tagarray_all = $array_temp = $threadtag_array = array();
		$tags = DB::result_first("SELECT tags FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND first=1");
		$tagarray_all = explode("\t", $tags);
		if($tagarray_all) {
			foreach($tagarray_all as $var) {
				if($var) {
					$array_temp = explode(',', $var);
					$threadtag_array[] = $array_temp['1'];
				}
			}
		}
		$tags = implode(',', $threadtag_array);

		$recent_use_tag = array();
		$i = 0;
		$query = DB::query("SELECT tagid, tagname FROM ".DB::table('common_tagitem')." WHERE idtype='tid' ORDER BY itemid DESC LIMIT 10");
		while($result = DB::fetch($query)) {
			if($i > 4) {
				break;
			}
			if($recent_use_tag[$result['tagid']] == '') {
				$i++;
			}
			$recent_use_tag[$result['tagid']] = $result['tagname'];
		}
	}
} elseif($op == 'set') {
	$tagstr = modthreadtag($_G['gp_tags'], $_G[tid]);
	DB::query("UPDATE ".DB::table($posttable)." SET tags='$tagstr' WHERE tid='$_G[tid]' AND first=1");
}

include_once template("forum/tag");
?>