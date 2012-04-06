<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: search_album.php 22166 2011-04-25 02:03:44Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
define('NOROBOT', TRUE);

require_once libfile('function/home');

if(!$_G['setting']['search']['album']['status']) {
	showmessage('search_album_closed');
}

if($_G['adminid'] != 1 && !($_G['group']['allowsearch'] & 8)) {
	showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
}

$_G['setting']['search']['album']['searchctrl'] = intval($_G['setting']['search']['album']['searchctrl']);

$srchmod = 4;

$cachelife_time = 300;		// Life span for cache of searching in specified range of time
$cachelife_text = 3600;		// Life span for cache of text searching

$srchtype = empty($_G['gp_srchtype']) ? '' : trim($_G['gp_srchtype']);
$searchid = isset($_G['gp_searchid']) ? intval($_G['gp_searchid']) : 0;

$srchtxt = $_G['gp_srchtxt'];
$keyword = isset($srchtxt) ? htmlspecialchars(trim($srchtxt)) : '';

if(!submitcheck('searchsubmit', 1)) {

	include template('search/album');

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

		$albumlist = array();
		$maxalbum = $nowalbum = 0;
		$query = DB::query("SELECT * FROM ".DB::table('home_album')." WHERE albumid IN($index[ids]) ORDER BY updatetime DESC LIMIT $start_limit, $_G[tpp]");
		while ($value = DB::fetch($query)) {
			if($value['friend'] != 4 && ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
				$value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
			} elseif ($value['picnum']) {
				$value['pic'] = STATICURL.'image/common/nopublish.jpg';
			} else {
				$value['pic'] = '';
			}
			$value['albumname'] = bat_highlight($value['albumname'], $keyword);
			$albumlist[$value['albumid']] = $value;
		}

		$multipage = multi($index['num'], $_G['tpp'], $page, "search.php?mod=album&searchid=$searchid&orderby=$orderby&ascdesc=$ascdesc&searchsubmit=yes");

		$url_forward = 'search.php?mod=album&'.$_SERVER['QUERY_STRING'];

		include template('search/album');

	} else {

		$searchstring = 'album|title|'.addslashes($srchtxt);
		$searchindex = array('id' => 0, 'dateline' => '0');

		$query = DB::query("SELECT searchid, dateline,
			('".$_G['setting']['search']['album']['searchctrl']."'<>'0' AND ".(empty($_G['uid']) ? "useip='$_G[clientip]'" : "uid='$_G[uid]'")." AND $_G[timestamp]-dateline<'".$_G['setting']['search']['album']['searchctrl']."') AS flood,
			(searchstring='$searchstring' AND expiration>'$_G[timestamp]') AS indexvalid
			FROM ".DB::table('common_searchindex')."
			WHERE srchmod='$srchmod' AND ('".$_G['setting']['search']['album']['searchctrl']."'<>'0' AND ".(empty($_G['uid']) ? "useip='$_G[clientip]'" : "uid='$_G[uid]'")." AND $_G[timestamp]-dateline<".$_G['setting']['search']['album']['searchctrl'].") OR (searchstring='$searchstring' AND expiration>'$_G[timestamp]')
			ORDER BY flood");

		while($index = DB::fetch($query)) {
			if($index['indexvalid'] && $index['dateline'] > $searchindex['dateline']) {
				$searchindex = array('id' => $index['searchid'], 'dateline' => $index['dateline']);
				break;
			} elseif($_G['adminid'] != '1' && $index['flood']) {
				showmessage('search_ctrl', 'search.php?mod=album', array('searchctrl' => $_G['setting']['search']['album']['searchctrl']));
			}
		}

		if($searchindex['id']) {

			$searchid = $searchindex['id'];

		} else {

			!($_G['group']['exempt'] & 2) && checklowerlimit('search');

			if(!$srchtxt && !$srchuid && !$srchuname) {
				dheader('Location: search.php?mod=album');
			}

			if($_G['adminid'] != '1' && $_G['setting']['search']['album']['maxspm']) {
				if((DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_searchindex')." WHERE srchmod='$srchmod' AND dateline>'$_G[timestamp]'-60")) >= $_G['setting']['search']['album']['maxspm']) {
					showmessage('search_toomany', 'search.php?mod=album', array('maxspm' => $_G['setting']['search']['album']['maxspm']));
				}
			}

			$num = $ids = 0;
			$_G['setting']['search']['album']['maxsearchresults'] = $_G['setting']['search']['album']['maxsearchresults'] ? intval($_G['setting']['search']['album']['maxsearchresults']) : 500;
			list($srchtxt, $srchtxtsql) = searchkey($keyword, "albumname LIKE '%{text}%'", true);
			$query = DB::query("SELECT albumid FROM ".DB::table('home_album')." WHERE 1 $srchtxtsql ORDER BY albumid DESC LIMIT ".$_G['setting']['search']['album']['maxsearchresults']);
			while($album = DB::fetch($query)) {
				$ids .= ','.$album['albumid'];
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

		dheader("location: search.php?mod=album&searchid=$searchid&searchsubmit=yes&kw=".urlencode($keyword));

	}

}

?>