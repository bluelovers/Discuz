<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_forumdisplay.php 7610 2010-04-09 01:55:40Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$navtitle = $sortlist[$sortid]['name'].' - '.$channel['title'].' - ';
$optionadd = $filterurladd = $searchsorton = '';

if(empty($sortid)) {
	showmessage(lang('category/template', 'house_undefined_action'));
}

require_once libfile('function/category');

$showpic = intval($_G['gp_showpic']);
$templatearray = $sortoptionarray = array();
loadcache(array('category_option_'.$sortid, 'category_template_'.$sortid));

$_G['gp_listtype'] = in_array($_G['gp_listtype'], array('text', 'pic')) ? $_G['gp_listtype'] : $channel['listmode'];
$templatearray = $_G['gp_listtype'] == 'text' ? $_G['cache']['category_template_'.$sortid]['subjecttext'] : $_G['cache']['category_template_'.$sortid]['subject'];
$vtemplatearray = $_G['cache']['category_template_'.$sortid]['visit'];
$sortoptionarray = $_G['cache']['category_option_'.$sortid];
$perpage = $_G['cache']['category_template_'.$sortid]['perpage'] ? $_G['cache']['category_template_'.$sortid]['perpage'] : $_G['tpp'];

if(empty($sortoptionarray)) {
	showmessage(lang('category/template', 'house_class_nothing'));
}

$quicksearchlist = quicksearch($sortoptionarray);
$districtid = $_G['gp_district'] ? intval($_G['gp_district']) : '';
$streetid = $_G['gp_street'] ? intval($_G['gp_street']) : '';
$cityid = $_G['gp_city'] ? intval($_G['gp_city']) : '';

$citysearchlist = $arealist ? $arealist['city'] : '';
$districtsearchlist = $arealist && $cityid ? $arealist['district'][$cityid] : '';
$streetsearchlist = $arealist && $districtid ? $arealist['street'][$districtid] : '';

$page = $_G['page'];
$start_limit = ($page - 1) * $perpage;

$filteradd = $sortoptionurl = $space = '';
$sorturladdarray = $selectadd = $conditionlist = $saveconditionlist = $savedistrictlist = $savestreetlist = $_G['category_threadlist'] = array();
$catedisplayadd['order'] = '';
$filterfield = array('sortid', 'page', 'recommend', 'attachid', 'all');
$_G['gp_filter'] = isset($_G['gp_filter']) && in_array($_G['gp_filter'], $filterfield) ? $_G['gp_filter'] : 'all';

foreach ($filterfield as $v) {
	$catedisplayadd[$v] = '';
}

if($query_string = $_SERVER['QUERY_STRING']) {
	$query_string = substr($query_string, (strpos($query_string, "&") + 1));
	parse_str($query_string, $geturl);
	$geturl = daddslashes($geturl, 1);
	if($geturl && is_array($geturl)) {
		$selectadd = $geturl;
		foreach($filterfield as $option) {
			$sfilterfield = array_merge(array('filter', 'sortid'), $filterfield);
			foreach($geturl as $soption => $value) {
				$catedisplayadd[$option] .= !in_array($soption, $sfilterfield) ? "&amp;$soption=$value" : '';
			}
		}

		foreach($quicksearchlist as $option) {
			$conditionlist[$option['identifier']]['choices'] = $option['choices'];
			$conditionlist[$option['identifier']]['type'] = $option['type'];
			if($option['unit']) {
				$conditionlist[$option['identifier']]['unit'] = $option['unit'];
			}
			$identifier = $option['identifier'];
			foreach($geturl as $option => $value) {
				$sorturladdarray[$identifier] .= !in_array($option, array('filter', 'sortid', $identifier)) ? "&amp;$option=$value" : '';
			}
		}

		$conditionlist['city'] = $arealist['city'];
		$conditionlist['district'] = $arealist['district'][$cityid];
		$conditionlist['street'] = $arealist['street'][$districtid];

		foreach($geturl as $option => $value) {
			$sorturladdarray['city'] .= !in_array($option, array('filter', 'sortid', 'city', 'district', 'street')) ? "&amp;$option=$value" : '';
			$sorturladdarray['district'] .= !in_array($option, array('filter', 'sortid', 'district', 'street')) ? "&amp;$option=$value" : '';
			$sorturladdarray['street'] .= !in_array($option, array('filter', 'sortid', 'street')) ? "&amp;$option=$value" : '';
		}

		foreach($geturl as $soption => $value) {
			$catedisplayadd['order'] .= !in_array($soption, array('filter', 'sortid', 'orderby', 'ascdesc')) ? "&amp;$soption=$value" : '';
		}

		foreach($geturl as $field => $value) {
			if($conditionlist[$field]['choices'][$value]) {
				$url = $modurl.'?mod=list&filter='.$_G['gp_filter'].'&sortid='.$sortid;
				if($field == 'city') {
					$savecitylist['title'] = $conditionlist[$field][$value];
					$savecitylist['url'] = $url.$sorturladdarray[$field];
				} elseif($field == 'district') {
					$savedistrictlist['title'] = $conditionlist[$field][$value];
					$savedistrictlist['url'] = $url.$sorturladdarray[$field];
				} elseif($field == 'street') {
					$savestreetlist['title'] = $conditionlist[$field][$value];
					$savestreetlist['url'] = $url.$sorturladdarray[$field];
				} else {
					$saveconditionlist[$field]['title'] = $conditionlist[$field]['choices'][$value].($conditionlist[$field]['type'] != 'range' ? $conditionlist[$field]['unit'] : '');
					$saveconditionlist[$field]['url'] = $url.$sorturladdarray[$field];
				}
			}
		}
	}
}

