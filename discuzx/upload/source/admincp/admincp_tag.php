<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_tag.php 17506 2010-10-20 05:49:48Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
cpheader();
$operation = in_array($operation, array('admin')) ? $operation : 'admin';
$current = array($operation => 1);
shownav('global', 'tag');
showsubmenu('tag', array(
	array('search', 'tag&operation=admin', $current['admin']),
));
if($operation == 'admin') {
	$tagarray = array();
	if(submitcheck('submit') && !empty($_G['gp_tagidarray']) && is_array($_G['gp_tagidarray']) && !empty($_G['gp_operate_type'])) {
		$tagidarray = array();
		$operate_type = $newtag = $thread =  '';
		$tagidarray = $_G['gp_tagidarray'];
		$operate_type = $_G['gp_operate_type'];
		if($operate_type == 'delete') {
			$tidarray = $blogidarray = array();
			$query = DB::query("SELECT * FROM ".DB::table('common_tagitem')." WHERE tagid IN (".dimplode($tagidarray).")");
			while($result = DB::fetch($query)) {
				$result[tagname] = addslashes($result['tagname']);
				if($result['idtype'] == 'tid') {
					$itemid = $result[itemid];
					$tidarray[$itemid] = $tidarray[$itemid] == '' ? 'tags' : $tidarray[$itemid];
					$tidarray[$itemid] = "(REPLACE($tidarray[$itemid], '$result[tagid],$result[tagname]\t', ''))";
				} elseif($result['idtype'] == 'blogid') {
					$itemid = $result[itemid];
					$blogidarray[$itemid] = $blogidarray[$itemid] == '' ? 'tag' : $blogidarray[$itemid];
					$blogidarray[$itemid] = "(REPLACE($blogidarray[$itemid], '$result[tagid],$result[tagname]\t', ''))";
				}
			}
			if($tidarray) {
				foreach($tidarray as $key => $var) {
					$thread = get_thread_by_tid($key);
					DB::query("UPDATE ".DB::table($thread[posttable])." SET tags=$var WHERE tid='$key' AND first=1");
				}
			}
			if($blogidarray) {
				foreach($blogidarray as $key => $var) {
					DB::query("UPDATE ".DB::table('home_blogfield')." SET tag=$var WHERE blogid='$key'");
				}
			}
			DB::query("DELETE FROM ".DB::table('common_tag')." WHERE tagid IN (".dimplode($tagidarray).")");
			DB::query("DELETE FROM ".DB::table('common_tagitem')." WHERE tagid IN (".dimplode($tagidarray).")");
		} elseif($operate_type == 'open') {
			DB::query("UPDATE ".DB::table('common_tag')." SET status='0' WHERE tagid IN (".dimplode($tagidarray).")");
		} elseif($operate_type == 'close') {
			DB::query("UPDATE ".DB::table('common_tag')." SET status='1' WHERE tagid IN (".dimplode($tagidarray).")");
		} elseif($operate_type == 'merge') {
			$newtag = str_replace(array(','), '', $_G['gp_newtag']);
			$newtag = trim($newtag);
			if(!$newtag) {
				cpmsg('tag_empty');
			}
			if(preg_match('/^([\x7f-\xff_-]|\w|\s){3,20}$/', $newtag)) {
				$tidarray = $blogidarray = array();
				$query = DB::query("SELECT * FROM ".DB::table('common_tagitem')." WHERE tagid IN (".dimplode($tagidarray).")");
				while($result = DB::fetch($query)) {
					if($result['idtype'] == 'tid') {
						$itemid = $result[itemid];
						$tidarray[$itemid] = $tidarray[$itemid] == '' ? 'tags' : $tidarray[$itemid];
						$tidarray[$itemid] = "(REPLACE($tidarray[$itemid], '$result[tagid],$result[tagname]\t', ''))";
					} elseif($result['idtype'] == 'blogid') {
						$itemid = $result[itemid];
						$blogidarray[$itemid] = $blogidarray[$itemid] == '' ? 'tag' : $blogidarray[$itemid];
						$blogidarray[$itemid] = "(REPLACE($blogidarray[$itemid], '$result[tagid],$result[tagname]\t', ''))";
					}
				}
				DB::query("DELETE FROM ".DB::table('common_tag')." WHERE tagid IN (".dimplode($tagidarray).")");
				$newid = DB::fetch_first("SELECT tagid FROM ".DB::table('common_tag')." WHERE tagname='$newtag'");
				if(!$newid) {
					DB::query("INSERT INTO ".DB::table('common_tag')." (tagname, status) VALUES ('$newtag', '0')");
					$newid = DB::insert_id();
				}
				DB::query("UPDATE ".DB::table('common_tagitem')." SET tagid='$newid',tagname='$newtag' WHERE tagid IN (".dimplode($tagidarray).")");
				if($tidarray) {
					foreach($tidarray as $key => $var) {
						$thread = get_thread_by_tid($key);
						DB::query("UPDATE ".DB::table($thread[posttable])." SET tags=$var WHERE tid='$key' AND first=1");
						DB::query("UPDATE ".DB::table($thread[posttable])." SET tags=concat(tags, '$newid,$newtag\t') WHERE tid='$key' AND first=1");
					}
				}
				if($blogidarray) {
					foreach($blogidarray as $key => $var) {
						DB::query("UPDATE ".DB::table('home_blogfield')." SET tag=$var WHERE blogid='$key'");
						DB::query("UPDATE ".DB::table('home_blogfield')." SET tag=concat(tag, '$newid,$newtag\t') WHERE blogid='$key'");
					}
				}
			} else {
				cpmsg('tag_length');
			}
		}
		cpmsg('tag_admin_updated', 'action=tag&operation=admin&searchsubmit=yes&tagname='.$_G['gp_tagname'].'&perpage='.$_G['gp_perpage'].'&status='.$_G['gp_status'].'&page='.$_G['gp_page'], 'succeed');
	}
	if(!submitcheck('searchsubmit', 1)) {
		showformheader('tag&operation=admin');
		showtableheader();
		showsetting('tagname', 'tagname', $tagname, 'text');
		showsetting('feed_search_perpage', '', $_G['gp_perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
		showsetting('misc_tag_status', array('status', array(
			array('', cplang('unlimited')),
			array(0, cplang('misc_tag_status_0')),
			array(1, cplang('misc_tag_status_1')),
		), TRUE), '', 'mradio');
		showsubmit('searchsubmit');
		showtablefooter();
		showformfooter();
		showtagfooter('div');
	} else {
		$where = 'WHERE 1';
		$tagname = trim($_G['gp_tagname']);
		$status = $_G['gp_status'];
		if($tagname) {
			$where .= " AND tagname LIKE '%$tagname%'";
		}
		if($status != '') {
			$where .= " AND status='$status'";
		}
		$ppp = $_G['gp_perpage'];
		$startlimit = ($page - 1) * $ppp;
		$multipage = '';
		$totalcount = DB::result_first("SELECT count(*) FROM ".DB::table('common_tag')." $where");
		$multipage = multi($totalcount, $ppp, $page, ADMINSCRIPT."?action=tag&operation=admin&searchsubmit=yes&tagname=$tagname&perpage=$ppp&status=$status");
		$query = DB::query("SELECT * FROM ".DB::table('common_tag')." $where LIMIT $startlimit, $ppp");
		showformheader('tag&operation=admin');
		showtableheader(cplang('tag_result').' '.$totalcount.' <a href="###" onclick="location.href=\''.ADMINSCRIPT.'?action=tag&operation=admin;\'" class="act lightlink normal">'.cplang('research').'</a>', 'nobottom');
		showhiddenfields(array('page' => $_G['gp_page'], 'tagname' => $tagname, 'status' => $status, 'perpage' => $ppp));
		showsubtitle(array('', 'tagname', 'misc_tag_status'));
		while($result =	DB::fetch($query)) {
			if($result['status'] == 0) {
				$tagstatus = cplang('misc_tag_status_0');
			} elseif($result['status'] == 1) {
				$tagstatus = cplang('misc_tag_status_1');
			}
			showtablerow('', array('class="td25"', 'width=400', ''), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"tagidarray[]\" value=\"$result[tagid]\" />",
				$result['tagname'],
				$tagstatus
			));
		}
		showtablerow('', array('class="td25" colspan="3"'), array('<input name="chkall" id="chkall" type="checkbox" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'tagidarray\', \'chkall\')" /><label for="chkall">'.cplang('select_all').'</label>'));
		showtablerow('', array('class="td25"', 'colspan="2"'), array(
				cplang('operation'),
				'<input class="radio" type="radio" name="operate_type" value="open" checked> '.cplang('misc_tag_status_0').' &nbsp; &nbsp;<input class="radio" type="radio" name="operate_type" value="close"> '.cplang('misc_tag_status_1').' &nbsp; &nbsp;<input class="radio" type="radio" name="operate_type" value="delete"> '.cplang('delete').' &nbsp; &nbsp;<input class="radio" type="radio" name="operate_type" value="merge"> '.cplang('mergeto').' <input name="newtag" value="" class="txt" type="text">'
			));
		showsubmit('submit', 'submit', '', '', $multipage);
		showtablefooter();
		showformfooter();
	}
}
?>