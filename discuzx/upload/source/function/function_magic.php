<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_magic.php 19561 2011-01-10 02:28:57Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function checkmagicperm($perms, $id) {
	$id = $id ? intval($id) : '';
	return strexists("\t".trim($perms)."\t", "\t".trim($id)."\t") || !$perms;
}

function getmagic($magicid, $magicnum, $weight, $totalweight, $uid, $maxmagicsweight, $force = 0) {
	if($weight + $totalweight > $maxmagicsweight && !$force) {
		showmessage('magics_weight_range_invalid', '', array('less' => $weight + $totalweight - $maxmagicsweight));
	} else {
		$query = DB::query("SELECT magicid FROM ".DB::table('common_member_magic')." WHERE uid='$uid' AND magicid='$magicid'");
		if(DB::num_rows($query)) {
			DB::query("UPDATE ".DB::table('common_member_magic')." SET num=num+'$magicnum' WHERE uid='$uid' AND magicid='$magicid'");
		} else {
			DB::query("INSERT INTO ".DB::table('common_member_magic')." (uid, magicid, num) VALUES ('$uid', '$magicid', '$magicnum')");
		}
	}
}

function getmagicweight($uid, $magicarray) {
	$totalweight = 0;
	$query = DB::query("SELECT magicid, num FROM ".DB::table('common_member_magic')." WHERE uid='$uid'");
	while($magic = DB::fetch($query)) {
		$totalweight += $magicarray[$magic['magicid']]['weight'] * $magic['num'];
	}

	return $totalweight;
}

function getpostinfo($id, $type, $colsarray = '') {
	global $_G;
	$sql = $comma = '';
	$type = in_array($type, array('tid', 'pid', 'blogid')) && !empty($type) ? $type : 'tid';
	$cols = '*';

	if(!empty($colsarray) && is_array($colsarray)) {
		$cols = '';
		foreach($colsarray as $val) {
			$cols .= $comma.$val;
			$comma = ', ';
		}
	}

	switch($type) {
		case 'tid':
			$sql = "SELECT $cols FROM ".DB::table('forum_thread')." WHERE tid='$id' AND displayorder>='0'";
			break;
		case 'pid':
			$posttable = getposttablebytid($_G['tid']);
			$sql = "SELECT $cols FROM ".DB::table($posttable)." p, ".DB::table('forum_thread')." t WHERE p.pid='$id' AND p.invisible='0' AND t.tid=p.tid";
			break;
		case 'blogid':
			$sql = "SELECT $cols FROM ".DB::table('home_blog')." WHERE blogid='$id' AND status='0'";
			break;
	}

	if($sql) {
		$info = DB::fetch_first($sql);
		if(!$info) {
			showmessage('magics_target_nonexistence');
		} else {
			return daddslashes($info, 1);
		}
	}
}

function getuserinfo($username, $colsarray = '') {
	$cols = '*';
	if(!empty($colsarray) && is_array($colsarray)) {
		$cols = '';
		foreach($colsarray as $val) {
			$cols .= $comma.$val;
			$comma = ', ';
		}
	}

	$member = DB::fetch_first("SELECT $cols FROM ".DB::table('common_member')." WHERE username='$username'");
	if(!$member) {
		showmessage('magics_target_nonexistence');
	} else {
		return daddslashes($member, 1);
	}
}

function givemagic($username, $magicid, $magicnum, $totalnum, $totalprice, $givemessage, $magicarray) {
	global $_G;

	$member = DB::fetch_first("SELECT m.uid, m.username, u.maxmagicsweight FROM ".DB::table('common_member')." m LEFT JOIN ".DB::table('common_usergroup_field')." u ON u.groupid=m.groupid WHERE m.username='$username'");
	if(!$member) {
		showmessage('magics_target_nonexistence');
	} elseif($member['uid'] == $_G['uid']) {
		showmessage('magics_give_myself');
	}

	$totalweight = getmagicweight($member['uid'], $magicarray);
	$magicweight = $magicarray[$magicid]['weight'] * $magicnum;
	if($magicarray[$magicid]['weight'] && $magicweight + $totalweight > $member['maxmagicsweight']) {
		$num = floor(($member['maxmagicsweight'] - $totalweight) / $magicarray[$magicid]['weight']);
		$num = max(0, $num);
		showmessage('magics_give_weight_range_invalid', '', array('num' => $num));
	}

	getmagic($magicid, $magicnum, $magicweight, $totalweight, $member['uid'], $member['maxmagicsweight']);

	notification_add($member['uid'], 'magic', 'magics_receive', array('magicname' => $magicarray[$magicid]['name'], 'msg' => $givemessage));
	updatemagiclog($magicid, '3', $magicnum, $magicarray[$magicid]['price'], $member['uid']);

	if(empty($totalprice)) {
		usemagic($magicid, $totalnum, $magicnum);
		showmessage('magics_give_succeed', 'home.php?mod=magic&action=mybox', array('toname' => $username, 'num' => $magicnum, 'magicname' => $magicarray[$magicid]['name']));
	}
}

