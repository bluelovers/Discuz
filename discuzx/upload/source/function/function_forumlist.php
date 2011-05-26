<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_forumlist.php 22254 2011-04-27 01:12:11Z congyushuai $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function checkautoclose($thread) {
	global $_G;

	if(!$_G['forum']['ismoderator'] && $_G['forum']['autoclose']) {
		$closedby = $_G['forum']['autoclose'] > 0 ? 'dateline' : 'lastpost';
		$_G['forum']['autoclose'] = abs($_G['forum']['autoclose']);
		if(TIMESTAMP - $thread[$closedby] > $_G['forum']['autoclose'] * 86400) {
			return 'post_thread_closed_by_'.$closedby;
		}
	}
	return FALSE;
}

function forum(&$forum) {
	global $_G;
	$lastvisit = $_G['member']['lastvisit'];
	if(!$forum['viewperm'] || ($forum['viewperm'] && forumperm($forum['viewperm'])) || !empty($forum['allowview']) || (isset($forum['users']) && strstr($forum['users'], "\t$_G[uid]\t"))) {
		$forum['permission'] = 2;
	} elseif(!$_G['setting']['hideprivate']) {
		$forum['permission'] = 1;
	} else {
		return FALSE;
	}

	if($forum['icon']) {
		$forum['icon'] = get_forumimg($forum['icon']);
		$forum['icon'] = '<a href="forum.php?mod=forumdisplay&fid='.$forum['fid'].'"><img src="'.$forum['icon'].'" align="left" alt="" /></a>';
	}

	$lastpost = array(0, 0, '', '');

	$forum['lastpost'] = is_string($forum['lastpost']) ? explode("\t", $forum['lastpost']) : $forum['lastpost'];

	$forum['lastpost'] =count($forum['lastpost']) != 4 ? $lastpost : $forum['lastpost'];

	list($lastpost['tid'], $lastpost['subject'], $lastpost['dateline'], $lastpost['author']) = $forum['lastpost'];

	$forum['folder'] = (isset($_G['cookie']['fid'.$forum['fid']]) && $_G['cookie']['fid'.$forum['fid']] > $lastvisit ? $_G['cookie']['fid'.$forum['fid']] : $lastvisit) < $lastpost['dateline'] ? ' class="new"' : '';

	if($lastpost['tid']) {
		$lastpost['dateline'] = dgmdate($lastpost['dateline'], 'u');
		$lastpost['authorusername'] = $lastpost['author'];
		if($lastpost['author']) {
			$lastpost['author'] = '<a href="home.php?mod=space&username='.rawurlencode($lastpost['author']).'">'.$lastpost['author'].'</a>';
		}
		$forum['lastpost'] = $lastpost;
	} else {
		$forum['lastpost'] = $lastpost['authorusername'] = '';
	}

	$forum['moderators'] = moddisplay($forum['moderators'], $_G['setting']['moddisplay'], !empty($forum['inheritedmod']));

	if(isset($forum['subforums'])) {
		$forum['subforums'] = implode(', ', $forum['subforums']);
	}

	return TRUE;
}

