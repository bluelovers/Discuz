<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_postsplit.php 16845 2010-09-15 09:41:41Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
define('IN_DEBUG', false);

@set_time_limit(0);
define('MAX_POSTS_MOVE', 100000);
cpheader();
$topicperpage = 50;

if(empty($operation)) {
	$operation = 'manage';
}

$query = DB::query("SELECT skey, svalue FROM ".DB::table('common_setting')." WHERE skey IN ('posttable_info', 'posttableids', 'threadtableids')");
while($var = DB::fetch($query)) {
	switch($var['skey']) {
		case 'posttable_info':
			$posttable_info = $var['svalue'];
			break;
		case 'posttableids':
			$posttableids = $var['svalue'];
			break;
		case 'threadtableids':
			$threadtableids = $var['svalue'];
			break;
	}
}

if(empty($posttable_info)) {
	$posttable_info = array();
	$posttable_info[0]['type'] = 'primary';
} else {
	$posttable_info = unserialize($posttable_info);
}

if(empty($posttableids)) {
	$posttableids = array();
} else {
	$posttableids = unserialize($posttableids);
}

if($operation == 'manage') {
	shownav('founder', 'nav_postsplit');
	if(!submitcheck('postsplit_manage')) {
		showsubmenu('nav_postsplit', array(
			array('nav_postsplit_manage', 'postsplit&operation=manage', 1),
			array('nav_postsplit_move', 'postsplit&operation=move', 0),
		));

		showtips('postsplit_manage_tips');
		showformheader('postsplit&operation=manage');
		showtableheader();

		showsubtitle(array('postsplit_manage_tablename', $lang['type'], 'postsplit_manage_postcount', 'postsplit_manage_datalength', 'postsplit_manage_indexlength', 'postsplit_manage_table_createtime', 'postsplit_manage_table_memo', ''));
		$query = DB::query("SHOW TABLES LIKE '".DB::table('forum_post')."\_%'");

		$tablename = DB::table('forum_post');
		$tableid = 0;
		$tablestatus = gettablestatus($tablename);
		$postcount = $tablestatus['Rows'];
		$data_length = $tablestatus['Data_length'];
		$index_length = $tablestatus['Index_length'];

		$primarychecked = $posttable_info[$tableid]['type'] == 'primary' ? 'checked="checked"' : '';
		$additionchecked = $posttable_info[$tableid]['type'] == 'addition' ? 'checked="checked"' : '';
		$archivechecked = $posttable_info[$tableid]['type'] == 'archive' ? 'checked="checked"' : '';

		$tabletypeselect = "<select name=\"tabletype[$tableid]\">";
		foreach(array('primary', 'addition', 'archive') as $typename) {
			$typename_zhs = $lang['postsplit_manage_'.$typename.'_table'];
			$tabletypeselect .= "<option value=\"$typename\"".($posttable_info[$tableid]['type'] == $typename ? ' selected="selected"' : '').">$typename_zhs</option>";
		}
		$tabletypeselect .= "</select>";
		showtablerow('', array('', '', 'class="td25"'), array($tablename, $tabletypeselect, $postcount, $data_length, $index_length, $tablestatus['Create_time'], "<input type=\"text\" class=\"txt\" name=\"memo[0]\" value=\"{$posttable_info[0]['memo']}\" />", ''));

		while($table = DB::fetch($query)) {
			list($tempkey, $tablename) = each($table);
			$tableid = gettableid($tablename);
			if(in_array($tableid, array('tableid')) || !preg_match('/^\d+$/', $tableid)) {
				continue;
			}
			$tablestatus = gettablestatus($tablename);
			$postcount = $tablestatus['Rows'];
			$data_length = $tablestatus['Data_length'];
			$index_length = $tablestatus['Index_length'];

			$primarychecked = $posttable_info[$tableid]['type'] == 'primary' ? 'checked="checked"' : '';
			$additionchecked = $posttable_info[$tableid]['type'] == 'addition' ? 'checked="checked"' : '';
			$archivechecked = $posttable_info[$tableid]['type'] == 'archive' ? 'checked="checked"' : '';

			$tabletypeselect = "<select name=\"tabletype[$tableid]\">";
			foreach(array('primary', 'addition', 'archive') as $typename) {
				$typename_zhs = $lang['postsplit_manage_'.$typename.'_table'];
				$tabletypeselect .= "<option value=\"$typename\"".($posttable_info[$tableid]['type'] == $typename ? ' selected="selected"' : '').">$typename_zhs</option>";
			}
			$tabletypeselect .= "</select>";

			showtablerow('', array('style="width: 160px;"'), array($tablename, $tabletypeselect, $postcount, $data_length, $index_length, $tablestatus['Create_time'], "<input type=\"text\" class=\"txt\" name=\"memo[$tableid]\" value=\"{$posttable_info[$tableid]['memo']}\" />", '<a href="'.ADMINSCRIPT."?action=postsplit&operation=droptable&tableid={$tableid}\"> {$lang['delete']} </a>"));
		}
		showsubmit('postsplit_manage', 'postsplit_manage_update_tabletype_submit', '', "<a class=\"btn\" style=\"border-style: solid; border-width: 1px;\" href=\"?action=postsplit&operation=addnewtable&tabletype=primary\">{$lang['postsplit_manage_primary_table_add']}</a>&nbsp;&nbsp;<a class=\"btn\" style=\"border-style: solid; border-width: 1px;\" href=\"?action=postsplit&operation=addnewtable&tabletype=archive\">{$lang['postsplit_manage_archive_table_add']}</a> <a class=\"btn\" style=\"border-style: solid; border-width: 1px;\" href=\"?action=postsplit&operation=pidreset\">{$lang['postsplit_manage_pidreset']}</a>");
		showtablefooter();
		showformfooter();
	} else {
		$primary_count = $addition_count = $archive_count = 0;
		$posttable_info = array();
		$tableids = get_posttableids();
		foreach($_G['gp_tabletype'] as $key => $value) {
			if(!in_array($key, $tableids)) {
				continue;
			}
			$posttable_info[$key]['memo'] = $_G['gp_memo'][$key];
			switch($value) {
				case 'primary':
					$posttable_info[$key]['type'] = 'primary';
					$primary_count ++;
					break;
				case 'addition':
					$posttable_info[$key]['type'] = 'addition';
					$addition_count ++;
					break;
				case 'archive':
					$posttable_info[$key]['type'] = 'archive';
					$archive_count ++;
					break;
			}
		}
		foreach($posttable_info as $key => $value) {
			if($key === '') {
				unset($posttable_info[$key]);
			}
		}

		if($primary_count != 1) {
			cpmsg('postsplit_no_prime_table', 'action=postsplit&operation=manage', 'error');
		}
		if($addition_count > 1) {
			cpmsg('postsplit_more_addition_table', 'action=postsplit&operation=manage', 'error');
		}

		DB::insert('common_setting', array(
			'skey' => 'posttable_info',
			'svalue' => serialize($posttable_info),
		), false, true);
		save_syscache('posttable_info', $posttable_info);
		update_posttableids();
		cpmsg('postsplit_table_type_update_succeed', 'action=postsplit&operation=manage', 'succeed');
	}

} elseif($operation == 'move') {
	if(!$_G['setting']['bbclosed'] && !IN_DEBUG) {
		cpmsg('postsplit_forum_must_be_closed', 'action=postsplit&operation=manage', 'error');
	}

	require_once libfile('function/forumlist');
	$threadtableselect = '<select name="threadtableid"><option value="0">'.DB::table('forum_thread')."</option>";

	if(!$threadtableids) {
		$threadtableids = array();
	} else {
		$threadtableids = unserialize($threadtableids);
	}
	foreach($threadtableids as $threadtableid) {
		$selected = $_G['gp_threadtableid'] == $threadtableid ? 'selected="selected"' : '';
		$threadtableselect .= "<option value=\"$threadtableid\" $selected>".DB::table("forum_thread_$threadtableid")."</option>";
	}
	$threadtableselect .= '</select>';

	$forumselect = '<select name="inforum"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option>'.
		'<option value="">&nbsp;</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>';
	if(isset($_G['gp_inforum'])) {
		$forumselect = preg_replace("/(\<option value=\"{$_G['gp_inforum']}\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
	}
	$posttableselect = '<select name="sourcetableid"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option>';
	foreach(array_keys($posttable_info) as $tableid) {
		$selected = $_G['gp_sourcetableid'] == $tableid ? 'selected="selected"' : '';
		$tablename = $tableid ? "forum_post_$tableid" : 'forum_post';
		$typename = $lang['postsplit_manage_'.$posttable_info[$tableid]['type'].'_table'];
		$posttableselect .= "<option value=\"$tableid\" $selected>".DB::table($tablename)." ($typename)</option>";
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
		$('threadforum').page.value=number;
		$('threadforum').postsplit_move_search.click();
	}
</script>
EOT;
	shownav('founder', 'nav_postsplit');
	if(!submitcheck('postsplit_move_submit') && !$_G['gp_moving']) {
		showsubmenu('nav_postsplit', array(
			array('nav_postsplit_manage', 'postsplit&operation=manage', 0),
			array('nav_postsplit_move', 'postsplit&operation=move', 1),
		));
		showtips('postsplit_move_tips');
		showtagheader('div', 'threadsearch', !submitcheck('postsplit_move_search'));
		showformheader('postsplit&operation=move', '', 'threadforum');
		showhiddenfields(array('page' => $_G['gp_page']));
		showtableheader();
		showsetting('threads_search_detail', 'detail', $_G['gp_detail'], 'radio');
		showsetting('postsplit_move_threadtable', '', '', $threadtableselect);
		showsetting('postsplit_move_threads_search_posttable', '', '', $posttableselect);
		showsetting('threads_search_forum', '', '', $forumselect);
		showsetting('postsplit_move_tidrange', array('tidmin', 'tidmax'), array($_G['gp_tidmin'], $_G['gp_tidmax']), 'range');
		showsetting('threads_search_noreplyday', 'noreplydays', isset($_G['gp_noreplydays']) ? $_G['gp_noreplydays'] : '365', 'text');
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
		showsetting('threads_search_special', array('specialthread', array(
			array(0, cplang('unlimited'), array('showspecial' => 'none')),
			array(1, cplang('threads_search_include_yes'), array('showspecial' => '')),
			array(2, cplang('threads_search_include_no'), array('showspecial' => '')),
		), TRUE), isset($_G['gp_specialthread']) ? $_G['gp_specialthread'] : 2, 'mradio');
		showtablerow('id="showspecial" style="display:'.($_G['gp_specialthread'] ? '' : 'none').'"', 'class="sub" colspan="2"', mcheckbox('special', array(
			1 => cplang('thread_poll'),
			2 => cplang('thread_trade'),
			3 => cplang('thread_reward'),
			4 => cplang('thread_activity'),
			5 => cplang('thread_debate')
		), $_G['gp_special'] ? $_G['gp_special'] : array(1, 2, 3, 4, 5)));
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

		showsubmit('postsplit_move_search', 'submit', '', 'more_options');
		showtablefooter();
		showformfooter();
		showtagfooter('div');
		if(submitcheck('postsplit_move_search')) {
			$conditions = array(
				'threadtableid' => $_G['gp_threadtableid'],
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
				'viewsless' => $_G['viewsless'],
				'repliesmore' => $_G['gp_repliesmore'],
				'repliesless' => $_G['gp_repliesless'],
				'readpermmore' => $_G['gp_readpermmore'],
				'pricemore' => $_G['gp_readpermmore'],
				'noreplydays' => $_G['gp_noreplydays'],
				'specialthread' => $_G['gp_specialthread'],
				'special' => $_G['gp_special'],
				'sticky' => $_G['gp_sticky'],
				'digest' => $_G['gp_digest'],
				'attach' => $_G['gp_attach'],
				'rate' => $_G['gp_rate'],
				'highlight' => $_G['gp_highlight'],
			);
			$searchurladd = array();
			if($_G['gp_detail']) {
				$pagetmp = $page;
				$threadlist = postsplit_search_threads($conditions, ($pagetmp - 1) * $topicperpage, $topicperpage);
			} else {
				$threadtomove = postsplit_search_threads($conditions, NULL, NULL, TRUE, TRUE);
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
						$thread['posttableid'] ? DB::table("forum_post_{$thread['posttableid']}") : DB::table("forum_post"),
						"<a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>",
						$thread['replies'],
						$thread['views']
					), TRUE);
				}
				$multi = multi($threadcount, $topicperpage, $page, ADMINSCRIPT."?action=postsplit&amp;operation=move");
				$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=postsplit&amp;operation=move&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
				$multi = str_replace("window.location='".ADMINSCRIPT."?action=postsplit&amp;operation=move&amp;page='+this.value", "page(this.value)", $multi);
			} else {
				foreach($threadlist as $thread) {
					$fids[] = $thread['fid'];
					$tids .= ','.$thread['tid'];
				}
				$multi = '';
			}
			$fids = implode(',', array_unique($fids));

			showtagheader('div', 'threadlist', TRUE);
			$urladd = implode('&', $searchurladd);
			showformheader("postsplit&operation=move&threadtableid={$_G['gp_threadtableid']}&threadtomove=".$threadtomove."&{$urladd}");
			showhiddenfields($_G['gp_detail'] ? array('fids' => $fids) : array('conditions' => serialize($conditions)));
			showtableheader(cplang('threads_result').' '.$threadcount.' <a href="###" onclick="$(\'threadlist\').style.display=\'none\';$(\'threadsearch\').style.display=\'\';" class="act lightlink normal">'.cplang('research').'</a>', 'nobottom');
			showsubtitle(array('', 'postsplit_move_to', 'postsplit_manage_postcount', 'postsplit_manage_datalength', 'postsplit_manage_indexlength', 'postsplit_manage_table_createtime', 'postsplit_manage_table_memo'));

			if(!$threadcount) {

				showtablerow('', 'colspan="3"', cplang('threads_thread_nonexistence'));

			} else {
				$query = DB::query("SHOW TABLES LIKE '".DB::table('forum_post')."\_%'");

				$tablename = DB::table('forum_post');
				$tableid = 0;
				$tablestatus = gettablestatus($tablename);
				$postcount = $tablestatus['Rows'];
				$data_length = $tablestatus['Data_length'];
				$index_length = $tablestatus['Index_length'];

				$tabletype = $lang['postsplit_manage_'.$posttable_info[$tableid]['type'].'_table'];
				showtablerow('', array('class="td25"'), array("<input class=\"radio\" ".($_G['gp_sourcetableid'] == '0' ? 'disabled="disabled"' : '')." type=\"radio\" name=\"tableid\" value=\"0\" />", $tablename." ($tabletype)", $postcount, $data_length, $index_length, $tablestatus['Create_time'], $posttable_info[$tableid]['memo']));
				while($table = DB::fetch($query)) {
					list($tempkey, $tablename) = each($table);
					$tableid = gettableid($tablename);
					if(in_array($tableid, array('tableid')) || !preg_match('/^\d+$/', $tableid)) {
						continue;
					}
					$tablestatus = gettablestatus($tablename);
					$postcount = $tablestatus['Rows'];
					$data_length = $tablestatus['Data_length'];
					$index_length = $tablestatus['Index_length'];

					$tabletype = $lang['postsplit_manage_'.$posttable_info[$tableid]['type'].'_table'];
					showtablerow('', array(), array("<input class=\"radio\" ".($_G['gp_sourcetableid'] == $tableid ? 'disabled="disabled"' : '')." type=\"radio\" name=\"tableid\" value=\"$tableid\" />", $tablename." ($tabletype)", $postcount, $data_length, $index_length, $tablestatus['Create_time'], $posttable_info[$tableid]['memo']));
				}

				if($_G['gp_detail']) {

					showtablefooter();
					showtableheader('threads_list', 'notop');
					showsubtitle(array('', 'subject', 'forum', 'postsplit_move_thread_table', 'author', 'threads_replies', 'threads_views'));
					echo $threads;

				}

			}
			showtablefooter();

			if($threadcount) {
				showtableheader('');
				showsetting('postsplit_move_threads_per_time', 'threads_per_time', 100, 'text');
				showtablefooter();
				showsubmit('postsplit_move_submit', 'submit', $_G['gp_detail'] ? '<input name="chkall" id="chkall" type="checkbox" class="checkbox" checked="checked" onclick="checkAll(\'prefix\', this.form, \'tidarray\', \'chkall\')" /><label for="chkall">'.cplang('select_all').'</label>' : '', '', $multi);

			}
			showformfooter();
			showtagfooter('div');

		}
	} else {
		if(!isset($_G['gp_tableid'])) {
			cpmsg('postsplit_no_target_table', '', 'error');
		}
		$continue = false;
		$conditions = unserialize(stripslashes($_G['gp_conditions']));
		$tidsarray = !empty($_G['gp_tidarray']) ? $_G['gp_tidarray'] : array();

		if(empty($tidsarray) && !empty($_G['gp_conditions'])) {
			$max_threads_move = intval($_G['gp_threads_per_time']) ? intval($_G['gp_threads_per_time']) : 100;
			$threadlist = postsplit_search_threads($conditions, 0, $max_threads_move);

			foreach($threadlist as $thread) {
				$tidsarray[] = $thread['tid'];
				$continue = true;
			}
		}
		if($tidsarray[0] == '0') {
			array_shift($tidsarray);
		}
		$threadtable = $conditions['threadtableid'] ? "forum_thread_{$conditions['threadtableid']}" : 'forum_thread';
		$threadlist = array();

		if(!empty($tidsarray)) {
			$query = DB::query("SELECT tid, replies, posttableid FROM ".DB::table($threadtable)." WHERE tid IN(".dimplode($tidsarray).")");

			while($thread = DB::fetch($query)) {
				$threadlist[$thread['tid']] = $thread;
			}
		}
		$firstlist = getatidheap($threadlist);

		$tidsarray = array_keys($threadlist);

		if(!empty($firstlist['tids'])) {
			$continue = true;
		}
		if($continue) {
			foreach($firstlist['tids'] as $tid) {
				$posttableid = $threadlist[$tid]['posttableid'];
				if($posttableid == $_G['gp_tableid']) {
					continue;
				}
				$posttable_source = $posttableid ? "forum_post_$posttableid" : 'forum_post';
				$posttable_target = $_G['gp_tableid'] ? "forum_post_{$_G['gp_tableid']}" : 'forum_post';

				DB::query("INSERT INTO ".DB::table($posttable_target)." SELECT * FROM ".DB::table($posttable_source)." WHERE tid='$tid'", 'SILENT');
				if(DB::errno()) {
					DB::delete($posttable_target, "tid='$tid'");
					DB::query("INSERT INTO ".DB::table($posttable_target)." SELECT * FROM ".DB::table($posttable_source)." WHERE tid='$tid'");
				}
				DB::update($threadtable, array(
					'posttableid' => intval($_G['gp_tableid']),
				), "tid='$tid'");
				DB::delete($posttable_source, "tid='$tid'");
			}


			$completed = intval($_G['gp_completed']) + count($firstlist['tids']);

			foreach($firstlist['tids'] as $tid) {
				unset($threadlist[$tid]);
			}
			$nextstep = $step + 1;
			cpmsg('postsplit_moving', "action=postsplit&operation=move&{$_G['gp_urladd']}&tableid={$_G['gp_tableid']}&completed=$completed&threadtomove={$_G['gp_threadtomove']}&step=$nextstep&moving=1", 'loadingform', array('count' => $completed, 'total' => intval($_G['gp_threadtomove']), 'tids' => implode(',', array_keys($threadlist)), 'threads_per_time' => $_G['gp_threads_per_time'], 'conditions' => stripslashes(htmlspecialchars($_G['gp_conditions']))));
		}
		cpmsg('postsplit_move_succeed', "action=postsplit&operation=manage", 'succeed');
	}
} elseif($operation == 'addnewtable') {
	$maxtableid = getmaxposttableid();

	DB::query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');
	$db = & DB::object();
	$query = DB::query("SHOW CREATE TABLE ".DB::table('forum_post'));
	$create = $db->fetch_row($query);
	$createsql = $create[1];
	$tableid = $maxtableid + 1;
	$createsql = str_replace(DB::table('forum_post'), DB::table('forum_post').'_'.$tableid, $createsql);
	DB::query($createsql);


	if($_G['gp_tabletype'] == 'primary'){
		$atableid = getposttableid('a');
		$ptableid = getposttableid('p');
		if($ptableid === NULL) {
			$ptableid = 0;
		}
		$posttable_info[$ptableid]['type'] = 'addition';
		if($atableid !== NULL) {
			$posttable_info[$atableid]['type'] = 'archive';
		}
		$posttable_info[$tableid]['type'] = 'primary';
	} elseif($_G['gp_tabletype'] == 'archive') {
		$posttable_info[$tableid]['type'] = 'archive';
	}
	DB::insert('common_setting', array(
		'skey' => 'posttable_info',
		'svalue' => serialize($posttable_info),
	), false, true);
	save_syscache('posttable_info', $posttable_info);
	update_posttableids();
	cpmsg('postsplit_table_create_succeed', 'action=postsplit&operation=manage', 'succeed');
} elseif($operation == 'droptable') {
	if($_G['gp_tableid'] > 0) {
		$tablename = DB::table("forum_post_".$_G['gp_tableid']);
	} else {
		$tablename = DB::table('forum_post');
	}
	$query = DB::query("SHOW TABLES LIKE '$tablename'");
	if(!DB::num_rows($query)) {
		cpmsg('postsplit_table_no_exists', 'action=postsplit&operation=manage', 'error', array('table' => $_G['gp_tablename']));
	}
	if($tablename == DB::table('forum_post')) {
		cpmsg('postsplit_drop_table_forum_post_error', 'action=postsplit&operation=manage', 'error', array('table' => DB::table('forum_post')));
	} else {
		if(DB::result_first("SELECT COUNT(*) FROM $tablename") > 0) {
			cpmsg('postsplit_drop_table_no_empty_error', 'action=postsplit&operation=manage', 'error');
		} else {
			$tableid = $_G['gp_tableid'];

			DB::query("DROP TABLE $tablename");
			if($posttable_info[$tableid]['type'] == 'primary') {
				$maxtableid = getmaxposttableid();
				$posttable_info[$maxtableid]['type'] = 'primary';
			}
			unset($posttable_info[$tableid]);
			DB::insert('common_setting', array(
				'skey' => 'posttable_info',
				'svalue' => serialize($posttable_info),
			), false, true);
			save_syscache('posttable_info', $posttable_info);

			update_posttableids();

			cpmsg('postsplit_drop_table_succeed', 'action=postsplit&operation=manage', 'succeed', array('table' => $tablename));
		}
	}
} elseif($operation == 'pidreset'){
	loadcache('posttableids');
	if(!empty($_G['cache']['posttableids'])) {
		$posttableids = $_G['cache']['posttableids'];
	} else {
		$posttableids = array('0');
	}
	$pidmax = 0;
	foreach($posttableids as $id) {
		if($id == 0) {
			$pidtmp = DB::result_first("SELECT MAX(pid) FROM ".DB::table('forum_post'));
		} else {
			$pidtmp = DB::result_first("SELECT MAX(pid) FROM ".DB::table("forum_post_$id"));
		}
		if($pidtmp > $pidmax) {
			$pidmax = $pidtmp;
		}
	}
	$auto_increment = $pidmax + 1;
	DB::query("ALTER TABLE ".DB::table('forum_post_tableid')." AUTO_INCREMENT=$auto_increment");
	cpmsg('postsplit_resetpid_succeed', 'action=postsplit&operation=manage', 'succeed');
}

