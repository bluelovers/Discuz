<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_reply.php 24361 2011-09-14 08:40:47Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

loadcache('posttableids');
$posttable = in_array($_G['gp_posttableid'], $_G['cache']['posttableids']) ? $_G['gp_posttableid'] : 0;

if(!submitcheck('modsubmit') && !$_G['gp_fast']) {

	require_once libfile('function/discuzcode');

	$select[$_G['gp_ppp']] = $_G['gp_ppp'] ? "selected='selected'" : '';
	$ppp_options = "<option value='20' $select[20]>20</option><option value='50' $select[50]>50</option><option value='100' $select[100]>100</option>";
	$ppp = !empty($_G['gp_ppp']) ? $_G['gp_ppp'] : '20';
	$start_limit = ($page - 1) * $ppp;
	$dateline = $_G['gp_dateline'] ? $_G['gp_dateline'] : '604800';
	$dateline_options = '';
	foreach(array('all', '604800', '2592000', '7776000') as $v) {
		$selected = '';
		if($dateline == $v) {
			$selected = "selected='selected'";
		}
		$dateline_options .= "<option value=\"$v\" $selected>".cplang("dateline_$v");
	}

	$posttableselect = getposttableselect();

	shownav('topic', $lang['moderate_replies']);
	showsubmenu('nav_moderate_posts', $submenu);

	showformheader("moderate&operation=replies");
	showtableheader('search');

	showtablerow('', array('width="60"', 'width="160"', 'width="60"', $posttableselect ? 'width="160"' : '', $posttableselect ? 'width="60"' : ''),
		array(
			cplang('username'), "<input size=\"15\" name=\"username\" type=\"text\" value=\"$_G[gp_username]\" />",
			cplang('moderate_title_keyword'), "<input size=\"15\" name=\"title\" type=\"text\" value=\"$_G[gp_title]\" />",
			$posttableselect ? cplang('postsplit_select') : '',
			$posttableselect
		)
	);
	showtablerow('', array('width="60"', 'width="160"', 'width="60"', 'colspan="3"'),
                array(
                        "$lang[perpage]",
                        "<select name=\"ppp\">$ppp_options</select><label><input name=\"showcensor\" type=\"checkbox\" class=\"checkbox\" value=\"yes\" ".($showcensor ? ' checked="checked"' : '')."/> $lang[moderate_showcensor]</label>",
                        "$lang[moderate_bound]",
                        "<select name=\"filter\">$filteroptions</select>
                        <select name=\"modfid\">$forumoptions</select>
                        <select name=\"dateline\">$dateline_options</select>
                        <input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"
                )
        );

	showtablefooter();
	showtableheader();
	$fidadd = array();
	$sqlwhere = '';
	if(!empty($_G['gp_username'])) {
		$sqlwhere .= " AND p.author='{$_G['gp_username']}'";
	}
	if(!empty($dateline) && $dateline != 'all') {
		$sqlwhere .= " AND p.dateline>'".(TIMESTAMP - $dateline)."'";
	}
	if(!empty($_G['gp_title'])) {
		$sqlwhere .= " AND t.subject LIKE '%{$_G['gp_title']}%'";
	}
	if($modfid > 0) {
		$fidadd['and'] = ' AND';
		$fidadd['fids'] = " p.fid='$modfid'";
	}

	$modcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_moderate')." m
			LEFT JOIN ".DB::table(getposttable($posttable))." p on p.pid=m.id
			LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=p.tid WHERE m.idtype='pid' AND m.status='$moderatestatus' AND p.first='0' $fidadd[and]$fidadd[fids]".($modfid == -1 ? " AND t.isgroup='1'" : '')." $sqlwhere");
	if(empty($modcount) && $sqlwhere == '' && empty($fidadd) && empty($modfid)) {
		DB::delete('common_moderate', "status='$moderatestatus' AND idtype='pid'");
	}
	$start_limit = ($page - 1) * $ppp;
	$query = DB::query("SELECT f.name AS forumname, f.allowsmilies, f.allowhtml, f.allowbbcode, f.allowimgcode, p.pid, p.fid, p.tid, p.author, p.authorid, p.subject, p.dateline, p.message, p.useip, p.attachment, p.htmlon, p.smileyoff, p.bbcodeoff, t.subject AS tsubject
			FROM ".DB::table('common_moderate')." m
			LEFT JOIN ".DB::table(getposttable($posttable))." p on p.pid=m.id
			LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=p.tid LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=p.fid
			WHERE m.idtype='pid' AND m.status='$moderatestatus' AND p.first='0' $fidadd[and]$fidadd[fids]".($modfid ==-1 ? " AND t.isgroup='1'" : '')." $sqlwhere
			ORDER BY m.dateline DESC
			LIMIT $start_limit, $ppp");
	$multipage = multi($modcount, $ppp, $page, ADMINSCRIPT."?action=moderate&operation=replies&filter=$filter&modfid=$modfid&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&title={$_G['gp_title']}&ppp=$ppp&showcensor=$showcensor&posttableid=$posttable");

	echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a><p>';

	require_once libfile('class/censor');
	$censor = & discuz_censor::instance();
	$censor->highlight = '#FF0000';
	require_once libfile('function/misc');
	while($post = DB::fetch($query)) {
		$post['dateline'] = dgmdate($post['dateline']);
		$post['subject'] = $post['subject'] ? '<b>'.$post['subject'].'</b>' : '<i>'.$lang['nosubject'].'</i>';
		$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], sprintf('%00b', $post['htmlon']), $post['allowsmilies'], $post['allowbbcode'], $post['allowimgcode'], $post['allowhtml']);
		if($showcensor) {
			$censor->check($post['tsubject']);
			$censor->check($post['message']);
		}
		$post_censor_words = $censor->words_found;
		if(count($post_censor_words) > 3) {
			$post_censor_words = array_slice($post_censor_words, 0, 3);
		}
		$post['censorwords'] = implode(', ', $post_censor_words);
		$post['modthreadkey'] = modauthkey($post['tid']);
		$post['useip'] = $post['useip'] . '-' . convertip($post['useip']);

		if($post['attachment']) {
			require_once libfile('function/attachment');

			$queryattach = DB::query("SELECT aid, filename, filesize, attachment, isimage, remote FROM ".DB::table(getattachtablebytid($post['tid']))." WHERE pid='$post[pid]'");
			while($attach = DB::fetch($queryattach)) {
				$_G['setting']['attachurl'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
				$attach['url'] = $attach['isimage']
				 		? " $attach[filename] (".sizecount($attach['filesize']).")<br /><br /><img src=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" onload=\"if(this.width > 400) {this.resized=true; this.width=400;}\">"
					 	 : "<a href=\"".$_G['setting']['attachurl']."forum/$attach[attachment]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
				$post['message'] .= "<br /><br />$lang[attachment]: ".attachtype(fileext($attach['filename'])).$attach['url'];
			}
		}

		if(count($post_censor_words)) {
			$post_censor_text = "<span style=\"color: red;\">({$post['censorwords']})</span>";
		} else {
			$post_censor_text = '';
		}
		showtagheader('tbody', '', true, 'hover');
		showtablerow("id=\"mod_$post[pid]_row1\"", array("id=\"mod_$post[pid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_1\" value=\"validate\" onclick=\"mod_setbg($post[pid], 'validate');\"><label for=\"mod_$post[pid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_2\" value=\"delete\" onclick=\"mod_setbg($post[pid], 'delete');\"><label for=\"mod_$post[pid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$post[pid]]\" id=\"mod_$post[pid]_3\" value=\"ignore\" onclick=\"mod_setbg($post[pid], 'ignore');\"><label for=\"mod_$post[pid]_3\">$lang[ignore]</label></li></ul>",
			"<h3><a href=\"javascript:;\" onclick=\"display_toggle('$post[pid]');\">$post[tsubject]</a> $post_censor_text</h3><p>$post[useip]</p>",
			"<a href=\"forum.php?mod=forumdisplay&fid=$post[fid]\">$post[forumname]</a>",
			"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$post[authorid]&submit=yes\">$post[author]</a></p> <p>$post[dateline]</p>",
			"<a target=\"_blank\" href=\"forum.php?mod=redirect&goto=findpost&ptid=$post[tid]&pid=$post[pid]\">$lang[view]</a>&nbsp;<a href=\"forum.php?mod=viewthread&tid=$post[tid]&modthreadkey=$post[modthreadkey]\" target=\"_blank\">$lang[edit]</a>",
		));
		showtablerow("id=\"mod_$post[pid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$post['message'].'</div>');
		showtablerow("id=\"mod_$post[pid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=replies&fast=1&fid=$post[fid]&tid=$post[tid]&pid=$post[pid]&moderate[$post[pid]]=validate&page=$page&posttableid=$posttable&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=replies&fast=1&fid=$post[fid]&tid=$post[tid]&pid=$post[pid]&moderate[$post[pid]]=delete&page=$page&posttableid=$posttable&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=replies&fast=1&fid=$post[fid]&tid=$post[tid]&pid=$post[pid]&moderate[$post[pid]]=ignore&page=$page&posttableid=$posttable&frame=no\" target=\"fasthandle\">$lang[ignore]</a>&nbsp;&nbsp;|&nbsp;&nbsp; ".$lang['moderate_reasonpm']."&nbsp; <input type=\"text\" class=\"txt\" name=\"pm_$post[pid]\" id=\"pm_$post[pid]\" style=\"margin: 0px;\"> &nbsp; <select style=\"margin: 0px;\" onchange=\"$('pm_$post[pid]').value=this.value\">$modreasonoptions</select>");
		showtagfooter('tbody');

	}

	showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a> &nbsp;<label><input class="checkbox" type="checkbox" name="apply_all" id="chk_apply_all"  value="1" disabled="disabled" />'.cplang('moderate_apply_all').'</label>', $multipage, false);
	showtablefooter();
	showformfooter();

} else {

	$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
	$pmlist = array();
	$validates = $ignores = $deletes = 0;

	if(is_array($moderate)) {
		foreach($moderate as $pid => $act) {
			$moderation[$act][] = intval($pid);
		}
	}

	if($_G['gp_apply_all']) {
		$apply_all_action = $_G['gp_apply_all'];
		$sqlwhere = "p.first='0'";
		if($filter == 'ignore') {
			$sqlwhere .= " AND p.invisible='-3'";
		} else {
			$sqlwhere .= " AND p.invisible='-2'";
		}
		if($modfid > 0) {
			$sqlwhere .= " AND p.fid='$modfid'";
		}
		if($modfid == -1) {
			$sqlwhere .= " AND t.isgroup='1'";
		}
		if(!empty($_G['gp_dateline']) && $_G['gp_dateline'] != 'all') {
			$sqlwhere .= " AND p.dateline>'{$_G['gp_dateline']}'";
		}
		if(!empty($_G['gp_username'])) {
			$sqlwhere .= " AND p.author='{$_G['gp_username']}'";
		}
		if(!empty($_G['gp_title'])) {
			$title = str_replace(array('_', '\%'), array('\_', '\%'), $_G['gp_title']);
			$sqlwhere .= " AND t.subject LIKE '%{$title}%'";
		}
		$query = DB::query("SELECT p.pid FROM ".DB::table(getposttable($posttable))." p LEFT JOIN ".DB::table('forum_thread')." t ON p.tid=t.tid WHERE $sqlwhere");
		while($post = DB::fetch($query)) {
			switch($apply_all_action) {
				case 'validate':
					$moderation['validate'][] = $post['pid'];
					break;
				case 'delete':
					$moderation['delete'][] = $post['pid'];
					break;
				case 'ignore':
					$moderation['ignore'][] = $post['pid'];
					break;
			}
		}
	}
	if($ignorepids = dimplode($moderation['ignore'])) {
		DB::query("UPDATE ".DB::table(getposttable($posttable))." SET invisible='-3' WHERE pid IN ($ignorepids) AND invisible='-2' AND first='0' $fidadd[and]$fidadd[fids]");
		$ignores = DB::affected_rows();
		updatemoderate('pid', $moderation['ignore'], 1);
	}

	if($deletepids = dimplode($moderation['delete'])) {
		$pids = $recyclebinpids = array();
		$query = DB::query('SELECT pid, authorid, tid, fid, message FROM '.DB::table(getposttable($posttable))." WHERE pid IN ($deletepids) AND invisible='$displayorder' AND first='0' $fidadd[and]$fidadd[fids]");
		while($post = DB::fetch($query)) {
			if($recyclebins[$post['fid']]) {
				$recyclebinpids[] = $post['pid'];
			} else {
				$pids[] = $post['pid'];
			}
			$pm = 'pm_'.$post['pid'];
			if(isset($_G['gp_'.$pm]) && $_G['gp_'.$pm] <> '' && $post['authorid']) {
				$pmlist[] = array(
					'action' => 'modreplies_delete',
					'notevar' => array('pid' => $post['pid'], 'post' => dhtmlspecialchars(cutstr($post['message'], 30)), 'reason' => dhtmlspecialchars($_G['gp_'.$pm])),
					'authorid' => $post['authorid'],
				);
			}
		}

		if($recyclebinpids) {
			DB::query("UPDATE ".DB::table(getposttable($posttable))." SET invisible='-5' WHERE pid IN (".dimplode($recyclebinpids).")");
		}

		if($pids) {
			require_once libfile('function/delete');
			$deletes = deletepost($pids, 'pid', false, $posttable);
		}
		$deletes += count($recyclebinpids);
		updatemodworks('DLP', count($moderation['delete']));
		updatemoderate('pid', $moderation['delete'], 2);
	}

	if($validatepids = dimplode($moderation['validate'])) {
		require_once libfile('function/forum');
		$forums = $threads = $lastpost = $attachments = $pidarray = $authoridarray = array();
		$query = DB::query("SELECT t.lastpost, p.pid, p.fid, p.tid, p.authorid, p.author, p.dateline, p.attachment, p.message, p.anonymous, p.status
			FROM ".DB::table(getposttable($posttable))." p LEFT JOIN ".DB::table('forum_thread')." t ON t.tid=p.tid
			WHERE pid IN ($validatepids)  AND first='0'");
		while($post = DB::fetch($query)) {
			$pidarray[] = $post['pid'];
			my_post_log('validate', array('pid' => $post['pid']));
			if(getstatus($post['status'], 3) == 0) {
				updatepostcredits('+', $post['authorid'], 'reply', $post['fid']);
				$attachcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table(getattachtablebytid($post['tid']))." WHERE pid='$post[pid]'");
				updatecreditbyaction('postattach', $post['authorid'], array(), '', $attachcount, 1, $post['fid']);
			}

			$forums[] = $post['fid'];

			$threads[$post['tid']]['posts']++;
			$threads[$post['tid']]['lastpostadd'] = $post['dateline'] > $post['lastpost'] && $post['dateline'] > $lastpost[$post['tid']] ?
				", lastpost='$post[dateline]', lastposter='".($post['anonymous'] && $post['dateline'] != $post['lastpost'] ? '' : addslashes($post[author]))."'" : '';
			$threads[$post['tid']]['attachadd'] = $threads[$post['tid']]['attachadd'] || $post['attachment'] ? ', attachment=\'1\'' : '';

			$pm = 'pm_'.$post['pid'];
			if(isset($_G['gp_'.$pm]) && $_G['gp_'.$pm] <> '' && $post['authorid']) {
				$pmlist[] = array(
					'action' => 'modreplies_validate',
					'notevar' => array('pid' => $post['pid'], 'tid' => $post['tid'], 'post' => dhtmlspecialchars(cutstr($post['message'], 30)), 'reason' => dhtmlspecialchars($_G['gp_'.$pm])),
					'authorid' => $post['authorid'],
				);
			}
		}

		foreach($threads as $tid => $thread) {
			DB::query("UPDATE ".DB::table('forum_thread')." SET replies=replies+$thread[posts] $thread[lastpostadd] $thread[attachadd] WHERE tid='$tid'", 'UNBUFFERED');
		}

		foreach(array_unique($forums) as $fid) {
			updateforumcount($fid);
		}

		if(!empty($pidarray)) {
			DB::query("UPDATE ".DB::table(getposttable($posttable))." SET status='4' WHERE pid IN (0,".implode(',', $pidarray).") AND status='0' AND invisible='-2'");
			DB::query("UPDATE ".DB::table(getposttable($posttable))." SET invisible='0' WHERE pid IN (0,".implode(',', $pidarray).")");
			$validates = DB::affected_rows();
			updatemodworks('MOD', $validates);
			updatemoderate('pid', $pidarray, 2);
		} else {
			updatemodworks('MOD', 1);
		}
	}

	if($pmlist) {
		foreach($pmlist as $pm) {
			notification_add($pm['authorid'], 'system', $pm['action'], $pm['notevar'], 1);
		}
	}
	if($_G['gp_fast']) {
		echo callback_js($_G['gp_pid']);
		exit;
	} else {
		cpmsg('moderate_replies_succeed', "action=moderate&operation=replies&page=$page&filter=$filter&modfid=$modfid&posttableid=$posttable&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&title={$_G['gp_title']}&ppp={$_G['gp_ppp']}&showcensor=$showcensor", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'recycles' => $recycles, 'deletes' => $deletes));
	}

}

?>