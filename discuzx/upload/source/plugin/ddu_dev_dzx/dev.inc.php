<?php
$var = $_G['cache']['plugin']['ddu_dev_dzx'];
$note = nl2br($var['note']);
$adminid = explode(',',$var['adminid']);

if(!$_G['gp_user']){
	$nquery = DB::query("SELECT * FROM ".DB::table('ddu_dev_dzx')." WHERE `stat` <> 'OK'");
	$nmrcs = array();
	while($nmrc = DB::fetch($nquery)) {
		$nmrcs[] = $nmrc;
	}
	$query = DB::query("SELECT * FROM ".DB::table('ddu_dev_dzx')." WHERE `stat` = 'OK'");
	$mrcs = array();
	while($mrc = DB::fetch($query)) {
		$mrcs[] = $mrc;
	}
}else{
	$nquery = DB::query("SELECT * FROM ".DB::table('ddu_dev_dzx')." WHERE `stat` <> 'OK' AND `user` = '".$_G['gp_user']."'");
	$nmrcs = array();
	while($nmrc = DB::fetch($nquery)) {
		$nmrcs[] = $nmrc;
	}
	$query = DB::query("SELECT * FROM ".DB::table('ddu_dev_dzx')." WHERE `stat` = 'OK' AND `user` = '".$_G['gp_user']."'");
	$mrcs = array();
	while($mrc = DB::fetch($query)) {
		$mrcs[] = $mrc;
	}
}

include template('ddu_dev_dzx:dev');
?>