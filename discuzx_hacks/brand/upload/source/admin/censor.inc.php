<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: censor.inc.php 4337 2010-09-06 04:48:05Z fanshengshuai $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

$censor = array();

$allowvars = array('censor');//允许提交的settings变量名

//读入缓存
updatecensorcache(false, 86400);

if(!empty($_POST['valuesubmit'])) {

	//提交了数据
	$censorarr = explode("\n", $_POST['censor']);
	$newcensorstr = $censorstr = $censorcomma = '';
	foreach($censorarr as $value) {
		list($newfind, $newreplacement) = array_map('trim', explode('=', $value));
		$newreplacement = empty($newreplacement) ? '**' : addslashes(str_replace("\\\'", "\'", $newreplacement));
		if(strlen($newfind) < 3) {
			continue;
		} else {
			$newcensorstr .= $newfind.'='.$newreplacement."\n";
		}
	}
	$censorvalue = trim(strip_tags($newcensorstr));
	$rpsql = " ('censor', '$censorvalue') ";
	if(!empty($rpsql)) {
		DB::query('REPLACE INTO '.tname('data').' (`variable`, `value`) VALUES '.$rpsql);
	}
	updatecensorcache(true, 43200, $censorvalue); //更新设置缓存
	cpmsg('message_success', 'admin.php?action=censor');

} else {
	//没有提交数据
	$query = DB::query('SELECT * FROM '.tname('data').' WHERE variable=\'censor\' LIMIT 1');
	$censor = DB::fetch($query);
	shownav('global', 'settings_censor');
	showsubmenu('settings_censor');
	showtips('global_censor_tips');
	showformheader('censor');
	showhiddenfields(array('valuesubmit' => 'yes'));
	showtableheader();
	echo '<tr class="noborder"><td class="vtop rowform" style="">
<textarea class="tarea" cols="100" id="censor" name="censor" onkeyup="textareasize(this, 0)" ondblclick="textareasize(this, 1)" rows="12" style="height:220px;">'
	.$censor['value'].
	'</textarea></td><td class="vtop tips2"><br>'.lang('tips_textarea').'</td></tr>';
	showsubmit('settingsubmit', 'submit', '');
	showtablefooter();
	showformfooter();
}

?>