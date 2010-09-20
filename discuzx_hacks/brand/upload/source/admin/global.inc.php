<?php

/**
 *      [品牌空间] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: global.inc.php 4432 2010-09-14 04:05:23Z yumiao $
 */

if(!defined('IN_ADMIN')) {
	exit('Acess Denied');
}

$editvalue = array();

$allowvars = array('wwwname', 'wwwurl', 'sitename',
					'seokeywords', 'seodescription', 'mapapikey', 
					'sitetel', 'siteqq', 
					'allowcache', 'cachemode', 'discounturl', 'attenddescription', 'registerrule', 'miibeian', 
					'analytics', 'urltype', 'regurl', 'enablecard', 'commstatus', 'commentmodel', 'enablemap', 
					'commentperpage', 'noticeperpage', 'goodperpage', 'consumeperpage', 'shopsearchperpage',
					'consumesearchperpage', 'goodsearchperpage', 
					'cardperpage', 'albumsearchperpage', 'allowcreateimg', 'fontpath', 'seccode',
					'siteclosed', 'siteclosed_reason', 'multipleshop', 'auditnewshops', 'defaultshopgroup');//允许提交的settings变量名

//读入缓存
$cachefile = B_ROOT.'./data/system/config.cache.php';
if(!file_exists($cachefile)) {
	updatesettingcache();
}
@include($cachefile);
$editvalue = $_G['setting'];
$editvalue['siteclosed'] = $editvalue['siteclosed'] ? $editvalue['siteclosed'] : 0;

