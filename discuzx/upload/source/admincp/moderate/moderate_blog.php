<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_blog.php 24018 2011-08-22 02:28:39Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('modsubmit') && !$_G['gp_fast']) {

	require_once libfile('function/discuzcode');

	shownav('topic', $lang['moderate_blogs']);
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
	$blog_status = 1;
	if($_G['gp_filter'] == 'ignore') {
		$blog_status = 2;
	}
	showformheader("moderate&operation=blogs");
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
		$sqlwhere .= " AND b.username='{$_G['gp_username']}'";
	}
	if(!empty($dateline) && $dateline != 'all') {
		$sqlwhere .= " AND b.dateline>'".(TIMESTAMP - $dateline)."'";
	}
	if(!empty($_G['gp_title'])) {
		$sqlwhere .= " AND b.subject LIKE '%{$_G['gp_title']}%'";
	}
	$modcount = DB::result_first("SELECT COUNT(*)
		FROM ".DB::table('common_moderate')." m
		LEFT JOIN ".DB::table('home_blog')." b ON b.blogid=m.id
		LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid
		LEFT JOIN ".DB::table('home_class')." c ON b.classid=c.classid
		WHERE m.idtype='blogid' AND m.status='$moderatestatus' $sqlwhere");
	do {
		$start_limit = ($pagetmp - 1) * $tpp;
		$query = DB::query("SELECT b.blogid, b.uid, b.username, b.classid, b.subject, b.dateline, bf.message, bf.postip, c.classname
			FROM ".DB::table('common_moderate')." m
			LEFT JOIN ".DB::table('home_blog')." b ON b.blogid=m.id
			LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid
			LEFT JOIN ".DB::table('home_class')." c ON b.classid=c.classid
			WHERE m.idtype='blogid' AND m.status='$moderatestatus' $sqlwhere
			ORDER BY m.dateline DESC
			LIMIT $start_limit, $tpp");
			$pagetmp = $pagetmp - 1;
	} while($pagetmp > 0 && DB::num_rows($query) == 0);
	$page = $pagetmp + 1;
	$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=blogs&filter=$filter&modfid=$modfid&ppp=$tpp&showcensor=$showcensor");

	echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';

	showtableheader();
	require_once libfile('class/censor');
	$censor = & discuz_censor::instance();
	$censor->highlight = '#FF0000';
	require_once libfile('function/misc');
	while($blog = DB::fetch($query)) {
		$blog['dateline'] = dgmdate($blog['dateline']);
		$blog['subject'] = $blog['subject'] ? '<b>'.$blog['subject'].'</b>' : '<i>'.$lang['nosubject'].'</i>';
		if($showcensor) {
			$censor->check($blog['subject']);
			$censor->check($blog['message']);
		}
		$blog_censor_words = $censor->words_found;
		if(count($post_censor_words) > 3) {
			$blog_censor_words = array_slice($blog_censor_words, 0, 3);
		}
		$blog['censorwords'] = implode(', ', $blog_censor_words);
		$blog['modblogkey'] = modauthkey($blog['blogid']);
		$blog['postip'] = $blog['postip'] . '-' . convertip($blog['postip']);

		if(count($blog_censor_words)) {
			$blog_censor_text = "<span style=\"color: red;\">({$blog['censorwords']})</span>";
		} else {
			$blog_censor_text = '';
		}
		showtagheader('tbody', '', true, 'hover');
		showtablerow("id=\"mod_$blog[blogid]_row1\"", array("id=\"mod_$blog[blogid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$blog[blogid]]\" id=\"mod_$blog[blogid]_1\" value=\"validate\" onclick=\"mod_setbg($blog[blogid], 'validate');\"><label for=\"mod_$blog[blogid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$blog[blogid]]\" id=\"mod_$blog[blogid]_2\" value=\"delete\" onclick=\"mod_setbg($blog[blogid], 'delete');\"><label for=\"mod_$blog[blogid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$blog[blogid]]\" id=\"mod_$blog[blogid]_3\" value=\"ignore\" onclick=\"mod_setbg($blog[blogid], 'ignore');\"><label for=\"mod_$blog[blogid]_3\">$lang[ignore]</label></li></ul>",
			"<h3><a href=\"javascript:;\" onclick=\"display_toggle('$blog[blogid]');\">$blog[subject]</a> $blog_censor_text</h3><p>$blog[postip]</p>",
			$blog[classname],
			"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$blog[uid]&submit=yes\">$blog[username]</a></p> <p>$blog[dateline]</p>",
			"<a href=\"home.php?mod=space&uid=$blog[uid]&do=blog&id=$blog[blogid]&modblogkey=$blog[modblogkey]\" target=\"_blank\">$lang[view]</a>&nbsp;<a href=\"home.php?mod=spacecp&ac=blog&blogid=$blog[blogid]&modblogkey=$blog[modblogkey]\" target=\"_blank\">$lang[edit]</a>",
		));
		showtablerow("id=\"mod_$blog[blogid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$blog['message'].'</div>');
		showtablerow("id=\"mod_$blog[blogid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=blogs&fast=1&blogid=$blog[blogid]&moderate[$blog[blogid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=blogs&fast=1&blogid=$blog[blogid]&moderate[$blog[blogid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=blogs&fast=1&blogid=$blog[blogid]&moderate[$blog[blogid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");
		showtagfooter('tbody');
	}

	showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
	showtablefooter();
	showformfooter();

} else {

	$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
	$validates = $deletes = $ignores = 0;
	if(is_array($moderate)) {
		foreach($moderate as $blogid => $act) {
			$moderate[$act][] = $blogid;
		}
	}

	if($validate_blogids = dimplode($moderate['validate'])) {
		DB::update('home_blog', array('status' => '0'), "blogid IN ($validate_blogids)");
		$validates = DB::affected_rows();
		$query_t = DB::query("SELECT uid, COUNT(blogid) AS count
			FROM ".DB::table('home_blog')."
			WHERE blogid IN ($validate_blogids)
			GROUP BY uid");
		while($blog_user = DB::fetch($query_t)) {
			$credit_times = $blog_user['count'];
			updatecreditbyaction('publishblog', $blog_user['uid'], array('blogs' => 1), '', $credit_times);
		}
		updatemoderate('blogid', $moderate['validate'], 2);
	}

	if($moderate['delete']) {
		require_once libfile('function/delete');
		$delete_blogs = deleteblogs($moderate['delete']);
		$deletes = count($delete_blogs);
		updatemoderate('blogid', $moderate['delete'], 2);
	}

	if($ignore_blogids = dimplode($moderate['ignore'])) {
		DB::update('home_blog', array('status' => '2'), "blogid IN ($ignore_blogids)");
		$ignores = DB::affected_rows();
		updatemoderate('blogid', $moderate['ignore'], 1);
	}

	if($_G['gp_fast']) {
		echo callback_js($_G['gp_blogid']);
		exit;
	} else {
		cpmsg('moderate_blogs_succeed', "action=moderate&operation=blogs&page=$page&filter=$filter&dateline={$_G['gp_dateline']}&username={$_G['gp_username']}&title={$_G['gp_title']}&tpp={$_G['gp_tpp']}&showcensor=$showcensor", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'recycles' => $recycles, 'deletes' => $deletes));
	}

}

?>