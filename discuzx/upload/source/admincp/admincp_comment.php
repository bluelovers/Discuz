<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_comment.php 29125 2012-03-27 06:21:23Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$detail = !empty($_GET['authorid']) ? true : $_G['gp_detail'];
$idtype = $_G['gp_idtype'];
$id = $_G['gp_id'];
$author = $_G['gp_author'];
$authorid = $_G['gp_authorid'];
$uid = $_G['gp_uid'];
$message = $_G['gp_message'];
$ip = $_G['gp_ip'];
$users = $_G['gp_users'];
$starttime = $_G['gp_starttime'];
$endtime = $_G['gp_endtime'];
$searchsubmit = $_G['gp_searchsubmit'];
$cids = $_G['gp_cids'];
$page = max(1, $_G['gp_page']);

$fromumanage = $_G['gp_fromumanage'] ? 1 : 0;

cpheader();
if(empty($operation)) {
	if(!submitcheck('commentsubmit')) {

		if($fromumanage) {
			$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? '' : $starttime;
			$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? '' : $endtime;
		} else {
			$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $starttime;
			$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? dgmdate(TIMESTAMP, 'Y-n-j') : $endtime;
		}

		shownav('topic', 'nav_comment');
		showsubmenu('nav_comment', array(
			array('comment_comment', 'comment', 1),
			array('comment_article_comment', 'comment&operation=article', 0),
			array('comment_topic_comment', 'comment&operation=topic', 0)
		));
		showtips('comment_tips');
		echo <<<EOT
	<script type="text/javascript" src="static/js/calendar.js"></script>
	<script type="text/JavaScript">
	function page(number) {
		$('commentforum').page.value=number;
		$('commentforum').searchsubmit.click();
	}
	</script>
EOT;
		showtagheader('div', 'searchposts', !$searchsubmit);
		showformheader("comment", '', 'commentforum');
		showhiddenfields(array('page' => $page, 'pp' => $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage']));
		showtableheader();
		showsetting('comment_search_detail', 'detail', $detail, 'radio');
		showsetting('comment_search_perpage', '', $_G['gp_perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
		showsetting('comment_idtype', array('idtype', array(
			array('', $lang['all']),
			array('uid', $lang['comment_uid']),
			array('blogid', $lang['comment_blogid']),
			array('picid', $lang['comment_picid']),
			array('sid', $lang['comment_sid']),
		)), 'comment_idtype', 'select');
		showsetting('comment_search_id', 'id', $id, 'text');
		showsetting('comment_search_author', 'author', $author, 'text');
		showsetting('comment_search_authorid', 'authorid', $authorid, 'text');
		showsetting('comment_search_uid', 'uid', $uid, 'text');
		showsetting('comment_search_message', 'message', $message, 'text');
		showsetting('comment_search_ip', 'ip', $ip, 'text');
		showsetting('comment_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
		echo '<input type="hidden" name="fromumanage" value="'.$fromumanage.'">';
		showsubmit('searchsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

	} else {
		$cids = authcode($cids, 'DECODE');
		$cidsadd = $cids ? explode(',', $cids) : $_G['gp_delete'];
		include_once libfile('function/delete');
		$deletecount = count(deletecomments($cidsadd));
		$cpmsg = cplang('comment_succeed', array('deletecount' => $deletecount));

	?>
	<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('commentforum').searchsubmit.click();</script>
	<?php

	}

	if(submitcheck('searchsubmit', 1)) {

		$comments = $commentcount = '0';
		$sql = $error = '';
		$author = trim($author);

		if($id !='') {
			$sql .=" AND c.id IN ('".str_replace(',', '\',\'', str_replace(' ', '', $id))."')";
		}

		if($author != '') {
			$authorids = array();
			$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $author))."')");
			while($arr = DB::fetch($query)) {
				$authorids[] = intval($arr['uid']);
			}
			$authorid = ($authorid ? $authorid.',' : '').implode(',',$authorids);
		}

		$authorid = trim($authorid, ', ');
		if($authorid != '') {
			$sql .= " AND c.authorid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $authorid))."')";
		}

		if($idtype != '') {
			$sql .= " AND c.idtype='$idtype'";
		}

		if($starttime != '') {
			$starttime = strtotime($starttime);
			$sql .= " AND c.dateline>'$starttime'";
		}

		if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j')) {
			if($endtime != '') {
				$endtime = strtotime($endtime);
				$sql .= " AND c.dateline<'$endtime'";
			}
		} else {
			$endtime = TIMESTAMP;
		}

		if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
			$error = 'comment_mod_range_illegal';
		}

		$uid = trim($uid, ', ');
		if($uid !='') {
			$sql .=" AND c.uid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $uid))."')";
		}

		if($message != '') {
			$sqlmessage = '';
			$or = '';
			$message = explode(',', str_replace(' ', '', $message));

			for($i = 0; $i < count($message); $i++) {
				if(preg_match("/\{(\d+)\}/", $message[$i])) {
					$message[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($message[$i], '/'));
					$message .= " $or c.message REGEXP '".$message[$i]."'";
				} else {
					$sqlmessage .= " $or c.message LIKE '%".$message[$i]."%'";
				}
				$or = 'OR';
			}
			$sql .= " AND ($sqlmessage)";
		}

		if($ip != '') {
			$sql .= " AND c.ip LIKE '".str_replace('*', '%', $ip)."'";
		}

		if(!$error) {
			if(($commentcount = DB::result_first("SELECT count(*) FROM ".DB::table('home_comment')." c WHERE 1 $sql"))) {
				if($detail) {
					$_G['gp_perpage'] = intval($_G['gp_perpage']) < 1 ? 20 : intval($_G['gp_perpage']);
					$perpage = $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage'];
					$query = DB::query("SELECT c.cid, c.uid, c.message, c.author, c.idtype, c.id, c.ip, c.dateline FROM ".DB::table('home_comment')." c  WHERE 1 $sql ORDER BY c.dateline DESC LIMIT ".(($page - 1) * $perpage).",{$perpage}");

					$comments = '';

					while($comment = DB::fetch($query)) {
						$comment['dateline'] = dgmdate($comment['dateline']);
						switch($comment['idtype']) {
							case 'picid':
								$address = "<a href=\"home.php?mod=space&uid=$comment[uid]&do=album&picid=$comment[id]\" target=\"_blank\">$comment[message]</a>";
								break;
							case 'uid':
								$address = "<a href=\"home.php?mod=space&uid=$comment[uid]&do=uid\" target=\"_blank\">$comment[message]</a>";
								break;
							case 'sid':
								$address = "<a href=\"home.php?mod=space&uid=1&do=share&id=$comment[id]\" target=\"_blank\">$comment[message]</a>";
								break;
							case 'blogid':
								$address = "<a href=\"home.php?mod=space&uid=$comment[uid]&do=blog&id=$comment[id]\" target=\"_blank\">$comment[message]</a>";
								break;
						}
						$comments .= showtablerow('', '', array(
							"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$comment[cid]\" />",
							$address,
							"<a href=\"home.php?mod=space&uid=$comment[uid]\" target=\"_blank\">$comment[author]</a>",
							$comment['ip'],
							$comment['idtype'],
							$comment['dateline']
						), TRUE);
					}
					$multi = multi($commentcount, $perpage, $page, ADMINSCRIPT."?action=comment");
					$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=comment&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
					$multi = str_replace("window.location='".ADMINSCRIPT."?action=comment&amp;page='+this.value", "page(this.value)", $multi);
			} else {
				$query = DB::query("SELECT c.cid FROM ".DB::table('home_comment')." c WHERE 1 $sql");
				while($comment = DB::fetch($query)) {
					$cids .= ','.$comment['cid'];
				}
			}
		} else
			$error = 'comment_post_nonexistence';
		}

		showtagheader('div', 'postlist', $searchsubmit);
		showformheader('comment&frame=no', 'target="commentframe"');
		showhiddenfields(array('cids' => authcode($cids, 'ENCODE')));
		showtableheader(cplang('comment_result').' '.$commentcount.' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'commentforum\').pp.value=\'\';$(\'commentforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>', 'fixpadding');

		if($error) {
			echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
		} else {
			if($detail) {
				showsubtitle(array('', 'message', 'author', 'ip', 'comment_idtype', 'time'));
				echo $comments;
			}
		}

		showsubmit('commentsubmit', 'delete', $detail ? 'del' : '', '', $multi);
		showtablefooter();
		showformfooter();
		echo '<iframe name="commentframe" style="display:none"></iframe>';
		showtagfooter('div');

	}
}

if($operation == 'article' || $operation == 'topic') {

	$aid = $_G['gp_aid'];
	$subject = $_G['gp_subject'];
	$idtype = $operation == 'article' ? 'aid' : 'topicid';
	$tablename = $idtype == 'aid' ? 'portal_article_title' : 'portal_topic';

	if(!submitcheck('articlesubmit')) {

		$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $starttime;
		$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? dgmdate(TIMESTAMP, 'Y-n-j') : $endtime;

		shownav('topic', 'nav_comment');
		showsubmenu('nav_comment', array(
			array('comment_comment', 'comment', 0),
			array('comment_article_comment', 'comment&operation=article', $operation == 'article' ? 1 : 0),
			array('comment_topic_comment', 'comment&operation=topic',  $operation == 'topic' ? 1 : 0)
		));
		showtips('comment_'.$operation.'_tips');
		echo <<<EOT
	<script type="text/javascript" src="static/js/calendar.js"></script>
	<script type="text/JavaScript">
	function page(number) {
		$('articleforum').page.value=number;
		$('articleforum').searchsubmit.click();
	}
	</script>
EOT;
		showtagheader('div', 'searchposts', !$searchsubmit);
		showformheader("comment&operation=$operation", '', 'articleforum');
		showhiddenfields(array('page' => $page, 'pp' => $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage']));
		showtableheader();
		showsetting('comment_search_perpage', '', $_G['gp_perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
		showsetting("comment_{$operation}_subject", 'subject', $subject, 'text');
		showsetting("comment_{$operation}_id", 'aid', $aid, 'text');
		showsetting('comment_search_message', 'message', $message, 'text');
		showsetting('comment_search_author', 'author', $author, 'text');
		showsetting('comment_search_authorid', 'authorid', $authorid, 'text');
		showsetting('comment_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
		showsubmit('searchsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');

	} else {

		$cidsadd = dimplode($_G['gp_delete']);

		$query = DB::query("SELECT id, idtype FROM ".DB::table('portal_comment')." WHERE cid IN (".$cidsadd.")");
		while($value = DB::fetch($query)) {
			$updatetablename = $value['idtype'] == 'aid' ? 'portal_article_count' : 'portal_topic';
			DB::query("UPDATE ".DB::table($updatetablename)." SET commentnum=commentnum-1 WHERE $value[idtype]='$value[id]'");
		}
		DB::query("DELETE FROM ".DB::table('portal_comment')." WHERE cid IN (".$cidsadd.")");
		$cpmsg = cplang('comment_article_delete');

	?>
	<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('articleforum').searchsubmit.click();</script>
	<?php
	}

	if(submitcheck('searchsubmit')) {

		$comments = $commentcount = '0';
		$sql = $error = '';
		$author = trim($author);

		if($subject != '') {
			$sqlsubject = '';
			$or = '';
			$subject = explode(',', str_replace(' ', '', $subject));

			for($i = 0, $l = count($subject); $i < $l; $i++) {
				if(preg_match("/\{(\d+)\}/", $subject[$i])) {
					$subject[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($subject[$i], '/'));
					$sqlsubject .= " $or title REGEXP '".$subject[$i]."'";
				} else {
					$sqlsubject .= " $or title LIKE '%".$subject[$i]."%'";
				}
				$or = 'OR';
			}
			if($sqlsubject) {
				$ids = array();
				$query = DB::query("SELECT $idtype FROM ".DB::table($tablename)." WHERE $sqlsubject");
				while(($value=DB::fetch($query))) {
					$ids[] = intval($value[$idtype]);
				}
				$aid = ($aid ? $aid.',':'').implode(',',$ids);
			}
		}

		if($aid !='') {
			$sql .=" AND c.id IN ('".str_replace(',', '\',\'', str_replace(' ', '', $aid))."')";
		}

		if($idtype != '') {
			$sql .= " AND c.idtype='$idtype'";
		}

		if($author != '') {
			$authorids = array();
			$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $author))."')");
			while($arr = DB::fetch($query)) {
				$authorids[] = intval($arr['uid']);
			}
			$authorid = ($authorid ? $authorid.',' : '').implode(',',$authorids);
		}

		$authorid = trim($authorid,', ');
		if($authorid != '') {
			$sql .= " AND c.uid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $authorid))."')";
		}

		if($starttime != '0') {
			$starttime = strtotime($starttime);
			$sql .= " AND c.dateline>'$starttime'";
		}

		if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j')) {
			if($endtime != '0') {
				$endtime = strtotime($endtime);
				$sql .= " AND c.dateline<'$endtime'";
			}
		} else {
			$endtime = TIMESTAMP;
		}

		if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
			$error = 'comment_mod_range_illegal';
		}

		if($message != '') {
			$sqlmessage = '';
			$or = '';
			$message = explode(',', str_replace(' ', '', $message));

			for($i = 0, $l = count($message); $i < $l; $i++) {
				if(preg_match("/\{(\d+)\}/", $message[$i])) {
					$message[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($message[$i], '/'));
					$sqlmessage .= " $or c.message REGEXP '".$message[$i]."'";
				} else {
					$sqlmessage .= " $or c.message LIKE '%".$message[$i]."%'";
				}
				$or = 'OR';
			}
			$sql .= " AND ($sqlmessage)";
		}

		if(!$error) {

			$commentcount = DB::result_first("SELECT count(*) FROM ".DB::table('portal_comment')." c WHERE 1 $sql");
			if($commentcount) {

				$_G['gp_perpage'] = intval($_G['gp_perpage']) < 1 ? 20 : intval($_G['gp_perpage']);
				$perpage = $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage'];
				$query = DB::query("SELECT c.*, a.title FROM ".DB::table('portal_comment')." c LEFT JOIN ".DB::table($tablename).
						" a ON a.$idtype=c.id WHERE 1 $sql ORDER BY c.dateline DESC LIMIT ".(($page - 1) * $perpage).",{$perpage}");

				$comments = '';

				$mod = $idtype == 'aid' ? 'view' : 'topic';
				while($comment = DB::fetch($query)) {
					$comment['dateline'] = dgmdate($comment['dateline']);
					$comments .= showtablerow('', '', array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$comment[cid]\" />",
						"<a href=\"portal.php?mod=$mod&$idtype=$comment[id]\" target=\"_blank\">$comment[title]</a>",
						$comment[message],
						"<a href=\"home.php?mod=space&uid=$comment[uid]\" target=\"_blank\">$comment[username]</a>",
						$comment['dateline']
					), TRUE);
				}

				$multi = multi($commentcount, $perpage, $page, ADMINSCRIPT."?action=comment&operation=$operation");
				$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=comment&operation=$operation&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
				$multi = str_replace("window.location='".ADMINSCRIPT."?action=comment&amp;operation=$operation&amp;page='+this.value", "page(this.value)", $multi);

			} else {
				$error = 'comment_post_nonexistence';
			}
		}

		showtagheader('div', 'postlist', $searchsubmit);
		showformheader('comment&operation='.$operation.'&frame=no', 'target="articleframe"');
		showtableheader(cplang('comment_result').' '.$commentcount.' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'articleforum\').pp.value=\'\';$(\'articleforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>', 'fixpadding');

		if($error) {
			echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
		} else {
			showsubtitle(array('', 'article_title', 'message', 'author', 'time'));
			echo $comments;
		}

		showsubmit('articlesubmit', 'delete', 'del', '', $multi);
		showtablefooter();
		showformfooter();
		echo '<iframe name="articleframe" style="display:none"></iframe>';
		showtagfooter('div');

	}
}
?>