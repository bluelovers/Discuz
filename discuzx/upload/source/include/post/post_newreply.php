<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: post_newreply.php 27374 2012-01-19 04:43:04Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/forumlist');

$isfirstpost = 0;
$showthreadsorts = 0;
$quotemessage = '';

if($special == 5) {
	$debate = array_merge($thread, DB::fetch_first("SELECT * FROM ".DB::table('forum_debate')." WHERE tid='$_G[tid]'"));
	$standquery = DB::query("SELECT stand FROM ".DB::table('forum_debatepost')." WHERE tid='$_G[tid]' AND uid='$_G[uid]' AND stand>'0' ORDER BY dateline LIMIT 1");
	$firststand = DB::result_first("SELECT stand FROM ".DB::table('forum_debatepost')." WHERE tid='$_G[tid]' AND uid='$_G[uid]' AND stand>'0' ORDER BY dateline LIMIT 1");
	$stand = $firststand ? $firststand : intval($_G['gp_stand']);

	if($debate['endtime'] && $debate['endtime'] < TIMESTAMP) {
		showmessage('debate_end');
	}
}

if(!$_G['uid'] && !((!$_G['forum']['replyperm'] && $_G['group']['allowreply']) || ($_G['forum']['replyperm'] && forumperm($_G['forum']['replyperm'])))) {
	showmessage('replyperm_login_nopermission', NULL, array(), array('login' => 1));
} elseif(empty($_G['forum']['allowreply'])) {
	if(!$_G['forum']['replyperm'] && !$_G['group']['allowreply']) {
		showmessage('replyperm_none_nopermission', NULL, array(), array('login' => 1));
	} elseif($_G['forum']['replyperm'] && !forumperm($_G['forum']['replyperm'])) {
		showmessagenoperm('replyperm', $_G['forum']['fid']);
	}
} elseif($_G['forum']['allowreply'] == -1) {
	showmessage('post_forum_newreply_nopermission', NULL);
}

if(!$_G['uid'] && ($_G['setting']['need_avatar'] || $_G['setting']['need_email'] || $_G['setting']['need_friendnum'])) {
	showmessage('replyperm_login_nopermission', NULL, array(), array('login' => 1));
}

if(empty($thread)) {
	showmessage('thread_nonexistence');
} elseif($thread['price'] > 0 && $thread['special'] == 0 && !$_G['uid']) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

checklowerlimit('reply', 0, 1, $_G['forum']['fid']);

