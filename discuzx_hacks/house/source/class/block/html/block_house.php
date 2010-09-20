<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_house.php 55 2010-09-15 05:41:47Z sunxianwei $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('commonblock_html', 'class/block/html');

class block_house extends commonblock_html {

	function block_house() {}

	function name() {
		return lang('blockclass', 'blockclass_html_script_house');
	}

	function getsetting() {
		global $_G;

		$settings = array(
			'tids' => array(
				'title' => 'categorylist_infoid',
				'type' => 'text'
			),
			'sortids' => array(
				'title' => 'categorylist_sortids',
				'type' => 'mradio',
				'value' => array()
			),
			'styleids' => array(
				'title' => 'categorylist_styleids',
				'type' => 'select',
				'value' => array(
					array('style1', 'categorylist_styleids_style1'),
					array('style2', 'categorylist_styleids_style2'),
					array('style3', 'categorylist_styleids_style3'),
					array('style4', 'categorylist_styleids_style4'),
					array('style5', 'categorylist_styleids_style5'),
				)
			),
			'district' => array(
				'title' => 'categorylist_district',
				'type' => 'select',
				'default' => 0,
				'value' => array(
					array(0, 'categorylist_all')
				)
			),
			'pic' => array(
				'title' => 'categorylist_haspic',
				'type' => 'mradio',
				'default' => 0,
				'value' => array(
					array('0', 'categorylist_any'),
					array('1', 'categorylist_withpic_only'),
				)
			),
			'displayorder' => array(
				'title' => 'categorylist_top',
				'type' => 'mradio',
				'default' => 0,
				'value' => array(
					array('0', 'categorylist_any'),
					array('1', 'categorylist_top_only'),
				)
			),
			'recommend' => array(
				'title' => 'categorylist_recommend_thread',
				'type' => 'mradio',
				'default' => 0,
				'value' => array(
					array('0', 'categorylist_any'),
					array('1', 'categorylist_digest_only'),
				)
			),
			'startrow' => array(
				'title' => 'sortlist_startrow',
				'type' => 'text',
				'default' => 0
			),
			'showitems' => array(
				'title' => 'sortlist_showitems',
				'type' => 'text',
				'default' => 10
			),
		);

		// 分类信息
		if($settings['sortids']) {
			$defaultvalue = '';
			$query = DB::query("SELECT sortid, name FROM ".DB::table('category_sort')." WHERE cid=1 ORDER BY displayorder DESC");
			while($threadsort = DB::fetch($query)) {
				if(empty($defaultvalue)) {
					$defaultvalue = $threadsort['sortid'];
				}
				$settings['sortids']['value'][] = array($threadsort['sortid'], $threadsort['name']);
			}
			$settings['sortids']['default'] = $defaultvalue;
		}

		if($settings['district']) {
			$query = DB::query("SELECT aid, title FROM ".DB::table('category_area')." WHERE type='district' ORDER BY displayorder DESC");
			while($area = DB::fetch($query)) {
				$settings['district']['value'][] = array($area['aid'],$area['title']);
			}
		}
		return $settings;
	}

	function cookparameter($parameter) {
		return $parameter;
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);

