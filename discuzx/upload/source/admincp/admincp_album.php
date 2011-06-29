<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_album.php 22995 2011-06-13 03:15:57Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
include_once libfile('function/portalcp');

cpheader();

$detail = !empty($_GET['uid']) ? true : $_G['gp_detail'];
$albumname = $_G['gp_albumname'];
$albumid = $_G['gp_albumid'];
$uid = $_G['gp_uid'];
$users = $_G['gp_users'];
$starttime = $_G['gp_starttime'];
$endtime = $_G['gp_endtime'];
$searchsubmit = $_G['gp_searchsubmit'];
$albumids = $_G['gp_albumids'];
$friend = $_G['gp_friend'];
$orderby = $_G['gp_orderby'];
$ordersc = $_G['gp_ordersc'];

$fromumanage = $_G['gp_fromumanage'] ? 1 : 0;

$muticondition = '';
$muticondition .= $albumname ? '&albumname='.$albumname : '';
$muticondition .= $albumid ? '&albumid='.$albumid : '';
$muticondition .= $uid ? '&uid='.$uid : '';
$muticondition .= $users ? '&users='.$users : '';
$muticondition .= $starttime ? '&starttime='.$starttime : '';
$muticondition .= $endtime ? '&endtime='.$endtime : '';
$muticondition .= $friend ? '&friend='.$friend : '';
$muticondition .= $orderby ? '&orderby='.$orderby : '';
$muticondition .= $ordersc ? '&ordersc='.$ordersc : '';
$muticondition .= $fromumanage ? '&fromumanage='.$fromumanage : '';
$muticondition .= $searchsubmit ? '&searchsubmit='.$searchsubmit : '';
$muticondition .= $_G['gp_search'] ? '&search='.$_G['gp_search'] : '';
$muticondition .= $detail ? '&detail='.$detail : '';

