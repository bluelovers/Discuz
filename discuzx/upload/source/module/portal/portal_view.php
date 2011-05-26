<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_view.php 21605 2011-04-02 03:22:50Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$aid = empty($_GET['aid'])?0:intval($_GET['aid']);
if(empty($aid)) {
	showmessage('view_no_article_id');
}
$article = DB::fetch_first("SELECT * FROM ".DB::table('portal_article_title')." WHERE aid='$aid'");
require_once libfile('function/portalcp');
$permission = getallowcategory($_G['uid']);

if(empty($article) || ($article['status'] > 0 && $article['uid'] != $_G['uid'] && !$_G['group']['allowmanagearticle'] && empty($permission[$article['catid']]['allowmanage']) && $_G['adminid'] != 1 && $_G['gp_modarticlekey'] != modauthkey($article['aid']))) {
	showmessage('view_article_no_exist');
}

$article_count = DB::fetch_first("SELECT * FROM ".DB::table('portal_article_count')." WHERE aid='$aid'");
if($article_count) $article = array_merge($article_count, $article);

if($article_count) {
	DB::query("UPDATE ".DB::table('portal_article_count')." SET catid='$article[catid]', dateline='$article[dateline]', viewnum=viewnum+1 WHERE aid='$aid'");
} else {
	DB::insert('portal_article_count', array(
		'aid'=>$aid,
		'catid'=>$article['catid'],
		'dateline'=>$article['dateline'],
		'viewnum'=>1));
}

if($article['url']) {
	dheader("location:{$article['url']}");
	exit();
}


$cat = category_remake($article['catid']);

$article['pic'] = pic_get($article['pic'], 'portal', $article['thumb'], $article['remote']);

$page = intval($_GET['page']);
if($page<1) $page = 1;
$start = $page-1;

$content = $contents = array();
$multi = '';

$query = DB::query("SELECT * FROM ".DB::table('portal_article_content')." WHERE aid='$aid' ORDER BY pageorder LIMIT $start,1");
$content = DB::fetch($query);

if($article['contents'] && $article['showinnernav']) {
	$query = DB::query("SELECT title FROM ".DB::table('portal_article_content')." WHERE aid='$aid' ORDER BY pageorder");
	while ($value = DB::fetch($query)) {
		$contents[] = $value;
	}
	if(empty($contents)) {
		DB::update('portal_article_title', array('showinnernav' => '0'), "aid ='$aid'");
	}
}

require_once libfile('function/blog');
$content['content'] = blog_bbcode($content['content']);

$multi = multi($article['contents'], 1, $page, "portal.php?mod=view&aid=$aid");
$org = array();
if($article['idtype'] == 'tid' || $content['idtype']=='pid') {
	$thread = $firstpost = array();
	require_once libfile('function/discuzcode');
	require_once libfile('function/forum');
	$thread = get_thread_by_tid($article[id]);
	if(!empty($thread)) {
		$wherer = $content['idtype']=='pid' ? "p.pid='$content[id]' AND p.tid='$article[id]'" : "p.tid='$article[id]' AND p.first='1'";
		$firstpost = DB::fetch_first("SELECT p.first, p.authorid AS uid, p.author AS username, p.dateline, p.message, p.smileyoff, p.bbcodeoff, p.htmlon, p.attachment, p.pid FROM ".DB::table($thread['posttable'])." p WHERE $wherer");
	}
	if(!empty($firstpost) && !empty($thread) && $thread['displayorder'] != -1) {
		$_G['tid'] = $article['id'];
		$attachpids = -1;
		$attachtags = $aimgs = array();
		$firstpost['message'] = $content['content'];
		if($firstpost['attachment']) {
			if($_G['group']['allowgetattach'] || $_G['group']['allowgetimage']) {
				$attachpids .= ",$firstpost[pid]";
				if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $firstpost['message'], $matchaids)) {
					$attachtags[$firstpost['pid']] = $matchaids[1];
				}
			} else {
				$firstpost['message'] = preg_replace("/\[attach\](\d+)\[\/attach\]/i", '', $firstpost['message']);
			}
		}

		$post = array();
		$post[$firstpost['pid']] = $firstpost;
		if($attachpids != '-1') {
			require_once libfile('function/attachment');
			parseattach($attachpids, $attachtags, $post);
		}

		$content['content'] = $post[$firstpost['pid']]['message'];
		$content['pid'] = $firstpost['pid'];
		unset($post);

		$org = $firstpost;
		$org_url = "forum.php?mod=viewthread&tid=$article[id]";
	} else {
		DB::update('portal_article_title', array('id' => 0, 'idtype' => ''), array('aid' => $aid));
		DB::update('portal_article_content', array('id' => 0, 'idtype' => ''), array('aid' => $aid));
	}
} elseif($article['idtype']=='blogid') {
	$org = DB::fetch_first("SELECT * FROM ".DB::table('home_blog')." WHERE blogid='$article[id]'");
	if(empty($org)) {
		DB::update('portal_article_title', array('id'=>'0', 'idtype'=>''),array('aid'=>$aid));
		dheader('location: portal.php?mod=view&aid='.$aid);
		exit();
	}
}