if($_G['setting']['commentnumber'] && !empty($_G['gp_comment'])) {
	$posttable = getposttablebytid($_G['tid']);
	if(!submitcheck('commentsubmit', 0, $seccodecheck, $secqaacheck)) {
		showmessage('submitcheck_error', NULL);
	}
	$post = DB::fetch_first('SELECT * FROM '.DB::table($posttable)." WHERE pid='$_G[gp_pid]'");
	if(!$post) {
		showmessage('post_nonexistence', NULL);
	}
	if($thread['closed'] && !$_G['forum']['ismoderator'] && !$thread['isgroup']) {
		showmessage('post_thread_closed');
	} elseif(!$thread['isgroup'] && $post_autoclose = checkautoclose($thread)) {
		showmessage($post_autoclose, '', array('autoclose' => $_G['forum']['autoclose']));
	} elseif(checkflood()) {
		showmessage('post_flood_ctrl', '', array('floodctrl' => $_G['setting']['floodctrl']));
	} elseif(checkmaxpostsperhour()) {
		showmessage('post_flood_ctrl_posts_per_hour', '', array('posts_per_hour' => $_G['group']['maxpostsperhour']));
	}
	$commentscore = '';
	if(!empty($_G['gp_commentitem']) && !empty($_G['uid']) && $post['authorid'] != $_G['uid']) {
		foreach($_G['gp_commentitem'] as $itemk => $itemv) {
			if($itemv !== '') {
				$commentscore .= strip_tags(trim($itemk)).': <i>'.intval($itemv).'</i> ';
			}
		}
	}
	$comment = cutstr(($commentscore ? $commentscore.'<br />' : '').censor(trim(htmlspecialchars($_G['gp_message'])), '***'), 200, ' ');
	if(!$comment) {
		showmessage('post_sm_isnull');
	}
	DB::insert('forum_postcomment', array(
		'tid' => $post['tid'],
		'pid' => $post['pid'],
		'author' => $_G['username'],
		'authorid' => $_G['uid'],
		'dateline' => TIMESTAMP,
		'comment' => $comment,
		'score' => $commentscore ? 1 : 0,
		'useip' => $_G['clientip'],
	));
	DB::update($posttable, array('comment' => 1), "pid='$_G[gp_pid]'");
	!empty($_G['uid']) && updatepostcredits('+', $_G['uid'], 'reply', $_G['fid']);
	if(!empty($_G['uid']) && $_G['uid'] != $post['authorid']) {
		notification_add($post['authorid'], 'pcomment', 'comment_add', array(
			'tid' => $_G['tid'],
			'pid' => $_G['gp_pid'],
			'subject' => $thread['subject'],
			'commentmsg' => cutstr(str_replace(array('[b]', '[/b]', '[/color]'), '', preg_replace("/\[color=([#\w]+?)\]/i", "", stripslashes($comment))), 200)
		));
	}
	if($_G['setting']['heatthread']['type'] == 2) {
		update_threadpartake($post['tid']);
	}
	$pcid = DB::result_first("SELECT id FROM ".DB::table('forum_postcomment')." WHERE pid='$_G[gp_pid]' AND authorid='-1'");
	if(!empty($_G['uid']) && $_G['gp_commentitem']) {
		$query = DB::query('SELECT comment FROM '.DB::table('forum_postcomment')." WHERE pid='$_G[gp_pid]' AND score='1'");
		$totalcomment = array();
		while($comment = DB::fetch($query)) {
			$comment['comment'] = addslashes($comment['comment']);
			if(strexists($comment['comment'], '<br />')) {
				if(preg_match_all("/([^:]+?):\s<i>(\d+)<\/i>/", $comment['comment'], $a)) {
					foreach($a[1] as $k => $itemk) {
						$totalcomment[trim($itemk)][] = $a[2][$k];
					}
				}
			}
		}
		$totalv = '';
		foreach($totalcomment as $itemk => $itemv) {
			$totalv .= strip_tags(trim($itemk)).': <i>'.(floatval(sprintf('%1.1f', array_sum($itemv) / count($itemv)))).'</i> ';
		}

		if($pcid) {
			DB::update('forum_postcomment', array('comment' => $totalv, 'dateline' => TIMESTAMP + 1), "id='$pcid'");
		} else {
			DB::insert('forum_postcomment', array(
				'tid' => $post['tid'],
				'pid' => $post['pid'],
				'author' => '',
				'authorid' => '-1',
				'dateline' => TIMESTAMP + 1,
				'comment' => $totalv
			));
		}
	}
	DB::update('forum_postcomment', array('dateline' => TIMESTAMP + 1), "id='$pcid'");
	showmessage('comment_add_succeed', "forum.php?mod=viewthread&tid=$post[tid]&pid=$post[pid]&page=$_G[gp_page]&extra=$extra#pid$post[pid]", array('tid' => $post['tid'], 'pid' => $post['pid']));
}

if($special == 127) {
	$posttable = getposttablebytid($_G['tid']);
	$postinfo = DB::fetch_first("SELECT message FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND first='1'");
	$sppos = strrpos($postinfo['message'], chr(0).chr(0).chr(0));
	$specialextra = substr($postinfo['message'], $sppos + 3);
}

