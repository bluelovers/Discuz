<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_picture.php 25729 2011-11-21 03:52:24Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('modsubmit') && !$_G['gp_fast']) {

	shownav('topic', $lang['moderate_pictures']);
	showsubmenu('nav_moderate_posts', $submenu);

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
	$pic_status = 1;
	if($_G['gp_filter'] == 'ignore') {
		$pic_status = 2;
	}
	showformheader("moderate&operation=pictures");
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
                        <select name=\"dateline\">$dateline_options</select>
                        <input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"
                )
        );

	showtablefooter();

	$pagetmp = $page;
	$sqlwhere = '';
	if(!empty($_G['gp_username'])) {
		$sqlwhere .= " AND p.username='{$_G['gp_username']}'";
	}
	if(!empty($dateline) && $dateline != 'all') {
		$sqlwhere .= " AND p.dateline>'".(TIMESTAMP - $dateline)."'";
	}
	if(!empty($_G['gp_title'])) {
		$sqlwhere .= " AND p.title LIKE '%{$_G['gp_title']}%'";
	}
	$modcount = DB::result_first("SELECT COUNT(*)
		FROM ".DB::table('common_moderate')." m
		LEFT JOIN ".DB::table('home_pic')." p ON p.picid=m.id
		WHERE m.idtype='picid' AND m.status='$moderatestatus' $sqlwhere");
	do {
		$start_limit = ($pagetmp - 1) * $tpp;
		$query = DB::query("SELECT p.picid, p.albumid, p.uid, p.username, p.title, p.dateline, p.filepath, p.thumb, p.remote, p.postip, a.albumname
			FROM ".DB::table('common_moderate')." m
			LEFT JOIN ".DB::table('home_pic')." p ON p.picid=m.id
			LEFT JOIN ".DB::table('home_album')." a ON p.albumid=a.albumid
			WHERE m.idtype='picid' AND m.status='$moderatestatus' $sqlwhere
			ORDER BY m.dateline DESC
			LIMIT $start_limit, $tpp");
			$pagetmp = $pagetmp - 1;
	} while($pagetmp > 0 && DB::num_rows($query) == 0);
	$page = $pagetmp + 1;
	$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=pictures&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&title={$_G['gp_title']}&tpp=$tpp&showcensor=$showcensor");

	echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';

	showtableheader();
	require_once libfile('class/censor');
	$censor = & discuz_censor::instance();
	$censor->highlight = '#FF0000';
	require_once libfile('function/misc');
	require_once libfile('function/home');
	while($pic = DB::fetch($query)) {
		$pic['dateline'] = dgmdate($pic['dateline']);
		$pic['title'] = $pic['title'] ? '<b>'.$pic['title'].'</b>' : '<i>'.$lang['nosubject'].'</i>';
		if($showcensor) {
			$censor->check($pic['title']);
		}
		$pic_censor_words = $censor->words_found;
		if(count($pic_censor_words) > 3) {
			$pic_censor_words = array_slice($pic_censor_words, 0, 3);
		}
		$pic['censorwords'] = implode(', ', $pic_censor_words);
		$pic['modpickey'] = modauthkey($pic['picid']);
		$pic['postip'] = $pic['postip'] . '-' . convertip($pic['postip']);
		$pic['url'] = pic_get($pic['filepath'], 'album', $pic['thumb'], $pic['remote']);

		if(count($pic_censor_words)) {
			$pic_censor_text = "<span style=\"color: red;\">({$pic['censorwords']})</span>";
		} else {
			$pic_censor_text = '';
		}
		showtagheader('tbody', '', true, 'hover');
		showtablerow("id=\"mod_$pic[picid]_row1\"", array("id=\"mod_$pic[picid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$pic[picid]]\" id=\"mod_$pic[picid]_1\" value=\"validate\" onclick=\"mod_setbg($pic[picid], 'validate');\"><label for=\"mod_$pic[picid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$pic[picid]]\" id=\"mod_$pic[picid]_2\" value=\"delete\" onclick=\"mod_setbg($pic[picid], 'delete');\"><label for=\"mod_$pic[picid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$pic[picid]]\" id=\"mod_$pic[picid]_3\" value=\"ignore\" onclick=\"mod_setbg($pic[picid], 'ignore');\"><label for=\"mod_$pic[picid]_3\">$lang[ignore]</label></li></ul>",
			"<h3><a href=\"javascript:;\" onclick=\"display_toggle('$pic[picid]');\">$pic[title]</a> $pic_censor_text</h3><p>$pic[postip]</p>",
			"<a target=\"_blank\" href=\"home.php?mod=space&uid=$pic[uid]&do=album&id=$pic[albumid]\">$pic[albumname]</a>",
			"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$pic[uid]&submit=yes\">$pic[username]</a></p> <p>$pic[dateline]</p>",
			"<a target=\"_blank\" href=\"home.php?mod=space&uid=$pic[uid]&do=album&picid=$pic[picid]&modpickey=$pic[modpickey]\">$lang[view]</a>",
		));
		showtablerow("id=\"mod_$pic[picid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;"><img src="'.$pic['url'].'" /></div>');
		showtablerow("id=\"mod_$pic[picid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=pictures&fast=1&picid=$pic[picid]&moderate[$pic[picid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=pictures&fast=1&picid=$pic[picid]&moderate[$pic[picid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=pictures&fast=1&picid=$pic[picid]&moderate[$pic[picid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");
		showtagfooter('tbody');
	}

	showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
	showtablefooter();
	showformfooter();

} else {

	$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
	$validates = $deletes = $ignores = 0;
	if(is_array($moderate)) {
		foreach($moderate as $picid => $act) {
			$moderation[$act][] = $picid;
		}
	}
	if($validate_picids = dimplode($moderation['validate'])) {
		DB::update('home_pic', array('status' => '0'), "picid IN ($validate_picids)");
		$validates = DB::affected_rows();
		foreach($moderation['validate'] as $picids) {
			$albumid = DB::result_first("SELECT albumid FROM ".DB::table('home_pic')." WHERE picid='$picids'");
			DB::query("UPDATE ".DB::table('home_album')." SET picnum=picnum+1 WHERE albumid='$albumid'", 'UNBUFFERED');
		}
		updatemoderate('picid', $moderation['validate'], 2);
	}

	if(!empty($moderation['delete'])) {
		require_once libfile('function/delete');
		$pics = deletepics($moderation['delete']);
		$deletes = count($pics);
		updatemoderate('picid', $moderation['delete'], 2);
	}

	if($ignore_picids = dimplode($moderation['ignore'])) {
		DB::update('home_pic', array('status' => '2'), "picid IN ($ignore_picids)");
		$ignores = DB::affected_rows();
		updatemoderate('picid', $moderation['ignore'], 1);
	}

	if($_G['gp_fast']) {
		echo callback_js($_G['gp_picid']);
		exit;
	} else {
		cpmsg('moderate_pictures_succeed', "action=moderate&operation=pictures&page=$page&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&title={$_G['gp_title']}&tpp={$_G['gp_tpp']}&showcensor=$showcensor", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'recycles' => $recycles, 'deletes' => $deletes));
	}

}

?>