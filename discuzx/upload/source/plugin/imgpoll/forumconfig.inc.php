<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$forumonquery = DB::query("SELECT fid, threadplugin FROM ".DB::table("forum_forumfield")." ORDER BY fid");
$forumonarray = array();
while ($forumon = DB::fetch($forumonquery)) {
	$forumonarray[$forumon['fid']] = unserialize($forumon['threadplugin']);
}

if (submitcheck("forumsubmit")){
	$rightforum = $_POST['rightforum'];
	foreach ($rightforum as $key=>$value){
		if (!in_array($plugin["identifier"], $forumonarray[$value])) {
			if ($forumonarray[$value] == null) {
				$forumonarray[$value] = array();
			}
			array_push($forumonarray[$value], $plugin["identifier"]);
		}
	}
	
	foreach ($forumonarray as $key=>$value){
		if (!in_array($key, $rightforum)) {
			if (in_array($plugin["identifier"], $value)) {
				unset($value[array_search($plugin["identifier"], $value)]);
				$forumonarray[$key] = array_values($value);
			}
		}
		$allowthreadpluginnew = addslashes(serialize($forumonarray[$key]));
		DB::query("UPDATE ".DB::table("forum_forumfield")." SET threadplugin = '{$allowthreadpluginnew}' WHERE fid ='{$key}'");
	}
	$done = true;
}	

if ($done) {
	showtableheader('【'.$plugin["name"].'】版块权限设定');
	echo '<tr class="hover"><td><b style="color:Green;font-size:14px;">版块权限设置成功！</b></td></tr>';
	showtablefooter();
}else {
	$forumgroupquery = DB::query("SELECT fid, type, name FROM ".DB::table("forum_forum")." WHERE status=1 AND type='group' ORDER BY fid");
	$forumlist = "";
	while($forumgroup = DB::fetch($forumgroupquery)){
		$forumlist .= '<tr class="hover"><td><b>'.$forumgroup['name'].'</b></td><td></td><td></td></tr>';
		$forumquery = DB::query("SELECT fid, name FROM ".DB::table("forum_forum")." WHERE status=1 AND fup='".$forumgroup['fid']."' ORDER BY fid");
		while($forum = DB::fetch($forumquery)){
			$checked = in_array($plugin["identifier"], $forumonarray[$forum['fid']]) ? 'checked="checked"' : '';
			$forumlist .= '<tr class="hover"><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$forum['name'].'</td><td>'.'<input type="checkbox" class="checkbox" name="rightforum[]" value="'.$forum['fid'].'"'.$checked.'></td></tr>';
			$forumquery = DB::query("SELECT fid, name FROM ".DB::table("forum_forum")." WHERE status=1 AND fup='".$forum['fid']."' ORDER BY fid");
			while($forum = DB::fetch($forumquery)){
				$checked = in_array($plugin["identifier"], $forumonarray[$forum['fid']]) ? 'checked="checked"' : '';
				$forumlist .= '<tr class="hover"><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$forum['name'].'</td><td>'.'<input type="checkbox" class="checkbox" name="rightforum[]" value="'.$forum['fid'].'"'.$checked.'></td></tr>';
			}
		}
	}
	showformheader("plugins&operation=config&do=".$plugin["pluginid"]."&identifier=".$plugin["identifier"]."&pmod=forumconfig");
	showtableheader('【'.$plugin["name"].'】版块权限设定');
	showsubtitle(array('版块名称', '开启【'.$plugin["name"].'】'));
	echo $forumlist;
	showsubmit('forumsubmit', '提交', '', '');
	showtablefooter();
	showformfooter();
}



?>
