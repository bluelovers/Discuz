<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_comment.php 24018 2011-08-22 02:28:39Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('modsubmit') && !$_G['gp_fast']) {

	shownav('topic', $lang['moderate_comments']);
	showsubmenu('nav_moderate_posts', $submenu);

	$select[$_G['gp_tpp']] = $_G['gp_tpp'] ? "selected='selected'" : '';
	$tpp_options = "<option value='20' $select[20]>20</option><option value='50' $select[50]>50</option><option value='100' $select[100]>100</option>";
	$tpp = !empty($_G['gp_tpp']) ? $_G['gp_tpp'] : '20';
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
	$idtype_select = '<option value="">'.$lang['all'].'</option>';
	foreach(array('uid', 'blogid', 'picid', 'sid') as $v) {
		$selected = '';
		if($_G['gp_idtype'] == $v) {
			$selected = 'selected="selected"';
		}
		$idtype_select .= "<option value=\"$v\" $selected>".$lang["comment_$v"]."</option>";
	}
	$comment_status = 1;
	if($_G['gp_filter'] == 'ignore') {
		$comment_status = 2;
	}
	showformheader("moderate&operation=comments");
	showtableheader('search');


	showtablerow('', array('width="60"', 'width="160"', 'width="60"'),
		array(
			cplang('username'), "<input size=\"15\" name=\"username\" type=\"text\" value=\"$_G[gp_username]\" />",
			cplang('moderate_content_keyword'), "<input size=\"15\" name=\"keyword\" type=\"text\" value=\"$_G[gp_keyword]\" />",
		)
	);
	showtablerow('', array('width="60"', 'width="160"', 'width="60"'),
                array(
                        "$lang[perpage]",
                        "<select name=\"tpp\">$tpp_options</select><label><input name=\"showcensor\" type=\"checkbox\" class=\"checkbox\" value=\"yes\" ".($showcensor ? ' checked="checked"' : '')."/> $lang[moderate_showcensor]</label>",
                        "$lang[moderate_bound]",
                        "<select name=\"filter\">$filteroptions</select>
			<select name=\"idtype\">$idtype_select</select>
                        <select name=\"dateline\">$dateline_options</select>
                        <input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"
                )
        );

	showtablefooter();

	$pagetmp = $page;
	$sqlwhere = '';
	if(!empty($_G['gp_idtype'])) {
		$mtype = " m.idtype='{$_G['gp_idtype']}_cid'";
	} else {
		$mtype = " m.idtype IN ('uid_cid', 'blogid_cid', 'picid_cid', 'sid_cid')";
	}
	if(!empty($_G['gp_username'])) {
		$sqlwhere .= " AND c.author='{$_G['gp_username']}'";
	}
	if(!empty($dateline) && $dateline != 'all') {
		$sqlwhere .= " AND c.dateline>'".(TIMESTAMP - $dateline)."'";
	}
	if(!empty($_G['gp_keyword'])) {
		$keyword = str_replace(array('_', '%'), array('\_', '\%'), $_G['gp_keyword']);
		$sqlwhere .= " AND c.message LIKE '%{$keyword}%'";
	}
	$modcount = DB::result_first("SELECT COUNT(*)
		FROM ".DB::table('common_moderate')." m
		LEFT JOIN ".DB::table('home_comment')." c ON c.cid=m.id
		WHERE $mtype AND m.status='$moderatestatus' $sqlwhere");
	do {
		$start_limit = ($pagetmp - 1) * $tpp;
		$query = DB::query("SELECT c.cid, c.uid, c.id, c.idtype, c.authorid, c.author, c.message, c.dateline, c.ip
			FROM ".DB::table('common_moderate')." m
			LEFT JOIN ".DB::table('home_comment')." c ON c.cid=m.id
			WHERE $mtype AND m.status='$moderatestatus' $sqlwhere
			ORDER BY c.dateline DESC
			LIMIT $start_limit, $tpp");
			$pagetmp = $pagetmp - 1;
	} while($pagetmp > 0 && DB::num_rows($query) == 0);
	$page = $pagetmp + 1;
	$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=comments&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&keyword={$_G['gp_keyword']}&idtype={$_G['gp_idtype']}&ppp=$tpp&showcensor=$showcensor");

	echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';

	showtableheader();
	require_once libfile('class/censor');
	$censor = & discuz_censor::instance();
	$censor->highlight = '#FF0000';
	require_once libfile('function/misc');
	while($comment = DB::fetch($query)) {
		$comment['dateline'] = dgmdate($comment['dateline']);
		$short_desc = cutstr($comment['message'], 75);
		if($showcensor) {
			$censor->check($short_desc);
			$censor->check($comment['message']);
		}
		$comment_censor_words = $censor->words_found;
		if(count($comment_censor_words) > 3) {
			$comment_censor_words = array_slice($comment_censor_words, 0, 3);
		}
		$comment['censorwords'] = implode(', ', $comment_censor_words);
		$comment['ip'] = $comment['ip'] . ' - ' . convertip($comment['ip']);
		$comment['modkey'] = modauthkey($comment['id']);
		$comment['modcommentkey'] = modauthkey($comment['cid']);

		if(count($comment_censor_words)) {
			$comment_censor_text = "<span style=\"color: red;\">({$comment['censorwords']})</span>";
		} else {
			$comment_censor_text = lang('admincp', 'no_censor_word');
		}
		$viewurl = '';
		$commenttype = '';
		$editurl = "home.php?mod=spacecp&ac=comment&op=edit&cid=$comment[cid]&modcommentkey=$comment[modcommentkey]";
		switch($comment['idtype']) {
			case 'uid':
				$commenttype = lang('admincp', 'comment_uid');
				$viewurl = "home.php?mod=space&uid=$comment[uid]&do=wall#comment_anchor_$comment[cid]";
				break;
			case 'blogid':
				$commenttype = lang('admincp', 'comment_blogid');
				$viewurl = "home.php?mod=space&uid=$comment[uid]&do=blog&id=$comment[id]&modblogkey=$comment[modkey]#comment_anchor_$comment[cid]";
				break;
			case 'picid':
				$commenttype = lang('admincp', 'comment_picid');
				$viewurl = "home.php?mod=space&uid=$comment[uid]&do=album&picid=$comment[id]&modpickey=$comment[modkey]#comment_anchor_$comment[cid]";
				break;
			case 'sid':
				$commenttype = lang('admincp', 'comment_sid');
				$viewurl = "home.php?mod=space&uid=$comment[uid]&do=share&id=$comment[id]#comment_anchor_$comment[cid]";
				break;
		}
		showtagheader('tbody', '', true, 'hover');
		showtablerow("id=\"mod_$comment[cid]_row1\"", array("id=\"mod_$comment[cid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"', 'width="55"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$comment[cid]]\" id=\"mod_$comment[cid]_1\" value=\"validate\" onclick=\"mod_setbg($comment[cid], 'validate');\"><label for=\"mod_$comment[cid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$comment[cid]]\" id=\"mod_$comment[cid]_2\" value=\"delete\" onclick=\"mod_setbg($comment[cid], 'delete');\"><label for=\"mod_$comment[cid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$comment[cid]]\" id=\"mod_$comment[cid]_3\" value=\"ignore\" onclick=\"mod_setbg($comment[cid], 'ignore');\"><label for=\"mod_$comment[cid]_3\">$lang[ignore]</label></li></ul>",
			"<h3><a href=\"javascript:;\" onclick=\"display_toggle({$comment[cid]});\"> $short_desc $comment_censor_text</a></h3><p>$comment[ip]</p>",
			$commenttype.'<input name="idtypes['.$comment['cid'].']" type="hidden" value="'.$comment['idtype'].'">',
			"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$comment[authorid]&submit=yes\">$comment[author]</a></p> <p>$comment[dateline]</p>",
			"<a target=\"_blank\" href=\"$viewurl\">$lang[view]</a>&nbsp;<a href=\"$editurl\" target=\"_blank\">$lang[edit]</a>",
		));

		showtablerow("id=\"mod_$comment[cid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$comment['message'].'</div>');
		showtablerow("id=\"mod_$comment[cid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=comments&fast=1&cid=$comment[cid]&moderate[$comment[cid]]=validate&idtypes[$comment[cid]]=$comment[idtype]&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=comments&fast=1&cid=$comment[cid]&moderate[$comment[cid]]=delete&idtypes[$comment[cid]]=$comment[idtype]&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=comments&fast=1&cid=$comment[cid]&moderate[$comment[cid]]=ignore&idtypes[$comment[cid]]=$comment[idtype]&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");

		showtagfooter('tbody');
	}

	showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
	showtablefooter();
	showformfooter();

} else {

	$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
	$validates = $deletes = $ignores = 0;
	$moderatedata = array();
	if(is_array($moderate)) {
		foreach($moderate as $cid => $act) {
			$moderation[$act][] = $cid;
			$moderatedata[$act][$_G['gp_idtypes'][$cid]][] = $cid;
		}
	}

	foreach($moderatedata as $act => $typeids) {
		foreach($typeids as $idtype => $ids) {
			$op = $act == 'ignore' ? 1 : 2;
			updatemoderate($idtype.'_cid', $ids, $op);
		}
	}

	if($validate_cids = dimplode($moderation['validate'])) {
		DB::update('home_comment', array('status' => '0'), "cid IN ($validate_cids)");
		$validates = DB::affected_rows();
	}
	if(!empty($moderation['delete'])) {
		require_once libfile('function/delete');
		$comments = deletecomments($moderation['delete']);
		$deletes = count($comments);
	}
	if($ignore_cids = dimplode($moderation['ignore'])) {
		DB::update('home_comment', array('status' => '2'), "cid IN ($ignore_cids)");
		$ignores = DB::affected_rows();
	}

	if($_G['gp_fast']) {
		echo callback_js($_G['gp_cid']);
		exit;
	} else {
		cpmsg('moderate_comments_succeed', "action=moderate&operation=comments&page=$page&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&keyword={$_G['gp_keyword']}&idtype={$_G['gp_idtype']}&tpp={$_G['gp_tpp']}&showcensor=$showcensor", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'deletes' => $deletes));
	}

}

?>