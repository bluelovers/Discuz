<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_portalcomment.php 24018 2011-08-22 02:28:39Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$idtype = $tablename = $mod = '';
if($operation == 'articlecomments') {
	$idtype = 'aid';
	$tablename = 'portal_article_title';
	$mod = 'view';
} else {
	$idtype = 'topicid';
	$tablename = 'portal_topic';
	$mod = 'topic';
}
if(!submitcheck('modsubmit') && !$_G['gp_fast']) {

	shownav('topic', $lang['moderate_articlecomments']);
	showsubmenu('nav_moderate_articlecomments', $submenu);

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
	$cat_select = '';
	if($operation == 'articlecomments') {
		$cat_select = '<option value="">'.$lang['all'].'</option>';
		$query = DB::query("SELECT catid, catname FROM ".DB::table('portal_category'));
		while($cat = DB::fetch($query)) {
			$selected = '';
			if($cat['catid'] == $_G['gp_catid']) {
				$selected = 'selected="selected"';
			}
			$cat_select .= "<option value=\"$cat[catid]\" $selected>$cat[catname]</option>";
		}
		$cat_select = "<select name=\"catid\">$cat_select</select>";
	}

	$articlecomment_status = 1;
	if($_G['gp_filter'] == 'ignore') {
		$articlecomment_status = 2;
	}
	showformheader("moderate&operation=$operation");
	showtableheader('search');

	if($operation == 'articlecomments') {
		showtablerow('', array('width="60"', 'width="160"', 'width="60"', 'width="200"', 'width="60"'),
			array(
				cplang('username'), "<input size=\"15\" name=\"username\" type=\"text\" value=\"$_G[gp_username]\" />",
				cplang('moderate_article_category'), $cat_select,
				cplang('moderate_content_keyword'), "<input size=\"15\" name=\"keyword\" type=\"text\" value=\"$_G[gp_keyword]\" />",
			)
		);
	} else {
		showtablerow('', array('width="60"', 'width="160"', 'width="60"'),
			array(
				cplang('username'), "<input size=\"15\" name=\"username\" type=\"text\" value=\"$_G[gp_username]\" />",
				cplang('moderate_content_keyword'), "<input size=\"15\" name=\"keyword\" type=\"text\" value=\"$_G[gp_keyword]\" />",
			)
		);
	}
	showtablerow('', $operation == 'articlecomments' ?
		array('width="60"', 'width="160"', 'width="60"', 'colspan="3"') :
		array('width="60"', 'width="160"', 'width="60"'),
                array(
                        "$lang[perpage]",
                        "<select name=\"tpp\">$tpp_options</select><label><input name=\"showcensor\" type=\"checkbox\" class=\"checkbox\" value=\"yes\" ".($showcensor ? ' checked="checked"' : '')."/> $lang[moderate_showcensor]</label>",
                        "$lang[moderate_bound]",
                        "<select name=\"filter\">$filteroptions</select>
                        <select name=\"dateline\">$dateline_options</select>
                        <input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"
                )
        );

	showtablefooter();

	$pagetmp = $page;
	$sqlwhere = "";
	if(!empty($_G['gp_catid']) && $idtype == 'aid') {
		$sqlwhere .= " AND a.catid='{$_G['gp_catid']}'";
	}
	if(!empty($_G['gp_username'])) {
		$sqlwhere .= " AND c.username='{$_G['gp_username']}'";
	}
	if($dateline != 'all') {
		$sqlwhere .= " AND c.dateline>'".(TIMESTAMP - $dateline)."'";
	}
	if(!empty($_G['gp_keyword'])) {
		$sqlwhere .= " AND c.message LIKE '%{$_G['gp_keyword']}%'";
	}
	$sqlwhere .=  "AND c.idtype='$idtype'";
	$modcount = DB::result_first("SELECT COUNT(*)
		FROM ".DB::table('common_moderate')." m
		LEFT JOIN ".DB::table('portal_comment')." c ON c.cid=m.id
		LEFT JOIN ".DB::table($tablename)." a ON a.$idtype=c.id
		WHERE m.idtype='{$idtype}_cid' AND m.status='$moderatestatus' $sqlwhere");
	do {
		$start_limit = ($pagetmp - 1) * $tpp;
		$query = DB::query("SELECT c.cid, c.uid, c.username, c.id, c.postip, c.dateline, c.message, a.title
			FROM ".DB::table('common_moderate')." m
			LEFT JOIN ".DB::table('portal_comment')." c ON c.cid=m.id
			LEFT JOIN ".DB::table($tablename)." a ON a.$idtype=c.id
			WHERE m.idtype='{$idtype}_cid' AND m.status='$moderatestatus' $sqlwhere
			ORDER BY m.dateline DESC
			LIMIT $start_limit, $tpp");
			$pagetmp = $pagetmp - 1;
	} while($pagetmp > 0 && DB::num_rows($query) == 0);
	$page = $pagetmp + 1;
	$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=$operation&filter=$filter&modfid=$modfid&ppp=$tpp&showcensor=$showcensor");

	echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';

	showtableheader();
	require_once libfile('class/censor');
	$censor = & discuz_censor::instance();
	$censor->highlight = '#FF0000';
	require_once libfile('function/misc');
	while($articlecomment = DB::fetch($query)) {
		$articlecomment['dateline'] = dgmdate($articlecomment['dateline']);
		if($showcensor) {
			$censor->check($articlecomment['title']);
			$censor->check($articlecomment['message']);
		}
		$articlecomment_censor_words = $censor->words_found;
		if(count($articlecomment_censor_words) > 3) {
			$articlecomment_censor_words = array_slice($articlecomment_censor_words, 0, 3);
		}
		$articlecomment['censorwords'] = implode(', ', $articlecomment_censor_words);
		$articlecomment['modarticlekey'] = modauthkey($articlecomment['aid']);
		$articlecomment['modarticlecommentkey'] = modauthkey($articlecomment['cid']);

		if(count($articlecomment_censor_words)) {
			$articlecomment_censor_text = "<span style=\"color: red;\">({$articlecomment['censorwords']})</span>";
		} else {
			$articlecomment_censor_text = '';
		}
		showtagheader('tbody', '', true, 'hover');
		showtablerow("id=\"mod_$articlecomment[cid]_row1\"", array("id=\"mod_$articlecomment[cid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="55"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$articlecomment[cid]]\" id=\"mod_$articlecomment[cid]_1\" value=\"validate\" onclick=\"mod_setbg($articlecomment[cid], 'validate');\"><label for=\"mod_$articlecomment[cid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$articlecomment[cid]]\" id=\"mod_$articlecomment[cid]_2\" value=\"delete\" onclick=\"mod_setbg($articlecomment[cid], 'delete');\"><label for=\"mod_$articlecomment[cid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$articlecomment[cid]]\" id=\"mod_$articlecomment[cid]_3\" value=\"ignore\" onclick=\"mod_setbg($articlecomment[cid], 'ignore');\"><label for=\"mod_$articlecomment[cid]_3\">$lang[ignore]</label></li></ul>",
			"<h3><a href=\"javascript:;\" onclick=\"display_toggle({$articlecomment[cid]});\">$articlecomment[title] $articlecomment_censor_text</a></h3>",
			"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$articlecomment[uid]&submit=yes\">$articlecomment[username]</a></p> <p>$articlecomment[dateline]</p>",
			"<a target=\"_blank\" href=\"portal.php?mod=$mod&$idtype=$articlecomment[id]&modarticlekey=$articlecomment[modarticlekey]#comment_anchor_{$articlecomment[cid]}\">$lang[view]</a>&nbsp;<a href=\"portal.php?mod=portalcp&ac=comment&op=edit&cid=$articlecomment[cid]&modarticlecommentkey=$articlecomment[modarticlecommentkey]\" target=\"_blank\">$lang[edit]</a>",
		));

		showtablerow("id=\"mod_$articlecomment[cid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$articlecomment['message'].'</div>');

		showtablerow("id=\"mod_$articlecomment[cid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=$operation&fast=1&cid=$articlecomment[cid]&moderate[$articlecomment[cid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=$operation&fast=1&cid=$articlecomment[cid]&moderate[$articlecomment[cid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=$operation&fast=1&cid=$articlecomment[cid]&moderate[$articlecomment[cid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");
		showtagfooter('tbody');
	}

	showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
	showtablefooter();
	showformfooter();

} else {

	$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
	$validates = $deletes = $ignores = 0;
	if(is_array($moderate)) {
		foreach($moderate as $cid => $act) {
			$moderation[$act][] = $cid;
		}
	}

	if($validate_cids = dimplode($moderation['validate'])) {
		DB::update('portal_comment', array('status' => '0'), "cid IN ($validate_cids)");
		$validates = DB::affected_rows();
		updatemoderate($idtype.'_cid', $moderation['validate'], 2);
	}
	if($delete_cids = dimplode($moderation['delete'])) {
		DB::delete('portal_comment', "cid IN ($delete_cids)");
		$deletes = DB::affected_rows();
		updatemoderate($idtype.'_cid', $moderation['delete'], 2);
	}
	if($ignore_cids = dimplode($moderation['ignore'])) {
		DB::update('portal_comment', array('status' => '2'), "cid IN ($ignore_cids)");
		$ignores = DB::affected_rows();
		updatemoderate($idtype.'_cid', $moderation['ignore'], 1);
	}

	if($_G['gp_fast']) {
		echo callback_js($_G['gp_cid']);
		exit;
	} else {
		cpmsg('moderate_'.$operation.'_succeed', "action=moderate&operation=$operation&page=$page&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&keyword={$_G['gp_keyword']}&catid={$_G['gp_catid']}&tpp={$_G['gp_tpp']}&showcensor=$showcensor", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'deletes' => $deletes));
	}

}

?>