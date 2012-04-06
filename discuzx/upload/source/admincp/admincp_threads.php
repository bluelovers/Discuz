<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_threads.php 23581 2011-07-26 08:18:27Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

require_once libfile('function/post');

cpheader();

$optype = $_G['gp_optype'];
$fromumanage = $_G['gp_fromumanage'] ? 1 : 0;

if((!$operation && !$optype) || ($operation == 'group' && empty($optype))) {
	if(!submitcheck('searchsubmit', 1) && empty($_G['gp_search'])) {
		$newlist = 1;
		$_G['gp_intype'] = '';
		$_G['gp_detail'] = 1;
		$_G['gp_inforum'] = 'all';
		$_G['gp_starttime'] = dgmdate(TIMESTAMP - 86400 * 30, 'Y-n-j');
	}
	$intypes = '';
	if($_G['gp_inforum'] && $_G['gp_inforum'] != 'all' && $_G['gp_intype']) {
		$forumthreadtype = DB::result_first("SELECT threadtypes FROM ".DB::table('forum_forumfield')." WHERE fid='$_G[gp_inforum]'");
		if($forumthreadtype) {
			$forumthreadtype = unserialize($forumthreadtype);
			foreach($forumthreadtype['types'] as $typeid => $typename) {
				$intypes .= '<option value="'.$typeid.'"'.($typeid == $_G['gp_intype'] ? ' selected' : '').'>'.$typename.'</option>';
			}
		}
	}
	require_once libfile('function/forumlist');
	$forumselect = '<b>'.$lang['threads_search_forum'].':</b><br><br><select name="inforum" onchange="ajaxget(\'forum.php?mod=ajax&action=getthreadtypes&selectname=intype&fid=\' + this.value, \'forumthreadtype\')"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option><option value="">&nbsp;</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';
	$typeselect = $lang['threads_move_type'].' <span id="forumthreadtype"><select name="intype"><option value=""></option>'.$intypes.'</select></span>';
	if(isset($_G['gp_inforum'])) {
		$forumselect = preg_replace("/(\<option value=\"$_G[gp_inforum]\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
	}

	$sortselect = '';
	$query = DB::query("SELECT * FROM ".DB::table('forum_threadtype')." ORDER BY displayorder");
	while($type = DB::fetch($query)) {
		if($type['special']) {
			$sortselect .= '<option value="'.$type['typeid'].'">&nbsp;&nbsp;> '.$type['name'].'</option>';
		}
	}

	if(isset($_G['gp_insort'])) {
		$sortselect = preg_replace("/(\<option value=\"{$_G['gp_insort']}\")(\>)/", "\\1 selected=\"selected\" \\2", $sortselect);
	}

	echo <<<EOT
<script src="static/js/calendar.js"></script>
<script type="text/JavaScript">
	function page(number) {
		$('threadforum').page.value=number;
		$('threadforum').searchsubmit.click();
	}
</script>
EOT;
	shownav('topic', 'nav_maint_threads'.($operation ? '_'.$operation : ''));
	showsubmenu('nav_maint_threads'.($operation ? '_'.$operation : ''), array(
		array('newlist', 'threads'.($operation ? '&operation='.$operation : ''), !empty($newlist)),
		array('search', 'threads&search=true'.($operation ? '&operation='.$operation : ''), empty($newlist)),
	));
	empty($newlist) && showsubmenusteps('', array(
		array('threads_search', !$_G['gp_searchsubmit']),
		array('nav_maint_threads', $_G['gp_searchsubmit'])
	));
	if(empty($newlist)) {
		$search_tips = 1;
		showtips('threads_tips');
	}
	showtagheader('div', 'threadsearch', !submitcheck('searchsubmit', 1) && empty($newlist));
	showformheader('threads'.($operation ? '&operation='.$operation : ''), '', 'threadforum');
	showhiddenfields(array('page' => $page, 'pp' => $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage']));
	showtableheader();
	showsetting('threads_search_detail', 'detail', $_G['gp_detail'], 'radio');
	if($operation != 'group') {
		showtablerow('', array('class="rowform" colspan="2" style="width:auto;"'), array($forumselect.$typeselect));
	}
	showsetting('threads_search_perpage', '', $_G['gp_perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
	if(!$fromumanage) {
		empty($_G['gp_starttime']) && $_G['gp_starttime'] = date('Y-m-d', time() - 86400 * 30);
	}
	echo '<input type="hidden" name="fromumanage" value="'.$fromumanage.'">';
	showsetting('threads_search_time', array('starttime', 'endtime'), array($_G['gp_starttime'], $_G['gp_endtime']), 'daterange');
	showsetting('threads_search_user', 'users', $_G['gp_users'], 'text');
	showsetting('threads_search_keyword', 'keywords', $_G['gp_keywords'], 'text');

	showtagheader('tbody', 'advanceoption');
	showsetting('threads_search_sort', '', '', '<select name="insort"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option><option value="">&nbsp;</option><option value="0">&nbsp;&nbsp;> '.$lang['threads_search_type_none'].'</option>'.$sortselect.'</select>');
	showsetting('threads_search_viewrange', array('viewsmore', 'viewsless'), array($_G['gp_viewsmore'], $_G['gp_viewsless']), 'range');
	showsetting('threads_search_replyrange', array('repliesmore', 'repliesless'), array($_G['gp_repliesmore'], $_G['gp_repliesless']), 'range');
	showsetting('threads_search_readpermmore', 'readpermmore', $_G['gp_readpermmore'], 'text');
	showsetting('threads_search_pricemore', 'pricemore', $_G['gp_pricemore'], 'text');
	showsetting('threads_search_noreplyday', 'noreplydays', $_G['gp_noreplydays'], 'text');
	showsetting('threads_search_type', array('specialthread', array(
		array(0, cplang('unlimited'), array('showspecial' => 'none')),
		array(1, cplang('threads_search_include_yes'), array('showspecial' => '')),
		array(2, cplang('threads_search_include_no'), array('showspecial' => '')),
	), TRUE), $_G['gp_specialthread'], 'mradio');
	showtablerow('id="showspecial" style="display:'.($_G['gp_specialthread'] ? '' : 'none').'"', 'class="sub" colspan="2"', mcheckbox('special', array(
		1 => cplang('thread_poll'),
		2 => cplang('thread_trade'),
		3 => cplang('thread_reward'),
		4 => cplang('thread_activity'),
		5 => cplang('thread_debate')
	), $_G['gp_special'] ? $_G['gp_special'] : array(0)));
	showsetting('threads_search_sticky', array('sticky', array(
		array(0, cplang('unlimited')),
		array(1, cplang('threads_search_include_yes')),
		array(2, cplang('threads_search_include_no')),
	), TRUE), $_G['gp_sticky'], 'mradio');
	showsetting('threads_search_digest', array('digest', array(
		array(0, cplang('unlimited')),
		array(1, cplang('threads_search_include_yes')),
		array(2, cplang('threads_search_include_no')),
	), TRUE), $_G['gp_digest'], 'mradio');
	showsetting('threads_search_attach', array('attach', array(
		array(0, cplang('unlimited')),
		array(1, cplang('threads_search_include_yes')),
		array(2, cplang('threads_search_include_no')),
	), TRUE), $_G['gp_attach'], 'mradio');
	showsetting('threads_rate', array('rate', array(
		array(0, cplang('unlimited')),
		array(1, cplang('threads_search_include_yes')),
		array(2, cplang('threads_search_include_no')),
	), TRUE), $_G['gp_rate'], 'mradio');
	showsetting('threads_highlight', array('highlight', array(
		array(0, cplang('unlimited')),
		array(1, cplang('threads_search_include_yes')),
		array(2, cplang('threads_search_include_no')),
	), TRUE), $_G['gp_highlight'], 'mradio');
	showsetting('threads_save', 'savethread', $_G['gp_savethread'], 'radio');
	showtagfooter('tbody');

	showsubmit('searchsubmit', 'submit', '', 'more_options');
	showtablefooter();
	showformfooter();
	showtagfooter('div');
	if(submitcheck('searchsubmit', 1) || $newlist) {
		$operation == 'group' && $_G['gp_inforum'] = 'isgroup';
		$sql = '';
		$sql .= $_G['gp_inforum'] != '' && $_G['gp_inforum'] != 'all' && $_G['gp_inforum'] != 'isgroup' ? " AND fid='{$_G['gp_inforum']}'" : '';
		$sql .= $_G['gp_inforum'] != '' && $_G['gp_inforum'] == 'isgroup' ? " AND isgroup='1'" : ' AND isgroup=\'0\'';
		$sql .= $_G['gp_intype'] !== '' ? " AND typeid='{$_G['gp_intype']}'" : '';
		$sql .= $_G['gp_insort'] != '' && $_G['gp_insort'] != 'all' ? " AND sortid='{$_G['gp_insort']}'" : '';
		$sql .= $_G['gp_viewsless'] != '' ? " AND views<'{$_G['gp_viewsless']}'" : '';
		$sql .= $_G['gp_viewsmore'] != '' ? " AND views>'{$_G['gp_viewsmore']}'" : '';
		$sql .= $_G['gp_repliesless'] != '' ? " AND replies<'{$_G['gp_repliesless']}'" : '';
		$sql .= $_G['gp_repliesmore'] != '' ? " AND replies>'{$_G['gp_repliesmore']}'" : '';
		$sql .= $_G['gp_readpermmore'] != '' ? " AND readperm>'{$_G['gp_readpermmore']}'" : '';
		$sql .= $_G['gp_pricemore'] != '' ? " AND price>'{$_G['gp_pricemore']}'" : '';
		$sql .= $_G['gp_beforedays'] != '' ? " AND dateline<'$_G[timestamp]'-'{$_G['gp_beforedays']}'*86400" : '';
		$sql .= $_G['gp_noreplydays'] != '' ? " AND lastpost<'$_G[timestamp]'-'{$_G['gp_noreplydays']}'*86400" : '';
		$sql .= $_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime'])."'" : '';
		$sql .= $_G['gp_endtime'] != '' ? " AND dateline<='".strtotime($_G['gp_endtime'])."'" : '';
		$sql .= !empty($_G['gp_savethread']) ? " AND displayorder='-4'" : '';

		if(trim($_G['gp_keywords'])) {
			$sqlkeywords = '';
			$or = '';
			$keywords = explode(',', str_replace(' ', '', $_G['gp_keywords']));
			for($i = 0; $i < count($keywords); $i++) {
				$sqlkeywords .= " $or subject LIKE '%".$keywords[$i]."%'";
				$or = 'OR';
			}
			$sql .= " AND ($sqlkeywords)";
		}

		$sql .= trim($_G['gp_users']) ? " AND author IN ('".str_replace(',', '\',\'', str_replace(' ', '', trim($_G['gp_users'])))."')" : '';

		if($_G['gp_sticky'] == 1) {
			$sql .= " AND displayorder>'0'";
		} elseif($_G['gp_sticky'] == 2) {
			$sql .= " AND displayorder='0'";
		}
		if($_G['gp_digest'] == 1) {
			$sql .= " AND digest>'0'";
		} elseif($_G['gp_digest'] == 2) {
			$sql .= " AND digest='0'";
		}
		if($_G['gp_attach'] == 1) {
			$sql .= " AND attachment>'0'";
		} elseif($_G['gp_attach'] == 2) {
			$sql .= " AND attachment='0'";
		}
		if($_G['gp_rate'] == 1) {
			$sql .= " AND rate>'0'";
		} elseif($_G['gp_rate'] == 2) {
			$sql .= " AND rate='0'";
		}
		if($_G['gp_highlight'] == 1) {
			$sql .= " AND highlight>'0'";
		} elseif($_G['gp_highlight'] == 2) {
			$sql .= " AND highlight='0'";
		}
		if(!empty($_G['gp_special'])) {
			$specials = $comma = '';
			foreach($_G['gp_special'] as $val) {
				$specials .= $comma.'\''.$val.'\'';
				$comma = ',';
			}
			if($_G['gp_specialthread'] == 1) {
				$sql .=  " AND special IN ($specials)";
			} elseif($_G['gp_specialthread'] == 2) {
				$sql .=  " AND special NOT IN ($specials)";
			}
		}

		$fids = array();
		$tids = $threadcount = '0';
		if($sql) {
			$sql = (empty($_G['gp_savethread']) ? "displayorder>='0'" : '1').' '.$sql;
			if($_G['gp_detail']) {
				$_G['gp_perpage'] = intval($_G['gp_perpage']) < 1 ? 20 : intval($_G['gp_perpage']);
				$perpage = $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage'];
				$query = DB::query("SELECT fid, tid, readperm, price, subject, authorid, author, views, replies, lastpost, isgroup, displayorder FROM ".DB::table('forum_thread')." FORCE INDEX(PRIMARY) WHERE $sql ORDER BY tid DESC LIMIT ".(($page - 1) * $perpage).",{$perpage}");
				$threads = '';
				$groupsname = $groupsfid = $threadlist = array();
				while($thread = DB::fetch($query)) {
					$fids[] = $thread['fid'];
					if($thread['isgroup']) {
						$groupsfid[$thread[fid]] = $thread['fid'];
					}
					$thread['lastpost'] = dgmdate($thread['lastpost']);
					$threadlist[] = $thread;
				}
				if($groupsfid) {
					$query = DB::query("SELECT fid, name FROM ".DB::table('forum_forum')." WHERE fid IN(".dimplode($groupsfid).")");
					while($row = DB::fetch($query)) {
						$groupsname[$row[fid]] = $row['name'];
					}
				}
				if($threadlist) {
					foreach($threadlist as $thread) {
						$threads .= showtablerow('', array('class="td25"', '', '', '', 'class="td25"', 'class="td25"'), array(
							"<input class=\"checkbox\" type=\"checkbox\" name=\"tidarray[]\" value=\"$thread[tid]\" />",
							"<a href=\"forum.php?mod=viewthread&tid=$thread[tid]".($thread['displayorder'] != -4 ? '' : '&modthreadkey='.modauthkey($thread['tid']))."\" target=\"_blank\">$thread[subject]</a>".($thread['readperm'] ? " - [$lang[threads_readperm] $thread[readperm]]" : '').($thread['price'] ? " - [$lang[threads_price] $thread[price]]" : ''),
						"<a href=\"forum.php?mod=forumdisplay&fid=$thread[fid]\" target=\"_blank\">".(empty($thread['isgroup']) ? $_G['cache']['forums'][$thread[fid]]['name'] : $groupsname[$thread[fid]])."</a>",
							"<a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>",
							$thread['replies'],
							$thread['views'],
							$thread['lastpost']
						), TRUE);
					}
				}

				$threadcount = DB::result_first("SELECT count(*) FROM ".DB::table('forum_thread')." WHERE $sql");
				$multi = multi($threadcount, $perpage, $page, ADMINSCRIPT."?action=threads");
				$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=threads&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
				$multi = str_replace("window.location='".ADMINSCRIPT."?action=threads&amp;page='+this.value", "page(this.value)", $multi);
			} else {
				$query = DB::query("SELECT fid, tid FROM ".DB::table('forum_thread')." WHERE $sql");
				while($thread = DB::fetch($query)) {
					$fids[] = $thread['fid'];
					$tids .= ','.$thread['tid'];
				}
				$threadcount = DB::result_first("SELECT count(*) FROM ".DB::table('forum_thread')." WHERE $sql");
				$multi = '';
			}
		}
		$fids = implode(',', array_unique($fids));

		showtagheader('div', 'threadlist', TRUE);
		showformheader('threads&frame=no'.($operation ? '&operation='.$operation : ''), 'target="threadframe"');
		showhiddenfields($_G['gp_detail'] ? array('fids' => $fids) : array('fids' => $fids, 'tids' => $tids));
		if(!$search_tips) {
			showtableheader(cplang('threads_new_result').' '.$threadcount, 'nobottom');
		} else {
			showtableheader(cplang('threads_result').' '.$threadcount.' <a href="###" onclick="$(\'threadlist\').style.display=\'none\';$(\'threadsearch\').style.display=\'\';$(\'threadforum\').pp.value=\'\';$(\'threadforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>', 'nobottom');
		}
		if(!$threadcount) {

			showtablerow('', 'colspan="3"', cplang('threads_thread_nonexistence'));

		} else {

			if($_G['gp_detail']) {
				showsubtitle(array('', 'subject', 'forum', 'author', 'threads_replies', 'threads_views', 'threads_lastpost'));
				echo $threads;
				showtablerow('', array('class="td25" colspan="7"'), array('<input name="chkall" id="chkall" type="checkbox" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'tidarray\', \'chkall\')" /><label for="chkall">'.cplang('select_all').'</label>'));
				showtablefooter();
				showtableheader('operation', 'notop');

			}
			showsubtitle(array('', 'operation', 'option'));
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
				'<input class="radio" type="radio" id="optype_moveforum" name="optype" value="moveforum" onclick="this.form.modsubmit.disabled=false;">',
				$lang['threads_move_forum'],
				'<select name="toforum" onchange="$(\'optype_moveforum\').checked=\'checked\';ajaxget(\'forum.php?mod=ajax&action=getthreadtypes&fid=\' + this.value, \'threadtypes\')">'.forumselect(FALSE, 0, 0, TRUE).'</select>'.
				$lang['threads_move_type'].' <span id="threadtypes"><select name="threadtypeid" onchange="$(\'optype_moveforum\').checked=\'checked\'"><option value="0"></option></select></span>'
			));
			if($operation != 'group') {
				showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
					'<input class="radio" type="radio" id="optype_movesort" name="optype" value="movesort" onclick="this.form.modsubmit.disabled=false;">',
					$lang['threads_move_sort'],
					'<select name="tosort" onchange="$(\'optype_movesort\').checked=\'checked\';"><option value="0">&nbsp;&nbsp;> '.$lang['threads_search_type_none'].'</option>'.$sortselect.'</select>'
				));
				showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
					'<input class="radio" type="radio" id="optype_stick" name="optype" value="stick" onclick="this.form.modsubmit.disabled=false;">',
					$lang['threads_stick'],
					'<input class="radio" type="radio" name="stick_level" value="0" onclick="$(\'optype_stick\').checked=\'checked\'"> '.$lang['threads_remove'].' &nbsp; &nbsp;<input class="radio" type="radio" name="stick_level" value="1" onclick="$(\'optype_stick\').checked=\'checked\'"> '.$lang['threads_stick_one'].' &nbsp; &nbsp;<input class="radio" type="radio" name="stick_level" value="2" onclick="$(\'optype_stick\').checked=\'checked\'"> '.$lang['threads_stick_two'].' &nbsp; &nbsp;<input class="radio" type="radio" name="stick_level" value="3" onclick="$(\'optype_stick\').checked=\'checked\'"> '.$lang['threads_stick_three']
				));
				showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
					'<input class="radio" type="radio" id="optype_addstatus" name="optype" value="addstatus" onclick="this.form.modsubmit.disabled=false;">',
					$lang['threads_open_close'],
					'<input class="radio" type="radio" name="status" value="0" onclick="$(\'optype_addstatus\').checked=\'checked\'"> '.$lang['open'].' &nbsp; &nbsp;<input class="radio" type="radio" name="status" value="1"  onclick="$(\'optype_addstatus\').checked=\'checked\'"> '.$lang['closed']
				));
			}
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
				'<input class="radio" type="radio" id="optype_delete" name="optype" value="delete" onclick="this.form.modsubmit.disabled=false;">',
				$lang['threads_delete'],
				'<input class="checkbox" type="checkbox" name="donotupdatemember" id="donotupdatemember" value="1" /><label for="donotupdatemember"> '.$lang['threads_delete_no_update_member'].'</label>'
			));
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
				'<input class="radio" type="radio" name="optype" id="optype_adddigest" value="adddigest" onclick="this.form.modsubmit.disabled=false;">',
				$lang['threads_add_digest'],
				'<input class="radio" type="radio" name="digest_level" value="0" onclick="$(\'optype_adddigest\').checked=\'checked\'"> '.$lang['threads_remove'].' &nbsp; &nbsp;<input class="radio" type="radio" name="digest_level" value="1" onclick="$(\'optype_adddigest\').checked=\'checked\'"> '.$lang['threads_digest_one'].' &nbsp; &nbsp;<input class="radio" type="radio" name="digest_level" value="2" onclick="$(\'optype_adddigest\').checked=\'checked\'"> '.$lang['threads_digest_two'].' &nbsp; &nbsp;<input class="radio" type="radio" name="digest_level" value="3" onclick="$(\'optype_adddigest\').checked=\'checked\'"> '.$lang['threads_digest_three']
			));
			showtablerow('', array('class="td25"', 'class="td24"', 'class="rowform" style="width:auto;"'), array(
				'<input class="radio" type="radio" name="optype" value="deleteattach" onclick="this.form.modsubmit.disabled=false;">',
				$lang['threads_delete_attach'],
				''
			));

		}

		showsubmit('modsubmit', 'submit', '', '', $multi);
		showtablefooter();
		showformfooter();
		echo '<iframe name="threadframe" style="display:none"></iframe>';
		showtagfooter('div');

	}

} else {

	$tidsarray = isset($_G['gp_tids']) ? explode(',', $_G['gp_tids']) : $_G['gp_tidarray'];
	$tidsadd = 'tid IN ('.dimplode($tidsarray).')';
	if($optype == 'moveforum') {

		if(!DB::result_first("SELECT fid FROM ".DB::table('forum_forum')." WHERE fid='{$_G['gp_toforum']}' AND type<>'group'")) {
			cpmsg('threads_move_invalid', '', 'error');
		}
		DB::query("UPDATE ".DB::table('forum_thread')." SET fid='{$_G['gp_toforum']}', typeid='{$_G['gp_threadtypeid']}', isgroup='0' WHERE $tidsadd");
		updatepost(array('fid' => $_G['gp_toforum']), $tidsadd);

		foreach(explode(',', $_G['gp_fids'].','.$_G['gp_toforum']) as $fid) {
			updateforumcount(intval($fid));
		}

		foreach($_G['gp_tidarray'] as $tid) {
			my_thread_log('move', array('tid' => $tid, 'otherid' => $_G['gp_toforum']));
		}

		$cpmsg = cplang('threads_succeed');

	} elseif($optype == 'movesort') {

		if($_G['gp_tosort'] != 0) {
			if(!DB::result_first("SELECT typeid FROM ".DB::table('forum_threadtype')." WHERE typeid='{$_G['gp_tosort']}'")) {
				cpmsg('threads_move_invalid', '', 'error');
			}
		}

		DB::query("UPDATE ".DB::table('forum_thread')." SET sortid='{$_G['gp_tosort']}' WHERE $tidsadd");

		$cpmsg = cplang('threads_succeed');

	} elseif($optype == 'delete') {

		require_once libfile('function/delete');
		deletethread($tidsarray, !$_G['gp_donotupdatemember'], !$_G['gp_donotupdatemember']);

		if($_G['setting']['globalstick']) {
			updatecache('globalstick');
		}

		foreach(explode(',', $_G['gp_fids']) as $fid) {
			updateforumcount(intval($fid));
		}

		foreach($_G['gp_tidarray'] as $tid) {
			my_thread_log('delete', array('tid' => $tid));
		}
		$cpmsg = cplang('threads_succeed');

	} elseif($optype == 'deleteattach') {

		require_once libfile('function/delete');
		deleteattach($tidsarray, 'tid');
		DB::query("UPDATE ".DB::table('forum_thread')." SET attachment='0' WHERE $tidsadd");
		updatepost(array('attachment' => '0'), $tidsadd);

		$cpmsg = cplang('threads_succeed');

	} elseif($optype == 'stick') {

		DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='{$_G['gp_stick_level']}' WHERE $tidsadd");
		$my_act = $_G['gp_stick_level'] ? 'sticky' : 'update';
		foreach($_G['gp_tidarray'] as $tid) {
			my_thread_log($my_act, array('tid' => $tid));
		}

		if($_G['setting']['globalstick']) {
			updatecache('globalstick');
		}

		$cpmsg = cplang('threads_succeed');

	} elseif($optype == 'adddigest') {

		$query = DB::query("SELECT tid, fid, authorid, digest FROM ".DB::table('forum_thread')." WHERE $tidsadd");
		while($thread = DB::fetch($query)) {
			if($_G['gp_digest_level'] == $thread['digest']) continue;
			$extsql = array();
			if($_G['gp_digest_level'] > 0 && $thread['digest'] == 0) {
				$extsql = array('digestposts' => 1);
			}
			if($_G['gp_digest_level'] == 0 && $thread['digest'] > 0) {
				$extsql = array('digestposts' => -1);
			}
			updatecreditbyaction('digest', $thread['authorid'], $extsql, '', $_G['gp_digest_level'] - $thread['digest'], 1, $thread['fid']);
		}
		DB::query("UPDATE ".DB::table('forum_thread')." SET digest='{$_G['gp_digest_level']}' WHERE $tidsadd");
		$my_act = $_G['gp_digest_level'] ? 'digest' : 'update';
		foreach($_G['gp_tidarray'] as $tid) {
			my_thread_log($my_act, array('tid' => $tid));
		}
		$cpmsg = cplang('threads_succeed');

	} elseif($optype == 'addstatus') {

		DB::query("UPDATE ".DB::table('forum_thread')." SET closed='{$_G['gp_status']}' WHERE $tidsadd");
		$my_opt = $_G['gp_status'] ? 'close' : 'open';
		foreach($_G['gp_tidarray'] as $tid) {
			my_thread_log($my_opt, array('tid' => $tid));
		}

		$cpmsg = cplang('threads_succeed');

	} elseif($operation == 'forumstick') {
		shownav('topic', 'threads_forumstick');
		loadcache(array('forums', 'grouptype'));
		$forumstickthreads = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='forumstickthreads'");
		$forumstickthreads = isset($forumstickthreads) ? unserialize($forumstickthreads) : array();
		if(!submitcheck('forumsticksubmit')) {
			showsubmenu('threads_forumstick', array(
				array('admin', 'threads&operation=forumstick', !$do),
				array('add', 'threads&operation=forumstick&do=add', $do == 'add'),
			));
			showtips('threads_forumstick_tips');
			if(!$do) {
				showformheader('threads&operation=forumstick');
				showtableheader('admin', 'fixpadding');
				showsubtitle(array('', 'subject', 'threads_forumstick_forum', 'threads_forumstick_group', 'edit'));
				if(is_array($forumstickthreads)) {
					foreach($forumstickthreads as $k => $v) {
						$forumnames = array();
						foreach($v['forums'] as $forum_id){
							if($_G['cache']['forums'][$forum_id]['name']) {
								$forumnames[] = $name = $_G['cache']['forums'][$forum_id]['name'];
							} elseif($_G['cache']['grouptype']['first'][$forum_id]['name']) {
								$grouptypes[] = $name = $_G['cache']['grouptype']['first'][$forum_id]['name'];
							} elseif($_G['cache']['grouptype']['second'][$forum_id]['name']) {
								$grouptypes[] = $name = $_G['cache']['grouptype']['second'][$forum_id]['name'];
							}
						}
						showtablerow('', array('class="td25"'), array(
							"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$k\">",
							"<a href=\"forum.php?mod=viewthread&tid=$v[tid]\" target=\"_blank\">$v[subject]</a>",
							implode(', ', $forumnames),
							implode(', ', $grouptypes),
							"<a href=\"".ADMINSCRIPT."?action=threads&operation=forumstick&do=edit&id=$k\">$lang[threads_forumstick_targets_change]</a>",
						));
					}
				}
				showsubmit('forumsticksubmit', 'submit', 'del');
				showtablefooter();
				showformfooter();
			} elseif($do == 'add') {
				require_once libfile('function/forumlist');
				showformheader('threads&operation=forumstick&do=add');
				showtableheader('add', 'fixpadding');
				showsetting('threads_forumstick_threadurl', 'forumstick_url', '', 'text');
				$targetsselect = '<select name="forumsticktargets[]" size="10" multiple="multiple">'.forumselect(FALSE, 0, 0, TRUE).'</select>';
				require_once libfile('function/group');
				$groupselect = '<select name="forumsticktargets[]" size="10" multiple="multiple">'.get_groupselect(0, 0, 0).'</select>';
				showsetting('threads_forumstick_targets', '', '', $targetsselect);
				showsetting('threads_forumstick_targetgroups', '', '', $groupselect);
				echo '<input type="hidden" value="add" name="do" />';
				showsubmit('forumsticksubmit', 'submit');
				showtablefooter();
				showformfooter();
			} elseif($do == 'edit') {
				require_once libfile('function/forumlist');
				showformheader("threads&operation=forumstick&do=edit&id={$_G['gp_id']}");
				showtableheader('edit', 'fixpadding');
				$targetsselect = '<select name="forumsticktargets[]" size="10" multiple="multiple">'.forumselect(FALSE, 0, 0, TRUE).'</select>';
				require_once libfile('function/group');
				$groupselect = '<select name="forumsticktargets[]" size="10" multiple="multiple">'.get_groupselect(0, 0, 0).'</select>';
				foreach($forumstickthreads[$_G['gp_id']]['forums'] as $target) {
					$targetsselect = preg_replace("/(\<option value=\"$target\")([^\>]*)(\>)/", "\\1 \\2 selected=\"selected\" \\3", $targetsselect);
					$groupselect = preg_replace("/(\<option value=\"$target\")([^\>]*)(\>)/", "\\1 \\2 selected=\"selected\" \\3", $groupselect);
				}
				showsetting('threads_forumstick_targets', '', '', $targetsselect);
				showsetting('threads_forumstick_targetgroups', '', '', $groupselect);
				echo '<input type="hidden" value="edit" name="do" />';
				echo "<input type=\"hidden\" value=\"{$_G['gp_id']}\" name=\"id\" />";
				showsubmit('forumsticksubmit', 'submit');
				showtablefooter();
				showformfooter();
			}
		} else {
			if(!$do) {
				$do = 'del';
			}
			if($do == 'del') {
				if(!empty($_G['gp_delete']) && is_array($_G['gp_delete'])) {
					$del_tids = array();
					foreach($_G['gp_delete'] as $del_tid){
						unset($forumstickthreads[$del_tid]);
						$del_tids[] = $del_tid;
					}
					if($del_tids) {
						DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='0' WHERE tid IN (".dimplode($del_tids).")");
					}
				} else {
					cpmsg('threads_forumstick_del_nochoice', '', 'error');
				}
			} elseif($do == 'add') {
				$_G['gp_forumstick_url'] = rawurldecode($_G['gp_forumstick_url']);
				if(preg_match('/tid=(\d+)/i', $_G['gp_forumstick_url'], $matches)) {
					$forumstick_tid = $matches[1];
				} elseif(in_array('forum_viewthread', $_G['setting']['rewritestatus']) && $_G['setting']['rewriterule']['forum_viewthread']) {
					preg_match_all('/(\{tid\})|(\{page\})|(\{prevpage\})/', $_G['setting']['rewriterule']['forum_viewthread'], $matches);
					$matches = $matches[0];

					$tidpos = array_search('{tid}', $matches);
					if($tidpos === false) {
						cpmsg('threads_forumstick_url_invalid', "action=threads&operation=forumstick&do=add", 'error');
					}
					$tidpos = $tidpos + 1;
					$rewriterule = str_replace(
						array('\\', '(', ')', '[', ']', '.', '*', '?', '+'),
						array('\\\\', '\(', '\)', '\[', '\]', '\.', '\*', '\?', '\+'),
						$_G['setting']['rewriterule']['forum_viewthread']
					);

					$rewriterule = str_replace(array('{tid}', '{page}', '{prevpage}'), '(\d+?)', $rewriterule);
					$rewriterule = str_replace(array('{', '}'), array('\{', '\}'), $rewriterule);
					preg_match("/$rewriterule/i", $_G['gp_forumstick_url'], $match_result);
					$forumstick_tid = $match_result[$tidpos];
				} elseif(in_array('all_script', $_G['setting']['rewritestatus']) && $_G['setting']['rewriterule']['all_script']) {
					preg_match_all('/(\{script\})|(\{param\})/', $_G['setting']['rewriterule']['all_script'], $matches);
					$matches = $matches[0];
					$parampos = array_search('{param}', $matches);
					if($parampos === false) {
						cpmsg('threads_forumstick_url_invalid', "action=threads&operation=forumstick&do=add", 'error');
					}
					$parampos = $parampos + 1;
					$rewriterule = str_replace(
						array('\\', '(', ')', '[', ']', '.', '*', '?', '+'),
						array('\\\\', '\(', '\)', '\[', '\]', '\.', '\*', '\?', '\+'),
						$_G['setting']['rewriterule']['all_script']
					);
					$rewriterule = str_replace(array('{script}', '{param}'), '([\w\d\-=]+?)', $rewriterule);
					$rewriterule = str_replace(array('{', '}'), array('\{', '\}'), $rewriterule);
					$rewriterule = "/\\/$rewriterule/i";
					preg_match($rewriterule, $_G['gp_forumstick_url'], $match_result);
					$param = $match_result[$parampos];

					if(preg_match('/viewthread-tid-(\d+)/i', $param, $tidmatch)) {
						$forumstick_tid = $tidmatch[1];
					} else {
						cpmsg('threads_forumstick_url_invalid', "action=threads&operation=forumstick&do=add", 'error');
					}
				} else {
					cpmsg('threads_forumstick_url_invalid', "action=threads&operation=forumstick&do=add", 'error');
				}
				if(empty($_G['gp_forumsticktargets'])) {
					cpmsg('threads_forumstick_targets_empty', "action=threads&operation=forumstick&do=add", 'error');
				}
				$stickthread_tmp = array(
					'subject' => DB::result_first("SELECT subject FROM ".DB::table('forum_thread')." WHERE tid='$forumstick_tid'"),
					'tid' => $forumstick_tid,
					'forums' => $_G['gp_forumsticktargets'],
				);
				$forumstickthreads[$forumstick_tid] = $stickthread_tmp;
				DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='4' WHERE tid='$forumstick_tid'");
			} elseif($do == 'edit') {
				if(empty($_G['gp_forumsticktargets'])) {
					cpmsg('threads_forumstick_targets_empty', "action=threads&operation=forumstick&do=edit&id={$_G['gp_id']}", 'error');
				}
				$forumstickthreads[$_G['gp_id']]['forums'] = $_G['gp_forumsticktargets'];
				DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='4' WHERE tid='$forumstick_tid'");
			}

			$forumstickthreads = addslashes(serialize($forumstickthreads));
			DB::query("REPLACE INTO ".DB::table('common_setting')."(skey, svalue) VALUES('forumstickthreads', '$forumstickthreads')");
			updatecache(array('forumstick', 'setting'));
			cpmsg('threads_forumstick_'.$do.'_succeed', "action=threads&operation=forumstick", 'succeed');
		}
	} elseif($operation == 'postposition') {

		shownav('topic', 'threads_postposition');
		if(!$do) {
			if(submitcheck('delpositionsubmit')) {
				delete_position($_G['gp_delete']);
				cpmsg('delete_position_succeed', 'action=threads&operation=postposition', 'succeed');
			} elseif(submitcheck('delandaddsubmit')) {
				delete_position($_G['gp_delete']);
				cpmsg('delete_position_gotu_add', 'action=threads&operation=postposition&do=add&addpositionsubmit=yes&formhash='.FORMHASH.'&tids='.urlencode(implode(',', $_G['gp_delete'])), 'succeed');
			} else {
				showsubmenu('threads_postposition', array(
					array('admin', 'threads&operation=postposition', !$do),
					array('add', 'threads&operation=postposition&do=add', $do == 'add'),
				));
				showtips('threads_postposition_tips');
				loadcache('forums');
				showformheader('threads&operation=postposition');
				showtableheader('admin', 'fixpadding');
				showsubtitle(array('', 'ID', 'subject', 'forum', 'replies', 'dateline'));
				$limit_start = 20 * ($page - 1);
				if($count = DB::result_first("SELECT COUNT(DISTINCT(tid)) FROM ".DB::table('forum_postposition')."")) {
					$multipage = multi($count, 20, $page, ADMINSCRIPT."?action=threads&operation=postposition");
					$query = DB::query("SELECT DISTINCT(tid) FROM ".DB::table('forum_postposition')." LIMIT $limit_start, 20");
					$tids = 0;
					while($row = DB::fetch($query)) {
						$tids .= ", $row[tid]";
					}
					$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE tid IN ($tids)");
					while($v = DB::fetch($query)) {
						showtablerow('', array('class="td25"'), array(
								"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$v[tid]\">",
								$v['tid'],
								"<a href=\"forum.php?mod=viewthread&tid=$v[tid]\" target=\"_blank\">$v[subject]</a>",
								'<a href="forum.php?mod=forumdisplay&fid='.$v['fid'].'" target="_blank">'.$_G['cache']['forums'][$v['fid']]['name'].'</a>',
								$v['replies'],
								dgmdate($v['dateline'], 'u')
							));
					}
				}
				$multipage = isset($multipage) ? $multipage : '';
				showsubmit('delpositionsubmit', 'deleteposition', 'select_all', '<input type="submit" class="btn" name="delandaddsubmit" value="'.cplang('delandadd').'" />', $multipage);
				showtablefooter();
				showformfooter();
			}
		} elseif($do == 'add') {
			if(submitcheck('addpositionsubmit', 1)) {
				$delete = isset($_G['gp_delete']) && is_array($_G['gp_delete']) ? $_G['gp_delete'] : (!empty($_G['gp_tids']) ? explode(',', $_G['gp_tids']) : '');
				if(empty($delete)) {
					cpmsg('select_thread_empty');
				}
				$lastpid = create_position($delete, intval($_G['gp_lastpid']));
				if(empty($delete)) {
					cpmsg('add_postposition_succeed', 'action=threads&operation=postposition', 'succeed');
				}
				cpmsg('addpostposition_continue', 'action=threads&operation=postposition&do=add&addpositionsubmit=yes&formhash='.FORMHASH.'&tids='.urlencode(implode(',', $delete)).'&lastpid='.$lastpid, 'succeed');

			} else {
				showsubmenu('threads_postposition', array(
					array('admin', 'threads&operation=postposition', !$do),
					array('add', 'threads&operation=postposition&do=add', $do == 'add'),
				));
				showtips('threads_postposition_tips');

				showformheader('threads&operation=postposition&do=add');
				showtableheader('srchthread', 'fixpadding');
				echo '<tr><td>'.cplang('srch_replies').'<label><input type="radio" name="replies" value="5000"'.($_G['gp_replies'] == 5000 ? ' checked="checked"' : '').' />5000</label> &nbsp;&nbsp;'.
				'<label><input type="radio" name="replies" value="10000"'.($_G['gp_replies'] == 10000 ? ' checked="checked"' : '').' />10000</label>&nbsp;&nbsp;'.
				'<label><input type="radio" name="replies" value="20000"'.($_G['gp_replies'] == 20000 ? ' checked="checked"' : '').' />20000</label>&nbsp;&nbsp;'.
				'<label><input type="radio" name="replies" value="50000"'.($_G['gp_replies'] == 50000 ? ' checked="checked"' : '').' />50000</label>&nbsp;&nbsp;'.
				'<label><input id="replies_other" type="radio" name="replies" value="0"'.(isset($_G['gp_replies']) && $_G['gp_replies'] == 0 ? ' checked="checked"' : '').' onclick="$(\'above_replies\').focus()" />'.cplang('threads_postposition_replies').'</label>&nbsp;'.
				'<input id="above_replies" onclick="$(\'replies_other\').checked=true" type="text class="txt" name="above_replies" value="'.$_G['gp_above_replies'].'" size="5" />&nbsp;&nbsp;&nbsp;&nbsp;'.
				'&nbsp;&nbsp;&nbsp;&nbsp;<label>'.cplang('srch_tid').'&nbsp;<input type="text class="txt" name="srchtid" size="5" value="'.$_G[gp_srchtid].'" /></label>&nbsp;'.
				'&nbsp;&nbsp;&nbsp;<input type="submit" class="btn" name="srchthreadsubmit" value="'.cplang('submit').'" />';
				showtablefooter();
				showformfooter();


				loadcache('forums');
				showformheader('threads&operation=postposition&do=add');
				showtableheader('addposition', 'fixpadding');
				showsubtitle(array('', 'ID', 'subject', 'forum', 'replies', 'dateline'));
				if(submitcheck('srchthreadsubmit', 1)) {
					if($srchtid = max(0, intval($_G['gp_srchtid']))) {
						if($thread = DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid='$srchtid'")) {
							showtablerow('', array('class="td25"'), array(
								"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$thread[tid]\">",
								$thread['tid'],
								"<a href=\"forum.php?mod=viewthread&tid=$thread[tid]\" target=\"_blank\">$thread[subject]</a>",
								'<a href="forum.php?mod=forumdisplay&fid='.$thread['fid'].'" target="_blank">'.$_G['cache']['forums'][$thread['fid']]['name'].'</a>',
								$thread['replies'],
								dgmdate($thread['dateline'], 'u')
							));
						}
					} else {
						$r_replies = $_G['gp_replies'] ? $_G['gp_replies'] : $_G['gp_above_replies'];
						if($r_replies = max(0, intval($r_replies))) {
							$limit_start = 20 * ($page - 1);
							if($count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE replies>'$r_replies'")) {
								$multipage = multi($count, 20, $page, ADMINSCRIPT."?action=threads&operation=postposition&do=add&srchthreadsubmit=yes&replies=$r_replies");
								$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE replies>'$r_replies' LIMIT $limit_start, 20");
								$have = 0;
								while($thread = DB::fetch($query)) {
									if(getstatus($thread['status'], 1)) continue;
									$have = 1;
									showtablerow('', array('class="td25"'), array(
										"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$thread[tid]\">",
										$thread['tid'],
										"<a href=\"forum.php?mod=viewthread&tid=$thread[tid]\" target=\"_blank\">$thread[subject]</a>",
										'<a href="forum.php?mod=forumdisplay&fid='.$thread['fid'].'" target="_blank">'.$_G['cache']['forums'][$thread['fid']]['name'].'</a>',
										$thread['replies'],
										dgmdate($thread['dateline'], 'u')
									));
								}
								if($have == 0) {
									dheader("Location: ".ADMINSCRIPT."?action=threads&operation=postposition&do=add&srchthreadsubmit=yes&replies=$r_replies&page=".($page+1));
								}
							}

						}
					}
				}
				$multipage = isset($multipage) ? $multipage : '';
				showsubmit('addpositionsubmit', 'addposition', 'select_all', '', $multipage);
				showtablefooter();
				showformfooter();
			}
		}
	}

	$_G['gp_tids'] && deletethreadcaches($_G['gp_tids']);
	$cpmsg = $cpmsg ? "alert('$cpmsg');" : '';
	echo '<script type="text/JavaScript">'.$cpmsg.'if(parent.$(\'threadforum\')) parent.$(\'threadforum\').searchsubmit.click();</script>';
}

