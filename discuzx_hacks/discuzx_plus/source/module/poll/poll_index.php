<?php

/**
 *      [Discuz! XPlus] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: poll_index.php 650 2010-09-13 07:25:03Z yexinhao $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$itemid = !empty($_G['gp_id']) ? intval($_G['gp_id']) : 0;
$iframeurl = !empty($iframe) ? "&iframe=$iframe" : '';
$bgcolorurl = !empty($bgcolor) && !empty($iframe) ? "&bgcolor=$bgcolor" : '';
$poll_url = 'poll.php?id='.$itemid.$iframeurl.$bgcolorurl;

$see = $checkbox = FALSE;
$metakeywords = $metadescription = $title = '';
$poll_setting = array();

if($itemid) {
	$poll_setting = DB::fetch_first("SELECT * FROM ".DB::table('poll_item')." i LEFT JOIN ".DB::table('poll_item_field')." f ON i.itemid = f.itemid WHERE i.itemid = '$itemid' LIMIT 0, 1");
	$tpl = $_G['showmessage']['tpl'] = $poll_setting['templateid'] && $_G['cache']['template']['poll'][$poll_setting['templateid']]['directory'] ? $_G['cache']['template']['poll'][$poll_setting['templateid']]['directory'] : 'default';
	$_G['showmessage']['cssurl'] = 'template/'.$_G['showmessage']['module'].'/'.$_G['showmessage']['tpl'].'/'.$_G['showmessage']['module'].'.css';
	$see = checksee();
	$checkbox = !empty($poll_setting['type']) ? true : false;
	$with_img = !empty($poll_setting['contenttype']) ? true : false;
	$with_img && $checkbox = false;
}

if(empty($poll_setting) || (empty($poll_setting['available']) && empty($_G['adminid']))) {
	showmessage('poll_inexistence');
}

if(empty($_G['gp_action'])) {
	$metakeywords = $poll_setting['seokeywords'];
	$metadescription = $poll_setting['seodesc'];
	$from = !empty($_G['gp_from']) ? intval($_G['gp_from']) : 0;
	$title = $poll_setting['title'].'-'.$_G['setting']['bbname'];

	$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table('poll_choice')." WHERE itemid = '$itemid'");

	$start_limit = $pagenum = 0;
	$page = 1;
	$condition = $multi = '';
	$poll_setting['numperpage'] = $poll_setting['numperpage'] > 0 && $poll_setting['numperpage'] < 200 ? intval($poll_setting['numperpage']) : 200;
	if($with_img && $poll_setting['numperpage'] > 0) {
		$pagenum = ceil($count / $poll_setting['numperpage']);
		$page = !empty($_G['gp_page']) ? intval($_G['gp_page']) : 1;
		$page = min($pagenum, $page);
		$page = max(1, $page);
		$start_limit = ($page - 1) * $poll_setting['numperpage'];
		$condition = "LIMIT $start_limit, {$poll_setting['numperpage']}";
		$multi = multi($count, $poll_setting['numperpage'], $page, $poll_url);
	}

	$query = DB::query("SELECT * FROM ".DB::table('poll_choice')." WHERE itemid = '$itemid' ORDER BY displayorder $condition");
	while($row = DB::fetch($query)) {
		$row['imagethumb'] = $with_img && $row['imageurl'] ? $_G['setting']['attachurl'].'poll/'.$row['imageurl'].'.thumb.jpg' : 'static/image/common/default.jpg.thumb.jpg';
		$row['image'] = $with_img && $row['imageurl'] ? $_G['setting']['attachurl'].'poll/'.$row['imageurl'] : $_G['setting']['attachurl'].'poll/default.jpg.thumb.jpg';
		$row['percent'] = !empty($poll_setting['totalnum']) ? round($row['pollnum'] / $poll_setting['totalnum'], 2) * 100 : 0;
		$choice[] = $row;
	}

	while (list($key) = each($choice)) {
		$prevkey = $key - 1;
		$nextkey = $key + 1;
		$choice[$key]['prevchoiceid'] = !empty($choice[$prevkey]['choiceid']) ? $choice[$prevkey]['choiceid'] : NULL;
		$choice[$key]['nextchoiceid'] = !empty($choice[$nextkey]['choiceid']) ? $choice[$nextkey]['choiceid'] : NULL;
	}

	$so = showsoflash();

	$tplfile = $with_img ? 'index_image' : 'index_normal';
	include template('poll/'.$tpl.'/'.$tplfile);

} elseif($_G['gp_action'] == 'choose') {

	if($poll_setting['starttime'] > TIMESTAMP) {
		showmessage('poll_unstart');
	}
	if(!empty($poll_setting['endtime']) && $poll_setting['endtime'] < TIMESTAMP) {
		showmessage('poll_expired');
	}

	$choice_arr = $choicelist = array();

	if(is_array($_G['gp_choose_value'])) {
		$choicelist = dhtmlspecialchars($_G['gp_choose_value']);
	} else {
		$choicelist[] = dhtmlspecialchars($_G['gp_choose_value']);
	}

	foreach($choicelist as $value) {
		$value = intval($value);
		if($value > 0) {
			$choice_arr[$value] = $value;
		}
	}

	if(empty($choice_arr)) {
		showmessage('poll_choice_null');
	} elseif($checkbox && $poll_setting['choicenum'] > 0 && count($choice_arr) > $poll_setting['choicenum']) {
		showmessage('poll_choicenum_more', '', array('num' => $poll_setting['choicenum'] ));
	} elseif(!$checkbox && count($choice_arr) > 1) {
		showmessage('poll_choicenum_more', '', array('num' => $poll_setting['choicenum'] ));
	}

	if(!empty($poll_setting['repeattype'])) {
		checkvote();
	}

	$pollnum = array();
	foreach($choice_arr as $value) {
		empty($pollnum[$value]) && $pollnum[$value] = 1;
		$data = array(
			'uid' => $_G['uid'],
			'ip' => $_G['clientip'],
			'dateline' => $_G['timestamp'],
			'itemid' => $itemid,
			'choiceid' => $value
		);
		DB::insert('poll_value', $data);
	}

	$tnum = 0;
	$returnjs_num = array();
	foreach($pollnum as $id => $value) {
		DB::query("UPDATE ".DB::table('poll_choice')." SET pollnum = (pollnum + $value) WHERE choiceid = '$id' ");
		if(DB::affected_rows()) {
			$tnum += $value;
			$returnjs_num[] = $id;
		}
	}
	if($tnum > 0) {
		DB::query("UPDATE ".DB::table('poll_item')." SET totalnum = (totalnum + $tnum) WHERE itemid = '$itemid' ");
	}

	pollsetcookie();
	$see = checksee();
	if($see) {
		$customlang = customlang('poll_vote_succeed', null, $_G['cache']['poll_setting'], 'poll/message');
		showmessage($customlang, $poll_url, $returnjs_num);
	} else {
		showmessage('poll_vote_succeed_login', $poll_url, $returnjs_num, array('showmsg' => true, 'login' => 1));
	}

}

function checksee() {
	global $_G, $poll_setting;
	static $see;
	$itemid = $poll_setting['itemid'];
	$see === null && empty($poll_setting['resultview_time']) && ($poll_setting['resultview_mod'] == '1' || ($poll_setting['resultview_mod'] == '2' && $_G['uid'])) && $see = true;
	$see === null && !empty($poll_setting['resultview_time']) && !empty($_G['cookie']['c_'.$itemid]) && ($poll_setting['resultview_mod'] == '1' || ($poll_setting['resultview_mod'] == '2' && $_G['uid'])) && $see = true;
	return $see;
}

function checkvote($cmd = null) {
	global $_G, $poll_setting;
	$cookierkey = $lastpolldate = '';
	$itemid = $poll_setting['itemid'];
	$cookiekey = 'c_'.$itemid;
	$cookie = &$_G['cookie'];
	$repeatarr = array(
		1 => 'cookie',
		2 => 'username',
		3 => 'ip',
		4 => 'so',
	);
	$repeatcount = count($repeatarr);
	$repeattype = (string) intval($poll_setting['repeattype']);
	$repeattype = sprintf('%0'.$repeatcount.'b', $repeattype);
	$i = 1;
	foreach($repeatarr as $key => $value) {
		$check[$value] = intval($repeattype{$repeatcount - $i});
		$i++;
	}

	if($cmd == 'check') {
		return $check;
	}

	if($check['cookie']) {
		if(!empty($cookie[$cookiekey])) {
			$lastpolldate = $cookie[$cookiekey];
			checkinlastdate($lastpolldate, 'cookie');
		}
	}

	if($check['username']) {
		if(empty($_G['uid'])) {
			showmessage('poll_guest_unallowed', '', '', array('showmsg' => true, 'login' => 1));
		}
		$cookierkey = 'r_'.$itemid.'_uid';
		if(!empty($cookie[$cookierkey]) && $cookie[$cookierkey] == $_G['uid']) {
			$lastpolldate = $cookie[$cookiekey];
			checkinlastdate($lastpolldate, 'username');
		}
		$lastpolldate = DB::result_first('SELECT MAX(dateline) AS dateline FROM '.DB::table('poll_value')." WHERE uid='$_G[uid]' AND itemid = '$itemid' ");
		checkinlastdate($lastpolldate, 'username');
	}

	if($check['ip']) {
		$cookierkey = 'r_'.$itemid.'_ip';
		if(!empty($cookie[$cookierkey]) && $cookie[$cookierkey] == $_G['clientip']) {
			$lastpolldate = $cookie[$cookiekey];
			 checkinlastdate($cookie[$cookiekey], 'ip');
		}
		$lastpolldate = DB::result_first('SELECT MAX(dateline) AS dateline FROM '.DB::table('poll_value')." WHERE ip='{$_G['clientip']}' AND itemid = '$itemid' ");
		checkinlastdate($lastpolldate, 'ip');
	}

	if($check['so']) {
		$cookie['sodateline'] = intval($cookie['sodateline']);
		if(empty($cookie['sodateline']) /*|| $_G['timestamp'] - $cookie['sodateline'] > 1800*/ ) {
			showmessage('poll_vote_so_error');
		}
		$swfhash = md5(substr(md5($_G['config']['security']['authkey']), 8).'_'.$_G['siteurl'].'_'.$poll_setting['itemid'].'_'.$cookie['sodateline']);
		if(!($cookie['sohash'] == $swfhash && $_G['inajax'] && $_SERVER['REQUEST_METHOD'] == 'POST')) {
			showmessage('poll_vote_so_error');
		}
		if(!empty($cookie[$swfhash])) {
			$lastpolldate = $cookie[$swfhash];
			checkinlastdate($cookie[$swfhash], 'so');
		}
	}
}