function magicrand($odds) {
	$odds = $odds > 100 ? 100 : intval($odds);
	$odds = $odds < 0 ? 0 : intval($odds);
	if(rand(1, 100) > 100 - $odds) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function magicthreadmod($tid) {
	$query = DB::query("SELECT * FROM ".DB::table('forum_threadmod')." WHERE tid='$tid' AND magicid='0'");
	while($threadmod = DB::fetch($query)) {
		if(!$threadmod['magicid'] && in_array($threadmod['action'], array('CLS', 'ECL', 'STK', 'EST', 'HLT', 'EHL'))) {
			showmessage('magics_mod_forbidden');
		}
	}
}


function magicshowsetting($setname, $varname, $value, $type = 'radio', $width = '20%') {
	$check = array();

	echo '<p class="mtm mbn">'.$setname.'</p>';
	if($type == 'radio') {
		$value ? $check['true'] = 'checked="checked"' : $check['false'] = 'checked="checked"';
		echo "<input type=\"radio\" name=\"$varname\" class=\"pr\" value=\"1\" $check[true] /> ".lang('core', 'yes')." &nbsp; &nbsp; \n".
			"<input type=\"radio\" name=\"$varname\" class=\"pr\" value=\"0\" $check[false] /> ".lang('core', 'no')."\n";
	} elseif($type == 'text') {
		echo "<input type=\"text\" name=\"$varname\" class=\"px p_fre\" value=\"".dhtmlspecialchars($value)."\" size=\"12\" autocomplete=\"off\" />\n";
	} elseif($type == 'hidden') {
		echo "<input type=\"hidden\" name=\"$varname\" value=\"".dhtmlspecialchars($value)."\" />\n";
	} else {
		echo $type;
	}

}

function magicshowtips($tips) {
	echo '<p>'.$tips.'</p>';
}

function magicshowtype($type = '') {
	if($type != 'bottom') {
		echo '<p>';
	} else {
		echo '</p>';
	}
}

function magicselect($uid, $typeid, $data) {
	$magiclist = array();
	$dataadd = $char = '';
	if($uid) {
		$typeidadd = $typeid ? 	"AND m.type='".intval($typeid)."'" : '';
		if($data && is_array($data)) {
			if($data['magic']) {
				foreach($data['magic'] as $item) {
					$dataadd .=  $char.'m.'.$item;
					$char = ' ,';
				}
			}
			if($data['member']) {
				foreach($data['member'] as $item) {
					$dataadd .=  $char.'m.'.$item;
					$char = ' ,';
				}
			}
		} else {
			$dataadd = 'm.*, mm.*';
		}
		$query = DB::query("SELECT $dataadd
				FROM ".DB::table('common_member_magic')." mm
				LEFT JOIN ".DB::table('common_magic')." m ON mm.magicid=m.magicid
				WHERE mm.uid='$uid' $typeidadd");
		while($mymagic = DB::fetch($query)) {
			$magiclist[] = $mymagic;
		}
	}

	return $magiclist;

}

function usemagic($magicid, $totalnum, $num = 1) {
	global $_G;

	if($totalnum == $num) {
		DB::query("DELETE FROM ".DB::table('common_member_magic')." WHERE uid='$_G[uid]' AND magicid='$magicid'");
	} else {
		DB::query("UPDATE ".DB::table('common_member_magic')." SET num=num+(-'$num') WHERE uid='$_G[uid]' AND magicid='$magicid'");
	}
}

function updatemagicthreadlog($tid, $magicid, $action = 'MAG', $expiration = 0, $extra = 0) {
	global $_G;
	$_G['username'] = !$extra ? $_G['username'] : '';
	DB::query("REPLACE INTO ".DB::table('forum_threadmod')." (tid, uid, magicid, username, dateline, expiration, action, status)
		VALUES ('$tid', '$_G[uid]', '$magicid', '$_G[username]', '$_G[timestamp]', '$expiration', '$action', '1')", 'UNBUFFERED');
}

function updatemagiclog($magicid, $action, $amount, $price, $targetuid = 0, $idtype = '', $targetid = 0) {
	global $_G;
	list($price, $credit) = explode('|', $price);
	DB::query("INSERT INTO ".DB::table('common_magiclog')." (uid, magicid, action, dateline, amount, price, credit, idtype, targetid, targetuid)
		VALUES ('$_G[uid]', '$magicid', '$action', '$_G[timestamp]', '$amount', '$price', '$credit', '$idtype', '$targetid', '$targetuid')", 'UNBUFFERED');
}


function magic_get($mid) {
	global $_G, $space;

	$query = DB::query("SELECT * FROM ".DB::table('common_magic')." WHERE mid = '$mid'");
	if(!$magic = DB::fetch($query)) {
		showmessage('unknown_magic');
	} else {
		$magic['forbiddengid'] = empty($magic['forbiddengid']) ? array() : explode(',', $magic['forbiddengid']);
		$magic['custom'] = $magic['custom'] ? unserialize($magic['custom']) : array();
	}

	if($magic['close']) {
		showmessage('magic_is_closed');
	}

	return $magic;
}

function magic_buy_get($magic) {
	global $_G, $space;

	if(!$magic) {
		showmessage('unknown_magic');
	} else {
		$mid = $magic['mid'];
	}

	$blacklist = array('coupon');
	if(in_array($mid, $blacklist)) {
		showmessage('magic_not_for_sale');
	}

	if(!checkperm('allowmagic')) {
		showmessage('magic_groupid_not_allowed');
	}

	if($magic['forbiddengid'] && in_array($space['groupid'], $magic['forbiddengid'])) {
		showmessage('magic_groupid_limit');
	}

	$setarr = array(
	'mid' => $mid,
	'storage' => $magic['providecount'],
	'lastprovide' => $_G['timestamp']
	);
	$query = DB::query('SELECT * FROM '.DB::table('common_magicstore')." WHERE mid = '$mid'");
	$magicstore = DB::fetch($query);
	if(!$magicstore) {
		DB::insert('magicstore', $setarr);
		$magicstore['storage'] = $magic['providecount'];
	} elseif($magicstore['storage'] < $magic['providecount'] &&
	$magicstore['lastprovide'] + $magic['provideperoid'] < $_G['timestamp']) {

		unset($setarr['mid']);
		DB::update('magicstore', $setarr, array('mid'=>$mid));
		$magicstore['storage'] = $magic['providecount'];
	}

	if($magicstore['storage'] < 1) {
		showmessage('magics_num_no_enough');
	}

	$discount = checkperm('magicdiscount');
	$charge = $magic['charge'];
	if($discount > 0) {
		$charge = intval($magic['charge'] * $discount / 10);
		if($charge < 1) {
			$charge = 1;
		}
	} elseif($discount < 0) {
		$charge = 0;
	}

	$magicstore['maxbuy'] = $charge ? min( $magicstore['storage'], floor($space['credit'] / $charge)) : $magicstore['storage'];

	$query = DB::query("SELECT * FROM ".DB::table("home_usermagic")." WHERE uid='$_G[uid]' AND mid = 'coupon'");
	$coupon = DB::fetch($query);

	return array(
	'magicstore' => $magicstore,
	'coupon' => $coupon,
	'discount' => $discount,
	'charge' => $charge
	);
}

function magic_buy_post($magic, $magicstore, $coupon) {
	global $_G, $space;

	if(!$magic) {
		showmessage('unknown_magic');
	} else {
		$mid = $magic['mid'];
	}

	$_POST['buynum'] = intval($_POST['buynum']);
	if($_POST['buynum'] < 1) {
		showmessage('bad_buynum');
	}

	if($magicstore['storage'] < $_POST['buynum']) {
		showmessage('magics_num_no_enough');
	}

	$_POST['coupon'] = intval($_POST['coupon']);

	$discard = 0;
	if($_POST['coupon']) {
		if($coupon['count'] < $_POST['coupon']) {
			showmessage('not_enough_coupon');
		}
		$discard = 100 * $_POST['coupon'];
	}

	$discount = checkperm('magicdiscount');
	if($discount > 0) {
		$magic['charge'] = intval($magic['charge'] * $discount / 10);
		if($magic['charge'] < 1) {
			$magic['charge'] = 1;
		}
	} elseif($discount < 0) {
		$magic['charge'] = 0;
	}
	$charge = $_POST['buynum'] * $magic['charge'] - $discard;
	$charge = $charge > 0 ? $charge : 0;
	if($charge > $space['credit']) {
		showmessage('credit_is_not_enough');
	}

	DB::query("UPDATE ".DB::table("magicstore")." SET storage = storage - $_POST[buynum], sellcount = sellcount + $_POST[buynum], sellcredit = sellcredit + $charge WHERE mid = '$mid'");

	$experience = $_POST['buynum'] * intval($magic['experience']);
	$arr = array('credit'=>0-$charge, 'experience'=>0-$experience);
	member_count_update($_G['uid'], $arr);

	$query = DB::query("SELECT * FROM ".DB::table("home_usermagic")." WHERE uid='$_G[uid]' AND mid='$mid'");
	if($value = DB::fetch($query)) {
		$count = $value['count'] + $_POST['buynum'];
	} else {
		$count = $_POST['buynum'];
	}
	DB::query("REPLACE ".DB::table('home_usermagic')."(uid, username, mid, count) VALUES ('$_G[uid]', '$_G[username]', '$mid', '$count')");

	DB::insert('magicinlog',
	array(
	'uid'=>$_G['uid'],
	'username'=>$_G['username'],
	'mid'=>$mid,
	'count'=>$_POST['buynum'],
	'type'=>1,
	'credit'=>$charge,
	'dateline'=>$_G['timestamp']));

	if($_POST['coupon']) {
		DB::query("UPDATE ".DB::table("home_usermagic")." SET count = count - $_POST[coupon] WHERE uid='$_G[uid]' AND mid = 'coupon'");
	}

	return $charge;
}

function magic_check_idtype($id, $idtype) {
	global $_G;

	include_once libfile('function/spacecp');
	$value = '';
	$tablename = gettablebyidtype($idtype);
	if($tablename) {
		$query = DB::query('SELECT * FROM '.DB::table($tablename)." WHERE uid='$_G[uid]' AND $idtype='$id'");
		$value = DB::fetch($query);
	}
	if(empty($value)) {
		showmessage('magicuse_bad_object');
	}
	return $value;
}

function magic_use($mid, $magicuselog=array(), $replace=0) {
	global $_G;

	DB::query('UPDATE '.DB::table('home_usermagic')." SET count = count - 1 WHERE uid = '$_G[uid]' AND mid = '$mid' AND count > 0");

	$value = array();
	if($replace) {
		$where = '';
		if($magicuselog['id']) {
			$where = " AND id='$magicuselog[id]' AND idtype='$magicuselog[idtype]'";
		}
		$query = DB::query('SELECT * FROM '.DB::table('common_magicuselog')." WHERE uid = '$_G[uid]' AND mid = '$mid' $where");
		$value = DB::fetch($query);
	}
	$magicuselog['mid'] = $mid;
	$magicuselog['uid'] = $_G['uid'];
	$magicuselog['username'] = $_G['username'];
	$magicuselog['dateline'] = $_G['timestamp'];
	$magicuselog['count'] = $value['count'] ? $value['count'] + 1 : 1;

	if($value['logid']) {
		DB::update('magicuselog', $magicuselog, array('logid'=>$value['logid']));
	} else {
		DB::insert('magicuselog', $magicuselog);
	}
}

function magic_peroid($magic, $uid) {
	global $_G;
	if($magic['useperoid']) {
		$dateline = 0;
		if($magic['useperoid'] == 1) {
			$dateline = TIMESTAMP - (TIMESTAMP + $_G['setting']['timeoffset'] * 3600) % 86400 + $_G['setting']['timeoffset'] * 3600;
		} elseif($magic['useperoid'] == 4) {
			$dateline = TIMESTAMP - 86400;
		} elseif($magic['useperoid'] == 2) {
			$dateline = TIMESTAMP - 86400 * 7;
		} elseif($magic['useperoid'] == 3) {
			$dateline = TIMESTAMP - 86400 * 30;
		}
		$num = DB::result_first("SELECT count(*) FROM ".DB::table('common_magiclog')." WHERE uid='$uid' AND magicid='$magic[magicid]' AND action='2' AND dateline>'$dateline'");
		return $magic['usenum'] - $num;
	} else {
		return true;
	}
}

?>