function gettableid($tablename) {
	$tableid = substr($tablename, strrpos($tablename, '_') + 1);
	return $tableid;
}

function getmaxposttableid() {
	$query = DB::query("SHOW TABLES LIKE '".DB::table('forum_post')."\_%'");
	$maxtableid = 0;
	while($table = DB::fetch($query)) {
		list($tempkey, $tablename) = each($table);
		$tableid = intval(gettableid($tablename));
		if($tableid > $maxtableid) {
			$maxtableid = $tableid;
		}
	}
	return $maxtableid;
}

function update_posttableids() {
	$tableids = get_posttableids();
	DB::insert('common_setting', array(
		'skey' => 'posttableids',
		'svalue' => serialize($tableids),
	), false, true);
	save_syscache('posttableids', $tableids);
}

function get_posttableids() {
	$tableids = array(0);
	$query = DB::query("SHOW TABLES LIKE '".DB::table('forum_post')."\_%'");
	while($table = DB::fetch($query)) {
		list($tempkey, $tablename) = each($table);
		$tableid = gettableid($tablename);
		if(!preg_match('/^\d+$/', $tableid)) {
			continue;
		}
		$tableid = intval($tableid);
		if(!$tableid) {
			continue;
		}
		$tableids[] = $tableid;
	}
	return $tableids;
}

