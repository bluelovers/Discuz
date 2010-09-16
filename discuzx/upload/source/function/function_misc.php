<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_misc.php 16293 2010-09-02 10:34:18Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function convertip($ip) {

	$return = '';

	if(preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {

		$iparray = explode('.', $ip);

		if($iparray[0] == 10 || $iparray[0] == 127 || ($iparray[0] == 192 && $iparray[1] == 168) || ($iparray[0] == 172 && ($iparray[1] >= 16 && $iparray[1] <= 31))) {
			$return = '- LAN';
		} elseif($iparray[0] > 255 || $iparray[1] > 255 || $iparray[2] > 255 || $iparray[3] > 255) {
			$return = '- Invalid IP Address';
		} else {
			$tinyipfile = DISCUZ_ROOT.'./data/ipdata/tinyipdata.dat';
			$fullipfile = DISCUZ_ROOT.'./data/ipdata/wry.dat';
			if(@file_exists($tinyipfile)) {
				$return = convertip_tiny($ip, $tinyipfile);
			} elseif(@file_exists($fullipfile)) {
				$return = convertip_full($ip, $fullipfile);
			}
		}
	}

	return $return;

}

function convertip_tiny($ip, $ipdatafile) {

	static $fp = NULL, $offset = array(), $index = NULL;

	$ipdot = explode('.', $ip);
	$ip    = pack('N', ip2long($ip));

	$ipdot[0] = (int)$ipdot[0];
	$ipdot[1] = (int)$ipdot[1];

	if($fp === NULL && $fp = @fopen($ipdatafile, 'rb')) {
		$offset = @unpack('Nlen', @fread($fp, 4));
		$index  = @fread($fp, $offset['len'] - 4);
	} elseif($fp == FALSE) {
		return  '- Invalid IP data file';
	}

	$length = $offset['len'] - 1028;
	$start  = @unpack('Vlen', $index[$ipdot[0] * 4] . $index[$ipdot[0] * 4 + 1] . $index[$ipdot[0] * 4 + 2] . $index[$ipdot[0] * 4 + 3]);

	for ($start = $start['len'] * 8 + 1024; $start < $length; $start += 8) {

		if ($index{$start} . $index{$start + 1} . $index{$start + 2} . $index{$start + 3} >= $ip) {
			$index_offset = @unpack('Vlen', $index{$start + 4} . $index{$start + 5} . $index{$start + 6} . "\x0");
			$index_length = @unpack('Clen', $index{$start + 7});
			break;
		}
	}

	@fseek($fp, $offset['len'] + $index_offset['len'] - 1024);
	if($index_length['len']) {
		return '- '.@fread($fp, $index_length['len']);
	} else {
		return '- Unknown';
	}

}

function convertip_full($ip, $ipdatafile) {

	if(!$fd = @fopen($ipdatafile, 'rb')) {
		return '- Invalid IP data file';
	}

	$ip = explode('.', $ip);
	$ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

	if(!($DataBegin = fread($fd, 4)) || !($DataEnd = fread($fd, 4)) ) return;
	@$ipbegin = implode('', unpack('L', $DataBegin));
	if($ipbegin < 0) $ipbegin += pow(2, 32);
	@$ipend = implode('', unpack('L', $DataEnd));
	if($ipend < 0) $ipend += pow(2, 32);
	$ipAllNum = ($ipend - $ipbegin) / 7 + 1;

	$BeginNum = $ip2num = $ip1num = 0;
	$ipAddr1 = $ipAddr2 = '';
	$EndNum = $ipAllNum;

	while($ip1num > $ipNum || $ip2num < $ipNum) {
		$Middle= intval(($EndNum + $BeginNum) / 2);

		fseek($fd, $ipbegin + 7 * $Middle);
		$ipData1 = fread($fd, 4);
		if(strlen($ipData1) < 4) {
			fclose($fd);
			return '- System Error';
		}
		$ip1num = implode('', unpack('L', $ipData1));
		if($ip1num < 0) $ip1num += pow(2, 32);

		if($ip1num > $ipNum) {
			$EndNum = $Middle;
			continue;
		}

		$DataSeek = fread($fd, 3);
		if(strlen($DataSeek) < 3) {
			fclose($fd);
			return '- System Error';
		}
		$DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
		fseek($fd, $DataSeek);
		$ipData2 = fread($fd, 4);
		if(strlen($ipData2) < 4) {
			fclose($fd);
			return '- System Error';
		}
		$ip2num = implode('', unpack('L', $ipData2));
		if($ip2num < 0) $ip2num += pow(2, 32);

		if($ip2num < $ipNum) {
			if($Middle == $BeginNum) {
				fclose($fd);
				return '- Unknown';
			}
			$BeginNum = $Middle;
		}
	}

	$ipFlag = fread($fd, 1);
	if($ipFlag == chr(1)) {
		$ipSeek = fread($fd, 3);
		if(strlen($ipSeek) < 3) {
			fclose($fd);
			return '- System Error';
		}
		$ipSeek = implode('', unpack('L', $ipSeek.chr(0)));
		fseek($fd, $ipSeek);
		$ipFlag = fread($fd, 1);
	}

	if($ipFlag == chr(2)) {
		$AddrSeek = fread($fd, 3);
		if(strlen($AddrSeek) < 3) {
			fclose($fd);
			return '- System Error';
		}
		$ipFlag = fread($fd, 1);
		if($ipFlag == chr(2)) {
			$AddrSeek2 = fread($fd, 3);
			if(strlen($AddrSeek2) < 3) {
				fclose($fd);
				return '- System Error';
			}
			$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
			fseek($fd, $AddrSeek2);
		} else {
			fseek($fd, -1, SEEK_CUR);
		}

		while(($char = fread($fd, 1)) != chr(0))
		$ipAddr2 .= $char;

		$AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
		fseek($fd, $AddrSeek);

		while(($char = fread($fd, 1)) != chr(0))
		$ipAddr1 .= $char;
	} else {
		fseek($fd, -1, SEEK_CUR);
		while(($char = fread($fd, 1)) != chr(0))
		$ipAddr1 .= $char;

		$ipFlag = fread($fd, 1);
		if($ipFlag == chr(2)) {
			$AddrSeek2 = fread($fd, 3);
			if(strlen($AddrSeek2) < 3) {
				fclose($fd);
				return '- System Error';
			}
			$AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
			fseek($fd, $AddrSeek2);
		} else {
			fseek($fd, -1, SEEK_CUR);
		}
		while(($char = fread($fd, 1)) != chr(0))
		$ipAddr2 .= $char;
	}
	fclose($fd);

	if(preg_match('/http/i', $ipAddr2)) {
		$ipAddr2 = '';
	}
	$ipaddr = "$ipAddr1 $ipAddr2";
	$ipaddr = preg_replace('/CZ88\.NET/is', '', $ipaddr);
	$ipaddr = preg_replace('/^\s*/is', '', $ipaddr);
	$ipaddr = preg_replace('/\s*$/is', '', $ipaddr);
	if(preg_match('/http/i', $ipaddr) || $ipaddr == '') {
		$ipaddr = '- Unknown';
	}

	return '- '.$ipaddr;

}

function procthread($thread, $timeformat = 'd') {
	global $_G;

	$lastvisit = $_G['member']['lastvisit'];
	loadcache('icons');
	if(empty($_G['forum_colorarray'])) {
		$_G['forum_colorarray'] = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');
	}

	if($thread['closed']) {
		$thread['new'] = 0;
		if($thread['isgroup'] && $thread['closed'] > 1) {
			$thread['folder'] = 'common';
		} else {
			$thread['folder'] = 'lock';
		}
	} else {
		$thread['folder'] = 'common';
		if($lastvisit < $thread['lastpost'] && (empty($_G['cookie']['oldtopics']) || strpos($_G['cookie']['oldtopics'], 'D'.$thread['tid'].'D') === FALSE)) {
			$thread['new'] = 1;
			$thread['folder'] = 'new';
		} else {
			$thread['new'] = 0;
		}
	}

	$thread['icon'] = '';
	$thread['id'] = random(6, 1);
	if(!$thread['forumname']) {
		$thread['forumname'] = empty($_G['cache']['forums'][$thread['fid']]['name']) ? 'Forum' : $_G['cache']['forums'][$thread['fid']]['name'];
	}
	$thread['dateline'] = dgmdate($thread['dateline'], $timeformat);
	$thread['lastpost'] = dgmdate($thread['lastpost'], 'u');
	$thread['lastposterenc'] = rawurlencode($thread['lastposter']);

	if($thread['replies'] > $thread['views']) {
		$thread['views'] = $thread['replies'];
	}

	$postsnum = $thread['special'] ? $thread['replies'] : $thread['replies'] + 1;
	$pagelinks = '';
	if($postsnum  > $_G['ppp']) {
		$posts = $postsnum;
		$topicpages = ceil($posts / $_G['ppp']);
		for($i = 1; $i <= $topicpages; $i++) {
			$pagelinks .= '<a href="forum.php?mod=viewthread&tid='.$thread['tid'].'&page='.$i.($_G['gp_from'] ? '&from='.$_G['gp_from'] : '').'" target="_blank">'.$i.'</a> ';
			if($i == 6) {
				$i = $topicpages + 1;
			}
		}
		if($topicpages > 6) {
			$pagelinks .= ' .. <a href="forum.php?mod=viewthread&tid='.$thread['tid'].'&page='.$topicpages.'" target="_blank">'.$topicpages.'</a> ';
		}
		$thread['multipage'] = '... '.$pagelinks;
	} else {
		$thread['multipage'] = '';
	}

	if($thread['highlight']) {
		$string = sprintf('%02d', $thread['highlight']);
		$stylestr = sprintf('%03b', $string[0]);

		$thread['highlight'] = 'style="';
		$thread['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
		$thread['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
		$thread['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
		$thread['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]] : '';
		$thread['highlight'] .= '"';
	} else {
		$thread['highlight'] = '';
	}

	return $thread;
}

function updateviews($table, $idcol, $viewscol, $logfile) {
	$viewlog = $viewarray = array();
	if(@$viewlog = file($logfile = DISCUZ_ROOT.$logfile)) {
		if(!@unlink($logfile)) {
			if($fp = @fopen($logfile, 'w')) {
				fwrite($fp, '');
				fclose($fp);
			}
		}

		$viewlog = array_count_values($viewlog);
		foreach($viewlog as $id => $views) {
			$viewarray[$views] .= ($id > 0) ? ','.intval($id) : '';
		}
		foreach($viewarray as $views => $ids) {
			DB::query("UPDATE LOW_PRIORITY ".DB::table($table)." SET $viewscol=$viewscol+'$views' WHERE $idcol IN (0$ids)", 'UNBUFFERED');
		}
	}
}

function modlog($thread, $action) {
	global $_G;
	$reason = $_G['gp_reason'];
	writelog('modslog', dhtmlspecialchars("$_G[timestamp]\t$_G[username]\t$_G[adminid]\t$_G[clientip]\t".$_G['forum']['fid']."\t".$_G['forum']['name']."\t$thread[tid]\t$thread[subject]\t$action\t$reason"));
}

function checkreasonpm() {
	global $_G;
	$reason = trim(strip_tags($_G['gp_reason']));
	if(($_G['group']['reasonpm'] == 1 || $_G['group']['reasonpm'] == 3) && !$reason) {
		showmessage('admin_reason_invalid');
	}
	return $reason;
}

function procreportlog($tids = '', $pids = '', $del = FALSE) {
	return false;
	global $_G;

	if(!$pids && $tids) {
		$pids = $comma = '';
		$postarray = getfieldsofposts('pid', "tid IN ($tids)".($del ? '' : ' AND first=1'));
		foreach($postarray as $post) {
			$pids .= $comma.$post['pid'];
			$comma = ',';
		}
	}
	if($pids) {
		if($del) {
			DB::query("DELETE FROM ".DB::table('forum_report')." WHERE pid IN ($pids)", 'UNBUFFERED');
		} else {
			DB::query("UPDATE ".DB::table('forum_report')." SET status=0 WHERE pid IN ($pids)", 'UNBUFFERED');
		}
		if($_G['forum']['modworks'] && !DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_report')." WHERE fid='$_G[fid]' AND status=1")) {
			DB::query("UPDATE ".DB::table('forum_forum')." SET modworks='0' WHERE fid='$_G[fid]'", 'UNBUFFERED');
		}
	}

}

function sendreasonpm($var, $item, $notevar) {
	global $_G;
	if(!empty($var['authorid']) && $var['authorid'] != $_G['uid']) {
		if(!empty($notevar['modaction'])) {
			$notevar['modaction'] = lang('forum/modaction', $notevar['modaction']);
		}
		notification_add($var['authorid'], 'system', $item, $notevar, 1);
	}
}

function modreasonselect($isadmincp = 0) {
	global $_G;
	if(!isset($_G['cache']['modreasons']) || !is_array($_G['cache']['modreasons'])) {
		loadcache(array('modreasons', 'stamptypeid'));
	}
	$select = '';
	foreach($_G['cache']['modreasons'] as $reason) {
		$select .= !$isadmincp ? ($reason ? '<li>'.$reason.'</li>' : '<li></li>') : ($reason ? '<option value="'.htmlspecialchars($reason).'">'.$reason.'</option>' : '<option></option>');
	}
	return $select;
}



function acpmsg($message, $url = '', $type = '', $extra = '') {
	if(defined('IN_ADMINCP')) {
		!defined('CPHEADER_SHOWN') && cpheader();
		cpmsg($message, $url, $type, $extra);
	} else {
		showmessage($message, $url, $extra);
	}
}

function savebanlog($username, $origgroupid, $newgroupid, $expiration, $reason) {
	global $_G;
	writelog('banlog', dhtmlspecialchars("$_G[timestamp]\t{$_G[member][username]}\t$_G[groupid]\t$_G[clientip]\t$username\t$origgroupid\t$newgroupid\t$expiration\t$reason"));
}

function clearlogstring($str) {
	if(!empty($str)) {
		if(!is_array($str)) {
			$str = dhtmlspecialchars(trim($str));
			$str = str_replace(array("\t", "\r\n", "\n", "   ", "  "), ' ', $str);
		} else {
			foreach ($str as $key => $val) {
				$str[$key] = clearlogstring($val);
			}
		}
	}
	return $str;
}

function implodearray($array, $skip = array()) {
	$return = '';
	if(is_array($array) && !empty($array)) {
		foreach ($array as $key => $value) {
			if(empty($skip) || !in_array($key, $skip)) {
				if(is_array($value)) {
					$return .= "$key={".implodearray($value, $skip)."}; ";
				} else {
					$return .= "$key=$value; ";
				}
			}
		}
	}
	return $return;
}

function deletethreads($tids = array()) {
	global $_G;

	static $cleartable = array(
		'forum_threadmod', 'forum_relatedthread', 'forum_post', 'forum_poll',
		'forum_polloption', 'forum_trade', 'forum_activity', 'forum_activityapply', 'forum_debate',
		'forum_debatepost', 'forum_attachment', 'forum_typeoptionvar', 'forum_forumrecommend', 'forum_postposition'
	);

	foreach($tids as $tid) {
		my_thread_log('delete', array('tid' => $tid));
	}

	$threadsdel = 0;
	if($tids = dimplode($tids)) {
		$auidarray = array();
		$query = DB::query("SELECT uid, attachment, dateline, thumb, remote, aid FROM ".DB::table('forum_attachment')." WHERE tid IN ($tids)");
		while($attach = DB::fetch($query)) {
			dunlink($attach);
			if($attach['dateline'] > $_G['setting']['losslessdel']) {
				$auidarray[$attach['uid']] = !empty($auidarray[$attach['uid']]) ? $auidarray[$attach['uid']] + 1 : 1;
			}
		}

		if($auidarray) {
			updateattachcredits('-', $auidarray, $_G['setting']['creditspolicy']['postattach']);
		}

		require_once libfile('function/delete');
		foreach($cleartable as $tb) {
			if($tb == 'forum_post') {
				deletepost("tid IN ($tids)");
				continue;
			}
			DB::query("DELETE FROM ".DB::table($tb)." WHERE tid IN ($tids)", 'UNBUFFERED');
		}

		DB::query("DELETE FROM ".DB::table('forum_thread')." WHERE tid IN ($tids)");
		$threadsdel = DB::affected_rows();
	}
	return $threadsdel;
}

function undeletethreads($tids) {
	global $_G;
	$threadsundel = 0;
	if($tids && is_array($tids)) {
		foreach($tids as $t) {
			my_thread_log('restore', array('tid' => $t));
		}
		$tids = '\''.implode('\',\'', $tids).'\'';

		$tuidarray = $ruidarray = $fidarray = array();
		$postarray = getfieldsofposts('fid, first, authorid', "tid IN ($tids)");
		foreach($postarray as $post) {
			if($post['first']) {
				$tuidarray[] = $post['authorid'];
			} else {
				$ruidarray[] = $post['authorid'];
			}
			if(!in_array($post['fid'], $fidarray)) {
				$fidarray[] = $post['fid'];
			}
		}
		if($tuidarray) {
			updatepostcredits('+', $tuidarray, 'post');
		}
		if($ruidarray) {
			updatepostcredits('+', $ruidarray, 'reply');
		}

		updatepost(array('invisible' => '0'), "tid IN ($tids)", true);
		DB::query("UPDATE ".DB::table('forum_thread')." SET displayorder='0', moderated='1' WHERE tid IN ($tids)");
		$threadsundel = DB::affected_rows();

		updatemodlog($tids, 'UDL');
		updatemodworks('UDL', $threadsundel);

		foreach($fidarray as $fid) {
			updateforumcount($fid);
		}
	}
	return $threadsundel;
}
?>