function checkinlastdate($lastdate, $check = 'cookie') {
	global $_G, $poll_setting;
	$lastdate = intval($lastdate);
	$limittime =  intval($poll_setting['limittime']) * 60;
	if($lastdate && empty($poll_setting['limittime'])) {
		if(!empty($poll_setting['errordetail'])) {
			$customlang = customlang('poll_per_once', null, $_G['cache']['poll_setting'], 'poll/message');
			showmessage($customlang);
		} else {
			$customlang = customlang('poll_vote_error', null, $_G['cache']['poll_setting'], 'poll/message');
			showmessage($customlang);
		}
	} elseif(intval($_G['timestamp'] - $lastdate) < $limittime) {
		if(!empty($poll_setting['errordetail'])) {
			$utime = dgmdate($lastdate + $limittime, 'u');
			$check = lang('poll/message', 'poll_repeattype_'.$check);
			$customlang = customlang('poll_limittime_short', array('check' => $check, 'utime' => $utime), $_G['cache']['poll_setting'], 'poll/message');
			showmessage($customlang);
		} else {
			$customlang = customlang('poll_vote_error', null, $_G['cache']['poll_setting'], 'poll/message');
			showmessage($customlang);
		}
	}
}

function pollsetcookie() {
	global $_G, $poll_setting;
	$itemid = $poll_setting['itemid'];
	$check = checkvote('check');
	dsetcookie('c_'.$itemid, $_G['timestamp'], 86400);
	$check['username'] && dsetcookie('r_'.$itemid.'_uid', $_G['uid'], $poll_setting['limittime']);
	$check['ip'] && dsetcookie('r_'.$itemid.'_ip', $_G['clientip'], $poll_setting['limittime']);
}

function showsoflash() {
	global $_G, $poll_setting;
	$check = checkvote('check');
	if($check['so']) {
		return "
			<div id=\"soflash\">
				<script type=\"text/javascript\">
					$('soflash').innerHTML = AC_FL_RunContent(
						'width', '1', 'height', '1',
						'src', '".STATICURL."image/common/soflash.swf?site={$_G['siteurl']}misc.php%3fmod=so%26&module=poll&pollid={$poll_setting['itemid']}&operation=config&random=".random(4)."',
						'quality', 'high',
						'id', 'soflash_swf',
						'menu', 'false',
						'allowScriptAccess', 'always',
						'wmode', 'transparent'
					);
				</script>
			</div>
		";
	} else {
		return '';
	}
}

?>