function postsplit_search_threads($conditions, $offset = null, $length = null, $nodetails = FALSE, $onlycount = FALSE) {
	global $_G, $searchurladd, $page, $threadcount;
	$sql = '';
	if($conditions['sourcetableid'] != '' && $conditions['sourcetableid'] != 'all') {
		$sql .= "AND posttableid='{$conditions['sourcetableid']}'";
		$searchurladd[] = "sourcetableid={$conditions['sourcetableid']}";
	}
	if($conditions['inforum'] != '' && $conditions['inforum'] != 'all') {
		$sql .= " AND fid='{$conditions['inforum']}'";
		$searchurladd[] = "inforum={$conditions['inforum']}";
	}
	if($conditions['intype'] != '' && $conditions['intype'] != 'all') {
		$sql .= " AND typeid='{$conditions['intype']}'";
		$searchurladd[] = "intype={$conditions['intype']}";
	}
	if($conditions['insort'] != '' && $conditions['insort'] != 'all') {
		$sql .= " AND sortid='{$conditions['insort']}'";
		$searchurladd[] = "insort={$conditions['insort']}";
	}
	if($conditions['tidmin'] != '') {
		$sql .= " AND tid>='{$conditions['tidmin']}'";
		$searchurladd[] = "tidmin={$conditions['tidmin']}";
	}
	if($conditions['tidmax'] != '') {
		$sql .= " AND tid<='{$conditions['tidmax']}'";
		$searchurladd[] = "tidmax={$conditions['tidmax']}";
	}
	if($conditions['viewsless'] != '') {
		$sql .= " AND views<'{$conditions['viewsless']}'";
		$searchurladd[] = "viewsless={$conditions['viewsless']}";
	}
	if($conditions['viewsmore'] != '') {
		$sql .= " AND views>'{$conditions['viewsmore']}'";
		$searchurladd[] = "viewsmore={$conditions['viewsmore']}";
	}
	if($conditions['repliesless'] != '') {
		$sql .= " AND replies<'{$conditions['repliesless']}'";
		$searchurladd[] = "repliesless={$conditions['repliesless']}";
	}
	if($conditions['repliesmore'] != '') {
		$sql .= " AND replies>'{$conditions['repliesmore']}'";
		$searchurladd[] = "repliesmore={$conditions['repliesmore']}";
	}
	if($conditions['readpermmore'] != '') {
		$sql .= " AND readperm>'{$conditions['readpermmore']}'";
		$searchurladd[] = "readpermmore={$conditions['readpermmore']}";
	}
	if($conditions['pricemore'] != '') {
		$sql .= " AND price>'{$conditions['pricemore']}'";
		$searchurladd[] = "pricemore={$conditions['pricemore']}";
	}
	if($conditions['beforedays'] != '') {
		$sql .= " AND dateline<'{$_G['timestamp']}'-'{$conditions['beforedays']}'*86400";
		$searchurladd[] = "beforedays={$conditions['beforedays']}";
	}
	if($conditions['noreplydays'] != '') {
		$sql .= " AND lastpost<'{$_G['timestamp']}'-'{$conditions['noreplydays']}'*86400";
		$searchurladd[] = "noreplydays={$conditions['noreplydays']}";
	}
	if($conditions['starttime'] != '') {
		$sql .= " AND dateline>'".strtotime($conditions['starttime'])."'";
		$searchurladd[] = "starttime={$conditions['starttime']}";
	}
	if($conditions['endtime'] != '') {
		$sql .= " AND dateline<='".strtotime($conditions['endtime'])."'";
		$searchurladd[] = "endtime={$conditions['endtime']}";
	}

	if(trim($conditions['keywords'])) {
		$sqlkeywords = '';
		$or = '';
		$keywords = explode(',', str_replace(' ', '', $conditions['keywords']));
		for($i = 0; $i < count($keywords); $i++) {
			$sqlkeywords .= " $or subject LIKE '%".$keywords[$i]."%'";
			$or = 'OR';
		}
		$sql .= " AND ($sqlkeywords)";
		$searchurladd[] = "keywords={$conditions['keywords']}";
	}

	if($conditions['users'] != '') {
		$sql .= trim($conditions['users']) ? " AND author IN ('".str_replace(',', '\',\'', str_replace(' ', '', trim($conditions['users'])))."')" : '';
		$searchurladd[] = "users={$conditions['users']}";
	}

	if($conditions['sticky'] == 1) {
		$sql .= " AND displayorder>'0'";
		$searchurladd[] = "sticky=1";
	} elseif($conditions['sticky'] == 2) {
		$sql .= " AND displayorder='0'";
		$searchurladd[] = "sticky=2";
	}
	if($conditions['digest'] == 1) {
		$sql .= " AND digest>'0'";
		$searchurladd[] = "digest=1";
	} elseif($conditions['digest'] == 2) {
		$sql .= " AND digest='0'";
		$searchurladd[] = "digest=2";
	}
	if($conditions['attach'] == 1) {
		$sql .= " AND attachment>'0'";
		$searchurladd[] = "attach=1";
	} elseif($conditions['attach'] == 2) {
		$sql .= " AND attachment='0'";
		$searchurladd[] = "attach=2";
	}
	if($conditions['rate'] == 1) {
		$sql .= " AND rate>'0'";
		$searchurladd[] = "rate=1";
	} elseif($conditions['rate'] == 2) {
		$sql .= " AND rate='0'";
		$searchurladd[] = "rate=2";
	}
	if($conditions['highlight'] == 1) {
		$sql .= " AND highlight>'0'";
		$searchurladd[] = "highlight=1";
	} elseif($conditions['highlight'] == 2) {
		$sql .= " AND highlight='0'";
		$searchurladd[] = "highlight=2";
	}
	if(!empty($conditions['special'])) {
		$specials = $comma = '';
		$searchurladd[] = "special={$_G['gp_special']}";
		foreach($conditions['special'] as $val) {
			$specials .= $comma.'\''.$val.'\'';
			$comma = ',';
		}
		if($conditions['specialthread'] == 1) {
			$sql .=  " AND special IN ($specials)";
			$searchurladd[] = "specialthread=1";
		} elseif($conditions['specialthread'] == 2) {
			$sql .=  " AND special NOT IN ($specials)";
			$searchurladd[] = "specialthread=2";
		}
	}
	$threadtable = $conditions['threadtableid'] ? "forum_thread_{$conditions['threadtableid']}" : 'forum_thread';
	$threadlist = array();
	if($nodetails) {
		$fields = 'fid, tid';
	} else {
		$fields = 'fid, tid, posttableid, subject, authorid, author, views, replies, lastpost';
	}
	if($sql || $conditions['threadtableid']) {
		$sql = "displayorder>='0' $sql";
		$threadcount = DB::result_first("SELECT count(*) FROM ".DB::table($threadtable)." WHERE $sql");
		if(isset($offset) && isset($length)) {
			$sql .= " LIMIT $offset, $length";
		}
		if($onlycount) {
			return DB::result_first("SELECT COUNT(*) FROM ".DB::table($threadtable)." WHERE $sql");
		}
		$query = DB::query("SELECT $fields FROM ".DB::table($threadtable)." WHERE $sql");

		while($thread = DB::fetch($query)) {
			$thread['lastpost'] = dgmdate($thread['lastpost']);
			$threadlist[] = $thread;
		}
	}
	return $threadlist;
}

function gettablestatus($tablename) {
	$status = DB::fetch_first("SHOW TABLE STATUS LIKE '".str_replace('_', '\_', $tablename)."'");
	$status['Data_length'] = $status['Data_length'] / 1024 / 1024;
	$nums = intval(log($status['Data_length']) / log(10));
	$digits = 0;
	if($nums <= 3) {
		$digits = 3 - $nums;
	}
	$status['Data_length'] = number_format($status['Data_length'], $digits).' MB';

	$status['Index_length'] = $status['Index_length'] / 1024 / 1024;
	$nums = intval(log($status['Index_length']) / log(10));
	$digits = 0;
	if($nums <= 3) {
		$digits = 3 - $nums;
	}
	$status['Index_length'] = number_format($status['Index_length'], $digits).' MB';
	return $status;
}

function getatidheap($threadlist) {
	$heap = array();
	$heap['num'] = 0;
	$heap['tids'] = array();
	$index = 0;
	foreach($threadlist as $thread) {
		if($heap['num'] && $heap['num'] + $thread['replies'] > MAX_POSTS_MOVE) {
			break;
		}
		$heap['num'] += $thread['replies'] + 1;
		$heap['tids'][] = $thread['tid'];
		$index ++;
	}
	return $heap;
}