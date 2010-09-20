<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: commentmodel.inc.php 4351 2010-09-06 12:21:08Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

require_once(B_ROOT.'./source/adminfunc/tool.func.php');

$commentmodelstr = '';
$commentmodel = $commentmodelarr = array();
$checkresults = array();

if(!empty($_POST['deletesubmit']) && $_POST['operation'] == "delete") {

	if(!empty($_POST['cmids'])) {
		$cmid = implode(',', $_POST['cmids']);
		DB::query("DELETE FROM ".tname('commentmodels')." WHERE cmid IN ($cmid)");
		cpmsg('message_success', 'admin.php?action=commentmodel');
	} else {
		cpmsg('no_item', 'admin.php?action=commentmodel');
	}
}

if(($_GET['op'] == 'edit' || $_GET['op'] == 'add') && !empty($_POST['valuesubmit'])) {

	$scorename = '';
	$scorearr = array();
	$_POST['modelname'] = trim($_POST['modelname']);
	if(empty($_POST['modelname']) || bstrlen($_POST['modelname']) > 10) {
		array_push($checkresults, array('modelname'=>lang('commentmodel_modelname_length_error')));
	}
	$notfillednum = $fillednum = 0;
	for($i = 1; $i <= 8; $i++) {
		$_POST['score'.$i] = trim($_POST['score'.$i]);
		if(!empty($_POST['score'.$i])) {
			if(bstrlen($_POST['score'.$i]) > 10) {
				array_push($checkresults, array('score'.$i=>lang('commentmodel_modelname_length_error')));
			}
			$scorearr[$i] = $_POST['score'.$i];
			$fillednum++;
		} else {
			$notfillednum++;
		}
	}
	if($notfillednum == 8) {
		cpmsg('commentmodel_score_notwrite');
	}
	if(!empty($checkresults)) {
		cpmsg('add_error', '', 'error', '', true, true, $checkresults);
	}
	$scorename = serialize($scorearr);
	DB::query("REPLACE INTO ".tname('commentmodels')." (cmid, modelname, scorenum, scorename, dateline) VALUES ('$_POST[cmid]', '$_POST[modelname]', '$fillednum', '$scorename', '$_G[timestamp]');");

	cpmsg('message_success', 'admin.php?action=commentmodel');

} elseif($_GET['op'] == 'add' || $_GET['op'] == 'edit') {

	if($_GET['op'] == 'edit') {
		$commentmodelarr = DB::fetch(DB::query("SELECT * FROM ".tname('commentmodels')." WHERE cmid='$_GET[cmid]'"));
		$commentmodelarr['scorename'] = unserialize($commentmodelarr['scorename']);
		foreach($commentmodelarr['scorename'] as $key => $commentmodel) {
			$commentmodelarr['score'.$key] = $commentmodel;
		}
	}
	shownav('global', 'commentmodel_'.$_GET['op']);
	showsubmenu('commentmodel_add', array(
		array('nav_commentmodel', 'commentmodel', '0'),
		array('commentmodel_add', 'commentmodel&op=add', '1')
	));
	showtips('commentmodel_'.$_GET['op'].'_tips');
	showformheader('commentmodel&op='.$_GET['op']);
	showtableheader('');
	showsetting('commentmodel_name', 'modelname', $commentmodelarr['modelname'], 'text');
	for($i = 1; $i <= 8; $i++) {
		showsetting('commentmodel_score'.$i, 'score'.$i, $commentmodelarr['score'.$i], 'text');
	}
	showhiddenfields(array('cmid' => $_GET['cmid']));
	showsubmit('valuesubmit');
	showtablefooter();
	showformfooter();
	bind_ajax_form();

} else {

	shownav('global', 'commentmodel_list');
	showsubmenu('nav_commentmodel', array(
		array('nav_commentmodel', 'commentmodel', '1'),
		array('commentmodel_add', 'commentmodel&op=add', '0')
	));
	showtips('commentmodel_list_tips');
	showformheader('commentmodel');
	showtableheader('');
	showsubtitle(array('<input type="checkbox" onclick="checkall(this.form, \'cmids\')" name="chkall" >', 'cmid', 'modelname', 'scorename', 'cmdateline', 'commentmodel_operation'));

	$query = DB::query('SELECT * FROM '.tname('commentmodels').' ORDER BY cmid ASC');
	while($commentmodel = DB::fetch($query)) {
		foreach(unserialize($commentmodel['scorename']) as $scorename) {
			$commentmodel['scorenamestr'] .= '['.$scorename.']';
		}
		$commentmodelarr[] = $commentmodel;
	}
	foreach($commentmodelarr as $value) {
		showtablerow('', array(), array("<input class='checkbox' type='checkbox' name='cmids[]' value='$value[cmid]' />", $value['cmid'], $value['modelname'], $value['scorenamestr'], date('Y-m-d', $value['dateline']), '<a href="admin.php?action=commentmodel&op=edit&cmid='.$value['cmid'].'">'.lang('commentmodeledit').'</a>'));
	}
	showcommentmod();
	showtablefooter();
	showformfooter();
	bind_ajax_form();
}

?>