<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_thread.php 11780 2010-06-13 02:11:52Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class block_thread {
	var $setting = array();

	function block_thread(){
		$this->setting = array(
			'tids' => array(
				'title' => 'threadlist_tids',
				'type' => 'text'
			),
			'uids' => array(
				'title' => 'threadlist_uids',
				'type' => 'text'
			),
			'keyword' => array(
				'title' => 'threadlist_keyword',
				'type' => 'text'
			),
			'fids'	=> array(
				'title' => 'threadlist_fids',
				'type' => 'mselect',
				'value' => array()
			),
			'typeids' => array(
				'title' => 'threadlist_typeids',
				'type' => 'text'
			),
			'sortids' => array(
				'title' => 'threadlist_sortids',
				'type' => 'mselect',
				'value' => array()
			),
			'digest' => array(
				'title' => 'threadlist_digest',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'threadlist_digest_1'),
					array(2, 'threadlist_digest_2'),
					array(3, 'threadlist_digest_3'),
					array(0, 'threadlist_digest_0')
				),
			),
			'stick' => array(
				'title' => 'threadlist_stick',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'threadlist_stick_1'),
					array(2, 'threadlist_stick_2'),
					array(3, 'threadlist_stick_3'),
					array(0, 'threadlist_stick_0')
				),
			),
			'recommend' => array(
				'title' => 'threadlist_recommend',
				'type' => 'radio'
			),
			'special' => array(
				'title' => 'threadlist_special',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'threadlist_special_1'),
					array(2, 'threadlist_special_2'),
					array(3, 'threadlist_special_3'),
					array(4, 'threadlist_special_4'),
					array(5, 'threadlist_special_5'),
					array(0, 'threadlist_special_0'),
				),
				'default' => array('0')
			),
			'viewmod' => array(
				'title' => 'threadlist_viewmod',
				'type' => 'radio'
			),
			'rewardstatus' => array(
				'title' => 'threadlist_special_reward',
				'type' => 'mradio',
				'value' => array(
					array(0, 'threadlist_special_reward_0'),
					array(1, 'threadlist_special_reward_1'),
					array(2, 'threadlist_special_reward_2')
				),
				'default' => 0,
			),
			'picrequired' => array(
				'title' => 'threadlist_picrequired',
				'type' => 'radio',
				'value' => '0'
			),
			'orderby' => array(
				'title' => 'threadlist_orderby',
				'type'=> 'mradio',
				'value' => array(
					array('lastpost', 'threadlist_orderby_lastpost'),
					array('dateline', 'threadlist_orderby_dateline'),
					array('replies', 'threadlist_orderby_replies'),
					array('views', 'threadlist_orderby_views'),
					array('heats', 'threadlist_orderby_heats'),
					array('recommends', 'threadlist_orderby_recommends'),
				),
				'default' => 'lastpost'
			),
			'lastpost' => array(
				'title' => 'threadlist_lastpost',
				'type'=> 'mradio',
				'value' => array(
					array('0', 'threadlist_lastpost_nolimit'),
					array('3600', 'threadlist_lastpost_hour'),
					array('86400', 'threadlist_lastpost_day'),
					array('604800', 'threadlist_lastpost_week'),
					array('2592000', 'threadlist_lastpost_month'),
				),
				'default' => '0'
			),
			'titlelength' => array(
				'title' => 'threadlist_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'summarylength' => array(
				'title' => 'threadlist_summarylength',
				'type' => 'text',
				'default' => 80
			),
			'startrow' => array(
				'title' => 'threadlist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_thread_script_thread');
	}

	function blockclass() {
		return array('thread', lang('blockclass', 'blockclass_forum_thread'));
	}

	function fields() {
		return array(
					'url' => array('name' => lang('blockclass', 'blockclass_thread_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
					'title' => array('name' => lang('blockclass', 'blockclass_thread_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
					'pic' => array('name' => lang('blockclass', 'blockclass_thread_field_pic'), 'formtype' => 'pic', 'datatype' => 'pic'),
					'summary' => array('name' => lang('blockclass', 'blockclass_thread_field_summary'), 'formtype' => 'summary', 'datatype' => 'summary'),
					'author' => array('name' => lang('blockclass', 'blockclass_thread_field_author'), 'formtype' => 'text', 'datatype' => 'string'),
					'authorid' => array('name' => lang('blockclass', 'blockclass_thread_field_authorid'), 'formtype' => 'text', 'datatype' => 'int'),
					'avatar' => array('name' => lang('blockclass', 'blockclass_thread_field_avatar'), 'formtype' => 'text', 'datatype' => 'string'),
					'avatar_big' => array('name' => lang('blockclass', 'blockclass_thread_field_avatar_big'), 'formtype' => 'text', 'datatype' => 'string'),
					'icon' => array('name' => lang('blockclass', 'blockclass_thread_field_icon'), 'formtype' => 'text', 'datatype' => 'string'),
					'forumurl' => array('name' => lang('blockclass', 'blockclass_thread_field_forumurl'), 'formtype' => 'text', 'datatype' => 'string'),
					'forumname' => array('name' => lang('blockclass', 'blockclass_thread_field_forumname'), 'formtype' => 'text', 'datatype' => 'string'),
					'typename' => array('name' => lang('blockclass', 'blockclass_thread_field_typename'), 'formtype' => 'text', 'datatype' => 'string'),
					'typeicon' => array('name' => lang('blockclass', 'blockclass_thread_field_typeicon'), 'formtype' => 'text', 'datatype' => 'string'),
					'typeurl' => array('name' => lang('blockclass', 'blockclass_thread_field_typeurl'), 'formtype' => 'text', 'datatype' => 'string'),
					'sortname' => array('name' => lang('blockclass', 'blockclass_thread_field_sortname'), 'formtype' => 'text', 'datatype' => 'string'),
					'sorturl' => array('name' => lang('blockclass', 'blockclass_thread_field_sorturl'), 'formtype' => 'text', 'datatype' => 'string'),
					'posts' => array('name' => lang('blockclass', 'blockclass_thread_field_posts'), 'formtype' => 'text', 'datatype' => 'int'),
					'todayposts' => array('name' => lang('blockclass', 'blockclass_thread_field_todayposts'), 'formtype' => 'text', 'datatype' => 'int'),
					'lastpost' => array('name' => lang('blockclass', 'blockclass_thread_field_lastpost'), 'formtype' => 'date', 'datatype' => 'date'),
					'dateline' => array('name' => lang('blockclass', 'blockclass_thread_field_dateline'), 'formtype' => 'date', 'datatype' => 'date'),
					'replies' => array('name' => lang('blockclass', 'blockclass_thread_field_replies'), 'formtype' => 'text', 'datatype' => 'int'),
					'views' => array('name' => lang('blockclass', 'blockclass_thread_field_views'), 'formtype' => 'text', 'datatype' => 'int'),
					'heats' => array('name' => lang('blockclass', 'blockclass_thread_field_heats'), 'formtype' => 'text', 'datatype' => 'int'),
					'recomments' => array('name' => lang('blockclass', 'blockclass_thread_field_recomments'), 'formtype' => 'text', 'datatype' => 'int'),
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
		if($settings['sortids']) {
			$settings['sortids']['value'][] = array(0, 'threadlist_sortids_all');
			$query = DB::query("SELECT typeid, name, special FROM ".DB::table('forum_threadtype')." WHERE special>'0' ORDER BY typeid DESC");
			while($threadtype = DB::fetch($query)) {
				$settings['sortids']['value'][] = array($threadtype['typeid'], $threadtype['name']);
			}
		}
		return $settings;
	}

	function cookparameter($parameter) {
		return $parameter;
	}

	function getdata($style, $parameter) {
		global $_G;

		$returndata = array('html' => '', 'data' => '');
		$parameter = $this->cookparameter($parameter);

		loadcache('forums');
		$tids		= !empty($parameter['tids']) ? explode(',', $parameter['tids']) : array();
		$uids		= !empty($parameter['uids']) ? explode(',', $parameter['uids']) : array();
		$startrow	= isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= !empty($parameter['items']) ? intval($parameter['items']) : 10;
		$digest		= isset($parameter['digest']) ? $parameter['digest'] : 0;
		$stick		= isset($parameter['stick']) ? $parameter['stick'] : 0;
		$orderby	= isset($parameter['orderby']) ? (in_array($parameter['orderby'],array('lastpost','dateline','replies','views','heats','recommends')) ? $parameter['orderby'] : 'lastpost') : 'lastpost';
		$lastpost	= isset($parameter['lastpost']) ? intval($parameter['lastpost']) : 0;
		$titlelength	= !empty($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$summarylength	= !empty($parameter['summarylength']) ? intval($parameter['summarylength']) : 80;
		$recommend	= !empty($parameter['recommend']) ? 1 : 0;
		$keyword	= !empty($parameter['keyword']) ? $parameter['keyword'] : '';
		$typeids	= !empty($parameter['typeids']) ? explode(',',$parameter['typeids']) : array();
		$sortids	= !empty($parameter['sortids']) && !in_array(0, (array)$parameter['sortids']) ? $parameter['sortids'] : array();
		$special	= !empty($parameter['special']) ? $parameter['special'] : array();
		$rewardstatus	= !empty($parameter['rewardstatus']) ? intval($parameter['rewardstatus']) : 0;
		$picrequired	= !empty($parameter['picrequired']) ? 1 : 0;
		$viewmod	= !empty($parameter['viewmod']) ? 1 : 0;

		$fids = array();
		if(!empty($parameter['fids'])) {
			if($parameter['fids'][0] == '0') {
				unset($parameter['fids'][0]);
			}
			$fids = $parameter['fids'];
		}
		if(empty($fids)) {
			if(!empty($_G['setting']['allowviewuserthread'])) {
				$fids = $_G['setting']['allowviewuserthread'];
			} else {
				return $returndata;
			}
		} else {
			$fids = dimplode($fids);
		}

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		require_once libfile('function/post');

		$datalist = $list = array();
		$threadtypeids = array();
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
		$sql = ($fids ? ' AND t.fid IN ('.$fids.')' : '')
			.($tids ? ' AND t.tid IN ('.dimplode($tids).')' : '')
			.($uids ? ' AND t.authorid IN ('.dimplode($uids).')' : '')
			.($typeids ? ' AND t.typeid IN ('.dimplode($typeids).')' : '')
			.($sortids ? ' AND t.sortid IN ('.dimplode($sortids).')' : '')
			.($special ? ' AND t.special IN ('.dimplode($special).')' : '')
			.((in_array(3, $special) && $rewardstatus) ? ($rewardstatus == 1 ? ' AND t.price < 0' : ' AND t.price > 0') : '')
			.($digest ? ' AND t.digest IN ('.dimplode($digest).')' : '')
			.($stick ? ' AND t.displayorder IN ('.dimplode($stick).')' : '')
			.($picrequired ? ' AND t.attachment = 2' : '')
			.($bannedids ? ' AND t.tid NOT IN ('.dimplode($bannedids).')' : '')
			.$keyword
			." AND t.closed='0' AND t.isgroup='0'";

		if($lastpost) {
			$time = TIMESTAMP - $lastpost;
			$sql .= " AND t.lastpost >= '$time'";
		}
		if($orderby == 'heats') {
			$_G['setting']['indexhot']['days'] = !empty($_G['setting']['indexhot']['days']) ? intval($_G['setting']['indexhot']['days']) : 8;
			$heatdateline = TIMESTAMP - 86400 * $_G['setting']['indexhot']['days'];
			$sql .= " AND t.dateline>'$heatdateline' AND t.heats>'0'";
		}
		$sqlfrom = "FROM `".DB::table('forum_thread')."` t";
		if($recommend) {
			$sqlfrom .= " INNER JOIN `".DB::table('forum_forumrecommend')."` fc ON fc.tid=t.tid";
		}
		$query = DB::query("SELECT t.*
			$sqlfrom WHERE t.readperm='0'
			$sql
			AND t.displayorder>='0'
			ORDER BY t.$orderby DESC
			LIMIT $startrow,$items;"
			);
		$threadsorts = $threadtypes = null;
		while($data = DB::fetch($query)) {
			$_G['thread'][$data['tid']] = $data;
			if($style['getpic'] && $data['attachment']=='2') {
				$pic = $this->getpic($data['tid']);
				$data['attachment'] = $pic['attachment'];
				$data['remote'] = $pic['remote'];
			}
			if($data['sortid'] && null===$threadsorts) {
				$threadsorts = array();
				$querytmp = DB::query("SELECT typeid, name, special FROM ".DB::table('forum_threadtype')." WHERE special>'0'");
				while($value = DB::fetch($querytmp)) {
					$threadsorts[$value['typeid']] = $value;
				}
			}
			if($data['typeid'] && null===$threadtypes) {
				$threadtypes = array();
				$querytmp = DB::query("SELECT * FROM ".DB::table('forum_threadclass'));
				while($value = DB::fetch($querytmp)) {
					$threadtypes[$value['typeid']] = $value;
				}
			}
			$list[] = array(
				'id' => $data['tid'],
				'idtype' => 'tid',
				'title' => cutstr(str_replace('\\\'', '&#39;', addslashes($data['subject'])), $titlelength, ''),
				'url' => 'forum.php?mod=viewthread&tid='.$data['tid'].($viewmod ? '&from=portal' : ''),
				'pic' => $data['attachment'] ? 'forum/'.$data['attachment'] : STATICURL.'image/common/nophoto.gif',
				'picflag' => $data['attachment'] ? ($data['remote'] ? '2' : '1') : '0',
				'summary' => $style['getsummary'] ? $this->getthread($data['tid'], $summarylength) : '',
				'fields' => array(
					'fulltitle' => str_replace('\\\'', '&#39;', addslashes($data['subject'])),
					'threads' => $data['threads'],
					'author' => $data['author'] ? $data['author'] : 'Anonymous',
					'authorid' => $data['author'] ? $data['authorid'] : 0,
					'avatar' => avatar(($data['author'] ? $data['authorid'] : 0), 'small', true),
					'avatar_big' => avatar(($data['author'] ? $data['authorid'] : 0), 'middle', true),
					'posts' => $data['posts'],
					'todayposts' => $data['todayposts'],
					'lastpost' => $data['lastpost'],
					'dateline' => $data['dateline'],
					'replies' => $data['replies'],
					'forumurl' => 'forum.php?mod=forumdisplay&fid='.$data['fid'],
					'forumname' => $_G['cache']['forums'][$data['fid']]['name'],
					'typename' => $threadtypes[$data['typeid']]['name'],
					'typeicon' => $threadtypes[$data['typeid']]['icon'],
					'typeurl' => 'forum.php?mod=forumdisplay&fid='.$data['fid'].'&filter=typeid&typeid='.$data['typeid'],
					'sortname' => $threadsorts[$data['sortid']]['name'],
					'sorturl' => 'forum.php?mod=forumdisplay&fid='.$data['fid'].'&filter=sortid&sortid='.$data['sortid'],
					'views' => $data['views'],
					'heats' => $data['heats'],
					'recommends' => $data['recommends'],
					'hourviews' => $data['views'],
					'todayviews' => $data['views'],
					'weekviews' => $data['views'],
					'monthviews' => $data['views']
				)
			);
		}
		$returndata['data'] = $list;
		return $returndata;
	}

	function getthread($tid, $messagelength = 80, $nospecial=false) {
		global $_G;
		if(!$tid) {
			return '';
		}
		require_once libfile('function/post');
		if(empty($_G['thread'][$tid])) {
			$thread = DB::fetch_first("SELECT subject, fid, special, price, posttableid FROM ".DB::table('forum_thread')." WHERE tid='$tid'");
			$_G['thread'][$tid] = $thread;
		} else {
			$thread = $_G['thread'][$tid];
		}
		if($thread['posttableid'] == 0) {
			$posttable = 'forum_post';
		} else {
			$posttable = "forum_post_{$thread['posttableid']}";
		}
		$fid = $thread['fid'];
		if($nospecial) {
			$thread['special'] = 0;
		}
		if($thread['special'] == 1) {
			$multiple = DB::result_first("SELECT multiple FROM ".DB::table('forum_poll')." WHERE tid='$tid'");
			$optiontype = $multiple ? 'checkbox' : 'radio';
			$query = DB::query("SELECT polloptionid, polloption FROM ".DB::table('forum_polloption')." WHERE tid='$tid' ORDER BY displayorder");
			while($polloption = DB::fetch($query)) {
				$polloption['polloption'] = preg_replace("/\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i",
					"<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $polloption['polloption']);
				$polloptions[] = $polloption;
			}
		} elseif($thread['special'] == 2) {
			$trade = DB::fetch_first("SELECT subject, price, credit, aid, pid FROM ".DB::table('forum_trade')." WHERE tid='$tid' ORDER BY displayorder DESC LIMIT 1");
			$trade['aid'] = $trade['aid'] ? getforumimg($trade['aid']) : '';
			$trades[] = $trade;
		} elseif($thread['special'] == 3) {
			$extcredits = $_G['settings']['extcredits'];
			$creditstransextra = $_G['settings']['creditstransextra'];
			$rewardend = $thread['price'] < 0;
			$rewardprice = abs($thread['price']);
			$message = messagecutstr(DB::result_first("SELECT message FROM ".DB::table($posttable)." WHERE tid='$tid' AND first=1"), $messagelength);
		} elseif($thread['special'] == 4) {
			$message = messagecutstr(DB::result_first("SELECT message FROM ".DB::table($posttable)." WHERE tid='$tid' AND first=1"), $messagelength);
			$activity = DB::fetch_first("SELECT aid, number, applynumber FROM ".DB::table('forum_activity')." WHERE tid='$tid'");
			$activity['aid'] = $activity['aid'] ? getforumimg($activity['aid']) : '';
			$activity['aboutmember'] = $activity['number'] - $activity['applynumber'];
		} elseif($thread['special'] == 5) {
			$message = messagecutstr(DB::result_first("SELECT message FROM ".DB::table($posttable)." WHERE tid='$tid' AND first=1"), $messagelength);
			$debate = DB::fetch_first("SELECT affirmdebaters, negadebaters, affirmvotes, negavotes, affirmpoint, negapoint FROM ".DB::table('forum_debate')." WHERE tid='$tid'");
			$debate['affirmvoteswidth'] = $debate['affirmvotes']  ? intval(80 * (($debate['affirmvotes'] + 1) / ($debate['affirmvotes'] + $debate['negavotes'] + 1))) : 1;
			$debate['negavoteswidth'] = $debate['negavotes']  ? intval(80 * (($debate['negavotes'] + 1) / ($debate['affirmvotes'] + $debate['negavotes'] + 1))) : 1;
			@require_once libfile('function/discuzcode');
			$debate['affirmpoint'] = discuzcode($debate['affirmpoint'], 0, 0, 0, 1, 1, 0, 0, 0, 0, 0);
			$debate['negapoint'] = discuzcode($debate['negapoint'], 0, 0, 0, 1, 1, 0, 0, 0, 0, 0);
		} else {
			$message = messagecutstr(DB::result_first("SELECT message FROM ".DB::table($posttable)." WHERE tid='$tid' AND first=1"), $messagelength);
		}

		include template('common/block_thread');
		return $return;
	}

	function getpic($tid) {
		global $_G;
		if(!$tid) {
			return '';
		}
		require_once libfile('function/post');
		if(empty($_G['thread'][$tid])) {
			$thread = DB::fetch_first("SELECT subject, fid, special, price, posttableid FROM ".DB::table('forum_thread')." WHERE tid='$tid'");
			$_G['thread'][$tid] = $thread;
		} else {
			$thread = $_G['thread'][$tid];
		}
		if($thread['posttableid'] == 0) {
			$posttable = 'forum_post';
		} else {
			$posttable = "forum_post_{$thread['posttableid']}";
		}
		$pic = DB::fetch_first("SELECT fa.attachment, fa.remote FROM ".DB::table($posttable)." fp LEFT JOIN ".DB::table('forum_attachment')." fa ON fp.tid=fa.tid AND fp.pid=fa.pid WHERE fp.tid='$tid' AND fp.first=1 AND fa.isimage IN (1, -1) LIMIT 0,1");
		return $pic;
	}
}


?>