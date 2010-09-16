<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_faq.php 13682 2010-07-29 08:37:44Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$keyword = isset($_G['gp_keyword']) ? dhtmlspecialchars($_G['gp_keyword']) : '';

$faqparent = $faqsub = array();
$query = DB::query("SELECT id, fpid, title FROM ".DB::table('forum_faq')." ORDER BY displayorder");
while($faq = DB::fetch($query)) {
	if(empty($faq['fpid'])) {
		$faqparent[$faq['id']] = $faq;
		if($_G['gp_id'] == $faq['id']) {
			$ctitle = $faq['title'];
		}
	} else {
		$faqsub[$faq['fpid']][] = $faq;
	}
}

if($_G['gp_action'] == 'faq') {

	$id = intval($_G['gp_id']);
	if($ffaq = DB::fetch_first("SELECT title FROM ".DB::table('forum_faq')." WHERE fpid='$id'")) {

		$navigation = "<em>&rsaquo;</em> $ctitle";
		$faqlist = array();
		$messageid = empty($_G['gp_messageid']) ? 0 : $_G['gp_messageid'];
		$query = DB::query("SELECT id,title,message FROM ".DB::table('forum_faq')." WHERE fpid='$id' ORDER BY displayorder");
		while($faq = DB::fetch($query)) {
			if(!$messageid) {
				$messageid = $faq['id'];
			}
			$faqlist[] = $faq;
		}

	} else {
		showmessage("faq_content_empty", 'misc.php?mod=faq');
	}

} elseif($_G['gp_action'] == 'search') {
	if(submitcheck('searchsubmit')) {
		$keyword = isset($_G['gp_keyword']) ? trim($_G['gp_keyword']) : '';
		if($keyword) {
			$sqlsrch = '';
			$searchtype = in_array($_G['gp_searchtype'], array('all', 'title', 'message')) ? $_G['gp_searchtype'] : 'all';
			switch($searchtype) {
				case 'all':
					$sqlsrch = "WHERE title LIKE '%$keyword%' OR message LIKE '%$keyword%'";
					break;
				case 'title':
					$sqlsrch = "WHERE title LIKE '%$keyword%'";
					break;
				case 'message':
					$sqlsrch = "WHERE message LIKE '%$keyword%'";
					break;
			}

			$keyword = dstripslashes($keyword);
			$faqlist = array();
			$query = DB::query("SELECT fpid, title, message FROM ".DB::table('forum_faq')." $sqlsrch ORDER BY displayorder");
			while($faq = DB::fetch($query)) {
				if(!empty($faq['fpid'])) {
					$faq['title'] = preg_replace("/(?<=[\s\"\]>()]|[\x7f-\xff]|^)(".preg_quote($keyword, '/').")(([.,:;-?!()\s\"<\[]|[\x7f-\xff]|$))/siU", "<u><b><font color=\"#FF0000\">\\1</font></b></u>\\2", dstripslashes($faq['title']));
					$faq['message'] = preg_replace("/(?<=[\s\"\]>()]|[\x7f-\xff]|^)(".preg_quote($keyword, '/').")(([.,:;-?!()\s\"<\[]|[\x7f-\xff]|$))/siU", "<u><b><font color=\"#FF0000\">\\1</font></b></u>\\2", dstripslashes($faq['message']));
					$faqlist[] = $faq;
				}
			}
		} else {
			showmessage('faq_keywords_empty', 'misc.php?mod=faq');
		}
	}

} elseif($_G['gp_action'] == 'plugin' && !empty($_G['gp_id'])) {

	$navtitle = $_G['setting']['plugins']['faq'][$_G['gp_id']]['name'];
	$navigation = '<em>&rsaquo;</em> '.$_G['setting']['plugins']['faq'][$_G['gp_id']]['name'];
	include pluginmodule($_G['gp_id'], 'faq');

}

include template('common/faq');

?>