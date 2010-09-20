<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: viewcomment.php 4378 2010-09-09 02:55:13Z fanshengshuai $
 */

if(!defined('IN_BRAND')) {
	exit('Access Denied');
}

include_once(B_ROOT.'./source/function/misc.func.php');

$_POST['typeid'] = intval($_POST['typeid']);
$_POST['delitemid'] = intval($_POST['delitemid']);
if(!in_array($_POST['type'], array('shop', 'good', 'consume', 'notice', 'photo', 'albumn', 'groupbuy'))) {
//提交評論類型的限制
	showmessage('Type Denied');
}

if($_G['setting']['commstatus'] != 1) {
	showmessage('comment_fobidden', 'index.php');
}
if(submitcheck('submitdelcomm') && $_SGET['do'] == 'del') {
	if(!array_key_exists($_POST['typeid'], $_G['myshopsarr']) && !ckfounder($_G['uid']) ){
		showmessage("no_permission", $_POST['stuffurl']);
	} else {
		$query = DB::query("SELECT uid FROM ".tname($_POST['type']."items")." WHERE itemid = '$_POST[typeid]' LIMIT 1");
		$itemuid = DB::result($query,0);
		if($itemuid != $_G['uid'] && !ckfounder($_G['uid'])) {
			showmessage("no_permission", $_POST['stuffurl']);
		} else {
			$query = DB::query("DELETE FROM ".tname("spacecomments")." WHERE cid = '$_POST[delitemid]' AND itemid = '$_POST[typeid]'");
			$affected_rows = DB::affected_rows($query);
			DB::query("DELETE FROM ".tname("spacecomments")." WHERE itemid = '$_POST[typeid]' AND upcid = '$_POST[delitemid]'");
			if($affected_rows) {
				DB::query("UPDATE ".tname($_POST['type']."items")." SET replynum=replynum-1 WHERE itemid='$_POST[typeid]'");
				$_BCACHE->deltype('storelist', 'comment', $_POST['typeid']);
				showmessage("do_success", $_POST['stuffurl']);
			}
		}
	}
} elseif(submitcheck('submitcomm', 1)) {

	$checkresults = array();
	$itemid = empty($_POST['itemid']) ? 0 : intval($_POST['itemid']);
	$ismodle = empty($_POST['ismodle']) ? 0 : intval($_POST['ismodle']);
	$isprivate = empty($_POST['isprivate']) ? 0 : intval($_POST['isprivate']);
	$type = empty($_POST['type']) ? 'news' : trim($_POST['type']);
	$commentscorestr = empty($_POST['commentscorestr']) ? '' : trim($_POST['commentscorestr']);

	$allowreply = DB::result_first('SELECT allowreply FROM '.tname($type."items")." WHERE itemid = '$itemid'");
	if(!$allowreply) {
		array_push($checkresults, array('message'=>$lang['noperm_forcomment']));
	}
	if($type == 'shop' && !empty($commentscorestr) && !$_G['setting']['commentmodel']) {
		array_push($checkresults, array('score'=>$lang['noperm_forremark']));
	}
	$upcid = empty($_POST['upcid'])?0:intval($_POST['upcid']);
	if(empty($itemid)) {
		array_push($checkresults, array('message'=>$lang['not_found']));
	}
	if(empty($_G['uid'])) {
		if(empty($_G['setting']['allowguest'])) {
			setcookie('_refer', rawurlencode(geturl('action/viewcomment/itemid/'.$itemid, 1)));
			array_push($checkresults, array('message'=>$lang['no_login']));
		}
	}
	$table_name = ($ismodle ? $type : 'space').'items';
	$query = DB::query('SELECT * FROM '.tname($table_name).' WHERE itemid=\''.$itemid.'\' AND allowreply=\'1\'');
	if(!$item = DB::fetch($query)) {
		array_push($checkresults, array('message'=>$lang['no_permission']));
	}
	$_POST['commentmessage'] = shtmlspecialchars(trim($_POST['commentmessage']));
	if($_POST['commentmessage'] == $_G['setting']['commdefault'] || bstrlen($_POST['commentmessage']) < 1 || bstrlen($_POST['commentmessage']) > 250) {
		array_push($checkresults, array('commentmessage'=>$lang['wordlimited']));
	}

	if(!empty($commentscorestr)) {
		$rootcatid = getrootcatid($item['catid']);
		$scorenum = DB::result_first("SELECT cm.scorenum FROM ".tname('categories')." c
										LEFT JOIN ".tname('commentmodels')." cm ON cm.cmid=c.cmid
										WHERE c.catid = '$rootcatid'");
		if(bstrlen($commentscorestr) < ($scorenum * 5)) {
			array_push($checkresults, array('score'=>$lang['scorelimited']));
		}
	}
	if(!empty($_G['setting']['commenttime']) && !ckfounder($_G['uid'])) {
		if($_G['timestamp'] - $_G['member']['lastcommenttime'] < $_G['setting']['commenttime']) {
			array_push($checkresults, array('message'=>$lang['comment_too_much']));
		}
	}
	if(!empty($checkresults)) {
		showmessage('comment_submit_error', '', '', '', $checkresults);
	}
	//更新用戶最新更新時間
	if($_G['uid']) {
		updatetable('members', array('updatetime'=>$_G['timestamp'], 'lastcommenttime'=>$_G['timestamp']), array('uid'=>$_G['uid']));
	}

	$_POST['commentmessage'] = str_replace('[br]', '<br>', $_POST['commentmessage']);
	$_POST['commentmessage'] = '<div class=\"new\"><span name=\"cid_{cid}_info\">'.preg_replace("/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is", "<div class=\"quote\"><blockquote>\\1</blockquote></div>", $_POST['commentmessage']).'</span></div>';
	$_POST['type'] = saddslashes($_POST['type']);

	//關於蓋樓
	$comment = array('floornum' => 0, 'firstcid' =>0);
	if($upcid) {
		if($_G['uid'] != $item['uid']) showmessage('no_permission');
		$query = DB::query('SELECT * FROM '.tname('spacecomments').' WHERE cid=\''.$upcid.'\' AND status=\'1\'');
		if($comment = DB::fetch($query)) {
			$comment['floornum'] += 1;
			if($comment['floornum']==1) $comment['firstcid'] = $comment['cid'];
		} else {
			$upcid = 0;
		}
	}

	if($comment['floornum']) {

		$comment['hideauthor'] = (!empty($comment['hideauthor']) && !empty($_G['setting']['commanonymous'])) ? 1 : 0;
		$comment['hideip'] = (!empty($comment['hideip']) && !empty($_G['setting']['commhideip'])) ? 1 : 0;
		$comment['hidelocation'] = (!empty($comment['hidelocation']) && !empty($_G['setting']['commhidelocation'])) ? 1 : 0;
		$comment['iplocation'] = str_replace(array('-', ' '), '', convertip($comment['ip']));
		$comment['ip'] = preg_replace("/^(\d{1,3})\.(\d{1,3})\.\d{1,3}\.\d{1,3}$/", "\$1.\$2.*.*", $comment['ip']);

		$html = '<div id="cid_{cid}_'.$comment['floornum'].'_title" class="old_title"><span class="author">'.$_G['setting']['sitename'];
		if (!$comment['hidelocation']) $html .= $comment['iplocation']!='LAN' ? $comment['iplocation'] : $lang['mars'];
		$html .= $lang['visitor'];
		if (!empty($comment['authorid']) && !$comment['hideauthor']) $html .= " [{$comment['author']}] ";
		if (!$comment['hideip']) $html .= " ({$comment['ip']}) ";
		$html .= $lang['from_the_original_note'].'</span><span class="color_red">'.$comment['floornum'].'</span></div>';
		$comment['message'] = str_replace('<div class="new"', $html.'<div id="cid_{cid}_'.$comment['floornum'].'_detail" class="detail"', $comment['message']);
		$comment['message'] = '<div id="cid_{cid}_'.$comment['floornum'].'" class="old">'.$comment['message'].'</div>';
		$comment['message'] = saddslashes($comment['message']);
		$_POST['message'] = $comment['message'].$_POST['commentmessage'];
	}

	//回複詞語屏蔽
	$_POST['commentmessage'] = censor($_POST['commentmessage']);
	$shopuid = getshopuid($type);
	$subtype = !empty($commentscorestr) ? '1' : '0';
	$setsqlarr = array(
		'itemid' => $itemid,
		'type' => $type,
		'uid' => $item['uid'],
		'authorid' => $_G['uid'],
		'author' => $_G['username'],
		'ip' => $_G['clientip'],
		'dateline' => $_G['timestamp'],
		'subject' => '',
		'message' => $_POST['commentmessage'],
		'floornum' => $comment['floornum'],
		'hideauthor' => $_POST['hideauthor'],
		'hideip' => $_POST['hideip'],
		'hidelocation' => $_POST['hidelocation'],
		'firstcid' => $comment['firstcid'],
		'upcid' => $upcid,
		'shopuid' => $shopuid,
		'status' => 1,
		'isprivate' => $isprivate,
		'subtype' => $subtype
	);

	$cid = inserttable('spacecomments', $setsqlarr, 1);

	if($cid && !empty($commentscorestr)) {
		$commentscore = $score = 0;
		$commentscorearr = array();
		for($i = 1; $i <= 8; $i++) {
			if(strpos($commentscorestr, '1'.$i.'@')) {
				$commentscore = substr($commentscorestr, strpos($commentscorestr, '@1'.$i.'@') + 4, 1);
				if(is_numeric($commentscore) && $commentscore <= 5 && $commentscore > 0) {
					$commentscorearr['score'.$i] = intval($commentscore);
				}
			}
		}
		$commentscorearr['score'] = array_sum($commentscorearr) / count($commentscorearr);
		$setsqlarr1 = array(
			'cid' => $cid,
			'score' => $commentscorearr['score'],
			'score1' => $commentscorearr['score1'],
			'score2' => $commentscorearr['score2'],
			'score3' => $commentscorearr['score3'],
			'score4' => $commentscorearr['score4'],
			'score5' => $commentscorearr['score5'],
			'score6' => $commentscorearr['score6'],
			'score7' => $commentscorearr['score7'],
			'score8' => $commentscorearr['score8']
		);
		inserttable('commentscores', $setsqlarr1);

		$scorestats = DB::fetch(DB::query('SELECT * FROM '.tname('scorestats').' WHERE itemid=\''.$itemid.'\''));
		if(!empty($scorestats)) {
			$scorestats['remarknum'] += 1;
			$scorestats['score'] += $commentscorearr['score'];
			for($i = 1; $i <= 8; $i++) {
				if($commentscorearr['score'.$i]) {
					$scorestats['score'.$i] += $commentscorearr['score'.$i];
				}
			}
			inserttable('scorestats', $scorestats, 0, 1);
		} else {
			$commentscorearr['itemid'] = $itemid;
			$commentscorearr['type'] = $_POST['type'];
			$commentscorearr['remarknum'] += 1;
			inserttable('scorestats', $commentscorearr);
		}
	}

	$_POST['commentmessage'] = str_replace(array('cid_{cid}_', 'cid_'.$comment['cid'].'_'), 'cid_'.$cid.'_', $_POST['commentmessage']);
	updatetable('spacecomments', array('message'=>$_POST['commentmessage']), array('cid'=>$cid));
	if(!$upcid) {
		DB::query('UPDATE '.tname($table_name).' SET lastpost='.$_G['timestamp'].', replynum=replynum+1 WHERE itemid=\''.$itemid.'\'');
	}

	$_BCACHE->deltype('storelist', 'comment', $itemid);

	if($_G['inajax'] == 1) {
		if($upcid) {
			$comment = DB::fetch(DB::query("SELECT message AS recomment, dateline AS replytime FROM ".tname('spacecomments')." WHERE cid='$cid' AND upcid='$upcid' AND status='1'"));
			$topage = $_POST['page'] ? '&page='.$_POST['page'] : '';
			$url = $_POST['stuffurl'].$topage.'#comment'.$upcid;
			showxmlheader($_G['charset']);
			echo '<root>
						<status>NEWRECOMMENT</status>
						<upcid>'.$upcid.'</upcid>
						<url><![CDATA['.$url.']]></url>
						<message><![CDATA['.$lang['comment_success'].']]></message>
						<content><![CDATA[';
			include template('templates/store/default/comment_recomment.html.php', 1);
			echo '		]]></content>
					</root>';
		} else {
			$_SGLOBAL['commentmodel'] = getcommentmodel($_SGLOBAL['shopcates'][$rootcatid]['cmid']);
			$comment = DB::fetch(DB::query("SELECT c.*, cs.score, cs.score1, cs.score2, cs.score3, cs.score4, cs.score5, cs.score6, cs.score7, cs.score8 FROM ".tname('spacecomments')." c
							LEFT JOIN ".tname('commentscores')." cs ON c.cid = cs.cid
							WHERE c.cid='$cid' AND c.upcid = 0 AND c.status='1'"));
			showxmlheader($_G['charset']);
			echo '<root>
						<status>NEWCOMMENT</status>
						<message><![CDATA['.$lang['comment_success'].']]></message>
						<content><![CDATA[';
			include template('templates/store/default/comment_node.html.php', 1);
			echo '		]]></content>
					</root>';
		}
		exit;
	} else {
		if($_POST['stuffurl']) {
			$topage = '';
			if($upcid) {
				$topage = $_POST['page'] ? '&page='.$_POST['page'] : '';
				$url = $_POST['stuffurl'].$topage.'#comment'.$upcid;
			} else {
				$commentlistarr = array();
				$commentnum = 0;
				$sql = "SELECT c.*, r.message AS recomment, r.dateline AS replytime, cs.score, cs.score1, cs.score2, cs.score3, cs.score4, cs.score5, cs.score6, cs.score7, cs.score8 FROM ".tname('spacecomments')." c LEFT JOIN ".tname('spacecomments')." r ON c.cid = r.upcid LEFT JOIN ".tname('commentscores')." cs ON c.cid = cs.cid WHERE c.type = '$type' AND c.upcid = 0 AND c.itemid='$comment_itemid' AND c.status='1' ORDER BY c.cid ".($_G['setting']['commorderby']?'DESC':'ASC');
				$_BCACHE->cachesql('shopcomment', $sql, 0, 1, 10, 0, 'storelist', 'comment', $itemid);
				$commentnum = $_SBLOCK['shopcomment_listcount'];
				$multipage = $_SBLOCK['shopcomment_multipage'];
				foreach($_SBLOCK['shopcomment'] as $comment) {
					$execute = true;
					if($comment['isprivate'] == 1) {
						if(!in_array($_G['uid'], array($comment['authorid'], $comment['shopuid']))) {
							$execute = false;
						}
					}
					if($execute) {
						$commentnum++;
					}
				}
				$topage = @ceil($commentnum / $_G['setting']['commentperpage']);
				$topage = '&page='.$topage;
				$url = $_POST['stuffurl'].$topage.'#comment'.$cid;
			}
		} else {
			$url = geturl('action/viewcomment/type/'.$type.'/itemid/'.$itemid);
		}
		showmessage('do_success', $url);
	}
}
?>