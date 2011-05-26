<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_album.php 22185 2011-04-25 09:18:47Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$albumid = empty($_GET['albumid'])?0:intval($_GET['albumid']);
$picid = empty($_GET['picid'])?0:intval($_GET['picid']);

if($_GET['op'] == 'edit') {

	if($albumid < 1) {
		showmessage('photos_do_not_support_the_default_settings', "home.php?mod=spacecp&ac=album&uid=$_G[uid]&op=editpic&quickforward=1");
	}

	$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE albumid='$albumid'");
	if(!$album = DB::fetch($query)) {
		showmessage('album_does_not_exist');
	}

	if($album['uid'] != $_G['uid'] && !checkperm('managealbum')) {
		showmessage('no_privilege_album_edit');
	}

	if(submitcheck('editsubmit')) {
		$_POST['albumname'] = getstr($_POST['albumname'], 50, 1, 1);
		$_POST['albumname'] = censor($_POST['albumname']);
		if(empty($_POST['albumname'])) {
			showmessage('album_name_errors');
		}

		$_POST['friend'] = intval($_POST['friend']);
		$_POST['target_ids'] = '';
		if($_POST['friend'] == 2) {
			$uids = array();
			$names = empty($_POST['target_names'])?array():explode(',', preg_replace("/(\s+)/s", ',', $_POST['target_names']));
			if($names) {
				$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username IN (".dimplode($names).")");
				while ($value = DB::fetch($query)) {
					$uids[] = $value['uid'];
				}
			}
			if(empty($uids)) {
				$_POST['friend'] = 3;
			} else {
				$_POST['target_ids'] = implode(',', $uids);
			}
		} elseif($_POST['friend'] == 4) {
			$_POST['password'] = trim($_POST['password']);
			if($_POST['password'] == '') $_POST['friend'] = 0;
		}
		if($_POST['friend'] !== 2) {
			$_POST['target_ids'] = '';
		}
		if($_POST['friend'] !== 4) {
			$_POST['password'] == '';
		}

		$_POST['catid'] = intval($_POST['catid']);
		if($_POST['catid'] != $album['catid']) {
			if($album['catid']) {
				DB::query("UPDATE ".DB::table('home_album_category')." SET num=num-1 WHERE catid='$album[catid]' AND num>0");
			}
			if($_POST['catid']) {
				DB::query("UPDATE ".DB::table('home_album_category')." SET num=num+1 WHERE catid='$_POST[catid]'");
			}
		}

		DB::update('home_album', array('albumname'=>$_POST['albumname'], 'catid'=>$_POST['catid'], 'friend'=>$_POST['friend'], 'password'=>$_POST['password'], 'target_ids'=>$_POST['target_ids'], 'depict'=>dhtmlspecialchars($_POST['depict'])), array('albumid'=>$albumid));
		showmessage('spacecp_edit_ok', "home.php?mod=spacecp&ac=album&op=edit&albumid=$albumid");
	}

	$album['target_names'] = '';

	$friendarr = array($album['friend'] => ' selected');

	$passwordstyle = $selectgroupstyle = 'display:none';
	if($album['friend'] == 4) {
		$passwordstyle = '';
	} elseif($album['friend'] == 2) {
		$selectgroupstyle = '';
		if($album['target_ids']) {
			$names = array();
			$query = DB::query("SELECT username FROM ".DB::table('common_member')." WHERE uid IN ($album[target_ids])");
			while ($value = DB::fetch($query)) {
				$names[] = $value['username'];
			}
			$album['target_names'] = implode(' ', $names);
		}
	}

	require_once libfile('function/friend');
	$groups = friend_group_list();

	if($_G['setting']['albumcategorystat']) {
		loadcache('albumcategory');
		$category = $_G['cache']['albumcategory'];

		$categoryselect = '';
		if($category) {
			$categoryselect = "<select id=\"catid\" name=\"catid\" width=\"120\"><option value=\"0\">------</option>";
			foreach ($category as $value) {
				if($value['level'] == 0) {
					$selected = $album['catid'] == $value['catid']?' selected':'';
					$categoryselect .= "<option value=\"$value[catid]\"{$selected}>$value[catname]</option>";
					if(!$value['children']) {
						continue;
					}
					foreach ($value['children'] as $catid) {
						$selected = $album['catid'] == $catid?' selected':'';
						$categoryselect .= "<option value=\"{$category[$catid][catid]}\"{$selected}>-- {$category[$catid][catname]}</option>";
						if($category[$catid]['children']) {
							foreach ($category[$catid]['children'] as $catid2) {
								$selected = $album['catid'] == $catid2?' selected':'';
								$categoryselect .= "<option value=\"{$category[$catid2][catid]}\"{$selected}>---- {$category[$catid2][catname]}</option>";
							}
						}
					}
				}
			}
			$categoryselect .= "</select>";
		}
	}

} elseif($_GET['op'] == 'delete') {

	$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE albumid='$albumid'");
	if(!$album = DB::fetch($query)) {
		showmessage('album_does_not_exist');
	}

	if($album['uid'] != $_G['uid'] && !checkperm('managealbum')) {
		showmessage('no_privilege_album_del');
	}

	$albums = getalbums($album['uid']);
	if(empty($albums[$albumid])) {
		showmessage('no_privilege_album_delother');
	}

	if(submitcheck('deletesubmit')) {
		$_POST['moveto'] = intval($_POST['moveto']);
		if($_POST['moveto'] < 0) {
			require_once libfile('function/delete');
			deletealbums(array($albumid));
		} else {
			if($_POST['moveto'] > 0 && $_POST['moveto'] != $albumid && !empty($albums[$_POST['moveto']])) {
				DB::update('home_pic', array('albumid'=>$_POST['moveto']), array('albumid'=>$albumid));
				album_update_pic($_POST['moveto']);
			} else {
				DB::update('home_pic', array('albumid'=>0), array('albumid'=>$albumid));
			}
			DB::query("DELETE FROM ".DB::table('home_album')." WHERE albumid='$albumid'");
		}
		showmessage('do_success', "home.php?mod=space&uid=$_G[gp_uid]&do=album&view=me");
	}
} elseif($_GET['op'] == 'editpic') {

	$managealbum = checkperm('managealbum');

	require_once libfile('class/bbcode');

	if($albumid > 0) {
		$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE albumid='$albumid'");
		if(!$album = DB::fetch($query)) {
			showmessage('album_does_not_exist', 'home.php?mod=space&uid='.$_G['uid'].'&do=album&view=me', array(), array('return' => true));
		}

		if($album['uid'] != $_G['uid'] && !$managealbum) {
			showmessage('no_privilege_pic_edit', 'home.php?mod=space&uid='.$_G['uid'].'&do=album&view=me', array(), array('return' => true));
		}
	} else {
		$album['uid'] = $_G['uid'];
	}
	if(submitcheck('editpicsubmit')) {
		$return = true;
		foreach ($_POST['title'] as $picid => $value) {
			if($value == $_G['gp_oldtitle'][$picid]) {
				continue;
			}
			$title = getstr($value, 150, 1, 1);
			$title = censor($title);
			if(censormod($title)) {
				$pic_status = 1;
				manage_addnotify('verifypic');
			} else {
				$pic_status = 0;
			}
			$wherearr = array('picid'=>$picid);
			if(!$managealbum) $wherearr['uid']  = $_G['uid'];
			DB::update('home_pic', array('title'=>$title, 'status' => $pic_status), $wherearr);
		}
		if($_GET['subop'] == 'delete') {
			if($_POST['ids']) {
				require_once libfile('function/delete');
				deletepics($_POST['ids']);

				if($albumid > 0) $return = album_update_pic($albumid);
			}

		} elseif($_GET['subop'] == 'move') {
			if($_POST['ids']) {
				$plussql = $managealbum?'':"AND uid='$_G[uid]'";
				$_POST['newalbumid'] = intval($_POST['newalbumid']);
				if($_POST['newalbumid']) {
					$query = DB::query("SELECT albumid FROM ".DB::table('home_album')." WHERE albumid='$_POST[newalbumid]' $plussql");
					if(!$album = DB::fetch($query)) {
						$_POST['newalbumid'] = 0;
					}
				}
				DB::query("UPDATE ".DB::table('home_pic')." SET albumid='$_POST[newalbumid]' WHERE picid IN (".dimplode($_POST['ids']).") $plussql");
				$updatecount = DB::affected_rows();
				if($updatecount) {
					if($albumid>0) {
						DB::query("UPDATE ".DB::table('home_album')." SET picnum=picnum-$updatecount WHERE albumid='$albumid' $plussql");
						$return = album_update_pic($albumid);
					}
					if($_POST['newalbumid']) {
						DB::query("UPDATE ".DB::table('home_album')." SET picnum=picnum+$updatecount WHERE albumid='$_POST[newalbumid]' $plussql");
						$return = album_update_pic($_POST['newalbumid']);
					}
				}
			}

		}

		$url = $return ? "home.php?mod=spacecp&ac=album&op=editpic&albumid=$albumid&page=$_POST[page]" : 'home.php?mod=space&uid='.$_G['uid'].'&do=album&view=me';
		if($_G['inajax']) {
			showmessage('do_success', $url, array('title' => $title),  array('showdialog' => 3, 'showmsg' => true, 'closetime' => true));
		} else {
			showmessage('do_success', $url);
		}
	}

	$perpage = 10;
	$page = empty($_GET['page'])?0:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = ($page-1)*$perpage;
	ckstart($start, $perpage);

	$picsql = $picid?"picid='$picid' AND ":'';

	if($albumid > 0) {
		$wheresql = "albumid='$albumid'";
		$count = $picid?1:$album['picnum'];
	} else {
		$wheresql = "albumid='0' AND uid='$_G[uid]'";
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('home_pic')." WHERE $picsql $wheresql"), 0);
	}

	$list = array();
	if($count) {
		if($page > 1 && $start >=$count) {
			$page--;
			$start = ($page-1)*$perpage;
		}
		$bbcode = & bbcode::instance();
		$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE $picsql $wheresql ORDER BY dateline DESC LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			if($picid) {
				$value['checked'] = ' checked';
			}
			$value['title'] = $bbcode->html2bbcode($value['title']);
			$value['pic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
			$value['bigpic'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote'], 0);
			$list[] = $value;
		}
	}

	$multi = multi($count, $perpage, $page, "home.php?mod=spacecp&ac=album&op=editpic&albumid=$albumid");

	$albumlist = getalbums($album['uid']);

} elseif($_GET['op'] == 'setpic') {

	album_update_pic($albumid, $picid);
	showmessage('do_success', dreferer(), array('picid' => $picid), array('showmsg' => true, 'closetime' => true));

} elseif($_GET['op'] == 'edittitle') {

	$picid = empty($_GET['picid'])?0:intval($_GET['picid']);
	$uidsql = checkperm('managealbum')?'':"AND uid='$_G[uid]'";
	$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE picid='$picid' $uidsql");
	$pic = DB::fetch($query);

} elseif($_GET['op'] == 'edithot') {
	if(!checkperm('managealbum')) {
		showmessage('no_privilege_edithot_album');
	}

	$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE picid='$picid'");
	if(!$pic = DB::fetch($query)) {
		showmessage('image_does_not_exist');
	}

	if(submitcheck('hotsubmit')) {
		$_POST['hot'] = intval($_POST['hot']);
		DB::update('home_pic', array('hot'=>$_POST['hot']), array('picid'=>$picid));
		if($_POST['hot'] > 0) {
			require_once libfile('function/feed');
			feed_publish($picid, 'picid');
		} else {
			DB::update('home_feed', array('hot'=>$_POST['hot']), array('id'=>$picid, 'idtype'=>'picid'));
		}

		showmessage('do_success', dreferer());
	}

}

include_once template("home/spacecp_album");

?>