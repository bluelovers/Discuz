<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_counter.php 24435 2011-09-20 02:48:14Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$pertask = isset($_G['gp_pertask']) ? intval($_G['gp_pertask']) : 100;
$current = isset($_G['gp_current']) && $_G['gp_current'] > 0 ? intval($_G['gp_current']) : 0;
$next = $current + $pertask;

if(submitcheck('forumsubmit', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&forumsubmit=yes";
	$processed = 0;

	$queryf = DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE type<>'group' LIMIT $current, $pertask");
	while($forum = DB::fetch($queryf)) {
		$processed = 1;
		$threads = $posts = 0;
		$threadtables = array('0');
		$archive = 0;
		$query_a = DB::query("SELECT threadtableid FROM ".DB::table('forum_forum_threadtable')." WHERE fid='{$forum['fid']}'");
		while($data = DB::fetch($query_a)) {
			$threadtables[] = $data['threadtableid'];
		}
		foreach($threadtables as $tableid) {
			$threadtable = $tableid ? "forum_thread_$tableid" : 'forum_thread';
			$data = DB::fetch_first("SELECT COUNT(*) AS threads, SUM(replies)+COUNT(*) AS posts FROM ".DB::table($threadtable)." WHERE fid='{$forum['fid']}' AND displayorder>='0'");
			$threads += $data['threads'];
			$posts += $data['posts'];
			if($data['threads'] == 0 && $tableid != 0) {
				DB::delete('forum_forum_threadtable', "fid='{$forum['fid']}' AND threadtableid='$tableid'");
			}
			if($data['threads'] > 0 && $tableid != 0) {
				$archive = 1;
			}
		}
		DB::update('forum_forum', array('archive' => $archive), "fid='{$forum['fid']}'");

		$thread = DB::fetch_first("SELECT tid, subject, lastpost, lastposter FROM ".DB::table('forum_thread')." WHERE fid='$forum[fid]' AND displayorder>='0' ORDER BY lastpost DESC LIMIT 1");
		$lastpost = addslashes("$thread[tid]\t$thread[subject]\t$thread[lastpost]\t$thread[lastposter]");

		DB::query("UPDATE ".DB::table('forum_forum')." SET threads='$threads', posts='$posts', lastpost='$lastpost' WHERE fid='$forum[fid]'");
	}

	if($processed) {
		cpmsg("$lang[counter_forum]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		DB::query("UPDATE ".DB::table('forum_forum')." SET threads='0', posts='0' WHERE type='group'");
		cpmsg('counter_forum_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('digestsubmit', 1)) {

	if(!$current) {
		DB::query("UPDATE ".DB::table('common_member_count')." SET digestposts=0", 'UNBUFFERED');
		$current = 0;
	}
	$nextlink = "action=counter&current=$next&pertask=$pertask&digestsubmit=yes";
	$processed = 0;
	$membersarray = $postsarray = array();

	$query = DB::query("SELECT authorid FROM ".DB::table('forum_thread')." WHERE digest<>'0' AND displayorder>='0' LIMIT $current, $pertask");
	while($thread = DB::fetch($query)) {
		$processed = 1;
		$membersarray[$thread['authorid']]++;
	}
	$threadtableids = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='threadtableids'");
	if($threadtableids) {
		$threadtableids = unserialize($threadtableids);
	} else {
		$threadtableids = array();
	}
	foreach($threadtableids as $tableid) {
		if(!$tableid) {
			continue;
		}
		$threadtable = "forum_thread_$tableid";
		$query = DB::query("SELECT authorid FROM ".DB::table($threadtable)." WHERE digest<>'0' AND displayorder>='0' LIMIT $current, $pertask");
		while($thread = DB::fetch($query)) {
			$processed = 1;
			$membersarray[$thread['authorid']] ++;
		}
	}

	foreach($membersarray as $uid => $posts) {
		$postsarray[$posts] .= ','.$uid;
	}
	unset($membersarray);

	foreach($postsarray as $posts => $uids) {
		DB::query("UPDATE ".DB::table('common_member_count')." SET digestposts=digestposts+'$posts' WHERE uid IN (0$uids)", 'UNBUFFERED');
	}

	if($processed) {
		cpmsg("$lang[counter_digest]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_digest_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('membersubmit', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&membersubmit=yes";
	$processed = 0;

	$queryt = DB::query("SELECT uid FROM ".DB::table('common_member')." LIMIT $current, $pertask");
	$threadtableids = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='threadtableids'");
	if($threadtableids) {
		$threadtableids = unserialize($threadtableids);
	} else {
		$threadtableids = array();
	}
	while($mem = DB::fetch($queryt)) {
		$processed = 1;
		$postcount = 0;
		loadcache('posttable_info');
		if(!empty($_G['cache']['posttable_info']) && is_array($_G['cache']['posttable_info'])) {
			foreach($_G['cache']['posttable_info'] as $key => $value) {
				$postcount += DB::result_first("SELECT COUNT(*) FROM ".DB::table(getposttable($key))." WHERE authorid='$mem[uid]' AND invisible='0'");
			}
		} else {
			$postcount += DB::result_first("SELECT COUNT(*) FROM ".DB::table(getposttable())." WHERE authorid='$mem[uid]' AND invisible='0'");
		}
		$postcount += DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_postcomment')." WHERE authorid='$mem[uid]'");
		$threadcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE authorid='$mem[uid]'");
		foreach($threadtableids as $tableid) {
			if(!$tableid) {
				continue;
			}
			$threadtable = "forum_thread_$tableid";
			$threadcount += DB::result_first("SELECT COUNT(*) FROM ".DB::table($threadtable)." WHERE authorid='$mem[uid]'");
		}
		DB::query("UPDATE ".DB::table('common_member_count')." SET posts='".$postcount."', threads='".$threadcount."' WHERE uid='$mem[uid]'");
	}

	if($processed) {
		cpmsg("$lang[counter_member]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_member_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('threadsubmit', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&threadsubmit=yes";
	$processed = 0;

	$queryt = DB::query("SELECT tid, replies, lastpost, lastposter, author FROM ".DB::table('forum_thread')." WHERE displayorder>='0' LIMIT $current, $pertask");
	while($threads = DB::fetch($queryt)) {
		$processed = 1;
		$posttable = getposttablebytid($threads['tid']);
		$replynum = DB::result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$threads[tid]' AND invisible='0'");
		$replynum--;
		$lastpost = DB::fetch_first("SELECT author, dateline FROM ".DB::table($posttable)." WHERE tid='$threads[tid]' AND invisible='0' ORDER BY dateline DESC LIMIT 1");
		if($threads['replies'] != $replynum || $threads['lastpost'] != $lastpost['dateline'] || $threads['lastposter'] != $lastpost['author']) {
			if(empty($threads['author'])) {
				$lastpost['author'] = '';
			}
			DB::query("UPDATE LOW_PRIORITY ".DB::table('forum_thread')." SET replies='$replynum', lastpost='$lastpost[dateline]', lastposter='".addslashes($lastpost['author'])."' WHERE tid='$threads[tid]'", 'UNBUFFERED');
		}
	}

	if($processed) {
		cpmsg("$lang[counter_thread]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_thread_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('movedthreadsubmit', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&movedthreadsubmit=yes";
	$processed = 0;

	$tids = 0;
	$updateclosed = array();
	$query = DB::query("SELECT t1.tid, t2.tid AS threadexists, f.status, t1.isgroup FROM ".DB::table('forum_thread')." t1
		LEFT JOIN ".DB::table('forum_thread')." t2 ON t2.tid=t1.closed AND t2.displayorder>='0' LEFT JOIN ".DB::table('forum_forum')." f ON f.fid=t1.fid
		WHERE t1.closed>'1' LIMIT $current, $pertask");

	while($thread = DB::fetch($query)) {
		$processed = 1;
		if($thread['isgroup'] && $thread['status'] == 3) {
			$updateclosed[] = $thread['tid'];
		} elseif($thread['threadexists']) {
			$tids .= ','.$thread['tid'];
			my_thread_log('delete', array('tid' => $thread['tid']));
		}
	}

	if($tids) {
		DB::query("DELETE FROM ".DB::table('forum_thread')." WHERE tid IN ($tids)", 'UNBUFFERED');
	}
	if($updateclosed) {
		DB::query("UPDATE ".DB::table('forum_thread')." SET closed='' WHERE tid IN (".dimplode($updateclosed).")");
	}

	if($processed) {
		cpmsg(cplang('counter_moved_thread').': '.cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_moved_thread_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('specialarrange', 1)) {
	$cursort = empty($_G['gp_cursort']) ? 0 : intval($_G['gp_cursort']);
	$changesort = isset($_G['gp_changesort']) && empty($_G['gp_changesort']) ? 0 : 1;
	$processed = 0;

	$fieldtypes = array('number' => 'bigint(20)', 'text' => 'mediumtext', 'radio' => 'smallint(6)', 'checkbox' => 'mediumtext', 'textarea' => 'mediumtext', 'select' => 'smallint(6)', 'calendar' => 'mediumtext', 'email' => 'mediumtext', 'url' => 'mediumtext', 'image' => 'mediumtext');

	$optionvalues = array();

	$query = DB::query("SELECT v.*, p.identifier, p.type FROM ".DB::table('forum_typevar')." v LEFT JOIN ".DB::table('forum_typeoption')." p ON p.optionid=v.optionid WHERE search='1' OR p.type IN('radio','select','number')");
	$optionvalues = $sortids = array();
	while($row = DB::fetch($query)) {
		$optionvalues[$row['sortid']][$row['identifier']] = $row['type'];
		$optionids[$row['sortid']][$row['optionid']] = $row['identifier'];
		$searchs[$row['sortid']][$row['optionid']] = $row['search'];
		$sortids[] = $row['sortid'];
	}
	$sortids = array_unique($sortids);
	sort($sortids);
	if($sortids[$cursort] && $optionvalues[$sortids[$cursort]]) {
		$processed = 1;
		$sortid = $sortids[$cursort];
		$options = $optionvalues[$sortid];
		$search = $searchs[$sortid];
		$tablename = "".DB::table('forum_optionvalue')."{$sortid}";
		$query = DB::query("SHOW TABLES LIKE '$tablename'");
		if(DB::num_rows($query) != 1) {
			$create_table_sql = "CREATE TABLE $tablename (";
			$create_table_sql .= "tid mediumint(8) UNSIGNED NOT NULL DEFAULT '0',fid smallint(6) UNSIGNED NOT NULL DEFAULT '0',";
			$create_table_sql .= "KEY (fid)";
			$create_table_sql .= ") TYPE=MyISAM;";
			$dbcharset = $_G['config']['db'][1]['dbcharset'];
			$charset = $_G['charset'];
			$dbcharset = empty($dbcharset) ? str_replace('-','',$charset) : $dbcharset;
			$db = DB::object();
			$create_table_sql = syntablestruct($create_table_sql, $db->version() > '4.1', $dbcharset);
			DB::query($create_table_sql);
		}
		if($changesort) DB::query("TRUNCATE $tablename");
		$opids = array_keys($optionids[$sortid]);
		$tables = array();

		$query = DB::query("SHOW FULL COLUMNS FROM $tablename", 'SILENT');
		while($field = @DB::fetch($query)) {
			$tables[$field['Field']] = 1;
		}
		foreach($optionids[$sortid] as $optionid => $identifier) {
			if(!$tables[$identifier] && (in_array($options[$identifier], array('radio', 'select', 'number')) || $search[$optionid])) {
				$fieldname = $identifier;
				if(in_array($options[$identifier], array('radio'))) {
					$fieldtype = 'smallint(6) UNSIGNED NOT NULL DEFAULT \'0\'';
				} elseif(in_array($options[$identifier], array('number', 'range'))) {
					$fieldtype = 'int(10) UNSIGNED NOT NULL DEFAULT \'0\'';
				} elseif($options[$identifier] == 'select') {
					$fieldtype = 'varchar(50) NOT NULL \'0\'';
				} else {
					$fieldtype = 'mediumtext NOT NULL';
				}
				DB::query("ALTER TABLE ".DB::table('forum_optionvalue')."$sortid ADD $fieldname $fieldtype");

				if(in_array($options[$identifier], array('radio', 'select', 'number'))) {
					DB::query("ALTER TABLE ".DB::table('forum_optionvalue')."$sortid ADD INDEX ($fieldname)");
				}
			}
		}

		$query = DB::query("SELECT t.*, th.fid FROM ".DB::table('forum_typeoptionvar')." t left join ".DB::table('forum_thread')." th ON th.tid=t.tid WHERE t.sortid='$sortid' AND t.optionid IN ('".implode("','", $opids)."')");
		$inserts = array();
		while($row = DB::fetch($query)) {
			$opname = $optionids[$sortid][$row['optionid']];
			if(empty($inserts[$row[tid]])) {
				$inserts[$row['tid']]['tid'] = $row['tid'];
				$inserts[$row['tid']]['fid'] = $row['fid'];
			}
			$inserts[$row['tid']][$opname] = addslashes($row['value']);
		}
		if($inserts) {
			foreach($inserts as $tid => $fieldval) {
				$rfields = array();
				$ikey = $ival = '';
				foreach($fieldval as $ikey => $ival) {
					$rfields[] = "`$ikey`='$ival'";
				}
				DB::query("REPLACE INTO $tablename SET ".implode(',', $rfields));
			}
		}
		$cursort ++;
		$changesort = 1;
	}

	$nextlink = "action=counter&changesort=$changesort&cursort=$cursort&specialarrange=yes";
	if($processed) {
		cpmsg('counter_special_arrange', $nextlink, 'loading', array('cursort' => $cursort, 'sortids' => count($sortids)));
	} else {
		cpmsg('counter_special_arrange_succeed', 'action=counter', 'succeed');
	}


	$nextlink = "action=counter&current=$next&pertask=$pertask&membersubmit=yes";
	$processed = 0;

	$queryt = DB::query("SELECT uid FROM ".DB::table('common_member')." LIMIT $current, $pertask");
	while($mem = DB::fetch($queryt)) {
		$processed = 1;
		$postcount = 0;
		loadcache('posttable_info');
		if(!empty($_G['cache']['posttable_info']) && is_array($_G['cache']['posttable_info'])) {
			foreach($_G['cache']['posttable_info'] as $key => $value) {
				$postcount += DB::query("SELECT COUNT(*) FROM ".DB::table(getposttable($key))." WHERE authorid='$mem[uid]' AND invisible='0'");
			}
		} else {
			$postcount += DB::query("SELECT COUNT(*) FROM ".DB::table(getposttable()), " WHERE authorid='$mem[uid]' AND invisible='0'");
		}
		$postcount += DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_postcomment')." WHERE authorid='$mem[uid]'");
		$query_threads = DB::query("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE authorid='$mem[uid]'");
		DB::query("UPDATE ".DB::table('common_member_count')." SET posts='".$postcount."', threads='".DB::result($query_threads, 0)."' WHERE uid='$mem[uid]'");
	}

	if($processed) {
		cpmsg("$lang[counter_member]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_member_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('groupmembernum', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&groupmembernum=yes";
	$processed = 0;

	$query = DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE status='3' AND type='sub' LIMIT $current, $pertask");
	while($group = DB::fetch($query)) {
		$processed = 1;
		$membernum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_groupuser')." WHERE fid='$group[fid]' AND level>'0'");
		DB::query("UPDATE ".DB::table('forum_forumfield')." SET membernum = '$membernum' WHERE fid='$group[fid]'");
	}

	if($processed) {
		cpmsg("$lang[counter_groupmember_num]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_groupmember_num_succeed', 'action=counter', 'succeed');
	}
} elseif(submitcheck('groupmemberpost', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&groupmemberpost=yes";
	$processed = 0;

	$queryf = DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE status='3' AND type='sub' LIMIT $current, $pertask");
	while($group = DB::fetch($queryf)) {
		$processed = 1;

		$mreplies_array = array();
		loadcache('posttableids');
		$posttables = empty($_G['cache']['posttableids']) ? array(0) : $_G['cache']['posttableids'];
		foreach($posttables as $posttableid) {
			$query = DB::query('SELECT COUNT(*) as num, authorid FROM '.DB::table(getposttable($posttableid))." WHERE fid='$group[fid]' AND first='0' GROUP BY authorid");
			while($mreplies = DB::fetch($query)) {
				$mreplies_array[$mreplies['authorid']] = $mreplies_array[$mreplies['authorid']] + $mreplies['num'];
			}
		}
		foreach($mreplies_array as $authorid => $num) {
			DB::query("UPDATE ".DB::table('forum_groupuser')." SET replies = '$num' WHERE fid='$group[fid]' AND uid='$authorid'");
		}
		$queryt = DB::query("SELECT COUNT(*) as num, authorid FROM ".DB::table('forum_thread')." WHERE fid='$group[fid]' GROUP BY authorid");
		while($mthreads = DB::fetch($queryt)) {
			DB::query("UPDATE ".DB::table('forum_groupuser')." SET threads = '$mthreads[num]' WHERE fid='$group[fid]' AND uid='$mthreads[authorid]'");
		}
	}

	if($processed) {
		cpmsg("$lang[counter_groupmember_post]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_groupmember_post_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('groupnum', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&groupnum=yes";
	$processed = 0;

	$queryf = DB::query("SELECT fid FROM ".DB::table('forum_forum')." WHERE status='3' AND type='forum' LIMIT $current, $pertask");
	while($group = DB::fetch($queryf)) {
		$processed = 1;
		$queryg = DB::query("SELECT COUNT(*) as num FROM ".DB::table('forum_forum')." WHERE fup='$group[fid]' GROUP BY fup");
		while($groupnum = DB::fetch($queryg)) {
			DB::query("UPDATE ".DB::table('forum_forumfield')." SET groupnum = '$groupnum[num]' WHERE fid='$group[fid]'");
		}
	}

	if($processed) {
		cpmsg("$lang[counter_groupnum]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		updatecache('grouptype');
		DB::query("UPDATE ".DB::table('common_member_field_forum')." SET groups=''");
		cpmsg('counter_groupnum_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('blogreplynum', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&blogreplynum=yes";
	if(blog_replynum_stat($current, $pertask)) {
		cpmsg("$lang[counter_blog_replynum]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_blog_replynum_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('friendnum', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&friendnum=yes";
	if(space_friendnum_stat($current, $pertask)) {
		cpmsg("$lang[counter_friendnum]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_friendnum_succeed', 'action=counter', 'succeed');
	}

} elseif(submitcheck('albumpicnum', 1)) {

	$nextlink = "action=counter&current=$next&pertask=$pertask&albumpicnum=yes";
	if(album_picnum_stat($current, $pertask)) {
		cpmsg("$lang[counter_album_picnum]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_album_picnum_succeed', 'action=counter', 'succeed');
	}
} elseif(submitcheck('setthreadcover', 1)) {
	$fid = intval($_G['gp_fid']);
	$allthread = intval($_G['gp_allthread']);
	if(empty($fid)) {
		cpmsg('counter_thread_cover_fiderror', 'action=counter', 'error');
	}
	$nextlink = "action=counter&current=$next&pertask=$pertask&setthreadcover=yes&fid=$fid&allthread=$allthread";
	$processed = 0;
	$forumpicstyle = DB::result_first("SELECT picstyle FROM ".DB::table('forum_forumfield')." WHERE fid='$fid'");

	if(empty($forumpicstyle)) {
		cpmsg('counter_thread_cover_fidnopicstyle', 'action=counter', 'error');
	}
	if($_G['setting']['forumpicstyle']) {
		$_G['setting']['forumpicstyle'] = unserialize($_G['setting']['forumpicstyle']);
		empty($_G['setting']['forumpicstyle']['thumbwidth']) && $_G['setting']['forumpicstyle']['thumbwidth'] = 214;
		empty($_G['setting']['forumpicstyle']['thumbheight']) && $_G['setting']['forumpicstyle']['thumbheight'] = 160;
	} else {
		$_G['setting']['forumpicstyle'] = array('thumbwidth' => 214, 'thumbheight' => 160);
	}
	require_once libfile('function/post');
	$coversql = empty($allthread) ? 'AND cover=\'0\'' : '';
	$queryt = DB::query("SELECT tid, cover FROM ".DB::table('forum_thread')." WHERE fid='$fid' AND displayorder>='0' $coversql LIMIT $current, $pertask");
	$_G['forum']['ismoderator'] = 1;
	while($threads = DB::fetch($queryt)) {
		$processed = 1;
		$posttable = getposttablebytid($threads['tid']);
		$pid = DB::result_first("SELECT pid FROM ".DB::table($posttable)." WHERE tid='$threads[tid]' AND invisible='0' AND first='1'");
		setthreadcover($pid);
	}

	if($processed) {
		cpmsg("$lang[counter_thread_cover]: ".cplang('counter_processing', array('current' => $current, 'next' => $next)), $nextlink, 'loading');
	} else {
		cpmsg('counter_thread_cover_succeed', 'action=counter', 'succeed');
	}
} else {

	shownav('tools', 'nav_updatecounters');
	showsubmenu('nav_updatecounters');
	showformheader('counter');
	showtableheader();
	showsubtitle(array('', 'counter_amount'));
	showhiddenfields(array('pertask' => ''));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_forum]:",
		'<input name="pertask1" type="text" class="txt" value="15" /><input type="submit" class="btn" name="forumsubmit" onclick="this.form.pertask.value=this.form.pertask1.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_digest]:",
		'<input name="pertask2" type="text" class="txt" value="1000" /><input type="submit" class="btn" name="digestsubmit" onclick="this.form.pertask.value=this.form.pertask2.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_member]:",
		'<input name="pertask3" type="text" class="txt" value="1000" /><input type="submit" class="btn" name="membersubmit" onclick="this.form.pertask.value=this.form.pertask3.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_thread]:",
		'<input name="pertask4" type="text" class="txt" value="500" /><input type="submit" class="btn" name="threadsubmit" onclick="this.form.pertask.value=this.form.pertask4.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_moved_thread]:",
		'<input name="pertask5" type="text" class="txt" value="100" /><input type="submit" class="btn" name="movedthreadsubmit" onclick="this.form.pertask.value=this.form.pertask5.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_special]:",
		'<input name="pertask7" type="text" class="txt" value="1" disabled/><input type="submit" class="btn" name="specialarrange" onclick="this.form.pertask.value=this.form.pertask7.value" value="'.$lang['submit'].'" />'
	));

	showtablerow('', array('class="td21"'), array(
		"$lang[counter_groupnum]:",
		'<input name="pertask8" type="text" class="txt" value="10" /><input type="submit" class="btn" name="groupnum" onclick="this.form.pertask.value=this.form.pertask8.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_groupmember_num]:",
		'<input name="pertask9" type="text" class="txt" value="100" /><input type="submit" class="btn" name="groupmembernum" onclick="this.form.pertask.value=this.form.pertask9.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_groupmember_post]:",
		'<input name="pertask10" type="text" class="txt" value="100" /><input type="submit" class="btn" name="groupmemberpost" onclick="this.form.pertask.value=this.form.pertask10.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_blog_replynum]:",
		'<input name="pertask11" type="text" class="txt" value="100" /><input type="submit" class="btn" name="blogreplynum" onclick="this.form.pertask.value=this.form.pertask11.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_friendnum]:",
		'<input name="pertask12" type="text" class="txt" value="100" /><input type="submit" class="btn" name="friendnum" onclick="this.form.pertask.value=this.form.pertask12.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_album_picnum]:",
		'<input name="pertask13" type="text" class="txt" value="100" /><input type="submit" class="btn" name="albumpicnum" onclick="this.form.pertask.value=this.form.pertask13.value" value="'.$lang['submit'].'" />'
	));
	showtablerow('', array('class="td21"'), array(
		"$lang[counter_thread_cover]:",
		'<input name="pertask14" type="text" class="txt" value="100" /> '.$lang['counter_forumid'].': <input type="text" class="txt" name="fid" value="" size="10">&nbsp;<input type="checkbox" value="1" name="allthread">'.$lang['counter_have_cover'].'&nbsp;&nbsp;<input type="submit" class="btn" name="setthreadcover" onclick="this.form.pertask.value=this.form.pertask14.value" value="'.$lang['submit'].'" />'
	));
	showtablefooter();
	showformfooter();

}

function runuchcount($start, $perpage) {

}

function syntablestruct($sql, $version, $dbcharset) {

	if(strpos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === FALSE) {
		return $sql;
	}

	$sqlversion = strpos($sql, 'ENGINE=') === FALSE ? FALSE : TRUE;

	if($sqlversion === $version) {

		return $sqlversion && $dbcharset ? preg_replace(array('/ character set \w+/i', '/ collate \w+/i', "/DEFAULT CHARSET=\w+/is"), array('', '', "DEFAULT CHARSET=$dbcharset"), $sql) : $sql;
	}

	if($version) {
		return preg_replace(array('/TYPE=HEAP/i', '/TYPE=(\w+)/is'), array("ENGINE=MEMORY DEFAULT CHARSET=$dbcharset", "ENGINE=\\1 DEFAULT CHARSET=$dbcharset"), $sql);

	} else {
		return preg_replace(array('/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i', '/\s*DEFAULT CHARSET=\w+/is', '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'), array('', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2'), $sql);
	}
}
?>