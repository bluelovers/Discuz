<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_categorysearch.php 55 2010-09-15 05:41:47Z sunxianwei $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('commonblock_html', 'class/block/html');

class block_categorysearch extends commonblock_html {

	function block_categorysearch() {}

	function name() {
		return lang('blockclass', 'blockclass_html_script_categorysearch');
	}

	function getsetting() {
		global $_G;

		$settings = array(
			'sortids' => array(
				'title' => 'categorylist_sorts',
				'type' => 'mradio',
				'default' => 0,
				'value' => array(
					array(0, 'categorylist_all')
				)
			),
			'styles' => array(
				'title' => 'categorylist_styles',
				'type' => 'mradio',
				'default' => 'list',
				'value' => array(
					array('list', 'categorylist_list'), 
					array('tab', 'categorylist_tab')
				)
			),
			'aids'	=> array(
				'title' => 'categorylist_area',
				'type' => 'mselect',
				'value' => array()
			),
		);

		if($settings['aids']) {
			$query = DB::query("SELECT aid, type, title FROM ".DB::table('category_area')." WHERE cid='1' ORDER BY displayorder");
			while($area = DB::fetch($query)) {
				if($area['type'] == 'district') {
					$settings['aids']['value'][] = array($area['aid'], $area['title']);
				}
			}
		}

		// 分类信息
		if($settings['sortids']) {
			$defaultvalue = '';
			$query = DB::query("SELECT sortid, name FROM ".DB::table('category_sort')." ORDER BY displayorder");
			while($threadsort = DB::fetch($query)) {
				$settings['sortids']['value'][] = array($threadsort['sortid'], $threadsort['name']);
			}
		}

		return $settings;
	}