$article['related'] = array();
$query = DB::query("SELECT a.aid,a.title
	FROM ".DB::table('portal_article_related')." r
	LEFT JOIN ".DB::table('portal_article_title')." a ON a.aid=r.raid
	WHERE r.aid='$aid' ORDER BY r.displayorder");
while ($value = DB::fetch($query)) {
	$article['related'][] = $value;
}

$article['allowcomment'] = !empty($cat['allowcomment']) && !empty($article['allowcomment']) ? 1 : 0;
$_G['catid'] = $_GET['catid'] = $article['catid'];
$common_url = '';
$commentlist = array();
if($article['allowcomment']) {

	if($org && empty($article['owncomment'])) {

		if($article['idtype'] == 'blogid') {

			$common_url = "home.php?mod=space&uid=$org[uid]&do=blog&id=$article[id]";
			$form_url = "home.php?mod=spacecp&ac=comment";

			$article['commentnum'] = getcount('home_comment', array('id'=>$article['id'], 'idtype'=>'blogid'));
			if($article['commentnum']) {
				$query = DB::query("SELECT authorid AS uid, author AS username, dateline, message
					FROM ".DB::table('home_comment')." WHERE id='$article[id]' AND idtype='blogid' ORDER BY dateline DESC LIMIT 0,20");
				while ($value = DB::fetch($query)) {
					if($value['status'] == 0 || $_G['adminid'] == 1 || $value['uid'] == $_G['uid']) {
						$commentlist[] = $value;
					}
				}
			}

		} elseif($article['idtype'] == 'tid') {

			$common_url = "forum.php?mod=viewthread&tid=$article[id]";
			$form_url = "forum.php?mod=post&action=reply&tid=$article[id]&replysubmit=yes&infloat=yes&handlekey=fastpost";

			require_once libfile('function/discuzcode');
			$posttable = empty($thread['posttable']) ? getposttablebytid($article['id']) : $thread['posttable'];
			$_G['tid'] = $article['id'];
			$article['commentnum'] = getcount($posttable, array('tid'=>$article['id'], 'first'=>'0'));

			if($article['allowcomment'] && $article['commentnum']) {
				$query = DB::query("SELECT pid, first, authorid AS uid, author AS username, dateline, message, smileyoff, bbcodeoff, htmlon, attachment, status
					FROM ".DB::table($posttable)." WHERE tid='$article[id]' AND invisible='0' ORDER BY dateline DESC LIMIT 0,20");
				$attachpids = -1;
				$attachtags = array();
				$_G['group']['allowgetattach'] = $_G['group']['allowgetimage'] = 1;
				while ($value = DB::fetch($query)) {
					if(!($value['status'] & 1) && !$value['first']) {
						$value['message'] = discuzcode($value['message'], $value['smileyoff'], $value['bbcodeoff'], $value['htmlon']);
						$value['cid'] = $value['pid'];
						$commentlist[$value['pid']] = $value;
						if($value['attachment']) {
							$attachpids .= ",$value[pid]";
							if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $value['message'], $matchaids)) {
								$attachtags[$value['pid']] = $matchaids[1];
							}
						}
					}
				}

				if($attachpids != '-1') {
					require_once libfile('function/attachment');
					parseattach($attachpids, $attachtags, $commentlist);
				}
			}
		}

	} else {

		$common_url = "portal.php?mod=comment&id=$aid&idtype=aid";
		$form_url = "portal.php?mod=portalcp&ac=comment";

		$query = DB::query("SELECT * FROM ".DB::table('portal_comment')." WHERE id='$aid' AND idtype='aid' ORDER BY dateline DESC LIMIT 0,20");
		$pricount = 0;
		while ($value = DB::fetch($query)) {
			if($value['status'] == 0 || $value['uid'] == $_G['uid'] || $_G['adminid'] == 1) {
				$value['allowop'] = 1;
				$commentlist[] = $value;
			} else {
				$pricount += 1;
			}
		}
	}
}

$hash = md5($article['uid']."\t".$article['dateline']);
$id = $article['aid'];
$idtype = 'aid';

loadcache('click');
$clicks = empty($_G['cache']['click']['aid'])?array():$_G['cache']['click']['aid'];
$maxclicknum = 0;
foreach ($clicks as $key => $value) {
	$value['clicknum'] = $article["click{$key}"];
	$value['classid'] = mt_rand(1, 4);
	if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];
	$clicks[$key] = $value;
}

$clickuserlist = array();
$query = DB::query("SELECT * FROM ".DB::table('home_clickuser')."
	WHERE id='$id' AND idtype='$idtype'
	ORDER BY dateline DESC
	LIMIT 0,24");
while ($value = DB::fetch($query)) {
	$value['clickname'] = $clicks[$value['clickid']]['name'];
	$clickuserlist[] = $value;
}

$article['dateline'] = dgmdate($article['dateline']);

foreach($cat['ups'] as $val) {
	$cats[] = $val['catname'];
}
$seodata = array('firstcat' => $cats[0], 'secondcat' => $cats[1], 'curcat' => $cat['catname'], 'subject' => $article['title'], 'user' => $article['username'], 'summary' => $article['summary'], 'page' => intval($_G['gp_page']));
list($navtitle, $metadescription, $metakeywords) = get_seosetting('article', $seodata);
if(empty($navtitle)) {
	$navtitle = get_title_page($article['title'], $_G['page']).' - '.$cat['catname'];
	$nobbname = false;
} else {
	$nobbname = true;
}
if(empty($metakeywords)) {
	$metakeywords = $article['title'];
}
if(empty($metadescription)) {
	$metadescription = $article['summary'] ? $article['summary'] : $article['title'];
}

$seccodecheck = $_G['group']['seccode'] ? $_G['setting']['seccodestatus'] & 4 : 0;
$secqaacheck = $_G['group']['seccode'] ? $_G['setting']['secqaa']['status'] & 2 : 0;

$catid = $article['catid'];
$_G['relatedlinks'] = getrelatedlink('article');
include_once template("diy:portal/view:{$catid}", NULL, NULL, NULL, $cat['articleprimaltplname']);
?>