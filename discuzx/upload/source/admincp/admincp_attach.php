<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_attach.php 23765 2011-08-09 07:41:42Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$searchsubmit = $_G['gp_searchsubmit'];

if(!submitcheck('deletesubmit')) {

	require_once libfile('function/forumlist');
	$anchor = isset($_G['gp_anchor']) ? $_G['gp_anchor'] : '';
	$anchor = in_array($anchor, array('search', 'admin')) ? $anchor : 'search';

	shownav('topic', 'nav_attaches'.($operation ? '_'.$operation : ''));
	showsubmenusteps('nav_attaches'.($operation ? '_'.$operation : ''), array(
		array('search', !$searchsubmit),
		array('admin', $searchsubmit),
	));
	showtips('attach_tips', 'attach_tips', $searchsubmit);
	showtagheader('div', 'search', !$searchsubmit);
	showformheader('attach&attachtableid='.$_G['gp_attachtableid'].($operation ? '&operation='.$operation : ''));
	showtableheader();
	$attachtableids = array();
	for($i = 0;$i < 10;$i++) {
		$attachtableids[] = array($i, $lang['attach_attachtableid_n'].' '.($i + 1));
	}
	showsetting('attach_attachtableid', array('attachtableid', $attachtableids), 0, 'select');
	showsetting('attach_nomatched', 'nomatched', 0, 'radio');
	if($operation != 'group') {
		showsetting('attach_forum', '', '', '<select name="inforum"><option value="all">&nbsp;&nbsp;>'.cplang('all').'</option><option value="">&nbsp;</option>'.forumselect(FALSE, 0, 0, TRUE).'</select>');
	}
	showsetting('attach_search_perpage', '', $_G['gp_perpage'], "<select name='perpage'><option value='20'>$lang[perpage_20]</option><option value='50'>$lang[perpage_50]</option><option value='100'>$lang[perpage_100]</option></select>");
	showsetting('attach_sizerange', array('sizeless', 'sizemore'), array('', ''), 'range');
	showsetting('attach_dlcountrange', array('dlcountless', 'dlcountmore'), array('', ''), 'range');
	showsetting('attach_daysold', 'daysold', '', 'text');
	showsetting('filename', 'filename', '', 'text');
	showsetting('attach_keyword', 'keywords', '', 'text');
	showsetting('attach_author', 'author', '', 'text');
	showsubmit('searchsubmit', 'search');
	showtablefooter();
	showformfooter();
	showtagfooter('div');

	if(submitcheck('searchsubmit')) {

		require_once libfile('function/attachment');
		$operation == 'group' && $_G['gp_inforum'] = 'isgroup';
		$sql = "1";
		$inforum = $_G['gp_inforum'] != 'all' && $_G['gp_inforum'] != 'isgroup' ? intval($_G['gp_inforum']) : $_G['gp_inforum'];

		$sql .= is_numeric($inforum) ? " AND t.fid='$inforum'" : '';
		$sql .= $inforum == 'isgroup' ? ' AND t.isgroup=\'1\'' : ' AND t.isgroup=\'0\'';
		if($_G['gp_author']) {
			if($authorid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$_G[gp_author]'")) {
				$sql .= " AND a.uid='$authorid'";
			}
		}
		$sql .= $_G['gp_filename'] ? " AND a.filename LIKE '%$_G[gp_filename]%'" : '';

		if($_G['gp_keywords']) {
			$sqlkeywords = $or = '';
			foreach(explode(',', str_replace(' ', '', $_G['gp_keywords'])) as $_G['gp_keywords']) {
				$sqlkeywords .= " $or a.description LIKE '%$_G[gp_keywords]%'";
				$or = 'OR';
			}
			$sql .= " AND ($sqlkeywords)";
		}

		$sql .= $_G['gp_sizeless'] ? " AND a.filesize<'$_G[gp_sizeless]'" : '';
		$sql .= $_G['gp_sizemore'] ? " AND a.filesize>'$_G[gp_sizemore]' " : '';
		$sql .= $_G['gp_dlcountless'] ? " AND ai.downloads<'$_G[gp_dlcountless]'" : '';
		$sql .= $_G['gp_dlcountmore'] ? " AND ai.downloads>'$_G[gp_dlcountmore]'" : '';
		$sql .= $_G['gp_daysold'] ? " AND a.dateline<'".(TIMESTAMP - intval($_G['gp_daysold']) * 86400)."'" : '';

		$attachments = '';
		$_G['gp_perpage'] = intval($_G['gp_perpage']) < 1 ? 20 : intval($_G['gp_perpage']);
		$perpage = $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage'];
		$attachmentcount = DB::result_first("SELECT COUNT(*)
			FROM ". DB::table('forum_attachment_'.$_G['gp_attachtableid'])." a
			INNER JOIN ".DB::table('forum_attachment')." ai USING(aid)
			INNER JOIN ".DB::table('common_member')." m ON m.uid=a.uid
			INNER JOIN ".DB::table('forum_thread')." t
			INNER JOIN ".DB::table('forum_forum')." f
			WHERE t.tid=a.tid AND f.fid=t.fid AND t.displayorder>='0' AND $sql");
		$query = DB::query("SELECT a.*, ai.downloads, m.username, t.fid, t.tid, t.subject, f.name AS fname
			FROM ". DB::table('forum_attachment_'.$_G['gp_attachtableid'])." a
			LEFT JOIN ".DB::table('forum_attachment')." ai USING(aid)
			LEFT JOIN ".DB::table('common_member')." m ON m.uid=a.uid
			INNER JOIN ".DB::table('forum_thread')." t
			INNER JOIN ".DB::table('forum_forum')." f
			WHERE t.tid=a.tid AND f.fid=t.fid AND t.displayorder>='0' AND $sql ORDER BY a.aid DESC
			LIMIT ".(($page - 1) * $perpage).','.$perpage);

		while($attachment = DB::fetch($query)) {
			if(!$attachment['remote']) {
				$matched = file_exists($_G['setting']['attachdir'].'/forum/'.$attachment['attachment']) ? '' : cplang('attach_lost');
				$attachment['url'] = $_G['setting']['attachurl'].'forum/';
			} else {
				@set_time_limit(0);
				if(@fclose(@fopen($_G['setting']['ftp']['attachurl'].$attachment['attachment'], 'r'))) {
					$matched = '';
				} else {
					$matched = cplang('attach_lost');
				}
				$attachment['url'] = $_G['setting']['ftp']['attachurl'].'forum/';
			}
			$attachsize = sizecount($attachment['filesize']);
			if(!$_G['gp_nomatched'] || ($_G['gp_nomatched'] && $matched)) {
				$attachment['url'] = trim($attachment['url'], '/');
				$attachments .= showtablerow('', array('class="td25"', 'title="'.$attachment['description'].'" class="td21"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$attachment[aid]\" />",
					$attachment['remote'] ? "<span class=\"diffcolor3\">$attachment[filename]" : $attachment['filename'],
					$attachment['username'],
					"<a href=\"forum.php?mod=viewthread&tid=$attachment[tid]\" target=\"_blank\">".cutstr($attachment['subject'], 20)."</a>",
					$attachsize,
					$attachment['downloads'],
					$matched ? "<em class=\"error\">$matched<em>" : "<a href=\"forum.php?mod=attachment&aid=".aidencode($attachment['aid'])."&noupdate=yes\" target=\"_blank\" class=\"act nomargin\">$lang[download]</a>"
				), TRUE);
			}
		}

		$multipage = multi($attachmentcount, $perpage, $page, ADMINSCRIPT."?action=attachments");
		$multipage = preg_replace("/href=\"".ADMINSCRIPT."\?action=attachments&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multipage);
		$multipage = str_replace("window.location='".ADMINSCRIPT."?action=attachments&amp;page='+this.value", "page(this.value)", $multipage);

		echo <<<EOT
<script type="text/JavaScript">
	function page(number) {
		$('attachmentforum').page.value=number;
		$('attachmentforum').searchsubmit.click();
	}
</script>
EOT;
		showtagheader('div', 'admin', $searchsubmit);
		showformheader('attach&attachtableid='.$_G['gp_attachtableid'].($operation ? '&operation='.$operation : ''), '', 'attachmentforum');
		showhiddenfields(array(
			'page' => $page,
			'nomatched' => $_G['gp_nomatched'],
			'inforum' => $_G['gp_inforum'],
			'sizeless' => $_G['gp_sizeless'],
			'sizemore' => $_G['gp_sizemore'],
			'dlcountless' => $_G['gp_dlcountless'],
			'dlcountmore' => $_G['gp_dlcountmore'],
			'daysold' => $_G['gp_daysold'],
			'filename' => $_G['gp_filename'],
			'keywords' => $_G['gp_keywords'],
			'author' => $_G['gp_author'],
			'pp' => $_G['gp_pp'] ? $_G['gp_pp'] : $_G['gp_perpage']
		));
		echo '<input type="submit" name="searchsubmit" value="'.cplang('submit').'" class="btn" style="display: none" />';
		showformfooter();

		showformheader('attach&attachtableid='.$_G['gp_attachtableid'].'&frame=no'.($operation ? '&operation='.$operation : ''), 'target="attachmentframe"');
		showtableheader();
		showsubtitle(array('', 'filename', 'author', 'attach_thread', 'size', 'attach_downloadnums', ''));
		echo $attachments;
		showsubmit('deletesubmit', 'submit', 'del', '<a href="###" onclick="$(\'admin\').style.display=\'none\';$(\'search\').style.display=\'\';$(\'attachmentforum\').pp.value=\'\';$(\'attachmentforum\').page.value=\'\';" class="act lightlink normal">'.cplang('research').'</a>', $multipage);
		showtablefooter();
		showformfooter();
		echo '<iframe name="attachmentframe" style="display:none"></iframe>';
		showtagfooter('div');

	}

} else {

	if($ids = dimplode($_G['gp_delete'])) {

		$tids = $pids = 0;
		$query = DB::query("SELECT tid, pid, attachment, thumb, remote, aid FROM ".DB::table('forum_attachment_'.$_G['gp_attachtableid'])." WHERE aid IN ($ids)");
		while($attach = DB::fetch($query)) {
			dunlink($attach);
			$tids .= ','.$attach['tid'];
			$pids .= ','.$attach['pid'];
		}
		DB::query("DELETE FROM ".DB::table('forum_attachment_'.$_G['gp_attachtableid'])." WHERE aid IN ($ids)");

		$attachtids = 0;
		$query = DB::query("SELECT tid FROM ".DB::table('forum_attachment_'.$_G['gp_attachtableid'])." WHERE tid IN ($tids) GROUP BY tid ORDER BY pid DESC");
		while($attach = DB::fetch($query)) {
			$attachtids .= ','.$attach['tid'];
		}
		DB::query("UPDATE ".DB::table('forum_thread')." SET attachment='0' WHERE tid IN ($tids)".($attachtids ? " AND tid NOT IN ($attachtids)" : NULL));

		$attachpids = 0;
		$query = DB::query("SELECT pid FROM ".DB::table('forum_attachment_'.$_G['gp_attachtableid'])." WHERE pid IN ($pids) GROUP BY pid ORDER BY pid DESC");
		while($attach = DB::fetch($query)) {
			$attachpids .= ','.$attach['pid'];
		}

		updatepost(array('attachment' => '0'), "pid IN ($pids)".($attachpids ? "AND pid NOT IN ($attachpids)" : NULL));

		$cpmsg = cplang('attach_edit_succeed');

	} else {

		$cpmsg = cplang('attach_edit_invalid');

	}

	echo "<script type=\"text/JavaScript\">alert('$cpmsg');parent.\$('attachmentforum').searchsubmit.click();</script>";
}

?>