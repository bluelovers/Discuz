<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_search.php 11212 2010-05-26 06:46:38Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

lang('admincp_searchindex');

$searchindex = $_G['lang']['admincp_searchindex'];
$anchorindex = $_G['lang']['admincp_searchindex']['_anchorindex'];

if(!$searchindex) {
	cpmsg('undefined_action', '', 'error');
}

$keywords = trim($_G['gp_keywords']);
$kws = explode(' ', $keywords);
$kws = array_map('trim', $kws);
$keywords = implode(' ', $kws);

$result = array();

if($_G['gp_searchsubmit'] && $keywords) {
	foreach($searchindex as $script => $index) {
		foreach($index as $key => $value) {
			$matched = TRUE;
			foreach($kws as $kw) {
				if(strpos(strtolower($value), strtolower($kw)) === FALSE) {
					$matched = FALSE;
					break;
				}
			}
			if($matched) {
				$result[] = array($script, $value, $key);
			}
		}
	}
	if($result) {
		require './source/admincp/admincp_menu.php';
		$is = $results = $iss = array();
		$count = '';
		foreach($menu as $items) {
			foreach($items as $item) {
				list($ac, $op) = explode('_', $item[1]);
				if(!file_exists('./source/admincp/admincp_'.$ac.'.php')) {
					continue;
				}
				$preurl = ADMINSCRIPT.'?frames=yes&action='.$ac;
				if($op) {
					$preurl .= '&operation='.$op;
				}
				$is[$ac][] = array($item[0], $preurl.'&highlight='.rawurlencode($keywords));
				$iss[] = $item[1];
			}
		}
		foreach($result as $item) {
			if($is[$item[0]]) {
				list($ac, $op) = explode('_', $item[2]);
				if(!file_exists('./source/admincp/admincp_'.$ac.'.php')) {
					continue;
				}
				$preurl = ADMINSCRIPT.'?frames=yes&action='.$ac;
				if($op) {
					$preurl .= '&operation='.$op;
				}
				$anchor = !empty($anchorindex[$ac][$item[2]]) ? '&anchor='.$anchorindex[$ac][$item[2]] : '';
				$results[$item[0]] .= '<div class="news"><a href="'.$preurl.'&highlight='.rawurlencode($keywords).$anchor.'"  target="_blank">'.$item[1].'</a></div>';
				$count++;
			}
		}
		if($count) {
			showsubmenu('search_result', array(), cplang('search_result_find').' '.$count.' '.cplang('search_result_num'));
			echo implode('<br />', $results);
			hlkws($kws);
		} else {
			cpmsg('search_result_noexists', '', 'error');
		}
	} else {
		cpmsg('search_result_noexists', '', 'error');
	}
} else {
	cpmsg('search_keyword_noexists', '', 'error');
}

function hlkws($kws) {
echo <<<EOF
<script type="text/JavaScript">
document.body.onload = function () {
EOF;
foreach($kws as $kw) {
	echo 'parsetag(\''.$kw.'\');';
}
echo '}</script>';
}

?>