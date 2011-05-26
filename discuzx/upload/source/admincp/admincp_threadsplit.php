<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_threadsplit.php 21121 2011-03-16 03:26:35Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

@set_time_limit(0);
define('IN_DEBUG', false);

define('MAX_THREADS_MOVE', 100);

cpheader();
$topicperpage = 50;
if(empty($operation)) {
	$operation = 'manage';
}

$threadtableids = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='threadtableids'");
if(!empty($threadtableids)) {
	$threadtableids = unserialize($threadtableids);
} else {
	$threadtableids = array();
}

$threadtable_info = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='threadtable_info'");
if(!empty($threadtable_info)) {
	$threadtable_info = unserialize($threadtable_info);
} else {
	$threadtable_info = array();
}

if($operation == 'manage') {
	shownav('founder', 'nav_threadsplit');
	if(!submitcheck('threadsplit_update_submit')) {
		showsubmenu('nav_threadsplit', array(
			array('nav_threadsplit_manage', 'threadsplit&operation=manage', 1),
			array('nav_threadsplit_move', 'threadsplit&operation=move', 0),
		));
		showtips('threadsplit_manage_tips');
		showformheader('threadsplit&operation=manage');
		showtableheader('threadsplit_manage_table_orig');

		$thread_table_orig =gettablestatus(DB::table('forum_thread'));
		showsubtitle(array('threadsplit_manage_tablename', 'threadsplit_manage_threadcount', 'threadsplit_manage_datalength', 'threadsplit_manage_indexlength', 'threadsplit_manage_table_createtime', 'threadsplit_manage_table_memo', ''));
		showtablerow('', array(), array($thread_table_orig['Name'], $thread_table_orig['Rows'], $thread_table_orig['Data_length'], $thread_table_orig['Index_length'], $thread_table_orig['Create_time'], "<input type=\"text\" class=\"txt\" name=\"memo[0]\" value=\"{$threadtable_info[0]['memo']}\" />", ''));

		showtableheader('threadsplit_manage_table_archive');
		showsubtitle(array('threadsplit_manage_tablename', 'threadsplit_manage_dislayname', 'threadsplit_manage_threadcount', 'threadsplit_manage_datalength', 'threadsplit_manage_indexlength', 'threadsplit_manage_table_createtime', 'threadsplit_manage_table_memo', ''));

		foreach($threadtableids as $tableid) {
			if(!$tableid) {
				continue;
			}
			$tablename = "forum_thread_$tableid";
			$table_info = gettablestatus(DB::table($tablename));
			showtablerow('', array(), array($table_info['Name'], "<input type=\"text\" class=\"txt\" name=\"displayname[$tableid]\" value=\"{$threadtable_info[$tableid]['displayname']}\" />", $table_info['Rows'], $table_info['Data_length'], $table_info['Index_length'], $table_info['Create_time'], "<input type=\"text\" class=\"txt\" name=\"memo[$tableid]\" value=\"{$threadtable_info[$tableid]['memo']}\" />", "<a href=\"?action=threadsplit&operation=droptable&tableid=$tableid\">{$lang['delete']}</a>"));
		}
		showtablefooter();
		showsubmit('threadsplit_update_submit', 'threadsplit_manage_update', '', '<a href="?action=threadsplit&operation=addnewtable" style="border-style: solid; border-width: 1px;" class="btn">'.$lang['threadsplit_manage_table_add'].'</a>&nbsp;<a href="?action=threadsplit&operation=forumarchive" style="border-style: solid; border-width: 1px;" class="btn">'.$lang['threadsplit_manage_forum_update'].'</a>');
		showformfooter();
	} else {
		$threadtable_info = array();
		$_G['gp_memo'] = !empty($_G['gp_memo']) ? $_G['gp_memo'] : array();
		$_G['gp_displayname'] = !empty($_G['gp_displayname']) ? $_G['gp_displayname'] : array();
		foreach(array_keys($_G['gp_memo']) as $tableid) {
			$threadtable_info[$tableid]['memo'] = $_G['gp_memo'][$tableid];
		}
		foreach(array_keys($_G['gp_displayname']) as $tableid) {
			$threadtable_info[$tableid]['displayname'] = $_G['gp_displayname'][$tableid];
		}
		DB::insert('common_setting', array(
			'skey' => 'threadtable_info',
			'svalue' => serialize($threadtable_info),
		), false, true);
		save_syscache('threadtable_info', $threadtable_info);
		update_threadtableids();
		updatecache('setting');
		cpmsg('threadsplit_manage_update_succeed', 'action=threadsplit&operation=manage', 'succeed');
	}
} elseif($operation == 'addnewtable') {
	if(empty($threadtableids)) {
		$maxtableid = 0;
	} else {
		$maxtableid = max($threadtableids);
	}

	DB::query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');
	$db = & DB::object();
	$query = DB::query("SHOW CREATE TABLE ".DB::table('forum_thread'));
	$create = $db->fetch_row($query);
	$createsql = $create[1];
	$tableid = $maxtableid + 1;
	$createsql = str_replace(DB::table('forum_thread'), DB::table('forum_thread').'_'.$tableid, $createsql);
	DB::query($createsql);

	update_threadtableids();
	updatecache('setting');
	cpmsg('threadsplit_table_create_succeed', 'action=threadsplit&operation=manage', 'succeed');
} elseif($operation == 'droptable') {
	$tableid = $_G['gp_tableid'];
	$tablename = "forum_thread_$tableid";
	$table_info = gettablestatus(DB::table($tablename));
	if(!$tableid || !$table_info) {
		cpmsg('threadsplit_table_no_exists', 'action=threadsplit&operation=manage', 'error');
	}
	if($table_info['Rows'] > 0) {
		cpmsg('threadsplit_drop_table_no_empty_error', 'action=threadsplit&operation=manage', 'error');
	}

	DB::query("DROP TABLE ".DB::table($tablename));
	unset($threadtable_info[$tableid]);

	update_threadtableids();

	DB::insert('common_setting', array(
		'skey' => 'threadtable_info',
		'svalue' => serialize($threadtable_info),
	), false, true);
	save_syscache('threadtable_info', $threadtable_info);
	updatecache('setting');
	cpmsg('threadsplit_drop_table_succeed', 'action=threadsplit&operation=manage', 'succeed');
} elseif($operation == 'move') {
	if(!$_G['setting']['bbclosed'] && !IN_DEBUG) {
		cpmsg('threadsplit_forum_must_be_closed', 'action=threadsplit&operation=manage', 'error');
	}

	require_once libfile('function/forumlist');
	$tableselect = '<select name="sourcetableid">';
	foreach($threadtableids as $tableid) {
		$selected = $_G['gp_sourcetableid'] == $tableid ? 'selected="selected"' : '';
		$tableselect .= "<option value=\"$tableid\" $selected>".DB::table("forum_thread".($tableid ? "_$tableid" : ''))."</option>";
	}
	$tableselect .= '</select>';

	$forumselect = '<select name="inforum"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option>'.
		'<option value="">&nbsp;</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';
	if(isset($_G['gp_inforum'])) {
		$forumselect = preg_replace("/(\<option value=\"{$_G['gp_inforum']}\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
	}

	$typeselect = $sortselect = '';
	$query = DB::query("SELECT * FROM ".DB::table('forum_threadtype')." ORDER BY displayorder");
	while($type = DB::fetch($query)) {
		if($type['special']) {
			$sortselect .= '<option value="'.$type['typeid'].'">&nbsp;&nbsp;> '.$type['name'].'</option>';
		} else {
			$typeselect .= '<option value="'.$type['typeid'].'">&nbsp;&nbsp;> '.$type['name'].'</option>';
		}
	}

	if(isset($_G['gp_insort'])) {
		$sortselect = preg_replace("/(\<option value=\"{$_G['gp_insort']}\")(\>)/", "\\1 selected=\"selected\" \\2", $sortselect);
	}

	if(isset($_G['gp_intype'])) {
		$typeselect = preg_replace("/(\<option value=\"{$_G['gp_intype']}\")(\>)/", "\\1 selected=\"selected\" \\2", $typeselect);
	}
echo <<<EOT
<script src="static/js/calendar.js"></script>
<script type="text/JavaScript">
	function page(number) {
		$('threadform').page.value=number;
		$('threadform').threadsplit_move_search.click();
	}
</script>
EOT;
	shownav('founder', 'nav_threadsplit');
	if(!submitcheck('threadsplit_move_submit') && !$_G['gp_moving']) {
		showsubmenu('nav_threadsplit', array(
			array('nav_threadsplit_manage', 'threadsplit&operation=manage', 0),
			array('nav_threadsplit_move', 'threadsplit&operation=move', 1),
		));
		showtips('threadsplit_move_tips');
		showtagheader('div', 'threadsearch', !submitcheck('threadsplit_move_search'));
		showformheader('threadsplit&operation=move', '', 'threadform');
		showhiddenfields(array('page' => $_G['gp_page']));
		showtableheader();
		showsetting('threads_search_detail', 'detail', $_G['gp_detail'], 'radio');
		showsetting('threads_search_sourcetable', '', '', $tableselect);
		showsetting('threads_search_forum', '', '', $forumselect);
		showsetting('threadsplit_move_tidrange', array('tidmin', 'tidmax'), array($_G['gp_tidmin'], $_G['gp_tidmax']), 'range');
		showsetting('threads_search_noreplyday', 'noreplydays', isset($_G['gp_noreplydays']) ? $_G['gp_noreplydays'] : 365, 'text');

		showtagheader('tbody', 'advanceoption');
		showsetting('threads_search_time', array('starttime', 'endtime'), array($_G['gp_starttime'], $_G['gp_endtime']), 'daterange');
		showsetting('threads_search_type', '', '', '<select name="intype"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option><option value="">&nbsp;</option><option value="0">&nbsp;&nbsp;> '.$lang['threads_search_type_none'].'</option>'.$typeselect.'</select>');
		showsetting('threads_search_sort', '', '', '<select name="insort"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option><option value="">&nbsp;</option><option value="0">&nbsp;&nbsp;> '.$lang['threads_search_type_none'].'</option>'.$sortselect.'</select>');
		showsetting('threads_search_viewrange', array('viewsmore', 'viewsless'), array($_G['gp_viewsmore'], $_G['gp_viewsless']), 'range');
		showsetting('threads_search_replyrange', array('repliesmore', 'repliesless'), array($_G['gp_repliesmore'], $_G['gp_repliesless']), 'range');
		showsetting('threads_search_readpermmore', 'readpermmore', $_G['gp_readpermmore'], 'text');
		showsetting('threads_search_pricemore', 'pricemore', $_G['gp_pricemore'], 'text');
		showsetting('threads_search_keyword', 'keywords', $_G['gp_keywords'], 'text');
		showsetting('threads_search_user', 'users', $_G['gp_users'], 'text');

		showsetting('threads_search_type', array('specialthread', array(
			array(0, cplang('unlimited'), array('showspecial' => 'none')),
			array(1, cplang('threads_search_include_yes'), array('showspecial' => '')),
			array(2, cplang('threads_search_include_no'), array('showspecial' => '')),
		), TRUE), isset($_G['gp_specialthread']) ? $_G['gp_specialthread'] : 2, 'mradio');
		showtablerow('id="showspecial" style="display:'.($_G['gp_specialthread'] || !isset($_G['gp_specialthread']) ? '' : 'none').'"', 'class="sub" colspan="2"', mcheckbox('special', array(
			1 => cplang('thread_poll'),
			2 => cplang('thread_trade'),
			3 => cplang('thread_reward'),
			4 => cplang('thread_activity'),
			5 => cplang('thread_debate')
		), $_G['gp_special'] ? $_G['gp_special'] : array(1,2,3,4,5)));
		showsetting('threads_search_sticky', array('sticky', array(
			array(0, cplang('unlimited')),
			array(1, cplang('threads_search_include_yes')),
			array(2, cplang('threads_search_include_no')),
		), TRUE), isset($_G['gp_sticky']) ? $_G['gp_sticky'] : 2, 'mradio');
		showsetting('threads_search_digest', array('digest', array(
			array(0, cplang('unlimited')),
			array(1, cplang('threads_search_include_yes')),
			array(2, cplang('threads_search_include_no')),
		), TRUE), isset($_G['gp_digest']) ? $_G['gp_digest'] : 2, 'mradio');
		showsetting('threads_search_attach', array('attach', array(
			array(0, cplang('unlimited')),
			array(1, cplang('threads_search_include_yes')),
			array(2, cplang('threads_search_include_no')),
		), TRUE), isset($_G['gp_attach']) ? $_G['gp_attach'] : 0, 'mradio');
		showsetting('threads_rate', array('rate', array(
			array(0, cplang('unlimited')),
			array(1, cplang('threads_search_include_yes')),
			array(2, cplang('threads_search_include_no')),
		), TRUE), isset($_G['gp_rate']) ? $_G['gp_rate'] : 2, 'mradio');
		showsetting('threads_highlight', array('highlight', array(
			array(0, cplang('unlimited')),
			array(1, cplang('threads_search_include_yes')),
			array(2, cplang('threads_search_include_no')),
		), TRUE), isset($_G['gp_highlight']) ? $_G['gp_highlight'] : 2, 'mradio');
		showtagfooter('tbody');

		showsubmit('threadsplit_move_search', 'submit', '', 'more_options');
		showtablefooter();
		showformfooter();
		showtagfooter('div');
		if(submitcheck('threadsplit_move_search')) {
			$searchurladd = array();
			$conditions = array(
				'sourcetableid' => $_G['gp_sourcetableid'],
				'inforum' => $_G['gp_inforum'],
				'tidmin' => $_G['gp_tidmin'],
				'tidmax' => $_G['gp_tidmax'],
				'starttime' => $_G['gp_starttime'],
				'endtime' => $_G['gp_endtime'],
				'keywords' => $_G['gp_keywords'],
				'users' => $_G['gp_users'],
				'intype' => $_G['gp_intype'],
				'insort' => $_G['gp_insort'],
				'viewsmore' => $_G['gp_viewsmore'],
				'viewsless' => $_G['gp_viewsless'],
				'repliesmore' => $_G['gp_repliesmore'],
				'repliesless' => $_G['gp_repliesless'],
				'readpermmore' => $_G['gp_readpermmore'],
				'pricemore' => $_G['gp_pricemore'],
				'noreplydays' => $_G['gp_noreplydays'],
				'specialthread' => $_G['gp_specialthread'],
				'special' => $_G['gp_special'],
				'sticky' => $_G['gp_sticky'],
				'digest' => $_G['gp_digest'],
				'attach' => $_G['gp_attach'],
				'rate' => $_G['gp_rate'],
				'highlight' => $_G['gp_highlight'],
			);
			if($_G['gp_detail']) {
				$pagetmp = $page;
				$threadlist = threadsplit_search_threads($conditions, ($pagetmp - 1) * $topicperpage, $topicperpage);
			} else {
				$threadtomove = threadsplit_search_threads($conditions, null, null, TRUE);
			}

			$fids = array();
			$tids = '0';
			if($_G['gp_detail']) {
				$threads = '';
				foreach($threadlist as $thread) {
					$fids[] = $thread['fid'];
					$thread['lastpost'] = dgmdate($thread['lastpost']);
					$threads .= showtablerow('', array('class="td25"', '', '', '', '', ''), array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"tidarray[]\" value=\"$thread[tid]\" checked=\"checked\" />",
						"<a href=\"forum.php?mod=viewthread&tid=$thread[tid]\" target=\"_blank\">$thread[subject]</a>",
						"<a href=\"forum.php?mod=forumdisplay&fid=$thread[fid]\" target=\"_blank\">{$_G['cache'][forums][$thread[fid]][name]}</a>",
						"<a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>",
						$thread['replies'],
						$thread['views']
					), TRUE);
				}
				$multi = multi($threadcount, $topicperpage, $page, ADMINSCRIPT."?action=threadsplit&amp;operation=move");
				$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=threadsplit&amp;operation=move&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
				$multi = str_replace("window.location='".ADMINSCRIPT."?action=threadsplit&amp;operation=move&amp;page='+this.value", "page(this.value)", $multi);
			} else {
				foreach($threadlist as $thread) {
					$fids[] = $thread['fid'];
					$tids .= ','.$thread['tid'];
				}
				$multi = '';
			}
			$fids = implode(',', array_unique($fids));

			showtagheader('div', 'threadlist', TRUE);
			showformheader("threadsplit&operation=move&sourcetableid={$_G['gp_sourcetableid']}&threadtomove=".$threadtomove);
			showhiddenfields($_G['gp_detail'] ? array('fids' => $fids) : array('conditions' => serialize($conditions)));
			showtableheader(cplang('threads_result').' '.$threadcount.' <a href="###" onclick="$(\'threadlist\').style.display=\'none\';$(\'threadsearch\').style.display=\'\';" class="act lightlink normal">'.cplang('research').'</a>', 'nobottom');
			showsubtitle(array('', 'threadsplit_move_to', 'threadsplit_manage_threadcount', 'threadsplit_manage_datalength', 'threadsplit_manage_indexlength', 'threadsplit_manage_table_createtime', 'threadsplit_manage_table_memo'));

			if(!$threadcount) {

				showtablerow('', 'colspan="3"', cplang('threads_thread_nonexistence'));

			} else {
				$threadtable_orig = gettablestatus(DB::table('forum_thread'));
				$tableid = 0;

				showtablerow('', array('class="td25"'), array("<input class=\"radio\" ".($_G['gp_sourcetableid'] == '0' ? 'disabled="disabled"' : '')." type=\"radio\" name=\"tableid\" value=\"0\" />", $threadtable_orig['Name'], $threadtable_orig['Rows'], $threadtable_orig['Data_length'], $threadtable_orig['Index_length'], $threadtable_orig['Create_time'], $threadtable_info[0]['memo']));
				foreach($threadtableids as $tableid) {
					if($tableid) {
						$tablename = "forum_thread_$tableid";
						$tablestatus = gettablestatus(DB::table($tablename));

						showtablerow('', array(), array("<input class=\"radio\" ".($_G['gp_sourcetableid'] == $tableid ? 'disabled="disabled"' : '')." type=\"radio\" name=\"tableid\" value=\"$tableid\" />", $tablestatus['Name'].($threadtable_info[$tableid]['displayname'] ? " (".htmlspecialchars($threadtable_info[$tableid]['displayname']).")" : ''), $tablestatus['Rows'], $tablestatus['Data_length'], $tablestatus['Index_length'], $tablestatus['Create_time'], $threadtable_info[$tableid]['memo']));
					}
				}

				if($_G['gp_detail']) {

					showtablefooter();
					showtableheader('threads_list', 'notop');
					showsubtitle(array('', 'subject', 'forum', 'author', 'threads_replies', 'threads_views'));
					echo $threads;

				}

			}
			showtablefooter();
			if($threadcount) {
				showtableheader('');
				showsetting('threadsplit_move_threads_per_time', 'threads_per_time', 200, 'text');
				showtablefooter();
				showsubmit('threadsplit_move_submit', 'submit', $_G['gp_detail'] ? '<input name="chkall" id="chkall" type="checkbox" class="checkbox" checked="checked" onclick="checkAll(\'prefix\', this.form, \'tidarray\', \'chkall\')" /><label for="chkall">'.cplang('select_all').'</label>' : '', '', $multi);

			}
			showformfooter();
			showtagfooter('div');

		}
	} else {
		if(!isset($_G['gp_tableid'])) {
			cpmsg('threadsplit_no_target_table', '', 'error');
		}
		$continue = false;

		$tidsarray = !empty($_G['gp_tidarray']) ? $_G['gp_tidarray'] : array();
		if(empty($tidsarray) && !empty($_G['gp_conditions'])) {
			$conditions = unserialize(stripslashes($_G['gp_conditions']));
			$max_threads_move = intval($_G['gp_threads_per_time']) ? intval($_G['gp_threads_per_time']) : MAX_THREADS_MOVE;
			$threadlist = threadsplit_search_threads($conditions, 0, $max_threads_move);
			foreach($threadlist as $thread) {
				$tidsarray[] = $thread['tid'];
				$continue = TRUE;
			}
		}
		if(empty($tidsarray[0])) {
			array_shift($tidsarray);
		}

		if(!empty($tidsarray)) {
			$continue = true;
		}
		$threadtable_target = $_G['gp_tableid'] ? "forum_thread_{$_G['gp_tableid']}" : 'forum_thread';
		$threadtable_source = $_G['gp_sourcetableid'] ? "forum_thread_{$_G['gp_sourcetableid']}" : 'forum_thread';
		if($_G['gp_tableid'] == $_G['gp_sourcetableid']) {
			cpmsg('threadsplit_move_source_target_no_same', 'action=threadsplit&operation=move', 'error');
		}
		if($continue) {
			DB::query("REPLACE INTO ".DB::table($threadtable_target)." SELECT * FROM ".DB::table($threadtable_source)." WHERE tid IN (".dimplode($tidsarray).")");
			DB::delete($threadtable_source, "tid IN (".dimplode($tidsarray).")");

			DB::delete('forum_forumrecommend', "tid IN (".dimplode($tidsarray).")");

			$completed = intval($_G['gp_completed']) + count($tidsarray);

			$nextstep = $step + 1;
			cpmsg('threadsplit_moving', "action=threadsplit&operation=move&{$_G['gp_urladd']}&tableid={$_G['gp_tableid']}&completed=$completed&sourcetableid={$_G['gp_sourcetableid']}&threadtomove={$_G['gp_threadtomove']}&step=$nextstep&moving=1", 'loadingform', array('count' => $completed, 'total' => intval($_G['gp_threadtomove']), 'threads_per_time' => $_G['gp_threads_per_time'], 'conditions' => stripslashes(htmlspecialchars($_G['gp_conditions']))));
		}

		cpmsg('threadsplit_move_succeed', "action=threadsplit&operation=forumarchive", 'succeed');
	}
} elseif($operation == 'forumarchive') {
	$step = intval($_G['gp_step']);
	$continue = false;
	if(isset($threadtableids[$step])) {
		$continue = true;
	}
	if($continue) {
		$threadtableid = $threadtableids[$step];
		DB::update('forum_forum_threadtable', array('threads' => '0', 'posts' => '0'), "threadtableid='$threadtableid'");
		$threadtable = $threadtableid ? "forum_thread_$threadtableid" : 'forum_thread';
		$query = DB::query("SELECT fid, COUNT(*) AS threads, SUM(replies)+COUNT(*) AS posts FROM ".DB::table($threadtable)." GROUP BY fid");
		while($row = DB::fetch($query)) {
			DB::insert('forum_forum_threadtable', array(
				'fid' => $row['fid'],
				'threadtableid' => $threadtableid,
				'threads' => $row['threads'],
				'posts' => $row['posts'],
			), false, true);
			if($row['threads'] > 0) {
				DB::update('forum_forum', array(
					'archive' => '1',
				), "fid='{$row['fid']}'");
			}
		}
		$nextstep = $step + 1;
		cpmsg('threadsplit_manage_forum_processing', "action=threadsplit&operation=forumarchive&step=$nextstep", 'loading', array('table' => DB::table($threadtable)));
	} else {
		DB::delete('forum_forum_threadtable', "threads='0'");
		$fids = array('0');
		$query = DB::query("SELECT fid FROM ".DB::table('forum_forum_threadtable'));
		while($row = DB::fetch($query)) {
			$fids[] = $row['fid'];
		}
		DB::update('forum_forum', array('archive' => '0'), "fid NOT IN (".dimplode($fids).")");
		cpmsg('threadsplit_manage_forum_complete', 'action=threadsplit&operation=manage', 'succeed');
	}
}

