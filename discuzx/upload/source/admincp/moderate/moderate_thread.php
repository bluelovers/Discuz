<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_thread.php 24018 2011-08-22 02:28:39Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('modsubmit') && !$_G['gp_fast']) {

	require_once libfile('function/discuzcode');

	$select[$_G['gp_tpp']] = $_G['gp_tpp'] ? "selected='selected'" : '';
	$tpp_options = "<option value='20' $select[20]>20</option><option value='50' $select[50]>50</option><option value='100' $select[100]>100</option>";
	$tpp = !empty($_G['gp_tpp']) ? $_G['gp_tpp'] : '20';
	$start_limit = ($page - 1) * $tpp;
	$dateline = $_G['gp_dateline'] ? $_G['gp_dateline'] : '604800';
	$dateline_options = '';
	foreach(array('all', '604800', '2592000', '7776000') as $v) {
		$selected = '';
		if($dateline == $v) {
			$selected = "selected='selected'";
		}
		$dateline_options .= "<option value=\"$v\" $selected>".cplang("dateline_$v");
	}

	shownav('topic', $lang['moderate_threads']);
	showsubmenu('nav_moderate_threads', $submenu);

	showformheader("moderate&operation=threads");
	showtableheader('search');
	showtablerow('', array('width="60"', 'width="160"', 'width="60"'),
		array(
			cplang('username'), "<input size=\"15\" name=\"username\" type=\"text\" value=\"$_G[gp_username]\" />",
			cplang('moderate_title_keyword'), "<input size=\"15\" name=\"title\" type=\"text\" value=\"$_G[gp_title]\" />",
		)
	);
        showtablerow('', array('width="60"', 'width="160"', 'width="60"'),
                array(
                        "$lang[perpage]",
                        "<select name=\"tpp\">$tpp_options</select><label><input name=\"showcensor\" type=\"checkbox\" class=\"checkbox\" value=\"yes\" ".($showcensor ? ' checked="checked"' : '')."/> $lang[moderate_showcensor]</label>",
                        "$lang[moderate_bound]",
                        "<select name=\"filter\">$filteroptions</select>
                        <select name=\"modfid\">$forumoptions</select>
                        <select name=\"dateline\">$dateline_options</select>
                        <input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"
                )
        );
	showtablefooter();
	showtableheader();

	$sqlwhere = '';
	if(!empty($_G['gp_username'])) {
		$sqlwhere .= " AND t.author='{$_G['gp_username']}'";
	}
	if(!empty($_G['gp_title'])) {
		$title = str_replace(array('_', '%'), array('\_', '\%'), $_G['gp_title']);
		$sqlwhere .= " AND t.subject LIKE '%{$title}%'";
	}
	$datesql = '';
	if(!empty($dateline) && $dateline != 'all') {
		$datesql = " AND m.dateline>'".(TIMESTAMP - $dateline)."'";
	}
	$modcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_moderate')." m
			LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=m.id WHERE m.idtype='tid' AND m.status='$moderatestatus' $datesql $fidadd[and] $fidadd[t]$fidadd[fids] ".($modfid == '-1' ? " AND t.isgroup='1'": '')."$sqlwhere");
	$start_limit = ($page - 1) * $tpp;
	$query = DB::query("SELECT f.name AS forumname, f.allowsmilies, f.allowhtml, f.allowbbcode, f.allowimgcode, t.tid, t.fid, t.posttableid, t.sortid, t.authorid, t.author, t.subject, t.dateline
		FROM ".DB::table('common_moderate')." m
			LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=m.id
			LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=t.fid
		WHERE m.idtype='tid' AND m.status='$moderatestatus' $datesql $fidadd[and] $fidadd[t]$fidadd[fids] ".($modfid == '-1' ? " AND t.isgroup='1'": '')." $sqlwhere
		ORDER BY m.dateline DESC
		LIMIT $start_limit, $tpp");
	$tids = $threadlist = array();
	while($thread = DB::fetch($query)) {
		$tids[$thread['posttableid']][] = $thread['tid'];
		$threadlist[$thread['tid']] = $thread;
	}
	if($tids) {
		foreach($tids as $posttableid => $tid) {
			$query = DB::query("SELECT tid, pid, message, useip, attachment, htmlon, smileyoff, bbcodeoff FROM ".DB::table(getposttable($posttableid))." WHERE tid IN (".dimplode($tid).") AND first='1'");
			while($post = DB::fetch($query)) {
				$threadlist[$post['tid']] = array_merge($threadlist[$post['tid']], $post);
			}
		}
	}
	$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=threads&filter=$filter&modfid=$modfid&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&title={$_G['gp_title']}&tpp=$tpp&showcensor=$showcensor");

	echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> &nbsp;<a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a><p>';

	require_once libfile('function/misc');
	foreach($threadlist as $thread) {
		$threadsortinfo = '';
		$thread['useip'] = $thread['useip'] . '-' . convertip($thread['useip']);
		if($thread['authorid'] && $thread['author']) {
			$thread['author'] = "<a href=\"?action=members&operation=search&uid=$thread[authorid]&submit=yes\" target=\"_blank\">$thread[author]</a>";
		} elseif($thread['authorid'] && !$thread['author']) {
			$thread['author'] = "<a href=\"?action=members&operation=search&uid=$thread[authorid]&submit=yes\" target=\"_blank\">$lang[anonymous]</a>";
		} else {
			$thread['author'] = $lang['guest'];
		}

		$thread['dateline'] = dgmdate($thread['dateline']);
		$thread['message'] = discuzcode($thread['message'], $thread['smileyoff'], $thread['bbcodeoff']);
		require_once libfile('class/censor');
		$censor = & discuz_censor::instance();
		$censor->highlight = '#FF0000';
		if($showcensor) {
			$censor->check($thread['subject']);
			$censor->check($thread['message']);
		}
		$thread['modthreadkey'] = modauthkey($thread['tid']);
		$censor_words = $censor->words_found;
		if(count($censor_words) > 3) {
			$censor_words = array_slice($censor_words, 0, 3);
		}
		$thread['censorwords'] = implode(', ', $censor_words);

		if($thread['attachment']) {
			require_once libfile('function/attachment');

			$queryattach = DB::query("SELECT aid, filename, filesize, attachment, isimage, remote FROM ".DB::table(getattachtablebytid($thread['tid']))." WHERE tid='$thread[tid]'");
			while($attach = DB::fetch($queryattach)) {
				$_G['setting']['attachurl'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
				$attach['url'] = $attach['isimage']
						? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" onload=\"if(this.width > 400) {this.resized=true; this.width=400;}\">"
						 : "<a href=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
				$thread['message'] .= "<br /><br />$lang[attachment]: ".attachtype(fileext($attach['filename'])).$attach['url'];
			}
		}

		if($thread['sortid']) {
			require_once libfile('function/threadsort');
			$threadsortshow = threadsortshow($thread['sortid'], $thread['tid']);

			foreach($threadsortshow['optionlist'] as $option) {
				$threadsortinfo .= $option['title'].' '.$option['value']."<br />";
			}
		}

		if(count($censor_words)) {
			$thread_censor_text = "<span style=\"color: red;\">($thread[censorwords])</span>";
		} else {
			$thread_censor_text = '';
		}
		showtagheader('tbody', '', true, 'hover');
		showtablerow("id=\"mod_$thread[tid]_row1\"", array("id=\"mod_$thread[tid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$thread[tid]]\" id=\"mod_$thread[tid]_1\" value=\"validate\" onclick=\"mod_setbg($thread[tid], 'validate');\"><label for=\"mod_$thread[tid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$thread[tid]]\" id=\"mod_$thread[tid]_2\" value=\"delete\" onclick=\"mod_setbg($thread[tid], 'delete');\"><label for=\"mod_$thread[tid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$thread[tid]]\" id=\"mod_$thread[tid]_3\" value=\"ignore\" onclick=\"mod_setbg($thread[tid], 'ignore');\"><label for=\"mod_$thread[tid]_3\">$lang[ignore]</label></li></ul>",
			"<h3><a href=\"javascript:;\" onclick=\"display_toggle('$thread[tid]');\">$thread[subject]</a> $thread_censor_text</h3><p>$thread[useip]</p>",
			"<a target=\"_blank\" href=\"forum.php?mod=forumdisplay&fid=$thread[fid]\">$thread[forumname]</a>",
			"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$thread[authorid]&submit=yes\">$thread[author]</a></p> <p>$thread[dateline]</p>",
			"<a target=\"_blank\" href=\"forum.php?mod=viewthread&tid=$thread[tid]&modthreadkey=$thread[modthreadkey]\">$lang[view]</a>&nbsp;<a href=\"forum.php?mod=post&action=edit&fid=$thread[fid]&tid=$thread[tid]&pid=$thread[pid]&modthreadkey=$thread[modthreadkey]\" target=\"_blank\">$lang[edit]</a>",
		));
		showtablerow("id=\"mod_$thread[tid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:120px; word-break: break-all;">'.$thread['message'].'<br /><br />'.$threadsortinfo.'</div>');
		showtablerow("id=\"mod_$thread[tid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=threads&fast=1&fid=$thread[fid]&tid=$thread[tid]&moderate[$thread[tid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=threads&fast=1&fid=$thread[fid]&tid=$thread[tid]&moderate[$thread[tid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=threads&fast=1&fid=$thread[fid]&tid=$thread[tid]&moderate[$thread[tid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a> | <a href=\"forum.php?mod=post&action=edit&fid=$thread[fid]&tid=$thread[tid]&pid=$thread[pid]&page=1&modthreadkey=$thread[modthreadkey]\" target=\"_blank\">".$lang['moderate_edit_thread']."</a> &nbsp;&nbsp;|&nbsp;&nbsp; ".$lang['moderate_reasonpm']."&nbsp; <input type=\"text\" class=\"txt\" name=\"pm_$thread[tid]\" id=\"pm_$thread[tid]\" style=\"margin: 0px;\"> &nbsp; <select style=\"margin: 0px;\" onchange=\"$('pm_$thread[tid]').value=this.value\">$modreasonoptions</select>");
		showtagfooter('tbody');
	}

	showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a> &nbsp;<label><input class="checkbox" type="checkbox" name="apply_all" id="chk_apply_all"  value="1" disabled="disabled" />'.cplang('moderate_apply_all').'</label>', $multipage, false);
	showtablefooter();
	showformfooter();

} else {

	$validates = $ignores = $recycles = $deletes = 0;
	$validatedthreads = $pmlist = array();
	$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());

	if(is_array($moderate)) {
		foreach($moderate as $tid => $act) {
			$moderation[$act][] = intval($tid);
		}
	}

	if($_G['gp_apply_all']) {
		$apply_all_action = $_G['gp_apply_all'];
		$sqlwhere = '1';
		if($modfid > 0) {
			$sqlwhere .= " AND fid='$modfid'";
		}
		if($filter == 'ignore') {
			$sqlwhere .= " AND displayorder='-3'";
		} else {
			$sqlwhere .= " AND displayorder='-2'";
		}
		if($modfid == -1) {
			$sqlwhere .= " AND isgroup='1'";
		}
		if(!empty($_G['gp_dateline']) && $_G['gp_dateline'] != 'all') {
			$sqlwhere .= " AND dateline>'{$_G['gp_dateline']}'";
		}
		if(!empty($_G['gp_username'])) {
			$sqlwhere .= " AND author='{$_G['gp_username']}'";
		}
		if(!empty($_G['gp_title'])) {
			$title = str_replace(array('_', '%'), array('\_', '\%'), $_G['gp_title']);
			$sqlwhere .= " AND subject LIKE '%{$title}%'";
		}
		$query = DB::query("SELECT tid FROM ".DB::table('forum_thread')." WHERE $sqlwhere");
		while($thread = DB::fetch($query)) {
			switch($apply_all_action) {
				case 'validate':
					$moderation['validate'][] = $thread['tid'];
					break;
				case 'delete':
					$moderation['delete'][] = $thread['tid'];
					break;
				case 'ignore':
					$moderation['ignore'][] = $thread['tid'];
					break;
			}
		}
	}

	if($moderation['ignore']) {
		$ignoretids = '\''.implode('\',\'', $moderation['ignore']).'\'';
		DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='-3' WHERE tid IN ($ignoretids) AND displayorder='-2'");
		$ignores = DB::affected_rows();
		updatemoderate('tid', $moderation['ignore'], 1);
	}

	if($moderation['delete']) {
		$deletetids = array();
		$recyclebintids = '0';
		$query = DB::query("SELECT tid, fid, authorid, subject FROM ".DB::table('forum_thread')." t WHERE t.tid IN ('".implode('\',\'', $moderation['delete'])."') AND displayorder='$displayorder' $fidadd[and]$fidadd[fids]");
		while($thread = DB::fetch($query)) {
			my_thread_log('delete', array('tid' => $thread['tid']));
			if($recyclebins[$thread['fid']]) {
				$recyclebintids .= ','.$thread['tid'];
			} else {
				$deletetids[] = $thread['tid'];
			}
			$pm = 'pm_'.$thread['tid'];
			if(isset($_G['gp_'.$pm]) && $_G['gp_'.$pm] <> '' && $thread['authorid']) {
				$pmlist[] = array(
					'action' => 'modthreads_delete',
					'notevar' => array('threadsubject' => $thread['subject'], 'reason' => stripslashes($_G['gp_'.$pm])),
					'authorid' => $thread['authorid'],
				);
			}
		}

		if($recyclebintids) {
			DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='-1', moderated='1' WHERE tid IN ($recyclebintids)");
			$recycles = DB::affected_rows();
			updatemodworks('MOD', $recycles);

			updatepost(array('invisible' => '-1'), "tid IN ($recyclebintids)");
			updatemodlog($recyclebintids, 'DEL');
		}

		require_once libfile('function/delete');
		$deletes = deletethread($deletetids);
		updatemoderate('tid', $moderation['delete'], 2);
	}

	if($moderation['validate']) {
		require_once libfile('function/forum');
		$forums = array();
		$validatetids = '\''.implode('\',\'', $moderation['validate']).'\'';

		$tids = $authoridarray = $moderatedthread = array();
		$query = DB::query("SELECT t.fid, t.tid, t.authorid, t.subject, t.author, t.dateline, t.posttableid FROM ".DB::table('forum_thread')." t
			WHERE t.tid IN ($validatetids) $fidadd[and]$fidadd[t]$fidadd[fids]");
		while($thread = DB::fetch($query)) {
			$posttable = $thread['posttableid'] ? "forum_post_{$thread['posttableid']}" : 'forum_post';
			$poststatus = DB::result_first("SELECT status FROM ".DB::table($posttable)." WHERE tid='$thread[tid]' AND first='1'");
			$tids[] = $thread['tid'];
			my_thread_log('validate', array('tid' => $thread['tid']));
			if(getstatus($poststatus, 3) == 0) {
				updatepostcredits('+', $thread['authorid'], 'post', $thread['fid']);
				$attachcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table(getattachtablebytid($thread['tid']))." WHERE tid='$thread[tid]'");
				updatecreditbyaction('postattach', $thread['authorid'], array(), '', $attachcount, 1, $thread['fid']);
			}

			$forums[] = $thread['fid'];
			$validatedthreads[] = $thread;

			$pm = 'pm_'.$thread['tid'];
			if(isset($_G['gp_'.$pm]) && $_G['gp_'.$pm] <> '' && $thread['authorid']) {
				$pmlist[] = array(
					'action' => 'modthreads_validate',
					'notevar' => array('tid' => $thread['tid'], 'threadsubject' => $thread['subject'], 'reason' => dhtmlspecialchars($_G['gp_'.$pm])),
					'authorid' => $thread['authorid'],
				);
			}
		}

		if($tids) {

			$tidstr = dimplode($tids);
			$validates = DB::query("UPDATE ".DB::table(getposttable())." SET status='4' WHERE tid IN ($tidstr) AND status='0' AND invisible='-2'");
			updatepost(array('invisible' => '0'), "tid IN ($tidstr) AND first='1'");
			DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='0', moderated='1' WHERE tid IN ($tidstr)");
			$validates = DB::affected_rows();

			foreach(array_unique($forums) as $fid) {
				updateforumcount($fid);
			}

			updatemodworks('MOD', $validates);
			updatemodlog($tidstr, 'MOD');
			updatemoderate('tid', $tids, 2);

		}
	}

	if($pmlist) {
		foreach($pmlist as $pm) {
			notification_add($pm['authorid'], 'system', $pm['action'], $pm['notevar'], 1);
		}
	}
	if($_G['gp_fast']) {
		echo callback_js($_G['gp_tid']);
		exit;
	} else {
		cpmsg('moderate_threads_succeed', "action=moderate&operation=threads&page=$page&filter=$filter&modfid=$modfid&username={$_G['gp_username']}&title={$_G['gp_title']}&tpp={$_G['gp_tpp']}&showcensor=$showcensor&dateline={$_G['gp_dateline']}", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'recycles' => $recycles, 'deletes' => $deletes));
	}

}

?>