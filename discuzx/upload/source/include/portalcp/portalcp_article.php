<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_article.php 24254 2011-09-02 02:43:37Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = in_array($_GET['op'], array('addpage', 'edit', 'delpage', 'delete', 'related', 'batch', 'pushplus', 'verify')) ? $_GET['op'] : 'add';
$aid = intval($_G['gp_aid']);
$catid = intval($_G['gp_catid']);
$seccodecheck = $_G['setting']['seccodestatus'] & 4;
$secqaacheck = $_G['setting']['secqaa']['status'] & 2;

$article = $article_content = array();
if($aid) {
	$article = DB::fetch_first("SELECT * FROM ".DB::table('portal_article_title')." WHERE aid='$aid'");
	if(!$article) {
		showmessage('article_not_exist', dreferer());
	}
}

loadcache('portalcategory');
$portalcategory = $_G['cache']['portalcategory'];
if($catid && empty($portalcategory[$catid])) {
	showmessage('portal_category_not_find', dreferer());
}
if(empty($article) && $catid && $portalcategory[$catid]['disallowpublish']) {
	showmessage('portal_category_disallowpublish', dreferer());
}

if(submitcheck("articlesubmit", 0, $seccodecheck, $secqaacheck)) {

	if($aid) {
		check_articleperm($article['catid'], $aid, $article);
	} else {
		check_articleperm($catid);
	}

	$_POST['title'] = getstr(trim($_POST['title']), 80, 1, 1);
	if(strlen($_POST['title']) < 1) {
		showmessage('title_not_too_little');
	}
	$_POST['title'] = censor($_POST['title']);

	$_POST['pagetitle'] = getstr(trim($_POST['pagetitle']), 60, 1, 1);
	$_POST['pagetitle'] = censor($_POST['pagetitle']);

	$highlight_style = $_G['gp_highlight_style'];
	$style = '';
	$style = implode('|',$highlight_style);
	if(empty($_POST['summary'])) $_POST['summary'] = preg_replace("/(\s|###NextPage(\[title=.*?\])?###)+/", ' ', $_POST['content']);
	$summary = portalcp_get_summary(stripslashes($_POST['summary']));
	$summary = censor($summary);

	$_G['gp_author'] = dhtmlspecialchars($_G['gp_author']);
	$_G['gp_url'] = str_replace('&amp;', '&', dhtmlspecialchars($_G['gp_url']));
	$_G['gp_from'] = dhtmlspecialchars($_G['gp_from']);
	$_G['gp_fromurl'] = str_replace('&amp;', '&', dhtmlspecialchars($_G['gp_fromurl']));
	$_G['gp_dateline'] = !empty($_G['gp_dateline']) ? strtotime($_G['gp_dateline']) : TIMESTAMP;
	$_G['gp_shorttitle'] = getstr(trim(dhtmlspecialchars($_G['gp_shorttitle'])), 80, 1, 1);
	$_G['gp_shorttitle'] = censor($_G['gp_shorttitle']);
	if(censormod($_G['gp_shorttitle']) || censormod($_POST['title']) || $_G['group']['allowpostarticlemod']) {
		$article_status = 1;
	} else {
		$article_status = 0;
	}

	$setarr = array(
		'title' => $_POST['title'],
		'shorttitle' => $_G['gp_shorttitle'],
		'author' => $_G['gp_author'],
		'from' => $_G['gp_from'],
		'fromurl' => $_G['gp_fromurl'],
		'dateline' => intval($_G['gp_dateline']),
		'url' => $_G['gp_url'],
		'allowcomment' => !empty($_POST['forbidcomment']) ? '0' : '1',
		'summary' => addslashes($summary),
		'catid' => intval($_POST['catid']),
		'tag' => article_make_tag($_POST['tag']),
		'status' => $article_status,
		'highlight' => $style,
		'showinnernav' => empty($_POST['showinnernav']) ? '0' : '1',
	);

	if(empty($setarr['catid'])) {
		showmessage('article_choose_system_category');
	}

	if($_G['gp_conver']) {
		$converfiles = unserialize(stripslashes($_G['gp_conver']));
		$setarr['pic'] = addslashes($converfiles['pic']);
		$setarr['thumb'] = intval($converfiles['thumb']);
		$setarr['remote'] = intval($converfiles['remote']);
	}

	$id = 0;
	$idtype = '';

	if(empty($article)) {
		$setarr['uid'] = $_G['uid'];
		$setarr['username'] = $_G['username'];
		$setarr['id'] = intval($_POST['id']);
		$table = '';
		if($setarr['id']) {
			if($_POST['idtype']=='blogid') {
				$table = 'home_blogfield';
				$setarr['idtype'] = 'blogid';
				$id = $setarr['id'];
				$idtype = $setarr['idtype'];
			} else {
				$table = 'forum_thread';
				$setarr['idtype'] = 'tid';

				require_once libfile('function/discuzcode');
				$posttable = getposttablebytid($setarr['id']);
				$org = DB::fetch_first("SELECT pid FROM ".DB::table($posttable)." WHERE tid='$setarr[id]' AND first='1'");
				$id = intval($org['pid']);
				$idtype = 'pid';
			}
		}
		$aid = DB::insert('portal_article_title', $setarr, 1);
		if($table) {
			DB::query('UPDATE '.DB::table($table)." SET pushedaid='$aid' WHERE $setarr[idtype] = '$setarr[id]'");
			if($setarr['idtype']=='tid') {
				$modarr = array(
					'tid' => $setarr['id'],
					'uid' => $_G['uid'],
					'username' => $_G['username'],
					'dateline' => TIMESTAMP,
					'action' => 'PTA',
					'status' => '1',
					'stamp' => '',
				);
				DB::insert('forum_threadmod', $modarr);

				DB::update('forum_thread', array('moderated'=>1), array('tid'=>$setarr['id']));
			}
		}
		DB::update('common_member_status', array('lastpost' => $_G['timestamp']), array('uid' => $_G['uid']));
		DB::query('UPDATE '.DB::table('portal_category')." SET articles=articles+1 WHERE catid = '$setarr[catid]'");
		DB::insert('portal_article_count', array('aid'=>$aid, 'catid'=>$setarr['catid'], 'dateline'=>$setarr['dateline'],'viewnum'=>1));
	} else {
		DB::update('portal_article_title', $setarr, array('aid' => $aid));
	}

	$cid = intval($_POST['cid']);
	if($cid) {
		$query = DB::query("SELECT * FROM ".DB::table('portal_article_content')." WHERE cid='$cid' AND aid='$aid'");
		$article_content = DB::fetch($query);
	}

	$content = getstr($_POST['content'], 0, 1, 1, 0, 1);
	$content = censor($content);
	if(censormod($content) || $_G['group']['allowpostarticlemod']) {
		$article_status = 1;
	} else {
		$article_status = 0;
	}

	$regexp = '/(###NextPage(\[title=(.*?)\])?###)+/';
	preg_match_all($regexp, $content ,$arr);
	$pagetitle = !empty($arr[3]) ? $arr[3] : array();
	$pagetitle = array_map('trim', $pagetitle);
	$contents = preg_split($regexp, $content);
	$content_count = count($contents);

	$pageorder = intval($_POST['pageorder']);

	if($pageorder>0) {
		$startorder = $pageorder - 1;
		$pageorder = DB::result(DB::query("SELECT pageorder FROM ".DB::table('portal_article_content')." WHERE aid='$aid' ORDER BY pageorder LIMIT $startorder, 1"), 0);

		if($article_content && $article_content['pageorder'] == $pageorder) {
			$content_count = $content_count - 1;
		}
		if($content_count > 0) {
			DB::query('UPDATE '.DB::table('portal_article_content')." SET pageorder = pageorder+$content_count WHERE aid='$aid' AND pageorder>='$pageorder'");
		}
	} else {
		$pageorder = DB::result(DB::query("SELECT MAX(pageorder) FROM ".DB::table('portal_article_content')." WHERE aid='$aid'"), 0);
		$pageorder = $pageorder + 1;
	}

	if($article_content) {
		$setarr = array(
			'title' => $_POST['pagetitle'],
			'content' => trim($contents[0]),
			'pageorder' => $pageorder,
			'dateline' => $_G['timestamp']
		);
		DB::update('portal_article_content', $setarr, array('cid'=>$cid));
		if($article_status == 1) {
			DB::update('portal_article_title', array('status' => '1'), array('aid' => $aid));
		}

		unset($contents[0]);
	}

	if($contents) {
		$inserts = array();
		foreach ($contents as $key => $value) {
			$value = trim($value);
			$inserts[] = "('$aid', '".(empty($pagetitle[$key-1]) ? $_POST['pagetitle'] : $pagetitle[$key-1])."', '$value', '".($pageorder+$key)."', '$_G[timestamp]', '$id', '$idtype')";
		}
		DB::query("INSERT INTO ".DB::table('portal_article_content')."
			(aid, title, content, pageorder, dateline, id, idtype)
			VALUES ".implode(',', $inserts));

		DB::query('UPDATE '.DB::table('portal_article_title')." SET status = '$article_status', contents = contents+".count($inserts)." WHERE aid='$aid'");
	}
	if($article_status == 1) {
		updatemoderate('aid', $aid);
		manage_addnotify('verifyarticle');
	}

	$newaids = array();
	$_POST['attach_ids'] = explode(',', $_POST['attach_ids']);
	foreach ($_POST['attach_ids'] as $newaid) {
		$newaid = intval($newaid);
		if($newaid) $newaids[$newaid] = $newaid;
	}
	if($newaids) {
		DB::update('portal_attachment', array('aid'=>$aid), "attachid IN (".dimplode($newaids).") AND aid='0'");
	}

	DB::query("DELETE FROM ".DB::table('portal_article_related')." WHERE aid='$aid' OR raid='$aid'");
	if($_POST['raids']) {
		$relatedarr = array();
		$relatedarr = array_map('intval', $_POST['raids']);
		$relatedarr = array_unique($relatedarr);
		$relatedarr = array_filter($relatedarr);
		if($relatedarr) {
			$query = DB::query("SELECT * FROM ".DB::table('portal_article_title')." WHERE aid IN (".dimplode($relatedarr).")");
			$list = array();
			while(($value=DB::fetch($query))) {
				$list[$value['aid']] = $value;
			}
			$replaces = array();
			$displayorder = 0;
			foreach($relatedarr as $relate) {
				if(($value = $list[$relate])) {
					if($value['aid'] != $aid) {
						$replaces[] = "('$aid', '$value[aid]', '$displayorder')";
						$replaces[] = "('$value[aid]', '$aid', '0')";
						$displayorder++;
					}
				}
			}
			if($replaces) {
				DB::query("REPLACE INTO ".DB::table('portal_article_related')." (aid,raid,displayorder) VALUES ".implode(',', $replaces));
			}
		}
	}

	if($_G['gp_from_idtype'] && $_G['gp_from_id']) {

		$id = intval($_G['gp_from_id']);
		$notify = array();
		switch ($_G['gp_from_idtype']) {
			case 'blogid':
				$blog = DB::fetch_first("SELECT * FROM ".DB::table('home_blog')." WHERE blogid='$id'");
				if(!empty($blog)) {
					$notify = array(
						'url' => "home.php?mod=space&uid=$blog[uid]&do=blog&id=$id",
						'subject' => $blog['subject']
					);
					$touid = $blog['uid'];
				}
				break;
			case 'tid':
				$thread = DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid='$id'");
				if(!empty($thread)) {
					$notify = array(
						'url' => "forum.php?mod=viewthread&tid=$id",
						'subject' => $thread['subject']
					);
					$touid = $thread['authorid'];
				}
				break;
		}
		if(!empty($notify)) {
			$notify['newurl'] = 'portal.php?mod=view&aid='.$aid;
			notification_add($touid, 'pusearticle', 'puse_article', $notify, 1);
		}
	}


	$viewarticleurl = $_POST['url']?"portal.php?mod=list&catid=$_POST[catid]":'portal.php?mod=view&aid='.$aid;

	if(trim($_G['gp_from']) != '') {
		$from_cookie = '';
		$from_cookie_array = array();
		$from_cookie = stripslashes(getcookie('from_cookie'));
		$from_cookie_array = explode('\t', $from_cookie);
		$from_cookie_array[] = $_G['gp_from'];
		$from_cookie_array = array_unique($from_cookie_array);
		$from_cookie_array = array_filter($from_cookie_array);
		$from_cookie_num = count($from_cookie_array);
		$from_cookie_start = $from_cookie_num - 10;
		$from_cookie_start = $from_cookie_start > 0 ? $from_cookie_start : 0;
		$from_cookie_array = array_slice($from_cookie_array, $from_cookie_start, $from_cookie_num);
		$from_cookie = implode('\t', $from_cookie_array);
		dsetcookie('from_cookie', $from_cookie);
	}
	dsetcookie('clearUserdata', 'home');
	$op = 'add_success';
	$article_add_page_url = '';
	$article_add_url = 'portal.php?mod=portalcp&ac=article&catid='.$catid;
	if($_G['gp_addpage']) {
		$article_add_page_url = $article_add_url.'&op=addpage&aid='.$aid;
	}
	include_once template("portal/portalcp_article");dexit();

} elseif(submitcheck('pushplussubmit')) {

	if($aid) {
		check_articleperm($article['catid'], $aid, $article);
	} else {
		showmessage('no_article_specified_for_pushplus', dreferer());
	}

	$tourl = !empty($_POST['toedit']) ? 'portal.php?mod=portalcp&ac=article&op=edit&aid='.$aid : dreferer();
	$pids = (array)$_POST['pushpluspids'];
	$posts = array();
	$tid = intval($_GET['tid']);
	if($tid && $pids) {
		$posttable = getposttablebytid($tid);
		$query = DB::query('SELECT * FROM '.DB::table($posttable)." WHERE tid='$tid' AND pid IN (".dimplode($pids).')');
		while(($value=DB::fetch($query))) {
			$posts[$value['pid']] = $value;
		}
	}
	if(empty($posts)) {
		showmessage('no_posts_for_pushplus', dreferer());
	}

	$pageorder = DB::result(DB::query("SELECT MAX(pageorder) FROM ".DB::table('portal_article_content')." WHERE aid='$aid'"), 0);
	$pageorder = intval($pageorder + 1);
	$inserts = array();
	foreach($posts as $post) {
		$summary = portalcp_get_postmessage($post);
		$summary .= lang('portalcp', 'article_pushplus_info', array('author'=>$post['author'], 'url'=>'forum.php?mod=redirect&goto=findpost&ptid='.$post['tid'].'&pid='.$post['pid']));
		$summary = addslashes($summary);
		$inserts[] = "('$aid', '$summary', '$pageorder', '$_G[timestamp]', '$post[pid]', 'pid')";
		$pageorder++;
	}
	DB::query('INSERT INTO '.DB::table('portal_article_content')."(aid, content, pageorder, dateline, id, idtype) VALUES ".implode(',',$inserts));

	$pluscount = count($posts);
	DB::query('UPDATE '.DB::table('portal_article_title')." SET contents=contents+'$pluscount', owncomment='1' WHERE aid='$aid'");
	$commentnum = DB::result_first('SELECT COUNT(*) FROM '.DB::table('portal_comment')." WHERE id='$aid' AND idtype='aid'");
	DB::update('portal_article_count', array('commentnum'=>intval($commentnum)), array('aid'=>$aid));
	showmessage('pushplus_do_success', $tourl, array(), array('header'=>1, 'refreshtime'=>0));

} elseif(submitcheck('verifysubmit')) {
	if($aid) {
		check_articleperm($article['catid'], $aid, $article, true);
	} else {
		showmessage('article_not_exist', dreferer());
	}
	if($_POST['status'] == '0') {
		DB::update('portal_article_title', array('status'=>'0'), array('aid'=>$aid));

		$tourl = dreferer('portal.php?mod=view&aid='.$aid);
		showmessage('article_passed', $tourl);

	} elseif($_POST['status'] == '2') {
		DB::update('portal_article_title', array('status'=>'2'), array('aid'=>$aid));

		$tourl = dreferer('portal.php?mod=view&aid='.$aid);
		showmessage('article_ignored', $tourl);

	} elseif($_POST['status'] == '-1') {
		include_once libfile('function/delete');
		deletearticle(array($aid), 0);

		$tourl = dreferer('portal.php?mod=portalcp&catid='.$article['catid']);
		showmessage('article_deleted', $tourl);

	} else {
		showmessage('select_operation');
	}
}

if ($op == 'delpage') {

	if(!$aid) {
		showmessage('article_edit_nopermission');
	}
	check_articleperm($article['catid'], $aid, $article);


	$pageorder = intval($_GET['pageorder']);
	$aid = intval($_GET['aid']);
	$cid = intval($_GET['cid']);

	if($aid && $cid) {
		$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('portal_article_content')." WHERE aid='$aid'"), 0);
		if($count > 1) {
			DB::query('DELETE FROM '.DB::table('portal_article_content')." WHERE cid='$cid' AND aid='$aid'");
			DB::query('UPDATE '.DB::table('portal_article_title')." SET contents = contents-1 WHERE aid='$aid'");
		} else {
			showmessage('article_delete_invalid_lastpage');
		}
	}
	showmessage('do_success', "portal.php?mod=portalcp&ac=article&op=edit&quickforward=1&aid=$aid");

} elseif($op == 'delete') {

	if(!$aid) {
		showmessage('article_edit_nopermission');
	}
	check_articleperm($article['catid'], $aid, $article);

	if(submitcheck('deletesubmit')) {
		include_once libfile('function/delete');
		$article = deletearticle(array(intval($_POST['aid'])), intval($_POST['optype']));
		showmessage('article_delete_success', "portal.php?mod=list&catid={$article[0][catid]}");
	}

} elseif($op == 'related') {

	$raid = intval($_GET['raid']);
	$ra = array();
	if($raid) {
		$query = DB::query("SELECT * FROM ".DB::table('portal_article_title')." WHERE aid='$raid'");
		$ra = DB::fetch($query);
	}

} elseif($op == 'batch') {

	check_articleperm($catid);

	$aids = $_POST['aids'];
	$optype = $_POST['optype'];
	if(empty($optype) || $optype == 'push') showmessage('article_action_invalid');
	$aids = array_map('intval', $aids);
	$aids = array_filter($aids);
	if(empty($aids)) showmessage('article_not_choose');

	if (submitcheck('batchsubmit')) {
		if ($optype == 'trash' || $optype == 'delete') {
				require_once libfile('function/delete');
				$istrash = $optype == 'trash' ? 1 : 0;
				$article = deletearticle($aids, $istrash);
				showmessage('article_delete_success', dreferer("portal.php?mod=portalcp&ac=category&catid={$article[0][catid]}"));
		} elseif($optype == 'move') {
			if($catid) {
				DB::update('portal_article_title', array('catid'=>$catid), 'aid IN('.dimplode($aids).')');
				showmessage('article_move_success', dreferer("portal.php?mod=portalcp&ac=category&catid=$catid"));
			} else {
				showmessage('article_move_select_cat', dreferer());
			}
		}

	}

} elseif($op == 'verify') {
	if($aid) {
		check_articleperm($article['catid'], $aid, $article);
	} else {
		showmessage('article_not_exist', dreferer());
	}

} elseif($op == 'pushplus') {
	if($aid) {
		check_articleperm($article['catid'], $aid, $article);
	} else {
		showmessage('no_article_specified_for_pushplus', dreferer());
	}

	$pids = (array)$_POST['topiclist'];
	$tid = intval($_GET['tid']);
	$pushedids = array();
	$pushcount = $pushedcount = 0;
	if(!empty($pids)) {
		$query = DB::query('SELECT id FROM '.DB::table('portal_article_content')." WHERE aid='$aid' AND id IN (".dimplode($pids).")");
		while(($value=DB::fetch($query))) {
			$pushedids[] = intval($value['id']);
			$pushedcount++;
		}
		$pids = array_diff($pids, $pushedids);
	}
	$pushcount = count($pids);

	if(empty($pids)) {
		showmessage($pushedids ? 'all_posts_pushed_already' : 'no_posts_for_pushplus');
	}

} else {

	if(empty($_G['cache']['portalcategory'])) {
		showmessage('portal_has_not_category');
	}

	if(!checkperm('allowmanagearticle') && !checkperm('allowpostarticle')) {
		$allowcategorycache = array();
		if($allowcategory = getallowcategory($_G['uid'])) {
			foreach($allowcategory as $catid => $category) {
				$allowcategorycache[$catid] = $_G['cache']['portalcategory'][$catid];
			}
		}
		foreach($allowcategorycache as $_id => $_value) {
			if($_value['upid'] && !isset($allowcategorycache[$_value['upid']])) {
				$allowcategorycache[$_id]['level'] = 0;
			}
		}
		$_G['cache']['portalcategory'] = $allowcategorycache;
	}

	if(empty($_G['cache']['portalcategory'])) {
		showmessage('portal_article_add_nopermission');
	}

	$category = $_G['cache']['portalcategory'];
	$cate = $category[$catid];
	$categoryselect = category_showselect('portal', 'catid', false, !empty($article['catid']) ? $article['catid'] : $catid);

	if($aid) {
		$catid = intval($article['catid']);
	}

	if($aid && $article['highlight']) {
		$stylecheck = '';
		$stylecheck = explode('|', $article['highlight']);
	}

	$from_cookie_str = '';
	$from_cookie = array();
	$from_cookie_str = stripcslashes(getcookie('from_cookie'));
	$from_cookie = explode('\t', $from_cookie_str);
	$from_cookie = array_filter($from_cookie);

	$page = empty($_GET['page'])?1:intval($_GET['page']);
	if($page<1) $page = 1;
	$start = $page-1;

	$pageselect = '';

	if($article) {

		if($op == 'addpage') {
			$article_content = array();
		} else {
			$query = DB::query("SELECT * FROM ".DB::table('portal_article_content')." WHERE aid='$aid' ORDER BY pageorder LIMIT $start,1");
			$article_content = DB::fetch($query);
		}

		$article['attach_image'] = $article['attach_file'] = '';
		$query = DB::query("SELECT * FROM ".DB::table('portal_attachment')." WHERE aid='$aid' ORDER BY attachid DESC");
		while ($value = DB::fetch($query)) {
			if($value['isimage']) {
				if($article['pic']) {
					$value['pic'] = $article['pic'];
				}
				$article['attach_image'] .= get_uploadcontent($value);
			} else {
				$article['attach_file'] .= get_uploadcontent($value);
			}
		}
		if($article['idtype'] == 'tid') {
			$query = DB::query("SELECT * FROM ".DB::table(getattachtablebytid($article['id']))." WHERE tid='$article[id]'");
			while ($value = DB::fetch($query)) {
				if($value['isimage']) {
					if($article['pic']) {
						$value['pic'] = $article['pic'];
					}
					$value['attachid'] = $value['aid'];
					$article['attach_image'] .= get_uploadcontent($value, 'forum');
				} else {
					$article['attach_file'] .= get_uploadcontent($value, 'forum');
				}
			}
		}

		if($article['pic']) {
			$article['conver'] = addslashes(serialize(array('pic'=>$article['pic'], 'thumb'=>$article['thumb'], 'remote'=>$article['remote'])));
		}

		if($article['contents'] > 0) {
			$pageselect = '<select name="pageorder">';
			$pageselect .= "<option value=\"0\">".lang('core','end')."</option>";
			for($i=1; $i<=$article['contents']; $i++) {
				$selected = ($op!='addpage' && $page == $i)?' selected':'';
				$pageselect .= "<option value=\"$i\"$selected>$i</option>";
			}
			$pageselect .= '</select>';
		}

		$multi = multi($article['contents'], 1, $page, "portal.php?mod=portalcp&ac=article&aid=$aid");

		$article['related'] = array();
		if($page < 2 && $op != 'addpage') {
			$query = DB::query("SELECT a.aid,a.title
				FROM ".DB::table('portal_article_related')." r
				LEFT JOIN ".DB::table('portal_article_title')." a ON a.aid=r.raid
				WHERE r.aid='$aid' ORDER BY r.displayorder");
			while ($value = DB::fetch($query)) {
				$article['related'][] = $value;
			}
		}
	}

	$_GET['from_id'] = empty ($_GET['from_id'])?0:intval($_GET['from_id']);
	if($_GET['from_idtype'] != 'blogid') $_GET['from_idtype'] = 'tid';

	$idtypes = array($_GET['from_idtype'] => ' selected');
	if($_GET['from_idtype'] && $_GET['from_id']) {

		$havepush = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('portal_article_title')." WHERE id='$_GET[from_id]' AND idtype='$_GET[from_idtype]'"), 0);
		if($havepush) {
			if($_GET['from_idtype'] == 'blogid') {
				showmessage('article_push_blogid_invalid_repeat', '', array(), array('return'=>true));
			} else {
				showmessage('article_push_tid_invalid_repeat', '', array(), array('return'=>true));
			}
		}

		switch ($_GET['from_idtype']) {
		case 'blogid':
			$query = DB::query("SELECT b.*, bf.message FROM ".DB::table('home_blog')." b
				LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid
				WHERE b.blogid='$_GET[from_id]'");
			if($blog = DB::fetch($query)) {
				if($blog['friend']) {
					showmessage('article_push_invalid_private');
				}
				$article['title'] = getstr($blog['subject'], 0);
				$article['summary'] = portalcp_get_summary($blog['message']);
				$article['fromurl'] = 'home.php?mod=space&uid='.$blog[uid].'&do=blog&id='.$blog[blogid];
				$article['author'] = $blog['username'];
				$article_content['content'] = dhtmlspecialchars($blog['message']);
			}
			break;
		default:
			$posttable = getposttablebytid($_GET['from_id']);
			$query = DB::query("SELECT t.*, p.* FROM ".DB::table('forum_thread')." t
				LEFT JOIN ".DB::table($posttable)." p ON p.tid=t.tid AND p.first='1'
				WHERE t.tid='$_GET[from_id]'");
			if($thread = DB::fetch($query)) {
				$article['title'] = $thread['subject'];
				$thread['message'] = portalcp_get_postmessage($thread);
				$article['summary'] = portalcp_get_summary($thread['message']);
				$article['fromurl'] = 'forum.php?mod=viewthread&tid='.$thread['tid'];
				$article['author'] = $thread['author'];
				$article_content['content'] = dhtmlspecialchars($thread['message']);

				$article['attach_image'] = $article['attach_file'] = '';
				$query = DB::query("SELECT * FROM ".DB::table(getattachtablebytid($thread['tid']))." WHERE pid='$thread[pid]' ORDER BY aid DESC");
				while($attach = DB::fetch($query)) {
					$attachcode = '[attach]'.$attach['aid'].'[/attach]';
					if(!strexists($article_content['content'], $attachcode)) {
						$article_content['content'] .= '<br /><br />'.$attachcode;
					}
					if($attach['isimage']) {
						if($article['pic']) {
							$attach['pic'] = $article['pic'];
						}
						$article['attach_image'] .= get_uploadcontent($attach, 'forum');
					} else {
						$article['attach_file'] .= get_uploadcontent($attach, 'forum');
					}
				}
			}
			break;
		}
	}

	if(!empty($article['dateline'])) {
		$article['dateline'] = dgmdate($article['dateline']);
	}

	$article_tags = article_parse_tags($article['tag']);
	$tag_names = article_tagnames();
}

include_once template("portal/portalcp_article");

function portalcp_get_summary($message) {
	$message = preg_replace(array("/\[attach\].*?\[\/attach\]/", "/\&[a-z]+\;/i", "/\<script.*?\<\/script\>/"), '', $message);
	$message = preg_replace("/\[.*?\]/", '', $message);
	$message = getstr(strip_tags($message), 200);
	return $message;
}

function portalcp_get_postmessage($post) {
	global $_G;
	$forum = DB::fetch_first('SELECT * FROM '.DB::table('forum_forum')." WHERE fid='$post[fid]'");
	require_once libfile('function/discuzcode');
	$language = lang('forum/misc');
	if($forum['type'] == 'sub' && $forum['status'] == 3) {
		loadcache('grouplevels');
		$grouplevel = $_G['grouplevels'][$forum['level']];
		$group_postpolicy = $grouplevel['postpolicy'];
		if(is_array($group_postpolicy)) {
			$forum = array_merge($forum, $group_postpolicy);
		}
	}
	$post['message'] = preg_replace($language['post_edit_regexp'], '', $post['message']);
	return discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], $post['htmlon'] & 1, $forum['allowsmilies'], $forum['allowbbcode'], ($forum['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $forum['allowhtml'], 0, 0, $post['authorid'], $forum['allowmediacode'], $post['pid']);
}
?>