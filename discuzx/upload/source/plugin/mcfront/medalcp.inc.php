<?php
	if(!defined('IN_DISCUZ')) {
		exit('Access Denied');
	}

	global $_G;
	$euid = $_GET['euid'];
	loadcache('medals');
	foreach($post['medals'] = explode("\t", $post['medals']) as $key => $medalid){
		$post['medals'][$key] = $_G['cache']['medals'][$medalid];
		$post['medals'][$key]['medalid'] = $medalid;
		$_G['medal_list'][$medalid] = $_G['cache']['medals'][$medalid];
	}
	if(isset($_POST['medalupdate'])) {
	    $medallist = implode("\t",$_G['gp_medallist']);
		$query = DB::query('UPDATE '.DB::table('common_member_field_forum').' SET `medals` = \''.$medallist.'\' WHERE `uid` = '.$euid);
	}
?>