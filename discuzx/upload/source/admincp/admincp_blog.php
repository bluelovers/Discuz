<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_blog.php 27940 2012-02-17 03:04:38Z liulanbo $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
include_once libfile('function/portalcp');

cpheader();

$detail = !empty($_GET['uid']) ? true : $_G['gp_detail'];
$uid = $_G['gp_uid'];
$blogid = $_G['gp_blogid'];
$users = $_G['gp_users'];
$keywords = $_G['gp_keywords'];
$lengthlimit = $_G['gp_lengthlimit'];
$viewnum1 = $_G['gp_viewnum1'];
$viewnum2 = $_G['gp_viewnum2'];
$replynum1 = $_G['gp_replynum1'];
$replynum2 = $_G['gp_replynum2'];
$hot1 = $_G['gp_hot1'];
$hot2 = $_G['gp_hot2'];
$starttime = $_G['gp_starttime'];
$endtime = $_G['gp_endtime'];
$searchsubmit = $_G['gp_searchsubmit'];
$blogids = $_G['gp_blogids'];
$friend = $_G['gp_friend'];
$ip = $_G['gp_ip'];
$orderby = $_G['gp_orderby'];
$ordersc = $_G['gp_ordersc'];

$fromumanage = $_G['gp_fromumanage'] ? 1 : 0;

$muticondition = '';
$muticondition .= $uid ? '&uid='.$uid : '';
$muticondition .= $blogid ? '&blogid='.$blogid : '';
$muticondition .= $users ? '&users='.$users : '';
$muticondition .= $keywords ? '&keywords='.$keywords : '';
$muticondition .= $lengthlimit ? '&lengthlimit='.$lengthlimit : '';
$muticondition .= $viewnum1 ? '&viewnum1='.$viewnum1 : '';
$muticondition .= $viewnum2 ? '&viewnum2='.$viewnum2 : '';
$muticondition .= $replynum1 ? '&replynum1='.$replynum1 : '';
$muticondition .= $replynum2 ? '&replynum2='.$replynum2 : '';
$muticondition .= $hot1 ? '&hot1='.$hot1 : '';
$muticondition .= $hot2 ? '&hot2='.$hot2 : '';
$muticondition .= $starttime ? '&starttime='.$starttime : '';
$muticondition .= $endtime ? '&endtime='.$endtime : '';
$muticondition .= $friend ? '&friend='.$friend : '';
$muticondition .= $ip ? '&ip='.$ip : '';
$muticondition .= $orderby ? '&orderby='.$orderby : '';
$muticondition .= $ordersc ? '&ordersc='.$ordersc : '';
$muticondition .= $fromumanage ? '&fromumanage='.$fromumanage : '';
$muticondition .= $searchsubmit ? '&searchsubmit='.$searchsubmit : '';
$muticondition .= $_G['gp_search'] ? '&search='.$_G['gp_search'] : '';
$muticondition .= $detail ? '&detail='.$detail : '';

