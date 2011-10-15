<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_article.php 24018 2011-08-22 02:28:39Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('modsubmit') && !$_G['gp_fast']) {

	shownav('topic', $lang['moderate_articles']);
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
	$cat_select = '<option value="">'.$lang['all'].'</option>';
	$query = DB::query("SELECT catid, catname FROM ".DB::table('portal_category'));
	while($cat = DB::fetch($query)) {
		$selected = '';
		if($cat['catid'] == $_G['gp_catid']) {
			$selected = 'selected="selected"';
		}
		$cat_select .= "<option value=\"$cat[catid]\" $selected>$cat[catname]</option>";
	}
	$article_status = 1;
	if($_G['gp_filter'] == 'ignore') {
		$article_status = 2;
	}
	showformheader("moderate&operation=articles");
	showtableheader('search');

	showtablerow('', array('width="60"', 'width="160"', 'width="60"'),
		array(
			cplang('username'), "<input size=\"15\" name=\"username\" type=\"text\" value=\"$_G[gp_username]\" />",
			cplang('moderate_article_category'), "<select name=\"catid\">$cat_select</select>",
		)
	);
	showtablerow('', array('width="60"', 'width="160"', 'width="60"'),
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
	if(!empty($_G['gp_catid'])) {
		$sqlwhere .= " AND a.catid='{$_G['gp_catid']}'";
	}
	if(!empty($_G['gp_username'])) {
		$sqlwhere .= " AND a.username='{$_G['gp_username']}'";
	}
	if($dateline != 'all') {
		$sqlwhere .= " AND a.dateline>'".(TIMESTAMP - $dateline)."'";
	}

	$modcount = DB::result_first("SELECT COUNT(*)
		FROM ".DB::table('common_moderate')." m
		LEFT JOIN ".DB::table('portal_article_title')." a ON a.aid=m.id
		WHERE m.idtype='aid' AND m.status='$moderatestatus' $sqlwhere");
	do {
		$start_limit = ($pagetmp - 1) * $tpp;
		$query = DB::query("SELECT a.aid, a.catid, a.uid, a.username, a.title, a.summary, a.dateline, cat.catname
			FROM ".DB::table('common_moderate')." m
			LEFT JOIN ".DB::table('portal_article_title')." a ON a.aid=m.id
			LEFT JOIN ".DB::table('portal_category')." cat ON cat.catid=a.catid
			WHERE m.idtype='aid' AND m.status='$moderatestatus' $sqlwhere
			ORDER BY m.dateline DESC
			LIMIT $start_limit, $tpp");
			$pagetmp = $pagetmp - 1;
	} while($pagetmp > 0 && DB::num_rows($query) == 0);
	$page = $pagetmp + 1;
	$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=articles&filter=$filter&catid={$_G['gp_catid']}&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&keyword={$_G['gp_keyword']}&tpp=$tpp&showcensor=$showcensor");

	echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';

	showtableheader();
	require_once libfile('class/censor');
	$censor = & discuz_censor::instance();
	$censor->highlight = '#FF0000';
	require_once libfile('function/misc');
	while($article = DB::fetch($query)) {
		$article['dateline'] = dgmdate($article['dateline']);
		if($showcensor) {
			$censor->check($article['title']);
			$censor->check($article['summary']);
		}
		$article_censor_words = $censor->words_found;
		if(count($article_censor_words) > 3) {
			$article_censor_words = array_slice($article_censor_words, 0, 3);
		}
		$article['censorwords'] = implode(', ', $article_censor_words);
		$article['modarticlekey'] = modauthkey($article['aid']);

		if(count($article_censor_words)) {
			$article_censor_text = "<span style=\"color: red;\">({$article['censorwords']})</span>";
		} else {
			$article_censor_text = '';
		}
		showtagheader('tbody', '', true, 'hover');
		showtablerow("id=\"mod_$article[aid]_row1\"", array("id=\"mod_$article[aid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="55"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$article[aid]]\" id=\"mod_$article[aid]_1\" value=\"validate\" onclick=\"mod_setbg($article[aid], 'validate');\"><label for=\"mod_$article[aid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$article[aid]]\" id=\"mod_$article[aid]_2\" value=\"delete\" onclick=\"mod_setbg($article[aid], 'delete');\"><label for=\"mod_$article[aid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$article[aid]]\" id=\"mod_$article[aid]_3\" value=\"ignore\" onclick=\"mod_setbg($article[aid], 'ignore');\"><label for=\"mod_$article[aid]_3\">$lang[ignore]</label></li></ul>",
			"<h3><a href=\"javascript:;\" onclick=\"display_toggle({$article[aid]});\">$article[title] $article_censor_text</a></h3>",
			"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$article[uid]&submit=yes\">$article[username]</a></p> <p>$article[dateline]</p>",
			"<a target=\"_blank\" href=\"portal.php?mod=view&aid=$article[aid]&modarticlekey=$article[modarticlekey]\">$lang[view]</a>&nbsp;<a href=\"portal.php?mod=portalcp&ac=article&op=edit&aid=$article[aid]&modarticlekey=$article[modarticlekey]\" target=\"_blank\">$lang[edit]</a>",
		));

		showtablerow("id=\"mod_$article[aid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$article['summary'].'</div>');

		showtablerow("id=\"mod_$article[aid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=articles&fast=1&aid=$article[aid]&moderate[$article[aid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=articles&fast=1&aid=$article[aid]&moderate[$article[aid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=articles&fast=1&aid=$article[aid]&moderate[$article[aid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");
		showtagfooter('tbody');
	}

	showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
	showtablefooter();
	showformfooter();

} else {

	$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
	$validates = $deletes = $ignores = 0;
	if(is_array($moderate)) {
		foreach($moderate as $aid => $act) {
			$moderation[$act][] = $aid;
		}
	}

	if($validate_aids = dimplode($moderation['validate'])) {
		DB::update('portal_article_title', array('status' => '0'), "aid IN ($validate_aids)");
		$validates = DB::affected_rows();
		updatemoderate('aid', $moderation['validate'], 2);
	}
	if(!empty($moderation['delete'])) {
		require_once libfile('function/delete');
		$articles = deletearticle($moderation['delete']);
		$deletes = count($articles);
		updatemoderate('aid', $moderation['delete'], 2);
	}
	if($ignore_aids = dimplode($moderation['ignore'])) {
		DB::update('portal_article_title', array('status' => '2'), "aid IN ($ignore_aids)");
		$ignores = DB::affected_rows();
		updatemoderate('aid', $moderation['ignore'], 1);
	}
	if($_G['gp_fast']) {
		echo callback_js($_G['gp_aid']);
		exit;
	} else {
		cpmsg('moderate_articles_succeed', "action=moderate&operation=articles&page=$page&filter=$filter&catid={$_G['gp_catid']}&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&keyword={$_G['gp_keyword']}&idtype={$_G['gp_idtype']}&tpp={$_G['gp_tpp']}&showcensor=$showcensor", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'deletes' => $deletes));
	}

}

?>