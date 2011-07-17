<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once libfile('function/post');
	$polloptionid = is_numeric($_G['gp_polloptionid']) ? $_G['gp_polloptionid'] : '';
	$tid=$_G[gp_tid];
	$overt = DB::result_first("SELECT overt FROM ".DB::table('forum_imgpoll')." WHERE tid='$tid'");
	$polloptions = array();
	$query = DB::query("SELECT polloptionid, polloption FROM ".DB::table('forum_imgpolloption')." WHERE tid='$tid' order by polloptionid");
	while($options = DB::fetch($query)) {
		if(empty($polloptionid)) {
			$polloptionid = $options['polloptionid'];
		}
		$options['polloption'] = preg_replace("/\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i",
			"<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $options['polloption']);
		$polloptions[] = $options;
	}

	$arrvoterids = array();
	if($overt || $_G['adminid'] == 1) {
		$voterids = '';
		$voterids = DB::result_first("SELECT voterids FROM ".DB::table('forum_imgpolloption')." WHERE polloptionid='$polloptionid'");
		$arrvoterids = explode("\t", trim($voterids));
	}

	if(!empty($arrvoterids)) {
		$arrvoterids = array_slice($arrvoterids, -100);
	}
	$voterlist = $voter = array();
	if($voterids = dimplode($arrvoterids)) {
		$query = DB::query("SELECT uid, username FROM ".DB::table('common_member')." WHERE uid IN ($voterids)");
		while($voter = DB::fetch($query)) {
			$voterlist[] = $voter;
		}
	}
	include template('imgpoll:viewthread_poll_voter');
?>