if(!submitcheck('albumsubmit')) {
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

	shownav('topic', 'nav_album');
	showsubmenu('nav_album', array(
		array('newlist', 'album', !empty($newlist)),
		array('search', 'album&search=true', empty($newlist)),
	));
	empty($newlist) && showsubmenusteps('', array(
		array('album_search', !$searchsubmit),
		array('nav_album', $searchsubmit)
	));
	if($muticondition) {
		showtips('album_tips');
	}
	echo <<<EOT
<script type="text/javascript" src="static/js/calendar.js"></script>
<script type="text/JavaScript">
function page(number) {
	$('albumforum').page.value=number;
	$('albumforum').searchsubmit.click();
}
</script>
EOT;
	showtagheader('div', 'searchposts', !$searchsubmit && empty($newlist));
	showformheader("album".(!empty($_G['gp_search']) ? '&search=true' : ''), '', 'albumforum');
	showhiddenfields(array('page' => $page, 'pp' => $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage']));
	showtableheader();
	showsetting('album_search_detail', 'detail', $detail, 'radio');
	showsetting('album_search_perpage', '', $_G['gp_perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
	showsetting('resultsort', '', $orderby, "<select name='orderby'><option value=''>$lang[defaultsort]</option><option value='dateline'>$lang[topic_dateline]</option><option value='updatetime'>$lang[updatetime]</option><option value='picnum'>$lang[pic_num]</option></select> ");
	showsetting('', '', $ordersc, "<select name='ordersc'><option value='desc'>$lang[orderdesc]</option><option value='asc'>$lang[orderasc]</option></select>");
	showsetting('album_search_albumname', 'albumname', $albumname, 'text');
	showsetting('album_search_albumid', 'albumid', $albumid, 'text');
	showsetting('album_search_uid', 'uid', $uid, 'text');
	showsetting('album_search_user', 'users', $users, 'text');
	showsetting('blog_search_friend', '', $friend, "<select name='friend'><option value='0'>$lang[setting_home_privacy_alluser]</option><option value='1'>$lang[setting_home_privacy_friend]</option><option value='2'>$lang[setting_home_privacy_specified_friend]</option><option value='3'>$lang[setting_home_privacy_self]</option><option value='4'>$lang[setting_home_privacy_password]</option></select>");
	showsetting('album_search_time', array('starttime', 'endtime'), array($starttime, $endtime), 'daterange');
	echo '<input type="hidden" name="fromumanage" value="'.$fromumanage.'">';
	showsubmit('searchsubmit');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

} else {
	if($_G['gp_albumids']) {
		$albumids = authcode($_G['gp_albumids'], 'DECODE');
		$albumidsadd = $albumids ? explode(',', $albumids) : $_G['gp_delete'];
		include_once libfile('function/delete');
		$deletecount = count(deletealbums($albumidsadd));
		$cpmsg = cplang('album_succeed', array('deletecount' => $deletecount));
	} else {
		$albums = $catids = array();
		$selectalbumids = !empty($_G['gp_ids']) && is_array($_G['gp_ids']) ? $_G['gp_ids'] : array();
		if($selectalbumids) {
			$query = DB::query('SELECT albumid, catid FROM '.DB::table('home_album')." WHERE albumid IN (".dimplode($selectalbumids).')');
			while($value=DB::fetch($query)) {
				$albums[$value['albumid']] = $value;
				$catids[] = intval($value['catid']);
			}
		}
		if($albums) {
			$selectalbumids = array_keys($albums);
			if($_POST['optype'] == 'delete') {
				include_once libfile('function/delete');
				$deletecount = count(deletealbums($selectalbumids));
				$cpmsg = cplang('album_succeed', array('deletecount' => $deletecount));
			} elseif($_POST['optype'] == 'move') {
				$tocatid = intval($_POST['tocatid']);
				$catids[] = $tocatid;
				$catids = array_merge($catids);
				DB::update('home_album', array('catid'=>$tocatid), 'albumid IN ('.dimplode($selectalbumids).')');
				foreach($catids as $catid) {
					$catid = intval($catid);
					$cnt = DB::result_first('SELECT COUNT(*) FROM '.DB::table('home_album')." WHERE catid = '$catid'");
					DB::update('home_album_category', array('num'=>intval($cnt)), array('catid'=>$catid));
				}
				$cpmsg = cplang('album_move_succeed');
			} else {
				$cpmsg = cplang('album_choose_at_least_one_operation');
			}
		} else {
			$cpmsg = cplang('album_choose_at_least_one_album');
		}
	}

?>
<script type="text/JavaScript">alert('<?php echo $cpmsg;?>');parent.$('albumforum').searchsubmit.click();</script>
<?php

}

if(submitcheck('searchsubmit', 1) || $newlist) {

	$albumids = $albumcount = '0';
	$sql = $error = '';
	$users = trim($users);

	if($users != '') {
		$uids = '-1';
		$query = DB::query("SELECT uid FROM ".DB::table('home_album')." WHERE username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $users))."')");
		while($member = DB::fetch($query)) {
			$uids .= ",$member[uid]";
		}
		$sql .= " AND a.uid IN ($uids)";
	}

	if($albumname !='') {
		$query =DB::query("SELECT albumname FROM ".DB::table('home_album')." WHERE albumname='$albumname'");
		$arr = DB::fetch($query);
		$albumname = $arr['albumname'];
		$sql .= " AND a.albumname='$albumname'";
	}

	if($starttime != '') {
		$starttime = strtotime($starttime);
		$sql .= " AND a.dateline>'$starttime'";
	}

	if($_G['adminid'] == 1 && $endtime != dgmdate(TIMESTAMP, 'Y-n-j')) {
		if($endtime != '') {
			$endtime = strtotime($endtime);
			$sql .= " AND a.dateline<'$endtime'";
		}
	} else {
		$endtime = TIMESTAMP;
	}

	if($albumid != '') {
		$albumids = '-1';
		$query = DB::query("SELECT albumid FROM ".DB::table('home_album')." WHERE albumid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $albumid))."')");
		while($arr = DB::fetch($query)) {
			$albumids .= ",$arr[albumid]";
		}
		$sql .= " AND a.albumid IN ($albumids)";
	}

	if($uid != '') {
		$uids = '-1';
		$query = DB::query("SELECT uid FROM ".DB::table('home_album')." WHERE uid IN ('".str_replace(',', '\',\'', str_replace(' ', '', $uid))."')");
		while($uidarr = DB::fetch($query)) {
			$uids .= ",$uidarr[uid]";
		}
		$sql .= " AND a.uid IN ($uids)";
	}

	$sql .= $friend ? " AND a.friend = '$friend'" : '';
	$orderby = $orderby ? "a.$orderby" : 'a.updatetime';
	$ordersc = $ordersc ? "$ordersc" : 'DESC';

	if(($_G['adminid'] == 2 && $endtime - $starttime > 86400 * 16) || ($_G['adminid'] == 3 && $endtime - $starttime > 86400 * 8)) {
		$error = 'album_mod_range_illegal';
	}

	if(!$error) {
		if($detail) {
			$_G['gp_perpage'] = intval($_G['gp_perpage']) < 1 ? 20 : intval($_G['gp_perpage']);
			$perpage = $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage'];
			$query = DB::query("SELECT * FROM ".DB::table('home_album')." a WHERE 1 $sql ORDER BY $orderby $ordersc LIMIT ".(($page - 1) * $perpage).",{$perpage}");
			$albums = '';

			include_once libfile('function/home');
			while($album = DB::fetch($query)) {
				if($album['friend'] != 4 && ckfriend($album['uid'], $album['friend'], $album['target_ids'])) {
					$album['pic'] = pic_cover_get($album['pic'], $album['picflag']);
				} else {
					$album['pic'] = STATICURL.'image/common/nopublish.gif';
				}
				$album['updatetime'] = dgmdate($album['updatetime']);
				switch ($album['friend']) {
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
				$album['friend'] = $album['friend'] ? " <a href=\"".ADMINSCRIPT."?action=album&friend=$album[friend]\">$privacy_name</a>" : $privacy_name;
				$albums .= showtablerow('', '', array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"ids[]\" value=\"$album[albumid]\" />",
					"<a href=\"home.php?mod=space&uid=$album[uid]&do=album&id=$album[albumid]\" target=\"_blank\"><img src='$album[pic]' /></a>",
					"<a href=\"home.php?mod=space&uid=$album[uid]&do=album&id=$album[albumid]\" target=\"_blank\">$album[albumname]</a>",
					"<a href=\"home.php?mod=space&uid=$album[uid]\" target=\"_blank\">".$album['username']."</a>",
					$album['updatetime'],"<a href=\"".ADMINSCRIPT."?action=pic&albumid=$album[albumid]\">".$album['picnum']."</a>",
					$album['friend']
				), TRUE);
			}
			$albumcount = DB::result_first("SELECT count(*) FROM ".DB::table('home_album')." a WHERE 1 $sql");
			$multi = multi($albumcount, $perpage, $page, ADMINSCRIPT."?action=album$muticondition");
		} else {
			$albumcount = 0;
			$query = DB::query("SELECT a.albumid FROM ".DB::table('home_album')." a WHERE 1 $sql");
			while($album = DB::fetch($query)) {
				$albumids .= ','.$album['albumid'];
				$albumcount++;
			}
			$multi = '';
		}

		if(!$albumcount) {
			$error = 'album_post_nonexistence';
		}
	}

	showtagheader('div', 'postlist', $searchsubmit || $newlist);
	showformheader('album&frame=no', 'target="albumframe"');
	if(!$muticondition) {
		showtableheader(cplang('album_new_result').' '.$albumcount, 'fixpadding');
	} else {
		showtableheader(cplang('album_result').' '.$albumcount.(empty($newlist) ? ' <a href="###" onclick="$(\'searchposts\').style.display=\'\';$(\'postlist\').style.display=\'none\';$(\'albumforum\').pp.value=\'\';$(\'albumforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>' : ''), 'fixpadding');
	}

	if($error) {
		echo "<tr><td class=\"lineheight\" colspan=\"15\">$lang[$error]</td></tr>";
	} else {
		if($detail) {
			showsubtitle(array('', 'albumpic', 'albumname', 'author', 'updatetime', 'pic_num', 'privacy'));
			echo $albums;
			$optypehtml = ''
			.'<input type="radio" name="optype" id="optype_delete" value="delete" class="radio" /><label for="optype_delete">'.cplang('delete').'</label>&nbsp;&nbsp;'
			;
			$optypehtml .= '<input type="radio" name="optype" id="optype_move" value="move" class="radio" /><label for="optype_move">'.cplang('article_opmove').'</label> '
					.category_showselect('album', 'tocatid', false)
					.'&nbsp;&nbsp;';
			showsubmit('', '', '', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'ids\')" /><label for="chkall">'.cplang('select_all').'</label>&nbsp;&nbsp;'.$optypehtml.'<input type="submit" class="btn" name="albumsubmit" value="'.cplang('submit').'" />', $multi);
		} else {
			showhiddenfields(array('albumids' => authcode($albumids, 'ENCODE')));
			showsubmit('albumsubmit', 'delete', $detail ? 'del' : '', '', $multi);
		}
	}

	showtablefooter();
	showformfooter();
	echo '<iframe name="albumframe" style="display:none;"></iframe>';
	showtagfooter('div');

}
?>