function delete_position($select) {
	if(empty($select) || !is_array($select)) {
		cpmsg('select_thread_empty', '', 'error');
	}
	$tids = dimplode($select);
	DB::query("DELETE FROM ".DB::table('forum_postposition')." WHERE tid IN($tids)");
	DB::query("UPDATE ".DB::table('forum_thread')." SET status=status & '1111111111111110' WHERE tid IN ($tids)");
}

function create_position(&$select, $lastpid = 0) {
	if(empty($select) || !is_array($select)) {
		return 0;
	}
	$tid = $select[0];
	if(empty($lastpid)) {
		$check = DB::result_first("SELECT tid FROM ".DB::table('forum_postposition')." WHERE tid='$tid' LIMIT 1");
		if($check) {
			unset($select[0]);
			return 0;
		}
	}
	$round = 500;
	$posttable = getposttablebytid($tid);
	$query = DB::query("SELECT pid FROM ".DB::table($posttable)." WHERE tid='$tid' AND pid>'$lastpid' ORDER BY pid ASC LIMIT 0, $round");
	while($post = DB::fetch($query)) {
		if(empty($post) || empty($post['pid'])) continue;
		savepostposition($tid, $post['pid']);
		$lastid = $post['pid'];
	}
	if(DB::num_rows($query) < $round) {
		DB::query("UPDATE ".DB::table('forum_thread')." SET status=status | '1' WHERE tid='$tid'");
		unset($select[0]);
		return 0;
	} else {
		return $lastid;
	}
}

?>