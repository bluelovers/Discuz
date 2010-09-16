<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_stat.php 14560 2010-08-12 07:55:16Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['setting']['updatestat'])) {
	showmessage('not_open_updatestat');
}

if($_GET['hash']) {
	dsetcookie('stat_hash', $_GET['hash']);
	showmessage('do_success', 'misc.php?mod=stat&op=trend&quickforward=1');
}

$stat_hash = md5($_G['setting']['sitekey']."\t".substr($_G['timestamp'], 0, 6));
if(!checkperm('allowstat') && $_G['cookie']['stat_hash'] != $stat_hash) {
	showmessage('no_privilege');
}

$cols = array();
$cols['login'] = array('login','register','invite','appinvite');
$cols['add'] = array('doing','blog','pic','poll','activity','share','thread', 'reward', 'debate', 'trade', 'group', 'groupthread');
$cols['comment'] = array('docomment','blogcomment','piccomment','grouppost','sharecomment','post','click');
$cols['space'] = array('wall','poke');

$type = empty($_GET['type'])?'all':$_GET['type'];

if(!empty($_GET['xml'])) {
	$xaxis = '';
	$graph = array();
	$count = 1;
	$query = DB::query("SELECT * FROM ".DB::table('common_stat')." ORDER BY daytime");
	while ($value = DB::fetch($query)) {
		$xaxis .= "<value xid='$count'>".substr($value['daytime'], 4, 4)."</value>";
		if($type == 'all') {
			foreach ($cols as $ck => $cvs) {
				if($ck == 'login') {
					$graph['login'] .= "<value xid='$count'>$value[login]</value>";
					$graph['register'] .= "<value xid='$count'>$value[register]</value>";
				} else {
					$num = 0;
					foreach ($cvs as $cvk) {
						$num = $value[$cvk] + $num;
					}
					$graph[$ck] .= "<value xid='$count'>".$num."</value>";
				}
			}
		} else {
			$graph[$type] .= "<value xid='$count'>".$value[$type]."</value>";
		}
		$count++;
	}
	$xml = '';
	$xml .= '<'."?xml version=\"1.0\" encoding=\"utf-8\"?>";
	$xml .= '<chart><xaxis>';
	$xml .= $xaxis;
	$xml .= "</xaxis><graphs>";
	$count = 0;
	foreach ($graph as $key => $value) {
		$xml .= "<graph gid='$count' title='".diconv(lang('spacecp', "do_stat_$key"), CHARSET, 'utf8')."'>";
		$xml .= $value;
		$xml .= '</graph>';
		$count++;
	}
	$xml .= '</graphs></chart>';

	@header("Expires: -1");
	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
	@header("Content-type: application/xml; charset=utf-8");
	echo $xml;
	exit();
}

require_once libfile('function/home');
$siteurl = getsiteurl();
$statuspara = "path=&settings_file=data/stat_setting.xml&data_file=".urlencode("misc.php?mod=stat&op=trend&xml=1&type=$type");

$actives = array($type => ' class="a"');

include template('home/misc_stat');

?>