<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_doing.php 24018 2011-08-22 02:28:39Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('modsubmit') && !$_G['gp_fast']) {

	shownav('topic', $lang['moderate_doings']);
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
	$doing_status = 1;
	if($_G['gp_filter'] == 'ignore') {
		$doing_status = 2;
	}
	showformheader("moderate&operation=doings");
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
                        <select name=\"dateline\">$dateline_options</select>
                        <input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"
                )
        );

	showtablefooter();

	$pagetmp = $page;
	$sqlwhere = '';
	if(!empty($_G['gp_username'])) {
		$sqlwhere .= " AND d.username='{$_G['gp_username']}'";
	}
	if(!empty($dateline) && $dateline != 'all') {
		$sqlwhere .= " AND d.dateline>'".(TIMESTAMP - $dateline)."'";
	}
	if(!empty($_G['gp_keyword'])) {
		$keyword = str_replace(array('_', '%'), array('\_', '\%'), $_G['gp_keyword']);
		$sqlwhere .= " AND d.message LIKE '%$keyword%'";
	}
	$modcount = DB::result_first("SELECT COUNT(*)
		FROM ".DB::table('common_moderate')." m
		LEFT JOIN ".DB::table('home_doing')." d ON d.doid=m.id
		WHERE m.idtype='doid' AND m.status='$moderatestatus' $sqlwhere");
	do {
		$start_limit = ($pagetmp - 1) * $tpp;
		$query = DB::query("SELECT d.doid, d.uid, d.username, d.dateline, d.message, d.ip
			FROM ".DB::table('common_moderate')." m
			LEFT JOIN ".DB::table('home_doing')." d ON d.doid=m.id
			WHERE m.idtype='doid' AND m.status='$moderatestatus' $sqlwhere
			ORDER BY m.dateline DESC
			LIMIT $start_limit, $tpp");
			$pagetmp = $pagetmp - 1;
	} while($pagetmp > 0 && DB::num_rows($query) == 0);
	$page = $pagetmp + 1;
	$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=doings&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&keyword={$_G['gp_keyword']}&tpp=$tpp&showcensor=$showcensor");

	echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';

	showtableheader();
	require_once libfile('class/censor');
	$censor = & discuz_censor::instance();
	$censor->highlight = '#FF0000';
	require_once libfile('function/misc');
	while($doing = DB::fetch($query)) {
		$doing['dateline'] = dgmdate($doing['dateline']);
		$short_desc = cutstr($doing['message'], 75);
		if($showcensor) {
			$censor->check($short_desc);
			$censor->check($doing['message']);
		}
		$doing_censor_words = $censor->words_found;
		if(count($post_censor_words) > 3) {
			$doing_censor_words = array_slice($doing_censor_words, 0, 3);
		}
		$doing['censorwords'] = implode(', ', $doing_censor_words);
		$doing['ip'] = $doing['ip'] . '-' . convertip($doing['ip']);

		if(count($doing_censor_words)) {
			$doing_censor_text = "<span style=\"color: red;\">({$doing['censorwords']})</span>";
		} else {
			$doing_censor_text = '';
		}
		showtagheader('tbody', '', true, 'hover');
		showtablerow("id=\"mod_$doing[doid]_row1\"", array("id=\"mod_$doing[doid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$doing[doid]]\" id=\"mod_$doing[doid]_1\" value=\"validate\" onclick=\"mod_setbg($doing[doid], 'validate');\"><label for=\"mod_$doing[doid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$doing[doid]]\" id=\"mod_$doing[doid]_2\" value=\"delete\" onclick=\"mod_setbg($doing[doid], 'delete');\"><label for=\"mod_$doing[doid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$doing[doid]]\" id=\"mod_$doing[doid]_3\" value=\"ignore\" onclick=\"mod_setbg($doing[doid], 'ignore');\"><label for=\"mod_$doing[doid]_3\">$lang[ignore]</label></li></ul>",
			"<h3><a href=\"javascript:;\" onclick=\"display_toggle({$doing[doid]});\">$short_desc $doing_censor_text</a></h3><p>$doing[ip]</p>",
			"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$doing[uid]&submit=yes\">$doing[username]</a></p> <p>$doing[dateline]</p>",
		));



		showtablerow("id=\"mod_$doing[doid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$doing['message'].'</div>');



		showtablerow("id=\"mod_$doing[doid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=doings&fast=1&doid=$doing[doid]&moderate[$doing[doid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=doings&fast=1&doid=$doing[doid]&moderate[$doing[doid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=doings&fast=1&doid=$doing[doid]&moderate[$doing[doid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");
		showtagfooter('tbody');
	}

	showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
	showtablefooter();
	showformfooter();

} else {

	$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
	$validates = $deletes = $ignores = 0;
	if(is_array($moderate)) {
		foreach($moderate as $doid => $act) {
			$moderation[$act][] = $doid;
		}
	}
	if($validate_doids = dimplode($moderation['validate'])) {
		DB::update('home_doing', array('status' => '0'), "doid IN ($validate_doids)");
		$query_t = DB::query("SELECT * FROM ".DB::table('home_doing')." WHERE doid IN ($validate_doids)");
		while($doing = DB::fetch($query_t)) {
			$feedarr = array(
				'appid' => '',
				'icon' => 'doing',
				'uid' => $doing['uid'],
				'username' => $doing['username'],
				'dateline' => $doing['dateline'],
				'title_template' => lang('feed', 'feed_doing_title'),
				'title_data' => daddslashes(serialize(dstripslashes(array('message'=>$doing['message'])))),
				'body_template' => '',
				'body_data' => '',
				'id' => $doing['doid'],
				'idtype' => 'doid'
			);
			DB::insert('home_feed', $feedarr);
		}
		$validates = DB::affected_rows();
		updatemoderate('doid', $moderation['validate'], 2);
	}
	if(!empty($moderation['delete'])) {
		require_once libfile('function/delete');
		$doings = deletedoings($moderation['delete']);
		$deletes = count($doings);
		updatemoderate('doid', $moderation['delete'], 2);
	}
	if($ignore_doids = dimplode($moderation['ignore'])) {
		DB::update('home_doing', array('status' => '2'), "doid IN ($ignore_doids)");
		$ignores = DB::affected_rows();
		updatemoderate('doid', $moderation['ignore'], 1);
	}

	if($_G['gp_fast']) {
		echo callback_js($_G['gp_doid']);
		exit;
	} else {
		cpmsg('moderate_doings_succeed', "action=moderate&operation=doings&page=$page&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&keyword={$_G['gp_keyword']}&tpp={$_G['gp_tpp']}&showcensor=$showcensor", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'deletes' => $deletes));
	}

}

?>