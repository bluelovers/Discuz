<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_poll.php 19433 2010-12-31 04:04:47Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$polloptions = array();
$votersuid = '';

if($count = DB::fetch_first("SELECT MAX(votes) AS max, SUM(votes) AS total FROM ".DB::table('forum_polloption')." WHERE tid='$_G[tid]'")) {


	$options = DB::fetch_first("SELECT * FROM ".DB::table('forum_poll')." WHERE tid='$_G[tid]'");
	$multiple = $options['multiple'];
	$visible = $options['visible'];
	$maxchoices = $options['maxchoices'];
	$expiration = $options['expiration'];
	$overt = $options['overt'];
	$voterscount = $options['voters'];

	$query = DB::query("SELECT polloptionid, votes, polloption, voterids FROM ".DB::table('forum_polloption')." WHERE tid='$_G[tid]' ORDER BY displayorder");
	$colors = array('E92725', 'F27B21', 'F2A61F', '5AAF4A', '42C4F5', '0099CC', '3365AE', '2A3591', '592D8E', 'DB3191');
	$voterids = $polloptionpreview = '';
	$ci = 0;
	$opts = 1;
	while($options = DB::fetch($query)) {
		$viewvoteruid[] = $options['voterids'];
		$voterids .= "\t".$options['voterids'];
		$option = preg_replace("/\[url=(https?){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i", "<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $options['polloption']);
		$polloptions[$opts++] = array
		(
			'polloptionid'	=> $options['polloptionid'],
			'polloption'	=> $option,
			'votes'		=> $options['votes'],
			'width'		=> $options['votes'] > 0 ? (@round($options['votes'] * 100 / $count['total'])).'%' : '8px',
			'percent'	=> @sprintf("%01.2f", $options['votes'] * 100 / $count['total']),
			'color'		=> $colors[$ci]
		);
		if($ci < 2) {
			$polloptionpreview .= $option."\t";
		}
		$ci++;
		if($ci == count($colors)) {
			$ci = 0;
		}
	}

	$voterids = explode("\t", $voterids);
	$voters = array_unique($voterids);
	array_shift($voters);

	if(!$expiration) {
		$expirations = TIMESTAMP + 86400;
	} else {
		$expirations = $expiration;
		if($expirations > TIMESTAMP) {
			$_G['forum_thread']['remaintime'] = remaintime($expirations - TIMESTAMP);
		}
	}

	$allwvoteusergroup = $_G['group']['allowvote'];
	$allowvotepolled = !in_array(($_G['uid'] ? $_G['uid'] : $_G['clientip']), $voters);
	$allowvotethread = ($_G['forum_thread']['isgroup'] || !$_G['forum_thread']['closed'] && !checkautoclose($_G['forum_thread']) || $_G['group']['alloweditpoll']) && TIMESTAMP < $expirations && $expirations > 0;

	$_G['group']['allowvote'] = $allwvoteusergroup && $allowvotepolled && $allowvotethread;

	$optiontype = $multiple ? 'checkbox' : 'radio';
	$visiblepoll = $visible || $_G['forum']['ismoderator'] || ($_G['uid'] && $_G['uid'] == $_G['forum_thread']['authorid']) || ($expirations >= TIMESTAMP && in_array(($_G['uid'] ? $_G['uid'] : $_G['clientip']), $voters)) || $expirations < TIMESTAMP ? 0 : 1;

}

?>