function forumselect($groupselectable = FALSE, $arrayformat = 0, $selectedfid = 0, $showhide = FALSE, $evalue = FALSE, $special = 0) {
	global $_G;

	if(!isset($_G['cache']['forums'])) {
		loadcache('forums');
	}
	$forumcache = &$_G['cache']['forums'];
	$forumlist = $arrayformat ? array() : '<optgroup label="&nbsp;">';
	foreach($forumcache as $forum) {
		if(!$forum['status'] && !$showhide) {
			continue;
		}
		if($selectedfid) {
			if(!is_array($selectedfid)) {
				$selected = $selectedfid == $forum['fid'] ? ' selected' : '';
			} else {
				$selected = in_array($forum['fid'], $selectedfid) ? ' selected' : '';
			}
		}
		if($forum['type'] == 'group') {
			if($arrayformat) {
				$forumlist[$forum['fid']]['name'] = $forum['name'];
			} else {
				$forumlist .= $groupselectable ? '<option value="'.($evalue ? 'gid_' : '').$forum['fid'].'" class="bold">--'.$forum['name'].'</option>' : '</optgroup><optgroup label="--'.$forum['name'].'">';
			}
			$visible[$forum['fid']] = true;
		} elseif($forum['type'] == 'forum' && isset($visible[$forum['fup']]) && (!$forum['viewperm'] || ($forum['viewperm'] && forumperm($forum['viewperm'])) || strstr($forum['users'], "\t$_G[uid]\t")) && (!$special || (substr($forum['allowpostspecial'], -$special, 1)))) {
			if($arrayformat) {
				$forumlist[$forum['fup']]['sub'][$forum['fid']] = $forum['name'];
			} else {
				$forumlist .= '<option value="'.($evalue ? 'fid_' : '').$forum['fid'].'"'.$selected.'>'.$forum['name'].'</option>';
			}
			$visible[$forum['fid']] = true;
		} elseif(!$arrayformat && $forum['type'] == 'sub' && isset($visible[$forum['fup']]) && (!$forum['viewperm'] || ($forum['viewperm'] && forumperm($forum['viewperm'])) || strstr($forum['users'], "\t$_G[uid]\t")) && (!$special || substr($forum['allowpostspecial'], -$special, 1))) {
			$forumlist .= '<option value="'.($evalue ? 'fid_' : '').$forum['fid'].'"'.$selected.'>&nbsp; &nbsp; &nbsp; '.$forum['name'].'</option>';
		}
	}
	if(!$arrayformat) {
		$forumlist .= '</optgroup>';
		$forumlist = str_replace('<optgroup label="&nbsp;"></optgroup>', '', $forumlist);
	}
	return $forumlist;
}

function visitedforums() {
	global $_G;

	$count = 0;
	$visitedforums = '';
	$fidarray = array($_G['forum']['fid']);
	$_G['cookie']['visitedfid'] = isset($_G['cookie']['visitedfid']) ? $_G['cookie']['visitedfid'] : '';

	if(!empty($_G['cookie']['visitedfid'])) {
		foreach(explode('D', $_G['cookie']['visitedfid']) as $fid) {
			if(isset($_G['cache']['forums'][$fid]) && !in_array($fid, $fidarray)) {
				if($fid != $_G['forum']['fid']) {
					$visitedforums .= '<li><a href="forum.php?mod=forumdisplay&fid='.$fid.'">'.$_G['cache']['forums'][$fid]['name'].'</a></li>';
					if(++$count >= $_G['setting']['visitedforums']) {
						break;
					}
				}
				$fidarray[] = $fid;
			}
		}
	}
	if(($visitedfid = implode('D', $fidarray)) != $_G['cookie']['visitedfid']) {
		dsetcookie('visitedfid', $visitedfid, 2592000);
	}
	return $visitedforums;
}

function moddisplay($moderators, $type, $inherit = 0) {
	if($moderators) {
		$modlist = $comma = '';
		foreach(explode("\t", $moderators) as $moderator) {
			$modlist .= $comma.'<a href="home.php?mod=space&username='.rawurlencode($moderator).'" class="notabs" c="1">'.($inherit ? '<strong>'.$moderator.'</strong>' : $moderator).'</a>';
			$comma = ', ';
		}
	} else {
		$modlist = '';
	}
	return $modlist;
}

function getcacheinfo($tid) {
	global $_G;
	$tid = intval($tid);
	$cachethreaddir2 = DISCUZ_ROOT.'./'.$_G['setting']['cachethreaddir'];
	$cache = array('filemtime' => 0, 'filename' => '');
	$tidmd5 = substr(md5($tid), 3);
	$fulldir = $cachethreaddir2.'/'.$tidmd5[0].'/'.$tidmd5[1].'/'.$tidmd5[2].'/';
	$cache['filename'] = $fulldir.$tid.'.htm';
	if(file_exists($cache['filename'])) {
		$cache['filemtime'] = filemtime($cache['filename']);
	} else {
		if(!is_dir($fulldir)) {
			dmkdir($fulldir);
		}
	}
	return $cache;
}

