<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: post_newthread.php 28920 2012-03-20 01:44:27Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['forum']['fid']) || $_G['forum']['type'] == 'group') {
	showmessage('forum_nonexistence');
}

if(($special == 1 && !$_G['group']['allowpostpoll']) || ($special == 2 && !$_G['group']['allowposttrade']) || ($special == 3 && !$_G['group']['allowpostreward']) || ($special == 4 && !$_G['group']['allowpostactivity']) || ($special == 5 && !$_G['group']['allowpostdebate'])) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

if(!$_G['uid'] && !((!$_G['forum']['postperm'] && $_G['group']['allowpost']) || ($_G['forum']['postperm'] && forumperm($_G['forum']['postperm'])))) {
	if(!defined('IN_MOBILE')) {
		showmessage('postperm_login_nopermission', NULL, array(), array('login' => 1));
	} else {
		showmessage('postperm_login_nopermission_mobile', NULL, array('referer' => rawurlencode(dreferer())), array('login' => 1));
	}
} elseif(empty($_G['forum']['allowpost'])) {
	if(!$_G['forum']['postperm'] && !$_G['group']['allowpost']) {
		showmessage('postperm_none_nopermission', NULL, array(), array('login' => 1));
	} elseif($_G['forum']['postperm'] && !forumperm($_G['forum']['postperm'])) {
		showmessagenoperm('postperm', $_G['fid'], $_G['forum']['formulaperm']);
	}
} elseif($_G['forum']['allowpost'] == -1) {
	showmessage('post_forum_newthread_nopermission', NULL);
}

if(!$_G['uid'] && ($_G['setting']['need_avatar'] || $_G['setting']['need_email'] || $_G['setting']['need_friendnum'])) {
	showmessage('postperm_login_nopermission', NULL, array(), array('login' => 1));
}

checklowerlimit('post', 0, 1, $_G['forum']['fid']);