if(!submitcheck('blogsubmit')) {
	if(empty($_G['gp_search'])) {
		$newlist = 1;
		$detail = 1;
	}

	if($fromumanage) {
		$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? '' : $starttime;
		$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? '' : $endtime;
	} else {
		$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? dgmdate(TIMESTAMP - 86400 * 7, 'Y-n-j') : $starttime;
		$endtime = $_G['adminid'] == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? dgmdate(TIMESTAMP, 'Y-n-j') : $endtime;
	}

	shownav('topic', 'nav_blog');
	showsubmenu('nav_blog', array(
		array('newlist', 'blog', !empty($newlist)),
		array('search', 'blog&search=true', empty($newlist)),
	));
	empty($newlist) && showsubmenusteps('', array(
		array('blog_search', !$searchsubmit),
		array('nav_blog', $searchsubmit)
	));
	if($muticondition) {
		showtips('blog_tips');
	}
	echo <<<EOT
<script type="text/javascript" src="static/js/calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('blogforum').page.value=number;
	$('blogforum').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'searchposts', !$searchsubmit && empty($newlist));
	showformheader("blog".(!empty($_G['gp_search']) ? '&search=true' : ''), '', 'blogforum');
	showhiddenfields(array('page' => $page, 'pp' => $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage']));
	showtableheader();
	showsetting('blog_search_detail', 'detail', $detail, 'radio');
	showsetting('blog_search_perpage', '', $_G['gp_perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
	showsetting('resultsort', '', $orderby, "<select name='orderby'><option value=''>$lang[defaultsort]</option><option value='dateline'>$lang[forums_edit_extend_order_starttime]</option><option value='viewnum'>$lang[blog_search_view]</option><option value='replynum'>$lang[blog_search_reply]</option><option value='hot'>$lang[blog_search_hot]</option></select> ");
	showsetting('', '', $ordersc, "<select name='ordersc'><option value='desc'>$lang[orderdesc]</option><option value='asc'>$lang[orderasc]</option></select>");
	showsetting('blog_search_uid', 'uid', $uid, 'text');
	showsetting('blog_search_blogid', 'blogid', $blogid, 'text');
	showsetting('blog_search_user', 'users', $users, 'text');
	showsetting('blog_search_keyword', 'keywords', $keywords, 'text');
	showsetting('blog_search_friend', '', $friend, "<select name='friend'><option value='0'>$lang[setting_home_privacy_alluser]</option><option value='1'>$lang[setting_home_privacy_friend]</option><option value='2'>$lang[setting_home_privacy_specified_friend]</option><option value='3'>$lang[setting_home_privacy_self]</option><option value='4'>$lang[setting_home_privacy_password]</option></select>");
	showsetting('blog_search_ip', 'ip', $ip, 'text');
	showsetting('blog_search_lengthlimit', 'lengthlimit', $lengthlimit, 'text');
	showsetting('blog_search_view', array('viewnum1', 'viewnum2'), array('', ''), 'range');
	showsetting('blog_search_reply', array('replynum1', 'replynum2'), array('', ''), 'range');
	showsetting('blog_search_hot', array('hot1', 'hot2'), array('', ''), 'range');
	showsetting('blog_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
	echo '<input type="hidden" name="fromumanage" value="'.$fromumanage.'">';
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {
    if($_G['gp_blogids']) {
		$blogids = authcode($_G['gp_blogids'], 'DECODE');
		$blogidsadd = $blogids ? explode(',', $blogids) : $_G['gp_delete'];
		include_once libfile('function/delete');
		$deletecount = count(deleteblogs($blogidsadd));
		$cpmsg = cplang('blog_succeed', array('deletecount' => $deletecount));
	} else {
		$blogs = $catids = array();
		$selectblogids = !empty($_G['gp_ids']) && is_array($_G['gp_ids']) ? $_G['gp_ids'] : array();
		if($selectblogids) {
			$query = DB::query('SELECT blogid, catid FROM '.DB::table('home_blog')." WHERE blogid IN (".dimplode($selectblogids).')');
			while($value=DB::fetch($query)) {
				$blogs[$value['blogid']] = $value;
				$catids[] = intval($value['catid']);
			}
		}
		if($blogs) {
			$selectblogids = array_keys($blogs);
			if($_POST['optype'] == 'delete') {
				include_once libfile('function/delete');
				$deletecount = count(deleteblogs($selectblogids));
				$cpmsg = cplang('blog_succeed', array('deletecount' => $deletecount));
			} elseif($_POST['optype'] == 'move') {
				$tocatid = intval($_POST['tocatid']);
				$catids[] = $tocatid;
				$catids = array_merge($catids);

				DB::update('home_blog', array('catid'=>$tocatid), 'blogid IN ('.dimplode($selectblogids).')');
				foreach($catids as $catid) {
					$catid = intval($catid);
					$cnt = DB::result_first('SELECT COUNT(*) FROM '.DB::table('home_blog')." WHERE catid = '$catid'");
					DB::update('home_blog_category', array('num'=>intval($cnt)), array('catid'=>$catid));
				}
				$cpmsg = cplang('blog_move_succeed');
			} else {
				$cpmsg = cplang('blog_choose_at_least_one_operation');
			}
		} else {
			$cpmsg = cplang('blog_choose_at_least_one_blog');
		}
	}
?>
<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('blogforum').searchsubmit.click();</script>
<?php

}

if(submitcheck('searchsubmit', 1) || $newlist) {

	$blogids = $blogcount = '0';
	$sql = $error = '';
	$keywords = trim($keywords);
	$users = trim($users);

	if($blogid != '') {
		$sql .= " AND  b.blogid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $blogid))."')";
	}

	if($users != '') {
		$uids = array();
		$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $users))."')");
		while($member = DB::fetch($query)) {
			$uids[] = intval($member['uid']);
		}
		$uid = ($uid ? $uid.',':'').implode(',',$uids);
	}

	$uid = trim($uid, ', ');
	if($uid != '') {
		$sql .= " AND b.uid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $uid))."')";
	}

	if($starttime != '') {
		$starttime = strtotime($starttime);
		$sql .= " AND b.dateline>'$starttime'";
	}

	if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j')) {
		if($endtime != '') {
			$endtime = strtotime($endtime);
			$sql .= " AND b.dateline<'$endtime'";
		}
	} else {
		$endtime = TIMESTAMP;
	}

	$sql .= $hot1 ? " AND b.hot >= '$hot1'" : '';
	$sql .= $hot2 ? " AND b.hot <= '$hot2'" : '';

	$sql .= $viewnum1 ? " AND b.viewnum >= '$viewnum1'" : '';
	$sql .= $viewnum2 ? " AND b.viewnum <= '$viewnum2'" : '';
	$sql .= $replynum1 ? " AND b.replynum >= '$replynum1'" : '';
	$sql .= $replynum2 ? " AND b.replynum <= '$replynum2'" : '';
	$sql .= $friend ? " AND b.friend = '$friend'" : '';
	$ip = str_replace('*', '', $ip);
	$sql .= $ip ? " AND bf.postip LIKE '%$ip%'" : '';
	$orderby = $orderby ? "b.$orderby" : 'b.dateline';
	$ordersc = $ordersc ? "$ordersc" : 'DESC';

	if($keywords != '') {
		$sqlkeywords = '';
		$or = '';
		$keywords = explode(',', str_replace(' ', '', $keywords));

		for($i = 0; $i < count($keywords); $i++) {
			if(preg_match("/\{(\d+)\}/", $keywords[$i])) {
				$keywords[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($keywords[$i], '/'));
				$sqlkeywords .= " $or b.subject REGEXP '".$keywords[$i]."' OR bf.message REGEXP '".$keywords[$i]."'";
			} else {
				$sqlkeywords .= " $or b.subject LIKE '%".$keywords[$i]."%' OR bf.message LIKE '%".$keywords[$i]."%'";
			}
			$or = 'OR';
		}
		$sql .= " AND ($sqlkeywords)";
	}

	if($lengthlimit != '') {
		$lengthlimit = intval($lengthlimit);
		$sql .= " AND LENGTH(bf.message) > $lengthlimit";
	}

	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'blog_mod_range_illegal';
	}

	if(!$error) {
		if($detail) {
			$pagetmp = $page;
			$_G['gp_perpage'] = intval($_G['gp_perpage']) < 1 ? 20 : intval($_G['gp_perpage']);
			$perpage = $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage'];
			do{
				$query = DB::query("SELECT b.hot, b.replynum, b.viewnum, b.blogid, b.uid, b.username, b.dateline, bf.message, b.subject, b.friend FROM ".DB::table('home_blog')." b LEFT JOIN ".DB::table('home_blogfield')." bf USING(blogid) " .
						"WHERE 1 $sql ORDER BY $orderby $ordersc LIMIT ".(($pagetmp - 1) * $perpage).",{$perpage}");
				$pagetmp--;
			} while(!DB::num_rows($query) && $pagetmp);
			$blogs = '';
			while($blog = DB::fetch($query)) {
				$blog['dateline'] = dgmdate($blog['dateline']);
				$blog['subject'] = cutstr($blog['subject'], 30);
				switch ($blog['friend']) {
					case '0':
						$privacy_name = $lang[setting_home_privacy_alluser];
						break;
					case '1':
						$privacy_name = $lang[setting_home_privacy_friend];
						break;
					case '2':
						$privacy_name = $lang[setting_home_privacy_specified_friend];
						break;
					case '3':
						$privacy_name = $lang[setting_home_privacy_self];
						break;
					case '4':
						$privacy_name = $lang[setting_home_privacy_password];
						break;
					default:
						$privacy_name = $lang[setting_home_privacy_alluser];
				}
				$blog['friend'] = $blog['friend'] ? " <a href=\"".ADMINSCRIPT."?action=blog&friend=$blog[friend]\">$privacy_name</a>" : $privacy_name;
				$blogs .= showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"ids[]\" value=\"$blog[blogid]\" />",
					$blog['blogid'],
					"<a href=\"home.php?mod=space&uid=$blog[uid]\" target=\"_blank\">$blog[username]</a>",
					"<a href=\"home.php?mod=space&uid=$blog[uid]&do=blog&id=$blog[blogid]\" target=\"_blank\">$blog[subject]</a>",
					$blog['viewnum'],
					$blog['replynum'],
					$blog['hot'],
					$blog['dateline'],
					$blog['friend']
				), TRUE);
			}
			$blogcount = DB::result_first("SELECT count(*) FROM ".DB::table('home_blog')." b LEFT JOIN ".DB::table('home_blogfield')." bf USING(blogid) WHERE 1 $sql");
			$multi = multi($blogcount, $perpage, $page, ADMINSCRIPT."?action=blog".($perpage ? '&perpage='.$perpage : '').$muticondition);
		} else {
			$blogcount = 0;
			$query = DB::query("SELECT b.blogid FROM ".DB::table('home_blog')." b LEFT JOIN ".DB::table('home_blogfield')." bf USING(blogid) WHERE 1 $sql");
			while($blog = DB::fetch($query)) {
				$blogids .= ','.$blog['blogid'];
				$blogcount++;
			}
			$multi = '';
		}

		if(!$blogcount) {
			$error = 'blog_post_nonexistence';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit || $newlist);
	showformheader('blog&frame=no', 'target="blogframe"');
	if(!$muticondition) {
		showtableheader(cplang('blog_new_result').' '.$blogcount, 'fixpadding');
	} else {
		showtableheader(cplang('blog_result').' '.$blogcount.(empty($newlist) ? ' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'blogforum\').pp.value=\'\';$(\'blogforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>' : ''), 'fixpadding');
	}

	if($error) {
		echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
	} else {
		if($detail) {
			showsubtitle(array('', 'blogid', 'author', 'subject', 'view', 'reply', 'hot', 'time', 'privacy'));
			echo $blogs;
			$optypehtml = ''
			.'<input type="radio" name="optype" id="optype_delete" value="delete" class="radio" /><label for="optype_delete">'.cplang('delete').'</label>&nbsp;&nbsp;'
			;
			$optypehtml .= '<input type="radio" name="optype" id="optype_move" value="move" class="radio" /><label for="optype_move">'.cplang('article_opmove').'</label> '
					.category_showselect('blog', 'tocatid', false)
					.'&nbsp;&nbsp;';
			showsubmit('', '', '', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'ids\')" /><label for="chkall">'.cplang('select_all').'</label>&nbsp;&nbsp;'.$optypehtml.'<input type="submit" class="btn" name="blogsubmit" value="'.cplang('submit').'" />', $multi);
		} else {
			showhiddenfields(array('blogids' => authcode($blogids, 'ENCODE')));
			showsubmit('blogsubmit', 'delete', $detail ? 'del' : '', '', $multi);
		}
	}

	showtablefooter();
	showformfooter();
	echo '<iframe name="blogframe" style="display:none;"></iframe>';
	showtagfooter('div');

}

?>