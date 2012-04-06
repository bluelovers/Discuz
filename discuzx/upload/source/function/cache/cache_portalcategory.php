<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_portalcategory.php 17789 2010-11-02 05:20:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_portalcategory() {
	global $_G;

	$data = array();
	$query = DB::query("SELECT * FROM ".DB::table('portal_category')." ORDER BY displayorder,catid");

	while($value = DB::fetch($query)) {
		$value['catname'] = dhtmlspecialchars($value['catname']);
		$data[$value['catid']] = $value;
	}
	foreach($data as $key => $value) {
		$upid = $value['upid'];
		$data[$key]['level'] = 0;
		if($upid && isset($data[$upid])) {
			$data[$upid]['children'][] = $key;
			while($upid && isset($data[$upid])) {
				$data[$key]['level'] += 1;
				$upid = $data[$upid]['upid'];
			}
		}
	}
	$domain = $_G['setting']['domain'];
	$channelrootdomain = !empty($domain['root']) && !empty($domain['root']['channel']) ? $domain['root']['channel'] : '';
	$portaldomain = '';
	if(!empty($domain['app']['portal'])) {
		$portaldomain = 'http://'.$domain['app']['portal'].$_G['siteroot'];
	} elseif(!empty($domain['app']['default'])) {
		$portaldomain = 'http://'.$domain['app']['default'].$_G['siteroot'];
	} else {
		$portaldomain = $_G['siteurl'];
	}
	foreach($data as $key => $value){
		$url = $topid = '';
		$foldername = $value['foldername'];
		if($value['level']) {
			$topid = $key;
			$foldername = '';
			while($data[$topid]['upid']) {
				if($data[$topid]['foldername'] && $data[$key]['foldername']) {
					$foldername = $data[$topid]['foldername'].'/'.$foldername;
				}
				$topid = $data[$topid]['upid'];
			}
			if($foldername) $foldername = $data[$topid]['foldername'].'/'.$foldername;
		} else {
			$topid = $key;
		}
		$data[$key]['topid'] = $topid;

		if($channelrootdomain && $data[$topid]['domain']){
			$url = 'http://'.$data[$topid]['domain'].'.'.$channelrootdomain.'/';
			if($foldername) {

				if(!empty($value['upid'])) {
					$url .= $foldername;
				}
			} else {
				$url = $portaldomain.'portal.php?mod=list&catid='.$key;
			}
		} elseif ($foldername) {
			$url = $portaldomain.$foldername;
			if(substr($url, -1, 1) != '/') $url.= '/';
		} else {
			$url = $portaldomain.'portal.php?mod=list&catid='.$key;
		}
		$data[$key]['caturl'] = $url;

		if($data[$key]['shownav']) {
			$rs = DB::update('common_nav', array('url' => addslashes($url), 'name' =>addslashes($value['catname'])), array('type' => '4','identifier' => $key));
		}
	}

	save_syscache('portalcategory', $data);

	if(!function_exists('get_cachedata_mainnav')) {
		include_once libfile('cache/setting','function');
	}
	$data = $_G['setting'];
	list($data['navs'], $data['subnavs'], $data['menunavs'], $data['navmns'], $data['navmn'], $data['navdms']) = get_cachedata_mainnav();
	save_syscache('setting', $data);
}

?>