if(!submitcheck('topicsubmit', 0, $seccodecheck, $secqaacheck)) {

	$savethreads = array();
	$savethreadothers = array();
	$query = DB::query("SELECT dateline, fid, tid, pid, subject FROM ".DB::table(getposttable())." WHERE authorid='$_G[uid]' AND invisible='-3' AND first='1'");
	while($savethread = DB::fetch($query)) {
		$savethread['dateline'] = dgmdate($savethread['dateline'], 'u');
		if($_G['fid'] == $savethread['fid']) {
			$savethreads[] = $savethread;
		} else {
			$savethreadothers[] = $savethread;
		}
	}
	$savethreadcount = count($savethreads);
	$savethreadothercount = count($savethreadothers);
	if($savethreadothercount) {
		loadcache('forums');
	}
	$savecount = $savethreadcount + $savethreadothercount;
	unset($savethread);

	$isfirstpost = 1;
	$allownoticeauthor = 1;
	$tagoffcheck = '';
	$showthreadsorts = !empty($sortid) || $_G['forum']['threadsorts']['required'] && empty($special);
	if(empty($sortid) && empty($special) && $_G['forum']['threadsorts']['required'] && $_G['forum']['threadsorts']['types']) {
		$tmp = array_keys($_G['forum']['threadsorts']['types']);
		$sortid = $tmp[0];

		require_once libfile('post/threadsorts', 'include');
	}

	if($special == 2 && $_G['group']['allowposttrade']) {

		$expiration_7days = date('Y-m-d', TIMESTAMP + 86400 * 7);
		$expiration_14days = date('Y-m-d', TIMESTAMP + 86400 * 14);
		$trade['expiration'] = $expiration_month = date('Y-m-d', mktime(0, 0, 0, date('m')+1, date('d'), date('Y')));
		$expiration_3months = date('Y-m-d', mktime(0, 0, 0, date('m')+3, date('d'), date('Y')));
		$expiration_halfyear = date('Y-m-d', mktime(0, 0, 0, date('m')+6, date('d'), date('Y')));
		$expiration_year = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')+1));

	} elseif($specialextra) {

		$threadpluginclass = null;
		if(isset($_G['setting']['threadplugins'][$specialextra]['module'])) {
			$threadpluginfile = DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
			if(file_exists($threadpluginfile)) {
				@include_once $threadpluginfile;
				$classname = 'threadplugin_'.$specialextra;
				if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread')) {
					$threadplughtml = $threadpluginclass->newthread($_G['fid']);
					$buttontext = lang('plugin/'.$specialextra, $threadpluginclass->buttontext);
					$iconfile = $threadpluginclass->iconfile;
					$iconsflip = array_flip($_G['cache']['icons']);
					$thread['iconid'] = $iconsflip[$iconfile];
				}
			}
		}

		if(!is_object($threadpluginclass)) {
			$specialextra = '';
		}
	}

	if($special == 4) {
		$activity = array('starttimeto' => '', 'starttimefrom' => '', 'place' => '', 'class' => '', 'cost' => '', 'number' => '', 'gender' => '', 'expiration' => '');
		$activitytypelist = $_G['setting']['activitytype'] ? explode("\n", trim($_G['setting']['activitytype'])) : '';
	}

	if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
		$attachlist = getattach(0);
		$attachs = $attachlist['attachs'];
		$imgattachs = $attachlist['imgattachs'];
		unset($attachlist);
	}

	!isset($attachs['unused']) && $attachs['unused'] = array();
	!isset($imgattachs['unused']) && $imgattachs['unused'] = array();

	getgpc('infloat') ? include template('forum/post_infloat') : include template('forum/post');

} else {

	if(trim($subject) == '') {
		showmessage('post_sm_isnull');
	}

	if(!$sortid && !$special && trim($message) == '') {
		showmessage('post_sm_isnull');
	}

	if($post_invalid = checkpost($subject, $message, ($special || $sortid))) {
		showmessage($post_invalid, '', array('minpostsize' => $_G['setting']['minpostsize'], 'maxpostsize' => $_G['setting']['maxpostsize']));
	}

	if(checkflood()) {
		showmessage('post_flood_ctrl', '', array('floodctrl' => $_G['setting']['floodctrl']));
	} elseif(checkmaxpostsperhour()) {
		showmessage('post_flood_ctrl_posts_per_hour', '', array('posts_per_hour' => $_G['group']['maxpostsperhour']));
	}
	$_G['gp_save'] = $_G['uid'] ? $_G['gp_save'] : 0;

	$typeid = isset($typeid) && isset($_G['forum']['threadtypes']['types'][$typeid]) && (empty($_G['forum']['threadtypes']['moderators'][$typeid]) || $_G['forum']['ismoderator']) ? $typeid : 0;
	$displayorder = $modnewthreads ? -2 : (($_G['forum']['ismoderator'] && $_G['group']['allowstickthread'] && !empty($_G['gp_sticktopic'])) ? 1 : (empty($_G['gp_save']) ? 0 : -4));
	if($displayorder == -2) {
		DB::update('forum_forum', array('modworks' => '1'), "fid='{$_G['fid']}'");
	} elseif($displayorder == -4) {
		$_G['gp_addfeed'] = 0;
	}
	$digest = $_G['forum']['ismoderator'] && $_G['group']['allowdigestthread'] && !empty($_G['gp_addtodigest']) ? 1 : 0;
	$readperm = $_G['group']['allowsetreadperm'] ? $readperm : 0;
	$isanonymous = $_G['group']['allowanonymous'] && $_G['gp_isanonymous'] ? 1 : 0;
	$price = intval($price);
	$price = $_G['group']['maxprice'] && !$special ? ($price <= $_G['group']['maxprice'] ? $price : $_G['group']['maxprice']) : 0;

	if(!$typeid && $_G['forum']['threadtypes']['required'] && !$special) {
		showmessage('post_type_isnull');
	}

	if(!$sortid && $_G['forum']['threadsorts']['required'] && !$special) {
		showmessage('post_sort_isnull');
	}

	if($price > 0 && floor($price * (1 - $_G['setting']['creditstax'])) == 0) {
		showmessage('post_net_price_iszero');
	}

	if($special == 1) {

		$polloption = $_G['gp_tpolloption'] == 2 ? explode("\n", $_G['gp_polloptions']) : $_G['gp_polloption'];
		$pollarray = array();
		foreach($polloption as $key => $value) {
			$polloption[$key] = censor($polloption[$key]);
			if(trim($value) === '') {
				unset($polloption[$key]);
			}
		}

		if(count($polloption) > $_G['setting']['maxpolloptions']) {
			showmessage('post_poll_option_toomany', '', array('maxpolloptions' => $_G['setting']['maxpolloptions']));
		} elseif(count($polloption) < 2) {
			showmessage('post_poll_inputmore');
		}

		$curpolloption = count($polloption);
		$pollarray['maxchoices'] = empty($_G['gp_maxchoices']) ? 0 : ($_G['gp_maxchoices'] > $curpolloption ? $curpolloption : $_G['gp_maxchoices']);
		$pollarray['multiple'] = empty($_G['gp_maxchoices']) || $_G['gp_maxchoices'] == 1 ? 0 : 1;
		$pollarray['options'] = $polloption;
		$pollarray['visible'] = empty($_G['gp_visibilitypoll']);
		$pollarray['overt'] = !empty($_G['gp_overt']);

		if(preg_match("/^\d*$/", trim($_G['gp_expiration']))) {
			if(empty($_G['gp_expiration'])) {
				$pollarray['expiration'] = 0;
			} else {
				$pollarray['expiration'] = TIMESTAMP + 86400 * $_G['gp_expiration'];
			}
		} else {
			showmessage('poll_maxchoices_expiration_invalid');
		}

	} elseif($special == 3) {

		$rewardprice = intval($_G['gp_rewardprice']);
		if($rewardprice < 1) {
			showmessage('reward_credits_please');
		} elseif($rewardprice > 32767) {
			showmessage('reward_credits_overflow');
		} elseif($rewardprice < $_G['group']['minrewardprice'] || ($_G['group']['maxrewardprice'] > 0 && $rewardprice > $_G['group']['maxrewardprice'])) {
			if($_G['group']['maxrewardprice'] > 0) {
				showmessage('reward_credits_between', '', array('minrewardprice' => $_G['group']['minrewardprice'], 'maxrewardprice' => $_G['group']['maxrewardprice']));
			} else {
				showmessage('reward_credits_lower', '', array('minrewardprice' => $_G['group']['minrewardprice']));
			}
		} elseif(($realprice = $rewardprice + ceil($rewardprice * $_G['setting']['creditstax'])) > getuserprofile('extcredits'.$_G['setting']['creditstransextra'][2])) {
			showmessage('reward_credits_shortage');
		}
		$price = $rewardprice;

	} elseif($special == 4) {

		$activitytime = intval($_G['gp_activitytime']);
		if(empty($_G['gp_starttimefrom'][$activitytime])) {
			showmessage('activity_fromtime_please');
		} elseif(@strtotime($_G['gp_starttimefrom'][$activitytime]) === -1 || @strtotime($_G['gp_starttimefrom'][$activitytime]) === FALSE) {
			showmessage('activity_fromtime_error');
		} elseif($activitytime && ((@strtotime($_G['gp_starttimefrom']) > @strtotime($_G['gp_starttimeto']) || !$_G['gp_starttimeto']))) {
			showmessage('activity_fromtime_error');
		} elseif(!trim($_G['gp_activityclass'])) {
			showmessage('activity_sort_please');
		} elseif(!trim($_G['gp_activityplace'])) {
			showmessage('activity_address_please');
		} elseif(trim($_G['gp_activityexpiration']) && (@strtotime($_G['gp_activityexpiration']) === -1 || @strtotime($_G['gp_activityexpiration']) === FALSE)) {
			showmessage('activity_totime_error');
		}

		$activity = array();
		$activity['class'] = censor(dhtmlspecialchars(trim($_G['gp_activityclass'])));
		$activity['starttimefrom'] = @strtotime($_G['gp_starttimefrom'][$activitytime]);
		$activity['starttimeto'] = $activitytime ? @strtotime($_G['gp_starttimeto']) : 0;
		$activity['place'] = censor(dhtmlspecialchars(trim($_G['gp_activityplace'])));
		$activity['cost'] = intval($_G['gp_cost']);
		$activity['gender'] = intval($_G['gp_gender']);
		$activity['number'] = intval($_G['gp_activitynumber']);

		if($_G['gp_activityexpiration']) {
			$activity['expiration'] = @strtotime($_G['gp_activityexpiration']);
		} else {
			$activity['expiration'] = 0;
		}
		if(trim($_G['gp_activitycity'])) {
			$subject .= '['.dhtmlspecialchars(trim($_G['gp_activitycity'])).']';
		}
		$extfield = $_G['gp_extfield'];
		$extfield = explode("\n", $_G['gp_extfield']);
		foreach($extfield as $key => $value) {
			$extfield[$key] = censor(trim($value));
			if($extfield[$key] === '' || is_numeric($extfield[$key])) {
				unset($extfield[$key]);
			}
		}
		$extfield = array_unique($extfield);
		if(count($extfield) > $_G['setting']['activityextnum']) {
			showmessage('post_activity_extfield_toomany', '', array('maxextfield' => $_G['setting']['activityextnum']));
		}
		$activity['ufield'] = array('userfield' => $_G['gp_userfield'], 'extfield' => $extfield);
		$activity['ufield'] = serialize($activity['ufield']);
		if(intval($_G['gp_activitycredit']) > 0) {
			$activity['credit'] = intval($_G['gp_activitycredit']);
		}
	} elseif($special == 5) {

		if(empty($_G['gp_affirmpoint']) || empty($_G['gp_negapoint'])) {
			showmessage('debate_position_nofound');
		} elseif(!empty($_G['gp_endtime']) && (!($endtime = @strtotime($_G['gp_endtime'])) || $endtime < TIMESTAMP)) {
			showmessage('debate_endtime_invalid');
		} elseif(!empty($_G['gp_umpire'])) {
			if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member')." WHERE username='$_G[gp_umpire]'")) {
				$_G['gp_umpire'] = dhtmlspecialchars($_G['gp_umpire']);
				showmessage('debate_umpire_invalid', '', array('umpire' => $umpire));
			}
		}
		$affirmpoint = censor(dhtmlspecialchars($_G['gp_affirmpoint']));
		$negapoint = censor(dhtmlspecialchars($_G['gp_negapoint']));
		$stand = censor(intval($_G['gp_stand']));

	} elseif($specialextra) {

		@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread_submit')) {
			$threadpluginclass->newthread_submit($_G['fid']);
		}
		$special = 127;

	}

	$sortid = $special && $_G['forum']['threadsorts']['types'][$sortid] ? 0 : $sortid;
	$typeexpiration = intval($_G['gp_typeexpiration']);

	if($_G['forum']['threadsorts']['expiration'][$typeid] && !$typeexpiration) {
		showmessage('threadtype_expiration_invalid');
	}

	$_G['forum_optiondata'] = array();
	if($_G['forum']['threadsorts']['types'][$sortid] && !$_G['forum']['allowspecialonly']) {
		$_G['forum_optiondata'] = threadsort_validator($_G['gp_typeoption'], $pid);
	}

	$author = !$isanonymous ? $_G['username'] : '';

	$moderated = $digest || $displayorder > 0 ? 1 : 0;

	$thread['status'] = 0;

	$_G['gp_ordertype'] && $thread['status'] = setstatus(4, 1, $thread['status']);

	$_G['gp_hiddenreplies'] && $thread['status'] = setstatus(2, 1, $thread['status']);

	if($_G['group']['allowpostrushreply'] && $_G['gp_rushreply']) {
		$_G['gp_rushreplyfrom'] = strtotime($_G['gp_rushreplyfrom']);
		$_G['gp_rushreplyto'] = strtotime($_G['gp_rushreplyto']);
		$_G['gp_rewardfloor'] = trim($_G['gp_rewardfloor']);
		$_G['gp_stopfloor'] = intval($_G['gp_stopfloor']);
		if($_G['gp_rushreplyfrom'] > $_G['gp_rushreplyto'] && !empty($_G['gp_rushreplyto'])) {
			showmessage('post_rushreply_timewrong');
		}
		if(($_G['gp_rushreplyfrom'] > $_G['timestamp']) || (!empty($_G['gp_rushreplyto']) && $_G['gp_rushreplyto'] < $_G['timestamp']) || ($_G['gp_stopfloor'] == 1) ) {
			$closed = true;
		}
		if(!empty($_G['gp_rewardfloor']) && !empty($_G['gp_stopfloor'])) {
			$floors = explode(',', $_G['gp_rewardfloor']);
			if(!empty($floors) && is_array($floors)) {
				foreach($floors AS $key => $floor) {
					if(strpos($floor, '*') === false) {
						if(intval($floor) == 0) {
							unset($floors[$key]);
						} elseif($floor > $_G['gp_stopfloor']) {
							unset($floors[$key]);
						}
					}
				}
				$_G['gp_rewardfloor'] = implode(',', $floors);
			}
		}
		$thread['status'] = setstatus(3, 1, $thread['status']);
		$thread['status'] = setstatus(1, 1, $thread['status']);
	}

	$_G['gp_allownoticeauthor'] && $thread['status'] = setstatus(6, 1, $thread['status']);
	$isgroup = $_G['forum']['status'] == 3 ? 1 : 0;

	if($_G['group']['allowreplycredit']) {
		$_G['gp_replycredit_extcredits'] = intval($_G['gp_replycredit_extcredits']);
		$_G['gp_replycredit_times'] = intval($_G['gp_replycredit_times']);
		$_G['gp_replycredit_membertimes'] = intval($_G['gp_replycredit_membertimes']);
		$_G['gp_replycredit_random'] = intval($_G['gp_replycredit_random']);

		$_G['gp_replycredit_random'] = $_G['gp_replycredit_random'] < 0 || $_G['gp_replycredit_random'] > 99 ? 0 : $_G['gp_replycredit_random'] ;
		$replycredit = $replycredit_real = 0;
		if($_G['gp_replycredit_extcredits'] > 0 && $_G['gp_replycredit_times'] > 0) {
			$replycredit_real = ceil(($_G['gp_replycredit_extcredits'] * $_G['gp_replycredit_times']) + ($_G['gp_replycredit_extcredits'] * $_G['gp_replycredit_times'] *  $_G['setting']['creditstax']));
			if($replycredit_real > getuserprofile('extcredits'.$_G['setting']['creditstransextra'][10])) {
				showmessage('replycredit_morethan_self');
			} else {
				$replycredit = ceil($_G['gp_replycredit_extcredits'] * $_G['gp_replycredit_times']);
			}
		}
	}


	DB::query("INSERT INTO ".DB::table('forum_thread')." (fid, posttableid, readperm, price, typeid, sortid, author, authorid, subject, dateline, lastpost, lastposter, displayorder, digest, special, attachment, moderated, status, isgroup, replycredit, closed)
		VALUES ('$_G[fid]', '0', '$readperm', '$price', '$typeid', '$sortid', '$author', '$_G[uid]', '$subject', '$_G[timestamp]', '$_G[timestamp]', '$author', '$displayorder', '$digest', '$special', '0', '$moderated', '$thread[status]', '$isgroup', '$replycredit', '".($closed ? "1" : '0')."')");
	$tid = DB::insert_id();
	useractionlog($_G['uid'], 'tid');


	DB::update('common_member_field_home', array('recentnote'=>$subject), array('uid'=>$_G['uid']));

	if($special == 3 && $_G['group']['allowpostreward']) {
		updatemembercount($_G['uid'], array($_G['setting']['creditstransextra'][2] => -$realprice), 1, 'RTC', $tid);
	}

	if($moderated) {
		updatemodlog($tid, ($displayorder > 0 ? 'STK' : 'DIG'));
		updatemodworks(($displayorder > 0 ? 'STK' : 'DIG'), 1);
	}

	if($special == 1) {

		foreach($pollarray['options'] as $polloptvalue) {
			$polloptvalue = dhtmlspecialchars(trim($polloptvalue));
			DB::query("INSERT INTO ".DB::table('forum_polloption')." (tid, polloption) VALUES ('$tid', '$polloptvalue')");
		}
		$polloptionpreview = '';
		$query = DB::query("SELECT polloption FROM ".DB::table('forum_polloption')." WHERE tid='$tid' ORDER BY displayorder LIMIT 2");
		while($option = DB::fetch($query)) {
			$polloptvalue = preg_replace("/\[url=(https?){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i", "<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $option['polloption']);
			$polloptionpreview .= $polloptvalue."\t";
		}

		$polloptionpreview = daddslashes($polloptionpreview);

		DB::query("INSERT INTO ".DB::table('forum_poll')." (tid, multiple, visible, maxchoices, expiration, overt, pollpreview)
			VALUES ('$tid', '$pollarray[multiple]', '$pollarray[visible]', '$pollarray[maxchoices]', '$pollarray[expiration]', '$pollarray[overt]', '$polloptionpreview')");

	} elseif($special == 4 && $_G['group']['allowpostactivity']) {
		DB::query("INSERT INTO ".DB::table('forum_activity')." (tid, uid, cost, starttimefrom, starttimeto, place, class, gender, number, expiration, aid, ufield, credit)
			VALUES ('$tid', '$_G[uid]', '$activity[cost]', '$activity[starttimefrom]', '$activity[starttimeto]', '$activity[place]', '$activity[class]', '$activity[gender]', '$activity[number]', '$activity[expiration]', '$_G[gp_activityaid]', '$activity[ufield]', '$activity[credit]')");

	} elseif($special == 5 && $_G['group']['allowpostdebate']) {

		DB::query("INSERT INTO ".DB::table('forum_debate')." (tid, uid, starttime, endtime, affirmdebaters, negadebaters, affirmvotes, negavotes, umpire, winner, bestdebater, affirmpoint, negapoint, umpirepoint)
			VALUES ('$tid', '$_G[uid]', '$_G[timestamp]', '$endtime', '0', '0', '0', '0', '$_G[gp_umpire]', '', '', '$affirmpoint', '$negapoint', '')");

	} elseif($special == 127) {

		$message .= chr(0).chr(0).chr(0).$specialextra;

	}

	if($_G['forum']['threadsorts']['types'][$sortid] && !empty($_G['forum_optiondata']) && is_array($_G['forum_optiondata'])) {
		$filedname = $valuelist = $separator = '';
		foreach($_G['forum_optiondata'] as $optionid => $value) {
			if($value) {
				$filedname .= $separator.$_G['forum_optionlist'][$optionid]['identifier'];
				$valuelist .= $separator."'".daddslashes($value)."'";
				$separator = ' ,';
			}

			if($_G['forum_optionlist'][$optionid]['type'] == 'image') {
				$identifier = $_G['forum_optionlist'][$optionid]['identifier'];
				$sortaids[] = intval($_G['gp_typeoption'][$identifier]['aid']);
			}

			DB::query("INSERT INTO ".DB::table('forum_typeoptionvar')." (sortid, tid, fid, optionid, value, expiration)
				VALUES ('$sortid', '$tid', '$_G[fid]', '$optionid', '".daddslashes($value)."', '".($typeexpiration ? TIMESTAMP + $typeexpiration : 0)."')");
		}

		if($filedname && $valuelist) {
			DB::query("INSERT INTO ".DB::table('forum_optionvalue')."$sortid ($filedname, tid, fid) VALUES ($valuelist, '$tid', '$_G[fid]')");
		}
	}

	$bbcodeoff = checkbbcodes($message, !empty($_G['gp_bbcodeoff']));
	$smileyoff = checksmilies($message, !empty($_G['gp_smileyoff']));
	$parseurloff = !empty($_G['gp_parseurloff']);
	$htmlon = $_G['group']['allowhtml'] && !empty($_G['gp_htmlon']) ? 1 : 0;
	$usesig = !empty($_G['gp_usesig']) && $_G['group']['maxsigsize'] ? 1 : 0;

	$tagstr = addthreadtag($_G['gp_tags'], $tid);

	if($_G['group']['allowreplycredit']) {
		if($replycredit > 0 && $replycredit_real > 0) {
			updatemembercount($_G['uid'], array('extcredits'.$_G['setting']['creditstransextra'][10] => -$replycredit_real), 1, 'RCT', $tid);
			DB::query("INSERT INTO ".DB::table('forum_replycredit')." (tid, extcredits, extcreditstype, times, membertimes, random)VALUES('$tid', '$_G[gp_replycredit_extcredits]', '{$_G[setting][creditstransextra][10]}', '$_G[gp_replycredit_times]', '$_G[gp_replycredit_membertimes]', '$_G[gp_replycredit_random]')");
		}
	}

	if($_G['group']['allowpostrushreply'] && $_G['gp_rushreply']) {
		DB::query("INSERT INTO ".DB::table('forum_threadrush')." (tid, stopfloor, starttimefrom, starttimeto, rewardfloor) VALUES ('$tid', '$_G[gp_stopfloor]', '$_G[gp_rushreplyfrom]', '$_G[gp_rushreplyto]', '$_G[gp_rewardfloor]')");
	}

	$pinvisible = $modnewthreads ? -2 : (empty($_G['gp_save']) ? 0 : -3);
	$message = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $message);
	$pid = insertpost(array(
		'fid' => $_G['fid'],
		'tid' => $tid,
		'first' => '1',
		'author' => $_G['username'],
		'authorid' => $_G['uid'],
		'subject' => $subject,
		'dateline' => $_G['timestamp'],
		'message' => $message,
		'useip' => $_G['clientip'],
		'invisible' => $pinvisible,
		'anonymous' => $isanonymous,
		'usesig' => $usesig,
		'htmlon' => $htmlon,
		'bbcodeoff' => $bbcodeoff,
		'smileyoff' => $smileyoff,
		'parseurloff' => $parseurloff,
		'attachment' => '0',
		'tags' => $tagstr,
		'replycredit' => 0,
		'status' => (defined('IN_MOBILE') ? 8 : 0)
	));

	if($pid && getstatus($thread['status'], 1)) {
		savepostposition($tid, $pid);
	}
	$threadimageaid = 0;
	$threadimage = array();
	if($special == 4 && $_G['gp_activityaid']) {
		$threadimageaid = $_G['gp_activityaid'];
		convertunusedattach($_G['gp_activityaid'], $tid, $pid);
	}

	if($_G['forum']['threadsorts']['types'][$sortid] && !empty($_G['forum_optiondata']) && is_array($_G['forum_optiondata']) && $sortaids) {
		foreach($sortaids as $sortaid) {
			convertunusedattach($sortaid, $tid, $pid);
		}
	}

	if(($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) && ($_G['gp_attachnew'] || $sortid || !empty($_G['gp_activityaid']))) {
		updateattach($displayorder == -4 || $modnewthreads, $tid, $pid, $_G['gp_attachnew']);
		if(!$threadimageaid) {
			$threadimage = DB::fetch_first("SELECT aid, attachment, remote FROM ".DB::table(getattachtablebytid($tid))." WHERE tid='$tid' AND isimage IN ('1', '-1') ORDER BY width DESC LIMIT 1");
			$threadimageaid = $threadimage['aid'];
		}
		if($_G['forum']['picstyle']) {
			setthreadcover($pid, 0, $threadimageaid);
		}
	}

	if($threadimageaid) {
		if(!$threadimage) {
			$threadimage = DB::fetch_first("SELECT attachment, remote FROM ".DB::table(getattachtablebytid($tid))." WHERE aid='$threadimageaid'");
		}
		$threadimage = daddslashes($threadimage);
		DB::insert('forum_threadimage', array(
			'tid' => $tid,
			'attachment' => $threadimage['attachment'],
			'remote' => $threadimage['remote'],
		));
	}

	$param = array('fid' => $_G['fid'], 'tid' => $tid, 'pid' => $pid);

	$statarr = array(0 => 'thread', 1 => 'poll', 2 => 'trade', 3 => 'reward', 4 => 'activity', 5 => 'debate', 127 => 'thread');
	include_once libfile('function/stat');
	updatestat($isgroup ? 'groupthread' : $statarr[$special]);

	dsetcookie('clearUserdata', 'forum');

	if($specialextra) {

		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread_submit_end')) {
			$threadpluginclass->newthread_submit_end($_G['fid'], $tid);
		}

	}

	if($modnewthreads) {
		updatemoderate('tid', $tid);
		DB::query("UPDATE ".DB::table('forum_forum')." SET todayposts=todayposts+1 WHERE fid='$_G[fid]'", 'UNBUFFERED');
		manage_addnotify('verifythread');
		showmessage('post_newthread_mod_succeed', "forum.php?mod=viewthread&tid=$tid&extra=$extra", $param);
	} else {

		$feed = array(
			'icon' => '',
			'title_template' => '',
			'title_data' => array(),
			'body_template' => '',
			'body_data' => array(),
			'title_data'=>array(),
			'images'=>array()
		);

		if(!empty($_G['gp_addfeed']) && $_G['forum']['allowfeed'] && !$isanonymous) {
			$message = !($price || $readperm) ? $message : '';
			if($special == 0) {
				$feed['icon'] = 'thread';
				$feed['title_template'] = 'feed_thread_title';
				$feed['body_template'] = 'feed_thread_message';
				$feed['body_data'] = array(
					'subject' => "<a href=\"forum.php?mod=viewthread&tid=$tid\">$subject</a>",
					'message' => messagecutstr($message, 150)
				);
				if(!empty($_G['forum_attachexist'])) {
					$firstaid = DB::result_first("SELECT aid FROM ".DB::table(getattachtablebytid($tid))." WHERE pid='$pid' AND dateline>'0' AND isimage='1' ORDER BY dateline LIMIT 1");
					if($firstaid) {
						$feed['images'] = array(getforumimg($firstaid));
						$feed['image_links'] = array("forum.php?mod=viewthread&do=tradeinfo&tid=$tid&pid=$pid");
					}
				}
			} elseif($special > 0) {
				if($special == 1) {
					$pvs = explode("\t", messagecutstr($polloptionpreview, 150));
					$s = '';
					$i = 1;
					foreach($pvs as $pv) {
						$s .= $i.'. '.$pv.'<br />';
					}
					$s .= '&nbsp;&nbsp;&nbsp;...';
					$feed['icon'] = 'poll';
					$feed['title_template'] = 'feed_thread_poll_title';
					$feed['body_template'] = 'feed_thread_poll_message';
					$feed['body_data'] = array(
						'subject' => "<a href=\"forum.php?mod=viewthread&tid=$tid\">$subject</a>",
						'message' => $s
					);
				} elseif($special == 3) {
					$feed['icon'] = 'reward';
					$feed['title_template'] = 'feed_thread_reward_title';
					$feed['body_template'] = 'feed_thread_reward_message';
					$feed['body_data'] = array(
						'subject'=> "<a href=\"forum.php?mod=viewthread&tid=$tid\">$subject</a>",
						'rewardprice'=> $rewardprice,
						'extcredits' => $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][2]]['title'],
					);
				} elseif($special == 4) {
					$feed['icon'] = 'activity';
					$feed['title_template'] = 'feed_thread_activity_title';
					$feed['body_template'] = 'feed_thread_activity_message';
					$feed['body_data'] = array(
						'subject' => "<a href=\"forum.php?mod=viewthread&tid=$tid\">$subject</a>",
						'starttimefrom' => $_G['gp_starttimefrom'][$activitytime],
						'activityplace'=> $activity['place'],
						'message' => messagecutstr($message, 150),
					);
					if($_G['gp_activityaid']) {
						$feed['images'] = array(getforumimg($_G['gp_activityaid']));
						$feed['image_links'] = array("forum.php?mod=viewthread&do=tradeinfo&tid=$tid&pid=$pid");
					}
				} elseif($special == 5) {
					$feed['icon'] = 'debate';
					$feed['title_template'] = 'feed_thread_debate_title';
					$feed['body_template'] = 'feed_thread_debate_message';
					$feed['body_data'] = array(
						'subject' => "<a href=\"forum.php?mod=viewthread&tid=$tid\">$subject</a>",
						'message' => messagecutstr($message, 150),
						'affirmpoint'=> messagecutstr($affirmpoint, 150),
						'negapoint'=> messagecutstr($negapoint, 150)
					);
				}
			}

			$feed['title_data']['hash_data'] = "tid{$tid}";
			$feed['id'] = $tid;
			$feed['idtype'] = 'tid';
			if($feed['icon']) {
				postfeed($feed);
			}
		}

		if($displayorder != -4) {
			if($digest) {
				updatepostcredits('+',  $_G['uid'], 'digest', $_G['fid']);
			}
			updatepostcredits('+',  $_G['uid'], 'post', $_G['fid']);
			if($isgroup) {
				DB::query("UPDATE ".DB::table('forum_groupuser')." SET threads=threads+1, lastupdate='".TIMESTAMP."' WHERE uid='$_G[uid]' AND fid='$_G[fid]'");
			}

			$subject = str_replace("\t", ' ', $subject);
			$lastpost = "$tid\t$subject\t$_G[timestamp]\t$author";
			DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost', threads=threads+1, posts=posts+1, todayposts=todayposts+1 WHERE fid='$_G[fid]'", 'UNBUFFERED');
			if($_G['forum']['type'] == 'sub') {
				DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost' WHERE fid='".$_G['forum'][fup]."'", 'UNBUFFERED');
			}
		}

		if($_G['forum']['status'] == 3) {
			require_once libfile('function/group');
			updateactivity($_G['fid'], 0);
			require_once libfile('function/grouplog');
			updategroupcreditlog($_G['fid'], $_G['uid']);
		}

		showmessage('post_newthread_succeed', "forum.php?mod=viewthread&tid=$tid&extra=$extra", $param);

	}
}

?>