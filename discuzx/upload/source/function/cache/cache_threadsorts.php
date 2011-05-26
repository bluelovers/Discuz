<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_threadsorts.php 19677 2011-01-13 08:40:56Z congyushuai $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_threadsorts() {
	$sortlist = $templatedata = $stemplatedata = $ptemplatedata = $btemplatedata = $template = array();
	$query = DB::query("SELECT t.typeid AS sortid, tt.optionid, tt.title, tt.type, tt.unit, tt.rules, tt.identifier, tt.description, tv.required, tv.unchangeable, tv.search, tv.subjectshow, tt.expiration, tt.protect
			FROM ".DB::table('forum_threadtype')." t
			LEFT JOIN ".DB::table('forum_typevar')." tv ON t.typeid=tv.sortid
			LEFT JOIN ".DB::table('forum_typeoption')." tt ON tv.optionid=tt.optionid
			WHERE t.special='1' AND tv.available='1'
			ORDER BY tv.displayorder");

	while($data = DB::fetch($query)) {
		$data['rules'] = unserialize($data['rules']);
		$sortid = $data['sortid'];
		$optionid = $data['optionid'];
		$sortlist[$sortid][$optionid] = array(
		'title' => dhtmlspecialchars($data['title']),
		'type' => dhtmlspecialchars($data['type']),
		'unit' => dhtmlspecialchars($data['unit']),
		'identifier' => dhtmlspecialchars($data['identifier']),
		'description' => dhtmlspecialchars($data['description']),
		'required' => intval($data['required']),
		'unchangeable' => intval($data['unchangeable']),
		'search' => intval($data['search']),
		'subjectshow' => intval($data['subjectshow']),
		'expiration' => intval($data['expiration']),
		'protect' => unserialize($data['protect']),
		);

		if(in_array($data['type'], array('select', 'checkbox', 'radio'))) {
			if($data['rules']['choices']) {
				$choices = array();
				foreach(explode("\n", $data['rules']['choices']) as $item) {
					list($index, $choice) = explode('=', $item);
					$choices[trim($index)] = trim($choice);
				}
				$sortlist[$sortid][$optionid]['choices'] = $choices;
			} else {
				$sortlist[$sortid][$optionid]['choices'] = array();
			}
			if($data['type'] == 'select') {
				$sortlist[$sortid][$optionid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : 108;
			}
		} elseif(in_array($data['type'], array('text', 'textarea', 'calendar'))) {
			$sortlist[$sortid][$optionid]['maxlength'] = intval($data['rules']['maxlength']);
			if($data['type'] == 'textarea') {
				$sortlist[$sortid][$optionid]['rowsize'] = $data['rules']['rowsize'] ? intval($data['rules']['rowsize']) : 5;
				$sortlist[$sortid][$optionid]['colsize'] = $data['rules']['colsize'] ? intval($data['rules']['colsize']) : 50;
			} else {
				$sortlist[$sortid][$optionid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
			}
			if(in_array($data['type'], array('text', 'textarea'))) {
				$sortlist[$sortid][$optionid]['defaultvalue'] = $data['rules']['defaultvalue'];
			}
			if($data['type'] == 'text') {
				$sortlist[$sortid][$optionid]['profile'] = $data['rules']['profile'];
			}
		} elseif($data['type'] == 'image') {
			$sortlist[$sortid][$optionid]['maxwidth'] = intval($data['rules']['maxwidth']);
			$sortlist[$sortid][$optionid]['maxheight'] = intval($data['rules']['maxheight']);
			$sortlist[$sortid][$optionid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
		} elseif(in_array($data['type'], array('number', 'range'))) {
			$sortlist[$sortid][$optionid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
			$sortlist[$sortid][$optionid]['maxnum'] = intval($data['rules']['maxnum']);
			$sortlist[$sortid][$optionid]['minnum'] = intval($data['rules']['minnum']);
			if($data['rules']['searchtxt']) {
				$sortlist[$sortid][$optionid]['searchtxt'] = explode(',', $data['rules']['searchtxt']);
			}
			if($data['type'] == 'number') {
				$sortlist[$sortid][$optionid]['defaultvalue'] = $data['rules']['defaultvalue'];
			}
		}
	}
	$query = DB::query("SELECT typeid, description, template, stemplate, ptemplate, btemplate FROM ".DB::table('forum_threadtype'));

	while($data = DB::fetch($query)) {
		$templatedata[$data['typeid']] = addcslashes($data['template'], '",\\');
		$stemplatedata[$data['typeid']] = addcslashes($data['stemplate'], '",\\');
		$ptemplatedata[$data['typeid']] = addcslashes($data['ptemplate'], '",\\');
		$btemplatedata[$data['typeid']] = addcslashes($data['btemplate'], '",\\');
	}

	$data['sortoption'] = $data['template'] = array();

	foreach($sortlist as $sortid => $option) {
		$template['viewthread'] =  $templatedata[$sortid];
		$template['subject'] = $stemplatedata[$sortid];
		$template['post'] = $ptemplatedata[$sortid];
		$template['block'] = $btemplatedata[$sortid];

		save_syscache('threadsort_option_'.$sortid, $option);
		save_syscache('threadsort_template_'.$sortid, $template);
	}

}

?>