if(!empty($_POST['valuesubmit'])) {

	//提交了数据
	$item = $checkresults = array();
	$key = $rpsql = $comma = '';
	if(intval($_POST['auditnewshops']) == 0 && empty($_POST['defaultshopgroup'])) {
		array_push($checkresults, array('auditnewshops'=>$lang['global_auditnewshops_comment']));
	}
	if(!empty($checkresults)) {
		cpmsg('global_submit_error', '', '', '', true, true, $checkresults);
	}
	foreach($_POST as $key=>$value) {
		if(in_array($key, $allowvars) && $_POST[$key]!=(string)$editvalue[$key]) {
			if(in_array($key, array('analytics'))) {
				$rpsql .= "$comma ('$key', '".$value."') ";
				$comma = ', ';
			} else {
				$rpsql .= "$comma ('$key', '".trim(strip_tags($value))."') ";
				$comma = ', ';
			}
		}
	}
	if(!empty($rpsql)) {
		$sql = 'REPLACE INTO '.tname('settings').' (`variable`, `value`) VALUES '.$rpsql;
		DB::query($sql);
	}
	updatesettingcache(); //更新设置缓存
	cpmsg('message_success', 'admin.php?action=global');

} else {
	//没有提交数据
	shownav('global', 'settings_basic');
	showsubmenu('settings_basic', array(
	array('settings_basic', 'global', '1'),
	array('nav_attach', 'attach', '0')
	));
	showtips('global_tips');
	showformheader('global');
	showhiddenfields(array('valuesubmit' => 'yes'));
	showtableheader();
	showsetting('global_wwwname', 'wwwname', $editvalue['wwwname'], 'text');
	showsetting('global_wwwurl', 'wwwurl', $editvalue['wwwurl'], 'text');
	showsetting('global_sitename', 'sitename', $editvalue['sitename'], 'text');
	showsetting('global_regurl', 'regurl', $editvalue['regurl'], 'text');
	showsetting('global_seokeywords', 'seokeywords', $editvalue['seokeywords'], 'text');
	showsetting('global_seodescription', 'seodescription', $editvalue['seodescription'], 'text');
	showsetting('global_enablemap', array('enablemap', array(
	array(1, lang('yes'), array('mapext' => '')),
	array(0, lang('no'), array('mapext' => 'none'))
	), true), $editvalue['enablemap'], 'mradio');
	showtagheader('tbody', 'mapext', $editvalue['enablemap'], 'sub');
	showsetting('global_mapapikey', 'mapapikey', $editvalue['mapapikey'], 'text');
	showtagfooter('tbody');
	showsetting('global_sitetel', 'sitetel', $editvalue['sitetel'], 'text');
	showsetting('global_siteqq', 'siteqq', $editvalue['siteqq'], 'text');
	showsetting('global_allowcache', array('allowcache', array(
	array(1, lang('yes'), array('cacheext' => '')),
	array(0, lang('no'), array('cacheext' => 'none'))
	), true), $editvalue['allowcache'], 'mradio');
	showtagheader('tbody', 'cacheext', $editvalue['allowcache'], 'sub');
	showsetting('global_cachemode', array('cachemode', array(
	array('database', lang('global_cachemode_database')),
	array('memcache', lang('global_cachemode_memcache')),
	array('tokyocabinet', lang('global_cachemode_tokyocabinet')),
	array('file', lang('global_cachemode_file'))
	)), $editvalue['cachemode'], 'select');
	showtagfooter('tbody');
	showsetting('global_multipleshop', array('multipleshop', array(
	array(1, lang('yes')),
	array(0, lang('no'))
	), true), $editvalue['multipleshop'], 'mradio');

	$query = DB::query('SELECT * FROM '.tname('shopgroup').' WHERE type=\'shop\' ORDER BY id ASC');
	while($shopgroup = DB::fetch($query)) {
		$shopgrouparr[] = $shopgroup;
	}
	showsetting('global_auditnewshops', array('auditnewshops', array(
					array(1, lang('yes'), array('auditnewshops' => 'none')),
					array(0, lang('no'), array('auditnewshops' => ''))
					), true), $_G['setting']['auditnewshops'], 'mradio');
	showtagheader('tbody', 'auditnewshops', !$_G['setting']['auditnewshops'], 'sub');
	echo "<tr><td colspan='2'>";
	if(!empty($shopgrouparr)) {
		echo "<table width='300' style='margin:10px;'>";
		showsubtitle(array('', 'groupid', 'grouptitle'));
		foreach($shopgrouparr as $value) {
			$checked = $value['id'] == $_G['setting']['defaultshopgroup'] ? 'checked' : '';
			showtablerow('', array('class="td27" style="width:30px;"'), array("<input class='radio' type='radio' name='defaultshopgroup' value='$value[id]' $checked/>", $value['id'], $value['title']));
		}
		echo "</table>";
	} else {
		showtablerow('', array('class="td27"'), array(lang('global_auditnewshops_addgroup')));
	}
	echo "</td></tr>";
	showtagfooter('tbody');

	showsetting('global_seccode', array('seccode', array(
	array(1, lang('yes')),
	array(0, lang('no'))
	), true), $editvalue['seccode'], 'mradio');
	showsetting('global_commstatus', array('commstatus', array(
	array(1, lang('yes')),
	array(0, lang('no'))
	), true), $editvalue['commstatus'], 'mradio');
	showsetting('global_commentmodel', array('commentmodel', array(
	array(1, lang('yes')),
	array(0, lang('no'))
	), true), $editvalue['commentmodel'], 'mradio');
	showsetting('global_enablecard', array('enablecard', array(
	array(1, lang('yes'), array('cardext' => '')),
	array(0, lang('no'), array('cardext' => 'none'))
	), true), $editvalue['enablecard'], 'mradio');
	showtagheader('tbody', 'cardext', $editvalue['enablecard'], 'sub');
	showsetting('global_discounturl', 'discounturl', $editvalue['discounturl'], 'text');
	showtagfooter('tbody');
	$fontarr = array();
	$dir = opendir(B_ROOT.'./static/image/fonts/en');
	while($entry = readdir($dir)) {
		if(in_array(strtolower(fileext($entry)), array('ttf', 'ttc'))) {
			$fontarr[] = array('en/'.$entry, $entry);
		}
	}
	$dir = opendir(B_ROOT.'./static/image/fonts/ch');
	while($entry = readdir($dir)) {
		if(in_array(strtolower(fileext($entry)), array('ttf', 'ttc'))) {
			$fontarr[] = array('ch/'.$entry, $entry);
		}
	}
	showsetting('global_allowcreateimg', array('allowcreateimg', array(
	array(1, lang('yes'), array('fontpath' => '')),
	array(0, lang('no'), array('fontpath' => 'none'))
	), true), $editvalue['allowcreateimg'], 'mradio');
	showtagheader('tbody', 'fontpath', $editvalue['allowcreateimg'], 'sub');
	showsetting('global_fontpath', array('fontpath', $fontarr), $editvalue['fontpath'], 'select');
	showtagfooter('tbody');
	showsetting('global_attenddescription', 'attenddescription', $editvalue['attenddescription'], 'textarea');
	showsetting('global_registerrule', 'registerrule', $editvalue['registerrule'], 'textarea');
	showsetting('global_miibeian', 'miibeian', $editvalue['miibeian'], 'text');
	showsetting('global_analytics', 'analytics', $editvalue['analytics'], 'text');
	showsetting('global_urltype', array('urltype', array(
	array(3, lang('global_urltype_rewrite')),
	array(4, lang('global_urltype_normal'))
	)), $editvalue['urltype'], 'select');
	showsetting('global_consumesearchperpage', 'consumesearchperpage', $editvalue['consumesearchperpage'], 'number');
	showsetting('global_cardperpage', 'cardperpage', $editvalue['cardperpage'], 'number');
	showsetting('global_shopsearchperpage', 'shopsearchperpage', $editvalue['shopsearchperpage'], 'number');
	showsetting('global_goodsearchperpage', 'goodsearchperpage', $editvalue['goodsearchperpage'], 'number');
	showsetting('global_albumsearchperpage', 'albumsearchperpage', $editvalue['albumsearchperpage'], 'number');
	showsetting('global_noticeperpage', 'noticeperpage', $editvalue['noticeperpage'], 'number');
	showsetting('global_goodperpage', 'goodperpage', $editvalue['goodperpage'], 'number');
	showsetting('global_consumeperpage', 'consumeperpage', $editvalue['consumeperpage'], 'number');
	showsetting('global_commentperpage', 'commentperpage', $editvalue['commentperpage'], 'number');

	// 关闭站点
	showsetting('global_siteclosed', array('siteclosed', array(
	array(1, lang('yes'), array('siteclosedext' => '')),
	array(0, lang('no'), array('siteclosedext' => 'none'))
	), true), $editvalue['siteclosed'], 'mradio');
	showtagheader('tbody', 'siteclosedext', $editvalue['siteclosed'], 'sub');
	showsetting('global_siteclosed_reason', 'siteclosed_reason', $editvalue['siteclosed_reason'], 'textarea');
	showtagfooter('tbody');

	showsubmit('settingsubmit', 'submit', '', $extbutton.(!empty($from) ? '<input type="hidden" name="from" value="'.$from.'">' : ''));
	showtablefooter();
	showformfooter();
	bind_ajax_form();
}

?>