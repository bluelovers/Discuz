<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block.inc.php 4360 2010-09-07 08:03:59Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

require_once(B_ROOT.'./source/adminfunc/tool.func.php');

$blockstr = '';
$block = $blockarr = $checkresults = array();

if(!empty($_POST['deletesubmit']) && $_POST['operation'] == "delete") {

	if(!empty($_POST['blockids'])) {
		$blockids = implode(',', $_POST['blockids']);
		DB::query("DELETE FROM ".tname('blocks')." WHERE blockid IN ($blockids)");
		cpmsg('message_success', 'admin.php?action=block');
	} else {
		cpmsg('notselect_item', 'admin.php?action=block');
	}
}

if(($_GET['op'] == 'edit' || $_GET['op'] == 'add') && !empty($_POST['valuesubmit'])) {

	$blockname = '';
	$blocksqlarr = array();
	$_POST['blocktype'] = 'sql';
	$_POST['blockname'] = trim($_POST['blockname']);
	$postarr = array();
	foreach ($_POST as $pkey => $pvalue) {
		$postarr[$pkey] = shtmlspecialchars($pvalue);
	}
	$blocktext = addslashes(serialize($postarr));
	if(empty($_POST['blockname']) || bstrlen($_POST['blockname']) > 10) {
		array_push($checkresults, array('blockname'=>lang('block_blockname_length_error')));
	}
	$_POST['blocksql'] = getblocksql($_POST['blocksql']);
	$blockcodearr[] = 'sql/'.rawurlencode($_POST['blocksql']);
	$_POST['blockstart'] = intval($_POST['blockstart']);
	$_POST['blocklimit'] = intval($_POST['blocklimit']);
	if($_POST['blocklimit'] < 1) {
		array_push($checkresults, array('blocklimit'=>lang('block_thread_code_limit')));
	} else {
		$blockcodearr[] = 'limit/'.$_POST['blockstart'].','.$_POST['blocklimit'];
	}
	if(!empty($_POST['tplname']) && !file_exists(B_ROOT.'static/blockstyle/'.$_POST['tplname'].'.html.php')) {
		array_push($checkresults, array('tplnameerror'=>lang('block_tplname_error')));
	}
	if(!empty($checkresults)) {
		cpmsg('add_error', '', 'error', '', true, true, $checkresults);
	}
	$_POST['cachetime'] = intval($_POST['cachetime']);
	if(!empty($_POST['cachetime'])) {
		$blockcodearr[] = 'cachetime/'.$_POST['cachetime'];
	}
	if(!empty($_POST['cachename'])) {
		$blockcodearr[] = 'cachename/'.rawurlencode($_POST['cachename']);
	}
	if(!empty($_POST['tplname'])) {
		$blockcodearr[] = 'tpl/'.rawurlencode($_POST['tplname']);
	}
	$blockcode = '';
	$blockcode .= '<!--{block name="'.$_POST['blocktype'].'" parameter="'.implode('/', $blockcodearr).'"}-->';
	$blockcode .= '<!--'.$_POST['blockname'].'-->';
	$blockcode = addslashes($blockcode);
	$scorename = serialize($scorearr);
	DB::query("REPLACE INTO ".tname('blocks')." (blockid, blockname, dateline, blocktext, blockcode, tplname) VALUES ('$_POST[blockid]', '$_POST[blockname]', '$_G[timestamp]', '$blocktext', '$blockcode', '$_POST[tplname]');");

	cpmsg('message_success', 'admin.php?action=block');

} elseif($_GET['op'] == 'add' || $_GET['op'] == 'edit') {

	if($_GET['op'] == 'edit') {
		$block = DB::fetch(DB::query("SELECT * FROM ".tname('blocks')." WHERE blockid='$_GET[blockid]'"));
		$blockarr = unserialize($block['blocktext']);
		foreach($blockarr['blocktext'] as $key => $value) {
			$blockarr[$key] = $value;
		}
	}
	shownav('global', 'block_'.$_GET['op']);
	showsubmenu('block_add', array(
		array('nav_block', 'block', '0'),
		array('block_add', 'block&op=add', '1')
	));
	showtips('block_'.$_GET['op'].'_tips');
	showformheader('block&op='.$_GET['op']);
	showtableheader('');
	showsetting('blockname', 'blockname', $blockarr['blockname'], 'text');

	showsetting('blocksql', 'blocksql', htmlspecialchars_decode($blockarr['blocksql']), 'textarea');
	showsetting('blockstart', 'blockstart', $blockarr['blockstart'], 'text');
	showsetting('blocklimit', 'blocklimit', $blockarr['blocklimit'], 'text');
	showsetting('cachetime', 'cachetime', $blockarr['cachetime'], 'text');
	showsetting('cachename', 'cachename', $blockarr['cachename'], 'text');
	showsetting('tplname', 'tplname', $blockarr['tplname'], 'text');
	showhiddenfields(array('blockid' => $_GET['blockid']));
	showsubmit('valuesubmit');
	showtablefooter();
	showformfooter();
	bind_ajax_form();

} else {

	shownav('global', 'block_list');
	showsubmenu('nav_block', array(
		array('nav_block', 'block', '1'),
		array('block_add', 'block&op=add', '0')
	));
	showtips('block_list_tips');
	showformheader('block');
	showtableheader('');
	showsubtitle(array('<input type="checkbox" onclick="checkall(this.form, \'blockids\')" name="chkall" >', 'blockid', 'blockname', 'block_dateline', 'blcokcode', 'block_operation'));

	$query = DB::query('SELECT * FROM '.tname('blocks').' ORDER BY blockid ASC');
	while($block = DB::fetch($query)) {
		foreach(unserialize($block['scorename']) as $scorename) {
			$block['scorenamestr'] .= '['.$scorename.']';
		}
		$blockarr[] = $block;
	}
	foreach($blockarr as $value) {
		$textarea = '';
		preg_match("/parameter\=\"(.*?)\"/is", $value['blockcode'], $matches);
		if(!empty($matches[1]) && strpos($matches[1], 'tpl/data') === false) {
			$value['blocktype'] = 'blocktype';
			$value['jscode'] = '<script charset="utf-8" language="JavaScript" src="'.B_URL.'/batch.javascript.php?param='.rawurlencode(passport_encrypt('blocktype/'.$value['blocktype'].'/'.$matches[1], $_G['setting']['sitekey'])).'"></script>';
		}
		$textarea = !empty($value['tplname'])?lang('showblockcode').'<br /><textarea cols="55" rows="3">'.$value['blockcode'].'</textarea><br />'.lang('showjsblockcode').'<br /><textarea cols="55" rows="3">'.$value['jscode'].'</textarea>':'<textarea cols="55" rows="3">'.$value['blockcode'].'</textarea>';
		showtablerow('', array(), array("<input class='checkbox' type='checkbox' name='blockids[]' value='$value[blockid]' />", $value['blockid'], $value['blockname'], date('Y-m-d', $value['dateline']), $textarea, '<a href="admin.php?action=block&op=edit&blockid='.$value['blockid'].'">'.lang('blockedit').'</a>'));
	}
	showcommentmod(false);
	showtablefooter();
	showformfooter();
	bind_ajax_form();
}
//加密函數
function passport_encrypt($txt, $key) {
	srand((double)microtime() * 1000000);
	$encrypt_key = md5(rand(0, 32000));
	$ctr = 0;
	$tmp = '';
	for($i = 0; $i < strlen($txt); $i++) {
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
	}
	return base64_encode(passport_key($tmp, $key));
}

//加密函數
function passport_key($txt, $encrypt_key) {
	$encrypt_key = md5($encrypt_key);
	$ctr = 0;
	$tmp = '';
	for($i = 0; $i < strlen($txt); $i++) {
		$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		$tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
	}
	return $tmp;
}
?>