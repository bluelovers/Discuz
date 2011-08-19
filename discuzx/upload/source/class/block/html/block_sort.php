<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_sort.php 11418 2010-06-02 02:28:01Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('commonblock_html', 'class/block/html');

class block_sort extends commonblock_html {

	var $setting = array();

	function block_sort(){
		$this->setting = array(
			'tids' => array(
				'title' => 'sortlist_tids',
				'type' => 'text'
			),
			'fids'	=> array(
				'title' => 'sortlist_fids',
				'type' => 'mselect',
				'value' => array()
			),
			'sortids' => array(
				'title' => 'sortlist_sortids',
				'type' => 'mradio',
				'value' => array()
			),
			'digest' => array(
				'title' => 'sortlist_digest',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'sortlist_digest_1'),
					array(2, 'sortlist_digest_2'),
					array(3, 'sortlist_digest_3'),
					array(0, 'sortlist_digest_0')
				),
			),
			'stick' => array(
				'title' => 'sortlist_stick',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'sortlist_stick_1'),
					array(2, 'sortlist_stick_2'),
					array(3, 'sortlist_stick_3'),
					array(0, 'sortlist_stick_0')
				),
			),
			'recommend' => array(
				'title' => 'sortlist_recommend',
				'type' => 'radio'
			),
			'orderby' => array(
				'title' => 'sortlist_orderby',
				'type'=> 'mradio',
				'value' => array(
					array('lastpost', 'sortlist_orderby_lastpost'),
					array('dateline', 'sortlist_orderby_dateline'),
					array('replies', 'sortlist_orderby_replies'),
					array('views', 'sortlist_orderby_views'),
					array('heats', 'sortlist_orderby_heats'),
					array('recommends', 'sortlist_orderby_recommends'),
				),
				'default' => 'lastpost'
			),
			'lastpost' => array(
				'title' => 'sortlist_lastpost',
				'type'=> 'mradio',
				'value' => array(
					array('0', 'sortlist_lastpost_nolimit'),
					array('3600', 'sortlist_lastpost_hour'),
					array('86400', 'sortlist_lastpost_day'),
					array('604800', 'sortlist_lastpost_week'),
					array('2592000', 'sortlist_lastpost_month'),
				),
				'default' => '0'
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
	}

	function name() {
		return lang('blockclass', 'blockclass_html_script_sort');
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		if($settings['fids']) {
			loadcache('forums');
			$settings['fids']['value'][] = array(0, lang('portalcp', 'block_all_forum'));
			foreach($_G['cache']['forums'] as $fid => $forum) {
				$settings['fids']['value'][] = array($fid, ($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')).$forum['name']);
			}
		}
		if($settings['sortids']) {
			$defaultvalue = '';
			$query = DB::query("SELECT typeid, name, special FROM ".DB::table('forum_threadtype')." ORDER BY typeid DESC");
			while($threadtype = DB::fetch($query)) {
				if($threadtype['special']) {
					if(empty($defaultvalue)) {
						$defaultvalue = $threadtype['typeid'];
					}
					$settings['sortids']['value'][] = array($threadtype['typeid'], $threadtype['name']);
				}
			}
			$settings['sortids']['default'] = $defaultvalue;
		}
		return $settings;
	}

	function cookparameter($parameter) {
		return $parameter;
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);

		loadcache('forums');
		$tids		= !empty($parameter['tids']) ? explode(',', $parameter['tids']) : array();
		$fids		= isset($parameter['fids']) && !in_array(0, (array)$parameter['fids']) ? $parameter['fids'] : array_keys($_G['cache']['forums']);
		$startrow	= !empty($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= !empty($parameter['showitems']) ? intval($parameter['showitems']) : 10;
		$digest		= isset($parameter['digest']) ? $parameter['digest'] : 0;
		$stick		= isset($parameter['stick']) ? $parameter['stick'] : 0;
		$orderby	= isset($parameter['orderby']) ? (in_array($parameter['orderby'],array('lastpost','dateline','replies','views','heats','recommends')) ? $parameter['orderby'] : 'lastpost') : 'lastpost';
		$lastpost	= isset($parameter['lastpost']) ? intval($parameter['lastpost']) : 0;
		$recommend	= !empty($parameter['recommend']) ? 1 : 0;
		$sortids	= isset($parameter['sortids']) ? $parameter['sortids'] : '';

		if($fids) {
			$thefids = array();
			foreach($fids as $fid) {
				if($_G['cache']['forums'][$fid]['type']=='group') {
					$thefids[] = $fid;
				}
			}
			if($thefids) {
				foreach($_G['cache']['forums'] as $value) {
					if($value['fup'] && in_array($value['fup'], $thefids)) {
						$fids[] = intval($value['fid']);
					}
				}
			}
			$fids = array_unique($fids);
		}

		$datalist = $list = array();
		$threadtypeids = array();

		$sql = ($tids ? ' AND t.tid IN ('.dimplode($tids).')' : '')
			.($sortids ? ' AND t.sortid IN ('.dimplode($sortids).')' : '')
			.($fids ? ' AND t.fid IN ('.dimplode($fids).')' : '')
			.($digest ? ' AND t.digest IN ('.dimplode($digest).')' : '')
			.($stick ? ' AND t.displayorder IN ('.dimplode($stick).')' : '')
			." AND t.closed='0' AND t.isgroup='0'";
		if($lastpost) {
			$historytime = TIMESTAMP - $lastpost;
			$sql .= " AND t.dateline>='$historytime'";
		}
		if($orderby == 'heats') {
			$_G['setting']['indexhot']['days'] = !empty($_G['setting']['indexhot']['days']) ? intval($_G['setting']['indexhot']['days']) : 8;
			$heatdateline = TIMESTAMP - 86400 * $_G['setting']['indexhot']['days'];
			$sql .= " AND t.dateline>'$heatdateline' AND t.heats>'0'";
		}
		$sqlfrom = "FROM `".DB::table('forum_thread')."` t";
		$joinmethod = empty($tids) ? 'INNER' : 'LEFT';
		if($recommend) {
			$sqlfrom .= " $joinmethod JOIN `".DB::table('forum_forumrecommend')."` fc ON fc.tid=t.tid";
		}

		$html = '';
		$query = DB::query("SELECT t.tid,t.fid,t.readperm,t.author,t.authorid,t.subject,t.dateline,t.lastpost,t.lastposter,t.views,t.replies,t.highlight,t.digest,t.typeid,t.sortid,t.heats,t.recommends
			$sqlfrom WHERE 1 $sql
			AND t.readperm='0'
			AND t.displayorder>='0'
			ORDER BY t.$orderby DESC
			LIMIT $startrow,$items;"
			);
		while($data = DB::fetch($query)) {
			$html .= $this->showsort($data);
		}
		return array('html' => $html, 'data' => null);
	}

	function showsort($threaddata) {
		global $_G;
		$sortid = intval($threaddata['sortid']);
		$tid = intval($threaddata['tid']);
		loadcache(array('threadsort_option_'.$sortid, 'threadsort_template_'.$sortid));

		$template = $_G['cache']['threadsort_template_'.$sortid]['block'];
		$sortoption = $_G['cache']['threadsort_option_'.$sortid];

		$optiondata = $optionvaluelist = $optiontitlelist = $optionunitlist = $searchtitle = $searchvalue = $searchunit = array();
		$typetemplate = '';
		$query = DB::query("SELECT optionid, value FROM ".DB::table('forum_typeoptionvar')." WHERE tid='$tid'");
		while($option = DB::fetch($query)) {
			$optiondata[$option['optionid']] = $option['value'];
		}

		$threaddata['subject'] = '<a href="'.'forum.php?mod=viewthread&tid='.$threaddata['tid'].'" '.$threaddata['highlight'].' target="_blank">'.$threaddata['subject'].'</a>';
		$threaddata['author'] = '<a href="'.'home.php?mod=space&uid='.$threaddata['authorid'].'" target="_blank">'.$threaddata['author'].'</a>';

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
				} elseif($sortoption[$optionid]['type'] == 'radio') {
					$optionvaluelist[] = $sortoption[$optionid]['choices'][$optiondata[$optionid]];
				} elseif($sortoption[$optionid]['type'] == 'select') {
					$tmpchoiceid = $tmpidentifiervalue = array();
					foreach(explode('.', $optiondata[$optionid]) as $choiceid) {
						$tmpchoiceid[] = $choiceid;
						$tmpidentifiervalue[] = $option['choices'][implode('.', $tmpchoiceid)];
					}
					$optionvaluelist[] = implode(' &raquo; ', $tmpidentifiervalue);
					unset($tmpchoiceid, $tmpidentifiervalue);
				} elseif($sortoption[$optionid]['type'] == 'image') {
					if($optiondata[$optionid]) {
						$imgvalue = unserialize($optiondata[$optionid]);
						$optionvaluelist[] = $imgvalue['url'];
					} else {
						$optionvaluelist[] = STATICURL.'image/common/nophoto.gif';
					}
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

			$typetemplate = preg_replace(array("/\{author\}/i", "/\{subject\}/i", "/\{dateline\}/i", "/\{url\}/i", "/\[url\](.+?)\[\/url\]/i"),
							array(
								$threaddata['author'],
								$threaddata['subject'],
								dgmdate($threaddata['dateline'], 'n-j'),
								"forum.php?mod=viewthread&tid=$tid",
								"<a href=\""."forum.php?mod=viewthread&tid=$tid\" target=\"_blank\">\\1</a>"
							), stripslashes($template));
			$typetemplate = preg_replace($searchtitle, $optiontitlelist, $typetemplate);
			$typetemplate = preg_replace($searchvalue, $optionvaluelist, $typetemplate);
			$typetemplate = preg_replace($searchunit, $optionunitlist, $typetemplate);
		}

		return $typetemplate;
	}
}


?>