function gettablestatus($tablename) {
	$table_info = DB::fetch_first("SHOW TABLE STATUS LIKE '".str_replace('_', '\_', $tablename)."'");
	$table_info['Data_length'] = $table_info['Data_length'] / 1024 / 1024;
	$nums = intval(log($table_info['Data_length']) / log(10));
	$digits = 0;
	if($nums <= 3) {
		$digits = 3 - $nums;
	}
	$table_info['Data_length'] = number_format($table_info['Data_length'], $digits).' MB';

	$table_info['Index_length'] = $table_info['Index_length'] / 1024 / 1024;
	$nums = intval(log($table_info['Index_length']) / log(10));
	$digits = 0;
	if($nums <= 3) {
		$digits = 3 - $nums;
	}
	$table_info['Index_length'] = number_format($table_info['Index_length'], $digits).' MB';
	return $table_info;
}

function threadsplit_search_threads($conditions, $offset = null, $length = null, $onlycount = FALSE) {
	global $_G, $searchurladd, $page, $threadcount;
	$sql = '';
	if($conditions['sourcetableid'] != '') {
		$searchurladd[] = "sourcetableid={$conditions['sourcetableid']}";
	}
	if($conditions['inforum'] != '' && $conditions['inforum'] != 'all') {
		$sql .= " AND t.fid='{$conditions['inforum']}'";
		$searchurladd[] = "inforum={$conditions['inforum']}";
	}

	if($conditions['tidmin'] != '') {
		$sql .= " AND t.tid>='{$conditions['tidmin']}'";
		$searchurladd[] = "tidmin={$conditions['tidmin']}";
	}

	if($conditions['tidmax'] != '') {
		$sql .= " AND t.tid<='{$conditions['tidmax']}'";
		$searchurladd[] = "tidmax={$conditions['tidmax']}";
	}

	if($conditions['sticky'] == 1) {
		$sql .= " AND t.displayorder>'0'";
		$searchurladd[] = "sticky=1";
	} elseif($conditions['sticky'] == 2) {
		$sql .= " AND t.displayorder='0'";
		$searchurladd[] = "sticky=2";
	} else {
		$sql .= " AND t.displayorder>='0'";
		$searchurladd[] = "sticky=0";
	}

	if($conditions['noreplydays'] != '') {
		$conditions['noreplydays'] = intval($conditions['noreplydays']);
		$lastpost = $_G['timestamp'] - $conditions['noreplydays'] * 86400;
		$sql .= " AND t.lastpost<$lastpost";
		$searchurladd[] = "noreplydays={$conditions['noreplydays']}";
	}

	if($conditions['intype'] != '' && $conditions['intype'] != 'all') {
		$sql .= " AND t.typeid='{$conditions['intype']}'";
		$searchurladd[] = "intype={$conditions['intype']}";
	}
	if($conditions['insort'] != '' && $conditions['insort'] != 'all') {
		$sql .= " AND t.sortid='{$conditions['insort']}'";
		$searchurladd[] = "insort={$conditions['insort']}";
	}

	if($conditions['viewsless'] != '') {
		$sql .= " AND t.views<'{$_G['viewsless']}'";
		$searchurladd[] = "viewsless={$conditions['viewsless']}";
	}
	if($conditions['viewsmore'] != '') {
		$sql .= " AND t.views>'{$conditions['viewsmore']}'";
		$searchurladd[] = "viewsmore={$conditions['viewsmore']}";
	}
	if($conditions['repliesless'] != '') {
		$sql .= " AND t.replies<'{$conditions['repliesless']}'";
		$searchurladd[] = "repliesless={$conditions['repliesless']}";
	}
	if($conditions['repliesmore'] != '') {
		$sql .= " AND t.replies>'{$conditions['repliesmore']}'";
		$searchurladd[] = "repliesmore={$conditions['repliesmore']}";
	}
	if($conditions['readpermmore'] != '') {
		$sql .= " AND t.readperm>'{$conditions['readpermmore']}'";
		$searchurladd[] = "readpermmore={$conditions['readpermmore']}";
	}
	if($conditions['pricemore'] != '') {
		$sql .= " AND t.price>'{$conditions['pricemore']}'";
		$searchurladd[] = "pricemore={$conditions['pricemore']}";
	}
	if($conditions['beforedays'] != '') {
		$sql .= " AND t.dateline<'{$_G['timestamp']}'-'{$conditions['beforedays']}'*86400";
		$searchurladd[] = "beforedays={$conditions['beforedays']}";
	}

	if($conditions['starttime'] != '') {
		$sql .= " AND t.dateline>'".strtotime($conditions['starttime'])."'";
		$searchurladd[] = "starttime={$conditions['starttime']}";
	}
	if($conditions['endtime'] != '') {
		$sql .= " AND t.dateline<='".strtotime($conditions['endtime'])."'";
		$searchurladd[] = "endtime={$conditions['endtime']}";
	}

	if($conditions['users'] != '') {
		$sql .= trim($conditions['users']) ? " AND t.author IN ('".str_replace(',', '\',\'', str_replace(' ', '', trim($conditions['users'])))."')" : '';
		$searchurladd[] = "users={$conditions['users']}";
	}

	if($conditions['digest'] == 1) {
		$sql .= " AND t.digest>'0'";
		$searchurladd[] = "digest=1";
	} elseif($conditions['digest'] == 2) {
		$sql .= " AND t.digest='0'";
		$searchurladd[] = "digest=2";
	}
	if($conditions['attach'] == 1) {
		$sql .= " AND t.attachment>'0'";
		$searchurladd[] = "attach=1";
	} elseif($conditions['attach'] == 2) {
		$sql .= " AND t.attachment='0'";
		$searchurladd[] = "attach=2";
	}
	if($conditions['rate'] == 1) {
		$sql .= " AND t.rate>'0'";
		$searchurladd[] = "rate=1";
	} elseif($conditions['rate'] == 2) {
		$sql .= " AND t.rate='0'";
		$searchurladd[] = "rate=2";
	}
	if($conditions['highlight'] == 1) {
		$sql .= " AND t.highlight>'0'";
		$searchurladd[] = "highlight=1";
	} elseif($conditions['highlight'] == 2) {
		$sql .= " AND t.highlight='0'";
		$searchurladd[] = "highlight=2";
	}
	if(!empty($conditions['special'])) {
		$specials = $comma = '';
		$searchurladd[] = "special={$conditions['special']}";
		foreach($conditions['special'] as $val) {
			$specials .= $comma.'\''.$val.'\'';
			$comma = ',';
		}
		if($conditions['specialthread'] == 1) {
			$sql .=  " AND t.special IN ($specials)";
			$searchurladd[] = "specialthread=1";
		} elseif($conditions['specialthread'] == 2) {
			$sql .=  " AND t.special NOT IN ($specials)";
			$searchurladd[] = "specialthread=2";
		}
	}

	if(trim($conditions['keywords'])) {
		$sqlkeywords = '';
		$or = '';
		$keywords = explode(',', str_replace(' ', '', $conditions['keywords']));
		for($i = 0; $i < count($keywords); $i++) {
			$sqlkeywords .= " $or t.subject LIKE '%".$keywords[$i]."%'";
			$or = 'OR';
		}
		$sql .= " AND ($sqlkeywords)";
		$searchurladd[] = "keywords={$conditions['keywords']}";
	}
	$threadlist = array();
	$threadtable = $conditions['sourcetableid'] ? "forum_thread_{$conditions['sourcetableid']}" : 'forum_thread';
	if($sql || $conditions['sourcetableid']) {
		$sql = "t.isgroup='0' $sql";
		$threadcount = DB::result_first("SELECT count(*) FROM ".DB::table($threadtable)." t WHERE $sql");
		if(isset($offset) && isset($length)) {
			$sql .= " LIMIT $offset, $length";
		}
		if($onlycount) {
			return DB::result_first("SELECT COUNT(*) FROM ".DB::table($threadtable)." t LEFT JOIN ".DB::table('forum_forum')." f ON t.fid=f.fid WHERE $sql");
		}
		$pagetmp = $page;
		do {
			$query = DB::query("SELECT t.fid, t.tid, t.posttableid, t.subject, t.authorid, t.author, t.views, t.replies, t.lastpost FROM ".DB::table($threadtable)." t LEFT JOIN ".DB::table('forum_forum')." f ON t.fid=f.fid WHERE $sql");
			$pagetmp--;
		} while(!DB::num_rows($query) && $pagetmp);

		while($thread = DB::fetch($query)) {
			$thread['lastpost'] = dgmdate($thread['lastpost']);
			$threadlist[] = $thread;
		}
	}
	return $threadlist;
}

function update_threadtableids() {
	$threadtableids = array('0' => 0);
	$db = DB::object();
	$query = $db->query("SHOW TABLES LIKE '".str_replace('_', '\_', DB::table('forum_thread').'_%')."'");
	while($table = $db->fetch_array($query, MYSQL_NUM)) {
		$tablename = $table[0];
		$tableid = substr($tablename, strrpos($tablename, '_') + 1);
		if(empty($tableid)) {
			continue;
		}
		$threadtableids[] = $tableid;
	}
	DB::insert('common_setting', array(
		'skey' => 'threadtableids',
		'svalue' => serialize($threadtableids),
	), false, true);
	save_syscache('threadtableids', $threadtableids);
}
?>