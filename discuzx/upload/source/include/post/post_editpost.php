<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: post_editpost.php 29004 2012-03-22 04:04:58Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
if(($special == 1 && !$_G['group']['allowpostpoll']) || ($special == 2 && !$_G['group']['allowposttrade']) || ($special == 3 && !$_G['group']['allowpostreward']) || ($special == 4 && !$_G['group']['allowpostactivity']) || ($special == 5 && !$_G['group']['allowpostdebate'])) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}
$posttable = getposttablebytid($_G['tid']);
$orig = DB::fetch_first("SELECT m.adminid, p.first, p.authorid, p.author, p.dateline, p.anonymous, p.invisible, p.htmlon FROM ".DB::table($posttable)." p
	LEFT JOIN ".DB::table('common_member')." m ON m.uid=p.authorid
	WHERE pid='$pid' AND tid='$_G[tid]' AND fid='$_G[fid]'");

if($_G['setting']['magicstatus']) {
	$magicid = DB::result_first("SELECT magicid FROM ".DB::table('forum_threadmod')." WHERE tid='$_G[tid]' AND magicid='10'");
	$_G['group']['allowanonymous'] = $_G['group']['allowanonymous'] || $magicid ? 1 : $_G['group']['allowanonymous'];
}

$isfirstpost = $orig['first'] ? 1 : 0;
$isorigauthor = $_G['uid'] && $_G['uid'] == $orig['authorid'];
$isanonymous = $_G['group']['allowanonymous'] && getgpc('isanonymous') ? 1 : 0;
$audit = $orig['invisible'] == -2 || $thread['displayorder'] == -2 ? $_G['gp_audit'] : 0;

if(empty($orig)) {
	showmessage('post_nonexistence');
} elseif((!$_G['forum']['ismoderator'] || !$_G['group']['alloweditpost'] || (in_array($orig['adminid'], array(1, 2, 3)) && $_G['adminid'] > $orig['adminid'])) && !(($_G['forum']['alloweditpost'] || $orig['invisible'] == -3)&& $isorigauthor)) {
	showmessage('post_edit_nopermission', NULL);
} elseif($isorigauthor && !$_G['forum']['ismoderator'] && $orig['invisible'] != -3) {
	$alloweditpost_status = getstatus($_G['setting']['alloweditpost'], $special + 1);
	if(!$alloweditpost_status && $_G['group']['edittimelimit'] && TIMESTAMP - $orig['dateline'] > $_G['group']['edittimelimit'] * 60) {
		showmessage('post_edit_timelimit', NULL, array('edittimelimit' => $_G['group']['edittimelimit']));
	}
}

$thread['pricedisplay'] = $thread['price'] == -1 ? 0 : $thread['price'];

if($special == 5) {
	$debate = array_merge($thread, daddslashes(DB::fetch_first("SELECT * FROM ".DB::table('forum_debate')." WHERE tid='$_G[tid]'"), 1));
	$firststand = DB::result_first("SELECT stand FROM ".DB::table('forum_debatepost')." WHERE tid='$_G[tid]' AND uid='$_G[uid]' AND stand>'0' ORDER BY dateline LIMIT 1");

	if(!$isfirstpost && $debate['endtime'] && $debate['endtime'] < TIMESTAMP && !$_G['forum']['ismoderator']) {
		showmessage('debate_end');
	}
	if($isfirstpost && $debate['umpirepoint'] && !$_G['forum']['ismoderator']) {
		showmessage('debate_umpire_comment_invalid');
	}
}

$rushreply = getstatus($thread['status'], 3);

$savepostposition = getstatus($thread['status'], 1);

if($isfirstpost && $isorigauthor && $_G['group']['allowreplycredit']) {
	if($replycredit_rule = DB::fetch_first("SELECT * FROM ".DB::table('forum_replycredit')." WHERE tid = '$_G[tid]' LIMIT 1")) {
		if($thread['replycredit']) {
			$replycredit_rule['lasttimes'] = $thread['replycredit'] / $replycredit_rule['extcredits'];
		}
		$replycredit_rule['extcreditstype'] = $replycredit_rule['extcreditstype'] ? $replycredit_rule['extcreditstype'] : $_G['setting']['creditstransextra'][10];
	}
}

if(!submitcheck('editsubmit')) {

	$thread['hiddenreplies'] = getstatus($thread['status'], 2);


	$postinfo = DB::fetch_first("SELECT * FROM ".DB::table($posttable)." WHERE pid='$pid' AND tid='$_G[tid]' AND fid='$_G[fid]'");

	$usesigcheck = $postinfo['usesig'] ? 'checked="checked"' : '';
	$urloffcheck = $postinfo['parseurloff'] ? 'checked="checked"' : '';
	$smileyoffcheck = $postinfo['smileyoff'] == 1 ? 'checked="checked"' : '';
	$codeoffcheck = $postinfo['bbcodeoff'] == 1 ? 'checked="checked"' : '';
	$tagoffcheck = $postinfo['htmlon'] & 2 ? 'checked="checked"' : '';
	$htmloncheck = $postinfo['htmlon'] & 1 ? 'checked="checked"' : '';
	$showthreadsorts = ($thread['sortid'] || !empty($sortid)) && $isfirstpost;
	$sortid = empty($sortid) ? $thread['sortid'] : $sortid;

	$poll = $temppoll = '';
	if($isfirstpost) {
		if($postinfo['tags']) {
			$tagarray_all = $array_temp = $threadtag_array = array();
			$tagarray_all = explode("\t", $postinfo['tags']);
			if($tagarray_all) {
				foreach($tagarray_all as $var) {
					if($var) {
						$array_temp = explode(',', $var);
						$threadtag_array[] = $array_temp['1'];
					}
				}
			}
			$postinfo['tag'] = implode(',', $threadtag_array);
		}
		$allownoticeauthor = getstatus($thread['status'], 6);

		if($rushreply) {
			$postinfo['rush'] = DB::fetch_first("SELECT * FROM ".DB::table('forum_threadrush')." WHERE tid='$_G[tid]'");
			$postinfo['rush']['stopfloor'] = $postinfo['rush']['stopfloor'] ? $postinfo['rush']['stopfloor'] : '';
			$postinfo['rush']['starttimefrom'] = $postinfo['rush']['starttimefrom'] ? dgmdate($postinfo['rush']['starttimefrom'], 'Y-m-d H:i') : '';
			$postinfo['rush']['starttimeto'] = $postinfo['rush']['starttimeto'] ? dgmdate($postinfo['rush']['starttimeto'], 'Y-m-d H:i') : '';
		}

		if($special == 127) {
			$sppos = strpos($postinfo['message'], chr(0).chr(0).chr(0));
			$specialextra = substr($postinfo['message'], $sppos + 3);
			if($specialextra && array_key_exists($specialextra, $_G['setting']['threadplugins']) && in_array($specialextra, $_G['forum']['threadplugin']) && in_array($specialextra, $_G['group']['allowthreadplugin'])) {
				$postinfo['message'] = substr($postinfo['message'], 0, $sppos);
			} else {
				showmessage('post_edit_nopermission_threadplign');
				$special = 0;
				$specialextra = '';
			}
		}
		$thread['freecharge'] = $_G['setting']['maxchargespan'] && TIMESTAMP - $thread['dateline'] >= $_G['setting']['maxchargespan'] * 3600 ? 1 : 0;
		$freechargehours = !$thread['freecharge'] ? $_G['setting']['maxchargespan'] - intval((TIMESTAMP - $thread['dateline']) / 3600) : 0;
		if($thread['special'] == 1 && ($_G['group']['alloweditpoll'] || $thread['authorid'] == $_G['uid'])) {
			$query = DB::query("SELECT polloptionid, displayorder, polloption, multiple, visible, maxchoices, expiration, overt FROM ".DB::table('forum_polloption')." AS polloptions LEFT JOIN ".DB::table('forum_poll')." AS polls ON polloptions.tid=polls.tid WHERE polls.tid ='$_G[tid]' ORDER BY displayorder");
			while($temppoll = DB::fetch($query)) {
				$poll['multiple'] = $temppoll['multiple'];
				$poll['visible'] = $temppoll['visible'];
				$poll['maxchoices'] = $temppoll['maxchoices'];
				$poll['expiration'] = $temppoll['expiration'];
				$poll['overt'] = $temppoll['overt'];
				$poll['polloptionid'][] = $temppoll['polloptionid'];
				$poll['displayorder'][] = $temppoll['displayorder'];
				$poll['polloption'][] = dstripslashes($temppoll['polloption']);
			}
		} elseif($thread['special'] == 3) {
			$rewardprice = $thread['price'];
		} elseif($thread['special'] == 4) {
			$activitytypelist = $_G['setting']['activitytype'] ? explode("\n", trim($_G['setting']['activitytype'])) : '';
			$activity = DB::fetch_first("SELECT * FROM ".DB::table('forum_activity')." WHERE tid='$_G[tid]'");
			$activity['starttimefrom'] = dgmdate($activity['starttimefrom'], 'Y-m-d H:i');
			$activity['starttimeto'] = $activity['starttimeto'] ? dgmdate($activity['starttimeto'], 'Y-m-d H:i') : '';
			$activity['expiration'] = $activity['expiration'] ? dgmdate($activity['expiration'], 'Y-m-d H:i') : '';
			$activity['ufield'] = $activity['ufield'] ? unserialize($activity['ufield']) : array();
			if($activity['ufield']['extfield']) {
				$activity['ufield']['extfield'] = implode("\n", $activity['ufield']['extfield']);
			}
		} elseif($thread['special'] == 5 ) {
			$debate['endtime'] = $debate['endtime'] ? dgmdate($debate['endtime'], 'Y-m-d H:i') : '';
		}
	}

	if($thread['special'] == 2 && ($thread['authorid'] == $_G['uid'] && $_G['group']['allowposttrade'] || $_G['group']['allowedittrade'])) {
		$query = DB::query("SELECT * FROM ".DB::table('forum_trade')." WHERE pid='$pid'");
		if(DB::num_rows($query)) {
			$trade = DB::fetch($query);
			$trade['expiration'] = $trade['expiration'] ? date('Y-m-d', $trade['expiration']) : '';
			$trade['costprice'] = $trade['costprice'] > 0 ? $trade['costprice'] : '';
			$trade['message'] = dhtmlspecialchars($trade['message']);
			$expiration_7days = date('Y-m-d', TIMESTAMP + 86400 * 7);
			$expiration_14days = date('Y-m-d', TIMESTAMP + 86400 * 14);
			$expiration_month = date('Y-m-d', mktime(0, 0, 0, date('m')+1, date('d'), date('Y')));
			$expiration_3months = date('Y-m-d', mktime(0, 0, 0, date('m')+3, date('d'), date('Y')));
			$expiration_halfyear = date('Y-m-d', mktime(0, 0, 0, date('m')+6, date('d'), date('Y')));
			$expiration_year = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')+1));
		} else {
			$special = 0;
			$trade = array();
		}
	}

	if($isfirstpost && $specialextra) {
		@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'editpost')) {
			$threadplughtml = $threadpluginclass->editpost($_G['fid'], $_G['tid']);
		}
	}

	$postinfo['subject'] = str_replace('"', '&quot;', $postinfo['subject']);
	$postinfo['message'] = dhtmlspecialchars($postinfo['message']);
	$language = lang('forum/misc');
	$postinfo['message'] = preg_replace($postinfo['htmlon'] ? $language['post_edithtml_regexp'] : (!$_G['forum']['allowbbcode'] || $postinfo['bbcodeoff'] ? $language['post_editnobbcode_regexp'] : $language['post_edit_regexp']), '', $postinfo['message']);

	if($special == 5) {
		$standselected = array($firststand => 'selected="selected"');
	}

	if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
		$attachlist = getattach($pid);
		$attachs = $attachlist['attachs'];
		$imgattachs = $attachlist['imgattachs'];
		unset($attachlist);
		$attachfind = $attachreplace = array();
		if(!empty($attachs['used'])) {
			foreach($attachs['used'] as $attach) {
				if($attach['isimage']) {
					$attachfind[] = "/\[attach\]$attach[aid]\[\/attach\]/i";
					$attachreplace[] = '[attachimg]'.$attach['aid'].'[/attachimg]';
				}
			}
		}
		if(!empty($imgattachs['used'])) {
			foreach($imgattachs['used'] as $attach) {
				$attachfind[] = "/\[attach\]$attach[aid]\[\/attach\]/i";
				$attachreplace[] = '[attachimg]'.$attach['aid'].'[/attachimg]';
			}
		}
		$attachfind && $postinfo['message'] = preg_replace($attachfind, $attachreplace, $postinfo['message']);
	}
	if($special == 2 && $trade['aid'] && !empty($imgattachs['used']) && is_array($imgattachs['used'])) {
		foreach($imgattachs['used'] as $k => $tradeattach) {
			if($tradeattach['aid'] == $trade['aid']) {
				unset($imgattachs['used'][$k]);
				break;
			}
		}
	}
	if($special == 4 && $activity['aid'] && !empty($imgattachs['used']) && is_array($imgattachs['used'])) {
		foreach($imgattachs['used'] as $k => $activityattach) {
			if($activityattach['aid'] == $activity['aid']) {
				unset($imgattachs['used'][$k]);
				break;
			}
		}
	}

	if($sortid) {
		require_once libfile('post/threadsorts', 'include');
		foreach($_G['forum_optionlist'] as $option) {
			if($option['type'] == 'image') {
				foreach($imgattachs['used'] as $k => $sortattach) {
					if($sortattach['aid'] == $option['value']['aid']) {
						unset($imgattachs['used'][$k]);
						break;
					}
				}
			}
		}
	}

	$imgattachs['unused'] = !$sortid ? $imgattachs['unused'] : '';

	include template('forum/post');

} else {

	$redirecturl = "forum.php?mod=viewthread&tid=$_G[tid]&page=$_G[gp_page]&extra=$extra".($vid && $isfirstpost ? "&vid=$vid" : '')."#pid$pid";

	if(empty($_G['gp_delete'])) {

		if($post_invalid = checkpost($subject, $message, $isfirstpost && ($special || $sortid))) {
			showmessage($post_invalid, '', array('minpostsize' => $_G['setting']['minpostsize'], 'maxpostsize' => $_G['setting']['maxpostsize']));
		}

		if(!$isorigauthor && !$_G['group']['allowanonymous']) {
			if($orig['anonymous'] && !$isanonymous) {
				$isanonymous = 0;
				$authoradd = ', author=\''.addslashes($orig['author']).'\'';
				$anonymousadd = ', anonymous=\'0\'';
			} else {
				$isanonymous = $orig['anonymous'];
				$authoradd = $anonymousadd = '';
			}
		} else {
			$authoradd = ', author=\''.($isanonymous ? '' : addslashes($orig['author'])).'\'';
			$anonymousadd = ", anonymous='$isanonymous'";
		}

		if($isfirstpost) {

			if(trim($subject) == '' && $thread['special'] != 2) {
				showmessage('post_sm_isnull');
			}

			if(!$sortid && !$thread['special'] && trim($message) == '') {
				showmessage('post_sm_isnull');
			}

			$typeid = isset($_G['forum']['threadtypes']['types'][$typeid]) ? $typeid : 0;
			if(!$_G['forum']['ismoderator'] && !empty($_G['forum']['threadtypes']['moderators'][$thread['typeid']])) {
				$typeid = $thread['typeid'];
			}
			$sortid = isset($_G['forum']['threadsorts']['types'][$sortid]) ? $sortid : 0;
			$typeexpiration = intval($_G['gp_typeexpiration']);

			if(!$typeid && $_G['forum']['threadtypes']['required'] && !$thread['special']) {
				showmessage('post_type_isnull');
			}

			$readperm = $_G['group']['allowsetreadperm'] ? intval($readperm) : ($isorigauthor ? 0 : 'readperm');
			if($thread['special'] == 3) {
				$price = $isorigauthor ? ($thread['price'] > 0 && $thread['price'] != $_G['gp_rewardprice'] ? $_G['gp_rewardprice'] : 0) : $thread['price'];
			} else {
				$price = intval($_G['gp_price']);
				$price = $thread['price'] < 0 && !$thread['special']
					?($isorigauthor || !$price ? -1 : $price)
					:($_G['group']['maxprice'] ? ($price <= $_G['group']['maxprice'] ? ($price > 0 ? $price : 0) : $_G['group']['maxprice']) : ($isorigauthor ? $price : $thread['price']));

				if($price > 0 && floor($price * (1 - $_G['setting']['creditstax'])) == 0) {
					showmessage('post_net_price_iszero');
				}
			}

			$polladd = '';
			if($thread['special'] == 1 && ($_G['group']['alloweditpoll'] || $isorigauthor) && !empty($_G['gp_polls'])) {
				$pollarray = '';
				foreach($_G['gp_polloption'] as $key => $val) {
					if(trim($val) === '') {
						unset($_G['gp_polloption'][$key]);
					}
				}
				$pollarray['options'] = $_G['gp_polloption'];
				if($pollarray['options']) {
					if(count($pollarray['options']) > $_G['setting']['maxpolloptions']) {
						showmessage('post_poll_option_toomany', '', array('maxpolloptions' => $_G['setting']['maxpolloptions']));
					}
					foreach($pollarray['options'] as $key => $value) {
						$pollarray['options'][$key] = censor($pollarray['options'][$key]);
						if(!trim($value)) {
							DB::query("DELETE FROM ".DB::table('forum_polloption')." WHERE polloptionid='$key' AND tid='$_G[tid]'");
							unset($pollarray['options'][$key]);
						}
					}
					$polladd = ', special=\'1\'';
					foreach($_G['gp_displayorder'] as $key => $value) {
						if(preg_match("/^-?\d*$/", $value)) {
							$pollarray['displayorder'][$key] = $value;
						}
					}
					$curpolloption = count($pollarray['options']);
					$pollarray['maxchoices'] = empty($_G['gp_maxchoices']) ? 0 : ($_G['gp_maxchoices'] > $curpolloption ? $curpolloption : $_G['gp_maxchoices']);
					$pollarray['multiple'] = empty($_G['gp_maxchoices']) || $_G['gp_maxchoices'] == 1 ? 0 : 1;
					$pollarray['visible'] = empty($_G['gp_visibilitypoll']);
					$pollarray['expiration'] = $_G['gp_expiration'];
					$pollarray['overt'] = !empty($_G['gp_overt']);
					foreach($_G['gp_polloptionid'] as $key => $value) {
						if(!preg_match("/^\d*$/", $value)) {
							showmessage('submit_invalid');
						}
					}
					$expiration = intval($_G['gp_expiration']);
					if($close) {
						$pollarray['expiration'] = TIMESTAMP;
					} elseif($expiration) {
						if(empty($pollarray['expiration'])) {
							$pollarray['expiration'] = 0;
						} else {
							$pollarray['expiration'] = TIMESTAMP + 86400 * $expiration;
						}
					}
					$optid = '';
					$query = DB::query("SELECT polloptionid FROM ".DB::table('forum_polloption')." WHERE tid='$_G[tid]'");
					while($tempoptid = DB::fetch($query)) {
						$optid[] = $tempoptid['polloptionid'];
					}
					foreach($pollarray['options'] as $key => $value) {
						$value = dhtmlspecialchars(trim($value));
						if(in_array($_G['gp_polloptionid'][$key], $optid)) {
							if($_G['group']['alloweditpoll']) {
								DB::query("UPDATE ".DB::table('forum_polloption')." SET displayorder='".$pollarray['displayorder'][$key]."', polloption='$value' WHERE polloptionid='".$_G['gp_polloptionid'][$key]."' AND tid='$_G[tid]'");
							} else {
								DB::query("UPDATE ".DB::table('forum_polloption')." SET displayorder='".$pollarray['displayorder'][$key]."' WHERE polloptionid='".$_G['gp_polloptionid'][$key]."' AND tid='$_G[tid]'");
							}
						} else {
							DB::query("INSERT INTO ".DB::table('forum_polloption')." (tid, displayorder, polloption) VALUES ('$_G[tid]', '".$pollarray['displayorder'][$key]."', '$value')");
						}
					}
					$polloptionpreview = '';
					$query = DB::query("SELECT polloption FROM ".DB::table('forum_polloption')." WHERE tid='$_G[tid]' ORDER BY displayorder LIMIT 2");
					while($option = DB::fetch($query)) {
						$polloptvalue = preg_replace("/\[url=(https?){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i", "<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $option['polloption']);
						$polloptionpreview .= $polloptvalue."\t";
					}

					$polloptionpreview = daddslashes($polloptionpreview);

					DB::query("UPDATE ".DB::table('forum_poll')." SET multiple='$pollarray[multiple]', visible='$pollarray[visible]', maxchoices='$pollarray[maxchoices]', expiration='$pollarray[expiration]', overt='$pollarray[overt]', pollpreview='$polloptionpreview' WHERE tid='$_G[tid]'", 'UNBUFFERED');
				} else {
					$polladd = ', special=\'0\'';
					DB::query("DELETE FROM ".DB::table('forum_poll')." WHERE tid='$_G[tid]'");
					DB::query("DELETE FROM ".DB::table('forum_polloption')." WHERE tid='$_G[tid]'");
				}

			} elseif($thread['special'] == 3 && $isorigauthor) {

				$rewardprice = intval($_G['gp_rewardprice']);
				if($thread['price'] > 0 && $thread['price'] != $_G['gp_rewardprice']) {
					if($rewardprice <= 0){
						showmessage('reward_credits_invalid');
					}
					$addprice = ceil(($rewardprice - $thread['price']) + ($rewardprice - $thread['price']) * $_G['setting']['creditstax']);
					if($rewardprice < $thread['price']) {
						showmessage('reward_credits_fall');
					} elseif($rewardprice < $_G['group']['minrewardprice'] || ($_G['group']['maxrewardprice'] > 0 && $rewardprice > $_G['group']['maxrewardprice'])) {
						showmessage('reward_credits_between', '', array('minrewardprice' => $_G['group']['minrewardprice'], 'maxrewardprice' => $_G['group']['maxrewardprice']));
					} elseif($addprice > getuserprofile('extcredits'.$_G['setting']['creditstransextra'][2])) {
						showmessage('reward_credits_shortage');
					}
					$realprice = ceil($thread['price'] + $thread['price'] * $_G['setting']['creditstax']);

					updatemembercount($thread['authorid'], array($_G['setting']['creditstransextra'][2] => -$addprice));
					DB::update('common_credit_log', array('extcredits'.$_G['setting']['creditstransextra'][2] => $realprice), array('uid' => $thread['authorid'], 'operation' => 'RTC', 'relatedid' => $_G['tid']));
				}

				if(!$_G['forum']['ismoderator']) {
					if($thread['replies'] > 1) {
						$subject = addslashes($thread['subject']);
					}
				}

				$price = $rewardprice;

			} elseif($thread['special'] == 4 && $_G['group']['allowpostactivity']) {

				$activitytime = intval($_G['gp_activitytime']);
				if(empty($_G['gp_starttimefrom'][$activitytime])) {
					showmessage('activity_fromtime_please');
				} elseif(strtotime($_G['gp_starttimefrom'][$activitytime]) === -1 || @strtotime($_G['gp_starttimefrom'][$activitytime]) === FALSE) {
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
				DB::query("UPDATE ".DB::table('forum_activity')." SET cost='$activity[cost]', starttimefrom='$activity[starttimefrom]', starttimeto='$activity[starttimeto]', place='$activity[place]', class='$activity[class]', gender='$activity[gender]', number='$activity[number]', expiration='$activity[expiration]', ufield='$activity[ufield]', credit='$activity[credit]' WHERE tid='$_G[tid]'", 'UNBUFFERED');

			} elseif($thread['special'] == 5 && $_G['group']['allowpostdebate']) {

				if(empty($_G['gp_affirmpoint']) || empty($_G['gp_negapoint'])) {
					showmessage('debate_position_nofound');
				} elseif(!empty($_G['gp_endtime']) && (!($endtime = @strtotime($_G['gp_endtime'])) || $endtime < TIMESTAMP)) {
					showmessage('debate_endtime_invalid');
				} elseif(!empty($_G['gp_umpire'])) {
					if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_member')." WHERE username='$_G[gp_umpire]'")) {
						$_G['gp_umpire'] = dhtmlspecialchars($_G['gp_umpire']);
						showmessage('debate_umpire_invalid');
					}
				}
				$affirmpoint = censor(dhtmlspecialchars($_G['gp_affirmpoint']));
				$negapoint = censor(dhtmlspecialchars($_G['gp_negapoint']));
				DB::query("UPDATE ".DB::table('forum_debate')." SET affirmpoint='$affirmpoint', negapoint='$negapoint', endtime='$endtime', umpire='$_G[gp_umpire]' WHERE tid='$_G[tid]'");

			} elseif($specialextra) {

				@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
				$classname = 'threadplugin_'.$specialextra;
				if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'editpost_submit')) {
					$threadpluginclass->editpost_submit($_G['fid'], $_G['tid']);
				}

			}

			$_G['forum_optiondata'] = array();
			if($_G['forum']['threadsorts']['types'][$sortid] && $_G['forum_checkoption']) {
				$_G['forum_optiondata'] = threadsort_validator($_G['gp_typeoption'], $pid);
			}

			$threadimageaid = 0;
			$threadimage = array();

			if($_G['forum']['threadsorts']['types'][$sortid] && $_G['forum_optiondata'] && is_array($_G['forum_optiondata'])) {
				$sql = $separator = $filedname = $valuelist = '';
				foreach($_G['forum_optiondata'] as $optionid => $value) {
					if($_G['forum_optionlist'][$optionid]['type'] == 'image') {
						$identifier = $_G['forum_optionlist'][$optionid]['identifier'];
						$newsortaid = intval($_G['gp_typeoption'][$identifier]['aid']);
						if($newsortaid && $newsortaid != $_G['gp_oldsortaid'][$identifier]) {
							$attach = DB::fetch_first("SELECT attachment, thumb, remote, aid FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE aid='{$_G['gp_oldsortaid'][$identifier]}'");
							DB::query("DELETE FROM ".DB::table('forum_attachment')." WHERE aid='{$_G['gp_oldsortaid'][$identifier]}'");
							DB::query("DELETE FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE aid='{$_G['gp_oldsortaid'][$identifier]}'");
							dunlink($attach);
							$threadimageaid = $newsortaid;
							convertunusedattach($newsortaid, $_G['tid'], $pid);
						}
					}
					if($_G['forum_optionlist'][$optionid]['unchangeable']) {
						continue;
					}
					if(($_G['forum_optionlist'][$optionid]['search'] || in_array($_G['forum_optionlist'][$optionid]['type'], array('radio', 'select', 'number'))) && $value) {
						$filedname .= $separator.$_G['forum_optionlist'][$optionid]['identifier'];
						$valuelist .= $separator."'$value'";
						$sql .= $separator.$_G['forum_optionlist'][$optionid]['identifier']."='$value'";
						$separator = ' ,';
					}
					DB::query("UPDATE ".DB::table('forum_typeoptionvar')." SET value='$value', sortid='$sortid' WHERE tid='$_G[tid]' AND optionid='$optionid'");
				}

				if($typeexpiration) {
					DB::query("UPDATE ".DB::table('forum_typeoptionvar')." SET expiration='".(TIMESTAMP + $typeexpiration)."' WHERE tid='$_G[tid]' AND sortid='$sortid'");
				}

				if($sql || ($filedname && $valuelist)) {
					if(DB::result_first("SELECT tid FROM ".DB::table('forum_optionvalue')."$sortid WHERE tid='$_G[tid]'")) {
						if($sql) {
							DB::query("UPDATE ".DB::table('forum_optionvalue')."$sortid SET $sql WHERE tid='$_G[tid]' AND fid='$_G[fid]'");
						}
					} elseif($filedname && $valuelist) {
						DB::query("INSERT INTO ".DB::table('forum_optionvalue')."$sortid ($filedname, tid, fid) VALUES ($valuelist, '$_G[tid]', '$_G[fid]')");
					}
				}
			}

			$thread['status'] = setstatus(4, $_G['gp_ordertype'], $thread['status']);

			$thread['status'] = setstatus(2, $_G['gp_hiddenreplies'], $thread['status']);

			$thread['status'] = setstatus(6, $_G['gp_allownoticeauthor'] ? 1 : 0, $thread['status']);

			$displayorder = empty($_G['gp_save']) ? ($thread['displayorder'] == -4 ? 0 : $thread['displayorder']) : -4;

			if($isorigauthor && $_G['group']['allowreplycredit']) {
				$_POST['replycredit_extcredits'] = intval($_POST['replycredit_extcredits']);
				$_POST['replycredit_times'] = intval($_POST['replycredit_times']);
				$_POST['replycredit_membertimes'] = intval($_POST['replycredit_membertimes']) > 0 ? intval($_POST['replycredit_membertimes']) : 1;
				$_POST['replycredit_random'] = intval($_POST['replycredit_random']) < 0 || intval($_POST['replycredit_random']) > 99 ? 0 : intval($_POST['replycredit_random']) ;
				if($_POST['replycredit_extcredits'] > 0 && $_POST['replycredit_times'] > 0) {
					$replycredit = $_POST['replycredit_extcredits'] * $_POST['replycredit_times'];
					$replycredit_diff =  $replycredit - $thread['replycredit'];
					if($replycredit_diff > 0) {
						$replycredit_diff = ceil($replycredit_diff + ($replycredit_diff * $_G['setting']['creditstax']));
						if(!$replycredit_rule) {
							$replycredit_rule = array();
							if($_G['setting']['creditstransextra']['10']) {
								$replycredit_rule['extcreditstype'] = $_G['setting']['creditstransextra']['10'];
							}
						}

						if($replycredit_diff > getuserprofile('extcredits'.$replycredit_rule['extcreditstype'])) {
							showmessage('post_edit_thread_replaycredit_nocredit');
						}
					}

					if($replycredit_diff) {
						updatemembercount($_G['uid'], array($replycredit_rule['extcreditstype'] => ($replycredit_diff > 0 ? -$replycredit_diff : abs($replycredit_diff))), 1, ($replycredit_diff > 0 ? 'RCT' : 'RCB'), $_G['tid']);
					}
				} elseif(($_POST['replycredit_extcredits'] == 0 || $_POST['replycredit_times'] == 0) && $thread['replycredit'] > 0) {
					$replycredit = 0;
					DB::query("DELETE FROM ".DB::table('forum_replycredit')." WHERE tid = '{$_G['tid']}'");
					updatemembercount($thread['authorid'], array($replycredit_rule['extcreditstype'] => $thread['replycredit']), 1, 'RCB', $_G['tid']);
					$replycreditadd = ", replycredit = '0'";
				} else {
					$replycredit = $thread['replycredit'];
				}
				if($replycredit) {
					$replycreditadd = ", replycredit = '$replycredit'";
					DB::query("REPLACE INTO ".DB::table('forum_replycredit')."(tid, extcredits, extcreditstype, times, membertimes, random)VALUES('$_G[tid]', '$_POST[replycredit_extcredits]', '$replycredit_rule[extcreditstype]', '$_POST[replycredit_times]', '$_POST[replycredit_membertimes]', '$_POST[replycredit_random]')");
				}
			}

			$closedadd = '';
			if($rushreply) {
				$_G['gp_rushreplyfrom'] = strtotime($_G['gp_rushreplyfrom']);
				$_G['gp_rushreplyto'] = strtotime($_G['gp_rushreplyto']);
				$_G['gp_rewardfloor'] = trim($_G['gp_rewardfloor']);
				$_G['gp_stopfloor'] = intval($_G['gp_stopfloor']);
				if($_G['gp_rushreplyfrom'] > $_G['gp_rushreplyto'] && !empty($_G['gp_rushreplyto'])) {
					showmessage('post_rushreply_timewrong');
				}
				$maxposition = DB::result_first("SELECT position FROM ".DB::table('forum_postposition')." WHERE tid = '{$_G['tid']}' ORDER BY position DESC LIMIT 1");
				if($thread['closed'] == 1 && ((!$_G['gp_rushreplyfrom'] && !$_G['gp_rushreplyto']) || ($_G['gp_rushreplyfrom'] < $_G['timestamp'] && $_G['gp_rushreplyto'] > $_G['timestamp']) || (!$_G['gp_rushreplyfrom'] && $_G['gp_rushreplyto'] > $_G['timestamp']) || ($_G['gp_stopfloor'] && $_G['gp_stopfloor'] > $maxposition) )) {
					$closedadd = " , closed = '0'";
				} elseif($thread['closed'] == 0 && (($_G['gp_rushreplyfrom'] && $_G['gp_rushreplyfrom'] > $_G['timestamp']) || ($_G['gp_rushreplyto'] && $_G['gp_rushreplyto'] && $_G['gp_rushreplyto'] < $_G['timestamp']) || ($_G['gp_stopfloor'] && $_G['gp_stopfloor'] <= $maxposition) )) {
					$closedadd = " , closed = '1'";
				}
				if(!empty($_G['gp_rewardfloor']) && !empty($_G['gp_stopfloor'])) {
					$floors = explode(',', $_G['gp_rewardfloor']);
					if(!empty($floors)) {
						foreach($floors AS $key => $floor) {
							if(strpos($floor, '*') === false) {
								if(intval($floor) == 0) {
									unset($floors[$key]);
								} elseif($floor > $_G['gp_stopfloor']) {
									unset($floors[$key]);
								}
							}
						}
					}
					$_G['gp_rewardfloor'] = implode(',', $floors);
				}
				DB::query("UPDATE ".DB::table('forum_threadrush')." SET stopfloor='$_G[gp_stopfloor]', starttimefrom='$_G[gp_rushreplyfrom]', starttimeto='$_G[gp_rushreplyto]', rewardfloor='$_G[gp_rewardfloor]' WHERE tid='$_G[tid]'", 'UNBUFFERED');
			}

			DB::query("UPDATE ".DB::table('forum_thread')." SET typeid='$typeid', sortid='$sortid', subject='$subject', readperm='$readperm', price='$price' $closedadd $authoradd $polladd $replycreditadd".($_G['forum_auditstatuson'] && $audit == 1 ? ",displayorder='0', moderated='1'" : ",displayorder='$displayorder'").", status='$thread[status]' WHERE tid='$_G[tid]'", 'UNBUFFERED');

			$thread['closed'] > 1 && DB::query("UPDATE ".DB::table('forum_thread')." SET subject='$subject' WHERE tid='$thread[closed]'", 'UNBUFFERED');

			$tagstr = modthreadtag($_G['gp_tags'], $_G['tid']);

		} else {

			if($subject == '' && $message == '' && $thread['special'] != 2) {
				showmessage('post_sm_isnull');
			}

		}

		$htmlon = $_G['group']['allowhtml'] && !empty($_G['gp_htmlon']) ? 1 : 0;

		if($_G['setting']['editedby'] && (TIMESTAMP - $orig['dateline']) > 60 && $_G['adminid'] != 1) {
			$editor = $isanonymous && $isorigauthor ? lang('forum/misc', 'anonymous') : $_G['username'];
			$edittime = dgmdate(TIMESTAMP);
			$message = lang('forum/misc', $htmlon ? 'post_edithtml' : (!$_G['forum']['allowbbcode'] || $_G['gp_bbcodeoff'] ? 'post_editnobbcode' : 'post_edit'), array('editor' => $editor, 'edittime' => $edittime)) . $message;
		}

		$bbcodeoff = checkbbcodes($message, !empty($_G['gp_bbcodeoff']));
		$smileyoff = checksmilies($message, !empty($_G['gp_smileyoff']));
		$tagoff = $isfirstpost ? !empty($tagoff) : 0;
		$attachupdate = !empty($_G['gp_delattachop']) || ($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) && ($_G['gp_attachnew'] || $special == 2 && $_G['gp_tradeaid'] || $special == 4 && $_G['gp_activityaid'] || $isfirstpost && $sortid);

		if($attachupdate) {
			updateattach($thread['displayorder'] == -4 || $_G['forum_auditstatuson'], $_G['tid'], $pid, $_G['gp_attachnew'], $_G['gp_attachupdate'], $orig['authorid']);
		}

		if($special == 2 && $_G['group']['allowposttrade']) {

			if($trade = DB::fetch_first("SELECT * FROM ".DB::table('forum_trade')." WHERE tid='$_G[tid]' AND pid='$pid'")) {
				$seller = empty($_G['gp_paymethod']) && $_G['gp_seller'] ? censor(dhtmlspecialchars(trim($_G['gp_seller']))) : '';
				$item_name = censor(dhtmlspecialchars(trim($_G['gp_item_name'])));
				$item_price = floatval($_G['gp_item_price']);
				$item_credit = intval($_G['gp_item_credit']);
				$item_locus = censor(dhtmlspecialchars(trim($_G['gp_item_locus'])));
				$item_number = intval($_G['gp_item_number']);
				$item_quality = intval($_G['gp_item_quality']);
				$item_transport = intval($_G['gp_item_transport']);
				$postage_mail = intval($_G['gp_postage_mail']);
				$postage_express = intval(trim($_G['gp_postage_express']));
				$postage_ems = intval($_G['gp_postage_ems']);
				$item_type = intval($_G['gp_item_type']);
				$item_costprice = floatval($_G['gp_item_costprice']);

				if(!trim($item_name)) {
					showmessage('trade_please_name');
				} elseif($_G['group']['maxtradeprice'] && $item_price > 0 && ($_G['group']['mintradeprice'] > $item_price || $_G['group']['maxtradeprice'] < $item_price)) {
					showmessage('trade_price_between', '', array('mintradeprice' => $_G['group']['mintradeprice'], 'maxtradeprice' => $_G['group']['maxtradeprice']));
				} elseif($_G['group']['maxtradeprice'] && $item_credit > 0 && ($_G['group']['mintradeprice'] > $item_credit || $_G['group']['maxtradeprice'] < $item_credit)) {
					showmessage('trade_credit_between', '', array('mintradeprice' => $_G['group']['mintradeprice'], 'maxtradeprice' => $_G['group']['maxtradeprice']));
				} elseif(!$_G['group']['maxtradeprice'] && $item_price > 0 && $_G['group']['mintradeprice'] > $item_price) {
					showmessage('trade_price_more_than', '', array('mintradeprice' => $_G['group']['mintradeprice']));
				} elseif(!$_G['group']['maxtradeprice'] && $item_credit > 0 && $_G['group']['mintradeprice'] > $item_credit) {
					showmessage('trade_credit_more_than', '', array('mintradeprice' => $_G['group']['mintradeprice']));
				} elseif($item_price <= 0 && $item_credit <= 0) {
					showmessage('trade_pricecredit_need');
				} elseif($item_number < 1) {
					showmessage('tread_please_number');
				}

				if($trade['aid'] != $_G['gp_tradeaid']) {
					$attach = DB::fetch_first("SELECT attachment, thumb, remote, aid FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE aid='$trade[aid]'");
					DB::query("DELETE FROM ".DB::table('forum_attachment')." WHERE aid='$trade[aid]'");
					DB::query("DELETE FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE aid='$trade[aid]'");
					dunlink($attach);
					$threadimageaid = $_G['gp_tradeaid'];
					convertunusedattach($_G['gp_tradeaid'], $_G['tid'], $pid);
				}

				$expiration = $_G['gp_item_expiration'] ? @strtotime($_G['gp_item_expiration']) : 0;
				$closed = $expiration > 0 && @strtotime($_G['gp_item_expiration']) < TIMESTAMP ? 1 : $closed;

				switch($_G['gp_transport']) {
					case 'seller':$item_transport = 1;break;
					case 'buyer':$item_transport = 2;break;
					case 'virtual':$item_transport = 3;break;
					case 'logistics':$item_transport = 4;break;
				}
				if(!$item_price || $item_price <= 0) {
					$item_price = $postage_mail = $postage_express = $postage_ems = '';
				}

				DB::query("UPDATE ".DB::table('forum_trade')." SET aid='$_G[gp_tradeaid]', account='$seller', tenpayaccount='$_G[gp_tenpay_account]', subject='$item_name', price='$item_price', amount='$item_number', quality='$item_quality', locus='$item_locus',
					transport='$item_transport', ordinaryfee='$postage_mail', expressfee='$postage_express', emsfee='$postage_ems', itemtype='$item_type', expiration='$expiration', closed='$closed',
					costprice='$item_costprice', credit='$item_credit', costcredit='$_G[gp_item_costcredit]' WHERE tid='$_G[tid]' AND pid='$pid'", 'UNBUFFERED');

				if(!empty($_G['gp_infloat'])) {
					$viewpid = DB::result_first("SELECT pid FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND first='1' LIMIT 1");
					$redirecturl = "forum.php?mod=viewthread&tid=$_G[tid]&viewpid=$viewpid#pid$viewpid";
				} else {
					$redirecturl = "forum.php?mod=viewthread&do=tradeinfo&tid=$_G[tid]&pid=$pid";
				}
			}

		}

		if($special == 4 && $isfirstpost && $_G['group']['allowpostactivity']) {
			$activityaid = DB::result_first("SELECT aid FROM ".DB::table('forum_activity')." WHERE tid='$_G[tid]'");
			if($activityaid && $activityaid != $_G['gp_activityaid']) {
				$attach = DB::fetch_first("SELECT attachment, thumb, remote, aid FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE aid='$activityaid'");
				DB::query("DELETE FROM ".DB::table('forum_attachment')." WHERE aid='$activityaid'");
				DB::query("DELETE FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE aid='$activityaid'");
				dunlink($attach);
			}
			if($_G['gp_activityaid']) {
				$threadimageaid = $_G['gp_activityaid'];
				convertunusedattach($_G['gp_activityaid'], $_G['tid'], $pid);
				DB::query("UPDATE ".DB::table('forum_activity')." SET aid='$_G[gp_activityaid]' WHERE tid='$_G[tid]'");
			}
		}

		if($isfirstpost && $attachupdate) {
			if(!$threadimageaid) {
				$threadimage = DB::fetch_first("SELECT aid, attachment, remote FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE pid='$pid' AND isimage IN ('1', '-1') ORDER BY width DESC LIMIT 1");
				$threadimageaid = $threadimage['aid'];
			}

			if($_G['forum']['picstyle']) {
				if(empty($thread['cover'])) {
					setthreadcover($pid, 0, $threadimageaid);
				} else {
					setthreadcover($pid, $_G['tid'], 0);
				}
			}

			if($threadimageaid) {
				if(!$threadimage) {
					$threadimage = DB::fetch_first("SELECT attachment, remote FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE tid='$_G[tid]' AND isimage IN ('1', '-1') ORDER BY width DESC LIMIT 1");
				}
				DB::delete('forum_threadimage', "tid='$_G[tid]'");
				$threadimage = daddslashes($threadimage);
				DB::insert('forum_threadimage', array(
					'tid' => $_G['tid'],
					'attachment' => $threadimage['attachment'],
					'remote' => $threadimage['remote'],
				));
			}
		}

		$feed = array();
		if($special == 127) {
			$message .= chr(0).chr(0).chr(0).$specialextra;
		}

		if($_G['forum_auditstatuson'] && $audit == 1) {
			DB::query("UPDATE ".DB::table(getposttable($thread['posttableid']))." SET status='4' WHERE pid='$pid' AND status='0' AND invisible='-2'");
			updatepostcredits('+', $orig['authorid'], ($isfirstpost ? 'post' : 'reply'), $_G['fid']);
			updatemodworks('MOD', 1);
			updatemodlog($_G['tid'], 'MOD');
		}

		$displayorder = $pinvisible = 0;
		if($isfirstpost) {
			$displayorder = $modnewthreads ? -2 : $thread['displayorder'];
			$pinvisible = $modnewthreads ? -2 : (empty($_G['gp_save']) ? 0 : -3);
			if($thread['displayorder'] == -4 && empty($_G['gp_save'])) {
				DB::query("UPDATE ".DB::table($posttable)." SET dateline='$_G[timestamp]', invisible='0' WHERE tid='$thread[tid]'");
				DB::query("UPDATE ".DB::table('forum_thread')." SET dateline='$_G[timestamp]', lastpost='$_G[timestamp]' WHERE tid='$thread[tid]'");
				$posts = $thread['replies'] + 1;
				if($thread['replies']) {
					$dateline = $_G['timestamp'];
					$query = DB::query("SELECT pid FROM ".DB::table($posttable)." WHERE tid='$thread[tid]' AND first='0'");
					while($post = DB::fetch($query)) {
						$dateline++;
						DB::query("UPDATE ".DB::table($posttable)." SET dateline='$dateline' WHERE pid='$post[pid]'");
						my_post_log('update', array('pid' => $post['pid']));
						updatepostcredits('+', $_G['uid'], 'reply', $_G['fid']);
					}
				}
				my_thread_log('update', array('tid' => $thread['tid']));
				updatepostcredits('+', $_G['uid'], 'post', $_G['fid']);
				$attachcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_attachment')." WHERE tid='$thread[tid]'");
				updatecreditbyaction('postattach', $_G['uid'], array(), '', $attachcount, 1, $_G['fid']);
				if($_G['forum']['status'] == 3) {
					DB::query("UPDATE ".DB::table('forum_groupuser')." SET threads=threads+1, lastupdate='".TIMESTAMP."' WHERE uid='$_G[uid]' AND fid='$_G[fid]'");
				}
				DB::query("UPDATE ".DB::table('forum_forum')." SET threads=threads+1, posts=posts+'".$posts."', todayposts=todayposts+'".$posts."' WHERE fid='$_G[fid]'", 'UNBUFFERED');
			}
		} else {
			$pinvisible = $modnewreplies ? -2 : ($thread['displayorder'] == -4 ? -3 : 0);
		}

		$message = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $message);
		$parseurloff = !empty($_G['gp_parseurloff']);
		DB::query("UPDATE ".DB::table($posttable)." SET message='$message', usesig='$_G[gp_usesig]', htmlon='$htmlon', bbcodeoff='$bbcodeoff', parseurloff='$parseurloff',
			smileyoff='$smileyoff', subject='$subject' $anonymousadd ".($_G['forum_auditstatuson'] && $audit == 1 ? ",invisible='0'" : ", invisible='$pinvisible'")." , tags='".$tagstr."'  WHERE pid='$pid'");

		$_G['forum']['lastpost'] = explode("\t", $_G['forum']['lastpost']);

		if($orig['dateline'] == $_G['forum']['lastpost'][2] && ($orig['author'] == $_G['forum']['lastpost'][3] || ($_G['forum']['lastpost'][3] == '' && $orig['anonymous']))) {
			$lastpost = "$_G[tid]\t".($isfirstpost ? $subject : addslashes($thread['subject']))."\t$orig[dateline]\t".($isanonymous ? '' : addslashes($orig['author']));
			DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost' WHERE fid='$_G[fid]'", 'UNBUFFERED');
		}

		if(!$_G['forum_auditstatuson'] || $audit != 1) {
			if($isfirstpost && $modnewthreads) {
				DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='-2' WHERE tid='$_G[tid]'");
				manage_addnotify('verifythread');
			} elseif(!$isfirstpost && $modnewreplies) {
				DB::query("UPDATE ".DB::table('forum_thread')." SET replies=replies-'1' WHERE tid='$_G[tid]'");
				manage_addnotify('verifypost');
			}
			if($modnewreplies || $modnewthreads) {

				DB::update('forum_forum', array('modworks' => '1'), "fid='{$_G['fid']}'");
			}
		}

		if($thread['lastpost'] == $orig['dateline'] && ((!$orig['anonymous'] && $thread['lastposter'] == $orig['author']) || ($orig['anonymous'] && $thread['lastposter'] == '')) && $orig['anonymous'] != $isanonymous) {
			DB::query("UPDATE ".DB::table('forum_thread')." SET lastposter='".($isanonymous ? '' : addslashes($orig['author']))."' WHERE tid='$_G[tid]'", 'UNBUFFERED');
		}

		if(!$isorigauthor) {
			updatemodworks('EDT', 1);
			require_once libfile('function/misc');
			modlog($thread, 'EDT');
		}

	} else {

		if(!$_G['setting']['editperdel']) {
			showmessage('post_edit_thread_ban_del', NULL);
		}

		if($isfirstpost && $thread['replies'] > 0) {
			showmessage(($thread['special'] == 3 ? 'post_edit_reward_already_reply' : 'post_edit_thread_already_reply'), NULL);
		}

		if($thread['special'] == 3) {
			if($thread['price'] < 0 && ($thread['dateline'] + 1 == $orig['dateline'])) {
				showmessage('post_edit_reward_nopermission', NULL);
			}
		}

		if($rushreply) {
			showmessage('post_edit_delete_rushreply_nopermission', NULL);
		}

		if($thread['displayorder'] >= 0) {
			updatepostcredits('-', $orig['authorid'], ($isfirstpost ? 'post' : 'reply'), $_G['fid']);
		}

		if($thread['special'] == 3 && $isfirstpost) {
			updatemembercount($orig['authorid'], array($_G['setting']['creditstransextra'][2] => $thread['price']));
			DB::delete('common_credit_log', array('uid' => $thread['authorid'], 'operation' => 'RTC', 'relatedid' => $_G['tid']));
		}

		if($thread['replycredit'] && $isfirstpost && !$isanonymous) {
			updatemembercount($orig['authorid'], array($replycredit_rule['extcreditstype'] => $thread['replycredit']), true, 'RCB', $_G['tid']);
			DB::delete('forum_replycredit', array('tid' => $_G['tid']));
		} elseif (!$isfirstpost && !$isanonymous) {
			if($postreplycredit = DB::result_first("SELECT replycredit FROM ".DB::table($posttable)." WHERE pid = '$pid' LIMIT 1")) {
				DB::query("UPDATE ".DB::table($posttable)." SET replycredit = 0 WHERE pid = '$pid' LIMIT 1");
				updatemembercount($orig['authorid'], array($replycredit_rule['extcreditstype'] => '-'.$postreplycredit));
			}
		}


		$thread_attachment = $post_attachment = 0;
		$query = DB::query("SELECT pid, attachment, thumb, remote, aid FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE tid='$_G[tid]'");
		while($attach = DB::fetch($query)) {
			if($attach['pid'] == $pid) {
				if($thread['displayorder'] >= 0) {
					$post_attachment++;
				}
				dunlink($attach);
			} else {
				$thread_attachment = 1;
			}
		}

		if($post_attachment) {
			DB::query("DELETE FROM ".DB::table('forum_attachment')." WHERE pid='$pid'", 'UNBUFFEREED');
			DB::query("DELETE FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE pid='$pid'", 'UNBUFFEREED');
			updatecreditbyaction('postattach', $orig['authorid'], array(),  '', -$post_attachment);
		}

		DB::query("DELETE FROM ".DB::table($posttable)." WHERE pid='$pid'");
		DB::delete('forum_postcomment', "rpid='$pid'");
		if($thread['special'] == 2) {
			DB::query("DELETE FROM ".DB::table('forum_trade')." WHERE pid='$pid'");
		}

		if($isfirstpost) {
			$forumadd = 'threads=threads-\'1\', posts=posts-\'1\'';
			$tablearray = array('forum_threadmod', 'forum_relatedthread', 'forum_thread', 'forum_debate', 'forum_debatepost', 'forum_polloption', 'forum_poll', 'forum_typeoptionvar');
			foreach ($tablearray as $table) {
				DB::query("DELETE FROM ".DB::table($table)." WHERE tid='$_G[tid]'", 'UNBUFFERED');
			}
			if($_G['setting']['globalstick'] && in_array($thread['displayorder'], array(2, 3))) {
				require_once libfile('function/cache');
				updatecache('globalstick');
			}
		} else {
			$savepostposition && DB::query("DELETE FROM ".DB::table('forum_postposition')." WHERE pid='$pid'");
			$forumadd = 'posts=posts-\'1\'';
			$query = DB::query("SELECT author, dateline, anonymous FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0' ORDER BY dateline DESC LIMIT 1");
			$lastpost = DB::fetch($query);
			$lastpost['author'] = !$lastpost['anonymous'] ? addslashes($lastpost['author']) : '';
			DB::query("UPDATE ".DB::table('forum_thread')." SET replies=replies-'1', attachment='$thread_attachment', lastposter='$lastpost[author]', lastpost='$lastpost[dateline]' WHERE tid='$_G[tid]'", 'UNBUFFERED');
		}

		$_G['forum']['lastpost'] = explode("\t", $_G['forum']['lastpost']);
		if($orig['dateline'] == $_G['forum']['lastpost'][2] && ($orig['author'] == $_G['forum']['lastpost'][3] || ($_G['forum']['lastpost'][3] == '' && $orig['anonymous']))) {
			$lastthread = daddslashes(DB::fetch_first("SELECT tid, subject, lastpost, lastposter FROM ".DB::table('forum_thread')."
				WHERE fid='$_G[fid]' AND displayorder>='0' ORDER BY lastpost DESC LIMIT 1"), 1);
			$forumadd .= ", lastpost='$lastthread[tid]\t$lastthread[subject]\t$lastthread[lastpost]\t$lastthread[lastposter]'";
		}

		DB::query("UPDATE ".DB::table('forum_forum')." SET $forumadd WHERE fid='$_G[fid]'", 'UNBUFFERED');

	}

	if($specialextra) {

		@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'editpost_submit_end')) {
			$threadpluginclass->editpost_submit_end($_G['fid'], $_G['tid']);
		}

	}

	if($_G['forum']['threadcaches']) {
		deletethreadcaches($_G['tid']);
	}

	$param = array('fid' => $_G['fid'], 'tid' => $_G['tid'], 'pid' => $pid);

	dsetcookie('clearUserdata', 'forum');

	if($_G['forum_auditstatuson']) {
		if($audit == 1) {
			updatemoderate($isfirstpost ? 'tid' : 'pid', $isfirstpost ? $_G['tid'] : $pid, '2');
			showmessage('auditstatuson_succeed', $redirecturl, $param);
		} else {
			updatemoderate($isfirstpost ? 'tid' : 'pid', $isfirstpost ? $_G['tid'] : $pid);
			showmessage('audit_edit_succeed', '', $param);
		}
	} else {
		if(!empty($_G['gp_delete']) && $isfirstpost) {
			my_thread_log('delete', array('tid' => $_G['tid']));
			showmessage('post_edit_delete_succeed', "forum.php?mod=forumdisplay&fid=$_G[fid]", $param);
		} elseif(!empty($_G['gp_delete'])) {
			my_post_log('delete', array('pid' => $pid));
			showmessage('post_edit_delete_succeed', "forum.php?mod=viewthread&tid=$_G[tid]&page=$_G[gp_page]&extra=$extra".($vid && $isfirstpost ? "&vid=$vid" : ''), $param);
		} else {
			if($isfirstpost && $modnewthreads) {
				updatemoderate('tid', $_G['tid']);
				showmessage('edit_newthread_mod_succeed', $redirecturl, $param);
			} elseif(!$isfirstpost && $modnewreplies) {
				updatemoderate('pid', $pid);
				showmessage('edit_reply_mod_succeed', "forum.php?mod=forumdisplay&fid=$_G[fid]", $param);
			} else {
				if($pinvisible != -3) {
					my_post_log('update', array('pid' => $pid));
				}
				showmessage('post_edit_succeed', $redirecturl, $param);
			}
		}
	}

}

?>