if(!submitcheck('replysubmit', 0, $seccodecheck, $secqaacheck)) {

	if($thread['special'] == 2 && ((!isset($_G['gp_addtrade']) || $thread['authorid'] != $_G['uid']) && !$tradenum = DB::result_first("SELECT count(*) FROM ".DB::table('forum_trade')." WHERE tid='$_G[tid]'"))) {
		showmessage('trade_newreply_nopermission', NULL);
	}

	$language = lang('forum/misc');
	$noticeauthor = $noticetrimstr = '';
	if(isset($_G['gp_repquote']) && $_G['gp_repquote'] = intval($_G['gp_repquote'])) {
		$posttable = getposttablebytid($_G['tid']);
		$thaquote = DB::fetch_first("SELECT tid, fid, author, authorid, first, message, useip, dateline, anonymous, status FROM ".DB::table($posttable)." WHERE pid='$_G[gp_repquote]' AND (invisible='0' OR (authorid='$_G[uid]' AND invisible='-2'))");
		if($thaquote['tid'] != $_G['tid']) {
			showmessage('reply_quotepost_error', NULL);
		}

		if(getstatus($thread['status'], 2) && $thaquote['authorid'] != $_G['uid'] && $_G['uid'] != $thread['authorid'] && $thaquote['first'] != 1 && !$_G['forum']['ismoderator']) {
			showmessage('reply_quotepost_error', NULL);
		}

		if(!($thread['price'] && !$thread['special'] && $thaquote['first'])) {
			$quotefid = $thaquote['fid'];
			$message = $thaquote['message'];

			if($_G['setting']['bannedmessages'] && $thaquote['authorid']) {
				$author = DB::fetch_first("SELECT groupid FROM ".DB::table('common_member')." WHERE uid='$thaquote[authorid]'");
				if(!$author['groupid'] || $author['groupid'] == 4 || $author['groupid'] == 5) {
					$message = $language['post_banned'];
				} elseif($thaquote['status'] & 1) {
					$message = $language['post_single_banned'];
				}
			}

			$time = dgmdate($thaquote['dateline']);
			$message = messagecutstr($message, 100);
			$message = implode("\n", array_slice(explode("\n", $message), 0, 3));

			$thaquote['useip'] = substr($thaquote['useip'], 0, strrpos($thaquote['useip'], '.')).'.x';
			if($thaquote['author'] && $thaquote['anonymous']) {
				$thaquote['author'] = lang('forum/misc', 'anonymoususer');
			} elseif(!$thaquote['author']) {
				$thaquote['author'] = lang('forum/misc', 'guestuser').' '.$thaquote['useip'];
			} else {
				$thaquote['author'] = $thaquote['author'];
			}

			$post_reply_quote = lang('forum/misc', 'post_reply_quote', array('author' => $thaquote['author'], 'time' => $time));
			$noticeauthormsg = htmlspecialchars($message);
			if(!defined('IN_MOBILE')) {
				$message = "[quote][size=2][color=#999999]{$post_reply_quote}[/color] [url=forum.php?mod=redirect&goto=findpost&pid=$_G[gp_repquote]&ptid={$_G['tid']}][img]static/image/common/back.gif[/img][/url][/size]\n{$message}[/quote]";
			} else {
				$message = "[quote][color=#999999]{$post_reply_quote}[/color]\n[color=#999999]{$message}[/color][/quote]";
			}
			$quotemessage = discuzcode($message, 0, 0);
			$noticeauthor = htmlspecialchars(authcode('q|'.$thaquote['authorid'], 'ENCODE'));
			$noticetrimstr = htmlspecialchars($message);
			$message = '';
		}
		$reppid = $_G['gp_repquote'];

	} elseif(isset($_G['gp_reppost']) && $_G['gp_reppost'] = intval($_G['gp_reppost'])) {
		$posttable = getposttablebytid($_G['tid']);
		$thapost = DB::fetch_first("SELECT tid, author, authorid, useip, dateline, anonymous, status, message FROM ".DB::table($posttable)." WHERE pid='$_G[gp_reppost]' AND (invisible='0' OR (authorid='$_G[uid]' AND invisible='-2'))");

		if($thapost['tid'] != $_G['tid']) {
			showmessage('targetpost_donotbelongto_thisthread', NULL);
		}

		$thapost['useip'] = substr($thapost['useip'], 0, strrpos($thapost['useip'], '.')).'.x';
		if($thapost['author'] && $thapost['anonymous']) {
			$thapost['author'] = '[color=Olive]'.lang('forum/misc', 'anonymoususer').'[/color]';
		} elseif(!$thapost['author']) {
			$thapost['author'] = '[color=Olive]'.lang('forum/misc', 'guestuser').'[/color] '.$thapost['useip'];
		} else {
			$thapost['author'] = '[color=Olive]'.$thapost['author'].'[/color]';
		}
		$posttable = getposttablebytid($thapost['tid']);
		$quotemessage = discuzcode($message, 0, 0);
		$noticeauthormsg = htmlspecialchars(messagecutstr($thapost['message'], 100));
		$noticeauthor = htmlspecialchars(authcode('r|'.$thapost['authorid'], 'ENCODE'));
		$noticetrimstr = htmlspecialchars($message);
		$message = '';
		$reppid = $_G['gp_reppost'];
	}

	if(isset($_G['gp_addtrade']) && $thread['special'] == 2 && $_G['group']['allowposttrade'] && $thread['authorid'] == $_G['uid']) {
		$expiration_7days = date('Y-m-d', TIMESTAMP + 86400 * 7);
		$expiration_14days = date('Y-m-d', TIMESTAMP + 86400 * 14);
		$trade['expiration'] = $expiration_month = date('Y-m-d', mktime(0, 0, 0, date('m')+1, date('d'), date('Y')));
		$expiration_3months = date('Y-m-d', mktime(0, 0, 0, date('m')+3, date('d'), date('Y')));
		$expiration_halfyear = date('Y-m-d', mktime(0, 0, 0, date('m')+6, date('d'), date('Y')));
		$expiration_year = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')+1));
	}

	if($thread['replies'] <= $_G['ppp']) {
		$postlist = array();
		$posttable = getposttablebytid($_G['tid']);
		$query = DB::query("SELECT p.* ".($_G['setting']['bannedmessages'] ? ', m.groupid ' : '').
			"FROM ".DB::table($posttable)." p ".($_G['setting']['bannedmessages'] ? "LEFT JOIN ".DB::table('common_member')." m ON p.authorid=m.uid " : '').
			"WHERE p.tid='$_G[tid]' AND p.invisible='0' ".($thread['price'] > 0 && $thread['special'] == 0 ? 'AND p.first = 0' : '')." ORDER BY p.dateline DESC");
		while($post = DB::fetch($query)) {

			$post['dateline'] = dgmdate($post['dateline'], 'u');

			if($_G['setting']['bannedmessages'] && ($post['authorid'] && (!$post['groupid'] || $post['groupid'] == 4 || $post['groupid'] == 5))) {
				$post['message'] = $language['post_banned'];
			} elseif($post['status'] & 1) {
				$post['message'] = $language['post_single_banned'];
			} else {
				$post['message'] = preg_replace("/\[hide=?\d*\](.*?)\[\/hide\]/is", "[b]$language[post_hidden][/b]", $post['message']);
				$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], $post['htmlon'] & 1, $_G['forum']['allowsmilies'], $_G['forum']['allowbbcode'], $_G['forum']['allowimgcode'], $_G['forum']['allowhtml'], $_G['forum']['jammer']);
			}

			$postlist[] = $post;
		}
	}

	if($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) {
		$attachlist = getattach(0);
		$attachs = $attachlist['attachs'];
		$imgattachs = $attachlist['imgattachs'];
		unset($attachlist);
	}

	getgpc('infloat') ? include template('forum/post_infloat') : include template('forum/post');

} else {

	if(trim($subject) == '' && trim($message) == '' && $thread['special'] != 2) {
		showmessage('post_sm_isnull');
	} elseif($thread['closed'] && !$_G['forum']['ismoderator'] && !$thread['isgroup']) {
		showmessage('post_thread_closed');
	} elseif(!$thread['isgroup'] && $post_autoclose = checkautoclose($thread)) {
		showmessage($post_autoclose, '', array('autoclose' => $_G['forum']['autoclose']));
	} elseif($post_invalid = checkpost($subject, $message, $special == 2 && $_G['group']['allowposttrade'])) {
		showmessage($post_invalid, '', array('minpostsize' => $_G['setting']['minpostsize'], 'maxpostsize' => $_G['setting']['maxpostsize']));
	} elseif(checkflood()) {
		showmessage('post_flood_ctrl', '', array('floodctrl' => $_G['setting']['floodctrl']));
	} elseif(checkmaxpostsperhour()) {
		showmessage('post_flood_ctrl_posts_per_hour', '', array('posts_per_hour' => $_G['group']['maxpostsperhour']));
	}
	if(!empty($_G['gp_trade']) && $thread['special'] == 2 && $_G['group']['allowposttrade']) {

		$item_price = floatval($_G['gp_item_price']);
		$item_credit = intval($_G['gp_item_credit']);
		if(!trim($_G['gp_item_name'])) {
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
		} elseif($_G['gp_item_number'] < 1) {
			showmessage('tread_please_number');
		}

	}

	$attentionon = empty($_G['gp_attention_add']) ? 0 : 1;
	$attentionoff = empty($attention_remove) ? 0 : 1;

	if($thread['lastposter'] != $_G['member']['username'] && $_G['uid']) {
		if($_G['setting']['heatthread']['type'] == 1 && $_G['setting']['heatthread']['reply']) {
			$posttable = getposttablebytid($_G['tid']);
			$userreplies = DB::result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND first='0' AND authorid='$_G[uid]'");
			$thread['heats'] += round($_G['setting']['heatthread']['reply'] * pow(0.8, $userreplies));
			DB::query("UPDATE ".DB::table('forum_thread')." SET heats='$thread[heats]' WHERE tid='$_G[tid]'", 'UNBUFFERED');
		} elseif($_G['setting']['heatthread']['type'] == 2) {
			update_threadpartake($_G['tid']);
		}
	}

	$bbcodeoff = checkbbcodes($message, !empty($_G['gp_bbcodeoff']));
	$smileyoff = checksmilies($message, !empty($_G['gp_smileyoff']));
	$parseurloff = !empty($_G['gp_parseurloff']);
	$htmlon = $_G['group']['allowhtml'] && !empty($_G['gp_htmlon']) ? 1 : 0;
	$usesig = !empty($_G['gp_usesig']) && $_G['group']['maxsigsize'] ? 1 : 0;

	$isanonymous = $_G['group']['allowanonymous'] && !empty($_G['gp_isanonymous'])? 1 : 0;
	$author = empty($isanonymous) ? $_G['username'] : '';

	$pinvisible = $modnewreplies ? -2 : ($thread['displayorder'] == -4 ? -3 : 0);
	$message = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $message);
	$postcomment = in_array(2, $_G['setting']['allowpostcomment']) && $_G['group']['allowcommentreply'] && !$pinvisible && !empty($_G['gp_reppid']) && ($nauthorid != $_G['uid'] || $_G['setting']['commentpostself']) ? messagecutstr($message, 200, ' ') : '';

	if(!empty($_G['gp_noticetrimstr'])) {
		$message = $_G['gp_noticetrimstr']."\n\n".$message;
		$bbcodeoff = false;
	}

	$pid = insertpost(array(
		'fid' => $_G['fid'],
		'tid' => $_G['tid'],
		'first' => '0',
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
		'status' => (defined('IN_MOBILE') ? 8 : 0),
	));

	if($pid && getstatus($thread['status'], 1)) {
		$postionid = savepostposition($_G['tid'], $pid, true);
	}
	if(getstatus($thread['status'], 3) && $postionid) {
		$rushstopfloor = DB::result_first("SELECT stopfloor FROM ".DB::table('forum_threadrush')." WHERE tid = '$_G[tid]'");
		if($rushstopfloor > 0 && $thread['closed'] == 0 && $postionid >= $rushstopfloor) {
			DB::query("UPDATE ".DB::table('forum_thread')." SET closed='1' WHERE tid='$_G[tid]'");
		}
	}
	useractionlog($_G['uid'], 'pid');

	$nauthorid = 0;
	if(!empty($_G['gp_noticeauthor']) && !$isanonymous && !$modnewreplies) {
		list($ac, $nauthorid) = explode('|', authcode($_G['gp_noticeauthor'], 'DECODE'));
		if($nauthorid != $_G['uid']) {
			if($ac == 'q') {
				notification_add($nauthorid, 'post', 'reppost_noticeauthor', array(
					'tid' => $thread['tid'],
					'subject' => $thread['subject'],
					'fid' => $_G['fid'],
					'pid' => $pid,
				));
			} elseif($ac == 'r') {
				notification_add($nauthorid, 'post', 'reppost_noticeauthor', array(
					'tid' => $thread['tid'],
					'subject' => $thread['subject'],
					'fid' => $_G['fid'],
					'pid' => $pid,
					'from_id' => $thread['tid'],
					'from_idtype' => 'post',
				));
			}
		}

		if($postcomment) {
			$rpid = intval($_G['gp_reppid']);
			if(!$posttable) {
				$posttable = getposttablebytid($thread['tid']);
			}
			if($rpost = DB::fetch_first("SELECT first FROM ".DB::table($posttable)." WHERE pid='$rpid'")) {
				if(!$rpost['first']) {
					DB::insert('forum_postcomment', array(
						'tid' => $thread['tid'],
						'pid' => $rpid,
						'rpid' => $pid,
						'author' => $_G['username'],
						'authorid' => $_G['uid'],
						'dateline' => TIMESTAMP,
						'comment' => $postcomment,
						'score' => 0,
						'useip' => $_G['clientip'],
					));
					DB::update($posttable, array('comment' => 1), "pid='$rpid'");
				}
			}
			unset($postcomment);
		}
	}

	if($thread['authorid'] != $_G['uid'] && getstatus($thread['status'], 6) && empty($_G['gp_noticeauthor']) && !$isanonymous && !$modnewreplies) {
		$posttable = getposttablebytid($_G['tid']);
		$thapost = DB::fetch_first("SELECT tid, author, authorid, useip, dateline, anonymous, status, message FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND first='1' AND invisible='0'");
		notification_add($thapost['authorid'], 'post', 'reppost_noticeauthor', array(
			'tid' => $thread['tid'],
			'subject' => $thread['subject'],
			'fid' => $_G['fid'],
			'pid' => $pid,
			'from_id' => $thread['tid'],
			'from_idtype' => 'post',
		));
	}

	if($thread['replycredit'] > 0 && !$modnewreplies && $thread['authorid'] != $_G['uid'] && $_G['uid']) {

		$replycredit_rule = DB::fetch_first("SELECT * FROM ".DB::table('forum_replycredit')." WHERE tid = '$_G[tid]' LIMIT 1");
		if(!empty($replycredit_rule['times'])) {
			$have_replycredit = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_credit_log')." WHERE relatedid = '{$_G[tid]}' AND uid = '{$_G[uid]}' AND operation = 'RCA' LIMIT {$replycredit_rule['times']} ");
			if($replycredit_rule['membertimes'] - $have_replycredit > 0 && $thread['replycredit'] - $replycredit_rule['extcredits'] >= 0) {
				$replycredit_rule['extcreditstype'] = $replycredit_rule['extcreditstype'] ? $replycredit_rule['extcreditstype'] : $_G['setting']['creditstransextra'][10];
				if($replycredit_rule['random'] > 0) {
					$rand = rand(1, 100);
					$rand_replycredit = $rand <= $replycredit_rule['random'] ? true : false ;
				} else {
					$rand_replycredit = true;
				}
				if($rand_replycredit) {
					if(!$posttable) {
						$posttable = getposttablebytid($_G['tid']);
					}
					updatemembercount($_G['uid'], array($replycredit_rule['extcreditstype'] => $replycredit_rule['extcredits']), 1, 'RCA', $_G[tid]);
					DB::update($posttable, array('replycredit' => $replycredit_rule['extcredits']), array('pid' => $pid));
					DB::update("forum_thread", array('replycredit' => $thread['replycredit'] - $replycredit_rule['extcredits']), array('tid' => $_G[tid]));
				}
			}
		}
	}

	if($special == 5) {

		if(!DB::num_rows($standquery)) {
			if($stand == 1) {
				DB::query("UPDATE ".DB::table('forum_debate')." SET affirmdebaters=affirmdebaters+1 WHERE tid='$_G[tid]'");
			} elseif($stand == 2) {
				DB::query("UPDATE ".DB::table('forum_debate')." SET negadebaters=negadebaters+1 WHERE tid='$_G[tid]'");
			}
		} else {
			$stand = $firststand;
		}
		if($stand == 1) {
			DB::query("UPDATE ".DB::table('forum_debate')." SET affirmreplies=affirmreplies+1 WHERE tid='$_G[tid]'");
		} elseif($stand == 2) {
			DB::query("UPDATE ".DB::table('forum_debate')." SET negareplies=negareplies+1 WHERE tid='$_G[tid]'");
		}
		DB::query("INSERT INTO ".DB::table('forum_debatepost')." (tid, pid, uid, dateline, stand, voters, voterids) VALUES ('$_G[tid]', '$pid', '$_G[uid]', '$_G[timestamp]', '$stand', '0', '')");
	}

	($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) && ($_G['gp_attachnew'] || $special == 2 && $_G['gp_tradeaid']) && updateattach($thread['displayorder'] == -4 || $modnewreplies, $_G['tid'], $pid, $_G['gp_attachnew']);

	$replymessage = 'post_reply_succeed';
	if($special == 2 && $_G['group']['allowposttrade'] && $thread['authorid'] == $_G['uid'] && !empty($_G['gp_trade']) && !empty($_G['gp_item_name'])) {

		require_once libfile('function/trade');
		trade_create(array(
			'tid' => $_G['tid'],
			'pid' => $pid,
			'aid' => $_G['gp_tradeaid'],
			'item_expiration' => $_G['gp_item_expiration'],
			'thread' => $thread,
			'discuz_uid' => $_G['uid'],
			'author' => $author,
			'seller' => empty($_G['gp_paymethod']) && $_G['gp_seller'] ? dhtmlspecialchars(trim($_G['gp_seller'])) : '',
			'item_name' => $_G['gp_item_name'],
			'item_price' => $_G['gp_item_price'],
			'item_number' => $_G['gp_item_number'],
			'item_quality' => $_G['gp_item_quality'],
			'item_locus' => $_G['gp_item_locus'],
			'transport' => $_G['gp_transport'],
			'postage_mail' => $_G['gp_postage_mail'],
			'postage_express' => $_G['gp_postage_express'],
			'postage_ems' => $_G['gp_postage_ems'],
			'item_type' => $_G['gp_item_type'],
			'item_costprice' => $_G['gp_item_costprice'],
			'item_credit' => $_G['gp_item_credit'],
			'item_costcredit' => $_G['gp_item_costcredit']
		));

		$replymessage = 'trade_add_succeed';
		if(!empty($_G['gp_tradeaid'])) {
			convertunusedattach($_G['gp_tradeaid'], $_G['tid'], $pid);
		}

	}

	if($specialextra) {

		@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newreply_submit_end')) {
			$threadpluginclass->newreply_submit_end($_G['fid'], $_G['tid']);
		}

	}

	$_G['forum']['threadcaches'] && deletethreadcaches($_G['tid']);

	include_once libfile('function/stat');
	updatestat($thread['isgroup'] ? 'grouppost' : 'post');

	$param = array('fid' => $_G['fid'], 'tid' => $_G['tid'], 'pid' => $pid, 'from' => $_G['gp_from'], 'sechash' => !empty($_G['gp_sechash']) ? $_G['gp_sechash'] : '');

	dsetcookie('clearUserdata', 'forum');

	if($modnewreplies) {
		updatemoderate('pid', $pid);
		unset($param['pid']);
		DB::query("UPDATE ".DB::table('forum_forum')." SET todayposts=todayposts+1, modworks='1' WHERE fid='$_G[fid]'", 'UNBUFFERED');
		$url = empty($_POST['portal_referer']) ? ("forum.php?mod=viewthread&tid={$thread[tid]}") :  $_POST['portal_referer'];
		manage_addnotify('verifypost');
		if(!isset($inspacecpshare)) {
			showmessage('post_reply_mod_succeed', $url, $param);
		}
	} else {
		$lastpostsql = $thread['lastpost'] < $_G['timestamp'] ? "lastpost='$_G[timestamp]'," : '';
		DB::query("UPDATE ".DB::table('forum_thread')." SET lastposter='$author', $lastpostsql replies=replies+1 WHERE tid='$_G[tid]'", 'UNBUFFERED');

		if($thread['displayorder'] != -4) {
			updatepostcredits('+', $_G['uid'], 'reply', $_G['fid']);
			if($_G['forum']['status'] == 3) {
				if($_G['forum']['closed'] > 1) {
					DB::query("UPDATE ".DB::table('forum_thread')." SET lastposter='$author', $lastpostsql replies=replies+1 WHERE tid='".$_G['forum']['closed']."'", 'UNBUFFERED');
				}
				DB::query("UPDATE ".DB::table('forum_groupuser')." SET replies=replies+1, lastupdate='".TIMESTAMP."' WHERE uid='$_G[uid]' AND fid='$_G[fid]'");
				updateactivity($_G['fid'], 0);
				require_once libfile('function/grouplog');
				updategroupcreditlog($_G['fid'], $_G['uid']);
			}

			$lastpost = "$thread[tid]\t".addslashes($thread['subject'])."\t$_G[timestamp]\t$author";
			DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost', posts=posts+1, todayposts=todayposts+1 WHERE fid='$_G[fid]'", 'UNBUFFERED');
			if($_G['forum']['type'] == 'sub') {
				DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost' WHERE fid='".$_G['forum']['fup']."'", 'UNBUFFERED');
			}
		}

		$feed = array();
		if(!isset($_G['gp_addfeed'])) {
			$space = array();
			space_merge($space, 'field_home');
			$_G['gp_addfeed'] = $space['privacy']['feed']['newreply'];
		}
		if(!empty($_G['gp_addfeed']) && $_G['forum']['allowfeed'] && !$isanonymous) {
			if($special == 2 && !empty($_G['gp_trade'])) {
				$feed['icon'] = 'goods';
				$feed['title_template'] = 'feed_thread_goods_title';
				if($_G['gp_item_price'] > 0) {
					if($_G['setting']['creditstransextra'][5] != -1 && $_G['gp_item_credit']) {
						$feed['body_template'] = 'feed_thread_goods_message_1';
					} else {
						$feed['body_template'] = 'feed_thread_goods_message_2';
					}
				} else {
					$feed['body_template'] = 'feed_thread_goods_message_3';
				}
				$feed['body_data'] = array(
					'itemname'=> "<a href=\"forum.php?mod=viewthread&do=tradeinfo&tid=$_G[tid]&pid=$pid\">$_G[gp_item_name]</a>",
					'itemprice'=> $_G['gp_item_price'],
					'itemcredit'=> $_G['gp_item_credit'],
					'creditunit'=> $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][5]]['unit'].$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][5]]['title'],
				);
				if($_G['gp_tradeaid']) {
					$feed['images'] = array(getforumimg($_G['gp_tradeaid']));
					$feed['image_links'] = array("forum.php?mod=viewthread&do=tradeinfo&tid=$_G[tid]&pid=$pid");
				}
			} elseif($special == 3 && $thread['authorid'] != $_G['uid']) {
				$feed['icon'] = 'reward';
				$feed['title_template'] = 'feed_reply_reward_title';
				$feed['title_data'] = array(
					'subject' => "<a href=\"forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a>",
					'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[author]</a>"
				);
			} elseif($special == 5 && $thread['authorid'] != $_G['uid']) {
				$feed['icon'] = 'debate';
				if($stand == 1) {
					$feed['title_template'] = 'feed_thread_debatevote_title_1';
				} elseif($stand == 2) {
					$feed['title_template'] = 'feed_thread_debatevote_title_2';
				} else {
					$feed['title_template'] = 'feed_thread_debatevote_title_3';
				}
				$feed['title_data'] = array(
					'subject' => "<a href=\"forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a>",
					'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[author]</a>"
				);
			} elseif($thread['authorid'] != $_G['uid']) {
				$post_url = "forum.php?mod=redirect&goto=findpost&pid=$pid&ptid=$_G[tid]";

				$feed['icon'] = 'post';
				$feed['title_template'] = !empty($thread['author']) ? 'feed_reply_title' : 'feed_reply_title_anonymous';
				$feed['title_data'] = array(
					'subject' => "<a href=\"$post_url\">$thread[subject]</a>",
					'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[author]</a>"
				);
				if(!empty($_G['forum_attachexist'])) {
					$firstaid = DB::result_first("SELECT aid FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE pid='$pid' AND dateline>'0' AND isimage='1' ORDER BY dateline LIMIT 1");
					if($firstaid) {
						$feed['images'] = array(getforumimg($firstaid));
						$feed['image_links'] = array($post_url);
					}
				}
			}
			$feed['title_data']['hash_data'] = "tid{$_G[tid]}";
			$feed['id'] = $pid;
			$feed['idtype'] = 'pid';
			if($feed['icon']) {
				postfeed($feed);
			}
		}

		$page = getstatus($thread['status'], 4) ? 1 : @ceil(($thread['special'] ? $thread['replies'] + 1 : $thread['replies'] + 2) / $_G['ppp']);

		if($special == 2 && !empty($_G['gp_continueadd'])) {
			dheader("location: forum.php?mod=post&action=reply&fid={$_G[forum][fid]}&firstpid=$pid&tid={$thread[tid]}&addtrade=yes");
		} else {
			$url = empty($_POST['portal_referer']) ? "forum.php?mod=viewthread&tid={$thread[tid]}&pid=$pid&page=$page&extra=$extra#pid$pid" : $_POST['portal_referer'];
		}
		if(!isset($inspacecpshare)) {
			showmessage($replymessage, $url, $param);
		}
	}

}

?>