if($_G['gp_searchoption']){
	$catedisplayadd['page'] = '&sortid='.$sortid;
	foreach($_G['gp_searchoption'] as $optionid => $option) {
		$identifier = $sortoptionarray[$sortid][$optionid]['identifier'];
		$catedisplayadd['page'] .= $option['value'] ? "&amp;searchoption[$optionid][value]=$option[value]&amp;searchoption[$optionid][type]=$option[type]" : '';
	}
}

$orderbyurl = array();
foreach($sortoptionarray as $sort) {
	if($sort['orderbyshow']) {
		$orderbyurl[$sort['identifier']]['title'] = $sort['title'];
		if(!empty($_G['gp_ascdesc']) && in_array($_G['gp_ascdesc'], array('asc', 'desc'))) {
			if($_G['gp_ascdesc'] == 'asc') {
				$orderbyurl[$sort['identifier']]['ascdesc'] =  'desc';
			} elseif($_G['gp_ascdesc'] == 'desc') {
				$orderbyurl[$sort['identifier']]['ascdesc'] =  'asc';
			}
		} else {
			$orderbyurl[$sort['identifier']]['ascdesc'] =  'desc';
		}
		$orderbyurl[$sort['identifier']]['classascdesc'] =  !empty($_G['gp_ascdesc']) && in_array($_G['gp_ascdesc'], array('asc', 'desc')) ? $_G['gp_ascdesc'] : 'desc';
	}
}

$sortcondition = array();
$sortcondition['orderby'] = !empty($_G['gp_orderby']) && $orderbyurl[$_G['gp_orderby']] ? $_G['gp_orderby'] : 'dateline';
$sortcondition['ascdesc'] = !empty($_G['gp_ascdesc']) && in_array(strtoupper($_G['gp_ascdesc']), array('ASC', 'DESC')) ? strtoupper($_G['gp_ascdesc']) : 'DESC';

$sortdata = sortsearch($_G['gp_sortid'], $sortoptionarray, $_G['gp_searchoption'], $selectadd, $sortcondition, $start_limit, $perpage);
$tidsadd = $sortdata['tids'] ? "tid IN (".dimplode($sortdata['tids']).")" : '';
$_G['category_threadcount'] = $sortdata['count'];

$catedisplayadd['order'] = !empty($catedisplayadd['order']) ? $catedisplayadd['order'] : '';
$multipage = multi($_G['category_threadcount'], $perpage, $page, "$modurl?mod=list&sortid=$sortid&filter=$_G[gp_filter]$catedisplayadd[order]", $_G['setting']['threadmaxpages']);
$extra = rawurlencode('page='.$page.($catedisplayadd['page'] ? '&filter='.$_G['gp_filter'].$catedisplayadd['page'] : ''));
$_G['category_threadlist'] = $sortdata['datalist'];

if($tidsadd) {
	$query = DB::query("SELECT * FROM ".DB::table('category_'.$modidentifier.'_thread')." WHERE $tidsadd");
	while($thread = DB::fetch($query)) {
		$_G['category_threadlist'][$thread['tid']]['subject'] .= $thread['subject'];
		$_G['category_threadlist'][$thread['tid']]['author'] .= $thread['author'];
		$_G['category_threadlist'][$thread['tid']]['authorid'] .= $thread['authorid'];
	}
}

if($sortoptionarray && $templatearray && $sortdata['tids']) {
	$sortlistarray = showsorttemplate($sortid, $sortoptionarray, $templatearray, $_G['category_threadlist'], $sortdata['tids'], $arealist, $modurl);
	$stemplate = $sortlistarray['template'];
}

if($threadvisited = getcookie('threadvisited')) {
	$threadvisited = explode(',', $threadvisited);
	$visitedlist = visitedshow($threadvisited, $sortoptionarray, $sortid, $vtemplatearray, $modurl);
}

include template('diy:category/'.$modidentifier.'_list');

?>