		//参数准备
		loadcache('forums');
		$tids		= !empty($parameter['tids']) ? explode(',', $parameter['tids']) : array();
		$startrow	= !empty($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= !empty($parameter['showitems']) ? intval($parameter['showitems']) : 10;
		$district	= isset($parameter['district']) ? $parameter['district'] : '';
		$sortids	= isset($parameter['sortids']) ? $parameter['sortids'] : '';
		$style		= isset($parameter['styleids']) ? $parameter['styleids'] : '';
		$pic		= isset($parameter['pic']) ? $parameter['pic'] : '';
		$displayorder	= isset($parameter['displayorder']) ? $parameter['displayorder'] : '';
		$recommend	= isset($parameter['recommend']) ? $parameter['recommend'] : '';

		loadcache(array('category_option_'.$sortids, 'category_template_block_'.$sortids));
		$headerhtml = $_G['cache']['category_template_block_'.$sortids][$style]['header'];
		$footerhtml = $_G['cache']['category_template_block_'.$sortids][$style]['footer'];
		$loophtml = $_G['cache']['category_template_block_'.$sortids][$style]['loop'];
		$sortoption = $_G['cache']['category_option_'.$sortids];

		$areadatalist = $sortdata = $sortdatatids = array();

		$sql = 	($tids ? ' AND tid IN ('.dimplode($tids).')' : '')
			.($district ? ' AND district=\''.$district.'\'' : '')
			.($pic ? ' AND attachid>0' : '')
			.($displayorder ? ' AND displayorder>0' : '')
			.($recommend ? ' AND recommend>0' : '');

		$query = DB::query("SELECT aid, title FROM ".DB::table('category_area')." ORDER BY displayorder");
		while($areadata = DB::fetch($query)) {
			$areadatalist[$areadata['aid']] =  $areadata['title'];
		}

		$sortcondition['orderby'] = 'dateline';
		$sortcondition['ascdesc'] = 'DESC';

		$query = DB::query("SELECT tid, attachid, dateline, expiration, displayorder, recommend, attachnum, highlight, groupid, city, district, street FROM ".DB::table('category_sortvalue')."$sortids WHERE 1 $sql ORDER BY displayorder DESC, $sortcondition[orderby] $sortcondition[ascdesc] LIMIT $startrow,$items");
		while($thread = DB::fetch($query)) {
			if($thread['highlight']) {
				$string = sprintf('%02d', $thread['highlight']);
				$stylestr = sprintf('%03b', $string[0]);

				$thread['highlight'] = ' style="';
				$thread['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
				$thread['highlight'] .= $string[1] ? 'color: '.$colorarray[$string[1]] : '';
				$thread['highlight'] .= '"';
			} else {
				$thread['highlight'] = '';
			}
			$sortdatatids[]= $thread['tid'];
			$sortdata['datalist'][$thread['tid']]= $thread;
		}

		if($sortdatatids) {
			$query = DB::query("SELECT * FROM ".DB::table('category_house_thread')." WHERE tid IN (".dimplode($sortdatatids).")");
			while($data = DB::fetch($query)) {
				$sortdata['datalist'][$data['tid']]['subject'] .= $data['subject'];
				$sortdata['datalist'][$data['tid']]['author'] .= $data['author'];
				$sortdata['datalist'][$data['tid']]['authorid'] .= $data['authorid'];
			}
		}

		$html = $headerhtml ? $headerhtml : '';

		//数据获取
		foreach($sortdata as $datalist) {
			foreach($datalist as $data) {
				$htmldata = $this->showsort($data, $sortoption, $loophtml, $areadatalist);
				$html .= $htmldata ? $htmldata : '';
			}
		}

		$html .= $footerhtml ? $footerhtml : '';
		$html = $html ? $html : lang('block/categorylist', 'categorylist_template_empty');
		return array('html' => $html, 'data' => null);
	}

	function getcateimg($aid, $nocache = 0, $w = 140, $h = 140, $type = '') {
		global $_G;
		return 'category.php?mod=misc&action=thumb&aid='.$aid.'&size='.$w.'x'.$h.'&key='.rawurlencode($key).($nocache ? '&nocache=yes' : '').($type ? '&type='.$type : '');
	}

	function showsort($threaddata, $sortoption, $template, $areadatalist) {
		global $_G;
		$sortid = intval($threaddata['sortid']);
		$tid = intval($threaddata['tid']);

		$optiondata = $optionvaluelist = $optiontitlelist = $optionunitlist = $searchtitle = $searchvalue = $searchunit = $typetemplate = array();
		$query = DB::query("SELECT optionid, value FROM ".DB::table('category_sortoptionvar')." WHERE tid='$tid'");
		while($option = DB::fetch($query)) {
			$optiondata[$option['optionid']] = $option['value'];
		}

		$threaddata['image'] = '<img src="static/image/common/nophotosmall.gif">';
		$threaddata['subject'] = '<a href="'.'house.php?mod=view&tid='.$threaddata['tid'].'" target="_blank">'.$threaddata['subject'].'</a>';
		$threaddata['author'] = '<a href="'.'home.php?mod=space&uid='.$threaddata['authorid'].'" target="_blank">'.$threaddata['author'].'</a>';
		if($threaddata['attachid']) {
			$w = $h = 140;
			$aid = $threaddata['attachid'];
			$key = authcode("$aid\t$w\t$h", 'ENCODE', $_G['config']['security']['authkey']);
			$threaddata['image'] = '<img src="category.php?mod=misc&action=thumb&aid='.$aid.'&size='.$w.'x'.$h.'&key='.rawurlencode($key).'">';
		}

		$threaddata['city'] = $threaddata['city'] ? $areadatalist[$threaddata['city']] : '';
		$threaddata['district'] = $threaddata['district'] ? $areadatalist[$threaddata['district']] : '';
		$threaddata['street'] = $threaddata['street'] ? $areadatalist[$threaddata['street']] : '';

		if($sortoption && $template && $optiondata && $threaddata) {
			foreach($sortoption as $optionid => $option) {
				$optiontitlelist[] = $sortoption[$optionid]['title'];
				$optionunitlist[] = $sortoption[$optionid]['unit'];
				if($sortoption[$optionid]['type'] == 'checkbox') {
					$choicedata = '';
					foreach(explode("\t", $optiondata[$optionid]) as $choiceid) {
						$choicedata .= '<span>'.$sortoption[$optionid]['choices'][$choiceid].'</span>';
					}
					$optionvaluelist[] = $choicedata;
				} elseif(in_array($sortoption[$optionid]['type'], array('radio', 'select'))) {
					$optionvaluelist[] = $sortoption[$optionid]['choices'][$optiondata[$optionid]];
				} elseif($sortoption[$optionid]['type'] == 'url') {
					$optiondata[$optionid] = preg_match('/^(ftp|http|)[s]?:\/\//', $optiondata[$optionid]) ? $optiondata[$optionid] : $optiondata[$optionid];
					$optionvaluelist[] = $optiondata[$optionid] ? "<a href=\"".$optiondata[$optionid]."\" target=\"_blank\">".$optiondata[$optionid]."</a>" : '';
				} elseif($sortoption[$optionid]['type'] == 'textarea') {
					$optionvaluelist[] = $optiondata[$optionid] ? nl2br($optiondata[$optionid]) : '';
				} else {
					$optionvaluelist[] = $optiondata[$optionid] ? $optiondata[$optionid] : $sortoption[$optionid]['defaultvalue'];
				}
			}

			foreach($sortoption as $option) {
				$searchtitle[] = '/{('.$option['identifier'].')}/i';
				$searchvalue[] = '/\[('.$option['identifier'].')value\]/i';
				$searchunit[] = '/\[('.$option['identifier'].')unit\]/i';
			}

			$typetemplate = preg_replace(array("/\{city\}/i", "/\{district\}/i", "/\{street\}/i", "/\{image\}/i", "/\{author\}/i", "/\{subject\}/i", "/\{dateline\}/i", "/\{url\}/i", "/\[url\](.+?)\[\/url\]/i"),
							array(
								$threaddata['city'],
								$threaddata['district'],
								$threaddata['street'],
								$threaddata['image'],
								$threaddata['author'],
								$threaddata['subject'],
								dgmdate($threaddata['dateline'], 'm-d'),
								"house.php?mod=view&tid=$tid",
								"<a href=\""."house.php?mod=view&tid=$tid\" target=\"_blank\">\\1</a>"
							), stripslashes($template));
			$typetemplate = preg_replace($searchtitle, $optiontitlelist, $typetemplate);
			$typetemplate = preg_replace($searchvalue, $optionvaluelist, $typetemplate);
			$typetemplate = preg_replace($searchunit, $optionunitlist, $typetemplate);
		}

		return $typetemplate;
	}

}


?>