<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_blog.php 22166 2011-04-25 02:03:44Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

require_once libfile('function/home');

if(!$_G['setting']['search']['blog']['status']) {
	showmessage('search_blog_closed');
}

if($_G['adminid'] != 1 && !($_G['group']['allowsearch'] & 4)) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

$_G['setting']['search']['blog']['searchctrl'] = intval($_G['setting']['search']['blog']['searchctrl']);

$srchmod = 3;

$cachelife_time = 300;		// Life span for cache of searching in specified range of time
$cachelife_text = 3600;		// Life span for cache of text searching

$srchtype = empty($_G['gp_srchtype']) ? '' : trim($_G['gp_srchtype']);
$searchid = isset($_G['gp_searchid']) ? intval($_G['gp_searchid']) : 0;


$srchtxt = $_G['gp_srchtxt'];

$keyword = isset($srchtxt) ? htmlspecialchars(trim($srchtxt)) : '';

if(!submitcheck('searchsubmit', 1)) {

	include template('search/blog');

} else {

	$orderby = in_array($_G['gp_orderby'], array('dateline', 'replies', 'views')) ? $_G['gp_orderby'] : 'lastpost';
	$ascdesc = isset($_G['gp_ascdesc']) && $_G['gp_ascdesc'] == 'asc' ? 'asc' : 'desc';

	if(!empty($searchid)) {

		$page = max(1, intval($_G['gp_page']));
		$start_limit = ($page - 1) * $_G['tpp'];

		$index = DB::fetch_first("SELECT searchstring, keywords, num, ids FROM ".DB::table('common_searchindex')." WHERE searchid='$searchid' AND srchmod='$srchmod'");
		if(!$index) {
			showmessage('search_id_invalid');
		}

		$keyword = htmlspecialchars($index['keywords']);
		$keyword = $keyword != '' ? str_replace('+', ' ', $keyword) : '';

		$index['keywords'] = rawurlencode($index['keywords']);
		$bloglist = array();
		$pricount = 0;
		$query = DB::query("SELECT b.*,bf.pic, bf.message FROM ".DB::table('home_blog')." b LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid WHERE b.blogid IN($index[ids]) ORDER BY b.blogid DESC LIMIT $start_limit, $_G[tpp]");
		while ($value = DB::fetch($query)) {
			if(ckfriend($value['uid'], $value['friend'], $value['target_ids']) && ($value['status'] == 0 || $value['uid'] == $_G['uid'] || $_G['adminid'] == 1)) {
				if($value['friend'] == 4) {
					$value['message'] = $value['pic'] = '';
				} else {
					$value['message'] = bat_highlight($value['message'], $keyword);
					$value['message'] = getstr($value['message'], 255, 0, 0, 0, -1);
				}
				$value['subject'] = bat_highlight($value['subject'], $keyword);
				$value['dateline'] = dgmdate($value['dateline']);
				$value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
				$bloglist[] = $value;
			} else {
				$pricount++;
			}
		}
		$multipage = multi($index['num'], $_G['tpp'], $page, "search.php?mod=blog&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

		$url_forward = 'search.php?mod=blog&'.$_SERVER['QUERY_STRING'];

		include template('search/blog');

	} else {

		$searchstring = 'blog|title|'.addslashes($srchtxt);
		$searchindex = array('id' => 0, 'dateline' => '0');

		$query = DB::query("SELECT searchid, dateline,
			('".$_G['setting']['search']['blog']['searchctrl']."'<>'0' AND ".(empty($_G['uid']) ? "useip='$_G[clientip]'" : "uid='$_G[uid]'")." AND $_G[timestamp]-dateline<'".$_G['setting']['search']['blog']['searchctrl']."') AS flood,
			(searchstring='$searchstring' AND expiration>'$_G[timestamp]') AS indexvalid
			FROM ".DB::table('common_searchindex')."
			WHERE srchmod='$srchmod' AND ('".$_G['setting']['search']['blog']['searchctrl']."'<>'0' AND ".(empty($_G['uid']) ? "useip='$_G[clientip]'" : "uid='$_G[uid]'")." AND $_G[timestamp]-dateline<".$_G['setting']['search']['blog']['searchctrl'].") OR (searchstring='$searchstring' AND expiration>'$_G[timestamp]')
			ORDER BY flood");

		while($index = DB::fetch($query)) {
			if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
				$searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
				break;
			} elseif($_G['adminid'] != '1' && $index['flood']) {
				showmessage('search_ctrl', 'search.php?mod=blog', array('searchctrl' => $_G['setting']['search']['blog']['searchctrl']));
			}
		}

		if($searchindex['id']) {

			$searchid = $searchindex['id'];

		} else {

			!($_G['group']['exempt'] & 2) && checklowerlimit('search');

			if(!$srchtxt && !$srchuid && !$srchuname) {
				dheader('Location: search.php?mod=blog');
			}

			if($_G['adminid'] != '1' && $_G['setting']['search']['blog']['maxspm']) {
				if((DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_searchindex')." WHERE srchmod='$srchmod' AND dateline>'$_G[timestamp]'-60")) >= $_G['setting']['search']['blog']['maxspm']) {
					showmessage('search_toomany', 'search.php?mod=blog', array('maxspm' => $_G['setting']['search']['blog']['maxspm']));
				}
			}

			$num = $ids = 0;
			$_G['setting']['search']['blog']['maxsearchresults'] = $_G['setting']['search']['blog']['maxsearchresults'] ? intval($_G['setting']['search']['blog']['maxsearchresults']) : 500;
			list($srchtxt, $srchtxtsql) = searchkey($keyword, "subject LIKE '%{text}%'", true);
			$query = DB::query("SELECT blogid FROM ".DB::table('home_blog')." WHERE 1 $srchtxtsql ORDER BY blogid DESC LIMIT ".$_G['setting']['search']['blog']['maxsearchresults']);
			while($blog = DB::fetch($query)) {
				$ids .= ','.$blog['blogid'];
				$num++;
			}
			DB::free_result($query);

			$keywords = str_replace('%', '+', $srchtxt);
			$expiration = TIMESTAMP + $cachelife_text;

			DB::query("INSERT INTO ".DB::table('common_searchindex')." (srchmod, keywords, searchstring, useip, uid, dateline, expiration, num, ids)
					VALUES ('$srchmod', '$keywords', '$searchstring', '$_G[clientip]', '$_G[uid]', '$_G[timestamp]', '$expiration', '$num', '$ids')");
			$searchid = DB::insert_id();

			!($_G['group']['exempt'] & 2) && updatecreditbyaction('search');
		}

		dheader("location: search.php?mod=blog&searchid=$searchid&searchsubmit=yes&kw=".urlencode($keyword));

	}

}

?>