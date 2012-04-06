<?php

/*
	[Discuz!] (C)2001-2009 Comsenz Inc111.
	This is NOT a freeware, use is subject to license terms

	$Id: home_magic.php 21217 2011-03-18 10:01:16Z monkey $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

if(!$_G['setting']['creditstransextra'][3]) {
	showmessage('credits_transaction_disabled');
} elseif(!$_G['setting']['magicstatus']) {
	showmessage('magics_close');
}

require_once libfile('function/magic');
loadcache('magics');

$_G['mnid'] = 'mn_common';
$magiclist = array();
$_G['tpp'] = 14;
$page = max(1, intval($_G['gp_page']));
$action = $_G['gp_action'];
$operation = $_G['gp_operation'];
$start_limit = ($page - 1) * $_G['tpp'];

$comma = $typeadd = $filteradd = $forumperm = $targetgroupperm = '';
$magicarray = is_array($_G['cache']['magics']) ? $_G['cache']['magics'] : array();

if(!$_G['uid'] && ($operation || $action == 'mybox')) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}

if(!$_G['group']['allowmagics']) {
	showmessage('magics_nopermission');
}

$totalweight = getmagicweight($_G['uid'], $magicarray);
$allowweight = $_G['group']['maxmagicsweight'] - $totalweight;
$location = 0;

if(empty($action) && !empty($_G['gp_mid'])) {
	$_G['gp_magicid'] = DB::result_first("SELECT m.magicid FROM ".DB::table('common_member_magic')." mm,".DB::table('common_magic')." m
		WHERE mm.uid='$_G[uid]' AND m.identifier='$_G[gp_mid]' AND mm.magicid=m.magicid");
	if($_G['gp_magicid']) {
		$action = 'mybox';
		$operation = 'use';
	} else {
		$action = 'shop';
		$operation = 'buy';
		$location = 1;
	}
}

$action = empty($action) ? 'shop' : $action;
$actives[$action] = ' class="a"';

if($action == 'shop') {

	$operation = empty($operation) ? 'index' : $operation;

	if(in_array($operation, array('index', 'hot'))) {

		$subactives[$operation] = 'class="a"';
		$filteradd = '';
		if($operation == 'index') {
			$navtitle = lang('core', 'title_magics_shop');
			$orderby = "ORDER BY displayorder";
		} else {
			$navtitle = lang('core', 'title_magics_hot');
			$filteradd = "AND salevolume>'0'";
			$orderby = "ORDER BY salevolume DESC";
		}

		$magiccount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_magic')." WHERE available='1' $filteradd");
		$multipage = multi($magiccount, $_G['tpp'], $page, "home.php?mod=magic&action=shop&operation=$operation");

		$query = DB::query("SELECT magicid, name, identifier, description, credit, price, num, salevolume, weight FROM ".DB::table('common_magic')." WHERE available='1' $filteradd $orderby LIMIT $start_limit,$_G[tpp]");
		while($magic = DB::fetch($query)) {
			$magic['discountprice'] = $_G['group']['magicsdiscount'] ? intval($magic['price'] * ($_G['group']['magicsdiscount'] / 10)) : intval($magic['price']);
			$magic['pic'] = strtolower($magic['identifier']).'.gif';
			$magiclist[] = $magic;
		}

		$magiccredits = array();
		foreach($magicarray as $magic) {
			$magiccredits[$magic['credit']] = $magic['credit'];
		}

	} elseif($operation == 'buy') {

		$magic = DB::fetch_first("SELECT * FROM ".DB::table('common_magic')." WHERE identifier='$_G[gp_mid]'");
		if(!$magic || !$magic['available']) {
			showmessage('magics_nonexistence');
		}
		$magicperm = unserialize($magic['magicperm']);
		$querystring = array();
		foreach($_GET as $k => $v) {
			$querystring[] = $k.'='.rawurlencode($v);
		}
		$querystring = implode('&', $querystring);

		if(!@include_once DISCUZ_ROOT.($magicfile = "./source/class/magic/magic_$magic[identifier].php")) {
			showmessage('magics_filename_nonexistence', '', array('file' => $magicfile));
		}
		$magicclass = 'magic_'.$magic['identifier'];
		$magicclass = new $magicclass;
		$magicclass->magic = $magic;
		$magicclass->parameters = $magicperm;
		if(method_exists($magicclass, 'buy')) {
			$magicclass->buy();
		}

		$magic['discountprice'] = $_G['group']['magicsdiscount'] ? intval($magic['price'] * ($_G['group']['magicsdiscount'] / 10)) : intval($magic['price']);
		$magic['pic'] = strtolower($magic['identifier']).".gif";
		$magic['credit'] = $magic['credit'] ? $magic['credit'] : $_G['setting']['creditstransextra'][3];
		$useperoid = magic_peroid($magic, $_G['uid']);

		if(!submitcheck('operatesubmit')) {

			$useperm = (strstr($magicperm['usergroups'], "\t$_G[groupid]\t") || !$magicperm['usergroups']) ? '1' : '0';

			if($magicperm['targetgroups']) {
				loadcache('usergroups');
				foreach(explode("\t", $magicperm['targetgroups']) as $_G['groupid']) {
					if(isset($_G['cache']['usergroups'][$_G['groupid']])) {
						$targetgroupperm .= $comma.$_G['cache']['usergroups'][$_G['groupid']]['grouptitle'];
						$comma = '&nbsp;';
					}
				}
			}

			if($magicperm['forum']) {
				loadcache('forums');
				foreach(explode("\t", $magicperm['forum']) as $fid) {
					if(isset($_G['cache']['forums'][$fid])) {
						$forumperm .= $comma.'<a href="forum.php?mod=forumdisplay&fid='.$fid.'" target="_blank">'.$_G['cache']['forums'][$fid]['name'].'</a>';
						$comma = '&nbsp;';
					}
				}
			}

			include template('home/space_magic_shop_opreation');
			dexit();

		} else {

			$magicnum = intval($_G['gp_magicnum']);
			$magic['weight'] = $magic['weight'] * $magicnum;
			$totalprice = $magic['discountprice'] * $magicnum;

			if(getuserprofile('extcredits'.$magic['credit']) < $totalprice) {
				if($_G['setting']['ec_ratio'] && $_G['setting']['creditstrans'][0] == $magic['credit']) {
					showmessage('magics_credits_no_enough_and_charge', '', array('credit' => $_G['setting']['extcredits'][$magic['credit']]['title']));
				} else {
					showmessage('magics_credits_no_enough', '', array('credit' => $_G['setting']['extcredits'][$magic['credit']]['title']));
				}
			} elseif($magic['num'] < $magicnum) {
				showmessage('magics_num_no_enough');
			} elseif(!$magicnum || $magicnum < 0) {
				showmessage('magics_num_invalid');
			}

			getmagic($magic['magicid'], $magicnum, $magic['weight'], $totalweight, $_G['uid'], $_G['group']['maxmagicsweight']);
			updatemagiclog($magic['magicid'], '1', $magicnum, $magic['price'].'|'.$magic['credit'], $_G['uid']);

			DB::query("UPDATE ".DB::table('common_magic')." SET num=num+(-'$magicnum'), salevolume=salevolume+'$magicnum' WHERE magicid='$magic[magicid]'");
			updatemembercount($_G['uid'], array($magic['credit'] => -$totalprice), true, 'BMC', $magic['magicid']);
			showmessage('magics_buy_succeed', 'home.php?mod=magic&action=mybox', array('magicname' => $magic['name'], 'num' => $magicnum, 'credit' => $totalprice.' '.$_G['setting']['extcredits'][$magic['credit']]['unit'].$_G['setting']['extcredits'][$magic['credit']]['title']));

		}

	} elseif($operation == 'give') {

		if($_G['group']['allowmagics'] < 2) {
			showmessage('magics_nopermission');
		}

		$magic = DB::fetch_first("SELECT * FROM ".DB::table('common_magic')." WHERE identifier='$_G[gp_mid]'");
		if(!$magic || !$magic['available']) {
			showmessage('magics_nonexistence');
		}

		$magic['discountprice'] = $_G['group']['magicsdiscount'] ? intval($magic['price'] * ($_G['group']['magicsdiscount'] / 10)) : intval($magic['price']);
		$magic['pic'] = strtolower($magic['identifier']).".gif";

		if(!submitcheck('operatesubmit')) {

			include libfile('function/friend');
			$buddyarray = friend_list($_G['uid'], 20);
			include template('home/space_magic_shop_opreation');
			dexit();

		} else {

			$magicnum = intval($_G['gp_magicnum']);
			$totalprice = $magic['price'] * $magicnum;

			if(getuserprofile('extcredits'.$magic['credit']) < $totalprice) {
				if($_G['setting']['ec_ratio'] && $_G['setting']['creditstrans'][0] == $magic['credit']) {
					showmessage('magics_credits_no_enough_and_charge', '', array('credit' => $_G['setting']['extcredits'][$magic['credit']]['title']));
				} else {
					showmessage('magics_credits_no_enough', '', array('credit' => $_G['setting']['extcredits'][$magic['credit']]['title']));
				}
			} elseif($magic['num'] < $magicnum) {
				showmessage('magics_num_no_enough');
			} elseif(!$magicnum || $magicnum < 0) {
				showmessage('magics_num_invalid');
			}

			$toname = dhtmlspecialchars(trim($_G['gp_tousername']));
			if(!$toname) {
				showmessage('magics_username_nonexistence');
			}

			$givemessage = dhtmlspecialchars(trim($_G['gp_givemessage']));
			givemagic($toname, $magic['magicid'], $magicnum, $magic['num'], $totalprice, $givemessage, $magicarray);
			DB::query("UPDATE ".DB::table('common_magic')." SET num=num+(-'$magicnum'), salevolume=salevolume+'$magicnum' WHERE magicid='$magicid'");
			updatemembercount($_G['uid'], array($magic['credit'] => -$totalprice), true, 'BMC', $magicid);
			showmessage('magics_buygive_succeed', 'home.php?mod=magic&action=shop', array('magicname' => $magic['name'], 'toname' => $toname, 'num' => $magicnum, 'credit' => $_G['setting']['extcredits'][$magic['credit']]['title'].' '.$totalprice.' '.$_G['setting']['extcredits'][$magic['credit']]['unit']), array('locationtime' => true));

		}

	} else {
		showmessage('undefined_action');
	}

} elseif($action == 'mybox') {

	if(empty($operation)) {

		$pid = !empty($_G['gp_pid']) ? intval($_G['gp_pid']) : 0;
		$magiccount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member_magic')." mm, ".DB::table('common_magic')." m WHERE mm.uid='$_G[uid]' AND mm.magicid=m.magicid");

		$multipage = multi($magiccount, $_G['tpp'], $page, "home.php?mod=magic&action=mybox&pid=$pid$typeadd");
		$query = DB::query("SELECT mm.num, m.magicid, m.name, m.identifier, m.description, m.weight, m.useevent
				FROM ".DB::table('common_member_magic')." mm
				LEFT JOIN ".DB::table('common_magic')." m ON mm.magicid=m.magicid
				WHERE mm.uid='$_G[uid]' LIMIT $start_limit,$_G[tpp]");
		while($mymagic = DB::fetch($query)) {
			$mymagic['pic'] = strtolower($mymagic['identifier']).'.gif';
			$mymagic['weight'] = intval($mymagic['weight'] * $mymagic['num']);
			$mymagic['type'] = $mymagic['type'];
			$mymagiclist[] = $mymagic;
		}
		$navtitle = lang('core', 'title_magics_user');

	} else {

		$magicid = intval($_G['gp_magicid']);
		$magic = DB::fetch_first("SELECT m.*, mm.num
				FROM ".DB::table('common_member_magic')." mm
				LEFT JOIN ".DB::table('common_magic')." m ON mm.magicid=m.magicid
				WHERE mm.uid='$_G[uid]' AND mm.magicid='$magicid'");
		if(!$magic) {
			showmessage('magics_nonexistence');
		} elseif(!$magic['num']) {
			DB::query("DELETE FROM ".DB::table('common_member_magic')." WHERE uid='$_G[uid]' AND magicid='$magic[magicid]'");
			showmessage('magics_nonexistence');
		}
		$magicperm = unserialize($magic['magicperm']);
		$magic['pic'] = strtolower($magic['identifier']).'.gif';

		if($operation == 'use') {

			$useperm = (strstr($magicperm['usergroups'], "\t$_G[groupid]\t") || empty($magicperm['usergroups'])) ? '1' : '0';
			if(!$useperm) {
				showmessage('magics_use_nopermission');
			}

			if($magic['num'] <= 0) {
				DB::query("DELETE FROM ".DB::table('common_member_magic')." WHERE uid='$_G[uid]' AND magicid='$magic[magicid]'");
				showmessage('magics_nopermission');
			}

			$magic['weight'] = intval($magicarray[$magic['magicid']]['weight'] * $magic['num']);

			if(!@include_once DISCUZ_ROOT.($magicfile = "./source/class/magic/magic_$magic[identifier].php")) {
				showmessage('magics_filename_nonexistence', '', array('file' => $magicfile));
			}
			$magicclass = 'magic_'.$magic['identifier'];
			$magicclass = new $magicclass;
			$magicclass->magic = $magic;
			$magicclass->parameters = $magicperm;
			$useperoid = magic_peroid($magic, $_G['uid']);

			if(submitcheck('usesubmit')) {
				if($useperoid !== true && $useperoid <= 0) {
					showmessage('magics_outofperoid_'.$magic['useperoid'], '', array('usenum' => $magic['usenum']));
				}
				if(method_exists($magicclass, 'usesubmit')) {
					$magicclass->usesubmit();
				}
				dexit();
			}

			include template('home/space_magic_mybox_opreation');
			dexit();

		} elseif($operation == 'sell') {

			$discountprice = floor($magic['price'] * $_G['setting']['magicdiscount'] / 100);
			if(!submitcheck('operatesubmit')) {
				include template('home/space_magic_mybox_opreation');
				dexit();
			} else {
				$magicnum = intval($_G['gp_magicnum']);

				if(!$magicnum || $magicnum < 0) {
					showmessage('magics_num_invalid');
				} elseif($magicnum > $magic['num']) {
					showmessage('magics_amount_no_enough');
				}
				usemagic($magic['magicid'], $magic['num'], $magicnum);
				updatemagiclog($magic['magicid'], '2', $magicnum, '0', 0, 'sell');
				$totalprice = $discountprice * $magicnum;
				updatemembercount($_G['uid'], array($magic['credit'] => $totalprice));
				showmessage('magics_sell_succeed', 'home.php?mod=magic&action=mybox', array('magicname' => $magic['name'], 'num' => $magicnum, 'credit' => $totalprice.' '.$_G['setting']['extcredits'][$magic['credit']]['unit'].$_G['setting']['extcredits'][$magic['credit']]['title']));
			}

		} elseif($operation == 'drop') {

			if(!submitcheck('operatesubmit')) {
				include template('home/space_magic_mybox_opreation');
				dexit();
			} else {
				$magicnum = intval($_G['gp_magicnum']);

				if(!$magicnum || $magicnum < 0) {
					showmessage('magics_num_invalid');
				} elseif($magicnum > $magic['num']) {
					showmessage('magics_amount_no_enough');
				}
				usemagic($magic['magicid'], $magic['num'], $magicnum);
				updatemagiclog($magic['magicid'], '2', $magicnum, '0', 0, 'drop');
				showmessage('magics_drop_succeed', 'home.php?mod=magic&action=mybox', array('magicname' => $magic['name'], 'num' => $magicnum), array('locationtime' => true));
			}

		} elseif($operation == 'give') {

			if($_G['group']['allowmagics'] < 2) {
				showmessage('magics_nopermission');
			}

			if(!submitcheck('operatesubmit')) {

				include libfile('function/friend');
				$buddyarray = friend_list($_G['uid'], 20);

				include template('home/space_magic_mybox_opreation');
				dexit();

			} else {

				$magicnum = intval($_G['gp_magicnum']);
				$toname = dhtmlspecialchars(trim($_G['gp_tousername']));
				if(!$toname) {
					showmessage('magics_username_nonexistence');
				} elseif($magic['num'] < $magicnum) {
					showmessage('magics_num_invalid');
				}

				$givemessage = dhtmlspecialchars(trim($_G['gp_givemessage']));
				givemagic($toname, $magic['magicid'], $magicnum, $magic['num'], '0', $givemessage, $magicarray);

			}

		} else {
			showmessage('undefined_action');
		}

	}

} elseif($action == 'log') {

	$subactives[$operation] = 'class="a"';
	$loglist = array();
	if($operation == 'uselog') {
		$query = DB::query("SELECT COUNT(*) FROM ".DB::table('common_magiclog')." WHERE uid='$_G[uid]' AND action='2'");
		$multipage = multi(DB::result($query, 0), $_G['tpp'], $page, 'home.php?mod=magic&action=log&amp;operation=uselog');

		$query = DB::query("SELECT ml.*, me.username FROM ".DB::table('common_magiclog')." ml
			LEFT JOIN ".DB::table('common_member')." me ON me.uid=ml.uid
			WHERE ml.action='2' AND ml.uid='$_G[uid]' ORDER BY ml.dateline DESC
			LIMIT $start_limit, $_G[tpp]");
		while($log = DB::fetch($query)) {
			$log['dateline'] = dgmdate($log['dateline'], 'u');
			$log['name'] = $magicarray[$log['magicid']]['name'];
			$loglist[] = $log;
		}

	} elseif($operation == 'buylog') {
		$query = DB::query("SELECT COUNT(*) FROM ".DB::table('common_magiclog')." WHERE uid='$_G[uid]' AND action='1'");
		$multipage = multi(DB::result($query, 0), $_G['tpp'], $page, 'home.php?mod=magic&action=log&amp;operation=buylog');

		$query = DB::query("SELECT * FROM ".DB::table('common_magiclog')."
			WHERE uid='$_G[uid]' AND action='1' ORDER BY dateline DESC
			LIMIT $start_limit, $_G[tpp]");
		while($log = DB::fetch($query)) {
			$log['credit'] = $log['credit'] ? $log['credit'] : $_G['setting']['creditstransextra'][3];
			$log['dateline'] = dgmdate($log['dateline'], 'u');
			$log['name'] = $magicarray[$log['magicid']]['name'];
			$loglist[] = $log;
		}

	} elseif($operation == 'givelog') {
		$query = DB::query("SELECT COUNT(*) FROM ".DB::table('common_magiclog')." WHERE uid='$_G[uid]' AND action='3'");
		$multipage = multi(DB::result($query, 0), $_G['tpp'], $page, 'home.php?mod=magic&action=log&amp;operation=givelog');

		$query = DB::query("SELECT ml.*, me.username FROM ".DB::table('common_magiclog')." ml
			LEFT JOIN ".DB::table('common_member')." me ON me.uid=ml.targetuid
			WHERE ml.uid='$_G[uid]' AND ml.action='3' ORDER BY ml.dateline DESC
			LIMIT $start_limit, $_G[tpp]");
		while($log = DB::fetch($query)) {
			$log['dateline'] = dgmdate($log['dateline'], 'u');
			$log['name'] = $magicarray[$log['magicid']]['name'];
			$loglist[] = $log;
		}

	} elseif($operation == 'receivelog') {
		$query = DB::query("SELECT COUNT(*) FROM ".DB::table('common_magiclog')." WHERE targetuid='$_G[uid]' AND action='3'");
		$multipage = multi(DB::result($query, 0), $_G['tpp'], $page, 'home.php?mod=magic&action=log&amp;operation=receivelog');

		$query = DB::query("SELECT ml.*, me.username FROM ".DB::table('common_magiclog')." ml
			LEFT JOIN ".DB::table('common_member')." me ON me.uid=ml.uid
			WHERE ml.targetuid='$_G[uid]' AND ml.action='3' ORDER BY ml.dateline DESC
			LIMIT $start_limit, $_G[tpp]");
		while($log = DB::fetch($query)) {
			$log['dateline'] = dgmdate($log['dateline'], 'u');
			$log['name'] = $magicarray[$log['magicid']]['name'];
			$loglist[] = $log;
		}

	}
	$navtitle = lang('core', 'title_magics_log');

} else {
	showmessage('undefined_action');
}

include template('home/space_magic');

?>