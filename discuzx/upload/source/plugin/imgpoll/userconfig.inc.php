<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$usergroupon = unserialize(DB::result_first("SELECT svalue FROM ".DB::table("common_setting")." WHERE skey='allowthreadplugin'"));

if (submitcheck("usergroupsubmit")){
	$rightusergroup = $_POST['rightusergroup'];

	foreach ($rightusergroup as $key=>$value){
		if (!in_array($plugin["identifier"], $usergroupon[$value])) {
			if ($usergroupon[$value] == null) {
				$usergroupon[$value] = array();
			}
			array_push($usergroupon[$value], $plugin["identifier"]);
		}
	}
	
	foreach ($usergroupon as $key=>$value){
		if (!in_array($key, $rightusergroup)) {
			if (in_array($plugin["identifier"], $value)) {
				unset($value[array_search($plugin["identifier"], $value)]);
				$usergroupon[$key] = array_values($value);
			}
		}
	}
	$allowthreadpluginnew = addslashes(serialize($usergroupon));
	DB::query("REPLACE INTO ".DB::table("common_setting")." (skey, svalue) VALUES ('allowthreadplugin', '{$allowthreadpluginnew}')");
	$done = true;
}
if ($done) {
	showtableheader('【'.$plugin["name"].'】用户权限设定');
	echo '<tr class="hover"><td><b style="color:Green;font-size:14px;">用户权限设置成功！</b></td></tr>';
	showtablefooter();
} else {
	$usergroupquery = DB::query("SELECT groupid, type, grouptitle FROM ".DB::table("common_usergroup")." ORDER BY type, groupid");
	$usergrouplist = "";
	$usergrouplist_system = '<tr class="hover"><td><b>系统用户组</b></td><td></td></tr>';
	$usergrouplist_member = '<tr class="hover"><td><b>会员用户组</b></td><td></td></tr>';
	$usergrouplist_special = '<tr class="hover"><td><b>特殊用户组</b></td><td></td></tr>';
	while($usergroup = DB::fetch($usergroupquery)){
		$checked = in_array($plugin["identifier"], $usergroupon[$usergroup['groupid']]) ? 'checked="checked"' : '';
		if ('system' == $usergroup['type']) {
			$usergrouplist_system .= '<tr class="hover"><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$usergroup['grouptitle'].'</td><td><input type="checkbox" class="checkbox" name="rightusergroup[]" value="'.$usergroup['groupid'].'"'.$checked.'></td></tr>';
		}else if ('member' == $usergroup['type']) {
			$usergrouplist_member .= '<tr class="hover"><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$usergroup['grouptitle'].'</td><td><input type="checkbox" class="checkbox" name="rightusergroup[]" value="'.$usergroup['groupid'].'"'.$checked.'></td></tr>';
		}else if ('special' == $usergroup['type']) {
			$usergrouplist_special .= '<tr class="hover"><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$usergroup['grouptitle'].'</td><td><input type="checkbox" class="checkbox" name="rightusergroup[]" value="'.$usergroup['groupid'].'"'.$checked.'></td></tr>';
		}
	}
	$usergrouplist = $usergrouplist_system.$usergrouplist_special.$usergrouplist_member;
	showformheader("plugins&operation=config&do=".$plugin["pluginid"]."&identifier=".$plugin["identifier"]."&pmod=userconfig");
	showtableheader('【'.$plugin["name"].'】用户权限设定');
	showsubtitle(array('用户组', '开启【'.$plugin["name"].'】'));
	echo $usergrouplist;
	showsubmit('usergroupsubmit', '提交', '', '');
	showtablefooter();
	showformfooter();
}
?>
