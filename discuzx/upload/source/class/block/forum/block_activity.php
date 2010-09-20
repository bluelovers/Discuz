<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_activity.php 11418 2010-06-02 02:28:01Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class block_activity {

	var $setting = array();

	function block_activity(){
		$this->setting = array(
			'tids' => array(
				'title' => 'activitylist_tids',
				'type' => 'text'
			),
			'uids' => array(
				'title' => 'activitylist_uids',
				'type' => 'text'
			),
			'keyword' => array(
				'title' => 'activitylist_keyword',
				'type' => 'text'
			),
			'fids'	=> array(
				'title' => 'activitylist_fids',
				'type' => 'mselect',
				'value' => array()
			),
			'viewmod' => array(
				'title' => 'threadlist_viewmod',
				'type' => 'radio'
			),
			'digest' => array(
				'title' => 'activitylist_digest',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'activitylist_digest_1'),
					array(2, 'activitylist_digest_2'),
					array(3, 'activitylist_digest_3'),
					array(0, 'activitylist_digest_0')
				),
			),
			'stick' => array(
				'title' => 'activitylist_stick',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'activitylist_stick_1'),
					array(2, 'activitylist_stick_2'),
					array(3, 'activitylist_stick_3'),
					array(0, 'activitylist_stick_0')
				),
			),
			'recommend' => array(
				'title' => 'activitylist_recommend',
				'type' => 'radio'
			),
			'place' => array(
				'title' => 'activitylist_place',
				'type' => 'text'
			),
			'class' => array(
				'title' => 'activitylist_class',
				'type' => 'select',
				'value' => array()
			),
			'gender' => array(
				'title' => 'activitylist_gender',
				'type' => 'mradio',
				'value' => array(
					array('', 'activitylist_gender_0'),
					array('1', 'activitylist_gender_1'),
					array('2', 'activitylist_gender_2'),
				),
				'default' => ''
			),
			'orderby' => array(
				'title' => 'activitylist_orderby',
				'type'=> 'mradio',
				'value' => array(
					array('dateline', 'activitylist_orderby_dateline'),
					array('weekstart', 'activitylist_orderby_weekstart'),
					array('monthstart', 'activitylist_orderby_monthstart'),
					array('weekexp', 'activitylist_orderby_weekexp'),
					array('monthexp', 'activitylist_orderby_monthexp'),
					array('weekhot', 'activitylist_orderby_weekhot'),
					array('monthhot', 'activitylist_orderby_monthhot'),
				),
				'default' => 'dateline'
			),
			'titlelength' => array(
				'title' => 'activitylist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'activitylist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'activitylist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_activity_script_activity');
	}

	function blockclass() {
		return array('activity', lang('blockclass', 'blockclass_activity_activity'));
	}

	function fields() {
		return array(
					'url' => array('name' => lang('blockclass', 'blockclass_activity_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
					'title' => array('name' => lang('blockclass', 'blockclass_activity_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
					'pic' => array('name' => lang('blockclass', 'blockclass_activity_field_pic'), 'formtype' => 'pic', 'datatype' => 'pic'),
					'summary' => array('name' => lang('blockclass', 'blockclass_activity_field_summary'), 'formtype' => 'summary', 'datatype' => 'summary'),
					'time' => array('name' => lang('blockclass', 'blockclass_activity_field_time'), 'formtype' => 'text', 'datatype' => 'text'),
					'expiration' => array('name' => lang('blockclass', 'blockclass_activity_field_expiration'), 'formtype' => 'text', 'datatype' => 'text'),
					'author' => array('name' => lang('blockclass', 'blockclass_activity_field_author'), 'formtype' => 'text', 'datatype' => 'text'),
					'authorid' => array('name' => lang('blockclass', 'blockclass_activity_field_authorid'), 'formtype' => 'text', 'datatype' => 'int'),
					'cost' => array('name' => lang('blockclass', 'blockclass_activity_field_cost'), 'formtype' => 'text', 'datatype' => 'int'),
					'place' => array('name' => lang('blockclass', 'blockclass_activity_field_place'), 'formtype' => 'text', 'datatype' => 'text'),
					'class' => array('name' => lang('blockclass', 'blockclass_activity_field_class'), 'formtype' => 'text', 'datatype' => 'text'),
					'gender' => array('name' => lang('blockclass', 'blockclass_activity_field_gender'), 'formtype' => 'text', 'datatype' => 'text'),
					'number' => array('name' => lang('blockclass', 'blockclass_activity_field_number'), 'formtype' => 'text', 'datatype' => 'int'),
					'applynumber' => array('name' => lang('blockclass', 'blockclass_activity_field_applynumber'), 'formtype' => 'text', 'datatype' => 'int'),
				);
	}

	function fieldsconvert() {
		return array(
				'group_activity' => array(
					'name' => lang('blockclass', 'blockclass_group_activity'),
					'script' => 'groupactivity',
					'searchkeys' => array(),
					'replacekeys' => array(),
				),
			);
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
		$activitytype = explode("\n", $_G['setting']['activitytype']);
		$settings['class']['value'][] = array('', 'activitylist_class_all');
		foreach($activitytype as $item) {
			$settings['class']['value'][] = array($item, $item);
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
		$uids		= !empty($parameter['uids']) ? explode(',', $parameter['uids']) : array();
		$startrow	= !empty($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= !empty($parameter['items']) ? intval($parameter['items']) : 10;
		$digest		= isset($parameter['digest']) ? $parameter['digest'] : 0;
		$stick		= isset($parameter['stick']) ? $parameter['stick'] : 0;
		$orderby	= isset($parameter['orderby']) ? (in_array($parameter['orderby'],array('dateline','weekstart','monthstart','weekexp','monthexp','weekhot','monthhot')) ? $parameter['orderby'] : 'dateline') : 'dateline';
		$titlelength	= !empty($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$summarylength	= !empty($parameter['summarylength']) ? intval($parameter['summarylength']) : 80;
		$recommend	= !empty($parameter['recommend']) ? 1 : 0;
		$keyword	= !empty($parameter['keyword']) ? $parameter['keyword'] : '';
		$place		= !empty($parameter['place']) ? $parameter['place'] : '';
		$class		= !empty($parameter['class']) ? $parameter['class'] : '';
		$gender		= !empty($parameter['gender']) ? intval($parameter['gender']) : '';
		$viewmod	= !empty($parameter['viewmod']) ? 1 : 0;

		$fids = array();
		if(!empty($parameter['fids'])) {
			if($parameter['fids'][0] == '0') {
				unset($parameter['fids'][0]);
			}
			$fids = $parameter['fids'];
		}

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		require_once libfile('function/post');

		$datalist = $list = array();
		if($keyword) {
			if(preg_match("(AND|\+|&|\s)", $keyword) && !preg_match("(OR|\|)", $keyword)) {
				$andor = ' AND ';
				$keywordsrch = '1';
				$keyword = preg_replace("/( AND |&| )/is", "+", $keyword);
			} else {
				$andor = ' OR ';
				$keywordsrch = '0';
				$keyword = preg_replace("/( OR |\|)/is", "+", $keyword);
			}
			$keyword = str_replace('*', '%', addcslashes($keyword, '%_'));
			foreach(explode('+', $keyword) as $text) {
				$text = trim($text);
				if($text) {
					$keywordsrch .= $andor;
					$keywordsrch .= "t.subject LIKE '%$text%'";
				}
			}
			$keyword = " AND ($keywordsrch)";
		} else {
			$keyword = '';
		}
		$sql = ($fids ? ' AND t.fid IN ('.dimplode($fids).')' : '')
			.$keyword
			.($tids ? ' AND t.tid IN ('.dimplode($tids).')' : '')
			.($bannedids ? ' AND t.tid NOT IN ('.dimplode($bannedids).')' : '')
			.($digest ? ' AND t.digest IN ('.dimplode($digest).')' : '')
			.($stick ? ' AND t.displayorder IN ('.dimplode($stick).')' : '')
			." AND t.closed='0' AND t.isgroup='0'";
		$where = '';
		if(in_array($orderby, array('weekstart','monthstart'))) {
			$historytime = 0;
			switch($orderby) {
				case 'weekstart':
					$historytime = TIMESTAMP + 86400 * 7;
				break;
				case 'monthstart':
					$historytime = TIMESTAMP + 86400 * 30;
				break;
			}
			$where = ' WHERE a.starttimefrom>='.TIMESTAMP.' AND a.starttimefrom<='.$historytime;
			$orderby = 'a.starttimefrom ASC';
		} elseif(in_array($orderby, array('weekexp','monthexp'))) {
			$historytime = 0;
			switch($orderby) {
				case 'weekexp':
					$historytime = TIMESTAMP + 86400 * 7;
				break;
				case 'monthexp':
					$historytime = TIMESTAMP + 86400 * 30;
				break;
			}
			$where = ' WHERE a.expiration>='.TIMESTAMP.' AND a.expiration<='.$historytime;
			$orderby = 'a.expiration ASC';
		} elseif(in_array($orderby, array('weekhot','monthhot'))) {
			$historytime = 0;
			switch($orderby) {
				case 'weekhot':
					$historytime = TIMESTAMP + 86400 * 7;
				break;
				case 'monthhot':
					$historytime = TIMESTAMP + 86400 * 30;
				break;
			}
			$where = ' WHERE a.expiration>='.TIMESTAMP.' AND a.expiration<='.$historytime;
			$orderby = 'a.applynumber DESC';
		} else {
			$orderby = 't.dateline DESC';
		}
		$where .= $uids ? ' AND t.authorid IN ('.dimplode($uids).')' : '';
		if($gender) {
			$where .= " AND a.gender='$gender'";
		}
		$sqlfrom = " INNER JOIN `".DB::table('forum_thread')."` t ON t.tid=a.tid $sql AND t.displayorder>='0'";
		if($recommend) {
			$sqlfrom .= " INNER JOIN `".DB::table('forum_forumrecommend')."` fc ON fc.tid=tr.tid";
		}
		$query = DB::query("SELECT a.*, t.tid, t.subject, t.authorid, t.author
			FROM ".DB::table('forum_activity')." a $sqlfrom $where
			ORDER BY $orderby
			LIMIT $startrow,$items;"
			);
		require_once libfile('block_thread', 'class/block/forum');
		$bt = new block_thread();
		while($data = DB::fetch($query)) {
			$data['time'] = dgmdate($data['starttimefrom']);
			if($data['starttimeto']) {
				$data['time'] .= ' - '.dgmdate($data['starttimeto']);
			}
			$list[] = array(
				'id' => $data['tid'],
				'idtype' => 'tid',
				'title' => cutstr(str_replace('\\\'', '&#39;', $data['subject']), $titlelength, ''),
				'url' => 'forum.php?mod=viewthread&tid='.$data['tid'].($viewmod ? '&from=portal' : ''),
				'pic' => ($data['aid'] ? getforumimg($data['aid']) : IMGDIR.'/nophoto.gif'),
				'picflag' => '0',
				'summary' => !empty($style['getsummary']) ? $bt->getthread($data['tid'], $summarylength, true) : '',
				'fields' => array(
					'fulltitle' => str_replace('\\\'', '&#39;', addslashes($data['subject'])),
					'time' => $data['time'],
					'expiration' => $data['expiration'] ? dgmdate($data['expiration']) : 'N/A',
					'author' => $data['author'] ? $data['author'] : 'Anonymous',
					'authorid' => $data['authorid'] ? $data['authorid'] : 0,
					'cost' => $data['cost'],
					'place' => $data['place'],
					'class' => $data['class'],
					'gender' => $data['gender'],
					'number' => $data['number'],
					'applynumber' => $data['applynumber'],
				)
			);
		}
		return array('html' => '', 'data' => $list);
	}
}


?>