	function getdata($style, $parameter) {
		global $_G;

		$return = '';

		require_once libfile('function/category');
		$sortid = isset($parameter['sortids']) ? intval($parameter['sortids']) : 0;

		$allsorts = $sorts = array();
		$allsortstr = '';
		$query = DB::query("SELECT * FROM ".DB::table('category_sort')." ORDER BY sortid");
		while($sort = DB::fetch($query)) {
			$cursortid = $sort['sortid'];
			$allsorts[$cursortid] = $sort;
			$allsortstr .= $cursortid.'||'.$sort['name'].'||';
		}
		$allsortstr && $allsortstr = substr($allsortstr, 0, -2);
		if(!$sortid) {
			$sorts = $allsorts;
		} else {
			$sorts = array($sortid => array('sortid' => $sortid));
		}

		$sortrandomid = '';
		foreach($sorts as $cursortid => $sort) {
			loadcache(array('category_option_'.$cursortid));
			$sortoptionarray = $_G['cache']['category_option_'.$cursortid];
			$quicksearchlist = quicksearch($sortoptionarray);

			$aids = !empty($parameter['aids']) ? $parameter['aids'] : array();
			$addarea = $aids ? "AND aid IN (".dimplode($aids).")" : '';

			$districtsearchlist = array();
			$query = DB::query("SELECT aid, type, title FROM ".DB::table('category_area')." WHERE cid='1' $addarea ORDER BY displayorder");
			while($area = DB::fetch($query)) {
				if($area['type'] == 'district') {
					$districtsearchlist[$area['aid']] = $area['title'];
				}
			}

			$url = $select = $input = $button = '';
			if($quicksearchlist) {
				$randomid = rand(1, 999);
				!$sortrandomid && $sortrandomid = $randomid;
				$show_style = '';
				if($cursortid == 2 && !$sortid) {
					$show_style = ' style="display: none;" ';
				}
				$select .= '<div id="searchdiv_'.$sortrandomid.'_'.$cursortid.'" class="bbda cgs pns cl"'.$show_style.'><form method="post" autocomplete="off" name="searhsort" id="searhsort" action="house.php?mod=list&amp;sortid='.$cursortid.'">';

				if(!$sortid && $parameter['styles'] == 'list') {
					if($allsorts) {
						$select .= '<span class="ftid"><select id="changesort'.$sortrandomid.$cursortid.'" change="changecategorysort(\'list\', \'changesort'.$sortrandomid.$cursortid.'\', \''.$sortrandomid.'\', \''.$allsortstr.'\', \''.$cursortid.'\')">';
						foreach($allsorts as $sid => $sortarray) {
							$selected = $sid == $cursortid ? 'selected' : '';
							$select .= '<option value="'.$sid.'" '.$selected.'>'.$sortarray['name'].'</option>';
						}
						$select .= '</select></span>';
						$select .= '<input type="hidden" name="categorysort" value="mselect"><script type="text/javascript" reload="1">simulateSelect(\'changesort'.$sortrandomid.$cursortid.'\');</script>';
					}
				}

				foreach($quicksearchlist as $optionid => $option) {
					if(($option['type'] == 'select' && $option['choices']) || ($option['type'] == 'range' && $option['choices'])) {
						$select .= '<span class="ftid"><select name="searchoption['.$optionid.'][value]" id="'.$option['identifier'].'_'.$randomid.'"><option value="0">'.$option['title'].lang('block/categorylist', 'categorylist_any').'</option>';
							foreach($option['choices'] as $id => $value) {
									$select .= '<option value="'.$id.'">'.$value.'</option>';
							}
						$select .= '</select></span>';
						$select .= '<input type="hidden" name="searchoption['.$optionid.'][type]" value="'.$option['type'].'"><script type="text/javascript" reload="1">simulateSelect(\''.$option['identifier'].'_'.$randomid.'\');</script>';
					}
					if($option['type'] == 'text' && !$option['choices']) {
						$input .= '<input type="text" name="searchoption['.$optionid.'][value]" size="15" id="'.$option['identifier'].'_'.$randomid.'" class="px" value="'.$option['title'].'" onclick="$(\''.$option['identifier'].'_'.$randomid.'\').value = \'\'" />';
					}
				}
				$button .= '<button type="submit" class="pn" name="searchsortsubmit"><em>'.lang('block/categorylist', 'categorylist_submit').'</em></button></form></div>';

				$url .= '<dl id="dl_'.$sortrandomid.'_'.$cursortid.'" class="cgsq pbm bbda cl"'.$show_style.'>';
				$url .= '<dt>'.lang('block/categorylist', 'categorylist_area').':</dt><dd><ul>';
				foreach($districtsearchlist as $did => $district) {
					$url .= '<li><a href="house.php?mod=list&amp;filter=all&amp;district='.$did.'&amp;sortid='.$cursortid.'" target="_blank">'.$district.'</a></li>';
				}
				$url .= '</ul></dd>';
				foreach($quicksearchlist as $optionid => $option) {
					if(in_array($option['type'], array('select', 'radio')) || ($option['type'] == 'range' && $option['choices'])) {
						$url .= '<dt>'.$option['title'].':</dt><dd><ul>';
							foreach($option['choices'] as $id => $value) {
								$url .= '<li><a href="house.php?mod=list&amp;filter=all&amp;sortid='.$cursortid.'&amp;'.$option['identifier'].'='.$id.'">'.$value.'</a></li>';
							}
						$url .= '</ul></dd>';
					}
				}
				$url .= '</dl>';
			}

			$return .= $select.$input.$button.$url;
		}

		$show_style = ' style="display: none;" ';
		if(!$sortid && $parameter['styles'] == 'tab') {
			$show_style = '';
		}
		$tab = '<script type="text/javascript" src="'.$_G[setting][jspath].'house.js?'.VERHASH.'"></script><div class="tab-title title column cl" switchtype="click"'.$show_style.'><ul class="tb cl">';
		if($allsorts) {
			$class = 'a';
			foreach($allsorts as $sid => $sortarray) {
				$tab .= '<li id="li_'.$sortrandomid.'_'.$sid.'" class="'.$class.'"><a href="javascript:;" onclick="javascript: changecategorysort(\'tab\', \''.$sid.'\', \''.$sortrandomid.'\', \''.$allsortstr.'\', \''.$sid.'\');return false;" onfocus="this.blur();">'.$sortarray['name'].'</a></li>';
				$class && $class = '';
			}
		}
		$tab .= '</ul></div>';
		$return = $tab.$return;

		return array('html' => $return, 'data' => null);
	}

}

?>
