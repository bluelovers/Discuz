<?php
$var = $_G['cache']['plugin']['ddu_dev_dzx'];
$note = nl2br($var['note']);
$adminid = explode(',',$var['adminid']);
if(empty($_G['uid'])) showmessage('to_login', 'member.php?mod=logging&action=login', array(), array('showmsg' => true, 'login' => 1));
if(!in_array($_G['uid'],$adminid)) showmessage('You are not admin!', 'plugin.php?id=ddu_dev_dzx:dev');

if($_G['gp_mode']==''){
	$query = DB::query("SELECT * FROM `".DB::table('ddu_dev_dzx')."`");
	$mrcs = array();
	while($mrc = DB::fetch($query)) {
		$mrcs[] = $mrc;
	}
}else if($_G['gp_mode']=='add' && $_G['gp_ok']=='1' && $_POST['profilesubmitbtn']){
	$name = $_G['gp_task'];
	$stat = $_G['gp_stat'];
	if($stat == "B") $stat = $_G['gp_statb'];
	$url  = $_G['gp_url'];
	$user = $_G['gp_user'];
	$last = $_G['gp_last'];
	if(!$last) $last = date("Y-m-d");
	$query = DB::query("INSERT INTO `".DB::table('ddu_dev_dzx')."` (`id`, `task`, `stat`, `url`, `user`, `last`) VALUES (NULL, '$name', '$stat', '$url', '$user', '$last');");
	if($query) showmessage('ddu_dev_dzx:aom',"plugin.php?id=ddu_dev_dzx:dev_admin");
	else showmessage('ddu_dev_dzx:anom',"plugin.php?id=ddu_dev_dzx:dev_admin");
}else if($_G['gp_mode']=='edit' && $_G['gp_ok']=='1' && $_POST['submitbtn']){
	$id   = $_G['gp_eid'];
	$name = $_G['gp_task'];
	$stat = $_G['gp_stat'];
	if($stat == "B") $stat = $_G['gp_statb'];
	$url  = $_G['gp_url'];
	$user = $_G['gp_user'];
	$last = $_G['gp_last'];
	if(!$last) $last = date("Y-m-d");
	$query = DB::query("UPDATE `".DB::table('ddu_dev_dzx')."` SET `task` = '$name', `stat` = '$stat', `url` = '$url', `user` = '$user', `last` = '$last' WHERE `id` ='$id';");
	if($query) showmessage('ddu_dev_dzx:eom',"plugin.php?id=ddu_dev_dzx:dev_admin");
	else showmessage('ddu_dev_dzx:enom',"plugin.php?id=ddu_dev_dzx:dev_admin");
}else if($_G['gp_mode']=='del' && $_G['gp_did']){
	$id    = $_G['gp_did'];
	$query = DB::query("DELETE FROM `".DB::table('ddu_dev_dzx')."` WHERE `id` = '$id'");
	if($query) showmessage('ddu_dev_dzx:dom',"plugin.php?id=ddu_dev_dzx:dev_admin");
	else showmessage('ddu_dev_dzx:dnom',"plugin.php?id=ddu_dev_dzx:dev_admin");
}

include template('ddu_dev_dzx:dev_admin');
?>