function recommendupdate($fid, &$modrecommend, $force = '', $position = 0) {
	global $_G;

	$recommendlist = $recommendimagelist = $modedtids = array();
	$num = $modrecommend['num'] ? intval($modrecommend['num']) : 10;
	$imagenum = $modrecommend['imagenum'] = $modrecommend['imagenum'] ? intval($modrecommend['imagenum']) : 0;
	$imgw = $modrecommend['imagewidth'] = $modrecommend['imagewidth'] ? intval($modrecommend['imagewidth']) : 200;
	$imgh = $modrecommend['imageheight'] = $modrecommend['imageheight'] ? intval($modrecommend['imageheight']) : 150;

	if($modrecommend['sort'] && (TIMESTAMP - $modrecommend['updatetime'] > $modrecommend['cachelife'] || $force)) {
		$query = DB::query("SELECT tid, moderatorid, aid FROM ".DB::table('forum_forumrecommend')." WHERE fid='$fid'");
		while($row = DB::fetch($query)) {
			if($modrecommend['sort'] == 2 && $row['moderatorid']) {
				$modedtids[] = $row['tid'];
			}
		}
		DB::query("DELETE FROM ".DB::table('forum_forumrecommend')." WHERE fid='$fid'".($modrecommend['sort'] == 2 ? " AND moderatorid='0'" : ''));
		$orderby = 'dateline';
		$conditions = $modrecommend['dateline'] ? 'AND dateline>'.(TIMESTAMP - $modrecommend['dateline'] * 3600) : '';
		switch($modrecommend['orderby']) {
			case '':
			case '1':$orderby = 'lastpost';break;
			case '2':$orderby = 'views';break;
			case '3':$orderby = 'replies';break;
			case '4':$orderby = 'digest';break;
			case '5':$orderby = 'recommends';$conditions .= " AND recommends>'0'";break;
			case '6':$orderby = 'heats';break;
		}

		$add = $comma = $i = '';
		$addthread = $addimg = $recommendlist = $recommendimagelist = $tids = array();
		$query = DB::query("SELECT fid, tid, author, authorid, subject, highlight FROM ".DB::table('forum_thread')." WHERE fid='$fid' AND displayorder>='0' $conditions ORDER BY $orderby DESC LIMIT 0, $num");
		while($thread = DB::fetch($query)) {
			$recommendlist[$thread['tid']] = $thread;
			$tids[] = $thread['tid'];
			if(!$modedtids || !in_array($thread['tid'], $modedtids)) {
				$addthread[$thread['tid']] = "'$thread[fid]', '$thread[tid]', '1', '$i', '".addslashes($thread['subject'])."', '".addslashes($thread['author'])."', '$thread[authorid]', '0', '0', '$thread[highlight]'";
				$i++;
			}
		}
		if($tids && $imagenum) {
			$attachtables = array();
			foreach($tids as $tid) {
				$attachtables[getattachtablebytid($tid)][] = $tid;
			}
			foreach($attachtables as $attachtable => $tids) {
				$query = DB::query('SELECT p.fid, p.tid, a.aid
							FROM '.DB::table(getposttable())." p
							INNER JOIN ".DB::table($attachtable)." a
							ON a.pid=p.pid AND a.isimage IN ('1', '-1') AND a.width>='$imgw'"."
							WHERE p.tid IN (".dimplode($tids).") AND p.first='1'");
				while($attachment = DB::fetch($query)) {
					if(isset($recommendimagelist[$attachment['tid']])) {
						continue;
					}
					$key = md5($attachment['aid'].'|'.$imgw.'|'.$imgh);
					$recommendlist[$attachment['tid']]['filename'] = $attachment['aid']."\t".$imgw."\t".$imgh."\t".$key;
					$recommendimagelist[$attachment['tid']] = $recommendlist[$attachment['tid']];
					$addimg[$attachment['tid']] = ",'', '".addslashes($recommendlist[$attachment['tid']]['filename'])."', '1'";
					if(count($recommendimagelist) == $imagenum) {
						break;
					}
				}
			}
		}
		foreach($addthread as $tid => $row) {
			$add .= $comma.'('.$row.(!isset($addimg[$tid]) ? ",'0','','0'" : $addimg[$tid]).')';
			$comma = ', ';
		}
		unset($recommendimagelist);

		if($add) {
			DB::query("REPLACE INTO ".DB::table('forum_forumrecommend')." (fid, tid, position, displayorder, subject, author, authorid, moderatorid, expiration, highlight, aid, filename, typeid) VALUES $add");
			$modrecommend['updatetime'] = TIMESTAMP;
			$modrecommendnew = addslashes(serialize($modrecommend));
			DB::query("UPDATE ".DB::table('forum_forumfield')." SET modrecommend='$modrecommendnew' WHERE fid='$fid'");
		}
	}

	$recommendlists = $recommendlist =  array();
	$position = $position ? "AND position IN ('0','$position')" : '';
	$query = DB::query("SELECT * FROM ".DB::table('forum_forumrecommend')." WHERE fid='$fid' $position ORDER BY displayorder");
	while($recommend = DB::fetch($query)) {
		if(($recommend['expiration'] && $recommend['expiration'] > TIMESTAMP) || !$recommend['expiration']) {
			if($recommend['filename'] && strexists($recommend['filename'], "\t")) {
				$imgd = explode("\t", $recommend['filename']);
				if($imgd[0] && $imgd[3]) {
					$recommend['filename'] = 'forum.php?mod=image&aid='.$imgd[0].'&size='.$imgd[1].'x'.$imgd[2].'&key='.rawurlencode($imgd[3]);
				}
			}
			$recommendlist[] = $recommend;
			if($recommend['typeid'] && count($recommendimagelist) < $imagenum) {
				$recommendimagelist[] = $recommend;
			}
		}
		if(count($recommendlist) == $num) {
			break;
		}
	}

	if($recommendlist) {
		$_G['forum_colorarray'] = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');
		foreach($recommendlist as $thread) {
			if($thread['highlight']) {
				$string = sprintf('%02d', $thread['highlight']);
				$stylestr = sprintf('%03b', $string[0]);

				$thread['highlight'] = ' style="';
				$thread['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
				$thread['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
				$thread['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
				$thread['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]] : '';
				$thread['highlight'] .= '"';
			} else {
				$thread['highlight'] = '';
			}
			$recommendlists[$thread['tid']]['author'] = $thread['author'];
			$recommendlists[$thread['tid']]['authorid'] = $thread['authorid'];
			$recommendlists[$thread['tid']]['subject'] = $modrecommend['maxlength'] ? cutstr($thread['subject'], $modrecommend['maxlength']) : $thread['subject'];
			$recommendlists[$thread['tid']]['subjectstyles'] = $thread['highlight'];
		}
	}

	if($recommendimagelist && $recommendlist) {
		$recommendlists['images'] = $recommendimagelist;
	}

	return $recommendlists;
}

function showstars($num) {
	global $_G;
	$alt = 'alt="Rank: '.$num.'"';
	if(empty($_G['setting']['starthreshold'])) {
		for($i = 0; $i < $num; $i++) {
			echo '<img src="'.$_G['style']['imgdir'].'/star_level1.gif" '.$alt.' />';
		}
	} else {
		for($i = 3; $i > 0; $i--) {
			$numlevel = intval($num / pow($_G['setting']['starthreshold'], ($i - 1)));
			$num = ($num % pow($_G['setting']['starthreshold'], ($i - 1)));
			for($j = 0; $j < $numlevel; $j++) {
				echo '<img src="'.$_G['style']['imgdir'].'/star_level'.$i.'.gif" '.$alt.' />';
			}
		}
	}
}

function get_forumimg($imgname) {
	global $_G;
	if($imgname) {
		$parse = parse_url($imgname);
		if(isset($parse['host'])) {
			$imgpath = $imgname;
		} else {
			if($_G['forum']['status'] != 3) {
				$imgpath = $_G['setting']['attachurl'].'common/'.$imgname;
			} else {
				$imgpath = $_G['setting']['attachurl'].'group/'.$imgname;
			}
		}
		return $imgpath;
	}
}

function forumleftside() {
	global $_G;
	$leftside = array('favorites' => array(), 'forums' => array());
	$leftside['forums'] = forumselect(FALSE, 1);
	if($_G['uid']) {
		$query = DB::query("SELECT favid, id, title FROM ".DB::table('home_favorite')." WHERE uid='$_G[uid]' AND idtype='fid' ORDER BY dateline DESC");
		while($result = DB::fetch($query)) {
			if($_G['fid'] == $result['id']) {
				$_G['forum_fidinfav'] = $result['favid'];
			}
			$leftside['favorites'][$result['id']] = array($result['title'], $result['favid']);
		}
	}
	$_G['leftsidewidth_mwidth'] = $_G['setting']['leftsidewidth'] + 15;
	return $leftside;
}

?>