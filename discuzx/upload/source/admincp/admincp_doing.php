<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_doing.php 22995 2011-06-13 03:15:57Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$detail = !empty($_GET['users']) ? true : $_G['gp_detail'];
$users = $_G['gp_users'];
$userip = $_G['gp_userip'];
$keywords = $_G['gp_keywords'];
$lengthlimit = $_G['gp_lengthlimit'];
$starttime = $_G['gp_starttime'];
$endtime = $_G['gp_endtime'];
$searchsubmit = $_G['gp_searchsubmit'];
$doids = $_G['gp_doids'];

$fromumanage = $_G['gp_fromumanage'] ? 1 : 0;

cpheader();

if(!submitcheck('doingsubmit')) {
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

	shownav('topic', 'nav_doing');
	showsubmenu('nav_doing', array(
		array('newlist', 'doing', !empty($newlist)),
		array('search', 'doing&search=true', empty($newlist)),
	));
	empty($newlist) && showsubmenusteps('', array(
		array('doing_search', !$searchsubmit),
		array('nav_doing', $searchsubmit)
	));
	if(empty($newlist)) {
		$search_tips = 1;
		showtips('doing_tips');
	}
	echo <<<EOT
<script type="text/javascript" src="static/js/calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('doingforum').page.value=number;
	$('doingforum').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'searchposts', !$searchsubmit && empty($newlist));
	showformheader("doing".(!empty($_G['gp_search']) ? '&search=true' : ''), '', 'doingforum');
	showhiddenfields(array('page' => $page, 'pp' => $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage']));
	showtableheader();
	showsetting('doing_search_detail', 'detail', $detail, 'radio');
	showsetting('doing_search_perpage', '', $_G['gp_perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
	showsetting('doing_search_user', 'users', $users, 'text');
	showsetting('doing_search_ip', 'userip', $userip, 'text');
	showsetting('doing_search_keyword', 'keywords', $keywords, 'text');
	showsetting('doing_search_lengthlimit', 'lengthlimit', $lengthlimit, 'text');
	showsetting('doing_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
	echo '<input type="hidden" name="fromumanage" value="'.$fromumanage.'">';
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {

	$doids = authcode($doids, 'DECODE');
	$doidsadd = $doids ? explode(',', $doids) : $_G['gp_delete'];
	include_once libfile('function/delete');
	$deletecount = count(deletedoings($doidsadd));
	$cpmsg = cplang('doing_succeed', array('deletecount' => $deletecount));

?>
<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('doingforum').searchsubmit.click();</script>
<?php

}

if(submitcheck('searchsubmit', 1) || $newlist) {

	$doids = $doingcount = '0';
	$sql = $error = '';

	$keywords = trim($keywords);
	$users = trim($users);

	if($users != '') {
		$uids = '-1';
		$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $users))."')");
		while($member = DB::fetch($query)) {
			$uids .= ",$member[uid]";
		}
		$sql .= " AND d.uid IN ($uids)";
	}
	if($useip != '') {
		$sql .= " AND d.ip LIKE '".str_replace('*', '%', $useip)."'";
	}
	if($keywords != '') {
		$sqlkeywords = '';
		$or = '';
		$keywords = explode(',', str_replace(' ', '', $keywords));

		for($i = 0; $i < count($keywords); $i++) {
			if(preg_match("/\{(\d+)\}/", $keywords[$i])) {
				$keywords[$i] = preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", preg_quote($keywords[$i], '/'));
				$sqlkeywords .= " $or d.message REGEXP '".$keywords[$i]."'";
			} else {
				$sqlkeywords .= " $or d.message LIKE '%".$keywords[$i]."%'";
			}
			$or = 'OR';
		}
		$sql .= " AND ($sqlkeywords)";
	}

	if($lengthlimit != '') {
		$lengthlimit = intval($lengthlimit);
		$sql .= " AND LENGTH(d.message) < $lengthlimit";
	}

	if($starttime != '') {
		$starttime = strtotime($starttime);
		$sql .= " AND d.dateline>'$starttime'";
	}

	if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j')) {
		if($endtime != '') {
			$endtime = strtotime($endtime);
			$sql .= " AND d.dateline<'$endtime'";
		}
	} else {
		$endtime = TIMESTAMP;
	}
	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'prune_mod_range_illegal';
	}

	if(!$error) {
		if($detail) {
			$_G['gp_perpage'] = intval($_G['gp_perpage']) < 1 ? 20 : intval($_G['gp_perpage']);
			$perpage = $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage'];
			$query = DB::query("SELECT d.uid, d.doid, d.username, d.message, d.ip, d.dateline FROM ".DB::table('home_doing')." d WHERE 1 $sql ORDER BY d.dateline DESC LIMIT ".(($page - 1) * $perpage).",{$perpage} ");
			$doings = '';

			while($doing = DB::fetch($query)) {
				$doing['dateline'] = dgmdate($doing['dateline']);
				$doings .= showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$doing[doid]\"  />",
					"<a href=\"home.php?mod=space&uid=$doing[uid]\" target=\"_blank\">$doing[username]</a>",
					$doing['message'],
					$doing['ip'],
					$doing['dateline']
				), TRUE);
			}
			$doingcount = DB::result_first("SELECT count(*) FROM ".DB::table('home_doing')." d WHERE 1 $sql");
			$multi = multi($doingcount, $perpage, $page, ADMINSCRIPT."?action=doing");
			$multi = preg_replace("/href=\"".ADMINSCRIPT."\?action=doing&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
			$multi = str_replace("window.location='".ADMINSCRIPT."?action=doing&amp;page='+this.value", "page(this.value)", $multi);

		} else {
			$doingcount = 0;
			$query = DB::query("SELECT doid FROM ".DB::table('home_doing')." d WHERE 1 $sql");
			while($doing = DB::fetch($query)) {
				$doids .= ','.$doing['doid'];
				$doingcount++;
			}
			$multi = '';
		}

		if(!$doingcount) {
			$error = 'doing_post_nonexistence';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit || $newlist);
	showformheader('doing&frame=no', 'target="doingframe"');
	showhiddenfields(array('doids' => authcode($doids, 'ENCODE')));
	if(!$search_tips) {
		showtableheader(cplang('doing_new_result').' '.$doingcount, 'fixpadding');
	} else {
		showtableheader(cplang('doing_result').' '.$doingcount.(empty($newlist) ? ' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'doingforum\').pp.value=\'\';$(\'doingforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>' : ''), 'fixpadding');
	}

	if($error) {
		echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
	} else {
		if($detail) {
			showsubtitle(array('', 'author', 'message', 'ip', 'time'));
			echo $doings;
		}
	}

	showsubmit('doingsubmit', 'delete', $detail ? 'del' : '', '', $multi);
	showtablefooter();
	showformfooter();
	echo '<iframe name="doingframe" style="display:none"></iframe>';
	showtagfooter('div');

}

?>