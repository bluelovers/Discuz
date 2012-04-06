<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_diyhelp.php 21666 2011-04-07 04:57:15Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$allowdiy = false; //diy权限:$_G['group']['allowdiy'] || $_G['group']['allowaddtopic'] && $topic['uid'] == $_G['uid'] || $_G['group']['allowmanagetopic']
$ref = $_G['gp_diy'] == 'yes';//DIY模式中
if(!$ref && $_G['gp_action'] == 'get') {
	if($_G['gp_type'] == 'index') {
		if($_G['group']['allowdiy']) {
			$allowdiy = true;
		}
	} else if($_G['gp_type'] == 'topic') {
		$topic = array();
		$topicid = max(0, intval($_G['gp_topicid']));
		if($topicid) {
			if($_G['group']['allowmanagetopic']) {
				$allowdiy = true;
			} else if($_G['group']['allowaddtopic']) {
				$topic = DB::fetch_first('SELECT uid FROM '.DB::table('portal_topic')." WHERE topicid='$topicid'");
				if($topic && $topic['uid'] == $_G['uid']) {
					$allowdiy = true;
				}
			}
		}
	}
}

include_once template('portal/portal_diyhelp');

?>