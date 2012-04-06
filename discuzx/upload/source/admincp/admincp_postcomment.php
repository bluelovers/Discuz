<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_postcomment.php 15149 2010-10-19 15:02:46Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$detail = !empty($_GET['authorid']) ? true : $_G['gp_detail'];
$author = $_G['gp_author'];
$authorid = $_G['gp_authorid'];
$uid = $_G['gp_uid'];
$message = $_G['gp_message'];
$ip = $_G['gp_ip'];
$users = $_G['gp_users'];
$starttime = $_G['gp_starttime'];
$endtime = $_G['gp_endtime'];
$searchtid = $_G['gp_searchtid'];
$searchpid = $_G['gp_searchpid'];
$searchsubmit = $_G['gp_searchsubmit'];
$cids = $_G['gp_cids'];
$page = max(1, $_G['gp_page']);

cpheader();

$aid = $_G['gp_aid'];
$subject = $_G['gp_subject'];

if(!submitcheck('postcommentsubmit')) {
	if(empty($_G['gp_search'])) {
		$newlist = 1;
		$detail = 1;
		$starttime = dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j');
	}

	$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $starttime;
	$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? dgmdate(TIMESTAMP, 'Y-n-j') : $endtime;

	shownav('topic', 'nav_postcomment');
	showsubmenu('nav_postcomment', array(
		array('newlist', 'postcomment', !empty($newlist)),
		array('search', 'postcomment&search=true', empty($newlist)),
	));
	empty($newlist) && showsubmenusteps('', array(
		array('postcomment_search', !$searchsubmit),
		array('nav_postcomment', $searchsubmit)
	));
	if(empty($newlist)) {
		$search_tips = 1;
		showtips('postcomment_tips');
	}
	echo <<<EOT
<script type="text/javascript" src="static/js/calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('postcommentforum').page.value=number;
	$('postcommentforum').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'searchposts', !$searchsubmit && empty($newlist));
	showformheader("postcomment".(!empty($_G['gp_search']) ? '&search=true' : ''), '', 'postcommentforum');
	showhiddenfields(array('page' => $page, 'pp' => $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage']));
	showtableheader();
	showsetting('postcomment_search_detail', 'detail', $detail, 'radio');
	showsetting('comment_search_perpage', '', $_G['gp_perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
	showsetting('postcomment_content', 'message', $message, 'text');
	showsetting('postcomment_search_tid', 'searchtid', $searchtid, 'text');
	showsetting('postcomment_search_pid', 'searchpid', $searchpid, 'text');
	showsetting('postcomment_search_author', 'author', $author, 'text');
	showsetting('postcomment_search_authorid', 'authorid', $authorid, 'text');
	showsetting('comment_search_ip', 'ip', $ip, 'text');
	showsetting('postcomment_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {
	$cids = authcode($cids, 'DECODE');
	$cidsadd = $cids ? explode(',', $cids) : $_G['gp_delete'];
	$cidsadd && DB::query("DELETE FROM ".DB::table('forum_postcomment')." WHERE id IN (".dimplode($cidsadd).")");
	$cpmsg = cplang('postcomment_delete');

?>
<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('postcommentforum').searchsubmit.click();</script>
<?php
}

if(submitcheck('searchsubmit') || $newlist) {

	$comments = $commentcount = '0';
	$sql = $error = '';
	$author = trim($author);

	if($author != '') {
		$authorids = array();
		$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $author))."')");
		while($arr = DB::fetch($query)) {
			$authorids[] = intval($arr['uid']);
		}
		$authorid = ($authorid ? $authorid.',' : '').implode(',',$authorids);
	}
	if($searchtid) {
		$sql .= ' AND c.tid IN ('.dimplode(explode(',', $searchtid)).')';
	}
	if($searchpid) {
		$sql .= ' AND c.pid IN ('.dimplode(explode(',', $searchpid)).')';
	}
	$authorid = trim($authorid,', ');
	if($authorid != '') {
		$sql .= " AND c.authorid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $authorid))."')";
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

	if($ip != '') {
		$sql .= " AND c.useip LIKE '".str_replace('*', '%', $ip)."'";
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
				$sqlmessage .= " $or c.comment REGEXP '".$message[$i]."'";
			} else {
				$sqlmessage .= " $or c.comment LIKE '%".$message[$i]."%'";
			}
			$or = 'OR';
		}
		$sql .= " AND ($sqlmessage)";
	}

	if(!$error) {
		if($detail) {
			$commentcount = DB::result_first("SELECT count(*) FROM ".DB::table('forum_postcomment')." c WHERE authorid>'-1' $sql");
			if($commentcount) {
				$_G['gp_perpage'] = intval($_G['gp_perpage']) < 1 ? 20 : intval($_G['gp_perpage']);
				$perpage = $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage'];
				$query = DB::query("SELECT c.* FROM ".DB::table('forum_postcomment')." c WHERE authorid>'-1' $sql ORDER BY c.dateline DESC LIMIT ".(($page - 1) * $perpage).",{$perpage}");

				$comments = '';

				while($comment = DB::fetch($query)) {
					$comment['dateline'] = dgmdate($comment['dateline']);
					$comments .= showtablerow('', '', array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$comment[id]\" />",
						str_replace(array('[b]', '[/b]', '[/color]'), array('<b>', '</b>', '</font>'), preg_replace("/\[color=([#\w]+?)\]/i", "<font color=\"\\1\">", $comment['comment'])),
						($comment['author'] ? "<a href=\"home.php?mod=space&uid=$comment[authorid]\" target=\"_blank\">".$comment['author']."</a>" : cplang('postcomment_guest')),
						$comment['dateline'],
						$comment['useip'],
						"<a href=\"forum.php?mod=redirect&goto=findpost&ptid=$comment[tid]&pid=$comment[pid]\" target=\"_blank\">".cplang('postcomment_pid')."</a>"
					), TRUE);
				}

				$multi = multi($commentcount, $perpage, $page, ADMINSCRIPT."?action=postcomment");
				$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=postcomment&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
				$multi = str_replace("window.location='".ADMINSCRIPT."?action=postcomment&amp;page='+this.value", "page(this.value)", $multi);
			} else {
				$error = 'postcomment_nonexistence';
			}
		} else {
			$commentcount = 0;
			$query = DB::query("SELECT id FROM ".DB::table('forum_postcomment')." c WHERE authorid>'-1' $sql");
			while($row = DB::fetch($query)) {
				$cids .= ','.$row['id'];
				$commentcount++;
			}
			$multi = '';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit || $newlist);
	showformheader('postcomment&frame=no', 'target="postcommentframe"');
	showhiddenfields(array('cids' => authcode($cids, 'ENCODE')));
	if(!$search_tips) {
		showtableheader(cplang('postcomment_new_result').' '.$commentcount, 'fixpadding');
	} else {
		showtableheader(cplang('postcomment_result').' '.$commentcount.(empty($newlist) ? ' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'postcommentforum\').pp.value=\'\';$(\'postcommentforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>' : ''), 'fixpadding');
	}

	if($error) {
		echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
	} elseif($detail) {
		showsubtitle(array('', 'postcomment_content', 'author', 'time', 'ip' ,''));
		echo $comments;
	}

	showsubmit('postcommentsubmit', 'delete', $detail ? 'del' : '', '', $multi);
	showtablefooter();
	showformfooter();
	echo '<iframe name="postcommentframe" style="display:none"></iframe>';
	showtagfooter('div');

}

?>