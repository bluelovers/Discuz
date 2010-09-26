<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_forumlinks.php 16696 2010-09-13 05:02:24Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_forumlinks() {
	global $_G;

	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('common_friendlink')." ORDER BY displayorder, name");

	if($_G['setting']['forumlinkstatus']) {
		$tightlink_content = $tightlink_text = $tightlink_logo = $comma = '';
		while($flink = DB::fetch($query)) {

			// bluelovers
			$forumlink['alt'] = " title=\"$flink[name]\n$flink[url]\n$flink[description]\" alt=\"$flink[name]\n$flink[url]\n$flink[description]\"";
			// bluelovers

			if($flink['description'] && $flink['displayorder'] < 10) {
				if($flink['logo']) {
					$tightlink_content .= '<li><div class="forumlogo"><img src="'.$flink['logo'].'" border="0" ' .  $forumlink['alt'] . ' /></div><div class="forumcontent"><h5><a href="'.$flink['url'].'" target="_blank" ' .  $forumlink['alt'] . '>'.$flink['name'].'</a></h5><p>'.$flink['description'].'</p></div>';
				} else {
					$tightlink_content .= '<li><div class="forumcontent"><h5><a href="'.$flink['url'].'" target="_blank" ' .  $forumlink['alt'] . '>'.$flink['name'].'</a></h5><p>'.$flink['description'].'</p></div>';
				}
			} else {

				// bluelovers
				$flink['url'] = $flink['displayorder'] < 11 ? $flink['url']. '" target="_blank"' : "javascript:void(0);\" onclick=\"window.open('$flink[url]', '_blank');doane(this);\" ohref=\"$flink[url]\"";
				// bluelovers

				if($flink['logo']) {
					$tightlink_logo .= '<a href="'.$flink['url'] . $forumlink['alt'] . '><img src="'.$flink['logo'].'" border="0" ' .  $forumlink['alt'] . ' /></a> ';
				} else {
					$tightlink_text .= '<li><a href="'.$flink['url'] . $forumlink['alt'] . '>'.$flink['name'].'</a></li>';
				}
			}
		}
		$data = array($tightlink_content, $tightlink_logo, $tightlink_text);
	}

	save_syscache('forumlinks', $data);
}

?>